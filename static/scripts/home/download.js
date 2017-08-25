// ダウンロードリンクを一部ブラウザー、プラットフォーム向けに調整
$(function () {
  var ua = navigator.userAgent;
  var $button = $('#sec-firefox-download .button');

  // IE 旧バージョン
  if (/MSIE\s[1-8]\./.test(ua)) {
    $button.click(function () {
      window.open('https://download.mozilla.org/?product=firefox-stub&os=win&lang=ja', 'download_window',
                  'toolbar=0,location=no,directories=0,status=0,scrollbars=0,resizeable=0,width=1,height=1,top=0,left=0');
      window.focus();
    });
  }

  // Android
  if (/Android/.test(ua)) {
    $button.attr('href', 'market://details?id=org.mozilla.firefox');
  }

  // iOS
  if (/iPhone|iPad/.test(ua)) {
    $button.attr('href', 'itms-apps://itunes.apple.com/us/app/apple-store/id989804926?pt=373246&mt=8');
  }
});

// モバイルレイアウトではダウンロードボタンをプロモーション枠の上に表示
$(function () {
  if (typeof window.matchMedia !== 'function') {
    return;
  }

  var mql = window.matchMedia('(max-width: 767px)');

  var handler = function (mql) {
    $('#outer-download').insertAfter(mql.matches ? '#outer-header' : '#outer-promos');
  };

  mql.addListener(handler);
  handler(mql);
});
