<?php
/*
 * CSS や JavaScript ファイルからコメントや空白、改行を取り除いて最小化する。
 * .htaccess の Action によって呼び出す。開発時は $enabled を false に。
 * JavaScript の場合、行末の ; が省略されていたりすると、改行を削除したときに
 * エラーになるので、必ず事前に動作確認すること。また、jQuery で使用する
 * $('#video-subtitles [lang="ja"]') ような単純な属性セレクタも、[ の前の
 * スペースが削除されるので注意が必要。
 */

require_once dirname(__FILE__) . '/config.inc.php';
require_once dirname(__FILE__) . '/lib/Cache_Lite/Lite.php';

class Cache
{
  public $enabled = true;
  public $valid = false;
  public $modified = 0;
  public $content = '';

  // キャッシュに保存するデータの区切り
  private $separator = '>>>>>>>>>>';

  public function __construct($id)
  {
    // Cache_Lite ライブラリを使ってキャッシュする
    // 有効期限は無期限 = ファイルが編集されるまでは更新しない
    $this->lib = new Cache_Lite(array('lifeTime' => null));

    $this->data = $this->get($id);

    if (count($this->data) === 2) {
      $this->valid = true;
      $this->modified = intval($this->data[0]);
      $this->content = $this->data[1];
    }
  }

  public function get($id)
  {
    $data = $this->lib->get($id);

    return (empty($data)) ? array() : explode($this->separator, $data);
  }

  public function save($data = array())
  {
    return $this->lib->save(implode($this->separator, $data));
  }
}

class MinifyCore
{
  public $enabled = true;
  public $minified = array();
  public $combined = array();

  public function __construct()
  {
    $this->file = (object) array();
    $this->file->path = $_SERVER['REDIRECT_URL'];
    $this->file->modified = $this->get_modified_time();

    $this->cache = new Cache('www.mozilla.jp' . $this->file->path);

    // キャッシュや最小化の無効化設定がある場合
    if (NO_MINIFY) {
      $this->enabled = false;
    }
    if (NO_CACHE) {
      $this->cache->enabled = false;
    }

    // URL に ?cache=0 を付けた場合は、キャッシュをスキップ
    if (isset($_GET['cache']) && intval($_GET['cache']) === 0) {
      $this->cache->enabled = false;
    }

    // URL に ?raw=1 を付けた場合は、最小化せず、キャッシュもスキップ
    if (isset($_GET['raw']) && intval($_GET['raw']) === 1) {
      $this->enabled = false;
      $this->cache->enabled = false;
    }

    // URL 末尾が -min.js や -min.css の場合は、最小化せず、キャッシュもスキップ
    //substr( $file, strlen( $file ) - 7 ) === '-min.js'
    if (preg_match('/\\.(css|js)$/', $this->file->path)) {
      $this->enabled = false;
      $this->cache->enabled = false;
    }
  }

  // ファイルの最終更新日時を取得
  public function get_modified_time()
  {
    // リクエストされたファイル名から特定
    if (preg_match('/\-(\d{8,10})(\.[a-z1-9]{2,4})?$/', $_SERVER['REQUEST_URI'], $matches)) {
      return strtotime($matches[1]);
    }

    // ファイル情報から直接取得
    return filemtime($_SERVER['DOCUMENT_ROOT'] . $this->file->path);
  }

  // Last-Modified ヘッダー用に日時をフォーマット
  public function format_time($time)
  {
    return str_replace('+0000', 'GMT', gmdate('r', $time));
  }

  // ソースを取得
  public function get_source($path)
  {
    $source = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $path);

    return (empty($source)) ? '' : $source;
  }

  // ソースを最小化
  // ファイルタイプによって処理を上書き
  public function minify_source($source)
  {
    return $source;
  }

  // メイン：ファイルの出力
  public function flush()
  {
    header('Content-type: ' . $this->mimetype);

    // サーバー側に有効なキャッシュが存在した場合
    if ($this->cache->enabled && $this->cache->valid) {
      $headers = apache_request_headers();

      // ブラウザー側にキャッシュが存在した場合は、単純に Not Modified ヘッダーを返して終了
      if (isset($headers['If-Modified-Since']) &&
          strtotime($headers['If-Modified-Since']) === $this->cache->modified) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');

        return;
      }

      // サーバー側のキャッシュが更新されていない場合は、その内容を返して終了
      if ($this->file->modified === $this->cache->modified) {
        header('Last-Modified: ' . $this->format_time($this->cache->modified));
        echo $this->cache->content;

        return;
      }
    }

    // 設定を確認し、必要ならば複数のファイルをひとつにまとめる
    $files = (array_key_exists($this->file->path, $this->combined))
           ? $this->combined[$this->file->path] : array($this->file->path);

    ob_start();

    foreach ($files as $file) {
      $source = $this->get_source($file);
      // すでに圧縮されている場合は、最小化せずそのまま出力
      if (!$this->enabled || in_array($file, $this->minified)) {
        echo $source;
      } else {
        echo $this->minify_source($source);
      }
    }

    // ヘッダーと最小化した内容を出力
    header('Last-Modified: ' . $this->format_time($this->file->modified));
    echo $content = ob_get_clean();

    // キャッシュに保存して終了
    if ($this->cache->enabled) {
      $this->cache->save(array($this->file->modified, $content));
    }
  }
}

class MinifyCSS extends MinifyCore
{
  public $mimetype = 'text/css';

  // 最小化処理
  public function minify_source($source)
  {
    // コメントと改行を削除
    $source = preg_replace('/(\/\*.*\*\/|\n)/sU', '', $source);

    // 空白を削除
    $source = preg_replace('/\s*([{}:;,])\s*/', '$1', $source);

    // 不要な区切り文字 (;) を削除
    $source = preg_replace('/;}/', '}', $source);

    return $source;
  }
}

class MinifyJS extends MinifyCore
{
  public $mimetype = 'text/javascript';

  // すでに圧縮されているファイル
  public $minified = array(
  );

  // ひとつに統合するファイル
  public $combined = array(
    '/static/scripts/mj/global.js' => array(
      '/static/scripts/lib/jquery/jquery.aria.js',
      '/static/scripts/mj/global.js'
    )
  );

  // 最小化処理
  public function minify_source($source)
  {
    // インラインコメントを削除
    $source = preg_replace('/\s\/\/\s.*$/m', '', $source);

    // ブロックコメントと改行を削除
    $source = preg_replace('/(\/\*.*\*\/|\n)/sU', '', $source);

    // 空白を削除
    $source = preg_replace('/\s+/', ' ', $source);
    $source = preg_replace('/\s*([{}\(\)\[\]:;,\+\-\*\/=<>\&\|\?!])\s*/', '$1', $source);

    // 不要な区切り文字 (;) を削除
    $source = preg_replace('/;}/', '}', $source);

    return $source;
  }
}

class Minify
{
  public $handler = null;

  public function __construct()
  {
    $info = pathinfo($_SERVER['REDIRECT_URL']);

    // ファイル拡張子によって処理方法を決定
    switch ($info['extension']) {
      case 'css':
        $this->handler = new MinifyCSS();
        break;
      case 'js':
        $this->handler = new MinifyJS();
        break;
    }
  }

  public function flush()
  {
    if ($this->handler) {
      $this->handler->flush();
    }
  }
}

$minify = new Minify();
$minify->flush();
