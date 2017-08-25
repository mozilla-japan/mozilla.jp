          <div id="upcoming-events" class="outer upcoming-events">
            <div class="inner">
              <aside>
                <h2>イベント情報</h2>
<?php

@include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/events.inc.php';

foreach ((new Events('business'))->get_all() as $event):
  echo <<< EOT
                <section itemscope itemtype="http://schema.org/Event">
                  <h3 itemprop="name"><a href="{$event->url}" itemprop="url">{$event->name}</a></h3>
                  <ul class="flat">
                    <li><time datetime="{$event->startDate}" itemprop="startDate">{$event->get_startDate()}</time></li>
                    <li itemprop="location" itemscope itemtype="http://schema.org/Place"><span itemprop="name">{$event->location->name}</span> <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">(<span itemprop="addressRegion">{$event->location->addressRegion}</span><span itemprop="addressLocality">{$event->location->addressLocality}</span>)</span></li>
                  </ul>
                </section>

EOT;
endforeach;

?>
                <footer>
                  <p><a href="/events/2015/seminar-1029/">「Firefox 法人利用セミナー」講演レポート</a> で、Firefox 法人導入における具体的なカスタマイズの事例や集中管理の方法などについて詳しくご紹介しています。<a href="https://mozilla.doorkeeper.jp/">Doorkeeper の Mozilla コミュニティ</a> に参加いただければ、今後のイベントの案内をメールでお送りします。</p>
                </footer>
              </aside>
            </div>
          </div>
