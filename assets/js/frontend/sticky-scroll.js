jQuery(function($){
  function updateOffset(){
    var headerH = $('header').first().outerHeight() || $('.site-header').first().outerHeight() || 0;
    var adminH  = $('#wpadminbar').length ? $('#wpadminbar').outerHeight() : 0;
    var offset  = headerH + adminH + 20;
    $('.tta-stick-on-scroll').each(function(){
      $(this).css('top', offset + 'px');
    });
  }
  $(window).on('resize.sticky', updateOffset);
  updateOffset();
});
