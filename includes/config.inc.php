<?php 

// サーバーのリスト
// 注: PHP 5.6 以降なら配列定数を定義可能
$production_servers = array('www.mozilla.jp', 'prod.www.mozilla.jp');
$staging_servers = array('trunk.www.mozilla.jp');
define('ON_PRODUCTION', in_array($_SERVER['SERVER_NAME'], $production_servers));
define('ON_STAGE', in_array($_SERVER['SERVER_NAME'], $staging_servers));

// 公開サーバーではエラー出力をオフにする
if (ON_PRODUCTION) {
  ini_set('display_errors', '0');
  ini_set('error_reporting', 0);
} else {
  ini_set('display_errors', '1');
  ini_set('error_reporting', E_ALL | E_STRICT);
}

// ローカルで開発中は最小化やキャッシュしない
if (!ON_PRODUCTION) {
  define("NO_MINIFY", true);
  define("NO_CACHE", true);
} else {
  define("NO_MINIFY", false);
  define("NO_CACHE", false);
}

// ロケール設定
mb_internal_encoding('UTF-8');
mb_language('ja');
date_default_timezone_set('Asia/Tokyo');
setlocale(LC_ALL, 'ja_JP.UTF-8');
