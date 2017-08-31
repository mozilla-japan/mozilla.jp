        </div><!-- end #doc -->
      </div><!-- end #doc-outer -->
<?php if (!empty($breadcrumbs)): ?>
      <nav id="breadcrumbs" role="navigation" aria-label="パンくずリスト" itemprop="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
        <p><?= $mj->build_breadcrumbs($breadcrumbs); ?></p>
      </nav>
<?php endif; ?>
      <footer id="pagefooter" role="contentinfo" itemscope itemtype="http://schema.org/WPFooter">
        <div id="globalfooter">
          <nav role="navigation" aria-label="Mozilla Japan グローバルナビゲーション" itemscope itemtype="http://schema.org/SiteNavigationElement">
            <h1 role="presentation"><a href="/">Mozilla</a></h1>
            <ul>
              <li><a href="https://www.mozilla.org/ja/firefox/">ブラウザー <strong>Firefox</strong></a>
                <ul>
                  <li><a href="https://www.mozilla.org/ja/firefox/desktop/">デスクトップ版</a></li>
                  <li><a href="https://www.mozilla.org/ja/firefox/android/">Android 版</a></li>
                  <li><a href="https://www.mozilla.org/ja/firefox/ios/">iOS 版</a></li>
                  <li><a href="https://addons.mozilla.org/ja/firefox/">アドオン</a></li>
                  <li><a href="https://support.mozilla.org/ja/">サポート</a></li>
                </ul>
              </li>
              <li><a href="https://www.thunderbird.net/ja/">メールソフト <strong>Thunderbird</strong></a>
                <ul>
                  <li><a href="https://addons.mozilla.org/ja/thunderbird/">アドオン</a></li>
                  <li><a href="https://support.mozilla.org/ja/products/thunderbird">サポート</a></li>
                </ul>
              </li>
              <li><a href="/business/">法人向け情報</a>
                <ul>
                </ul>
              </li>
              <li><a href="/community/">コミュニティ</a>
                <ul>
                  <li><a href="https://mozilla.doorkeeper.jp/">イベント</a></li>
                  <li><a href="https://fxstudent.tumblr.com/">Firefox 学生マーケティングチーム</a></li>
                  <li><a href="https://dev.mozilla.jp/">Mozilla Developer Street</a></li>
                  <li><a href="https://developer.mozilla.org/ja/">Mozilla Developer Network</a></li>
                </ul>
              </li>
              <li><a href="https://medium.com/mozilla-japan">最新情報</a>
                <ul>
                  <li><a href="https://medium.com/mozilla-japan">Medium</a></li>
                  <li><a href="https://twitter.com/mozillajp">Twitter</a></li>
                  <li><a href="https://www.facebook.com/mozillajapan">Facebook</a></li>
                </ul>
              </li>
              <li><a href="/about/"><strong>Mozilla</strong> について</a>
                <ul>
                </ul>
              </li>
            </ul>
          </nav>
          <div id="copyright">
            <p>Copyright &#169; <span itemprop="copyrightYear">2004</span>&#8211;<?=date('Y');?> <span itemprop="copyrightHolder" itemscope itemtype="http://schema.org/Organization"><span itemprop="name">Mozilla Japan Community</span></span>. All rights reserved. <?php if (!empty($extra_footer_links)): echo $extra_footer_links; endif; ?></p>
          </div>
        </div><!-- end #globalfooter -->
      </footer><!-- end #pagefooter -->
    </div><!-- end #wrapper -->
<?php
require_once dirname(__FILE__) . '/shared-footer.inc.php';
