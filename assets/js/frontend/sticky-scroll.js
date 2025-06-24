jQuery(function($){
  function update($el){
    var headerH = $('header').first().outerHeight() || $('.site-header').first().outerHeight() || 0;
    var adminH  = $('#wpadminbar').length ? $('#wpadminbar').outerHeight() : 0;
    $el.css({
      position: 'sticky',
      top: headerH + adminH + 20
    });
  }
  $('.tta-stick-on-scroll').each(function(){
    var $el = $(this);
    update($el);
    $(window).on('resize.sticky', function(){ update($el); });
  });
});
