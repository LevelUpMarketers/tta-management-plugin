(function($){
  function showMessage(msg, isError){
    var $resp = $('#tta-checkout-response');
    $resp.text(msg);
    if(isError){
      $resp.addClass('error');
    } else {
      $resp.removeClass('error');
    }
  }
  $(function(){
    var cfg = window.TTA_ACCEPT || {};
    var $form = $('form').has('button[name="tta_do_checkout"]');
    if(!$form.length){ return; }

    $form.on('submit', function(e){
      var submitter = e.originalEvent && e.originalEvent.submitter;
      if(submitter && $(submitter).attr('name') !== 'tta_do_checkout'){
        return;
      }
      e.preventDefault();
      var $btn = $form.find('button[name="tta_do_checkout"]');
      $btn.prop('disabled', true);
      $('.tta-admin-progress-spinner-svg').show();
      showMessage('Processing payment...');

      if(typeof Accept === 'undefined'){
        showMessage('Payment library not loaded. Please refresh and try again.', true);
        $('.tta-admin-progress-spinner-svg').hide();
        $btn.prop('disabled', false);
        return;
      }

      var cardNumber = $.trim($form.find('[name="card_number"]').val());
      var exp = $.trim($form.find('[name="card_exp"]').val());
      var cvc = $.trim($form.find('[name="card_cvc"]').val());
      exp = exp.replace(/\s+/g,'');
      if(/^[0-9]{4}$/.test(exp)){
        exp = exp.substring(0,2)+'/'+exp.substring(2);
      }
      var parts = exp.split(/[\/\-]/);
      var month = parts[0];
      var year = parts[1];
      if(year && year.length === 2){ year = '20'+year; }

      Accept.dispatchData({
        authData:{ apiLoginID: cfg.loginId, clientKey: cfg.clientKey },
        cardData:{ cardNumber: cardNumber, month: month, year: year, cardCode: cvc }
      }, function(response){
        if(response.messages.resultCode === 'Error'){
          console.warn('Accept.js error:', response.messages.message[0].text);
          showMessage(response.messages.message[0].text || 'Payment error', true);
          $('.tta-admin-progress-spinner-svg').hide();
          $btn.prop('disabled', false);
          return;
        }
        var opaque = response.opaqueData;
        var payload = {
          action: 'tta_process_payment',
          _wpnonce: cfg.nonce,
          amount: $form.find('[name="tta_amount"]').val() || $btn.data('amount') || $form.data('amount') || '25.00',
          billing: {
            first_name: $form.find('[name="billing_first_name"]').val(),
            last_name:  $form.find('[name="billing_last_name"]').val(),
            email:      $form.find('[name="billing_email"]').val(),
            address:    $form.find('[name="billing_street"]').val(),
            address2:   $form.find('[name="billing_street_2"]').val(),
            city:       $form.find('[name="billing_city"]').val(),
            state:      $form.find('[name="billing_state"]').val(),
            zip:        $form.find('[name="billing_zip"]').val(),
            country:    'USA'
          },
          opaqueData: { dataDescriptor: opaque.dataDescriptor, dataValue: opaque.dataValue }
        };
        $.ajax({
          url: cfg.ajaxUrl,
          method: 'POST',
          data: JSON.stringify(payload),
          contentType: 'application/json',
          dataType: 'json'
        }).done(function(res){
          if(res.success){
            var last4 = cardNumber.slice(-4);
            if(typeof window.ttaFinalizeOrder === 'function'){
              window.ttaFinalizeOrder(res.transaction_id, last4);
            } else {
              $('.tta-admin-progress-spinner-svg').hide();
              $btn.prop('disabled', false);
              showMessage('Payment processed but checkout handler missing', true);
            }
            $form.find('[name="card_number"],[name="card_exp"],[name="card_cvc"]').val('');
          } else {
            $('.tta-admin-progress-spinner-svg').hide();
            $btn.prop('disabled', false);
            showMessage(res.error || 'Payment failed', true);
          }
        }).fail(function(){
          $('.tta-admin-progress-spinner-svg').hide();
          $btn.prop('disabled', false);
          showMessage('Request failed', true);
        });
      });
    });
  });
})(jQuery);
