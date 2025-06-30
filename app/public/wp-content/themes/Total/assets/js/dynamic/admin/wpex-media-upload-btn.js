( function( $ ) {

	"use strict";

	var $document = $( document );

	$document.ready( function() {

		function upload_media() {

			var _custom_media = true,
			_orig_send_attachment = wp.media.editor.send.attachment;


			$( document ).on( 'click', '.wpex-upload-button', function() {

				var send_attachment_bkp	= wp.media.editor.send.attachment,
					button = $( this ),
					id = button.prev();
					_custom_media = true;

				wp.media.editor.send.attachment = function( props, attachment ) {
					if ( _custom_media ) {
						id.val( attachment.id ).trigger( 'change' );
					} else {
						return _orig_send_attachment.apply( this, [props, attachment] );
					};
				}

				wp.media.editor.open();

				return false;

			} );

			$( '.add_media' ).on( 'click', function() {
				_custom_media = false;
			} );

		}

		// Run on load
		upload_media();

		// Customizer support
		if ( wp.customize !== undefined ) {
			$document.on( 'widget-updated', upload_media );
			$document.on( 'widget-added', upload_media );
		}

	} );

} ) ( jQuery );