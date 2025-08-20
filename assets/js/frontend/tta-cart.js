jQuery(function($){
  var countdownTimers = [];

  function ttaHtmlAlert(msg){
    var $overlay = $('#tta-html-alert-overlay');
    if(!$overlay.length){
      $overlay = $('<div id="tta-html-alert-overlay" class="tta-alert-overlay" style="display:none;"><div class="tta-alert-modal"><button type="button" class="tta-alert-close" aria-label="Close">×</button><div class="tta-alert-content"></div></div></div>').appendTo('body');
      $overlay.on('click', '.tta-alert-close', function(){ $overlay.fadeOut(200); });
      $overlay.on('click', function(e){ if(e.target === this){ $overlay.fadeOut(200); } });
    }
    $overlay.find('.tta-alert-content').html(msg);
    $overlay.fadeIn(200);
  }
  // Quantity controls
  $('.tta-qty-increase').on('click', function(){
    var $input = $(this).closest('.tta-ticket-quantity').find('.tta-qty-input');
    var max    = parseInt($input.attr('max'), 10) || Infinity;
    var limit  = parseInt($input.data('limit'), 10) || 2;
    var purchased = parseInt($input.data('purchased'), 10) || 0;
    var allowed = Math.max(0, limit - purchased);
    var val    = parseInt($input.val(), 10) || 0;
    if ( val >= max || val >= allowed ) {
      if ( val >= allowed && window.ttaShowNotice ) {
        var msg = (purchased >= limit)
          ? tta_event.prev_limit_msg.replace('%d', limit)
          : tta_event.limit_msg.replace('%d', limit);
        window.ttaShowNotice($input, msg);
      }
      return;
    }
    var desired  = val + 1;
    var ticketId = $input.attr('name').match(/\d+/)[0];
    $.post( tta_ajax.ajax_url, {
      action: 'tta_check_stock',
      ticket_id: ticketId,
      nonce: tta_ajax.nonce
    }, function(res){
      if(res.success && parseInt(res.data.available,10) >= desired){
        $input.val(desired).trigger('change');
      } else if(window.ttaShowNotice){
        window.ttaShowNotice($input, tta_event.sold_out_msg);
      }
    }, 'json');
  });
  $('.tta-qty-decrease').on('click', function(){
    var $input = $(this).closest('.tta-ticket-quantity').find('.tta-qty-input');
    var min    = parseInt($input.attr('min'), 10) || 0;
    var val    = parseInt($input.val(), 10) || 0;
    if ( val > min ) $input.val(val - 1).trigger('change');
  });

  // Add to cart handler
  $('#tta-get-tickets').on('click', function(){
    var items = [];
    $('.tta-qty-input').each(function(){
      var qty = parseInt($(this).val(),10) || 0;
      var ticket_id = $(this).attr('name').match(/\d+/)[0];
      items.push({ ticket_id: ticket_id, quantity: qty });
    });
    $.post( tta_ajax.ajax_url, {
      action: 'tta_add_to_cart',
      items:  JSON.stringify(items),
      nonce:  tta_ajax.nonce
    }, function(res){
      if ( res.success ) {
        if ( res.data.message && window.ttaShowNotice ) {
          window.ttaShowNotice($('.tta-qty-input').first(), res.data.message);
        }
        if ( res.data.cart_url ) {
          window.location.href = res.data.cart_url;
        }
      } else {
        ttaHtmlAlert( res.data.message || 'Error adding to cart.' );
      }
    }, 'json');
  });

  function sendMembership(level){
    $.post( tta_ajax.ajax_url, {
      action: 'tta_add_membership',
      level: level,
      nonce: tta_ajax.nonce
    }, function(res){
      if(res.success){
        window.location.href = res.data.cart_url;
      } else {
        ttaHtmlAlert(res.data.message || 'Error adding membership.');
      }
    }, 'json');
  }

  function removeMembership(){
    $.post( tta_ajax.ajax_url, {
      action: 'tta_remove_membership',
      nonce: tta_ajax.nonce
    }, function(res){
      if(res.success){
        window.location.reload();
      }
    }, 'json');
  }

  $('#tta-basic-signup').on('click', function(){ sendMembership('basic'); });
  $('#tta-premium-signup').on('click', function(){ sendMembership('premium'); });
  $(document).on('click', '#tta-remove-membership', function(){ removeMembership(); });

  function collectCartData(){
    var data = { cart_qty: {} };
    $('.tta-cart-qty').each(function(){
      var id = $(this).attr('name').match(/\d+/)[0];
      data.cart_qty[id] = $(this).val();
    });
    return data;
  }

  function sendCartUpdate(extra, done){
    var payload = collectCartData();
    $.extend(true, payload, extra);
    clearTimers();
    payload.action = 'tta_update_cart';
    payload.nonce  = tta_ajax.nonce;

    var $cartWrap    = $('#tta-cart-container');
    var $summaryWrap = $('#tta-checkout-container');
    var $target      = $cartWrap.length ? $cartWrap : $summaryWrap;
    if($target.length){
      $target.fadeTo(200, 0.3);
    }
    $('.tta-admin-progress-spinner-svg').css({opacity:1,display:'inline-block'});

    $.post( tta_ajax.ajax_url, payload, function(res){
      setTimeout(function(){
        $('.tta-admin-progress-spinner-svg').fadeOut(200);
        if ( res.success ) {

          if($cartWrap.length){
            $cartWrap.html(res.data.html).fadeTo(200,1);
          }
          if($summaryWrap.length){
            $summaryWrap.html(res.data.summary).fadeTo(200,1);
          }

          if(res.data.message){
            $('.tta-discount-feedback').text(res.data.message).fadeIn(200).delay(4000).fadeOut(200);
          }
          $('.tta-ticket-notice.tt-show').each(function(){
            var $n = $(this);
            setTimeout(function(){ $n.fadeOut(200); }, 4000);
          });
        } else {
          ttaHtmlAlert(res.data.message || 'Error updating cart.');
          if($target.length){ $target.fadeTo(200,1); }
        }
        startTimers();
        updateApplyBtn();
        if (typeof done === 'function') { done(res); }
      }, 1000);
    }, 'json');
  }

  $(document).on('change', '.tta-cart-qty', function(){ sendCartUpdate({}); });

  function updateApplyBtn(){
    var hasCode = $.trim($('#tta-discount-code').val()) !== '';
    $('#tta-apply-discount').prop('disabled', !hasCode);
  }
  $(document).on('input', '#tta-discount-code', updateApplyBtn);
  $(document).on('click', '#tta-apply-discount', function(){
    var code = $('#tta-discount-code').val();
    $('#tta-discount-code').val('');
    updateApplyBtn();
    sendCartUpdate({discount_code: code});
  });

  $(document).on('click', '.tta-remove-discount', function(){
    var code = $(this).data('code');
    sendCartUpdate({remove_code: code});
  });

  $(document).on('click', '.tta-remove-item', function(){
    var id = $(this).data('ticket');
    $('input[name="cart_qty['+id+']"]').val(0);
    sendCartUpdate({});
  });

  function clearTimers(){
    countdownTimers.forEach(clearInterval);
    countdownTimers = [];
  }
  function startTimers(){
    clearTimers();
    var pendingRemove = {};
    var processing = false;
    function processPending(){
      if(processing || $.isEmptyObject(pendingRemove)) return;
      processing = true;
      sendCartUpdate({cart_qty: pendingRemove}, function(){
        processing = false;
        pendingRemove = {};
        processPending();
      });
    }
    $('.tta-cart-table tbody tr, .tta-checkout-summary tbody tr').each(function(){
      var $row = $(this);
      var expireAt = parseInt($row.data('expire-at'),10) * 1000;
      var $cd = $row.find('.tta-countdown');
      if(!expireAt || !$cd.length) return;
      function update(){
        var remain = Math.floor((expireAt - Date.now())/1000);
        if(remain <= 0){
          clearInterval(intv);
          var id = $row.data('ticket');
          if(id){
            pendingRemove[id] = 0;
            processPending();
          }
          return;
        }
        var m = Math.floor(remain/60);
        var s = remain % 60;
        $cd.text(m+':' + (s<10?'0':'')+s);
      }
      update();
      var intv = setInterval(update,1000);
      countdownTimers.push(intv);
    });
  }

  startTimers();
  updateApplyBtn();
  document.addEventListener('visibilitychange', function(){
    if(!document.hidden){
      startTimers();
    }
  });

  // Become a Member join/signup form
  function handleRegisterResult($resp, $spin, $btn, res){
    $spin.fadeOut(200);
    $btn.prop('disabled', false);
    $resp.removeClass('updated error');
    if(res.success){
      window.location.reload();
    } else {
      $resp.addClass('error').text(res.data.message || 'Error');
    }
  }

  $('.tta-join-now').on('click', function(e){
    e.preventDefault();
    $('#tta-register-form').fadeIn(200);
    $('html, body').animate({scrollTop: $('#tta-register-form').offset().top}, 500);
  });

  $('#tta-register-form').on('click', '.tta-cancel-register', function(e){
    e.preventDefault();
    $('#tta-register-form').fadeOut(200);
  });

  $('#tta-register-form').on('submit', function(e){
    e.preventDefault();
    e.stopPropagation();
    var $form = $(this);
    var $btn  = $form.find('button');
    var $spin = $form.find('.tta-admin-progress-spinner-svg');
    var $resp = $('#tta-register-response');
    $resp.removeClass('updated error').text('');

    var email = $form.find('[name="email"]').val();
    var emailVerify = $form.find('[name="email_verify"]').val();
    var pass = $form.find('[name="password"]').val();
    var passVerify = $form.find('[name="password_verify"]').val();

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
      last_name: $form.find('[name="last_name"]').val(),
      email: email,
      email_verify: emailVerify,
      password: pass,
      password_verify: passVerify
    }, function(res){ handleRegisterResult($resp, $spin, $btn, res); }, 'json').fail(function(){
      handleRegisterResult($resp, $spin, $btn, { success:false, data:{ message:'Request failed.' } });
    });
  });

  var $carousel = $('.tta-member-intro-gallery');
  var $imgs = $carousel.find('img');
  if ($imgs.length > 1) {
    var idx = 0;
    function cycle(){
      var $current = $imgs.eq(idx);
      idx = (idx + 1) % $imgs.length;
      var $next = $imgs.eq(idx);
      $current.removeClass('active').addClass('exit');
      $next.addClass('active');
      setTimeout(function(){ $current.removeClass('exit'); }, 1000);
      setTimeout(cycle, 5000);
    }
    setTimeout(cycle, 5000);
  }

  // Phone number mask for attendee fields
  $(document).on('input', '.tta-attendee-fields input[type="tel"]', function(){
    var v = $(this).val().replace(/\D/g,'').slice(0,10);
    if (v.length > 6) {
      v = '('+v.slice(0,3)+') '+v.slice(3,6)+'-'+v.slice(6);
    } else if (v.length > 3) {
      v = '('+v.slice(0,3)+') '+v.slice(3);
    }
    $(this).val(v);
  });

});
