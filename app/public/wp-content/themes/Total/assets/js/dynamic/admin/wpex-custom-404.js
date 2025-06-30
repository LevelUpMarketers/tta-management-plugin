window.wpexCustom404 = window.wpexCustom404 || {};

( function( $, obj ) {

	'use strict';

	/* Function Calls
	--------------------------------------------------------------------------------------------------- */
	$( document ).ready( function() {
		obj.chosenSelect.init();
		obj.toggleSettings.init();
	} );

	/* Enable Chosen Select
	--------------------------------------------------------------------------------------------------- */
	obj.chosenSelect = {

		init: function() {

			if ( 'undefined' === typeof $.fn.chosen ) {
				return;
			}

			$( '.wpex-chosen' ).chosen( {
				disable_search_threshold: 10
			} );

		}

	};

	/* Show/Hide metaboxes
	--------------------------------------------------------------------------------------------------- */
	obj.toggleSettings = {

		init: function() {

			var	$redirectErrorPage = $( '#error-page-redirect' ),
				$pageIdSelect      = $( '#error-page-content-id' ),
				$pageIdVal         = $pageIdSelect.val(),
				$fieldsTohide      = $( '#error-page-title, #wp-error_page_text-wrap' ),
				$elementsTohide    = $fieldsTohide.closest( 'tr' );

			if ( '1' == $redirectErrorPage.val() ) {
				$pageIdSelect.closest( 'tr' ).hide();
			}

			if ( $pageIdVal || '1' == $redirectErrorPage.val() ) {
				$elementsTohide.hide();
			}

			$( $redirectErrorPage ).change( function () {
				if ( $(this ).is( ":checked" ) ) {
					$pageIdSelect.closest( 'tr' ).hide();
					$elementsTohide.hide();
				} else {
					$pageIdSelect.closest( 'tr' ).show();
					if ( ! $pageIdSelect.val() ) {
						$elementsTohide.show();
					}
				}
			} );

			$( $pageIdSelect ).change( function () {
				var $selected = $( this ).val();
				if ( $selected !== '' ) {
					$elementsTohide.hide();
				} else {
					$elementsTohide.show();
				}
			} );

		}

	};

} ) ( jQuery, wpexCustom404 );