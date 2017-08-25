/* jQuery WAI-ARIA Enhancement Plugin | Dependencies: jQuery 1.3+ */
(function($) {
  // 独自の aria() を追加
  $.fn.aria = function (prop, value) {
    if (value === undefined) { // get
      value = this.attr('aria-' + prop);
      if (!value) return undefined;
      if (value === 'true') return true;
      if (value === 'false') return false;
      if (value === 'undefined') return undefined;
      if (!isNaN(value)) return parseFloat(value);
      value = value.split(' ');
      if ($('#' + value[0]).length) return $($.map(value, function(e) { return '#' + e; }).join(',')); // ID reference or ID reference list
      return (value.length > 1) ? value : value[0]; // token list or token/string
    } else { // set
      if (typeof value === 'boolean') value = value.toString();
      if (value === undefined) value = 'undefined';
      if ($.isArray(value)) value = value.join(' ');
      if (typeof value === 'object' && value.jquery) value = $.map(value, function(e) { return e.id; }).join(' ');
      return this.attr('aria-' + prop, value);
    }
  };

  // 標準の show() をオーバーライド 
  $.fn._show = $.fn.show;
  $.fn.show = function() {
    $.fn._show.apply(this, arguments);
    return this.aria('hidden', false);
  };

  // 標準の hide() をオーバーライド 
  $.fn._hide = $.fn.hide;
  $.fn.hide = function() {
    $.fn._hide.apply(this, arguments);
    return this.aria('hidden', true);
  };

  // 標準の click() をオーバーライド
  $.fn.click = function(handler) {
    // 通常の処理
    this.on('click', handler);

    // Enter/Return キー押下時にもクリックイベントを発生させる
    // ボタン要素は Space も対象とする
    this.on('keydown', function(event) {
      if (event.keyCode === 13 ||
          event.keyCode === 32 &&
          $(this).attr('role') === 'button') {
        $(this).trigger('click');
      }
      return true;
    });

    return this;
  };
})(jQuery);
