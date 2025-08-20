(function($){
  function init(){
    var isWide = window.matchMedia('(min-width: 769px)').matches;
    var $sidebars = $('.tta-events-right, .tta-event-right');

    // Calculate offsets once per run rather than inside each sidebar loop.
    var headerHeight = $('.site-header, .tta-header').first().outerHeight() || 0;
    var adminBarHeight = $('body').hasClass('admin-bar') ? $('#wpadminbar').outerHeight() || 0 : 0;
    var extraOffset = 148;

    $sidebars.each(function(){
      var $el = $(this);
      var inst = $el.data('stickyInstance');

      if(!isWide){
        if(inst){
          inst.destroy();
          $el.removeData('stickyInstance');
        }
        return;
      }

      if(!inst){
        inst = new StickySidebar(this, {
          innerWrapperSelector: '.tta-events-ad',
          topSpacing: headerHeight + adminBarHeight + extraOffset,
          bottomSpacing: 0,
          minWidth: 769,
          resizeSensor: true
        });
        $el.data('stickyInstance', inst);
      } else {
        inst.updateSticky();
      }
    });
  }
  $(init);
  $(window).on('resize', init);
})(jQuery);
