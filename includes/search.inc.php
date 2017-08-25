<?php 
if (!isset($_GET['q']) || empty($_GET['q']) || !isset($cse_id)) {
  $protocol = (isset($_SERVER['HTTPS'])) ? 'https' : 'http';
  header('Pragma: no-cache');
  header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, private');
  header('Location: ' . $protocol . '://' . $_SERVER['HTTP_HOST'] . '/');
  exit;
}

// Google Custom Search Engine と AJAX Search API を利用したサイト内検索
// 実際には JavaScript の代わりに PHP で JSON オブジェクトを処理している
// http://www.google.com/coop/cse/
// http://code.google.com/intl/ja/apis/ajaxsearch/documentation/#fonje
// http://jp.php.net/manual/ja/function.json-decode.php

// 検索の基本設定
$query = $_GET['q'];
$cse_url = 'http://www.google.com/cse?cx=' . $cse_id . '&cof=FORID%3A0&q=' . urlencode($query);

// 検索 URL の設定
$url = 'http://ajax.googleapis.com/ajax/services/search/web?v=1.0&rsz=large&hl=ja';
$url .= '&q=' . urlencode($query) . '&key=' . MJ::GOOGLE_API_KEY . '&cx=' . $cse_id;
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
  $start = ($_GET['page'] - 1) * 8;
  $url .= '&start=' . $start;
}

// 検索を実行
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_REFERER, 'https://www.mozilla.jp/');
$response = curl_exec($ch);
curl_close($ch);
$json = (!$response) ? false : json_decode($response);

// デバッグ
// print_r($json); exit;

// 結果を表示
function showResults()
{
  global $json, $query;

  $indent = '              ';
  
  // 結果を取得できなかった場合
  if (!$json || $json->responseStatus === 400) {
    echo $indent . '<section id="sec-results">' . PHP_EOL;
    echo $indent . '  <h2>検索結果</h2>' . PHP_EOL;
    echo $indent . '  <p>申し訳ありません。ただいまサイト内検索はご利用になれません。</p>' . PHP_EOL;
    echo $indent . '</section>' . PHP_EOL;
    return false;
  }
  
  $results = $json->responseData->results;

  // 結果が 0 件の場合
  if (count($results) === 0) {
    echo $indent . '<section id="sec-results">' . PHP_EOL;
    echo $indent . '  <h2>検索結果</h2>' . PHP_EOL;
    echo $indent . '  <p>お探しの情報は見つかりませんでした。キーワードを変えて検索してみてください。</p>' . PHP_EOL;
    echo $indent . '</section>' . PHP_EOL;
    return false;
  }

  // 結果一覧
  echo $indent . '<section id="sec-results">' . PHP_EOL;
    echo $indent . '  <h2>検索結果</h2>' . PHP_EOL;
  for ($i = 0; $i < count($results); $i++) {
    $r = $results[$i];
    echo $indent . '  <section>' . PHP_EOL;
    $linkURL = htmlspecialchars($r->unescapedUrl);
    echo $indent . '    <h3><a href="' . $linkURL . '">' . $r->title . '</a></h3>' . PHP_EOL;
    echo $indent . '    <p>' . $r->content . '</p>' . PHP_EOL;
    $visibleURL = htmlspecialchars(preg_replace("/^https?:\/\//", "", urldecode($r->unescapedUrl)));
    echo $indent . '    <p class="url">' . $visibleURL . '</p>' . PHP_EOL;
    echo $indent . '  </section>' . PHP_EOL;
  }
  echo $indent . '</section>' . PHP_EOL;

  // ページ一覧
  $pages = $json->responseData->cursor->pages;
  if (count($pages) > 0) {
    $currentIdx = $json->responseData->cursor->currentPageIndex;
    $q = urlencode($query);

    echo $indent . '<section id="sec-pagination">' . PHP_EOL;
    echo $indent . '  <h2>ページの移動</h2>' . PHP_EOL;
    echo $indent . '  <ul>' . PHP_EOL;
    if ($currentIdx !== 0) {
      $prev = $currentIdx;
      echo $indent . '    <li><a href="?q=' . $q . '&amp;page=' . $prev . '" rel="prev">前へ</a></li>' . PHP_EOL;
    }
    for ($i = 0; $i < count($pages); $i++) {
      $p = $pages[$i];
      if ($i === $currentIdx)
        echo $indent . '    <li><strong>' . $p->label . '</strong></li>' . PHP_EOL;
      else
        echo $indent . '    <li><a href="?q=' . $q . '&amp;page=' . $p->label . '">' . $p->label . '</a></li>' . PHP_EOL;
    }
    if ($currentIdx !== count($pages) - 1) {
      $next = $currentIdx + 2;
      echo $indent . '    <li><a href="?q=' . $q . '&amp;page=' . $next . '" rel="next">次へ</a></li>' . PHP_EOL;
    }
    echo $indent . '  </ul>' . PHP_EOL;
    echo $indent . '</section>' . PHP_EOL;
  }

  return true;
}
