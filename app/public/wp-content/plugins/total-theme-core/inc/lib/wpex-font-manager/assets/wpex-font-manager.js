window.wpexFontManager = window.wpexFontManager || {};

( function( $, fm ) {

	'use strict';

	/* Function Calls
	--------------------------------------------------------------------------------------------------- */
	$( document ).ready( function() {
		fm.toggleMetaboxes.init();
	} );

	/* Show/Hide metaboxes
	--------------------------------------------------------------------------------------------------- */
	fm.toggleMetaboxes = {

		init: function() {

			$( '#wpex-mf-field--type' ).change( function() {

				$( this ).find( 'option:selected' ).each(function() {

					var optionValue  = $( this ).attr( 'value' );
					var $genFields   = $( '#wpex-mf-tr--name, #wpex-mf-tr--fallback' );
					var $isGlobal    = $( '#wpex-mf-tr--is_global' );
					var $fontDisplay = $( '#wpex-mf-tr--display' );
					var $typeBoxes   = $( '#wpex-mf-metabox--adobe, #wpex-mf-metabox--google, #wpex-mf-metabox--custom' );
					var $extras      = $( 'wpex-mf-field--type .wpex-mf-field--extras' );
					var $extraBtns   = $( '.wpex-visit-google-btn,.wpex-visit-adobe-btn' );

					if ( optionValue ) {
						$genFields.show();
						$extras.show();
						$extraBtns.addClass( 'wpex-mf-hidden' );
						$isGlobal.hide();
						$fontDisplay.hide();
						$( '#wpex-mf-metabox--assign' ).show();
						$typeBoxes.not( '#wpex-mf-metabox--' + optionValue ).hide();
						$( '#wpex-mf-metabox--' + optionValue ).show();
						$( '.wpex-visit-' + optionValue + '-btn' ).removeClass( 'wpex-mf-hidden' );
						if ( 'google' == optionValue || 'adobe' == optionValue ) {
							$isGlobal.show();
						}
						if ( 'google' == optionValue || 'custom' == optionValue ) {
							$fontDisplay.show();
						}
		            } else{
		            	$fontDisplay.hide();
		            	$extras.hide();
						$typeBoxes.hide();
						$genFields.hide();
						$isGlobal.hide();
						$extraBtns.addClass( 'wpex-mf-hidden' );
						$( '#wpex-mf-metabox--assign' ).hide();
            		}

				} );

		    } ).change(); //invoke now

		}

	};

} ) ( jQuery, wpexFontManager );