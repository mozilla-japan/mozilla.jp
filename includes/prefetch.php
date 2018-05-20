<?php
require_once dirname(__FILE__) . '/config.inc.php';
require_once dirname(__FILE__) . '/functions.inc.php';
require_once dirname(__FILE__) . '/error-helper.inc.php';


$mj = new MJ();

// Don't set the language here; hack to include header/footer files properly
$lang = '';

$file_path = $_SERVER['REDIRECT_URL'];

// Google Webmaster Tools: site verification file
if ($file_path === '/google0d3bd3a8b976d918.html') {
  header('Status: 200 OK', true, 200);
  exit('google-site-verification: google0d3bd3a8b976d918.html');
}

if (is_dir($_SERVER['DOCUMENT_ROOT'] . $file_path)) {
  $file_path .= 'index.html';
}

if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file_path) && $file_path !== '/.htaccess') {
  $pos = strpos($_SERVER['REQUEST_URI'], '?');
  $canonical_url = 'https://www.mozilla.jp';
  $canonical_url .= ($pos === false) ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, $pos);
  unset($pos);
} else {
    $_GET['error'] = MJ\Error\normalize_error_code($file_path);
    MJ\Error\send_error_header(intval($_GET['error']));
    $file_path = MJ\Error\error_page_template(intval($_GET['error']));
    $canonical_url = '';
}

/* favicon の設定 */

if (strpos($file_path, '/firefox/') === 0) {
  $favicon = '/static/images/firefox/favicon-32.ico?v=201711';
  $page_image = '/static/images/firefox/logos/logo-only-128.png?v=201711';
} else if (strpos($file_path, '/thunderbird/') === 0) {
  $favicon = '/static/images/thunderbird/favicon-32.ico';
  $page_image = '/static/images/thunderbird/logos/logo-only-128.png';
} else {
  $favicon = '/static/images/global/favicon-2017011808.ico';
  $page_image = '/static/images/global/logo-1200-2017011808.png';
}

/* 全ページ共通 HTML 要素 (フッター) */

ob_start();

?>
    <!--[if lt IE 9]>
    <script src="/static/scripts/lib/iebugfix/IE9.js"></script>
    <script src="/static/scripts/lib/jquery/jquery-1.12.4.min.js"></script>
    <![endif]-->
    <!--[if gte IE 9]><!-->
    <script src="/static/scripts/lib/jquery/jquery-3.1.0.min.js"></script>
    <!--<![endif]-->
    <script src="https://use.typekit.net/qkb6mkb.js"></script>
    <script src="/static/scripts/mj/global-<?= $mj->js['mj/global'] ?>.js"></script>
<?php

$shared_footers = ob_get_clean();

// HTML ソース内のコメントで、":"を含まないものをすべて削除する
function remove_comments($buffer)
{
  return preg_replace('/\s*<!\-\-[^:]*\s\-\->/sU', '', $buffer);
}

ob_start('remove_comments');
@require_once $_SERVER['DOCUMENT_ROOT'] . $file_path;
ob_get_flush();

unset($lang, $file_path, $canonical_url, $favicon_dir, $shared_html_head, $caching_pages);

exit;
