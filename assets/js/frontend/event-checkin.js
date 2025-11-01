jQuery(function($){
  var minLength = parseInt(TTA_Checkin.email_min_length, 10) || 20;
  var minLengthMessage = TTA_Checkin.email_too_short || ('Please enter at least ' + minLength + ' characters before sending.');

  function normalizedLength(value){
    if (!value) {
      return 0;
    }
    return value.replace(/\s+/g, ' ').trim().length;
  }

  function refreshEmailButton($wrap){
    var $btn = $wrap.find('.tta-email-attendees__send');
    if (!$btn.length) {
      return;
    }
    var len = normalizedLength($wrap.find('.tta-email-attendees__message').val());
    if (len >= minLength) {
      $btn.prop('disabled', false).removeClass('disabled');
    } else {
      $btn.prop('disabled', true).addClass('disabled');
    }
  }

  function initEmailPanels($context){
    $context.find('.tta-email-attendees').each(function(){
      refreshEmailButton($(this));
    });
  }

  function findEventRow($el){
    var $row = $el.closest('.tta-event-row');
    if ($row.length) {
      return $row;
    }
    var $inline = $el.closest('.tta-inline-row');
    if ($inline.length) {
      $row = $inline.prev('.tta-event-row');
    }
    return $row;
  }

  function suppressRowToggle($row, suppressed){
    if (!$row || !$row.length) {
      return;
    }
    if (suppressed) {
      $row.data('ttaSuppressToggle', true);
    } else {
      $row.removeData('ttaSuppressToggle');
    }
  }

  function markInlineInteractionStart(){
    suppressRowToggle(findEventRow($(this)), true);
  }

  function markInlineInteractionEnd(){
    var $row = findEventRow($(this));
    if (!$row || !$row.length) {
      return;
    }
    setTimeout(function(){ suppressRowToggle($row, false); }, 0);
  }

  $(document).on('mousedown touchstart focusin', '.tta-inline-container :input, .tta-inline-wrapper :input', markInlineInteractionStart);
  $(document).on('mouseup touchend focusout', '.tta-inline-container :input, .tta-inline-wrapper :input', markInlineInteractionEnd);

  $(document).on('input', '.tta-email-attendees__message', function(){
    refreshEmailButton($(this).closest('.tta-email-attendees'));
  });

  // Toggle event rows
  $(document).on('click', '.tta-event-row', function(e){
    var $target = $(e.target);
    var $row    = $(this);
    if ($row.data('ttaSuppressToggle') && !$target.closest('.tta-toggle-cell').length) {
      return;
    }
    if ($target.is('button, a, input, textarea, select')) return;
    if ($target.closest('.tta-inline-container').length) return;
    suppressRowToggle($row, false);
    var $arrow      = $row.find('.tta-toggle-arrow'),
        ute         = $row.data('event-ute-id'),
        isMobile    = window.matchMedia('(max-width:1199px)').matches,
        $toggleCell = $row.find('.tta-toggle-cell'),
        $container  = $toggleCell.find('.tta-inline-container'),
        $ex         = $row.next('.tta-inline-row');

    if (isMobile){
      if ($toggleCell.hasClass('open')){
        $arrow.removeClass('open');
        $row.removeClass('open');
        $toggleCell.removeClass('open');
        $container.slideUp().empty();
        return;
      }
      $('.tta-toggle-cell.open').removeClass('open').find('.tta-inline-container').slideUp().empty();
      $('.tta-inline-row').remove();
      $('.tta-toggle-arrow').removeClass('open');
      $('.tta-event-row').removeClass('open');
      $.post(TTA_Checkin.ajax_url, { action:'tta_get_event_attendance', nonce:TTA_Checkin.get_nonce, event_ute_id: ute }, function(res){
        if(!res.success) return;
        $container.html(res.data.html).slideDown();
        initEmailPanels($container);
        $arrow.addClass('open');
        $row.addClass('open');
        $toggleCell.addClass('open');
      }, 'json');
      return;
    }

    if ($ex.length){
      $arrow.removeClass('open');
      $row.removeClass('open');
      $ex.find('.tta-inline-wrapper').slideUp(200, function(){ $ex.remove(); });
      return;
    }

    var $open = $('.tta-inline-row');
    if ($open.length){
      $open.prev('.tta-event-row').removeClass('open').find('.tta-toggle-arrow').removeClass('open');
      $open.find('.tta-inline-wrapper').slideUp(200, function(){ $open.remove(); });
    }

    $.post(TTA_Checkin.ajax_url, { action:'tta_get_event_attendance', nonce:TTA_Checkin.get_nonce, event_ute_id: ute }, function(res){
      if(!res.success) return;
      var colspan = $row.find('td').length;
      var $new = $('<tr class="tta-inline-row"><td colspan="'+colspan+'"><div class="tta-inline-wrapper" style="display:none">'+res.data.html+'</div></td></tr>');
      $row.after($new);
      $new.find('.tta-inline-wrapper').slideDown(200);
      $arrow.addClass('open');
      $row.addClass('open');
      initEmailPanels($new);
    }, 'json');
  });

  // Set attendance status
  $(document).on('click', '.tta-mark-attendance', function(e){
    e.preventDefault();
    var $btn = $(this), id = $btn.data('attendee-id'), status = $btn.data('status');
    var $wrap = $btn.closest('.tta-inline-wrapper, .tta-inline-container');
    var ute = $wrap.find('.tta-mark-all-no-show').data('event-ute-id');
    if(status === 'no_show'){
      var msg = 'Are you sure you want to mark this person as a no-show? If this is their fifth no-show, this member will be automatically banned until they purchase a Re-entry Ticket. They will be emailed with further instructions if you proceed.';
      if(!window.confirm(msg)) return;
    }
    $.post(TTA_Checkin.ajax_url, { action:'tta_set_attendance', nonce:TTA_Checkin.set_nonce, attendee_id:id, status:status }, function(res){
      if(!res.success) return;
      if(res.data && res.data.reload){
        var url = new URL(window.location.href);
        if(ute){ url.searchParams.set('event', ute); }
        window.location.href = url.toString();
        return;
      }
      var label = $btn.closest('tr').find('.status-label');
      var text  = status.replace('_',' ');
      text = text.replace(/\b\w/g, function(c){ return c.toUpperCase(); });
      label.text(text);
      $btn.closest('td').find('.tta-mark-attendance').prop('disabled', true).addClass('disabled');
      if(res.data){
        var $col = $btn.closest('tr').find('td').eq(2);
        var atLab = TTA_Checkin.attendance_label;
        var evLab = TTA_Checkin.attended_label;
        var nsLab = TTA_Checkin.noshow_label;
        $col.html('<span class="tta-info-title">'+atLab+'</span>'+res.data.attended+' '+evLab+', '+res.data.no_show+' '+nsLab);
      }
    }, 'json');
  });

  // Mark all pending attendees as no-shows
  $(document).on('click', '.tta-mark-all-no-show', function(e){
    e.preventDefault();
    var $btn  = $(this),
        ute   = $btn.data('event-ute-id'),
        $wrap = $btn.closest('.tta-no-show-actions'),
        $spin = $wrap.find('.tta-admin-progress-spinner-svg'),
        $resp = $wrap.find('.tta-admin-progress-response-p');
    var msg = 'Are you SURE you want to mark everyone that has a current status of "Pending" as a "No-Show"? This cannot be undone. If doing this gives a member their fifth No-Show, that member will be automatically banned until they purchase a Re-entry Ticket. Those members will be emailed with further instructions if you proceed.';
    if(!window.confirm(msg)) return;
    $resp.text('');
    $spin.css({display:'inline-block',opacity:0}).fadeTo(200,1);
    $.post(TTA_Checkin.ajax_url, { action:'tta_mark_pending_no_show', nonce:TTA_Checkin.set_nonce, event_ute_id: ute }, function(res){
      $spin.fadeOut(200);
      if(!res.success){
        $resp.text(res.data && res.data.message ? res.data.message : 'Error');
        return;
      }
      $resp.text('Updated.');
      $.post(TTA_Checkin.ajax_url, { action:'tta_get_event_attendance', nonce:TTA_Checkin.get_nonce, event_ute_id: ute }, function(r){
        if(!r.success) return;
        var $target = $btn.closest('.tta-inline-wrapper');
        if(!$target.length){ $target = $btn.closest('.tta-inline-container'); }
        $target.html(r.data.html);
        initEmailPanels($target);
      }, 'json');
    }, 'json');
  });

  // Email all attendees from the check-in view
  $(document).on('click', '.tta-email-attendees__send', function(e){
    e.preventDefault();
    var $btn   = $(this);
    if ($btn.prop('disabled')) { return; }
    var $wrap  = $btn.closest('.tta-email-attendees');
    var $text  = $wrap.find('.tta-email-attendees__message');
    var message = $text.val() || '';
    var trimmed = $.trim(message);
    var ute    = $btn.data('event-ute-id');
    var $spin  = $wrap.find('.tta-progress-spinner .tta-admin-progress-spinner-svg');
    var $resp  = $wrap.find('.tta-admin-progress-response-p');
    var lengthNormalized = normalizedLength(message);

    if (!trimmed.length) {
      $resp.text(TTA_Checkin.email_required || 'Please type a message before sending.');
      $text.focus();
      return;
    }

    if (lengthNormalized < minLength) {
      $resp.text(minLengthMessage);
      $text.focus();
      return;
    }

    $resp.text('');
    $spin.css({display:'inline-block',opacity:0}).fadeTo(200,1);
    $btn.prop('disabled', true).addClass('disabled');
    $.ajax({
      url: TTA_Checkin.ajax_url,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'tta_email_event_attendees',
        nonce: TTA_Checkin.email_nonce,
        event_ute_id: ute,
        message: message
      }
    }).done(function(res){
      if (res && res.success) {
        var success = (res.data && res.data.message) ? res.data.message : (TTA_Checkin.email_success || 'Email sent to all attendees.');
        $resp.text(success);
        $text.val('').trigger('input');
      } else {
        var error = (res && res.data && res.data.message) ? res.data.message : (TTA_Checkin.email_failed || 'Unable to send the email. Please try again.');
        $resp.text(error);
      }
    }).fail(function(){
      $resp.text(TTA_Checkin.email_failed || 'Unable to send the email. Please try again.');
    }).always(function(){
      $spin.fadeOut(200);
      refreshEmailButton($wrap);
    });
  });

  $(window).on('load', function(){
    var params = new URLSearchParams(window.location.search);
    var open   = params.get('event');
    if(open){
      var $row = $('.tta-event-row[data-event-ute-id="'+open+'"]');
      if($row.length){
        $('html,body').animate({scrollTop:$row.offset().top-20},400,function(){
          $row.trigger('click');
        });
      }
    }
  });

  initEmailPanels($(document));
});
