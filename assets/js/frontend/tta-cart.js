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

  function collectCartData(){
    var data = { cart_qty: {}, discount_code: $('#tta-discount-code').val() || '' };
    $('.tta-cart-qty').each(function(){
      var id = $(this).attr('name').match(/\d+/)[0];
      data.cart_qty[id] = $(this).val();
    });
    return data;
  }

  function sendCartUpdate(){
    var payload = collectCartData();
    clearTimers();
    payload.action = 'tta_update_cart';
    payload.nonce  = tta_ajax.nonce;

    $('#tta-cart-container').fadeTo(200, 0.3);
    $('.tta-admin-progress-spinner-svg').css({opacity:1,display:'inline-block'});

    $.post( tta_ajax.ajax_url, payload, function(res){
      setTimeout(function(){
        $('.tta-admin-progress-spinner-svg').fadeOut(200);
        if ( res.success ) {
          $('#tta-cart-container').html(res.data.html).fadeTo(200,1);
          if(res.data.message){
            $('.tta-discount-feedback').text(res.data.message).fadeIn(200).delay(4000).fadeOut(200);
          }
        } else {
          alert(res.data.message || 'Error updating cart.');
          $('#tta-cart-container').fadeTo(200,1);
        }
        startTimers();
        updateApplyBtn();
      }, 1000);
    }, 'json');
  }

  $(document).on('change', '.tta-cart-qty', sendCartUpdate);

  function updateApplyBtn(){
    var hasCode = $.trim($('#tta-discount-code').val()) !== '';
    $('#tta-apply-discount').prop('disabled', !hasCode);
  }
  $(document).on('input', '#tta-discount-code', updateApplyBtn);
  $(document).on('click', '#tta-apply-discount', sendCartUpdate);

  $(document).on('click', '.tta-remove-item', function(){
    var id = $(this).data('ticket');
    $('input[name="cart_qty['+id+']"]').val(0);
    sendCartUpdate();
  });

  function clearTimers(){
    countdownTimers.forEach(clearInterval);
    countdownTimers = [];
  }
  function startTimers(){
    clearTimers();
    $('.tta-cart-table tbody tr').each(function(){
      var $row = $(this);
      var expireAt = parseInt($row.data('expire-at'),10) * 1000;
      var $cd = $row.find('.tta-countdown');
      if(!expireAt || !$cd.length) return;
      function update(){
        var remain = Math.floor((expireAt - Date.now())/1000);
        if(remain <= 0){
          clearInterval(intv);
          $row.find('.tta-remove-item').click();
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
});
