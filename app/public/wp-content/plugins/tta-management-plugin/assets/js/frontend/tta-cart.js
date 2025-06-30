jQuery(function($){
  var countdownTimers = [];
  // Quantity controls
  $('.tta-qty-increase').on('click', function(){
    var $input = $(this).closest('.tta-ticket-quantity').find('.tta-qty-input');
    var max    = parseInt($input.attr('max'), 10) || Infinity;
    var val    = parseInt($input.val(), 10) || 0;
    if ( val < max ) $input.val(val + 1).trigger('change');
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
        window.location.href = res.data.cart_url;
      } else {
        alert( res.data.message || 'Error adding to cart.' );
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
        alert(res.data.message || 'Error adding membership.');
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
          alert(res.data.message || 'Error updating cart.');
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
