( function( $ ) {

    'use strict';

	if ( 'undefined' === typeof wpex ) {
		console.log( 'wpex is not defined' );
		return;
	}

	if ( ! $( 'div.infinite-scroll-nav' ).length ) {
		console.log( '.infinite-scroll-nav element not found' );
		return;
	}

    function wpexInfiteScroll() {

		var $container = $( '#blog-entries' );

		$container.infinitescroll( wpexInfiniteScroll, function( newElements ) {

			var $newElems = $( newElements ).css( 'opacity', 0 );

			$newElems.imagesLoaded( function() {

				if ( $container.hasClass( 'wpex-masonry-grid' ) ) {
					$container.isotope( 'appended', $newElems );
					$newElems.css( 'opacity', 0 );
				}

				if ( 'function' === typeof retinajs ) {
					retinajs();
				}

				$newElems.animate( {
					opacity: 1
				} );

				$container.trigger( 'wpexinfiniteScrollLoaded', [$newElems] );

				if ( 'undefined' !== typeof wpex.sliderPro ) {
					wpex.sliderPro( $newElems );
				}

				 if ( 'undefined' !== typeof wpex.equalHeights ) {
					wpex.equalHeights();
				}

				if ( 'undefined' !== typeof $.fn.mediaelementplayer ) {
					$newElems.find( 'audio, video' ).mediaelementplayer();
				}

			} );

		} );

    }

    $( window ).on( 'load', function() {
		wpexInfiteScroll();
	} );

} ) ( jQuery );