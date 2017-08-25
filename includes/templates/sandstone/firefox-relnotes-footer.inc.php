          </div>
        </div>
      </section><!-- end #notes-section -->
      <section class="notes-footer">
        <div class="container">
          <div class="all-download">
            <a rel="external" href="../../all/">すべてのダウンロード</a>
          </div>
          <div class="download-button">
            <p class="primary"><a class="button green" href="https://www.mozilla.org/ja/firefox/new/?scene=2">Firefox 無料ダウンロード</a></p>
            <p class="secondary"><a href="https://www.mozilla.org/ja/privacy/firefox/">Firefox プライバシー</a></p>
          </div>
        </div>
      </section>
    </article>
  </main>
  <footer class="release-footer">
    <div class="container">
      <div class="col">
        <h2>サポートとフィードバック</h2>
        <a rel="external" href="https://bugzilla.mozilla.org/enter_bug.cgi">バグを報告</a>
        <a rel="external" href="https://input.mozilla.org/feedback?utm_source=releasenotes">フィードバックを送る</a>
      </div>
      <div class="col">
        <h2>協力者募集中！</h2>
        <p>Firefox を作っている Mozilla プロジェクトには、誰でも様々な形で参加できます。</p>
        <a href="/community/">Mozilla のコミュニティ</a>
      </div>
      <div class="col">
        <h2>関連情報</h2>
        <a rel="external" href="https://developer.mozilla.org/ja/Firefox/Releases/<?= $_intver ?>">開発者情報</a>
        <a rel="external" href="https://bugzilla.mozilla.org/buglist.cgi?j_top=OR&amp;f1=target_milestone&amp;o3=equals&amp;v3=Firefox%20<?= $_intver ?>&amp;o1=equals&amp;resolution=FIXED&amp;o2=anyexact&amp;query_format=advanced&amp;f3=target_milestone&amp;f2=cf_status_firefox<?= $_intver ?>&amp;bug_status=RESOLVED&amp;bug_status=VERIFIED&amp;bug_status=CLOSED&amp;v1=mozilla<?= $_intver ?>&amp;v2=fixed%2Cverified&amp;limit=0">このバージョンでのすべての変更点</a>
        <a href="/blog/category/firefox/">Mozilla Japan ブログ</a>
        <a href="/business/">Firefox 法人向け延長サポート版</a>
        <a href="/firefox/releases/">すべてのデスクトップ版 Firefox リリースノート</a>
      </div>
    </div>
  </footer>
<?php

@include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/templates/sandstone/default-footer.inc.php';

?>
