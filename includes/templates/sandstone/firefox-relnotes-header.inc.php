<?php

$_channels = array('beta' => 'Beta', 'release' => 'Release');
$_channel = isset($_channel) ? $_channel : 'release';
$_channel_label = $_channels[$_channel];
$_channel_label_short = $_channel === 'release' ? '' : $_channel_label;

$_platforms = array('desktop' => 'デスクトップ版', 'android' => 'Android 版', 'ios' => 'iOS 版');
$_platform = isset($_platform) ? $_platform : 'desktop';
$_platform_label = $_platforms[$_platform];
$_platform_label_short = $_platform === 'desktop' ? '' : $_platform_label;

$_intver = intval($_version);

$page_type          = 'ItemPage';
$page_title         = "Firefox {$_version} {$_channel_label_short} {$_platform_label_short} リリースノート";
$body_id            = 'firefox-relnotes';
$body_class         = "firefox {$_channel} {$_platform} relnotes version";
$css                = array('sandstone/product-relnotes-sand');
$js                 = array('lib/jquery/jquery.waypoints.min.js', 'lib/jquery/jquery.waypoints.sticky.min.js', 'firefox/releasenotes.js');
$breadcrumbs        = array(
  'https://www.mozilla.org/ja/firefox/' => 'Firefox ブラウザー',
  "https://www.mozilla.org/ja/firefox/{$_platform}/" => $_platform_label,
  '/firefox/releases/' => 'リリースノート',
  "/firefox/{$_version}/releasenotes/" => "Firefox {$_version} {$_channel_label_short}",
);

if ($_platform !== 'desktop') {
  unset($breadcrumbs['/firefox/releases/']);
}

@include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/templates/sandstone/default-header.inc.php';

?>
  <main role="main" itemprop="mainContentOfPage" itemscope itemtype="http://schema.org/WebPageElement">
    <article role="article" itemscope itemtype="http://schema.org/Article">
      <header class="notes-head">
        <div class="intro">
          <div class="container">
            <h1 itemprop="name">Firefox リリースノート</h1>
            <p>このページでは Firefox の変更点をお伝えします。いつものように <a href="https://input.mozilla.org/ja/feedback/firefox/<?= $_version ?>">フィードバック</a> を歓迎します。<br>また、<a href="https://bugzilla.mozilla.org/">Bugzilla にバグを報告</a> したり、このバージョンの <a href="/firefox/<?= $_version ?>/system-requirements/">システム要件を確認</a> することもできます。</p>
          </div>
        </div>
        <div class="sticky-wrapper">
          <nav id="nav" class="navigator">
            <div class="container">
              <ul class="menu">
<? foreach(['desktop', 'android', 'ios'] as $__platform): ?>
<? if ($_platform === $__platform): ?>
                <li class="current"><?= $_platforms[$__platform] ?></li>
<? elseif ($__platform === 'android' && isset($_android_version)): ?>
                <li><a href="/firefox/android/<?= $_android_version ?>/releasenotes/"><?= $_platforms[$__platform] ?></a></li>
<? elseif ($__platform === 'desktop' && isset($_desktop_version)): ?>
                <li><a href="/firefox/<?= $_desktop_version ?>/releasenotes/"><?= $_platforms[$__platform] ?></a></li>
<? elseif ($__platform === 'desktop'): ?>
                <li><a href="/firefox/notes/"><?= $_platforms[$__platform] ?></a></li>
<? else: ?>
                <li><a href="/firefox/<?= $__platform ?>/notes/"><?= $_platforms[$__platform] ?></a></li>
<? endif; endforeach; ?>
                <li class="submenu-title" aria-expanded="false">
                  <a href="#" role="button" aria-controls="nav-submenu" aria-expanded="false">その他のリリース</a>
                  <ul class="submenu" id="nav-submenu">
                    <li class="title">開発中のデスクトップ版</li>
                    <li><a href="/firefox/beta/notes/">最新 Beta / Developer Edition</a></li>
                    <li><a href="https://www.mozilla.org/en-US/firefox/nightly/notes/">最新 Nightly</a></li>
                    <li class="title">開発中の Android 版</li>
                    <li><a href="/firefox/android/beta/notes/">最新 Beta</a></li>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
        </div>
        <div class="latest-release">
          <div class="container">
            <div class="version">
              <h2><?= $_version ?></h2>
              <h3><?= $_platform_label_short ?> Firefox <?= $_channel_label ?></h3>
              <p><time datetime="<?= $_date ?>" itemprop="datePublished"><?= str_replace('-', '/', $_date) ?></time></p>
            </div>
            <div class="description">
              <h2>バージョン <?= $_version ?> &mdash; <?= str_replace('-', '/', $_date) ?> より <?= $_channel_label ?> チャンネルユーザーへ提供開始</h2>
<? if ($_platform !== 'ios'): ?>
              <p>このバージョンに貢献いただいた <a href="https://blog.mozilla.org/community/firefox-<?= $_intver ?>-new-contributors/">新しい Mozillia コミュニティメンバー</a> 全員にこの場を借りて感謝します。</p>
<? endif; ?>
            </div>
          </div>
        </div>
      </header><!-- end #notes-head -->
      <section class="notes-section" itemprop="articleBody">
        <div class="features" id="new-features">
          <div class="container">
