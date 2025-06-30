( function( $, l10n ) {

    'use strict';

    $.wpexGalleryAdditionalImagesLightbox = {

        init : function() {

            $( document ).ready( function() {
                $.wpexGalleryAdditionalImagesLightbox.runLightbox();
            } );

            $( 'form.variations_form' ).on( 'wc_additional_variation_images_frontend_lightbox', function() {
                $.wpexGalleryAdditionalImagesLightbox.runLightbox();
            } );

        },

        runLightbox : function() {

            if ( 'undefined' === typeof $.fn.fancybox ) {
                return;
            }

            $( "<style>.woocommerce-product-gallery__wrapper{cursor:pointer;}</style>" ).appendTo( 'head' );

            $( 'body' ).on( 'click', '.woocommerce-product-gallery__wrapper', function () {

                event.preventDefault(); // prevents issues

                var iLightboxData = $( this ).data( 'ilightbox' );
                if ( iLightboxData ) {
                    iLightboxData.destroy();
                }

                var $items = $( '[data-large_image]', $( this ) );
                var images = [];
                var items  = [];
                var active = false;

                $items.each( function() {

                    var $this      = $( this );
                    var opts       = {};
                    var title      = '';
                    var $parent    = $( this ).parents( '.woocommerce-product-gallery__image' );
                    var largeImage = $this.data( 'large_image' ) || '';

                    if ( ! $parent.hasClass( 'clone' ) ) {

                        if ( $parent.hasClass( 'flex-active-slide' ) ) {
                            active = largeImage;
                        }

                        if ( largeImage.length ) {

                            if ( l10n.showTitle ) {
                                title = $this.attr( 'data-caption' ) || $this.attr( 'title' ) || '';
                            }

                            if ( title.length ) {
                                opts.caption = '<div class="fancybox-caption__title">' + title + '</div>';
                            }

                            opts.thumb = largeImage;

                            images.push( largeImage );

                            items.push( {
                                src  : largeImage,
                                opts : opts
                            } );

                        }

                    }

                } );

               if ( images.length > 1 ) {

                    var activeIndex = $.inArray( active, images );

                    $.fancybox.open( items, wpexLightboxSettings, parseInt( activeIndex ) );

                } else {

                    $.fancybox.open( items, wpexLightboxSettings );

                }

            } );

        }

    }

    $.wpexGalleryAdditionalImagesLightbox.init();

} ) ( jQuery, wpex_wc_lightbox );