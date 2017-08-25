<?php
// <meta>
ob_start();

?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
$shared_html_head['meta'] = ob_get_clean();

// <link>
ob_start();

?>
    <link rel="alternate" type="application/rss+xml" title="Mozilla Japanese Community ブログ (RSS)" href="https://medium.com/feed/mozilla-japan">
<?php
$shared_html_head['link'] = ob_get_clean();

// スタイルシート
$css = (empty($css)) ? array() : $css;
array_unshift($css, 'sandstone/global');
array_unshift($css, 'tabzilla/tabzilla');

require_once dirname(__FILE__) . '/shared-header.inc.php';

?>
  <body id="<?= $body_id ?>" class="<?= $body_class ?>" role="document" itemscope itemtype="http://schema.org/<?= $page_type ?>" itemref="copyright">
    <div id="wrapper">
      <header id="pageheader" role="banner" itemscope itemtype="http://schema.org/WPHeader">
        <div id="globalheader">
<?php if (strpos($file_path, '/firefox/') === 0 && strpos($file_path, '/firefox/os/') !== 0):?>
          <nav role="navigation" aria-label="Firefox 製品情報ナビゲーション" itemscope itemtype="http://schema.org/SiteNavigationElement" id="nav-firefox" class="nav-product">
            <h1 role="presentation"><a href="https://www.mozilla.org/ja/firefox/">Firefox</a></h1>
        <?php elseif (strpos($file_path, '/thunderbird/') === 0): ?>
          <nav role="navigation" aria-label="Thunderbird 製品情報ナビゲーション" itemscope itemtype="http://schema.org/SiteNavigationElement" id="nav-thunderbird" class="nav-product">
            <h1 role="presentation"><a href="https://www.thunderbird.net/ja/">無料メールソフト Thunderbird</a></h1>
        <?php else : ?>
          <nav role="navigation" aria-label="Mozilla Japan グローバルナビゲーション" itemscope itemtype="http://schema.org/SiteNavigationElement" id="nav-main">
            <h1 role="presentation"><strong>Mozilla</strong></h1>
        <?php endif; ?>
            <a href="/" id="tabzilla">Mozilla</a>
            <span class="toggle" tabindex="0" aria-controls="nav-main-menu" role="button">Menu</span>
            <ul id="nav-main-menu">
              <?php
                // 定義されていなければデフォルトのナビゲーション
                $header_nav = (empty($header_nav)) ? array(
                    "Firefox" => "https://www.mozilla.org/ja/firefox/",
                    "Thunderbird" => "https://www.thunderbird.net/ja/",
                    "法人向け情報" => "/business/",
                    "コミュニティ" => "/community/",
                    "最新情報" => "https://medium.com/mozilla-japan",
                    "Mozilla について" => "/about/",
                  ) : $header_nav;

                // $header_navが文字列だったらhtml (と期待して) そのまま出力
                if (is_string($header_nav)) :
                  echo $header_nav;

                // それ以外は配列 (ということになっている) なので、要素の数だけリンクをつくる
                else :
                  foreach ($header_nav as $key => $value) :
                    if (is_array($value)) :
              ?>
                      <li>
                        <a aria-haspopup="true" tabindex="0" href="<?php echo $value['href'] ?>"><?php echo $key ?></a>
                        <ul class="submenu" aria-expanded="false">
                          <?php foreach ($value['children'] as $title => $link) : ?>
                            <li><a tabindex="-1" href="<?php echo $link ?>"><?php echo $title ?></a></li>
                          <?php endforeach ?>
                        </ul>
                      </li>
                    <?php else : ?>
                      <li aria-expanded="false"><a href="<?php echo $value ?>"><?php echo $key ?></a></li>
                    <?php endif ?>
                  <?php endforeach ?>
                <?php endif ?>
            </ul>
          </nav>
        </div><!-- end #globalheader -->
      </header><!-- end #pageheader -->
      <div id="doc-outer">
        <div id="doc">
