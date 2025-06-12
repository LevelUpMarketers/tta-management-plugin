/**
 * assets/js/backend/media-uploader.js
 *
 * Handles “Select Image” buttons for:
 *  - Events (main image and gallery)
 *  - Members (profile image)
 *
 * Assumes that each button has:
 *   data-target="#FIELD_ID"
 * and that there is a corresponding preview container whose ID is derived from the field ID.
 *
 * For example:
 *   <button class="button tta-upload-single" data-target="#mainimageid">Select Image</button>
 *   <input type="hidden" id="mainimageid" name="mainimageid" value="">
 *   <div id="mainimage-preview"></div>
 *
 *   <button class="button tta-member-upload-single" data-target="#profileimgid">Select Profile Image</button>
 *   <input type="hidden" id="profileimgid" name="profileimgid" value="">
 *   <div id="profileimage-preview"></div>
 */

jQuery(function($){

  // ───────────────────────────────────────────────────────────
  // 1) SINGLE-IMAGE UPLOADER (generic for any “.tta-upload-single”)
  // ───────────────────────────────────────────────────────────
  $('body').on('click', '.tta-upload-single', function(e){
    e.preventDefault();

    var $button  = $(this),
        target   = $button.data('target'),                // e.g. "#mainimageid"
        $input   = $(target),                              // the hidden <input>
        // Replace “imageid” with “image-preview” in the ID string:
        previewID = target.replace('imageid', 'image-preview'),
        $preview = $(previewID);

    // Open WordPress media frame
    var frame = wp.media({
      title: 'Select or Upload an Image',
      button: { text: 'Use this image' },
      library: { type: 'image' },
      multiple: false
    });

    frame.on('select', function(){
      var attachment = frame.state().get('selection').first().toJSON(),
          thumb      = (attachment.sizes && attachment.sizes.thumbnail)
                         ? attachment.sizes.thumbnail.url
                         : attachment.url,
          html       = '<img src="' + thumb + '" style="max-width:150px;"/>';

      $input.val( attachment.id );
      $preview.html( html );
    });

    frame.open();
  });

  // ───────────────────────────────────────────────────────────
  // 2) MEMBER-PROFILE IMAGE UPLOADER
  //    (identical behavior, but targets ".tta-member-upload-single")
  // ───────────────────────────────────────────────────────────
  $('body').on('click', '.tta-member-upload-single', function(e){
    e.preventDefault();

    var $button   = $(this),
        target    = $button.data('target'),                  // e.g. "#profileimgid"
        $input    = $(target),                                // hidden <input>
        previewID = target.replace('imgid', 'image-preview'),
        $preview  = $(previewID);

    // Open WordPress media frame
    var frame = wp.media({
      title: 'Select or Upload a Profile Image',
      button: { text: 'Use this image' },
      library: { type: 'image' },
      multiple: false
    });

    frame.on('select', function(){
      var attachment = frame.state().get('selection').first().toJSON(),
          // always pull the full-size image, fallback to attachment.url
          fullSize   = (attachment.sizes && attachment.sizes.full)
                         ? attachment.sizes.full.url
                         : attachment.url,
          html       = '<img src="' + fullSize + '"/>';

      $input.val( attachment.id );
      $preview.html( html );
    });

    frame.open();
  });


  // ───────────────────────────────────────────────────────────
  // 3) MULTIPLE-IMAGE UPLOADER (existing for “.tta-upload-multiple”)
  // ───────────────────────────────────────────────────────────
  $('body').on('click', '.tta-upload-multiple', function(e){
    e.preventDefault();

    var $button  = $(this),
        target   = $button.data('target'),                   // e.g. "#otherimageids"
        $input   = $(target),                                 // hidden <input>
        previewID = target.replace('otherimageids', 'otherimage-preview'),
        $preview = $(previewID);

    // Open WordPress media frame
    var frame = wp.media({
      title: 'Select or Upload Images',
      button: { text: 'Use these images' },
      library: { type: 'image' },
      multiple: true
    });

    frame.on('select', function(){
      var attachments = frame.state().get('selection').toArray(),
          ids         = [],
          html        = '';

      attachments.forEach(function(attachment) {
        attachment = attachment.toJSON();
        ids.push( attachment.id );
        // choose thumbnail if available
        var thumb = (attachment.sizes && attachment.sizes.thumbnail)
                      ? attachment.sizes.thumbnail.url
                      : attachment.url;
        html += '<img src="' + thumb + '" style="max-width:100px; margin-right:5px;"/>';
      });

      $input.val( ids.join(',') );
      $preview.html( html );
    });

    frame.open();
  });

});
