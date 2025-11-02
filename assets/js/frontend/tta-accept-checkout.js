(function($){
  function showMessage(msg, isError){
    var $resp = $('#tta-checkout-response');
    var message = msg || '';
    if(/<[a-z][\s\S]*>/i.test(message)){
      $resp.html(message);
    }else{
      $resp.text(message);
    }
    if(isError){
      $resp.addClass('error').removeClass('updated');
    }else{
      $resp.removeClass('error');
    }
  }

  $(function(){
    var cfg = window.TTA_ACCEPT || {};
    var state = window.tta_checkout || {};
    var debugEnabled = !!state.debug || !!cfg.debug;
    var debugPrefix = '[TTA checkout]';
    function debugLog(){
      if(!debugEnabled || typeof console === 'undefined'){ return; }
      var fn = console.log || console.info;
      if(!fn){ return; }
      var args = Array.prototype.slice.call(arguments);
      args.unshift(debugPrefix);
      fn.apply(console, args);
    }
    function debugWarn(){
      if(!debugEnabled || typeof console === 'undefined'){ return; }
      var fn = console.warn || console.log;
      if(!fn){ return; }
      var args = Array.prototype.slice.call(arguments);
      args.unshift(debugPrefix);
      fn.apply(console, args);
    }
    function debugError(){
      if(!debugEnabled || typeof console === 'undefined'){ return; }
      var fn = console.error || console.log;
      if(!fn){ return; }
      var args = Array.prototype.slice.call(arguments);
      args.unshift(debugPrefix);
      fn.apply(console, args);
    }

    var $form = $('form').has('button[name="tta_do_checkout"]');
    if(!$form.length) return;
    var $btn  = $form.find('button[name="tta_do_checkout"]');
    var $spin = $form.find('.tta-admin-progress-spinner-svg');
    var $resp = $('#tta-checkout-response');
    var encryptionFailedMessage = ((window.tta_checkout && window.tta_checkout.encryption_failed_html) || 'Encryption of your payment information failed! Please try again later. If you\'re still having trouble, please contact us using the form on our Contact Page.');

    debugLog('Checkout script initialised', {
      checkoutKey: state.checkout_key || null,
      ajaxUrl: state.ajax_url || null,
      mode: cfg.mode || 'unknown',
      debugEnabled: debugEnabled
    });

    $btn.on('click', function(){
      debugLog('Place Order button clicked', {
        disabled: $btn.prop('disabled'),
        spinnerVisible: $spin.is(':visible')
      });
    });

    function encryptionFailed(debug){
      showMessage(encryptionFailedMessage, true);
      $spin.fadeOut(200);
      $btn.prop('disabled', false);
      sessionStorage.removeItem('ttaCheckout');
      if(debug){
        debugError('Accept.js encryption failed', debug);
      }
    }

    function finalizeResponse(res){
      var data = res && res.data ? res.data : res;
      debugLog('Received checkout response', res);
      if(data && data.debug){
        debugLog('Checkout response debug bundle', data.debug);
      }
      $spin.fadeOut(200);
      $btn.prop('disabled', false);
      if(res && res.success){
        var html = '';
        if(data.membership){
          if(data.membership === 'reentry'){
            html += '<p>Thanks for purchasing your Re-Entry Ticket! You can once again register for events. An email will be sent to ' + tta_checkout.user_email + ' for your records. Thanks again, and welcome back!</p>';
          } else {
            var amt = data.membership === 'premium' ? tta_checkout.premium_price : tta_checkout.basic_price;
            var levelName = data.membership === 'premium' ? 'Premium' : 'Standard';
            html += '<p>Thanks for becoming a ' + levelName + ' Member! ' + "There's nothing else for you to do - you'll be automatically billed $"+amt+" once monthly, and can cancel any time on your " + '<a href="'+ tta_checkout.dashboard_url +'">Member Dashboard</a>. ' + 'An email will be sent to ' + tta_checkout.user_email + ' with your Membership Details. Thanks again, and enjoy your Membership perks!</p>';
            if(data.membership === 'premium'){
              html += '<p>Did you know? You can earn a free event and other perks by referring friends and family! Let us know who you\'ve referred at <a href="mailto:sam@tryingtoadultrva.com">sam@tryingtoadultrva.com</a> and we\'ll reach out.</p>';
            }
          }
        }
        if(data.has_tickets){
          var intro = data.membership ? 'Also, thanks for signing up for our upcoming event!' : 'Thanks for signing up!';
          html += '<p>'+intro+' A receipt has been emailed to each of the email addresses below. Please keep these emails to present to the Event Host or Volunteer upon arrival.</p><ul>';
          var emails = Array.isArray(data.emails) ? data.emails : (data.emails ? [data.emails] : []);
          var unique = {};
          emails.forEach(function(e){
            if(!e){return;}
            var norm = String(e).trim().toLowerCase();
            if(!unique[norm]) unique[norm] = e.trim();
          });
          Object.values(unique).forEach(function(e){
            html += '<li>' + $('<div>').text(e).html() + '</li>';
          });
          html += '</ul>';
        }
        $resp.removeClass('error').addClass('updated').html(html);
        sessionStorage.removeItem('ttaCheckout');
        debugLog('Checkout completed successfully', {
          transactionId: data.transaction_id || null,
          membership: data.membership || null,
          hasTickets: !!data.has_tickets,
          emailCount: Array.isArray(data.emails) ? data.emails.length : 0
        });
      } else {
        $resp.removeClass('updated').addClass('error').text(data.message || 'Error processing payment');
        sessionStorage.removeItem('ttaCheckout');
        debugWarn('Checkout failed', {
          message: data && data.message ? data.message : null,
          transactionId: data && data.transaction_id ? data.transaction_id : null
        });
      }
    }

    function sendCheckout(token, billing, cardNumber){
      var dataArr = $form.serializeArray().filter(function(field){
        return ['card_number','card_exp','card_cvc'].indexOf(field.name) === -1 && field.name.indexOf('billing_') !== 0;
      });
      var formData = new FormData();
      dataArr.forEach(function(f){ formData.append(f.name, f.value); });
      formData.append('action','tta_checkout');
      formData.append('nonce', tta_checkout.nonce);
      formData.append('checkout_key', tta_checkout.checkout_key);
      formData.append('last4', (cardNumber||'').slice(-4));
      formData.append('billing[first_name]', billing.first_name);
      formData.append('billing[last_name]', billing.last_name);
      formData.append('billing[email]', billing.email);
      formData.append('billing[address]', billing.address);
      formData.append('billing[address2]', billing.address2);
      formData.append('billing[city]', billing.city);
      formData.append('billing[state]', billing.state);
      formData.append('billing[zip]', billing.zip);
      if(token){
        formData.append('opaqueData[dataDescriptor]', token.dataDescriptor);
        formData.append('opaqueData[dataValue]', token.dataValue);
      }
      sessionStorage.setItem('ttaCheckout', JSON.stringify({checkout_key: tta_checkout.checkout_key, timestamp: Date.now()}));

      if(debugEnabled && typeof formData.forEach === 'function'){
        var preview = {};
        formData.forEach(function(value, key){
          if(Object.prototype.hasOwnProperty.call(preview, key)){
            if(Array.isArray(preview[key])){
              preview[key].push(value);
            }else{
              preview[key] = [preview[key], value];
            }
          }else{
            preview[key] = value;
          }
        });
        debugLog('Dispatching checkout AJAX request', {
          token: token || null,
          billing: billing,
          formData: preview
        });
      }

      $.ajax({
        url: tta_checkout.ajax_url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json'
      }).done(finalizeResponse).fail(function(jqXHR, textStatus){
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        $resp.removeClass('updated').addClass('error').text('Request failed. Please try again.');
        sessionStorage.removeItem('ttaCheckout');
        debugError('Checkout AJAX request failed', {
          status: textStatus,
          xhrStatus: jqXHR && jqXHR.status,
          response: jqXHR && jqXHR.responseText
        });
      });
    }

    $form.on('submit', function(e){
      e.preventDefault();
      const amount = (function(){
        const clean = function(s){
          const match = String(s||'').trim().replace(/[, ]+/g,'').match(/-?\d+(?:\.\d+)?/);
          return match ? parseFloat(match[0]).toFixed(2) : null;
        };
        return (
          clean($('#tta-final-total').text() || $('#tta-final-total').val()) ||
          clean($form.find('[name="tta_amount"]').val()) ||
          clean($btn.data('amount')) ||
          clean($form.data('amount')) ||
          '0.00'
        );
      })();

      var paymentDisabled = $form.find('[name="card_number"]').is(':disabled');
      var isFree = parseFloat(amount) <= 0 || paymentDisabled;
      $btn.prop('disabled', true);
      $spin.css({display:'inline-block',opacity:0}).fadeTo(200,1);
      showMessage(isFree ? 'Submitting order…' : 'Processing payment…');
      debugLog('Checkout form submitted', {
        amount: amount,
        isFree: isFree,
        paymentDisabled: paymentDisabled
      });

      var cardNumber = $.trim($form.find('[name="card_number"]').val());
      var exp = $.trim($form.find('[name="card_exp"]').val());
      var cvc = $.trim($form.find('[name="card_cvc"]').val());
      var billing = {
        first_name: $form.find('[name="billing_first_name"]').val(),
        last_name: $form.find('[name="billing_last_name"]').val(),
        email: $form.find('[name="billing_email"]').val(),
        address: $form.find('[name="billing_street"]').val(),
        address2: $form.find('[name="billing_street_2"]').val(),
        city: $form.find('[name="billing_city"]').val(),
        state: $form.find('[name="billing_state"]').val(),
        zip: $form.find('[name="billing_zip"]').val(),
        country: 'USA'
      };

      if(isFree){
        sendCheckout(null, billing, cardNumber);
        return;
      }

      if(typeof Accept === 'undefined' || !Accept || typeof Accept.dispatchData !== 'function'){
        encryptionFailed('Accept.js unavailable');
        return;
      }

      exp = exp.replace(/\s+/g,'');
      if(/^[0-9]{4}$/.test(exp)) exp = exp.substring(0,2)+'/'+exp.substring(2);
      var parts = exp.split(/[\/\-]/); var month = parts[0]; var year = parts[1]; if(year && year.length===2) year='20'+year;

      try {
        debugLog('Dispatching Accept.js tokenisation', {
          hasCardNumber: !!cardNumber,
          month: month,
          year: year,
          hasCvc: !!cvc
        });
        Accept.dispatchData({
          authData:{clientKey:cfg.clientKey,apiLoginID:cfg.loginId},
          cardData:{cardNumber:cardNumber,month:month,year:year,cardCode:cvc}
        }, function(response){
          if(!response || !response.messages){
            encryptionFailed('Empty Accept.js response');
            return;
          }
          debugLog('Accept.js response received', response);
          if(response.messages.resultCode !== 'Ok'){
            var errors = (response.messages.message||[]).map(function(m){return m.text;}).filter(Boolean);
            if(!errors.length){
              encryptionFailed(response);
              return;
            }
            showMessage(errors.join(' | '), true);
            $spin.fadeOut(200);
            $btn.prop('disabled', false);
            sessionStorage.removeItem('ttaCheckout');
            return;
          }
          debugLog('Accept.js token created', response.opaqueData);
          sendCheckout(response.opaqueData, billing, cardNumber);
        });
      } catch(err){
        encryptionFailed(err);
        debugError('Accept.js threw an exception', err);
      }
    });
  });
})(jQuery);
