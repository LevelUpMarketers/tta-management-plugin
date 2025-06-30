( function( $ ) {

    'use strict';

    function wpexLoadMore() {

        if ( 'undefined' === typeof wpex ) {
            console.log( 'Load More script can not run because wpex is not defined.' );
            return;
        }

        var $loadMore = $( '.wpex-load-more' );

        if ( ! $loadMore.length ) {
            return;
        }

        $loadMore.each( function() {

            var $button      = $( this );
            var $wrap        = $( this ).parent( '.wpex-load-more-wrap' );
            var $buttonInner = $button.find( '.theme-button-inner' );
            var loading      = false;
            var text         = wpexLocalize.loadMore.text;
            var ajaxUrl      = wpexLocalize.ajaxurl;
            var loadingText  = wpexLocalize.loadMore.loadingText;
            var failedText   = wpexLocalize.loadMore.failedText;
            var buttonData   = $button.data( 'loadmore' );
            var $grid        = $( buttonData.grid );
            var page         = 2;
            var isMasonry    = false;

            if ( 1 != buttonData.maxPages ) {
                $button.addClass( 'wpex-visible' );
            }

            var loadmoreData = buttonData;

            $wrap.css( 'min-height', $wrap.outerHeight() ); // prevent jump when showing loader icon

            if ( $grid.hasClass( 'wpex-masonry-grid' ) ) {
                isMasonry = true;
            }

            $button.on( 'click', function() {

                if ( ! loading ) {

                    loading = true;

                    $wrap.addClass( 'wpex-loading' );
                    $buttonInner.text( loadingText );

                    var data = {
                        action   : 'wpex_ajax_load_more',
                        nonce    : buttonData.nonce,
                        page     : page,
                        loadmore : loadmoreData
                    };

                    $.post( ajaxUrl, data, function( res ) {

                        // Ajax request successful
                        if ( res.success ) {

                            //console.log( res.data );

                            // Increase page
                            page = page + 1;

                            // Define vars
                            var $newElements = $( res.data );
                            $newElements.css( 'opacity', 0 ); // hide until images are loaded

                            // Tweak new items
                            $newElements.each( function() {
                                var $this = $( this );

                                // Add duplicate tag to sticky incase someone want's to hide these
                                if ( $this.hasClass( 'sticky' ) ) {
                                    $this.addClass( 'wpex-duplicate' );
                                }

                                // Make sure masonry class exists to prevent issues
                                if ( isMasonry ) {
                                    $this.addClass( 'wpex-masonry-col' );
                                }

                            } );

                            $grid.append( $newElements ).imagesLoaded( function() {

								// Update counter (before we display items)
                                var $counterEl = $grid.find( '[data-count]' );
                                if ( $counterEl.length ) {
                                    loadmoreData.count = parseInt( $counterEl.data( 'count' ) );
                                    $counterEl.remove();
                                }

                                // Reload retina js
                                if ( 'function' === typeof retinajs ) {
                                    retinajs();
                                }

                                // Reload equal heights
                                if ( 'undefined' !== typeof wpex.equalHeights ) {
                                    wpex.equalHeights();
                                }

                                // Reload masonry
                                if ( isMasonry ) {
                                    //$grid.isotope().append( $newElements ).isotope( 'appended', $newElements ).isotope( 'layout' );
                                    //$grid.isotope().append( $newElements ).isotope( 'appended', $newElements );
                                    $grid.isotope( 'appended', $newElements );
                                }

                                // Trigger event before displaying items
                                $grid.trigger( 'wpexLoadMoreAddedHidden', [$newElements] );

                                // Show items
                                $newElements.css( 'opacity', 1 );

                                // Triger event after showing items
                                $grid.trigger( 'wpexLoadMoreAddedVisible', [$newElements] );

								// Reload overlayhovers
                                if ( 'undefined' !== typeof wpex.overlayHovers ) {
                                    wpex.overlayHovers();
                                }

                                // Reload overlaymobile support
                                if ( 'undefined' !== typeof wpex.overlaysMobileSupport ) {
                                    wpex.overlaysMobileSupport();
                                }

                                // REload sliderPro scripts
                                if ( 'undefined' !== typeof wpex.sliderPro ) {
                                    wpex.sliderPro( $newElements );
                                }

                                // Reload WPBakery hovers
                                if ( 'undefined' !== typeof wpex.vcexHovers ) {
                                    window.vcexHovers();
                                }

                                // Reload WP embeds
                                if ( 'undefined' !== typeof $.fn.mediaelementplayer ) {
                                    $newElements.find( 'audio, video' ).mediaelementplayer();
                                }

                                // Reset button
                                $wrap.removeClass( 'wpex-loading' );
                                $buttonInner.text( text );

                                // Hide button
                                if ( ( page - 1 ) == buttonData.maxPages ) {
                                    $button.hide();
                                }

                                // Set loading to false
                                loading = false;

                            } ); // End images loaded

                        } // End success

                        else {

                            $buttonInner.text( failedText );

                            console.log( res );

                        }

                    } ).fail( function( xhr, textGridster, e ) {

                        console.log( xhr.responseText );

                    } );

                } // end loading check

                return false;

            } ); // End click

        } ); // End each

    }

    $( window ).on( 'load', function() {
        wpexLoadMore();
    } );

} ) ( jQuery );