jQuery(function($){
  // Toggle event rows
  $(document).on('click', '.tta-event-row', function(e){
    if ($(e.target).is('button, a')) return;
    var $row   = $(this),
        $arrow = $row.find('.tta-toggle-arrow'),
        ute    = $row.data('event-ute-id'),
        $ex    = $row.next('.tta-inline-row');

    if ($ex.length){
      $arrow.removeClass('open');
      $ex.remove();
      return;
    }

    $('.tta-inline-row').remove();
    $('.tta-toggle-arrow').removeClass('open');

    $.post(TTA_Checkin.ajax_url, { action:'tta_get_event_attendance', nonce:TTA_Checkin.get_nonce, event_ute_id: ute }, function(res){
      if(!res.success) return;
      var colspan = $row.find('td').length;
      var $new = $('<tr class="tta-inline-row"><td colspan="'+colspan+'">'+res.data.html+'</td></tr>');
      $row.after($new);
      $arrow.addClass('open');
    }, 'json');
  });

  // Set attendance status
  $(document).on('click', '.tta-mark-attendance', function(e){
    e.preventDefault();
    var $btn = $(this), id = $btn.data('attendee-id'), status = $btn.data('status');
    $.post(TTA_Checkin.ajax_url, { action:'tta_set_attendance', nonce:TTA_Checkin.set_nonce, attendee_id:id, status:status }, function(res){
      if(!res.success) return;
      var label = $btn.closest('tr').find('.status-label');
      label.text(status.replace('_',' ').toUpperCase());
    }, 'json');
  });
});
