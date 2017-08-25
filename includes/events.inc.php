<?php

class Events
{
  /**
   * イベントキャッシュを読み込み、配列へ展開し、カテゴリーで絞り込み。
   * @constructor
   * @param {string} $category - イベントカテゴリー。all (すべて)、official (公式のみ)、community (コミュニティのみ)、
   *  business (法人向けのみ)、nonbusiness (法人向け以外) のいずれか。デフォルトは all。
   * @return {object} $events - Events インスタンス
   */
  public function __construct($category = 'all')
  {
    $this->category = $category;
    $this->list = array();

    foreach ($this->get_cache() as $event) {
      $this->list[] = new Event($event);
    }

    $this->list = array_filter($this->list, function($event) {
      if ($this->category === 'official' || $this->category === 'community') {
        return $event->type === $this->category;
      }

      if ($this->category === 'business') {
        return strpos($event->name, '法人向け') !== false;
      }

      if ($this->category === 'nonbusiness') {
        return strpos($event->name, '法人向け') === false;
      }

      return true;
    });
  }

  /**
   * キャッシュされているイベント情報を取得。キャッシュがなければ JSON を読み込んでキャッシュを生成。JSON は毎時 cron で Doorkeeper から
   * 取得している。ソースが複数あるため、タイプを設定し、日付順で並べ替える。
   * @param {undefined}
   * @return {undefined}
   * @see {@link http://www.doorkeeperhq.com/developer/api}
   * @see {@link https://github.com/mozilla-japan/webdev-tools/tree/master/retrieve_events}
   */
  function get_cache()
  {
    global $mj;

    // キャッシュマネージャーを取得。有効期限はデフォルトの 1 時間
    $caches = $mj->get_cache_manager();

    // 有効なキャッシュがあれば即座にデコードして返す
    if ($cache = $caches->get('www.mozilla.jp-upcoming-events')) {
      if ($events = json_decode($cache)) {
        return $events;
      }
    }

    $events = array();
    $source = array(
      'official' => '/includes/events/mozilla.json',
      'community' => '/includes/events/firefox.json',
    );

    // データを取得し配列へ展開
    foreach ($source as $type => $path) {
      $data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $path);

      if ($data && $list = json_decode($data)) {
        foreach ($list as $entry) {
          $events[] = (object) array(
            'data' => $entry->event,
            'type' => $type,
          );
        }
      }
    }

    // 日付順に並べ替え
    usort($events, function($a, $b) {
      $ad = strtotime($a->data->starts_at);
      $bd = strtotime($b->data->starts_at);

      if ($ad === $bd) {
        return 0;
      }

      return ($ad < $bd) ? -1 : 1;
    });

    // キャッシュに保存
    $caches->save(json_encode($events), 'www.mozilla.jp-upcoming-events');

    return $events;
  }

  /**
   * すべてのイベントを返す。
   * @param {undefined}
   * @return {array} $events - イベントリスト。
   */
  public function get_all()
  {
    return $this->list;
  }
}

class Event
{
  /**
   * 実際に出力するイベントデータを Schema.org に準じて用意。
   * @constructor
   * @param {object} $event - イベント項目。
   * @param {object} $event->data - Doorkeeper のイベント項目。
   * @param {string} $event->type - official (公式のみ)、community (コミュニティのみ) のいずれか。
   * @return {object} $event - Event インスタンス。
   * @see {@link http://www.doorkeeperhq.com/developer/api}
   * @see {@link https://schema.org/Event}
   */
  public function __construct($event)
  {
    $this->data = $event->data;
    $this->type = $event->type;

    preg_match('/^(.+[都道府県])(.+[市区町村])(.+)/u', $this->data->address, $address);

    $this->name = htmlspecialchars($this->data->title);
    $this->url = htmlspecialchars($this->data->public_url);
    $this->startDate = htmlspecialchars($this->data->starts_at);
    $this->endDate = htmlspecialchars($this->data->ends_at);
    $this->location = (object) array(
      'name' => htmlspecialchars($this->data->venue_name),
      'addressRegion' => $address ? htmlspecialchars($address[1]) : '',
      'addressLocality' => $address ? htmlspecialchars($address[2]) : '',
      'streetAddress' => $address ? htmlspecialchars($address[3]) : '',
    );
  }

  /**
   * イベント開始日時のラベルを取得。
   * @param {string} $date_format - 日付形式。デフォルトは「1 月 1 日 (月)」。
   * @return {string} $date - 日付ラベル。
   * @see {@link http://jp2.php.net/manual/ja/function.strftime.php)
   */
  public function get_startDate($format = '%b 月 %e 日 (%a)')
  {
    // 古いバージョンの PHP では %b 変換指定子が %B と同じく「1月」になってしまうため、変換して回避
    return str_replace('月 月', ' 月', strftime($format, strtotime($this->data->starts_at)));
  }

  /**
   * イベント終了日時のラベルを取得。
   * @param {string} $date_format - 日付形式。デフォルトは「1 月 1 日 (月)」。
   * @return {string} $date - 日付ラベル。
   * @see {@link http://jp2.php.net/manual/ja/function.strftime.php)
   */
  public function get_endDate($format = '%b 月 %e 日 (%a)')
  {
    // 古いバージョンの PHP では %b 変換指定子が %B と同じく「1月」になってしまうため、変換して回避
    return str_replace('月 月', ' 月', strftime($format, strtotime($this->data->ends_at)));
  }
}
