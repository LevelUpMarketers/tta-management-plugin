/**
 * Customizer border color live preview
 *
 * @version 4.6.5
 */

;( function( api, $, window, document, undefined ) {

	"use strict"

	if ( ! wp || ! wp.customize ) {
		console.log( 'wp or wp.customize objects not found.' );
		return;
	}

	if ( typeof wpexBorderColorElements === 'undefined' ) {
		console.log( 'no border color elements defined.' );
		return;
	}

	wp.customize( 'main_border_color', function( value ) {

			value.bind( function( newval ) {

				var style = '';

				if ( newval ) {

					var style = '<style id="wpex-borders-css">';

					style += wpexBorderColorElements.join( ',' ) + '{border-color:' + newval + ';}';

					style += '</style>';

					if ( $( '#wpex-borders-css' ).length !== 0 ) {
						$( '#wpex-borders-css' ).replaceWith( style );
					} else {
						$( style ).appendTo( $( 'head' ) );
					}

				} else if ( $( '#wpex-borders-css' ).length !== 0 ) {
					$( '#wpex-borders-css' ).remove();
				}

			} );

		} );

} ( wp.customize, jQuery, window, document ) );