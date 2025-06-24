jQuery(function($){
  var OFFSET = 20;
  $('.tta-stick-on-scroll').each(function(){
    var $el = $(this);
    var $ph = $('<div>').height($el.outerHeight()).insertBefore($el).hide();
    var orig = {
      position: $el.css('position'),
      top: $el.css('top'),
      width: $el.outerWidth()
    };
    var start = $el.offset().top - OFFSET;
    $(window).on('scroll.sticky resize.sticky', function(){
      var top = $(window).scrollTop();
      if ( top >= start ) {
        if (!$el.hasClass('tta-fixed')) {
          $ph.show();
          $el.css({position:'fixed', top: OFFSET, width: orig.width});
          $el.addClass('tta-fixed');
        }
      } else {
        if ($el.hasClass('tta-fixed')) {
          $el.removeClass('tta-fixed');
          $el.css(orig);
          $ph.hide();
        }
      }
    });
  });
});
