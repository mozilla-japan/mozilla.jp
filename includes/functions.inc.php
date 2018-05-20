<?php
class MJ
{
  // API キー (www.mozilla.jp 専用)
  const GOOGLE_API_KEY = 'ABQIAAAAYmLFSbOWmwJCCKjXaw81jBQ39dVujtsGkEETtP6H4vtmnKzdzhS0hxYFCV8HJShIu4flizPtbkmHBw';

  // CSS (特に複数のページで使い回すもの) の最終更新日を集中管理
  // 20141223 20140512
  public $css = array(
    'business/main' => 2017083020,
    'covehead/global' => 2017011823,
    'sandstone/global' => 2017112502,
    'sandstone/about' => 2017072519,
    'sandstone/community' => 2017011608,
    'sandstone/endorsements' => 2014122401,
    'sandstone/product-relnotes' => 2016042701,
    'sandstone/product-relnotes-sand' => 2017073100,
    'sandstone/product-sysreq' => 2016042701,
    'portala/base' => 2017073117,
    'tabzilla/tabzilla' => 2017011808,
  );

  public $js = array(
    'business/main' => 2016092818,
    'mj/global' => 2016092818,
    'home/pager' => 201351200,
  );

  public $products;

  /*
   * コンストラクター
   */
  public function __construct()
  {
    $this->products = array(
      'firefox' => new Firefox,
      'fxandroid' => new FirefoxAndroid,
      'thunderbird' => new Thunderbird
    );
  }

  /*
   * パンくずリストの生成
   */
  public function build_breadcrumbs($crumbs)
  {
    $position = 1;
    $last = end(array_keys($crumbs));

    ob_start();
    echo '<a href="/">ホーム</a>';

    foreach ($crumbs as $path => $label) {
      echo ' &rsaquo; <span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';

      if ($path === $last) {
        // 現在表示しているページにはリンクを張らない
        echo '<meta itemprop="item" content="' . $path . '"><strong itemprop="name">' . $label . '</strong>';
      } else {
        // 上位のページへのリンク
        echo '<a itemprop="item" href="' . $path . '"><span itemprop="name">' . $label . '</span></a>';
      }

      echo '<meta itemprop="position" content="' . $position .'"></span>';
      $position++;
    }

    return ob_get_clean();
  }

  /*
   * ブログ記事などの「この情報を共有」モジュールの取得
   * Twitter、はてなブックマークなどに対応。取得はバックグラウンドで行う。
   *
   * @param srting path - 記事のパス
   * @param string title - 記事のタイトル
   */
  public function get_social_widget($title, $path)
  {
    $str = array();
    $e_url = urlencode('https://www.mozilla.jp' . $path);
    $e_title = urlencode($title);

    // Twitter
    // 文字数を考慮: 140 - URL - RT 用に 3 文字
    $str[] = '<span class="tw"><a href="https://twitter.com/share?text=' . $e_title . '&amp;url=' . $e_url . '&amp;via=mozillajp" rel="nofollow" class="post" title="この記事を Twitter へ投稿">この記事を Twitter へ投稿</a></span>';

    // Facebook Shares/Likes
    $str[] = '<span class="fs"><a href="https://www.facebook.com/sharer.php?t=' . $e_title . '&amp;u=' . $e_url . '" rel="nofollow" class="post" title="この記事を Facebook へ投稿">この記事を Facebook へ投稿</a></span>';

    // Google+
    $str[] = '<span class="gp"><a href="https://plus.google.com/share?url=' . $e_url . '" rel="nofollow" class="post" title="この記事を Google+ で勧める">この記事を Google+ で勧める</a></span>';

    // はてなブックマーク
    $str[] = '<span class="hb"><a href="http://b.hatena.ne.jp/append?' . $e_url . '" rel="nofollow" class="post" title="この記事をはてなブックマークへ追加">この記事をはてなブックマークへ追加</a></span>';

    // スペースで連結して返す
    return '<span class="social-widget">' . join(" ", $str) . '</span>';
  }

  /*
   * キャッシュマネージャーの取得
   *
   * @param array options: Cache_Lite オプション
   * @return object cache: Cache_Lite
   */
  public function get_cache_manager($options = array())
  {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/lib/Cache_Lite/Lite.php');

    // キャッシュ有効期間 (引数オプションの単位は分なので、秒単位に変換。指定がなければ 1 時間)
    $options['lifeTime'] = (isset($options['lifeTime'])) ? 60 * $options['lifeTime'] : 3600;

    $cache = new Cache_Lite($options);
    return $cache;
  }
}
