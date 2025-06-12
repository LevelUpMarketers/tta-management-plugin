jQuery(function($){
  $('.tta-accordion-toggle').on('click', function(){
    var $btn  = $(this),
        $cont = $btn.siblings('.tta-accordion-content'),
        readMore = 'Read more',
        showLess = 'Show less';

    if ( $cont.hasClass('expanded') ) {
      $cont.removeClass('expanded');
      $btn.text( readMore );
    } else {
      $cont.addClass('expanded');
      $btn.text( showLess );
    }
  });
});

jQuery(function($){
  $('.tta-accordion-toggle-image-gallery').on('click', function(){
    var $btn  = $(this),
        $cont = $btn.siblings('.tta-accordion-content'),
        readMore = 'View Gallery',
        showLess = 'Show less';

    if ( $cont.hasClass('expanded') ) {
      $cont.removeClass('expanded');
      $btn.text( readMore );
    } else {
      $cont.addClass('expanded');
      $btn.text( showLess );
    }
  });
});


(function($){
  $(function(){
    // adjust this selector to match your fixed header
    var headerSelector = '.site-header, .tta-header'; 
    var headerHeight   = $(headerSelector).first().outerHeight() || 0;
    var extraOffset    = 200; // extra space below header

    $('a[href="#tta-event-buy"]').on('click', function(e){
      e.preventDefault();
      var target = $('#tta-event-buy');
      if ( target.length ) {
        $('html, body').animate({
          scrollTop: target.offset().top - headerHeight - extraOffset
        }, 600);
      }
    });
  });
})(jQuery);
