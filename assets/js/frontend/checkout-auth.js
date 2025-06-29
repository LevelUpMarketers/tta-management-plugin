jQuery(function($){
  function handleResult(res){
    var $resp = $('#tta-auth-response');
    $resp.removeClass('updated error');
    if(res.success){
      window.location.reload();
    } else {
      $resp.addClass('error').text(res.data.message || 'Error');
    }
  }

  $('#tta-login-form').on('submit', function(e){
    e.preventDefault();
    $.post( tta_ajax.ajax_url, {
      action: 'tta_login',
      nonce: tta_ajax.nonce,
      username: $(this).find('[name="log"]').val(),
      password: $(this).find('[name="pwd"]').val()
    }, handleResult, 'json').fail(function(){
      $('#tta-auth-response').addClass('error').text('Request failed.');
    });
  });

  $('#tta-register-form').on('submit', function(e){
    e.preventDefault();
    $.post( tta_ajax.ajax_url, {
      action: 'tta_register',
      nonce: tta_ajax.nonce,
      first_name: $(this).find('[name="first_name"]').val(),
      last_name:  $(this).find('[name="last_name"]').val(),
      email:      $(this).find('[name="email"]').val(),
      password:   $(this).find('[name="password"]').val()
    }, handleResult, 'json').fail(function(){
      $('#tta-auth-response').addClass('error').text('Request failed.');
    });
  });
});
