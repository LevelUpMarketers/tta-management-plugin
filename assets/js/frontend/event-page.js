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
  $('.tta-accordion-toggle-image-gallery')
    .each(function(){
      var txt = $(this).text().trim();
      $(this).data('readMore', txt || 'View Gallery');
    })
    .on('click', function(){
      var $btn  = $(this),
          $cont = $btn.siblings('.tta-accordion-content'),
          readMore = $btn.data('readMore') || 'View Gallery',
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

  window.ttaShowNotice = showNotice;

  function enforceLimit(){
    var $input    = $(this);
    var limit     = parseInt($input.data('limit'),10) || 2;
    var purchased = parseInt($input.data('purchased'),10) || 0;
    var allowed   = Math.max(0, limit - purchased);
    var val       = parseInt($input.val(),10) || 0;
    if(val > allowed){
      $input.val(allowed);
      var msg = (purchased >= limit)
        ? tta_event.prev_limit_msg.replace('%d', limit)
        : tta_event.limit_msg.replace('%d', limit);
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

  jQuery(function($){
    $('#tta-login-message').on('click', '.tta-show-register', function(e){
      e.preventDefault();
      var $link = $(this);
      $link.addClass('tta-button-disabled').attr('aria-disabled', 'true').attr('tabindex', '-1');
      $('#tta-login-wrap').fadeOut(200, function(){
        $('#tta-register-form').fadeIn(200);
      });
    });

    $('#tta-register-form').on('click', '.tta-cancel-register', function(e){
      e.preventDefault();
      $('#tta-register-form').fadeOut(200, function(){
        $('#tta-login-wrap').fadeIn(200);
      });
      var $link = $('#tta-login-message .tta-show-register');
      $link.removeClass('tta-button-disabled').removeAttr('aria-disabled tabindex');
    });

    $('#tta-register-form').on('submit', function(e){
      e.preventDefault();
      e.stopPropagation();
      var $form = $(this),
          $btn  = $form.find('button'),
          $spin = $form.find('.tta-admin-progress-spinner-svg'),
        $resp = $('#tta-register-response');
    $resp.removeClass('updated error').text('');

    var email       = $form.find('[name="email"]').val();
    var emailVerify = $form.find('[name="email_verify"]').val();
    var pass        = $form.find('[name="password"]').val();
    var passVerify  = $form.find('[name="password_verify"]').val();

    if(email !== emailVerify){
      $resp.addClass('error').text( tta_event.email_mismatch_msg );
      return;
    }

    if(pass !== passVerify){
      $resp.addClass('error').text( tta_event.password_mismatch_msg );
      return;
    }

    if(!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(pass)){
      $resp.addClass('error').text( tta_event.password_requirements_msg );
      return;
    }

    $btn.prop('disabled', true);
    $spin.show().css({opacity:0}).fadeTo(200,1);

    $.post( tta_ajax.ajax_url, {
      action: 'tta_register',
      nonce: tta_ajax.nonce,
      first_name: $form.find('[name="first_name"]').val(),
      last_name:  $form.find('[name="last_name"]').val(),
      email:      email,
      email_verify: emailVerify,
      password:   pass,
      password_verify: passVerify
    }, function(res){
      $spin.fadeOut(200);
      if(res.success){
        var count = 5;
        (function update(){
          $resp.removeClass('error').addClass('updated')
               .text( tta_event.account_created_msg.replace('%d', count) );
          if(count-- > 0){
            setTimeout(update, 1000);
          } else {
            window.location.reload();
          }
        })();
      } else {
        $btn.prop('disabled', false);
        $resp.addClass('error').text(res.data.message || 'Error');
      }
    }, 'json').fail(function(){
      $spin.fadeOut(200);
      $btn.prop('disabled', false);
      $resp.addClass('error').text( tta_event.request_failed_msg );
    });
  });
});
