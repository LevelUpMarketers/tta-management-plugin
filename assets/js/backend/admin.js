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

    // Determine data source (events or archive)
    var source = $row.data('source') || 'events';

    // Fetch the pre-populated edit form HTML for this event
    $.post(TTA_Ajax.ajax_url, {
      action:          'tta_get_event_form',
      event_id:        eventId,
      source:          source,
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
        if ( $('#tta_event_description').length && typeof wp !== 'undefined' && typeof wp.editor !== 'undefined' ) {
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

  // Email Logs tab
  var $logs = $('#tta-email-logs');
  if ($logs.length) {
    $logs.on('click', '.tta-email-log-event', function(){
      $(this).next('.tta-email-log-details').toggle();
      $(this).find('.tta-toggle-arrow').toggleClass('open');
    });

    $logs.on('click', '.tta-email-log-list', function(e){
      e.preventDefault();
      var $btn = $(this);
      $.post(TTA_Ajax.ajax_url, {
        action: 'tta_email_log_recipients',
        nonce: TTA_Ajax.email_logs_nonce,
        event_id: $btn.data('event'),
        hook: $btn.data('hook')
      }, function(res){
        if(res.success){
          alert(res.data.join('\n'));
        }
      });
    });

    $logs.on('click', '.tta-email-log-delete', function(e){
      e.preventDefault();
      if(!confirm('Delete this scheduled email?')) return;
      var $btn = $(this), row=$btn.closest('tr');
      $.post(TTA_Ajax.ajax_url, {
        action: 'tta_email_log_delete',
        nonce: TTA_Ajax.email_logs_nonce,
        event_id: $btn.data('event'),
        hook: $btn.data('hook'),
        template: $btn.data('template')
      }, function(res){
        if(res.success){ row.remove(); }
      });
    });

    function pad(num){ return (num < 10 ? '0' : '') + num; }
    setInterval(function(){
      $logs.find('.tta-countdown').each(function(){
        var $el = $(this);
        var remain = parseInt($el.data('remaining'), 10);
        if (isNaN(remain)) { return; }
        remain = Math.max(0, remain - 1);
        $el.data('remaining', remain);
        var hours = Math.floor(remain / 3600);
        var minutes = Math.floor((remain % 3600) / 60);
        var seconds = remain % 60;
        $el.text(pad(hours) + ' H, ' + pad(minutes) + ' M, ' + pad(seconds) + ' S');
      });
    }, 1000);
  }

  // Banned Members tab
  var $banned = $('#tta-banned-members');
  if ($banned.length) {
    $banned.on('click', '.tta-banned-member', function(){
      $(this).next('.tta-banned-details').toggle();
      $(this).find('.tta-toggle-arrow').toggleClass('open');
    });

    $banned.on('click', '.tta-banned-reinstate', function(e){
      e.preventDefault();
      if(!confirm('Reinstate this member?')) return;
      var $btn=$(this);
      $.post(TTA_Ajax.ajax_url, {
        action:'tta_reinstate_member',
        nonce:TTA_Ajax.banned_members_nonce,
        wp_user_id:$btn.data('user')
      }, function(res){
        if(res.success){
          var $details=$btn.closest('tr.tta-banned-details');
          $details.prev('.tta-banned-member').remove();
          $details.remove();
        }
      });
    });

    function pad(num){ return (num < 10 ? '0' : '') + num; }
    setInterval(function(){
      $banned.find('.tta-countdown').each(function(){
        var $el=$(this); var remain=parseInt($el.data('remaining'),10);
        if(isNaN(remain)) return;
        remain=Math.max(0,remain-1);
        $el.data('remaining',remain);
        var hours=Math.floor(remain/3600), minutes=Math.floor((remain%3600)/60), seconds=remain%60;
        $el.text(pad(hours)+' H, '+pad(minutes)+' M, '+pad(seconds)+' S');
      });
    },1000);
  }

  var $history = $('#tta-email-history');
  if ($history.length) {
    $history.on('click', '#tta-email-clear-log', function(e){
      e.preventDefault();
      if(!confirm('Clear email log?')) return;
      $.post(TTA_Ajax.ajax_url, {
        action: 'tta_email_clear_log',
        nonce: TTA_Ajax.email_log_clear_nonce
      }, function(res){
        if(res.success){
          $history.find('table tbody').empty();
        }
      });
    });
  }

  // Inline edit for Venues
  $(document).on('click', '#tta-venues-manage .widefat tbody tr[data-venue-id]', function(e){
    if($(e.target).is('a,button,input,textarea,select')) return;
    var $row=$(this), $arrow=$row.find('.tta-toggle-arrow');
    var id=$row.data('venue-id'), colspan=$row.find('td').length;
    var $existing=$row.next('.tta-inline-row');
    if($existing.length){ $arrow.removeClass('open'); $existing.remove(); return; }
    $('.tta-inline-row').remove(); $('.tta-toggle-arrow').removeClass('open');
    $arrow.addClass('open');
    $.post(TTA_Ajax.ajax_url,{action:'tta_get_venue_form',venue_id:id,get_venue_nonce:TTA_Ajax.get_venue_nonce},function(res){
      if(!res.success) return;
      var $new=$('<tr class="tta-inline-row"><td colspan="'+colspan+'"><div class="tta-inline-container" style="display:none;"></div></td></tr>');
      $row.after($new);
      var $c=$new.find('.tta-inline-container');
      $c.html(res.data.html).fadeIn(200); 
    },'json');
  });

  $(document).on('submit', '#tta-venue-edit-form', function(e){
    e.preventDefault();
    var $form=$(this);
    $('.tta-admin-progress-spinner-svg').css({opacity:1,display:'inline-block'});
    $('.tta-admin-progress-response-p').text('');
    var data=$form.serialize()+'&action=tta_update_venue'+'&tta_venue_save_nonce='+TTA_Ajax.save_venue_nonce;
    $.post(TTA_Ajax.ajax_url,data,function(res){
      $('.tta-admin-progress-spinner-svg').fadeOut(200);
      var $resp=$('.tta-admin-progress-response-p').removeClass('updated error').addClass(res.success?'updated':'error');
      $resp.text(res.data.message||'Error');
    },'json');
  });

  // Inline edit for Ads
  $(document).on('click', '#tta-ads-manage .widefat tbody tr[data-ad-id]', function(e){
    if($(e.target).is('a,button,input,textarea,select,img')) return;
    var $row=$(this), $arrow=$row.find('.tta-toggle-arrow');
    var id=$row.data('ad-id'), colspan=$row.find('td').length;
    var $existing=$row.next('.tta-inline-row');
    if($existing.length){ $arrow.removeClass('open'); $existing.remove(); return; }
    $('.tta-inline-row').remove(); $('.tta-toggle-arrow').removeClass('open');
    $arrow.addClass('open');
    $.post(TTA_Ajax.ajax_url,{action:'tta_get_ad_form',ad_id:id,get_ad_nonce:TTA_Ajax.get_ad_nonce},function(res){
      if(!res.success) return;
      var $new=$('<tr class="tta-inline-row"><td colspan="'+colspan+'"><div class="tta-inline-container" style="display:none;"></div></td></tr>');
      $row.after($new);
      var $c=$new.find('.tta-inline-container');
      $c.html(res.data.html).fadeIn(200);
    },'json');
  });

  $(document).on('submit', '#tta-ad-edit-form', function(e){
    e.preventDefault();
    var $form=$(this);
    $('.tta-admin-progress-spinner-svg').css({opacity:1,display:'inline-block'});
    $('.tta-admin-progress-response-p').text('');
    var data=$form.serialize()+'&action=tta_update_ad'+'&tta_ad_save_nonce='+TTA_Ajax.save_ad_nonce;
    $.post(TTA_Ajax.ajax_url,data,function(res){
      $('.tta-admin-progress-spinner-svg').fadeOut(200);
      var $resp=$('.tta-admin-progress-response-p').removeClass('updated error').addClass(res.success?'updated':'error');
      $resp.text(res.data.message||'Error');
    },'json');
  });

  //
  // Also open inline edit when clicking the Edit link
  //
  $(document).on('click', '.tta-edit-link', function(e){
    e.preventDefault();
    $(this).closest('tr').trigger('click');
  });

  // Expand member row to show history details
  $(document).on('click', '#tta-members-history .widefat tbody tr[data-member-id]', function(e){
    if ($(e.target).is('a, button, input, textarea, select')) return;

    var $row   = $(this),
        $arrow = $row.find('.tta-toggle-arrow'),
        id     = $row.data('member-id'),
        colsp  = $row.find('td').length,
        $ex    = $row.next('.tta-inline-row');

    if ($ex.length){
      $arrow.removeClass('open');
      $ex.remove();
      return;
    }

    $('.tta-inline-row').remove();
    $('.tta-toggle-arrow').removeClass('open');
    $arrow.addClass('open');

    var $spinner = $row.find('.tta-row-spinner').css({display:'inline-block',opacity:0}).fadeTo(200,1);
    $.post(TTA_Ajax.ajax_url, { action:'tta_get_member_history', member_id:id, get_member_nonce:TTA_Ajax.get_member_nonce }, function(res){
      $spinner.fadeTo(200,0,function(){ $(this).hide(); });
      if(!res.success) return;
      var $new = $('<tr class="tta-inline-row"><td colspan="'+colsp+'"><div class="tta-inline-container"></div></td></tr>');
      $row.after($new);
      var $container = $new.find('.tta-inline-container');
      $container.html(res.data.html).slideDown(200);
      $container.find('select[name="level"]').each(function(){
        var $priceInput = $(this).closest('form').find('input[name="price"], input[name="amount"]');
        if(!$priceInput.val()){
          syncLevelPrice($(this));
        }
      });
    }, 'json').fail(function(){ $spinner.fadeTo(200,0,function(){ $(this).hide(); }); });
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


  // ─────────────────────────────────────────────────────────────────────
  // Inline Edit for “Manage Members” rows
  //
  $(document).on('click', '#tta-members-manage .widefat tbody tr[data-member-id]', function(e){
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

  // Phone mask for Ads
  $(document).on('input', '#business_phone_edit', function(){
    var val = $(this).val().replace(/\D/g, '');
    if ( val.length > 3 && val.length <= 6 ) {
      val = '(' + val.slice(0,3) + ') ' + val.slice(3);
    } else if ( val.length > 6 ) {
      val = '(' + val.slice(0,3) + ') ' + val.slice(3,6) + '-' + val.slice(6,10);
    }
    $(this).val( val );
  });

  $('#business_phone').on('input', function(){
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
  var $row = $(this).closest('tr[data-waitlist-id], .tta-wl-entry');
  var id = $(this).data('waitlist-id');
  $.post(TTA_Ajax.ajax_url, {
    action: 'tta_remove_waitlist_entry',
    waitlist_id: id,
    nonce: TTA_Ajax.waitlist_admin_nonce
  }, function(res){
    if(res.success){
      $row.remove();
      alert(res.data && res.data.message ? res.data.message : 'Removed');
    }else{
      alert(res.data && res.data.message ? res.data.message : 'Error');
    }
  }, 'json');
});

  // ─────────────────────────────────────────────────────────────────────
  // Refund an attendee and optionally cancel attendance
  // ─────────────────────────────────────────────────────────────────────
  function handleRefund(e, mode){
    e.preventDefault();
    var id = $(e.currentTarget).data('attendee');
    var $btn = $(e.currentTarget);
    var $row = $btn.closest('tr[data-attendee-id]');
    var amount = $row.find('.tta-refund-amount').val();
    $.post(TTA_Ajax.ajax_url, {
      action: 'tta_refund_attendee',
      attendee_id: id,
      amount: amount,
      mode: mode,
      nonce: TTA_Ajax.attendee_admin_nonce
    }, function(res){
      if(res.success){
        if(res.data && res.data.pending){
          $btn.prop('disabled', true)
              .addClass('tta-disabled tta-tooltip-trigger')
              .attr('data-tooltip', 'Refund scheduled after settlement');
          alert(res.data && res.data.message ? res.data.message : 'Refund pending');
          return;
        }
        if(mode === 'cancel'){
          $row.remove();
          alert(res.data && res.data.message ? res.data.message : 'Refund processed');
          window.location.reload();
          return;
        }
        alert(res.data && res.data.message ? res.data.message : 'Refund processed');
      }else{
        alert(res.data && res.data.message ? res.data.message : 'Error');
      }
    }, 'json');
  }

  $(document).on('click', '.tta-refund-cancel-attendee', function(e){
    handleRefund(e, 'cancel');
  });

  $(document).on('click', '.tta-refund-keep-attendee', function(e){
    handleRefund(e, 'keep');
  });

  function handleCancel(e){
    e.preventDefault();
    var id  = $(e.currentTarget).data('attendee');
    var $row = $(e.currentTarget).closest('tr[data-attendee-id]');
    $.post(TTA_Ajax.ajax_url, {
      action: 'tta_cancel_attendance',
      attendee_id: id,
      nonce: TTA_Ajax.attendee_admin_nonce
    }, function(res){
      if(res.success){
        $row.remove();
        alert(res.data && res.data.message ? res.data.message : 'Attendance cancelled');
      }else{
        alert(res.data && res.data.message ? res.data.message : 'Error');
      }
    }, 'json');
  }

  $(document).on('click', '.tta-cancel-attendee', handleCancel);

  function handleRefundRequest(e, mode){
    e.preventDefault();
    var $btn = $(e.currentTarget);
    var $row = $btn.closest('tr[data-request]');
    var tx  = $btn.data('tx');
    var ticket = $btn.data('ticket');
    var amount = $row.find('.tta-refund-amount').val();
    var action = (mode === 'delete') ? 'tta_delete_refund_request' : 'tta_process_refund_request';
    var data = {
      action: action,
      tx: tx,
      ticket: ticket,
      amount: amount,
      nonce: TTA_Ajax.attendee_admin_nonce
    };
    $.post(TTA_Ajax.ajax_url, data, function(res){
      if(res.success){
        if(res.data && res.data.pending){
          $btn.prop('disabled', true)
              .addClass('tta-disabled tta-tooltip-trigger')
              .attr('data-tooltip', 'Refund scheduled after settlement');
          alert(res.data && res.data.message ? res.data.message : 'Refund pending');
          return;
        }
        $row.remove();
        alert(res.data && res.data.message ? res.data.message : 'OK');
      }else{
        alert(res.data && res.data.message ? res.data.message : 'Error');
      }
    }, 'json');
  }

  $(document).on('click', '.tta-refund-request-process', function(e){
    var mode = $(this).data('mode');
    handleRefundRequest(e, mode);
  });

  $(document).on('click', '.tta-refund-request-delete', function(e){
    handleRefundRequest(e, 'delete');
  });

  // Inline edit toggle for Email & SMS templates
  $(document).on('click', '.widefat tbody tr[data-comms-key]', function(e){
    if ( $(e.target).is('a, button, input, textarea, select') ) return;

    var $row    = $(this),
        $arrow  = $row.find('.tta-toggle-arrow'),
        $inline = $row.next('.tta-inline-row');

    if ( $inline.is(':visible') ) {
      $arrow.removeClass('open');
      $inline.find('.tta-inline-container').slideUp(200, function(){
        $inline.hide();
      });
      return;
    }

    $('.tta-inline-row').each(function(){
      $(this).prev('tr').find('.tta-toggle-arrow').removeClass('open');
      $(this).hide().find('.tta-inline-container').hide();
    });

    $arrow.addClass('open');
    $inline.show();
    $inline.find('.tta-inline-container').slideDown(200, function(){
      var offset = $inline.offset().top;
      $('html, body').animate({ scrollTop: offset - 120 }, 300);
    });
  });

  // Save a single communication template via AJAX
  $(document).on('submit', '.tta-comms-form', function(e){
    e.preventDefault();

    var $form = $(this),
        $btn  = $form.find('button[type=submit]').prop('disabled', true),
        $spin = $form.find('.tta-admin-progress-spinner-svg').css({display:'inline-block', opacity:0}).fadeTo(200,1),
        data  = $form.serialize() + '&action=tta_save_comm_template' + '&tta_comms_save_nonce=' + TTA_Ajax.save_comm_nonce;

    $.post(TTA_Ajax.ajax_url, data, function(res){
      setTimeout(function(){
        $spin.fadeTo(200,0,function(){ $(this).hide(); });
        var cls = res.success ? 'updated' : 'error',
            msg = res.data.message || 'Error saving template';
        $form.find('.tta-admin-progress-response-p')
            .removeClass('updated error')
            .addClass(cls)
            .text(msg);
        $btn.prop('disabled', false);
      }, 5000);
    }, 'json').fail(function(){
      setTimeout(function(){
        $spin.fadeTo(200,0,function(){ $(this).hide(); });
        $form.find('.tta-admin-progress-response-p')
            .removeClass('updated')
            .addClass('error')
            .text('Request failed.');
        $btn.prop('disabled', false);
      }, 5000);
    });
  });

  // Admin subscription forms
  function handleSubForm($form, action, e){
    e.preventDefault();
    var $btn  = $form.find('button[type=submit]').prop('disabled',true),
        $spin = $form.find('.tta-admin-progress-spinner-svg').css({display:'inline-block',opacity:0}).fadeTo(200,1),
        $resp = $form.find('#tta-subscription-response .tta-admin-progress-response-p').removeClass('updated error').text(''),
        data  = $form.serialize() + '&action=' + action + '&nonce=' + TTA_Ajax.membership_admin_nonce;
    $.post(TTA_Ajax.ajax_url, data, function(res){
      setTimeout(function(){
        $spin.fadeTo(200,0,function(){ $(this).hide(); });
        var cls = res.success ? 'updated':'error',
            msg = res.data.message||'Error';
        $resp.removeClass('updated error').addClass(cls).text(msg);
        $btn.prop('disabled',false);
      },5000);
    },'json').fail(function(){
      setTimeout(function(){
        $spin.fadeTo(200,0,function(){ $(this).hide(); });
        $resp.removeClass('updated').addClass('error').text('Request failed.');
        $btn.prop('disabled',false);
      },5000);
    });
  }

  $(document).on('submit','#tta-admin-update-payment-form',function(e){ handleSubForm($(this),'tta_admin_update_payment',e); });
  $(document).on('submit','#tta-admin-cancel-subscription-form',function(e){ handleSubForm($(this),'tta_admin_cancel_subscription',e); });
  $(document).on('submit','#tta-admin-reactivate-subscription-form',function(e){ handleSubForm($(this),'tta_admin_reactivate_subscription',e); });
  $(document).on('submit','#tta-admin-change-level-form',function(e){ handleSubForm($(this),'tta_admin_change_level',e); });
  $(document).on('submit','#tta-admin-assign-membership-form',function(e){ handleSubForm($(this),'tta_admin_assign_membership',e); });
  $(document).on('click','#tta-reactivate-current-btn',function(e){
    e.preventDefault();
    var $form = $('#tta-admin-reactivate-subscription-form');
    $form.find('input[name="use_current"]').val('1');
    $form.find('input[name="card_number"],input[name="exp_date"],input[name="card_cvc"]').prop('required',false);
    $form.trigger('submit');
    $form.find('input[name="card_number"],input[name="exp_date"],input[name="card_cvc"]').prop('required',true);
    $form.find('input[name="use_current"]').val('0');
  });
  $(document).on('click','#tta-create-sub-btn',function(e){
    e.preventDefault();
    if(!confirm('This will cancel the member\'s existing subscription and create a new one in Authorize.net. Are you sure you want to proceed?')){
      return;
    }
    var $form = $('#tta-admin-reactivate-subscription-form');
    $form.find('input[name="create_new"]').val('1');
    $form.trigger('submit');
    setTimeout(function(){ $form.find('input[name="create_new"]').val('0'); }, 500);
  });

  // Auto-fill price fields when membership level changes
  function syncLevelPrice($select){
    var level = $select.val();
    var price = '';
    if(level === 'basic'){ price = '5.00'; }
    else if(level === 'premium'){ price = '10.00'; }
    if(price){
      $select.closest('form').find('input[name="price"], input[name="amount"]').val(price);
    }
  }

  $('form select[name="level"]').each(function(){
    var $priceInput = $(this).closest('form').find('input[name="price"], input[name="amount"]');
    if(!$priceInput.val()){
      syncLevelPrice($(this));
    }
  });
  $(document).on('change','form select[name="level"]',function(){ syncLevelPrice($(this)); });

  // Track the last focused input for token insertion
  var activeField = null;
  $(document).on('focus', '.tta-comm-input', function(){
    activeField = this;
  });

  function insertAtCursor(field, text){
    if (!field) return;
    if (document.selection) {
      field.focus();
      var sel = document.selection.createRange();
      sel.text = text;
    } else if (field.selectionStart || field.selectionStart === 0) {
      var start = field.selectionStart, end = field.selectionEnd;
      field.value = field.value.substring(0, start) + text + field.value.substring(end);
      field.selectionStart = field.selectionEnd = start + text.length;
    } else {
      field.value += text;
    }
  }

  $(document).on('click', '.tta-insert-token', function(){
    insertAtCursor(activeField, $(this).data('token'));
    if (activeField) { $(activeField).trigger('blur'); }
  });

  $(document).on('click', '.tta-insert-br', function(){
    insertAtCursor(activeField, "\n");
    if (activeField) { $(activeField).trigger('blur'); }
  });

  $(document).on('click', '.tta-link-text', function(){
    if (!activeField) { return; }
    var field = activeField;
    if (field.selectionStart === undefined || field.selectionEnd === undefined) { return; }
    if (field.selectionStart === field.selectionEnd) { return; }
    var start = field.selectionStart;
    var end   = field.selectionEnd;
    var val   = field.value;
    // If user selected inside a token, expand to include braces.
    if (start > 0 && val[start - 1] === '{' && val[end] === '}') {
      start--;
      end++;
    }
    var sel = val.substring(start, end);
    var url = prompt('Enter URL or token');
    if (!url) { return; }
    url = url.trim();
    if (url && url[0] !== '{' && !/^[a-z]+:\/\//i.test(url) && url[0] !== '/' && url[0] !== '#') {
      url = '{' + url.replace(/^\{?|\}?$/g, '') + '}';
    }
    var md = '[' + sel + '](' + url + ')';
    field.value = val.substring(0, start) + md + val.substring(end);
    field.selectionStart = field.selectionEnd = start + md.length;
    $(field).trigger('blur');
  });

  $(document).on('click', '.tta-bold-text', function(){
    if (!activeField) { return; }
    var field = activeField;
    if (field.selectionStart === undefined || field.selectionEnd === undefined) { return; }
    if (field.selectionStart === field.selectionEnd) { return; }
    var start = field.selectionStart;
    var end   = field.selectionEnd;
    var val   = field.value;
    var sel   = val.substring(start, end);
    var md    = '**' + sel + '**';
    field.value = val.substring(0, start) + md + val.substring(end);
    field.selectionStart = start;
    field.selectionEnd   = start + md.length;
    $(field).trigger('blur');
  });

  $(document).on('click', '.tta-italic-text', function(){
    if (!activeField) { return; }
    var field = activeField;
    if (field.selectionStart === undefined || field.selectionEnd === undefined) { return; }
    if (field.selectionStart === field.selectionEnd) { return; }
    var start = field.selectionStart;
    var end   = field.selectionEnd;
    var val   = field.value;
    var sel   = val.substring(start, end);
    var md    = '*' + sel + '*';
    field.value = val.substring(0, start) + md + val.substring(end);
    field.selectionStart = start;
    field.selectionEnd   = start + md.length;
    $(field).trigger('blur');
  });

  function convertLinks(text){
    return text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');
  }

  function convertBoldItalic(text){
    return text.replace(/\*\*\*([^*]+)\*\*\*/g, '<strong><em>$1</em></strong>');
  }

  function convertBold(text){
    return text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
  }

  function convertItalic(text){
    return text.replace(/\*([^*]+)\*/g, '<em>$1</em>');
  }

  function stripFormatting(text){
    return text
      .replace(/\*\*\*([^*]+)\*\*\*/g, '$1')
      .replace(/\*\*([^*]+)\*\*/g, '$1')
      .replace(/\*([^*]+)\*/g, '$1');
  }

  function expandAnchors(text,map){
    return text.replace(/\{(dashboard_(?:profile|upcoming|waitlist|past|billing)_url) anchor="([^"]*)"\}/g,function(_,tok,anch){
      var url = map['{'+tok+'}'] || '';
      if(anch===''){ return url; }
      return '['+anch+']('+url+')';
    });
  }

  function renderPreview($form){
    var subj = $form.find('input[name=email_subject]').val() || '';
    var body = $form.find('textarea[name=email_body]').val() || '';
    var sms  = $form.find('textarea[name=sms_text]').val() || '';
    subj = subj.replace(/\\'/g, "'");
    body = body.replace(/\\'/g, "'");
    sms  = sms.replace(/\\'/g, "'");
    var ev   = TTA_Ajax.sample_event || {};
    var mem  = TTA_Ajax.sample_member || {};
    var map  = {
        '{event_name}': ev.name || 'Sample Event',
        '{event_address}': ev.address || '123 Main St',
        '{event_address_link}': ev.address_link || '#',
        '{event_link}': ev.page_url || '#',
        '{dashboard_profile_url}': ev.dashboard_profile_url || '#',
        '{dashboard_upcoming_url}': ev.dashboard_upcoming_url || '#',
        '{dashboard_waitlist_url}': ev.dashboard_waitlist_url || '#',
        '{dashboard_past_url}': ev.dashboard_past_url || '#',
        '{dashboard_billing_url}': ev.dashboard_billing_url || '#',
        '{event_date}': ev.date || '2025-01-01',
        '{event_time}': ev.time || '00:00',
        '{event_type}': ev.type || 'Open',
        '{venue_name}': ev.venue_name || 'Venue',
        '{venue_url}': ev.venue_url || '#',
        '{base_cost}': ev.base_cost || '0',
        '{member_cost}': ev.member_cost || '0',
        '{premium_cost}': ev.premium_cost || '0',
        '{event_host}': ev.host_names || 'TBD',
        '{event_hosts}': ev.host_names || 'TBD',
        '{event_volunteer}': ev.volunteer_names || 'TBD',
        '{event_volunteers}': ev.volunteer_names || 'TBD',
        '{host_notes}': ev.host_notes || '',
        '{first_name}': mem.first_name || 'First',
        '{last_name}': mem.last_name || 'Last',
        '{email}': mem.email || 'member@example.com',
        '{phone}': mem.phone || '555-555-5555',
        '{membership_level}': mem.membership_level || 'basic',
        '{member_type}': mem.member_type || 'member',
        '{reentry_link}': '/checkout?auto=reentry',
        '{attendee_first_name}': mem.first_name || 'First',
        '{attendee_last_name}': mem.last_name || 'Last',
        '{attendee_email}': mem.email || 'attendee@example.com',
        '{attendee_phone}': mem.phone || '555-555-5555',
        '{attendee2_first_name}': mem.first_name || 'First',
        '{attendee2_last_name}': mem.last_name || 'Last',
        '{attendee2_email}': mem.email || 'attendee2@example.com',
        '{attendee2_phone}': mem.phone || '555-555-5556',
        '{attendee3_first_name}': mem.first_name || 'First',
        '{attendee3_last_name}': mem.last_name || 'Last',
        '{attendee3_email}': mem.email || 'attendee3@example.com',
        '{attendee3_phone}': mem.phone || '555-555-5557',
        '{attendee4_first_name}': mem.first_name || 'First',
        '{attendee4_last_name}': mem.last_name || 'Last',
        '{attendee4_email}': mem.email || 'attendee4@example.com',
        '{attendee4_phone}': mem.phone || '555-555-5558',
        '{assistance_message}': mem.assistance_message || '',
        '{assistance_note}': mem.assistance_message || ''
      };
    subj = expandAnchors(subj, map);
    body = expandAnchors(body, map);
    Object.keys(map).forEach(function(tok){
      var val = map[tok];
      if (typeof val === 'string') {
        val = val.replace(/\\'/g, "'");
      }
      subj = subj.split(tok).join(val);
      body = body.split(tok).join(val);
      sms  = sms.split(tok).join(val);
    });
    subj = stripFormatting(subj);
    body = convertItalic(convertBold(convertBoldItalic(convertLinks(body))));
    sms  = stripFormatting(sms);
    var bodyHtml = body.replace(/\n/g, '<br>');
    $form.find('.tta-email-preview-subject').text(subj);
    $form.find('.tta-email-preview-body').html(bodyHtml);
    $form.find('.tta-sms-preview').text(sms);
    var count = sms.length;
    var $count = $form.find('.tta-sms-count').text(count);
    if (count > 160) { $count.addClass('tta-over-limit'); } else { $count.removeClass('tta-over-limit'); }
  }

  $(document).on('blur', '.tta-comm-input', function(){
    renderPreview($(this).closest('.tta-comms-form'));
  });

  $('.tta-comms-form').each(function(){
    renderPreview($(this));
  });

  // Auto-fill venue details when selecting a saved venue
  $(document).on('change input', '#venuename', function(){
    var val = $(this).val();
    var $opt = $('#tta-venue-options option[value="'+val+'"]');
    if($opt.length){
      $('#venueurl').val($opt.data('url') || '');
      $('#url2').val($opt.data('url2') || '');
      $('#url3').val($opt.data('url3') || '');
      $('#url4').val($opt.data('url4') || '');
      var parts = ($opt.data('address') || '').split(' - ');
      $('#street_address').val(parts[0]||'');
      $('#address_2').val(parts[1]||'');
      $('#city').val(parts[2]||'');
      $('#state').val(parts[3]||'');
      $('#zip').val(parts[4]||'');
    }
  });






























  // API Settings: switch authnet environment
  var $envSelect = $('#tta_authnet_sandbox');
  if ($envSelect.length && typeof TTA_Authnet !== 'undefined') {
    var $login = $('#tta_authnet_login_id');
    var $trans = $('#tta_authnet_transaction_key');

    function ttaFillCreds(env) {
      if (env === '1') {
        $login.val(TTA_Authnet.sandbox_login || '');
        $trans.val(TTA_Authnet.sandbox_key || '');
      } else {
        $login.val(TTA_Authnet.live_login || '');
        $trans.val(TTA_Authnet.live_key || '');
      }
    }

    ttaFillCreds($envSelect.val());
    $envSelect.on('change', function(){ ttaFillCreds(this.value); });
  }

  // Authorize.Net test suite button
  $(document).on('click', '#tta-authnet-test-button', function(e){
    e.preventDefault();
    var $btn  = $(this);
    var $spin = $btn.siblings('.tta-admin-progress-spinner-svg').css({display:'inline-block',opacity:0}).fadeTo(200,1);
    var $resp = $('#tta-authnet-test-wrapper .tta-admin-progress-response-p').removeClass('updated error').text('');
    $.post(TTA_Ajax.ajax_url, {
      action: 'tta_run_authnet_tests',
      nonce: TTA_Ajax.authnet_test_nonce
    }, function(res){
      $spin.fadeOut(200);
      if (res.success) {
        $resp.addClass('updated').text(res.data.message);
      } else {
        $resp.addClass('error').text(res.data.message || 'Error');
      }
    }, 'json').fail(function(){
      $spin.fadeOut(200);
      $resp.addClass('error').text('Request failed.');
    });
  });

});/* end jQuery(function($) ) */
