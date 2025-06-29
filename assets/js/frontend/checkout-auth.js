jQuery(function($){
  function restoreFields(){
    var raw = sessionStorage.getItem('tta_checkout_fields');
    if(!raw) return;
    sessionStorage.removeItem('tta_checkout_fields');
    try{
      var data = JSON.parse(raw);
      $.each(data, function(name,val){
        var $el = $('[name="'+name+'"]');
        if(!$el.length) return;
        if($el.attr('type') === 'checkbox'){
          $el.prop('checked', !!val);
        } else {
          $el.val(val);
        }
      });
    }catch(e){
      console.error(e);
    }
  }

  function saveFields(){
    var data = {};
    $('#tta-checkout-form').find('input,select,textarea').each(function(){
      var $el = $(this), name = $el.attr('name');
      if(!name) return;
      if($el.attr('type') === 'checkbox'){
        data[name] = $el.prop('checked');
      } else {
        data[name] = $el.val();
      }
    });
    sessionStorage.setItem('tta_checkout_fields', JSON.stringify(data));
  }

  restoreFields();
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
    saveFields();
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
    saveFields();
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
