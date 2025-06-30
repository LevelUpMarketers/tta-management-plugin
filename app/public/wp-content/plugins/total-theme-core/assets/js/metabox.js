( function( $ ) {

    'use strict';

	$( document ).ready( function() {

		// Date picker
		var $date = $( '.wpex-date-meta' );
		if ( $.datepicker && $date.length ) {
			$date.datepicker();
		}

		// Button Group
		var $buttonGroups = $( '.wpex-mb-btn-group' );
		if ( $buttonGroups.length ) {

			$buttonGroups.each( function() {

				var $this        = $( this );
				var $button      = $this.find( 'button' );
				var $hiddenInput = $this.find( '.wpex-mb-hidden' );

				$button.click( function() {
					$button.removeClass( 'active' );
					var $this = $( this );
					$this.addClass( 'active' );
					$hiddenInput.val( $this.data( 'value' ) );
				} );

			} );


		}

		// Tabs
		$( 'div#wpex-metabox ul.wp-tab-bar a' ).click( function() {
			var lis = $( '#wpex-metabox ul.wp-tab-bar li' ),
				data = $( this ).data( 'tab' ),
				tabs = $( '#wpex-metabox div.wp-tab-panel' );
			$( lis ).removeClass( 'wp-tab-active' );
			$( tabs ).hide();
			$( data ).show();
			$( this ).parent( 'li' ).addClass( 'wp-tab-active' );
			return false;
		} );

		// Color picker
		$( 'div#wpex-metabox .wpex-mb-color-field' ).wpColorPicker();

		// Reset
		$( 'div#wpex-metabox div.wpex-mb-reset a.wpex-reset-btn' ).click( function() {
			var $confirm = $( 'div.wpex-mb-reset div.wpex-reset-checkbox' ),
				$txt     = $confirm.is( ':visible' ) ? wpexMB.cancel : wpexMB.cancel;
			$( this ).text( $txt );
			$( 'div.wpex-mb-reset div.wpex-reset-checkbox input' ).attr( 'checked', false);
			$confirm.toggle();
		} );

		// Show hide title options
		var titleStyleField     = $( 'div#wpex-metabox select#wpex_post_title_style' ),
			titleStyleFieldVal  = titleStyleField.val(),
			pageTitleBgSettings = $( '#wpex_post_title_background_color_tr, #wpex_post_title_background_redux_tr,#wpex_post_title_height_tr,#wpex_post_title_background_overlay_tr,#wpex_post_title_background_overlay_opacity_tr,#wpex_post_title_background_position_tr,#wpex_post_title_background_image_style_tr' ),
			solidColorElements  = $( '#wpex_post_title_background_color_tr' );

		// Show hide title style settings
		if ( titleStyleFieldVal === 'background-image' ) {
			pageTitleBgSettings.show();
		} else if ( titleStyleFieldVal === 'solid-color' ) {
			solidColorElements.show();
		}

		titleStyleField.change(function () {
			pageTitleBgSettings.hide();
			if ( $(this).val() == 'background-image' ) {
				pageTitleBgSettings.show();
			}
			else if ( $(this).val() === 'solid-color' ) {
				solidColorElements.show();
			}
		} );

		// Show hide Overlay options
		var overlayField = $( 'div#wpex-metabox select#wpex_overlay_header' ),
			overlayFieldDependents = $( '#wpex_overlay_header_style_tr, #wpex_overlay_header_font_size_tr,#wpex_overlay_header_logo_tr,#wpex_overlay_header_logo_retina_tr,#wpex_overlay_header_logo_retina_height_tr,#wpex_overlay_header_dropdown_style_tr,#wpex_overlay_header_background_tr' );
		if ( overlayField.val() === 'on' ) {
			overlayFieldDependents.show();
		} else {
			overlayFieldDependents.hide();
		}
		overlayField.change( function () {
			if ( $(this).val() === 'on' ) {
				overlayFieldDependents.show();
			} else {
				overlayFieldDependents.hide();
			}
		} );

		// Media uploader
		var _custom_media = true;

		$( 'div#wpex-metabox .wpex-image-select .wpex-input' ).change( function() {
			if ( ! $( this ).val() ) {
				var $img = $( this ).parent().parent().find( '.wpex-img-holder img' );
				if ( $img.length ) {
					$img.remove();
				}
			}
		} );

		$( 'div#wpex-metabox .wpex-mb-uploader' ).click( function( event ) {
			event.preventDefault();
			var button     = $( this );
			var field      = button.prev();
			var current_id = field.val();
			wpexMediaSelector( field, current_id );
		} );

		$( 'div#wpex-metabox .add_media' ).on( 'click', function() {
			_custom_media = false;
		} );


	} );

} ) ( jQuery );

// Media selector function
// @todo Add support so when you click on image it opens popup with the selected item.
function wpexMediaSelector( field, current_id ) {

    'use strict';

 	// Define uploading vars
    var file_frame;

    /**
     * If an instance of file_frame already exists, then we can open it
     * rather than creating a new instance.
     */
    if ( undefined !== file_frame ) {
		file_frame.open();
		return;
    }

    /**
     * If we're this far, then an instance does not exist, so we need to
     * create our own.
     *
     */
    file_frame = wp.media.frames.file_frame = wp.media( {
    	id            : 'wpex_metabox_select',
    	frame         : 'post',
     	state         : 'insert',
     	filterable    : 'uploaded', // Whether the library is filterable, and if so what filters should be shown.
        multiple      : false,      // Whether multi-select is enabled.
        syncSelection : false,      // Whether the Attachments selection should be persisted from the last state.
        autoSelect    : true        // Whether an uploaded attachment should be automatically added to the selection.
    } );

    /**
     * Update field with selected ID
     *
     */
    file_frame.on( 'insert', function() {

    	// Get selection
    	var selection = file_frame.state().get( 'selection' ).first().toJSON();

    	// Update field value
    	field.val( selection.id );

    	// Update screenshot
    	if ( selection.url ) {
    		var $holder = field.parent().parent().find( '.wpex-img-holder' );
    		if ( $holder.length ) {
				$holder.empty();
				$holder.append( '<img src="' + selection.url + '" />' );
			}
		}
    } );

    // Now display the actual file_frame
    file_frame.open();

}