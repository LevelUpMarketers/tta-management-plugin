/**
 * Update Customizer settings live.
 *
 */
( function( api, $ ) {

    "use strict"

    // Bail if customizer object isn't in the DOM.
    if ( ! wp || ! wp.customize ) {
        console.log( 'wp or wp.customize objects not found.' );
        return;
    }

    // Declare variables
    var wpexinCSS         = {},
        body              = $( 'body' ),
        head              = $( 'head' ),
        siteheader        = $( '#site-header' ),
        topBarWrap        = $( '#top-bar-wrap' ),
        navWrap           = $( '#site-navigation-wrap' );

    /******** General *********/

        var $arrow = $( '#site-scroll-top > span.ticon' );
        if ( $arrow.length ) {
            api( 'scroll_top_arrow', function( value ) {
                value.bind( function( newval ) {
                    if ( newval ) {
                        $arrow.removeClass();
                        $arrow.addClass( 'ticon' );
                        $arrow.addClass( 'ticon-' + newval );
                    }
                } );
            } );
        }

        var $pagination = $( '.wpex-pagination, .woocommerce-pagination' );
        if ( $pagination.length ) {
            api( 'pagination_align', function( value ) {
                value.bind( function( newval ) {
                    $pagination.removeClass( 'wpex-center wpex-left wpex-right' );
                    $pagination.addClass( 'wpex-'+ newval );
                } );
            } );
        }

        api( 'pagination_arrow', function( value ) {
            if ( body.hasClass( 'rtl' ) ) {
                var $next = $( 'ul.page-numbers .next .ticon' );
                var $prev = $( 'ul.page-numbers .prev .ticon' );
            } else {
                var $prev = $( 'ul.page-numbers .next .ticon' );
                var $next = $( 'ul.page-numbers .prev .ticon' );
            }
            value.bind( function( newval ) {
                if ( $next.length ) {
                    $next.removeClass();
                    $next.addClass( 'ticon' );
                    $next.addClass( 'ticon-' + newval + '-left' );
                }
                if ( $prev.length ) {
                    $prev.removeClass();
                    $prev.addClass( 'ticon' );
                    $prev.addClass( 'ticon-' + newval  + '-right' );
                }
            } );
        } );

    /******** Layouts *********/

    api( 'boxed_dropdshadow', function( value ) {
        value.bind( function( newval ) {
            if ( newval ) {
                body.addClass( 'wrap-boxshadow' );
            } else {
                body.removeClass( 'wrap-boxshadow' );
            }
        } );
    } );

    api( 'header_flex_items', function( value ) {
        value.bind( function( newval ) {
            if ( newval ) {
                siteheader.addClass( 'wpex-header-two-flex-v' );
            } else {
                siteheader.removeClass( 'wpex-header-two-flex-v' );
            }
        } );
    } );

    api( 'site_frame_border', function( value ) {
        value.bind( function( newval ) {
            if ( newval ) {
                body.addClass( 'has-frame-border' );
            } else {
                body.removeClass( 'has-frame-border' );
            }
        } );
    } );

    /******** TOPBAR *********/

        if ( topBarWrap.length ) {

            api( 'top_bar_fullwidth', function( value ) {
                value.bind( function( newval ) {
                    if ( newval ) {
                        topBarWrap.addClass( 'wpex-full-width' );
                    } else {
                        topBarWrap.removeClass( 'wpex-full-width' );
                    }
                } );
            } );

            api( 'top_bar_bottom_border', function( value ) {
                value.bind( function( newval ) {
                    if ( newval ) {
                        topBarWrap.addClass( 'wpex-border-b wpex-border-main wpex-border-solid' );
                    } else {
                       topBarWrap.removeClass( 'wpex-border-b wpex-border-main wpex-border-solid' );
                    }
                } );
            } );

        }

    /******** HEADER *********/

        // Full-width header
        api( 'full_width_header', function( value ) {
            value.bind( function( newval ) {
                if ( newval && siteheader.length ) {
                    siteheader.addClass( 'wpex-full-width' );
                } else {
                    siteheader.removeClass( 'wpex-full-width' );
                }
            } );
        } );

        // Text Logo Icon
        api( 'logo_icon', function( value ) {
            value.bind( function( newval ) {
                var icon = $( '#site-logo-fa-icon' );
                if ( newval && 'none' !== newval && icon.length ) {
                    icon.show();
                    icon.removeClass();
                    icon.addClass( 'ticon' );
                    icon.addClass( 'ticon-'+ newval );
                } else {
                    icon.hide();
                }
            } );
        } );

        // Header Vertical Style - Fixed or not fixed
        api( 'vertical_header_style', function( value ) {
            value.bind( function( newval ) {
                if ( newval ) {
                    body.addClass( 'wpex-fixed-vertical-header' );
                } else {
                    body.removeClass( 'wpex-fixed-vertical-header' );
                }
            } );
        } );

        // Header borders
        api( 'header_menu_disable_borders', function( value ) {
            value.bind( function( newval ) {
                if ( newval ) {
                    $( '.navbar-style-two, .navbar-style-six' ).addClass( 'no-borders' );
                } else {
                    $( '.navbar-style-two, .navbar-style-six' ).removeClass( 'no-borders' );
                }
            } );
        } );

        // Header Center
        api( 'header_menu_center', function( value ) {
            var $headerTwoNav = $( '.navbar-style-two' );
            value.bind( function( newval ) {
                if ( newval ) {
                    $headerTwoNav.addClass( 'center-items' );
                } else {
                    $headerTwoNav.removeClass( 'center-items' );
                }
            } );
        } );

        // Header menu stretch
        api( 'header_menu_stretch_items', function( value ) {
            var $headerTwoNav = $( '.navbar-style-two, .navbar-style-three, .navbar-style-four' );
            value.bind( function( newval ) {
                if ( newval ) {
                    $headerTwoNav.addClass( 'wpex-stretch-items' );
                } else {
                    $headerTwoNav.removeClass( 'wpex-stretch-items' );
                }
            } );
        } );

    /******** NAVBAR *********/

        api( 'menu_dropdown_style', function( value ) {
            value.bind( function( newval ) {
                var headerClasses = siteheader.attr( 'class' ).split( ' ' );
                for(var i = 0; i < headerClasses.length; i++) {
                    if(headerClasses[i].indexOf( 'wpex-dropdown-style-' ) != -1) {
                        siteheader.removeClass(headerClasses[i]);
                    }
                }
                siteheader.addClass( 'wpex-dropdown-style-'+ newval );
            } );
        } );

        api( 'menu_dropdown_dropshadow', function( value ) {
            value.bind( function( newval ) {
                var headerClasses = siteheader.attr( 'class' ).split( ' ' );
                for(var i = 0; i < headerClasses.length; i++) {
                    if(headerClasses[i].indexOf( 'wpex-dropdowns-shadow-' ) != -1) {
                        siteheader.removeClass(headerClasses[i]);
                    }
                }
                siteheader.addClass( 'wpex-dropdowns-shadow-'+ newval );
            } );
        } );

    /******** Mobile Menu *********/

        api( 'mobile_menu_toggle_text', function( value ) {
            value.bind( function( newval ) {
                $( '.mobile-menu-toggle .wpex-text, #wpex-mobile-menu-navbar .wpex-text' ).text( newval );
            } );
        } );

        api( 'full_screen_mobile_menu_style', function( value ) {
            value.bind( function( newval ) {
                $( '.full-screen-overlay-nav' ).removeClass( 'white' ).removeClass( 'black' );
                $( '.full-screen-overlay-nav' ).addClass( newval );
            } );
        } );

    /******** Sidebar *********/

        api( 'has_widget_icons', function( value ) {
            value.bind( function( newval ) {
                if ( newval ) {
                    body.addClass( 'sidebar-widget-icons' );
                } else {
                    body.removeClass( 'sidebar-widget-icons' );
                }
            } );
        } );

    /******** Sidebar *********/

        api( 'sidebar_headings', function( value ) {
            value.bind( function( newval ) {
                var headings = $( '.sidebar-box .widget-title' );
                headings.each( function() {
                    $(this).replaceWith( '<' + newval +' class="widget-title">' + this.innerHTML + '</' + newval +'>' );
                } );
            } );
        } );

    /******** Blog *********/

        api( 'blog_single_header_custom_text', function( value ) {
            var title = $( 'body.single-post .page-header-title' );
            if ( title.length ) {
                var ogTitle = title.html();
                value.bind( function( newval ) {
                    if ( newval ) {
                        title.html( newval );
                    } else {
                        title.html( ogTitle );
                    }
                } );
            }
        } );

        api( 'blog_related_title', function( value ) {
            var heading = $( '.related-posts-title span.text' );
            if ( heading.length ) {
                var ogheading = heading.html();
                value.bind( function( newval ) {
                    if ( newval ) {
                        heading.html( newval );
                    } else {
                        heading.html( ogheading );
                    }
                } );
            }
        } );

    /******** Portfolio *********/

        api( 'portfolio_related_title', function( value ) {
            var heading = $( '.related-portfolio-posts-heading span.text' );
            if ( heading.length ) {
                var ogheading = heading.html();
                value.bind( function( newval ) {
                    if ( newval ) {
                        heading.html( newval );
                    } else {
                        heading.html( ogheading );
                    }
                } );
            }
        } );

    /******** Staff *********/

        api( 'staff_related_title', function( value ) {
            var heading = $( '.related-staff-posts-heading span.text' );
            if ( heading.length ) {
                var ogheading = heading.html();
                value.bind( function( newval ) {
                    if ( newval ) {
                        heading.html( newval );
                    } else {
                        heading.html( ogheading );
                    }
                } );
            }
        } );

    /******** Footer Headings *********/

        api( 'footer_headings', function( value ) {
            value.bind( function( newval ) {
                var headings = $( '.footer-widget .widget-title' );
                var widgetClass = headings.attr( 'class' );
                headings.each( function() {
                    $(this).replaceWith( '<' + newval +' class="' + widgetClass + '">' + this.innerHTML + '</' + newval +'>' );
                } );
            } );
        } );

    /******** Footer Gap *********/

        api( 'footer_widgets_gap', function( value ) {
            var widgets = $( '#footer-widgets' );
            value.bind( function( newval ) {
                var classes = widgets.attr("class").split( ' ' );
                if ( classes ) {
                    $.each(classes, function(i, c) {
                        if (c.indexOf("gap-") == 0) {
                            widgets.removeClass(c);
                        }
                    } );
                }
                if ( newval ) {
                    widgets.addClass( 'gap-'+ newval );
                }
            } );
        } );


    /******** WooCommerce *********/

        api( 'woo_shop_columns_gap', function( value ) {
            var widgets = $( '.products.wpex-row' );
            value.bind( function( newval ) {
                var classes = widgets.attr("class").split( ' ' );
                if ( classes ) {
                    $.each(classes, function(i, c) {
                        if (c.indexOf("gap-") == 0) {
                            widgets.removeClass(c);
                        }
                    } );
                }
                if ( newval ) {
                    widgets.addClass( 'gap-'+ newval );
                }
            } );
        } );

        api( 'woo_entry_equal_height', function( value ) {
            var $el = $( '.woocommerce ul.products' );
            value.bind( function( newval ) {
                $el.find( '.product .product-details.match-height-content' ).height( '' );
                if ( newval ) {
                    $el.addClass( 'match-height-grid' );
                    wpex.equalHeights();
                }
            } );
        } );

    /******** Accent Color *********/
    function wpexGenerateAccentColorCSS() {

        if ( 'undefined' === typeof wpex_accent_targets ) {
            return;
        }

        api( 'accent_color', function( value ) {

            value.bind( function( newval ) {

                var style = '';

                if ( newval ) {

                    var style = '<style id="wpex-accent-css">';

                    if ( wpex_accent_targets.texts ) {
                        style += wpex_accent_targets.texts.join( ',' ) + '{color:' + newval + ';}';
                    }

                    if ( wpex_accent_targets.backgrounds ) {
                        style += wpex_accent_targets.backgrounds.join( ',' ) + '{background-color:' + newval + ';}';
                    }

                    /*if ( wpex_accent_targets.backgroundsHover ) {
                        style += wpex_accent_targets.backgroundsHover.join( ',' ) + '{background-color:' + newval + ';}';
                    }*/

                    if ( wpex_accent_targets.borders ) {
                        _.each( wpex_accent_targets.borders, function( val, key ) {
                            if ( _.isArray( val ) ) {
                                _.each( val, function( borderLocation ) {
                                    style += key + '{border-' + borderLocation + '-color:' + newval + ';}';
                                } );
                            } else {
                                style += val + '{border-color:' + newval + ';}';
                            }
                        } );
                    }

                    style += '</style>';

                    if ( $( '#wpex-accent-css' ).length !== 0 ) {
                        $( '#wpex-accent-css' ).replaceWith( style );
                    } else {
                        $( style ).appendTo( head );
                    }

                } else if ( $( '#wpex-accent-css' ).length !== 0 ) {
                    $( '#wpex-accent-css' ).remove();
                }

            } );

        } );

        api( 'accent_color_hover', function( value ) {

             value.bind( function( newval ) {

                var style = '';

                if ( newval ) {

                    var style = '<style id="wpex-accent-hover-css">';

                    if ( wpex_accent_targets.backgroundsHover ) {
                        style += wpex_accent_targets.backgroundsHover.join( ',' ) + '{background-color:' + newval + ';}';
                    }

                    if ( wpex_accent_targets.textsHover ) {
                        style += wpex_accent_targets.textsHover.join( ',' ) + '{color:' + newval + ';}';
                    }

                    style += '</style>';

                    if ( $( '#wpex-accent-hover-css' ).length !== 0 ) {
                        $( '#wpex-accent-hover-css' ).replaceWith( style );
                    } else {
                        $( style ).appendTo( head );
                    }

                } else if ( $( '#wpex-accent-hover-css' ).length !== 0 ) {
                    $( '#wpex-accent-hover-css' ).remove();
                }

            } );

        } );

    }

    wpexGenerateAccentColorCSS();

    /**
     * Live design options
     *
     */
    wpexinCSS = {

        /**
         * Get and loop through inline css options
         *
         * @since 4.1
         */
        init : function() {

            if ( typeof wpexCustomizer === 'undefined' ) {
                return;
            }

            var stylingOptions = wpexCustomizer.stylingOptions;

            //console.log( stylingOptions );

            _.each( stylingOptions, function( settings, id ) {

                wpexinCSS.setStyle( settings, id );

            } );

        },

        /**
         * Set styles
         *
         * @since 4.1
         */
        setStyle : function( settings, id ) {

            var styleId     = 'wpex-customizer-' + id,
                target      = settings.target,
                property    = settings.alter,
                sanitize    = settings.sanitize ? settings.sanitize : '',
                important   = settings.important ? '!important' : '',
                media_query = settings.media_query ? settings.media_query : '';

            api( id, function( value ) {

                value.bind( function( newval ) {

                    if ( 'display' == property && 'checkbox' == sanitize ) {
                        newval = newval ? '' : 'none';
                    }

                    // Remove style
                    if ( newval === '' || typeof newval === "undefined" ) {
                        $( '#' + styleId ).remove();
                    }

                    // Build style
                    else {

                        var style = '<style id="' + styleId + '">';

                            if ( media_query ) {
                                style += '@media only screen and ' + media_query + '{';
                            }

                            // Sanitize val
                            if ( sanitize && newval ) {

                                if ( 'px' === sanitize ) {

                                    if ( newval.indexOf( 'px' ) == -1
                                        && newval.indexOf( 'em' ) == -1
                                        && newval.indexOf( '%' ) == -1
                                    ) {

                                        newval = parseInt( newval ); // set to integer
                                        newval = newval + 'px'; // Add px

                                    }

                                }

                                else if ( 'font-size' === sanitize ) {

                                    if ( newval.indexOf( 'px' ) == -1
                                        && newval.indexOf( 'em' ) == -1
                                    ) {
                                        newval = newval + 'px';
                                    }

                                }

                            } // End sanitize

                            // Target single item
                            if ( typeof property === 'string' ) {


                                // Add style
                                if ( Object.prototype.toString.call( target ) === '[object Array]' ) {
                                    $.each( target, function( index, value ) {
                                        style += value + '{' + property + ':' + newval + important + ';}';
                                    } );
                                } else {
                                    style += target + '{' + property + ':' + newval + important + ';}';
                                }

                            }

                            // Target multiple items
                            else {

                                if ( Object.prototype.toString.call( target ) === '[object Array]' ) {
                                    $.each( target, function( index, value ) {
                                        var eachTarget = value;
                                        $.each( property, function( index, value ) {
                                            style += eachTarget + '{' + value + ':' + newval + important + ';}';
                                        } );
                                    } );
                                } else {
                                    $.each( property, function( index, value ) {
                                        style += target + '{' + value + ':' + newval + important + ';}';
                                    } );
                                }

                            }

                        if ( media_query ) {
                            style += '}';
                        }

                        style += '</style>';

                        // Update previewer.
                        if ( $( '#' + styleId ).length !== 0 ) {
                            $( '#' + styleId ).replaceWith( style );
                        } else {
                            $( style ).appendTo( head );
                        }

                    }

                } );

            } );

        }

    };

    wpexinCSS.init();

} ( wp.customize, jQuery ) );