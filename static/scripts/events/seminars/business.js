jQuery(function($) {
  var nav = $("#fixed-menu");
  var body = $("body");
  var offset = nav.offset();
  var $window = $(window);
  $window.scroll(function () {
    if($window.scrollTop() > offset.top) {
      body.addClass("fixed");
    } else {
      body.removeClass("fixed");
    }
  });
});
