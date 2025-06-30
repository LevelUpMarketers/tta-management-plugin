( function( $ ) {

	'use strict';

	if ( 'function' !== typeof window.vcexJustifiedGallery ) {
		window.vcexJustifiedGallery = function ( $context ) {
			$( '.vcex-justified-gallery' ).each( function() {
				var $this = $( this );
				$this.justifiedGallery( $( this ).data( 'justified-gallery' ) );
			} );
		};
	}

	$( document ).ready( function() {
		window.vcexJustifiedGallery();
	} );

	$( window ).on( 'vc_reload', function() {
		window.vcexJustifiedGallery();
	} );

} ) ( jQuery );