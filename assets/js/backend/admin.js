jQuery(function($){

  //
  // Create New Event
  //
  var $createForm = $('#tta-event-form');
  if ( $createForm.length ) {
    $createForm.on('submit', function(e){
      e.preventDefault();

      // Show spinner
      $('.tta-admin-progress-spinner-svg').css({ opacity: 1, display: 'inline-block' });
      // Clear previous response text
      $('.tta-admin-progress-response-p').text('');

      var $btn = $createForm.find('.submit .button-primary').prop('disabled', true);
      var data = $createForm.serialize()
               + '&action=tta_save_event'
               + '&tta_event_save_nonce=' + TTA_Ajax.save_event_nonce;

      $.post(TTA_Ajax.ajax_url, data, function(res){
        // Artificial 5-second delay before showing the response
        setTimeout(function(){
          // Hide spinner
          $('.tta-admin-progress-spinner-svg').fadeOut(200);

          // Inject response message into our designated <p>
          var cls = res.success ? 'updated' : 'error';
          var msg = res.data.message || 'Unknown error';
          var $resp = $('.tta-admin-progress-response-p')
            .removeClass('updated error')
            .addClass(cls);

          // if a page_url was returned, append it as an <a>
          if ( res.data.page_url ) {
            $resp.html(msg);
          } else {
            $resp.text(msg);
          }

          // If success and returned a new ID, prepend hidden field
          if ( res.success && res.data.id && !$createForm.find('input[name="tta_event_id"]').length ) {
            $createForm.prepend('<input type="hidden" name="tta_event_id" value="' + res.data.id + '">');
          }

          $btn.prop('disabled', false);
        }, 5000);
      }, 'json')
      .fail(function(){
        // Even on AJAX error, wait 5 seconds before hiding spinner & showing “failed”
        setTimeout(function(){
          $('.tta-admin-progress-spinner-svg').fadeOut(200);
          var $resp = $('.tta-admin-progress-response-p');
          $resp
            .removeClass('updated')
            .addClass('error')
            .text('Request failed.');
          $btn.prop('disabled', false);
        }, 5000);
      });
    });
  }

  //
  // Create New Member
  //
  var $memberForm = $('#tta-member-form');
  if ( $memberForm.length ) {
    $memberForm.on('submit', function(e){
      e.preventDefault();

      // Before doing anything, verify that the two email fields match
      var email        = $('#email').val().trim();
      var emailConfirm = $('#email_verify').val().trim();
      if ( email !== emailConfirm ) {
        // Show error and abort
        var $resp = $('.tta-admin-progress-response-p')
          .removeClass('updated')
          .addClass('error')
          .text('Whoops! The email addresses do not match. Please correct and try again.');
        return;
      }

      // Show spinner
      $('.tta-admin-progress-spinner-svg').css({ opacity: 1, display: 'inline-block' });
      // Clear previous response text
      $('.tta-admin-progress-response-p').text('');

      var $btn = $memberForm.find('.submit .button-primary').prop('disabled', true);
      var data = $memberForm.serialize()
               + '&action=tta_save_member'
               + '&tta_member_save_nonce=' + TTA_Ajax.save_member_nonce;

      $.post(TTA_Ajax.ajax_url, data, function(res){
        // Artificial 5-second delay before showing the response
        setTimeout(function(){
          // Hide spinner
          $('.tta-admin-progress-spinner-svg').fadeOut(200);

          // Inject response message into our designated <p>
          var cls = res.success ? 'updated' : 'error';
          var msg = res.data.message || 'Unknown error';
          var $resp = $('.tta-admin-progress-response-p')
            .removeClass('updated error')
            .addClass(cls)
            .html(msg);

          $btn.prop('disabled', false);
        }, 5000);
      }, 'json')
      .fail(function(){
        // Even on AJAX error, wait 5 seconds before hiding spinner & showing “failed”
        setTimeout(function(){
          $('.tta-admin-progress-spinner-svg').fadeOut(200);
          var $resp = $('.tta-admin-progress-response-p');
          $resp
            .removeClass('updated')
            .addClass('error')
            .text('Request failed.');
          $btn.prop('disabled', false);
        }, 5000);
      });
    });
  }

  //
  // Inline Edit: fetch & inject an edit form when clicking a row (Events)
  //
  $(document).on('click', '.widefat tbody tr[data-event-id]', function(e){
    // Don’t trigger if clicking inside controls
    if ($(e.target).is('a, button, input, textarea, select')) {
      return;
    }

    var $row      = $(this);
    var $arrow    = $row.find('.tta-toggle-arrow');
    var eventId   = $row.data('event-id');
    var colspan   = $row.find('td').length;
    var $existing = $row.next('.tta-inline-row');

    // If already open, close it
    if ( $existing.length ) {
      $arrow.removeClass('open');
      $existing.find('.tta-inline-container').fadeOut(200, function(){
        $existing.remove();
      });
      return;
    }

    // Otherwise close any other open form first
    $('.tta-inline-row').each(function(){
      var $otherRow = $(this).prev('tr');
      $otherRow.find('.tta-toggle-arrow').removeClass('open');
      $(this).find('.tta-inline-container').fadeOut(200, function(){
        $(this).closest('.tta-inline-row').remove();
      });
    });

    // Start arrow animation immediately
    $arrow.addClass('open');

    // Fetch the pre-populated edit form HTML for this event
    $.post(TTA_Ajax.ajax_url, {
      action:          'tta_get_event_form',
      event_id:        eventId,
      get_event_nonce: TTA_Ajax.get_event_nonce
    }, function(res){
      if ( ! res.success ) {
        console.error('Event fetch failed', res.data && res.data.message);
        return;
      }
      var html = res.data.html;

      // Build a new row with a hidden container, then fade it in
      var $newRow = $(
        '<tr class="tta-inline-row">' +
          '<td colspan="' + colspan + '">' +
            '<div class="tta-inline-container" style="display:none;"></div>' +
          '</td>' +
        '</tr>'
      );
      $row.after($newRow);

      // Insert HTML & fadeIn
      var $container = $newRow.find('.tta-inline-container');
      $container.html(html).fadeIn(200, function(){
        // After fadeIn completes, scroll viewport so this row is in view,
        // leaving 120px above it so the trigger row stays visible.
        var offset = $newRow.offset().top;
        $('html, body').animate({ scrollTop: offset - 120 }, 300);

        //
        // At this point, the HTML (including the `wp_editor()` textarea) is in the DOM.
        // Now we must explicitly initialize TinyMCE + Quicktags on that textarea ID.
        //
        if ( typeof wp !== 'undefined' && typeof wp.editor !== 'undefined' ) {
          // If TinyMCE was previously initialized on this ID, remove it first:
          try {
            wp.editor.remove('tta_event_description');
          } catch(err) {
            // ignore if not already initialized
          }

          // Then initialize the full editor, matching the Create‐Page configuration:
          wp.editor.initialize('tta_event_description', {
            tinymce: {
              wpautop: true,
              toolbar1: [
                'formatselect',
                'bold',
                'italic',
                'bullist',
                'numlist',
                'blockquote',
                'alignleft',
                'aligncenter',
                'alignright',
                'alignjustify',
                'link',
                'table',
                'fullscreen',
                'wp_adv',
                'styleselect',
                'shortcodes'
              ].join(','),
              toolbar2: [
                'strikethrough',
                'hr',
                'forecolor',
                'pastetext',
                'pasteword',
                'removeformat',
                'charmap',
                'ltr',
                'rtl',
                'undo',
                'redo',
                'help',
                'fontsizeselect'
              ].join(','),
              toolbar3: '',
              toolbar4: '',
              block_formats:
                'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6'
            },
            mediaButtons: true,
            quicktags: true
          });
        }
      });
    }, 'json');
  });

  //
  // Also open inline edit when clicking the Edit link
  //
  $(document).on('click', '.tta-edit-link', function(e){
    e.preventDefault();
    $(this).closest('tr').trigger('click');
  });

  //
  // Update Existing Event (delegate to injected form)
  //
  $(document).on('submit', '#tta-event-edit-form', function(e){
    e.preventDefault();
    var $form = $(this);

    // Show spinner
    $('.tta-admin-progress-spinner-svg').css({ opacity: 1, display: 'inline-block' });
    // Clear previous response text
    $('.tta-admin-progress-response-p').text('');

    var $btn  = $form.find('.submit .button-primary').prop('disabled', true);

    // Rebuild userids before serializing
    rebuildUserids();

    var data = $form.serialize()
             + '&action=tta_update_event'
             + '&tta_event_save_nonce=' + TTA_Ajax.save_event_nonce;

    $.post(TTA_Ajax.ajax_url, data, function(res){
      // Artificial 5-second delay before showing the response
      setTimeout(function(){
        // Hide spinner
        $('.tta-admin-progress-spinner-svg').fadeOut(200);

        // Display response under the spinner
        var cls = res.success ? 'updated' : 'error';
        var msg = res.data.message || 'Unknown error';
        var $resp = $('.tta-admin-progress-response-p')
          .removeClass('updated error')
          .addClass(cls);

        // if a page_url was returned, append it as an <a>
        if ( res.data.page_url ) {
          $resp.html(msg);
        } else {
          $resp.text(msg);
        }

        $btn.prop('disabled', false);
      }, 5000);
    }, 'json')
    .fail(function(){
      // Even on AJAX error, wait 5 seconds before hiding spinner & showing “failed”
      setTimeout(function(){
        $('.tta-admin-progress-spinner-svg').fadeOut(200);
        var $resp = $('.tta-admin-progress-response-p');
        $resp
          .removeClass('updated')
          .addClass('error')
          .text('Request failed.');
        $btn.prop('disabled', false);
      }, 5000);
    });
  });

  //
  // Remove a waitlist entry
  //
  $(document).on('click', '.tta-remove-waitlist', function(e){
    e.preventDefault();
    $(this).closest('.tta-waitlist-entry').remove();
    rebuildUserids();
  });



  //
  // Rebuild the CSV of user IDs and store in a hidden input
  //
  function rebuildUserids(){
    var uids = [];
    $('#tta-waitlist-container .tta-waitlist-entry').each(function(){
      uids.push( $(this).data('userid') );
    });
    var csv = uids.join(',');
    var $form = $('#tta-event-edit-form');
    var $input = $form.find('input[name="userids"]');
    if ( ! $input.length ) {
      $input = $('<input>')
        .attr({ type: 'hidden', name: 'userids' })
        .appendTo( $form );
    }
    $input.val(csv);
  }


  // ─────────────────────────────────────────────────────────────────────
  // Inline Edit for “Manage Members” rows
  //
  $(document).on('click', '.widefat tbody tr[data-member-id]', function(e){
    // Don’t trigger if clicking inside controls
    if ( $(e.target).is('a, button, input, textarea, select') ) {
      return;
    }

    var $row      = $(this);
    var $arrow    = $row.find('.tta-toggle-arrow');
    var memberId  = $row.data('member-id');
    var colspan   = $row.find('td').length;
    var $existing = $row.next('.tta-inline-row');

    // If already open, close it
    if ( $existing.length ) {
      $arrow.removeClass('open');
      $existing.find('.tta-inline-container').fadeOut(200, function(){
        $existing.remove();
      });
      return;
    }

    // Otherwise close any other open inline‐edit form
    $('.tta-inline-row').each(function(){
      var $otherRow = $(this).prev('tr');
      $otherRow.find('.tta-toggle-arrow').removeClass('open');
      $(this).find('.tta-inline-container').fadeOut(200, function(){
        $(this).closest('.tta-inline-row').remove();
      });
    });

    // Start arrow animation immediately
    $arrow.addClass('open');

    // Fetch the pre-populated edit form HTML for this member
    $.post(TTA_Ajax.ajax_url, {
      action:       'tta_get_member_form',
      member_id:    memberId,
      get_member_nonce: TTA_Ajax.get_member_nonce
    }, function(res){
      if ( ! res.success ) {
        console.error('Member fetch failed', res.data && res.data.message);
        return;
      }
      var html = res.data.html;

      // Build a new row with a hidden container, then fade it in
      var $newRow = $(
        '<tr class="tta-inline-row">' +
          '<td colspan="' + colspan + '">' +
            '<div class="tta-inline-container" style="display:none;"></div>' +
          '</td>' +
        '</tr>'
      );
      $row.after($newRow);

      // Insert HTML & fadeIn
      var $container = $newRow.find('.tta-inline-container');
      $container.html(html).fadeIn(200, function(){
        // After fadeIn completes, scroll viewport so this row is in view,
        // leaving 120px above it so the trigger row stays visible.
        var offset = $newRow.offset().top;
        $('html, body').animate({ scrollTop: offset - 120 }, 300);

        //
        // If you added any WP editor areas inside members‐edit.php (e.g. a Biography WYSIWYG),
        // you would initialize them here, just like for events.
        //
        if ( typeof wp !== 'undefined' && typeof wp.editor !== 'undefined' ) {
          try {
            wp.editor.remove('biography_edit');
          } catch(err) {
            // ignore if not already initialized
          }
          // If you wanted a full TinyMCE field for bio, you’d do something like:
          // wp.editor.initialize('biography_edit', { tinymce: {...}, quicktags: true, mediaButtons: true });
        }
      });
    }, 'json');
  });


  //
  // Also open inline edit when clicking the “Edit” link in the row
  //
  $(document).on('click', '.tta-edit-link', function(e){
    e.preventDefault();
    $(this).closest('tr').trigger('click');
  });


  //
  // Update Existing Member (delegate to injected form)
  //
  $(document).on('submit', '#tta-member-edit-form', function(e){
    // Before doing anything, verify that the two email fields match
    var email        = $('#email_edit').val().trim();
    var emailConfirm = $('#email_verify_edit').val().trim();
    if ( email !== emailConfirm ) {
      e.preventDefault();
      $('.tta-admin-progress-response-p')
        .removeClass('updated')
        .addClass('error')
        .text('Whoops! The email addresses do not match. Please correct and try again.');
      return;
    }

    e.preventDefault();
    var $form = $(this);

    // Show spinner
    $('.tta-admin-progress-spinner-svg').css({ opacity: 1, display: 'inline-block' });
    // Clear previous response text
    $('.tta-admin-progress-response-p').text('');

    var $btn  = $form.find('.submit .button-primary').prop('disabled', true);

    // Update existing member
    var data = $form.serialize()
             + '&action=tta_update_member'
             + '&tta_member_update_nonce=' + TTA_Ajax.update_member_nonce;

    $.post(TTA_Ajax.ajax_url, data, function(res){
      // Artificial 5-second delay before showing the response
      setTimeout(function(){
        // Hide spinner
        $('.tta-admin-progress-spinner-svg').fadeOut(200);

        // Display response under the spinner
        var cls = res.success ? 'updated' : 'error';
        var msg = res.data.message || 'Unknown error';
        var $resp = $('.tta-admin-progress-response-p')
          .removeClass('updated error')
          .addClass(cls)
          .html(msg);

        $btn.prop('disabled', false);
      }, 5000);
    }, 'json')
    .fail(function(){
      // Even on AJAX error, wait 5 seconds before hiding spinner & showing “failed”
      setTimeout(function(){
        $('.tta-admin-progress-spinner-svg').fadeOut(200);
        var $resp = $('.tta-admin-progress-response-p');
        $resp
          .removeClass('updated')
          .addClass('error')
          .text('Request failed.');
        $btn.prop('disabled', false);
      }, 5000);
    });
  });


  //
  // Add new “Interests” field in inline‐edit
  //
  $(document).on('click', '#add-interest-edit', function(e){
    e.preventDefault();

    var $container = $('#interests-container');
    var count = $container.find('input.interest-field').length + 1;

    // Create the new input
    var $input = $('<input>', {
      type: 'text',
      name: 'interests[]',
      class: 'regular-text interest-field',
      placeholder: 'Interest #' + count
    });

    // Create the delete button
    var $button = $('<button>', {
      type: 'button',
      class: 'delete-interest',
      'aria-label': 'Remove this interest',
      style: 'background:none;border:none;cursor:pointer;margin-left:8px;'
    }).append(
      $('<img>', {
        src: 'http://trying-to-adult-rva-2025.local/wp-content/plugins/tta-management-plugin/assets/images/admin/bin.svg',
        alt: '×',
        style: 'width:16px;height:16px;'
      })
    );

    // Wrap input + button in a container div (optional, but keeps things tidy)
    var $entry = $('<div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;"></div>')
      .append($input)
      .append($button);

    // Append a line break for spacing, then our entry
    $container.append($entry);
  });

  // Add new "Hosts" field in edit form
  $(document).on('click', '#add-host-edit', function(e){
    e.preventDefault();
    var $container = $('#hosts-container');
    var count = $container.find('input.host-field').length + 1;
    var $input = $('<input>', {type:'text', name:'hosts[]', class:'regular-text host-field', list:'tta-member-options', placeholder:'Host #' + count});
    var $btn = $('<button>', {type:'button', class:'delete-interest', 'aria-label':'Remove', style:'background:none;border:none;cursor:pointer;margin-left:8px;'}).append(
      $('<img>', {src:'http://trying-to-adult-rva-2025.local/wp-content/plugins/tta-management-plugin/assets/images/admin/bin.svg', alt:'×', style:'width:16px;height:16px;'}));
    var $entry = $('<div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;"></div>').append($input).append($btn);
    $container.append($entry);
  });

  // Add new "Volunteers" field in edit form
  $(document).on('click', '#add-volunteer-edit', function(e){
    e.preventDefault();
    var $container = $('#volunteers-container');
    var count = $container.find('input.volunteer-field').length + 1;
    var $input = $('<input>', {type:'text', name:'volunteers[]', class:'regular-text volunteer-field', list:'tta-member-options', placeholder:'Volunteer #' + count});
    var $btn = $('<button>', {type:'button', class:'delete-interest', 'aria-label':'Remove', style:'background:none;border:none;cursor:pointer;margin-left:8px;'}).append(
      $('<img>', {src:'http://trying-to-adult-rva-2025.local/wp-content/plugins/tta-management-plugin/assets/images/admin/bin.svg', alt:'×', style:'width:16px;height:16px;'}));
    var $entry = $('<div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;"></div>').append($input).append($btn);
    $container.append($entry);
  });




//
  // Add new “Interests” field in inline‐edit
  //
  $(document).on('click', '#add-interest', function(e){
    e.preventDefault();

    var $container = $('#interests-container');
    var count = $container.find('input.interest-field').length + 1;

    // Create the new input
    var $input = $('<input>', {
      type: 'text',
      name: 'interests[]',
      class: 'regular-text interest-field',
      placeholder: 'Interest #' + count
    });

    // Create the delete button
    var $button = $('<button>', {
      type: 'button',
      class: 'delete-interest',
      'aria-label': 'Remove this interest',
      style: 'background:none;border:none;cursor:pointer;margin-left:8px;'
    }).append(
      $('<img>', {
        src: 'http://trying-to-adult-rva-2025.local/wp-content/plugins/tta-management-plugin/assets/images/admin/bin.svg',
        alt: '×',
        style: 'width:16px;height:16px;'
      })
    );

    // Wrap input + button in a container div (optional, but keeps things tidy)
    var $entry = $('<div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;"></div>')
      .append($input)
      .append($button);

    // Append a line break for spacing, then our entry
    $container.append($entry);
  });

  // Add host field on create form
  $(document).on('click', '#add-host', function(e){
    e.preventDefault();
    var $c = $('#hosts-container');
    var count = $c.find('input.host-field').length + 1;
    var $input = $('<input>', {type:'text', name:'hosts[]', class:'regular-text host-field', list:'tta-member-options', placeholder:'Host #' + count});
    var $btn = $('<button>', {type:'button', class:'delete-interest', 'aria-label':'Remove', style:'background:none;border:none;cursor:pointer;margin-left:8px;'}).append(
      $('<img>', {src:'http://trying-to-adult-rva-2025.local/wp-content/plugins/tta-management-plugin/assets/images/admin/bin.svg', alt:'×', style:'width:16px;height:16px;'}));
    var $entry = $('<div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;"></div>').append($input).append($btn);
    $c.append($entry);
  });

  // Add volunteer field on create form
  $(document).on('click', '#add-volunteer', function(e){
    e.preventDefault();
    var $c = $('#volunteers-container');
    var count = $c.find('input.volunteer-field').length + 1;
    var $input = $('<input>', {type:'text', name:'volunteers[]', class:'regular-text volunteer-field', list:'tta-member-options', placeholder:'Volunteer #' + count});
    var $btn = $('<button>', {type:'button', class:'delete-interest', 'aria-label':'Remove', style:'background:none;border:none;cursor:pointer;margin-left:8px;'}).append(
      $('<img>', {src:'http://trying-to-adult-rva-2025.local/wp-content/plugins/tta-management-plugin/assets/images/admin/bin.svg', alt:'×', style:'width:16px;height:16px;'}));
    var $entry = $('<div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;"></div>').append($input).append($btn);
    $c.append($entry);
  });
























  // Delegate click on delete-interest to remove its entry
  $(document).on('click', '.delete-interest', function(e){
    e.preventDefault();
    var $entry = $(this).closest('.interest-item');
    // Remove the <br> immediately before, if present
    $entry.prev('br').remove();
    $entry.remove();
  });


  // Prevent submission if emails don't match
  $('#tta-member-edit-form').on('submit', function(e){
      var email       = $('#email_edit').val().trim();
      var emailVerify = $('#email_verify_edit').val().trim();
      if ( email !== emailVerify ) {
          e.preventDefault();
          $('.tta-admin-progress-response-p')
              .removeClass('updated')
              .addClass('error')
              .text('Whoops! The email addresses do not match. Please correct and try again.');
      }
  });


  //
  // Basic phone-number formatting mask in inline‐edit
  //
  $(document).on('input', '#phone_edit', function(){
    var val = $(this).val().replace(/\D/g, '');
    if ( val.length > 3 && val.length <= 6 ) {
      val = '(' + val.slice(0,3) + ') ' + val.slice(3);
    } else if ( val.length > 6 ) {
      val = '(' + val.slice(0,3) + ') ' + val.slice(3,6) + '-' + val.slice(6,10);
    }
    $(this).val( val );
  });

  // Basic phone-number formatting mask
  $('#phone').on('input', function(){
      var val = $(this).val().replace(/\D/g, '');
      if (val.length > 3 && val.length <= 6) {
          val = '(' + val.slice(0,3) + ') ' + val.slice(3);
      } else if (val.length > 6) {
          val = '(' + val.slice(0,3) + ') ' + val.slice(3,6) + '-' + val.slice(6,10);
      }
      $(this).val(val);
  });



// ── Inline Edit for Tickets ──────────────────────────────────────────────
  // Expand an event row to show its tickets
  $(document).on('click', '.widefat tbody tr[data-event-ute-id]', function(e){
    // ignore clicks on links/buttons inside
    if ($(e.target).is('a, button, img')) return;

    var $row    = $(this),
        ute     = $row.data('event-ute-id'),
        $arrow  = $row.find('.tta-toggle-arrow'),
        colspan = $row.find('td').length,
        $ex     = $row.next('.tta-inline-row');

    // toggle close if already open
    if ($ex.length) {
      $arrow.removeClass('open');
      $ex.remove();
      return;
    }

    // close any other open
    $('.tta-inline-row').remove();
    $('.tta-toggle-arrow').removeClass('open');

    $arrow.addClass('open');

    $.post( TTA_Ajax.ajax_url, {
      action           : 'tta_get_ticket_form',   
      event_ute_id     : ute,
      get_ticket_nonce : TTA_Ajax.get_ticket_nonce
    }, function(res){
      if ( ! res.success ) return console.error(res);
      var $new = $('<tr class="tta-inline-row"><td colspan="'+colspan+'"><div class="tta-inline-container"></div></td></tr>');
      $row.after($new);
      $new.find('.tta-inline-container').html(res.data.html).slideDown(200);
    }, 'json');
  });


  // also open on clicking “Edit”
  $(document).on('click', '.tta-edit-ticket', function(e){
    e.preventDefault();
    $(this).closest('tr').trigger('click');
  });

  // ── Handle ticket‐save (delegate) ────────────────────────────────────────
  $(document).on('submit','#tta-ticket-edit-form',function(e){
    e.preventDefault();

    $('#tta-ticket-edit-form .tta-ticket-row').each(function(){
      var uids = [];
      // collect each remaining waitlist-entry's userid
      $(this).find('.tta-wl-entry[data-userid]').each(function(){
        uids.push( $(this).data('userid') );
      });
      // write it into the single hidden input in this row
      $(this).find('input.tta-hidden-waitlist').val( uids.join(',') );
    });



    var $form = $(this),
        $btn  = $form.find('button[type=submit]').prop('disabled',true),
        $spin = $form.find('.tta-admin-progress-spinner-svg').css({display:'inline-block',opacity:0}).fadeTo(200,1),
        data  = $form.serialize() 
              + '&action=tta_update_ticket'
              + '&tta_ticket_save_nonce=' + TTA_Ajax.save_ticket_nonce;

              console.log($form);

    $.post(TTA_Ajax.ajax_url, data, function(res){
      setTimeout(function(){
        $spin.fadeTo(200,0,function(){ $(this).hide(); });
        var cls = res.success ? 'updated':'error',
            msg = res.data.message||'Error saving ticket';
        $form.find('.tta-admin-progress-response-p')
             .removeClass('updated error')
             .addClass(cls)
             .text(msg);
        $btn.prop('disabled',false);
      },5000);
    },'json')
    .fail(function(){
      setTimeout(function(){
        $spin.fadeTo(200,0,function(){ $(this).hide(); });
        $form.find('.tta-admin-progress-response-p')
             .removeClass('updated')
             .addClass('error')
             .text('Request failed.');
        $btn.prop('disabled',false);
      },5000);
    });
  });

  // Add New Ticket (with incrementing number)
  $(document).on('click', '#add-new-ticket', function(){
    var $form = $('#tta-ticket-edit-form');

    // Count existing “New Ticket” entries
    var existingNew = $form.find('h3').filter(function(){
      return $(this).text().trim().indexOf('New Ticket') === 0;
    }).length;
    var index = existingNew + 1;

    // Clone the template
    var $tpl = $('#tta-new-ticket-template').contents().clone();



    // Clear any inputs
    $tpl.find('input').val('');

    // Insert before the submit buttons
    $(this).closest('p.submit').before($tpl);
  });

  // Remove a newly added ticket & re-number
  $(document).on('click', '.tta-delete-new-ticket', function(){
    // Remove this ticket block
    $(this).closest('.tta-ticket-row').remove();

    // Find *only* the New-Ticket headings (those with a delete button)
    var $newH3s = $('#tta-ticket-edit-form .tta-ticket-row h3').filter(function(){
      return $(this).find('.tta-delete-new-ticket').length;
    });

    
  });

    // Remove a newly added ticket & re-number
  $(document).on('click', '.tta-delete-ticket', function(){
    // Remove this ticket block
    $(this).closest('.tta-ticket-row').remove();

    // Find *only* the New-Ticket headings (those with a delete button)
    var $newH3s = $('#tta-ticket-edit-form .tta-ticket-row h3').filter(function(){
      return $(this).find('.tta-delete-ticket').length;
    });


  });


  // ─────────────────────────────────────────────────────────────────────
  // Remove a waitlist entry in the Tickets edit form
  // ─────────────────────────────────────────────────────────────────────
  $(document).on('click', '.tta-remove-waitlist-entry', function(e){
    e.preventDefault();
    // simply drop the entire entry block
    $(this).closest('.tta-wl-entry').remove();
  });

  // Simple accordion toggle used on Email & SMS admin page
  $(document).on('click', '.tta-admin-accordion .tta-accordion-toggle', function(){
    var $btn  = $(this),
        $cont = $btn.siblings('.tta-accordion-content'),
        openText  = $btn.data('openText') || 'Edit',
        closeText = $btn.data('closeText') || 'Hide';

    if ( $cont.hasClass('expanded') ) {
      $cont.removeClass('expanded');
      $btn.text(openText);
    } else {
      $cont.addClass('expanded');
      $btn.text(closeText);
    }
  });






























});/* end jQuery(function($) ) */
