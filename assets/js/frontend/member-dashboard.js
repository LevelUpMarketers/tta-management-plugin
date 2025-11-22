jQuery(function($){
  var $wrapper = $('.tta-dashboard-content'),
      $form    = $('#tta-member-dashboard-form');

  // 1) Toggle edit-mode on click
  $('#toggle-edit-mode').on('click', function(){
    var $btn = $(this);
    $wrapper.addClass('fading');
    setTimeout(function(){
      $wrapper.toggleClass('is-editing fading');
      if ( $wrapper.hasClass('is-editing') ) {
        $btn.text('Cancel Editing');
      } else {
        $btn.text('Edit Profile');
        $form[0].reset();
      }
    }, 200);
  });

  // 2) Tab switching
  $('.tta-dashboard-tabs li').on('click', function(){
    var tab = $(this).data('tab');
    $('.tta-dashboard-tabs li').removeClass('active');
    $(this).addClass('active');
    $('.tta-dashboard-section').hide();
    $('#tab-' + tab).show();
  });

  // Mobile accordion for narrow screens
  function initAccordion(){
    if (window.innerWidth >= 1200 || $('.tta-accordion-tab').length) return;
    $('.tta-dashboard-tabs li').each(function(){
      var tab = $(this).data('tab');
      var label = $(this).text();
      var $section = $('#tab-' + tab);
      var $acc = $('<div>', { class: 'tta-accordion-tab', 'data-tab': tab }).text(label);
      $section.before($acc);
    });
    $('.tta-dashboard-section').hide();
    var current = $('.tta-dashboard-tabs li.active').data('tab') || 'profile';
    $('.tta-accordion-tab[data-tab="' + current + '"]').addClass('active').next('.tta-dashboard-section').show();
    $('.tta-accordion-tab').on('click', function(){
      var tab = $(this).data('tab');
      if ($(this).hasClass('active')) {
        $(this).removeClass('active');
        $('#tab-' + tab).slideUp(200);
      } else {
        $('.tta-accordion-tab').removeClass('active');
        $('.tta-dashboard-section').slideUp(200);
        $(this).addClass('active');
        $('#tab-' + tab).slideDown(200);
      }
    });
  }
  function destroyAccordion(){
    var current = $('.tta-accordion-tab.active').data('tab') || 'profile';
    $('.tta-accordion-tab').remove();
    $('.tta-dashboard-section').hide();
    $('#tab-' + current).show();
    $('.tta-dashboard-tabs li').removeClass('active').filter('[data-tab="' + current + '"]').addClass('active');
  }
  initAccordion();
  $(window).on('resize', function(){
    if (window.innerWidth < 1200) {
      initAccordion();
    } else if ($('.tta-accordion-tab').length) {
      destroyAccordion();
    }
  });

  // Activate tab based on URL hash or ?tab=name parameter
  function activateTab(tab){
    if ($('.tta-accordion-tab').length) {
      var $acc = $('.tta-accordion-tab[data-tab="' + tab + '"]');
      if ($acc.length) {
        $acc.trigger('click');
      }
    } else {
      var $trigger = $('.tta-dashboard-tabs li[data-tab="' + tab + '"]');
      if ($trigger.length) {
        $trigger.trigger('click');
      }
    }
    var $wrap = $('.tta-member-dashboard-wrap');
    var h = $('.site-header, .tta-header').first().outerHeight() || 0;
    $('html, body').animate({
      scrollTop: $wrap.offset().top - h - 100
    }, 600);
  }

  var urlParams = new URLSearchParams(window.location.search);
  var paramTab = urlParams.get('tab');
  var hashTab = window.location.hash.replace('#tab-', '');
  var active = paramTab || hashTab;
  if (active) {
    activateTab(active);
  }

  // 3) Add/remove interests
  $('#add-interest-edit').on('click', function(e){
    e.preventDefault();
    var count = $('#interests-container input.interest-field').length + 1,
        $item = $('<div>', { class: 'interest-item' }),
        $input= $('<input>', {
                   type: 'text',
                   name: 'interests[]',
                   class:'regular-text interest-field edit-input',
                   placeholder:'Interest #' + count
                 }),
        $btn  = $('<button>', {
                   type:'button',
                   class:'delete-interest',
                   'aria-label':'Remove this interest'
                 }).append(
                   $('<img>', {
                     src: TTA_MemberDashboard.plugin_url + 'assets/images/public/bin.svg',
                     alt: '×'
                   })
                 );
    $item.append($input).append($btn);
    $(this).before($item);
  });
  $(document).on('click','.delete-interest',function(e){
    e.preventDefault();
    $(this).closest('.interest-item').remove();
  });

  // 4) Phone mask
  $('#phone').on('input', function(){
    var v = this.value.replace(/\D/g,'');
    if (v.length>6) v='('+v.slice(0,3)+') '+v.slice(3,6)+'-'+v.slice(6,10);
    else if (v.length>3) v='('+v.slice(0,3)+') '+v.slice(3);
    this.value = v;
  });

  // Login/register handling for guests
  $('.tta-login-message').on('click', '.tta-show-register', function(e){
    e.preventDefault();
    var $section = $(this).closest('.tta-login-message');
    var $link = $(this);
    $link.addClass('tta-button-disabled').attr('aria-disabled', 'true').attr('tabindex', '-1');
    $section.find('.tta-login-wrap').fadeOut(200, function(){
      $section.find('.tta-register-form').fadeIn(200);
    });
  });

  $('.tta-login-message').on('click', '.tta-cancel-register', function(e){
    e.preventDefault();
    var $section = $(this).closest('.tta-login-message');
    $section.find('.tta-register-form').fadeOut(200, function(){
      $section.find('.tta-login-wrap').fadeIn(200);
    });
    var $link = $section.find('.tta-show-register');
    $link.removeClass('tta-button-disabled').removeAttr('aria-disabled tabindex');
  });

  $('.tta-login-message').on('submit', '.tta-register-form', function(e){
    e.preventDefault();
    e.stopPropagation();
    var $form = $(this),
        $section = $form.closest('.tta-login-message'),
        $btn  = $form.find('button'),
        $spin = $form.find('.tta-admin-progress-spinner-svg'),
        $resp = $section.find('.tta-register-response'),
        email = $form.find('[name="email"]').val(),
        emailVerify = $form.find('[name="email_verify"]').val(),
        pass  = $form.find('[name="password"]').val(),
        passVerify = $form.find('[name="password_verify"]').val();

    $resp.removeClass('updated error').text('');

    if(email !== emailVerify){
      $resp.addClass('error').text(TTA_MemberDashboard.email_mismatch_msg);
      return;
    }
    if(pass !== passVerify){
      $resp.addClass('error').text(TTA_MemberDashboard.password_mismatch_msg);
      return;
    }
    if(!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(pass)){
      $resp.addClass('error').text(TTA_MemberDashboard.password_requirements_msg);
      return;
    }

    $btn.prop('disabled', true);
    $spin.show().css({opacity:0}).fadeTo(200,1);

    $.post(TTA_MemberDashboard.ajax_url, {
      action: 'tta_register',
      nonce: TTA_MemberDashboard.front_nonce,
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
               .text(TTA_MemberDashboard.account_created_msg.replace('%d', count));
          if(count-- > 0){
            setTimeout(update, 1000);
          } else {
            window.location.reload();
          }
        })();
      } else {
        $btn.prop('disabled', false);
        $resp.addClass('error').text(res.data.message || TTA_MemberDashboard.request_failed_msg);
      }
    }, 'json').fail(function(){
      $spin.fadeOut(200);
      $btn.prop('disabled', false);
      $resp.addClass('error').text(TTA_MemberDashboard.request_failed_msg);
    });
  });

  if ( !$form.length ) return;

  // 5) File picker & preview
  $('#select-profile-image').on('click', function(e){
    e.preventDefault();
    $('#profile-image-input').click();
  });
  $('#profile-image-input').on('change', function(){
    var f=this.files[0];
    if (!f) return;
    var r=new FileReader();
    r.onload = function(e){ $('#profileimage-preview img').attr('src',e.target.result); };
    r.readAsDataURL(f);
  });

  // 6) Ajax submit with 5s delay, spinner fade & rotate
  $form.on('submit', function(e){
    e.preventDefault();
    var $resp   = $form.find('.tta-admin-progress-response-p'),
        $btn    = $form.find('button[type="submit"]'),
        $spin   = $form.find('.tta-admin-progress-spinner-svg'),
        start   = Date.now(),
        dataObj = null,
        failed  = false;

    // email verify only in edit-mode
    if ( $('.hide-until-edit').is(':visible') ) {
      var e1 = $form.find('#email').val().trim(),
          e2 = $form.find('#email_verify').val().trim();
      if ( !e1 || e1!==e2 ) {
        return $resp.removeClass('updated').addClass('error')
                    .text('Whoops! Emails must match.');
      }
    }
    $resp.text('');

    // show spinner
    $btn.prop('disabled', true);
    $spin.stop(true).css({opacity:0,display:'inline'}).fadeTo(200,1);

    var fd = new FormData(this);
    fd.append('action','tta_front_update_member');
    fd.append('tta_member_front_update_nonce',TTA_MemberDashboard.update_nonce);

    $.ajax({
      url: TTA_MemberDashboard.ajax_url,
      method:'POST',
      data: fd,
      processData:false,
      contentType:false,
      dataType:'json'
    })
    .done(function(res){ dataObj = res; })
    .fail(function(){ failed = true; })
    .always(function(){
      var wait = Math.max(0,5000 - (Date.now()-start));
      setTimeout(function(){

        // hide spinner
        $spin.fadeTo(200,0,function(){ $spin.hide(); });
        $btn.prop('disabled',false);

        if (!failed && dataObj) {
          if ( dataObj.success ) {
            $resp.removeClass('error').addClass('updated')
                 .text(dataObj.data.message);

            // 1) update text‐fields & selects & textareas
            $form.find('input.edit-input:not([type="checkbox"]), select.edit-input, textarea.edit-input')
                 .each(function(){
              var $el = $(this), newVal;
              if ($el.is('select'))     newVal = $el.find('option:selected').text()||'—';
              else newVal = $el.val()||'—';
              $el.closest('td').find('.view-value').text(newVal);
            });

            // 2) update the interests list
            var ints = [];
            $('#interests-container input.interest-field').each(function(){
              var v=$.trim(this.value);
              if (v) ints.push(v);
            });
            $('#interests-container').closest('td')
              .find('.view-value').text(ints.join(', ')||'—');

            // 3) update opt-in checkboxes
            var opts = [];
            $form.find('fieldset.edit-input input:checked').each(function(){
              opts.push( $(this).parent().text().trim() );
            });
            $form.find('fieldset.edit-input').closest('td')
                 .find('.view-value').text(opts.join(', ')||'—');

            // 4) update profile image preview in view-mode
            if ( dataObj.data.preview ) {
              $('input#profileimgid').closest('td')
                .find('.view-value').html( dataObj.data.preview );
            }

            // *** NEW: Update the view-mode profile image ***
            var updatedSrc = $('#profileimage-preview img').attr('src');
            $form
              .find('input#profileimgid')
              .closest('td')
              .find('.view-value img')
              .attr('src', updatedSrc);

            if ( typeof dataObj.data.profileimgid !== 'undefined' ) {
              $form.find('#profileimgid').val( dataObj.data.profileimgid );
            }

            // exit edit-mode
            $wrapper.removeClass('is-editing');
            $('#toggle-edit-mode').text('Edit Profile');
          }
          else {
            $resp.removeClass('updated').addClass('error')
                 .text(dataObj.data.message||'Error saving profile.');
          }
        }
        else {
          $resp.removeClass('updated').addClass('error')
               .text('Request failed. Please try again.');
        }
      }, wait);
    });
  });

  // Refund/cancel form toggle
  $(document).on('click', '.tta-refund-link, .tta-cancel-link', function(e){
    e.preventDefault();
    var $wrap = $(this).closest('.tta-refund-wrapper');
    $wrap.find('.tta-refund-form').slideToggle(200);
  });

  // Submit refund request
  $(document).on('click', '.tta-refund-submit', function(e){
    e.preventDefault();
    var $btn  = $(this),
        $form = $btn.closest('.tta-refund-form'),
        tx    = $btn.data('tx'),
        ticket = $btn.data('ticket'),
        attendee = $btn.data('attendee'),
        reason= $form.find('textarea').val(),
        eventId = $form.data('event'),
        $spin = $form.find('.tta-admin-progress-spinner-svg'),
        $resp = $form.find('.tta-admin-progress-response-p'),
        start = Date.now();

    $resp.removeClass('updated error').text('');
    $btn.prop('disabled', true);
    $spin.show().css({opacity:0}).fadeTo(200,1);

    $.post(TTA_MemberDashboard.ajax_url, {
      action: 'tta_request_refund',
      nonce: TTA_MemberDashboard.front_nonce,
      transaction_id: tx,
      event_id: eventId,
      ticket_id: ticket,
      attendee_id: attendee,
      reason: reason
    }, function(res){
      var delay = Math.max(0, 5000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        if(res.success){
          $resp.addClass('updated').html(res.data.message);
          // prevent repeat submissions so the message can be read
          $form.find('textarea').prop('disabled', true);
          $btn.prop('disabled', true);
          $form.closest('.tta-refund-wrapper').find('.tta-refund-link, .tta-cancel-link').remove();
        }else{
          $resp.addClass('error').text(res.data.message||'Error');
        }
      }, delay);
    }, 'json').fail(function(){
      var delay = Math.max(0, 5000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        $resp.addClass('error').text('Request failed. Please try again.');
      }, delay);
    });
  });

  // Change membership level form
  $(document).on('submit', '.tta-change-level-form', function(e){
    e.preventDefault();
    var $form = $(this),
        $btn  = $form.find('button[type="submit"]'),
        $actions = $form.closest('.tta-membership-actions'),
        $spin = $actions.find('.tta-admin-progress-spinner-svg'),
        $resp = $actions.find('.tta-admin-progress-response-p'),
        start = Date.now();

    $resp.removeClass('updated error').text('');
    $btn.prop('disabled', true);
    $spin.show().css({opacity:0}).fadeTo(200,1);

    $.post(TTA_MemberDashboard.ajax_url, $form.serialize(), function(res){
      var delay = Math.max(0, 5000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        if(res.success){
          $resp.addClass('updated').text(res.data.message);
          window.location.reload();
        }else{
          $resp.addClass('error').text(res.data.message||'Error');
        }
      }, delay);
    }, 'json').fail(function(){
      var delay = Math.max(0, 5000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        $resp.addClass('error').text('Request failed. Please try again.');
      }, delay);
    });
  });

  // Cancel membership form
  $(document).on('submit', '#tta-cancel-membership-form', function(e){
    e.preventDefault();
    var $form = $(this),
        $btn  = $form.find('button[type="submit"]'),
        $actions = $form.closest('.tta-membership-actions'),
        $spin = $actions.find('.tta-admin-progress-spinner-svg'),
        $resp = $actions.find('.tta-admin-progress-response-p'),
        start = Date.now();

    $resp.removeClass('updated error').text('');
    $btn.prop('disabled', true);
    $spin.show().css({opacity:0}).fadeTo(200,1);

    var data = $form.serialize();
    $.post(TTA_MemberDashboard.ajax_url, data, function(res){
      var delay = Math.max(0, 5000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        if(res.success){
          $resp.addClass('updated').text(res.data.message);
          $('#tta-membership-status').text(res.data.status);
          $form.hide();
        }else{
          $resp.addClass('error').text(res.data.message||'Error');
        }
      }, delay);
    }, 'json').fail(function(){
      var delay = Math.max(0, 5000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        $resp.addClass('error').text('Request failed. Please try again.');
      }, delay);
    });
  });

  function ttaShowEncryptionError($resp, $spin, $btn, debug){
    $spin.fadeOut(200);
    $btn.prop('disabled', false);
    var message = TTA_MemberDashboard.encryption_failed_html || 'Encryption of your payment information failed! Please try again later. If you\'re still having trouble, please contact us using the form on our Contact Page.';
    $resp.removeClass('updated').addClass('error');
    if(/</.test(message)){
      $resp.html(message);
    }else{
      $resp.text(message);
    }
    if(debug){
      console.error('Accept.js encryption failed', debug);
    }
  }

  function ttaMask(str){
    if(!str){ return ''; }
    var s = String(str);
    if(s.length <= 6){
      return s[0] + '***' + s.slice(-1);
    }
    return s.slice(0, 3) + '***' + s.slice(-3);
  }

  function ttaDebug(label, payload){
    var ts = new Date().toISOString();
    if(window.console && console.log){
      console.log('[TTA Payment Debug]', ts, label, payload || '');
    }
  }

  function ttaGetAcceptConfig(){
    var cfg = TTA_MemberDashboard.accept || {};
    if((!cfg.clientKey || !cfg.loginId) && window.TTA_ACCEPT){
      cfg = window.TTA_ACCEPT;
    }
    return cfg || {};
  }

  function ttaSubmitEncryptedPayment($form, token, last4, controls, debugMeta){
    var dataArr = $form.serializeArray();
    var filtered = ['card_number','exp_date','card_cvc'];
    var payload = new FormData();
    dataArr.forEach(function(field){
      if(filtered.indexOf(field.name) !== -1){
        return;
      }
      payload.append(field.name, field.value);
    });
    if(token){
      payload.append('opaqueData[dataDescriptor]', token.dataDescriptor);
      payload.append('opaqueData[dataValue]', token.dataValue);
    }
    if(last4){
      payload.append('last4', last4);
    }
    if(debugMeta){
      payload.append('debug_meta', JSON.stringify(debugMeta));
    }

    ttaDebug('AJAX payload prepared', {
      hasToken: !!token,
      tokenDescriptor: token && token.dataDescriptor,
      tokenValueLength: token && token.dataValue ? token.dataValue.length : 0,
      last4: last4,
      debugMeta: debugMeta
    });

    $.ajax({
      url: TTA_MemberDashboard.ajax_url,
      method: 'POST',
      data: payload,
      processData: false,
      contentType: false,
      dataType: 'json'
    }).done(function(res){
      var delay = Math.max(0, 5000 - (Date.now()-controls.start));
      setTimeout(function(){
        controls.spin.fadeOut(200);
        controls.btn.prop('disabled', false);
        var data = res && res.data ? res.data : {};
        ttaDebug('AJAX response received', { res: res, elapsedMs: Date.now() - controls.start });
        if (data.subscription_raw) {
          try {
            var prettyRaw = JSON.stringify(data.subscription_raw, null, 2);
            console.log('[TTA Payment Debug] ARBGetSubscription raw response', prettyRaw);
          } catch (err) {
            console.log('[TTA Payment Debug] ARBGetSubscription raw response', data.subscription_raw);
          }
        }
        if(res && res.success){
          controls.resp.addClass('updated').removeClass('error').text(data.message);
          if(data.last4){
            $('#tta-card-last4').text(data.last4);
          }
          $form[0].reset();
        }else{
          var msg = data && data.message ? data.message : 'Error';
          controls.resp.addClass('error').removeClass('updated').text(msg);
        }
      }, delay);
    }).fail(function(err){
      var delay = Math.max(0, 5000 - (Date.now()-controls.start));
      setTimeout(function(){
        controls.spin.fadeOut(200);
        controls.btn.prop('disabled', false);
        controls.resp.addClass('error').removeClass('updated').text('Request failed. Please try again.');
        ttaDebug('AJAX request failed', { error: err, elapsedMs: Date.now() - controls.start });
      }, delay);
    });
  }

  // Update payment method form
  $(document).on('submit', '#tta-update-card-form', function(e){
    e.preventDefault();
    var $form = $(this),
        $btn  = $form.find('button[type="submit"]'),
        $spin = $form.find('.tta-admin-progress-spinner-svg'),
        $resp = $form.find('.tta-admin-progress-response-p'),
        start = Date.now();

    $resp.removeClass('updated error').text('');
    $btn.prop('disabled', true);
    $spin.show().css({opacity:0}).fadeTo(200,1);

    var cfg = ttaGetAcceptConfig();
    var debugMeta = {
      stage: 'init',
      submittedAt: new Date(start).toISOString(),
      acceptConfig: {
        loginId: cfg.loginId,
        clientKey: ttaMask(cfg.clientKey),
        script: cfg.url,
        sandbox: cfg.sandbox
      }
    };
    ttaDebug('Update payment clicked', debugMeta);
    if(!cfg.clientKey || !cfg.loginId || typeof Accept === 'undefined' || typeof Accept.dispatchData !== 'function'){
      ttaShowEncryptionError($resp, $spin, $btn, 'Missing Accept.js configuration');
      return;
    }

    var cardNumber = ($form.find('[name="card_number"]').val() || '').replace(/[^0-9]/g,'');
    var exp        = ($form.find('[name="exp_date"]').val() || '').replace(/\s+/g,'');
    var cvc        = ($form.find('[name="card_cvc"]').val() || '').replace(/[^0-9]/g,'');
    if(/^[0-9]{4}$/.test(exp)){
      exp = exp.substring(0,2) + '/' + exp.substring(2);
    }
    var parts = exp.split(/[\/\-]/);
    var month = parts[0] || '';
    var year  = parts[1] || '';
    if(year.length === 2){
      year = '20' + year;
    }

    var controls = { btn: $btn, spin: $spin, resp: $resp, start: start };

    debugMeta.stage = 'pre-dispatch';
    debugMeta.card = {
      last4: cardNumber.slice(-4),
      expRaw: exp,
      month: month,
      year: year,
      cvcLength: cvc.length
    };
    debugMeta.billing = {
      first: $form.find('[name="bill_first"]').val(),
      last: $form.find('[name="bill_last"]').val(),
      address: $form.find('[name="bill_address"]').val(),
      city: $form.find('[name="bill_city"]').val(),
      state: $form.find('[name="bill_state"]').val(),
      zip: $form.find('[name="bill_zip"]').val()
    };
    debugMeta.subscriptionId = $form.find('[name="subscription_id"]').val();
    debugMeta.email = $form.find('[name="email"]').val();
    ttaDebug('Dispatching Accept.dispatchData', debugMeta);

    Accept.dispatchData({
      authData: { clientKey: cfg.clientKey, apiLoginID: cfg.loginId },
      cardData: { cardNumber: cardNumber, month: month, year: year, cardCode: cvc }
    }, function(response){
      if(!response || response.messages.resultCode !== 'Ok' || !response.opaqueData){
        ttaShowEncryptionError($resp, $spin, $btn, response || 'No response');
        return;
      }
      var token = response.opaqueData;
      if(!token.dataDescriptor || !token.dataValue){
        ttaShowEncryptionError($resp, $spin, $btn, token);
        return;
      }
      var last4 = cardNumber.slice(-4);
      debugMeta.stage = 'tokenized';
      debugMeta.tokenCreatedAt = new Date().toISOString();
      debugMeta.token = {
        descriptor: token.dataDescriptor,
        valueLength: token.dataValue.length,
        last4: last4
      };
      ttaDebug('Accept.js token received', debugMeta);
      ttaSubmitEncryptedPayment($form, token, last4, controls, debugMeta);
    });
  });

  // Leave waitlist from dashboard
  $(document).on('click', '.tta-leave-waitlist', function(e){
    e.preventDefault();
    var $btn = $(this);
    $btn.prop('disabled', true);
    $.post(TTA_MemberDashboard.ajax_url, {
      action: 'tta_leave_waitlist',
      nonce: TTA_MemberDashboard.front_nonce,
      event_ute_id: $btn.data('event'),
      ticket_id: $btn.data('ticket')
    }, function(){
      window.location.reload();
    }, 'json').fail(function(){
      window.location.reload();
    });
  });

  $(document).on('click', '.tta-assistance-submit', function(e){
    e.preventDefault();
    var $btn  = $(this),
        $wrap = $btn.closest('.tta-assistance-form'),
        ute   = $btn.data('ute'),
        note  = $wrap.find('textarea').val(),
        $spin = $wrap.find('.tta-admin-progress-spinner-svg'),
        $resp = $wrap.find('.tta-admin-progress-response-p'),
        start = Date.now();

    $resp.removeClass('updated error').text('');
    $btn.prop('disabled', true);
    $spin.show().css({opacity:0}).fadeTo(200,1);

    $.post(TTA_MemberDashboard.ajax_url, {
      action: 'tta_send_assistance_note',
      nonce: TTA_MemberDashboard.front_nonce,
      event_ute_id: ute,
      message: note
    }, function(res){
      var delay = Math.max(0, 1000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        if(res.success){
          $resp.addClass('updated').text(res.data.message);
          $wrap.find('textarea').prop('disabled', true);
          $btn.hide();
        }else{
          $resp.addClass('error').text(res.data.message||'Error');
        }
      }, delay);
    }, 'json').fail(function(){
      var delay = Math.max(0, 1000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        $resp.addClass('error').text('Request failed. Please try again.');
      }, delay);
    });
  });

});
