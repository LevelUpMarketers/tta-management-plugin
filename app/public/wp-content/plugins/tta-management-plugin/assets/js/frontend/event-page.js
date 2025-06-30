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
  var headerSelector = '.site-header, .tta-header';
  var extraOffset    = 200; // extra space below header

  function getHeaderHeight(){
    return $(headerSelector).first().outerHeight() || 0;
  }

  $(function(){
    $('a[href="#tta-event-buy"]').on('click', function(e){
      e.preventDefault();
      var target = $('#tta-event-buy');
      if ( target.length ) {
        $('html, body').animate({
          scrollTop: target.offset().top - getHeaderHeight() - extraOffset
        }, 600);
      }
    });

    $('.tta-scroll-login').on('click', function(e){
      e.preventDefault();
      var target = $('#tta-login-message');
      $('.tta-message-center .tta-accordion-content').addClass('expanded');
      if ( target.length ) {
        $('html, body').animate({
          scrollTop: target.offset().top - getHeaderHeight() - extraOffset
        }, 600);
      }
    });
  });
})(jQuery);

jQuery(function($){
  function showNotice($input, msg){
    var $wrap = $input.closest('.tta-ticket-quantity');
    var $n = $wrap.find('.tta-ticket-notice');
    if(!$n.length) return;
    $n.text(msg).fadeIn(200);
    clearTimeout($n.data('timer'));
    $n.data('timer', setTimeout(function(){ $n.fadeOut(200); }, 4000));
  }

  function enforceLimit(){
    var $input = $(this);
    var total = 0;
    $('.tta-qty-input').each(function(){ total += parseInt($(this).val(),10)||0; });
    if(total > 2){
      var others = total - parseInt($input.val(),10)||0;
      var allowed = Math.max(0, 2 - others);
      $input.val(allowed);
      var msg = $('.tta-qty-input').length > 1 ? tta_event.multi_limit_msg : tta_event.single_limit_msg;
      showNotice($input, msg);
    }
  }

$(document).on('change', '.tta-qty-input', enforceLimit);
});

jQuery(function($){
  $('.tta-accordion-toggle-login').on('click', function(){
    var $btn  = $(this),
        $cont = $btn.closest('.tta-accordion').find('.tta-accordion-content');

    if ( $cont.hasClass('expanded') ) {
      $cont.removeClass('expanded');
    } else {
      $cont.addClass('expanded');
    }
  });
});
