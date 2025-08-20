jQuery(function($){
  function handleResult($resp, $spin, $btn, res){
    $spin.fadeOut(200);
    $btn.prop('disabled', false);
    $resp.removeClass('updated error');
    if(res.success){
      window.location.reload();
    } else {
      $resp.addClass('error').text(res.data.message || 'Error');
    }
  }

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
    var $form = $(this);
    var $btn  = $form.find('button');
    var $spin = $form.find('.tta-admin-progress-spinner-svg');
    var $resp = $('#tta-register-response');
    $resp.removeClass('updated error').text('');

    var email       = $form.find('[name="email"]').val();
    var emailVerify = $form.find('[name="email_verify"]').val();
    var pass        = $form.find('[name="password"]').val();
    var passVerify  = $form.find('[name="password_verify"]').val();

    if(email !== emailVerify){
      $resp.addClass('error').text('Email addresses do not match.');
      return;
    }
    if(pass !== passVerify){
      $resp.addClass('error').text('Passwords do not match.');
      return;
    }
    if(!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(pass)){
      $resp.addClass('error').text( tta_ajax.password_requirements_msg );
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
    }, function(res){ handleResult($resp, $spin, $btn, res); }, 'json').fail(function(){
      handleResult($resp, $spin, $btn, { success:false, data:{ message:'Request failed.' } });
    });
  });
});
