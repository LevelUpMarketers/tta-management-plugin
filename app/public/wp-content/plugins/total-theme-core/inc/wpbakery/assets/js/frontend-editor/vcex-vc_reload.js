( function( $ ) {

	'use strict';

	$( document ).ready( function() {

		var $modelId, $context = '';

		// Store model ID when events change
		parent.vc.events.on( 'shortcodes:add shortcodes:update shortcodes:clone', function( model ) {
			$modelId = model.id;
		} );

		$( window ).on( 'vc_reload', function() {

			// These functions need to re-run on every reload
			if ( 'undefined' !== typeof window.vcexCarousels ) {
				window.vcexCarousels();
			}

			if ( 'undefined' !== typeof window.vcexHovers ) {
				window.vcexHovers();
			}

			if ( 'undefined' !== typeof window.vcexHovers ) {
				window.vcexHovers();
			}

			if ( 'undefined' !== typeof window.vcexResponsiveCSS ) {
				window.vcexResponsiveCSS();
			}

			if ( 'undefined' !== typeof window.vcexResponsiveText ) {
				window.vcexResponsiveText();
			}

			if ( 'undefined' !== typeof window.vcexStickyNavbar ) {
				window.vcexStickyNavbar();
			}

			if ( 'undefined' !== typeof window.vcexNavbarMobileSelect ) {
				window.vcexNavbarMobileSelect();
			}

			if ( 'undefined' !== typeof window.vcexIsotopeGrids ) {
				window.vcexIsotopeGrids();
			}

			if ( 'undefined' !== typeof window.vcexNavbarFilterLinks ) {
				window.vcexNavbarFilterLinks();
			}

			// Re-run scripts when specific shortcodes are modified
			if ( $modelId ) {

				$context = $( '[data-model-id=' + $modelId + ']' ); // @todo is this secure?

				// Animated Text
				if ( $context.hasClass( 'vc_vcex_animated_text' ) ) {
					if ( 'undefined' !== typeof window.vcexAnimatedText ) {
						window.vcexAnimatedText( $context );
					}
					return;
				}

				// Countdown
				if ( $context.hasClass( 'vc_vcex_countdown' ) ) {
					if ( 'undefined' !== typeof window.vcexCountDown ) {
						window.vcexCountDown( $context );
					}
					return;
				}

				// Milestones
				if ( $context.hasClass( 'vc_vcex_milestone' ) ) {
					if ( 'undefined' !== typeof window.vcexMilestone ) {
						window.vcexMilestone( $context );
					}
					return;
				}

				// Skillbars
				if ( $context.hasClass( 'vc_vcex_skillbar' ) ) {
					if ( 'undefined' !== typeof window.vcexSkillbar ) {
						window.vcexSkillbar( $context );
					}
					return;
				}

				// Before/After images
				if ( $context.hasClass( 'vc_vcex_image_ba' ) ) {
					if ( 'undefined' !== typeof window.vcexBeforeAfter ) {
						window.vcexBeforeAfter( $context );
					}
					return;
				}

			}

		} );

	} );

} ) ( jQuery );