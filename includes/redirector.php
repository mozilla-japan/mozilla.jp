<?php
/*
 * .htaccess の RewriteRule から呼び出されるリダイレクタ
 */

require_once dirname(__FILE__) . '/config.inc.php';
require_once dirname(__FILE__) . '/products/details.inc.php';
require_once dirname(__FILE__) . '/redirector/helper.inc.php';
require_once dirname(__FILE__) . '/redirector/notes.inc.php';
require_once dirname(__FILE__) . '/functions.inc.php';
require_once dirname(__FILE__) . '/lib/chromephp/ChromePhp.php';

$mj = new MJ();

// Firefox/Thunderbird ダウンロードページ 旧 URL
if (preg_match('#^/products/download#', $_SERVER['REDIRECT_URL'])) {
  if (isset($_GET['product']) && preg_match("/^(firefox|thunderbird)-([0-9\.abrc]+)$/", $_GET['product'], $matches) === 1) {
    $product = $matches[1];
    $version = $matches[2];
  }
  if (isset($_GET['os']) && preg_match('/^(win|osx|linux)$/', $_GET['os'], $matches) === 1) {
    $os = $matches[1];
  }
  if (isset($product) && isset($version) && isset($os)) {
    if ($product === 'firefox' && preg_match("/^\d+(?:\.\d+)+[ab]\d+$/", $version) === 1) {
      $url = '/' . $product . '/download/beta/';
    } else {
      $url = '/' . $product . '/download/?os=' . $os . '&version=' . $version;
    }
  }
}

// Firefox システム要件
if (preg_match('#^/firefox/(download/)?system-requirements#', $_SERVER['REDIRECT_URL'])) {
  $url = '/firefox/' . Firefox::RELEASE_VERSION . '/system-requirements/';
}

// Firefox 最新版リリースノート
if (preg_match('#^/firefox/(latest/release)?notes/?#', $_SERVER['REDIRECT_URL'])) {
  $url = '/firefox/' . Firefox::RELEASE_VERSION . '/releasenotes/';
}
if (preg_match('#^/firefox/beta/notes/?#', $_SERVER['REDIRECT_URL'])) {
  $url = '/firefox/' . Firefox::BETA_MAJOR_VERSION . 'beta/releasenotes/';
}
if (preg_match('#^/(firefox/android|mobile)/notes/?#', $_SERVER['REDIRECT_URL'])) {
  $url = '/firefox/android/' . FirefoxAndroid::RELEASE_VERSION . '/releasenotes/';
}
if (preg_match('#^/(firefox/android|mobile)/beta/notes/?#', $_SERVER['REDIRECT_URL'])) {
  $url = '/firefox/android/' . FirefoxAndroid::BETA_MAJOR_VERSION . 'beta/releasenotes/';
}

// Thunderbird システム要件
if (preg_match('#^/thunderbird/(download/)?system-requirements#', $_SERVER['REDIRECT_URL'])) {
  $url = '/thunderbird/' . Thunderbird::RELEASE_VERSION . '/system-requirements/';
}

// Thunderbird 最新版リリースノート
if (preg_match('#^/thunderbird/(latest/release)?notes/#', $_SERVER['REDIRECT_URL'])) {
  $url = '/thunderbird/' . Thunderbird::RELEASE_VERSION . '/releasenotes/';
}
if (preg_match('#^/thunderbird/beta/notes/?#', $_SERVER['REDIRECT_URL'])) {
  $url = '/thunderbird/' . Thunderbird::BETA_MAJOR_VERSION . 'beta/releasenotes/';
}

// Firefox リリースノート、in-product ページ
if (preg_match('#^/firefox/(\d+(?:\.\d+)+(?:(?:beta|b|rc)\d*)?)/([\w\-]+)/#', $_SERVER['REDIRECT_URL'], $matches)) {
  switch ($matches[2]) {
    case 'releasenotes':
        if(MJ\Redirector\Notes\is_unreleased_version($matches[1], Firefox::RELEASE_MAJOR_VERSION, Firefox::BETA_MAJOR_VERSION)){
            //URL が /XX.X/ であっても未リリース時には beta
            MJ\Redirector\to_beta('firefox', $matches[1], 'releasenotes');
        }
        $path = MJ\Redirector\Notes\resolve("firefox", $matches[1]);
        break;

    case 'system-requirements':
      $filename = '/system-requirements.html';
      $version = $matches[1];
      $major_version = intval($version);
      if($major_version == 45 && floatval($version) >= 45.1){
        $filename = '/system-requirements-45.1-later.html';
      }
      $path = $major_version . $filename;
      break;
  }

  if (isset($path)) {
    $rewrite = true;
    $_SERVER['REDIRECT_URL'] = '/firefox/releases/' . $path;
  }
}

// Android 版 Firefox リリースノート、対応機種
if (preg_match('#^/firefox/android/(\d+(?:\.\d+)+(?:beta)?)/([\w\-]+)/#', $_SERVER['REDIRECT_URL'], $matches)) {
    switch ($matches[2]) {
    case 'releasenotes':
        if(MJ\Redirector\Notes\is_unreleased_version($matches[1], FirefoxAndroid::RELEASE_MAJOR_VERSION, FirefoxAndroid::BETA_MAJOR_VERSION)){
            MJ\Redirector\to_beta('firefox/android', $matches[1], 'releasenotes');
        }
        $path = MJ\Redirector\Notes\resolve("android", $matches[1]);
        break;
    case 'system-requirements':
        $path = intval($matches[1]) . '/system-requirements.html';
        break;
  }

  if (isset($path)) {
    $rewrite = true;
    $_SERVER['REDIRECT_URL'] = '/firefox/android/releases/' . $path;
  }
}

function try_resolution_fxios($url){
    $redirect = FirefoxIOS::resolve_releasenotes_path($url);

    if(isset($redirect)){
        if(!$redirect->rewrite()){
            return $path = $redirect->destination();
        }
        $rewrite = true;
        $_SERVER['REDIRECT_URL'] = $redirect->destination();
    }
    return null;
}
if(!isset($url)){
    $url = try_resolution_fxios($_SERVER['REDIRECT_URL']);
}

// Thunderbird リリースノート、in-product ページ
if (preg_match('#^/thunderbird/(\d+(?:\.\d+)+(?:(?:beta|b|rc|esr)\d*)?)/([\w\-]+)/#', $_SERVER['REDIRECT_URL'], $matches)) {
  switch ($matches[2]) {
    case 'releasenotes':
        if(MJ\Redirector\Notes\is_unreleased_version($matches[1], Thunderbird::RELEASE_MAJOR_VERSION, Thunderbird::BETA_MAJOR_VERSION)){
            MJ\Redirector\to_beta('thunderbird', $matches[1], 'releasenotes');
        }
        $path = MJ\Redirector\Notes\resolve("thunderbird", $matches[1]);
        break;

    case 'system-requirements':
      $path = intval($matches[1]) . '/system-requirements.html';
      break;
  }
  if (isset($path)) {
    $rewrite = true;
    $_SERVER['REDIRECT_URL'] = '/thunderbird/releases/' . $path;
  }
}

unset($product, $version, $os, $matches, $path);

if (!isset($url) && !isset($rewrite)) {
  $rewrite = true;
  $_GET['error'] = 404;
}

if (isset($url)) {
  // リダイレクト
    MJ\Redirector\to($url);
}

if (isset($rewrite)) {
  // 内部リライト (引き続き prefetch.php で処理)
  require_once dirname(__FILE__) . '/prefetch.php';
}
