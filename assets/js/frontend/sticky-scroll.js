jQuery(function ($) {
  $('.tta-stick-on-scroll').each(function () {
    var headerH = $('header').first().outerHeight() || $('.site-header').first().outerHeight() || 0;
    var adminH  = $('#wpadminbar').length ? $('#wpadminbar').outerHeight() : 0;
    var OFFSET  = headerH + adminH + 20;
    var $el = $(this);
    var $ph = $('<div>').height($el.outerHeight()).insertBefore($el).hide();
    var orig = {
      position: $el.css('position'),
      top: $el.css('top'),
      width: $el.outerWidth()
    };
    var start = $el.offset().top - OFFSET;

    function compute() {
      headerH = $('header').first().outerHeight() || $('.site-header').first().outerHeight() || 0;
      adminH  = $('#wpadminbar').length ? $('#wpadminbar').outerHeight() : 0;
      OFFSET  = headerH + adminH + 20;
      start = $el.offset().top - OFFSET;
      orig.width = $el.outerWidth();
      $ph.height($el.outerHeight());
    }

    function onScroll() {
      var top = $(window).scrollTop();
      if (top >= start) {
        if (!$el.hasClass('tta-fixed')) {
          $el.addClass('tta-fixed').css({ position: 'fixed', zIndex: 999, top: OFFSET, width: orig.width, left: $ph.offset().left });
          $ph.show();
        } else {
          $el.css({ left: $ph.offset().left, top: OFFSET });
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
    $el.find('img').on('load', function(){
      compute();
      onScroll();
    });
  });
});
