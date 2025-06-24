jQuery(function ($) {
  var OFFSET = 20;
  $('.tta-stick-on-scroll').each(function () {
    var $el = $(this);
    var $ph = $('<div>').height($el.outerHeight()).insertBefore($el).hide();
    var orig = {
      position: $el.css('position'),
      top: $el.css('top'),
      width: $el.outerWidth()
    };
    var start = $el.offset().top - OFFSET;

    function compute() {
      start = $el.offset().top - OFFSET;
      orig.width = $el.outerWidth();
      $ph.height($el.outerHeight());
    }

    function onScroll() {
      var top = $(window).scrollTop();
      if (top >= start) {
        if (!$el.hasClass('tta-fixed')) {
          $el.addClass('tta-fixed').css({ position: 'fixed', top: OFFSET, width: orig.width });
          $ph.show();
        }
      } else if ($el.hasClass('tta-fixed')) {
        $el.removeClass('tta-fixed').css(orig);
        $ph.hide();
      }
    }

    $(window).on('scroll.sticky', onScroll);
    $(window).on('resize.sticky', function () {
      compute();
      onScroll();
    });

    compute();
    onScroll();
  });
});
