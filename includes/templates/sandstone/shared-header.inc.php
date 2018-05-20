<?php
$page_type          = empty($page_type)         ? 'WebPage'         : $page_type; // http://schema.org/WebPage
$meta_robots        = empty($meta_robots)       ? ''                : $meta_robots;
$breadcrumbs        = empty($breadcrumbs)       ? array()           : $breadcrumbs;
$body_class         = empty($body_class)        ? ''                : $body_class;
$css                = empty($css)               ? array()           : $css;
$js                 = empty($js)                ? array()           : $js;
$language           = empty($language)          ? "ja"              : $language;

require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/path.inc.php";

if (!empty($meta_robots)) {
  header('X-Robots-Tag: ' . $meta_robots);
}

ob_start();

?>
<!DOCTYPE html>
<html lang="<?= $language ?>">
  <head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
<?php
echo $shared_html_head['meta'];

if (!empty($page_description)) {
  echo '    <meta name="description" content="' . $page_description . '">' . PHP_EOL;
}

if (!empty($og_type)) {
  echo '    <meta property="og:type" content="' . $og_type . '">' . PHP_EOL;
}

?>
    <meta property="og:site_name" content="Mozilla Japan">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:url" content="<?= $canonical_url ?>">
    <meta property="og:image" content="<?= preg_replace('#^/#', 'https://www.mozilla.jp/', $page_image) ?>">
    <meta property="og:title" content="<?= $page_title ?>">
<?php
if (!empty($page_description)) {
  echo '    <meta property="og:description" content="' . $page_description . '">' . PHP_EOL;
}

if (!empty($og_misc)) {
  echo $og_misc;
}

echo $shared_html_head['link'];

?>
    <link rel="canonical" href="<?= $canonical_url ?>">
    <link rel="shortcut icon" href="<?= $favicon ?>">
<?php
  foreach ($css as $href) {
    $with_serial = MJ\Path\add_serial_number($href, $mj->css);
    $full_path = MJ\Path\expand_path($with_serial, '/static/styles');
    echo '    <link rel="stylesheet" type="text/css" href="' . $full_path . '" media="print,screen">' . PHP_EOL;
  }

if (!empty($extra_headers)) {
  echo $extra_headers . PHP_EOL;
}

?>
  </head>
