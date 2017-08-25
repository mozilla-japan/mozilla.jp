(function() {
'use strict';
var $desc = $('#main-feature');

function setSectionSize() {
  $desc.css('height', $(window).height());
}

//$(window).on('resize', setSectionSize);
//setSectionSize();


}).call(this);