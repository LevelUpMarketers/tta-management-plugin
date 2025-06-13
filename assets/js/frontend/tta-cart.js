jQuery(function($){
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
});
