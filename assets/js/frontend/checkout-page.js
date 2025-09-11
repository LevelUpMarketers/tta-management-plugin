jQuery(function($){
  var $form = $('#tta-checkout-form');
  if(!$form.length) return;
  var $btn  = $form.find('button[name="tta_do_checkout"]');
  var $spin = $form.find('.tta-admin-progress-spinner-svg');
  var $resp = $('#tta-checkout-response');

  function setProcessing(msg){
    $btn.prop('disabled', true);
    if(!$spin.is(':visible')){
      $spin.show().css({opacity:0}).fadeTo(200,1);
    }
    if(msg){
      $resp.text(msg).removeClass('error');
    }
  }

  function clearProcessing(){
    $spin.fadeOut(200);
    $btn.prop('disabled', false);
  }

  function pollStatus(key){
    $.post(tta_checkout.ajax_url, {
      action: 'tta_checkout_status',
      nonce: tta_checkout.nonce,
      checkout_key: key
    }, function(res){
      if(res.success && res.data && res.data.transaction_id){
        sessionStorage.removeItem('ttaCheckout');
        $resp.removeClass('error').addClass('updated').text('Order completed. Please check your email for a receipt.');
        clearProcessing();
      } else {
        setTimeout(function(){ pollStatus(key); }, 3000);
      }
    }, 'json');
  }

  var pending = sessionStorage.getItem('ttaCheckout');
  if(pending){
    try { pending = JSON.parse(pending); } catch(e){ pending = null; }
  }
  if(pending && pending.checkout_key){
    setProcessing('Processing…');
    pollStatus(pending.checkout_key);
  }
});
