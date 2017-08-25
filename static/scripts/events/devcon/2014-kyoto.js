// 講演者プロフィールのオーバーレイ表示
$(document).ready(function() {
  if (bowser.msie && parseFloat(bowser.version) < 9) {
    return false;
  }

  var _overlay = $('<div id="speaker-overlay" tabindex="0" aria-hidden="true"></div>')
    .appendTo('body')
    .click(function() { hide_overlay(); });
  var _frame = $('<aside class="frame"></aside>').appendTo(_overlay);
  var _close = $('<div class="close" role="button" aria-label="閉じる">×</div>')
    .appendTo(_frame);
  var _content = $('<div class="content"></aside>').appendTo(_frame);
  var _launcher;

  function show_overlay() {
    _launcher.aria('haspopup', true).aria('owns', 'speaker-overlay');
    _content.html($(_launcher.attr('href').match(/#.+/)[0]).html());
    _overlay.aria('hidden', false).focus();
  }
  function hide_overlay() {
    _launcher.removeAttr('aria-haspopup').removeAttr('aria-owns').focus();
    _content.empty();
    _overlay.aria('hidden', true);
  }

  $('#sec-speakers').hide();
  $('#sec-program .speakers a')
    .attr('role', 'button')
    .click(function() {
      _launcher = $(this);
      show_overlay();
      return false;
    });
  $(document).keydown(function(event) {
    if (event.which === 27 && _overlay.aria('hidden') === false) {
      hide_overlay();
    }
  });
});
