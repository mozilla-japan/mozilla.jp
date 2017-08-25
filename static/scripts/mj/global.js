/*
 * Mozilla Japan サイトグローバルスクリプト
 * 依存ライブラリ: jQuery 1.5 以上
 */

var mj =
{
  /*
   * 初期化関数の呼び出しを設定
   */
  onbeforeload: function() {
    try {
      $(document).ready(function()
      {
        mj.init();
      });
    } catch (e) {}

    try {
      Typekit.load({ async: true });
    } catch (e) {}

    delete this.onbeforeload;
  },

  /*
   * 初期化
   */
  init: function() {
    this.analytics.init();
    this.nav.init();
    this.tabs.init();

    $('html').addClass('js');
    $('body').addClass('js');

    if(this.analytics){
      delete this.analytics.init;
    }

    if (typeof Mozilla === 'object' && Mozilla.Pager) {
      Mozilla.Pager.AUTO_ROTATE_INTERVAL = 5000;
    }

    delete this.nav.init;
    delete this.tabs.init;
    delete this.init;
  },

  get tracking(){
    return this.analytics != null;
  },

  nav:
  {
    init: function()
    {
      // モバイル用グローバルナビゲーションを有効化
      $('.toggle').on('click', function() {
        var $this = $(this);
        $this.parent().toggleClass('open');
        $this.siblings('ul').slideToggle();
      });
    }
  },

  tabs:
  {
    init: function()
    {
      $('.tabbox').each(function() {
        var tabbox = $(this);
        var tablist = $(this).find('[role="tablist"]:first');
        var tabs = tablist
          .find('[role="tab"]')
          .aria('selected', false)
          .attr('tabindex', '-1')
          .each(function() {
            if (!$(this).aria('controls')) {
              // <a role="tab"> の aria-controls 属性は省略可能
              // 省略された場合は href 属性を使用する
              $(this).aria('controls', $(this).attr('href').replace(/^#/, ''));
            }
            // 一度すべてのタブパネルを隠す
            $(this).aria('controls').aria('hidden', true).hide();
          });

        // <div id="*-tabbox"> の data-options 属性で表示オプションを設定可能
        //  * random: 最初に表示するタブをランダムに選択
        //  * rotation: 表示するタブを一定間隔で切り換え
        //  * autofocus: タブをクリックしなくてもマウスオーバーだけで切り換え
        var options = [];
        if (tabbox.data('options')) {
          options = tabbox.data('options').split(' ');
        }

        // 初期表示タブ
        var current_tab;
        var test_tab = $('[aria-controls="' + location.hash.substr(1) + '"]');
        if (location.hash && test_tab.length) {
          // ハッシュ値がタブの ID と一致していれば選択
          current_tab = test_tab;
        } else if (tablist.aria('activedescendant')) {
          // aria-activedescendant 属性で指定されていたら選択
          current_tab = tablist.aria('activedescendant');
        } else {
          // <ul role="tablist"> の aria-activedescendant 属性は省略可能
          // 省略された場合は、ランダムもしくは左端のタブを最初に表示
          if ($.inArray('random', options) > -1) {
            current_tab = tabs.eq(Math.floor(Math.random() * tabs.length));
          } else {
            current_tab = tabs.first();
          }
          tablist.aria('activedescendant', current_tab);
        }
        current_tab.aria('selected', true).attr('tabindex', '0');
        current_tab.aria('controls').show();

        function switch_tab(new_tab) {
          current_tab.aria('selected', false).attr('tabindex', '-1');
          var current_tabpanel = current_tab.aria('controls').aria('hidden', true);
          new_tab.aria('selected', true).attr('tabindex', '0');
          var new_tabpanel = new_tab.aria('controls').aria('hidden', false);
          tablist.aria('activedescendant', new_tab);
          current_tab = new_tab;
          current_tabpanel.stop(true, true).fadeOut('slow', function() {
            new_tabpanel.fadeIn('slow', function() {
              // Windows Vista IE: ClearType fonts antialiasing bug fix
              new_tabpanel.removeAttr('style');
            });
          });
        }

        if ($.inArray('rotation', options) > -1) {
          var rotator = {
            _timer: undefined,
            interval: 4000,
            start: function () {
              this._timer = window.setInterval(this._func, this.interval);
            },
            stop: function () {
              window.clearInterval(this._timer);
            },
            _func: function () {
              var index = tabs.index(current_tab);
              var new_tab = (index < tabs.length - 1) ? tabs.eq(index + 1) : tabs.first();
              switch_tab(new_tab);
            },
          };

          rotator.start();

          tabbox.mouseover(function() {
            rotator.stop();
          });

          tabbox.mouseout(function() {
            rotator.start();
          });
        }

        if ($.inArray('autofocus', options) > -1) {
          tabs.mouseover(function() {
            this.focus(); // $(this).focus() とすると動作しない場合がある
          });
        }

        tabs.click(function() {
          return !$(this).attr('href').match(/^#/);
        }).keydown(function(event) {
          var target, index = tabs.index($(this));
          switch (event.keyCode) {
            case 36: // Home
              target = tabs.first(); break;
            case 35: // End
              target = tabs.last(); break;
            case 38: // Up
            case 37: // Left
              target = (index > 0) ? tabs.eq(index - 1) : tabs.last(); break;
            case 39: // Right
            case 40: // Down
              target = (index < tabs.length - 1) ? tabs.eq(index + 1) : tabs.first(); break;
          }
          if (target) {
            target.get(0).focus(); // target.focus() とすると動作しない場合がある
            return false;
          }
          return true;
        }).focus(function() {
          if ($(this).aria('selected')) {
            return false;
          }
          switch_tab($(this));
        });

        $(window).on('hashchange', function() {
          var test_tab = $('[aria-controls="' + location.hash.substr(1) + '"]');
          if (location.hash && test_tab.length) {
            switch_tab(test_tab);
          }
        });
      });
    }
  },

  /*
   * アクセス解析関連
   * 複数ツールへの対応を考慮する
   */
  analytics:
  {
    // サイト固有の設定
    path: '',
    ga_property_id: 'UA-6459738-1',

    init: function() {
      if(navigator.doNotTrack == "1"){
        delete mj.analytics;
        return;
      }

      // https://developers.google.com/analytics/devguides/collection/analyticsjs/
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

      ga('create', this.ga_property_id, 'auto');

      // 独自のトラッキング URL
      var link = $('link[rel="tracking"]');
      if (link.length) {
        this.path = link.attr('href');
      }

      this.sendGleaningEvent();

      // イベントトラッキング
      var meta = $('meta[name="event-tracking"]');
      if (meta.length) {
        this.track_event(meta.attr('content').split('; '));
      }

      // リンクのトラッキングを設定
      $('a[href]').click(function(event) {
        mj.analytics.track_link($(this));
      });

      // 現在のページを記録
      this.log();
    },

    // target is jQuery
    track_link: function(target) {
      if (!target.context.hostname) {
        return;
      }

      // リンクにトラッキングコードが設定済みの場合はそれを優先する
      if (target.attr('onclick') &&
          target.attr('onclick').toString().indexOf('mj.analytics.log') !== -1) {
        return;
      }

      // イベントトラッキング
      var tracking_data = target.data('event-tracking');
      if (tracking_data) {
        this.track_event(tracking_data.split('; '));
        return;
      }

      var internal_link = (target.context.hostname === document.domain);
      var path = target.context.pathname;

      // ブラウザーによっては pathname の先頭にスラッシュが付かない
      if (path.substr(0, 1) !== '/') {
        path = '/' + path;
      }

      // 外部リンクのトラッキング
      if (!internal_link) {
        this.log('/oblink/' + target.context.href);
        return;
      }

      var re = new RegExp('\.(png|gif|jpg|jpeg|tif|tiff|ai|eps|psd|svg|pdf|doc|docx|xls|xlsx|ppt|pptx|zip|xpi|jar|exe)$', 'i');
      var file_link = (path !== undefined && re.test(path));

      // ファイルダウンロードのトラッキング
      if (internal_link && file_link) {
        this.log(path);
        return;
      }
    },

    track_event: function(arg) {
      ga('send', 'event', arg[0], arg[1], arg[2], arg[3]);
    },

    log: function() {
      // パスの設定
      var url;
      if (arguments.length === 1) {
        // クリックイベントなどによるカスタム URL
        url = arguments[0];
      } else if (this.path) {
        // ページ内で上書きされた URL (例: ダウンロードページ)
        url = this.path;
      } else {
        // 通常のページビュー
        url = location.pathname + location.search;
      }

      // Google Analytics の文字化け問題を回避 (unescape の代わりに decodeURI を使う)
      if (typeof decodeURI === 'function') {
        url = decodeURI(url);
      }

      // Google Analytics
      ga('send', 'pageview', url);
    },

    sendGleaningEvent: function(){
      var createKeyValuePair = function(pairs){
        var result = {};
        for(var i = 0; i < pairs.length; i++){
          var pair = pairs[i].split("=");
          result[pair[0]] = pair[1];
        }
        return result;
      };
      var params = function(urlString){
        var index = urlString.indexOf("?");
        if(index > 0){
          return createKeyValuePair(urlString.substr(index + 1).split("&"));
        }
        return {};
      };
      var extractUrl = function(src){
        var table = params(src);
        return (table.inc || "").replace(/-/g, "/");
      };

      var origin = extractUrl(window.location.href);
      if(origin != null && origin.length > 0){
        this.track_event(["gleanings", "redirected", origin]);
      }
    }
  }
};

mj.onbeforeload();
