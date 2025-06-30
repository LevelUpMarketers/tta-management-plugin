( function( $ ) {
	'use strict';
	$( document ).ready( function() {
		if ( 'undefined' !== typeof $.fn.wpColorPicker ) {
			$( '.wpex-color-field' ).wpColorPicker();
		}
	} );
} ) ( jQuery );