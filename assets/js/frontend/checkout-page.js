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
          var html = '';
          if(res.data.membership){
            if(res.data.membership === 'reentry'){
              html += '<p>Thanks for purchasing your Re-Entry Ticket! You can once again register for events. An email will be sent to ' + tta_checkout.user_email + ' for your records. Thanks again, and welcome back!</p>';
            } else {
              var amt = res.data.membership === 'premium' ? 10 : 5;
              html += '<p>Thanks for becoming a ' + res.data.membership.charAt(0).toUpperCase()+res.data.membership.slice(1) + ' Member! ' +
                "There's nothing else for you to do - you'll be automatically billed $"+amt+" once monthly, and can cancel anytime on your " +
                '<a href="https://trying-to-adult-rva-2025.local/member-dashboard/?tab=billing">Member Dashboard</a>. ' +
                'An email will be sent to ' + tta_checkout.user_email + ' with your Membership Details. Thanks again, and enjoy your Membership perks!</p>';
              if(res.data.membership === 'basic'){
                html += '<p>Did you know that there\'s even MORE perks and discounts to be had with a Premium Membership? <a href="https://trying-to-adult-rva-2025.local/become-a-member/">Learn more here.</a></p>';
              } else if(res.data.membership === 'premium'){
                html += '<p>Did you know? You can earn a free event and other perks by referring friends and family! Let us know who you\'ve referred at <a href="mailto:sam@tryingtoadultrva.com">sam@tryingtoadultrva.com</a> and we\'ll reach out.</p>';
              }
            }
          }
          if(res.data.has_tickets){
            var intro = res.data.membership ? 'Also, thanks for signing up for our upcoming event!' : 'Thanks for signing up!';
            html += '<p>'+intro+' A receipt has been emailed to each of the email addresses below. Please keep these emails to present to the Event Host or Volunteer upon arrival.</p><ul>';
            var emails = Array.isArray(res.data.emails) ? res.data.emails : (res.data.emails ? [res.data.emails] : []);
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

