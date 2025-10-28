(function($){
  function getState($form){
    return {
      $form: $form,
      $btn: $form.find('button[type=submit]').first(),
      $spin: $form.find('.tta-admin-progress-spinner-svg'),
      $resp: $form.find('#tta-subscription-response .tta-admin-progress-response-p')
    };
  }

  function begin(state){
    state.$btn.prop('disabled', true);
    state.$spin.css({ display: 'inline-block', opacity: 0 }).fadeTo(200, 1);
    state.$resp.removeClass('updated error').text('');
  }

  function hasHtml(message){
    return /<[a-z][\s\S]*>/i.test(message || '');
  }

  function failImmediate(state, message){
    var msg = message || '';
    state.$spin.stop(true, true);
    if ( state.$spin.is(':visible') ) {
      state.$spin.fadeTo(200, 0, function(){ $(this).hide(); });
    } else {
      state.$spin.hide();
    }
    state.$btn.prop('disabled', false);
    state.$resp.removeClass('updated').addClass('error');
    if ( hasHtml(msg) ) {
      state.$resp.html(msg);
    } else {
      state.$resp.text(msg);
    }
  }

  function finish(state, success, message){
    var msg = message || '';
    setTimeout(function(){
      state.$spin.stop(true, true).fadeTo(200, 0, function(){ $(this).hide(); });
      state.$btn.prop('disabled', false);
      state.$resp.removeClass('updated error').addClass(success ? 'updated' : 'error');
      if ( hasHtml(msg) ) {
        state.$resp.html(msg);
      } else {
        state.$resp.text(msg);
      }
    }, 5000);
  }

  function sanitizeExp(exp){
    var cleaned = String(exp || '').replace(/\s+/g, '');
    if (/^[0-9]{4}$/.test(cleaned)) {
      cleaned = cleaned.substring(0, 2) + '/' + cleaned.substring(2);
    }
    var parts = cleaned.split(/[\/\-]/);
    var month = parts[0] || '';
    var year = parts[1] || '';
    if ( year.length === 2 ) {
      year = '20' + year;
    }
    return { month: month, year: year };
  }

  function shouldBypass($form){
    if ( $form.attr('id') === 'tta-admin-reactivate-subscription-form' ) {
      var useCurrent = $form.find('input[name="use_current"]').val();
      if ( String(useCurrent) === '1' ) {
        return true;
      }
    }
    return false;
  }

  function performAjax($form, action, extras, state){
    var payload = $form.serializeArray().filter(function(field){
      return [ 'card_number', 'card_cvc', 'exp_date' ].indexOf(field.name) === -1;
    });

    (extras || []).forEach(function(extra){
      payload.push(extra);
    });

    payload.push({ name: 'action', value: action });
    payload.push({ name: 'nonce', value: TTA_Ajax.membership_admin_nonce });

    $.post(TTA_Ajax.ajax_url, $.param(payload), function(res){
      var success = !!(res && res.success);
      var msg = '';
      if ( res && res.data && res.data.message ) {
        msg = res.data.message;
      } else if ( ! success ) {
        msg = 'Error';
      }
      finish(state, success, msg);
    }, 'json').fail(function(){
      finish(state, false, 'Request failed.');
    });
  }

  $(document).on('submit', '#tta-admin-update-payment-form, #tta-admin-reactivate-subscription-form, #tta-admin-assign-membership-form', function(e){
    var $form = $(this);
    var actionMap = {
      'tta-admin-update-payment-form': 'tta_admin_update_payment',
      'tta-admin-reactivate-subscription-form': 'tta_admin_reactivate_subscription',
      'tta-admin-assign-membership-form': 'tta_admin_assign_membership'
    };
    var action = actionMap[this.id];
    if ( ! action ) {
      return;
    }

    if ( shouldBypass($form) ) {
      return;
    }

    e.preventDefault();
    e.stopImmediatePropagation();

    var cfg = window.TTA_ACCEPT_ADMIN || {};
    var state = getState($form);

    if ( ! cfg.clientKey || ! cfg.loginId ) {
      failImmediate(state, cfg.failureMessage || 'Encryption of your payment information failed. Please try again later.');
      return;
    }

    var cardNumber = $.trim($form.find('[name="card_number"]').val() || '');
    var expRaw     = $.trim($form.find('[name="exp_date"]').val() || '');
    var cvc        = $.trim($form.find('[name="card_cvc"]').val() || '');

    begin(state);

    if ( ! cardNumber || ! expRaw ) {
      failImmediate(state, cfg.failureMessage || 'Encryption of your payment information failed. Please try again later.');
      return;
    }

    if ( typeof Accept === 'undefined' || typeof Accept.dispatchData !== 'function' ) {
      if ( window.console ) {
        console.error('Accept.js unavailable for admin membership form');
      }
      failImmediate(state, cfg.failureMessage || 'Encryption of your payment information failed. Please try again later.');
      return;
    }

    var exp = sanitizeExp(expRaw);

    try {
      Accept.dispatchData({
        authData: {
          clientKey: cfg.clientKey,
          apiLoginID: cfg.loginId
        },
        cardData: {
          cardNumber: cardNumber,
          month: exp.month,
          year: exp.year,
          cardCode: cvc
        }
      }, function(response){
        if ( ! response || ! response.messages ) {
          failImmediate(state, cfg.failureMessage || 'Encryption of your payment information failed. Please try again later.');
          return;
        }

        if ( response.messages.resultCode !== 'Ok' ) {
          var errors = (response.messages.message || []).map(function(m){ return m.text; }).filter(Boolean);
          var message = errors.join(' | ') || (cfg.failureMessage || 'Encryption of your payment information failed. Please try again later.');
          failImmediate(state, message);
          return;
        }

        var opaque = response.opaqueData || {};
        if ( ! opaque.dataDescriptor || ! opaque.dataValue ) {
          failImmediate(state, cfg.failureMessage || 'Encryption of your payment information failed. Please try again later.');
          return;
        }

        var extras = [
          { name: 'opaqueData[dataDescriptor]', value: opaque.dataDescriptor },
          { name: 'opaqueData[dataValue]', value: opaque.dataValue }
        ];
        if ( cardNumber ) {
          extras.push({ name: 'last4', value: cardNumber.slice(-4) });
        }

        performAjax($form, action, extras, state);
      });
    } catch (err) {
      if ( window.console ) {
        console.error('Accept.js threw an error for admin membership form', err);
      }
      failImmediate(state, cfg.failureMessage || 'Encryption of your payment information failed. Please try again later.');
    }
  });
})(jQuery);
