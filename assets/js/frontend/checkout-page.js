jQuery(function($){
  var $form = $('#tta-checkout-form');
  if (!$form.length) return;

  var $container = $('#tta-checkout-container');
  var $left = $('.tta-checkout-left');
  var $right = $('.tta-checkout-right');
  var $btn  = $form.find('button[type="submit"]');
  var $spin = $form.find('.tta-admin-progress-spinner-svg');
  var $resp = $('#tta-checkout-response');

  $form.on('submit', function(e){
    e.preventDefault();
    $resp.removeClass('updated error').text('');
    var start = Date.now();

    $btn.prop('disabled', true);
    $spin.show().css({opacity:0}).fadeTo(200,1);
    $container.add($left).add($right).fadeTo(200,0.3);

    var data = $form.serialize();
    data += '&action=tta_do_checkout';
    data += '&nonce='+tta_checkout.nonce;

    $.post(tta_checkout.ajax_url, data, function(res){
      var delay = Math.max(0, 5000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $container.add($left).add($right).fadeTo(200,1);
        $btn.prop('disabled', false);
        if(res.success){
          $resp.removeClass('error').addClass('updated').text(res.data.message);
        } else {
          $resp.removeClass('updated').addClass('error').text(res.data.message||'Error processing payment');
        }
      }, delay);
    }, 'json').fail(function(){
      var delay = Math.max(0, 5000 - (Date.now()-start));
      setTimeout(function(){
        $spin.fadeOut(200);
        $container.add($left).add($right).fadeTo(200,1);
        $btn.prop('disabled', false);
        $resp.removeClass('updated').addClass('error').text('Request failed. Please try again.');
      }, delay);
    });
  });
});

