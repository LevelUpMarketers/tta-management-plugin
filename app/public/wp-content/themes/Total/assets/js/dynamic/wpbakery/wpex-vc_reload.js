// @version 4.9.3

( function( $ ) {

	'use strict';

	$( document ).ready( function() {

		if ( 'undefined' === typeof wpex ) {
			console.log( 'wpex not defined' );
		}

		var modelId, $context = '';

		parent.vc.events.on( 'shortcodes:add shortcodes:update shortcodes:clone', function( model ) {
			modelId = model.id;
		} );

		$( window ).on( 'vc_reload', function() {

			if ( 'function' === typeof retinajs ) {
				retinajs();
			}

			if ( 'undefined' !== typeof wpex.sliderPro ) {
				wpex.sliderPro();
			}

			if ( 'undefined' !== typeof wpex.equalHeights ) {
				wpex.equalHeights();
			}

			if ( 'undefined' !== typeof wpex.masonryGrids ) {
				wpex.masonryGrids();
			}

			// Re-run scripts when specific shortcodes are modified
			if ( modelId ) {

				$context = $( '[data-model-id=' + modelId + ']' );

				// Remove duplicate items
				window.wpexVcReloadRemoveDups( $context );

				if ( 'undefined' !== typeof wpex.parallax ) {
					wpex.parallax( $context );
				}

				if ( 'undefined' !== typeof wpex.overlayHovers ) {
					wpex.overlayHovers();
				}

				if ( 'undefined' !== typeof wpex.overlaysMobileSupport ) {
					wpex.overlaysMobileSupport();
				}

				if ( 'undefined' !== typeof wpex.customSelects ) {
					wpex.customSelects( $context );
					return;
				}

				// Module dependent
				if ( $context.hasClass( 'vc_vc_wp_custommenu' ) && 'undefined' !== typeof wpex.menuWidgetAccordion ) {
					wpex.menuWidgetAccordion( $context );
					return;
				}

			}

		} );

	} );

	/* Used to remove duplicate elements on vc_reload
	---------------------------------------------------------- */
	if ( 'function' !== typeof window.wpexVcReloadRemoveDups ) {

		window.wpexVcReloadRemoveDups = function ( $context ) {

			var $topShapeDivider, $bottomShapeDivider, $overlays, $videos, $parallax, $videoOverlays,
				$this = $context,
				$module = $this.children( ':first' );

			if ( ! $module.length ) {
				return;
			}

			// Shape dividers
			$topShapeDivider = $module.find( '> .wpex-shape-divider-top' );
			if ( $module.hasClass( 'wpex-has-shape-divider-top' ) ) {
				$topShapeDivider.not( ':first' ).remove();
			} else if ( $topShapeDivider.length ) {
				$topShapeDivider.remove();
			}

			$bottomShapeDivider = $module.find( '> .wpex-shape-divider-bottom' );
			if ( $module.hasClass( 'wpex-has-shape-divider-bottom' ) ) {
				$bottomShapeDivider.not( ':first' ).remove();
			} else if ( $bottomShapeDivider.length ) {
				$bottomShapeDivider.remove();
			}

			// Overlays
			$overlays = $module.find( '> .wpex-bg-overlay-wrap' );
			if ( $module.hasClass( 'wpex-has-overlay' ) ) {
				$overlays.not( ':first' ).remove();
			} else if ( $overlays.length ) {
				$overlays.remove();
			}

			// Self-hosted Videos
			$videos = $module.find( '> .wpex-video-bg-wrap' );
			if ( $module.hasClass( 'wpex-has-video-bg' ) ) {
				$videos.not( ':first' ).remove();
			} else if ( $videos.length ) {
				$videos.remove();
			}

			// Parallax
			$parallax = $module.find( '> .wpex-parallax-bg' );
			if ( $module.hasClass( 'wpex-parallax-bg-wrap' ) ) {
				$parallax.not( ':first' ).remove();
			} else if ( $parallax.length ) {
				$parallax.remove();
			}

			// Video Backgrounds
			// Deprecated? @todo Remove & test
			$videoOverlays = $module.find( '> .wpex-video-bg-overlay' );
			if ( $videoOverlays.length ) {
				$videoOverlays.not( ':first' ).remove();
			}
		}

	}

} ) ( jQuery );