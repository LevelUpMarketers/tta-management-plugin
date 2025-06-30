( function( $ ) {

    'use strict';

    $( document ).ready( function() {
        window.vcexHovers();
        window.vcexResponsiveCSS();
        window.vcexResponsiveText();
        window.vcexCarousels();
        window.vcexCountDown();
        window.vcexAnimatedText();
        window.vcexSkillbar();
        window.vcexIsotopeGrids();
        window.vcexNavbarFilterLinks();
        window.vcexNavbarMobileSelect();
    } );

    $( window ).on( 'load', function() {
        window.vcexBeforeAfter();
        window.vcexMilestone();
        window.vcexStickyNavbar();
    } );

    $( window ).resize( function() {
        window.vcexResponsiveText();
    } );

    $( window ).on( 'orientationchange', function() {
        window.vcexIsotopeGrids();
        window.vcexNavbarFilterLinks();
    } );

    function elData( name, fallback ) {
        return ( typeof name !== 'undefined' ) ? name : fallback;
    }

    function viewportWidth() {
        var e = window, a = 'inner';
        if ( ! ( 'innerWidth' in window ) ) {
            a = 'client';
            e = document.documentElement || document.body;
        }
        return e[ a+'Width' ];
    }

    var isRTL = false;

    if ( 'undefined' !== typeof( wpexLocalize ) ) {
        isRTL = wpexLocalize.isRTL;
    }

    /* Responsive Text
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexResponsiveText ) {

        window.vcexResponsiveText = function ( $context ) {
            var self = this;
            var $responsiveText = $( '.wpex-responsive-txt' );
            $responsiveText.each( function() {
                var $this      = $( this );
                var $thisWidth = $this.width();
                var $data      = $this.data();
                var $minFont   = elData( $data.minFontSize, 13 );
                var $maxFont   = elData( $data.maxFontSize, 40 );
                var $ratio     = elData( $data.responsiveTextRatio, 10 );
                var $fontBase  = $thisWidth / $ratio;
                var $fontSize  = $fontBase > $maxFont ? $maxFont : $fontBase < $minFont ? $minFont : $fontBase;
                $this.css( 'font-size', $fontSize + 'px' );
            } );
        };

    }

    /* Hover Styles
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexHovers ) {
        window.vcexHovers = function ( $context ) {

            var headCSS = '';
            var cssObj  = {};

            $( '.wpex-hover-data' ).remove(); // prevent dups / front-end editor fix

            // Newer Total 4.5.4.2 method
            $( '[data-wpex-hover]' ).each( function( index, value ) {

                var $this       = $( this );
                var data        = $this.data( 'wpex-hover' );
                var uniqueClass = 'wpex-dhover-' + index;
                var hoverCSS    = '';
                var target      = '';

                if ( data.parent ) {
                    $this.parents( data.parent ).addClass( uniqueClass + '-p' );
                    $this.addClass( uniqueClass );
                    target = '.' + uniqueClass + '-p:hover .' + uniqueClass;
                } else {
                    $this.addClass( uniqueClass );
                    target = '.' + uniqueClass + ':hover';
                }

                $.each( data, function( attribute, value ) {
                    if ( 'target' === attribute || 'parent' === attribute ) {
                        return true;
                    }
                    hoverCSS += attribute + ':' +  value + '!important;';
                } );

                if ( hoverCSS ) {
                    if ( hoverCSS in cssObj ) {
                        cssObj[hoverCSS] = cssObj[hoverCSS] + ',' + target;
                    } else {
                        cssObj[hoverCSS] = target;
                    }
                }

            } );

            if ( cssObj ) {

                $.each( cssObj, function( css, elements ) {

                    headCSS += elements + '{' + css + '}';

                } );

            }

            if ( headCSS ) {
                $( 'head' ).append( '<style class="wpex-hover-data">' + headCSS + '</style>' );
            }

        };

    }

    /* Responsive CSS
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexResponsiveCSS ) {
        window.vcexResponsiveCSS = function ( $context ) {

            var headCSS   = '';
            var mediaObj  = {};
            var bkPoints  = {};

            $( '.wpex-vc-rcss' ).remove(); // Prevent duplicates when editing the VC

            // Get breakpoints
            bkPoints.d = '';

            if ( 'undefined' !== typeof( wpexLocalize ) ) {
                bkPoints = $.extend( bkPoints, wpexLocalize.responsiveDataBreakpoints );
            } else {
                bkPoints = {
                    'tl':'1024px',
                    'tp':'959px',
                    'pl':'767px',
                    'pp':'479px'
                };
            }

            // Loop through breakpoints to create mediaObj
            $.each( bkPoints, function( key ) {
                mediaObj[key] = ''; // Create empty array of media breakpoints
            } );

            // loop through all modules and add CSS to mediaObj
            $( '[data-wpex-rcss]' ).each( function( index, value ) {

                var $this       = $( this );
                var uniqueClass = 'wpex-rcss-' + index;
                var data        = $this.data( 'wpex-rcss' );

                $this.addClass( uniqueClass );

                $.each( data, function( key, val ) {

                    var thisVal = val;
                    var target  = key;

                    $.each( bkPoints, function( key ) {

                        if ( thisVal[key] ) {

                            mediaObj[key] += '.' + uniqueClass + '{' + target + ':' + thisVal[key] + '!important;}';

                        }

                    } );

                } );

            } );

            $.each( mediaObj, function( key, val ) {

                if ( 'd' == key ) {
                    headCSS += val;
                } else {
                    if ( val ) {
                        headCSS += '@media(max-width:' + bkPoints[key] + '){' + val + '}';
                    }
                }

            } );

            if ( headCSS ) {

                headCSS = '<style class="wpex-vc-rcss">' + headCSS + '</style>';

                $( 'head' ).append( headCSS );

            }

        };

    }

    /* Carousels
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexCarousels ) {
        window.vcexCarousels = function ( $context ) {

            if ( 'undefined' === typeof $.fn.wpexOwlCarousel || 'undefined' === typeof $.fn.imagesLoaded ) {
                return;
            }

            $( '.wpex-carousel', $context ).each( function() {

                var $this    = $( this ),
                    settings = $this.data( 'wpex-carousel' );

                if ( ! settings ) {
                    console.log( 'Total Notice: The Carousel template in your child theme needs updating to include wpex-carousel data attribute.' );
                    return;
                }

                var defaults = {
                    animateIn          : false,
                    animateOut         : false,
                    lazyLoad           : false,
                    autoplayHoverPause : true,
                    rtl                : isRTL ? true : false,
                    navText            : [ '<span class="ticon ticon-chevron-left" aria-hidden="true"></span><span class="screen-reader-text">' + wpexCarousel.i18n.PREV + '</span>', '<span class="ticon ticon-chevron-right" aria-hidden="true"></span><span class="screen-reader-text">' + wpexCarousel.i18n.NEXT + '</span>' ],
                    responsive         : {
                        0: {
                            items : settings.itemsMobilePortrait
                        },
                        480: {
                            items : settings.itemsMobileLandscape
                        },
                        768: {
                            items : settings.itemsTablet
                        },
                        960: {
                            items : settings.items
                        }
                    },
                };

                $this.imagesLoaded( function() {
                    var owl = $this.wpexOwlCarousel( $.extend( true, {}, defaults, settings ) );
                } );

            } );

        };

    }

    /* CountDown
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexCountDown ) {
        window.vcexCountDown = function ( $context ) {

            if ( 'undefined' === typeof $.fn.countdown ) {
                return;
            }

            $( '.vcex-countdown', $context ).each( function() {

                var $this     = $( this ),
                    endDate  = $this.data( 'countdown' ),
                    days     = $this.data( 'days' ),
                    hours    = $this.data( 'hours' ),
                    minutes  = $this.data( 'minutes' ),
                    seconds  = $this.data( 'seconds' ),
                    timezone = $this.data( 'timezone' );

                if ( timezone && typeof moment.tz !== 'undefined' && $.isFunction( moment.tz ) ) {
                    endDate = moment.tz( endDate, timezone ).toDate();
                }

                if ( ! endDate ) {
                    return;
                }

                $this.countdown( endDate, function( event ) {
                    $this.html( event.strftime( '<div class="wpex-days"><span>%-D</span> <small>' + days + '</small></div> <div class="wpex-hours"><span>%-H</span> <small>' + hours + '</small></div class="wpex-months"> <div class="wpex-minutes"><span>%-M</span> <small>' + minutes + '</small></div> <div class="wpex-seconds"><span>%-S</span> <small>' + seconds + '</small></div>' ) );
                } );

            } );

        };

    }

    /* Animated Text
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexAnimatedText ) {
        window.vcexAnimatedText = function ( $context ) {
            if ( typeof Typed !== 'function' || 'undefined' === typeof $.fn.appear ) {
                return;
            }

            $( '.vcex-typed-text', $context ).each( function() {
                var $this     = $( this );
                var $settings = $this.data( 'settings' );
                $this.appear( function() {
                    $settings.typeSpeed  = parseInt( $settings.typeSpeed );
                    $settings.backDelay  = parseInt( $settings.backDelay );
                    $settings.backSpeed  = parseInt( $settings.backSpeed );
                    $settings.startDelay = parseInt( $settings.startDelay );
                    $settings.strings    = $this.data( 'strings' );
                    var typed = new Typed( this, $settings );
                } );
            } );
        };
    }

    /* MileStones
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexMilestone ) {
        window.vcexMilestone = function ( $context ) {

            if ( typeof CountUp !== 'function' || 'undefined' === typeof $.fn.appear ) {
                return;
            }

            $( '.vcex-countup', $context ).each( function() {

                var $this    = $( this ),
                    data     = $this.data( 'options' ),
                    startVal = data.startVal,
                    endVal   = data.endVal,
                    decimals = data.decimals,
                    duration = data.duration;

                var options = {
                    useEasing   : true,
                    useGrouping : true,
                    separator   : data.separator,
                    decimal     : data.decimal,
                    prefix      : '',
                    suffix      : ''
                };

                var numAnim = new CountUp( this, startVal, endVal, decimals, duration, options );

                $this.appear( function() {
                    numAnim.start();
                } );

            } );

        };

    }

    /* Skillbars
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexSkillbar ) {
        window.vcexSkillbar = function ( $context ) {
            if ( 'undefined' === typeof $.fn.appear ) {
                return;
            }

            $( '.vcex-skillbar', $context ).each( function() {
                var $this = $( this );
                $this.appear( function() {
                    $this.find( '.vcex-skillbar-bar' ).animate( {
                        width: $( this ).attr( 'data-percent' )
                    }, 800 );
                } );
            }, {
                accX : 0,
                accY : 0
            } );

        };
    }

    /* Before After Images
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexBeforeAfter ) {

        window.vcexBeforeAfter = function ( $context ) {

            if ( 'undefined' === typeof $.fn.twentytwenty ) {
                return;
            }

            $( '.vcex-image-ba', $context ).each( function() {
                var $this = $( this );
                $this.twentytwenty( $this.data( 'options' ) );
            } );

        };

    }

    /* Isotope Grids
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexIsotopeGrids ) {
        window.vcexIsotopeGrids = function () {

            if ( 'undefined' === typeof $.fn.imagesLoaded || 'undefined' === typeof $.fn.isotope ) {
                return;
            }

            // Standard grids
            $( '.vcex-isotope-grid' ).each( function() {

                // Isotope layout
                var $container = $( this );

                // Run only once images have been loaded
                $container.imagesLoaded( function() {

                    // Check filter
                    var activeItems;

                    // Filter links
                    var $filter = $container.prev( 'ul.vcex-filter-links' );
                    if ( $filter.length ) {
                        var $filterLinks = $filter.find( 'a' );
                        activeItems = $container.data( 'filter' );
                        if ( activeItems && ! $filter.find( '[data-filter="' + activeItems + '"]').length ) {
                            activeItems = '';
                        }
                        $filterLinks.click( function() {
                            $grid.isotope( {
                                filter : $( this ).attr( 'data-filter' )
                            } );
                            $( this ).parents( 'ul' ).find( 'li' ).removeClass( 'active' );
                            $( this ).parent( 'li' ).addClass( 'active' );
                            return false;
                        } );
                    }

                    // Crete the isotope layout
                    var $grid = $container.isotope( {
                        itemSelector       : '.vcex-isotope-entry',
                        transformsEnabled  : true,
                        isOriginLeft       : isRTL ? false : true,
                        transitionDuration : $container.data( 'transition-duration' ) ? $container.data( 'transition-duration' ) + 's' : '0.4s',
                        layoutMode         : $container.data( 'layout-mode' ) ? $container.data( 'layout-mode' ) : 'masonry',
                        filter             : activeItems
                    } );

                } );

            } );

        };

    }

    /* Navfilters
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexNavbarFilterLinks ) {
        window.vcexNavbarFilterLinks = function ( $context ) {

            if ( 'undefined' === typeof $.fn.imagesLoaded || 'undefined' === typeof $.fn.isotope ) {
                return;
            }

            // Filter Navs
            $( '.vcex-filter-nav', $context ).each( function() {

                var $nav        = $( this ),
                    $filterGrid = $( '#' + $nav.data( 'filter-grid' ) ),
                    $grid;

                if ( ! $filterGrid.hasClass( 'wpex-row' ) ) {
                    $filterGrid = $filterGrid.find( '.wpex-row' );
                }

                if ( $filterGrid.length ) {

                    // Remove isotope class
                    $filterGrid.removeClass( 'vcex-isotope-grid' );

                    // Run functions after images are loaded for grid
                    $filterGrid.imagesLoaded( function() {

                        // Create Isotope
                        if ( ! $filterGrid.hasClass( 'vcex-navbar-filter-grid' ) ) {

                            $filterGrid.addClass( 'vcex-navbar-filter-grid' );

                            var activeItems = $nav.data( 'filter' );
                            if ( activeItems && ! $nav.find( '[data-filter="' + activeItems + '"]').length ) {
                                activeItems = '';
                            }

                            $grid = $filterGrid.isotope( {
                                itemSelector       : '.col',
                                transformsEnabled  : true,
                                isOriginLeft       : isRTL ? false : true,
                                transitionDuration : $nav.data( 'transition-duration' ) ? $nav.data( 'transition-duration' ) + 's' : '0.4s',
                                layoutMode         : $nav.data( 'layout-mode' ) ? $nav.data( 'layout-mode' ) : 'masonry',
                                filter             : activeItems
                            } );

                        } else {

                            // Add isotope only, the filter grid already
                            $grid = $filterGrid.isotope();

                        }

                        // Loop through filter links for filtering items
                        var $filterLinks = $nav.find( 'a' );
                        $filterLinks.click( function() {

                            // Define link
                            var $link = $( this );

                            // Filter items
                            $grid.isotope( {
                                filter : $( this ).attr( 'data-filter' )
                            } );

                            // Remove all active class
                            $filterLinks.removeClass( 'active' );

                            // Add active class
                            $link.addClass( 'active' );

                            // Return false
                            return false;

                        } );

                    } );

                }

            } );

        };

    }

    /* Sticky Navbar
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexStickyNavbar ) {
        window.vcexStickyNavbar = function ( $context ) {

            var $nav      = $( '.vcex-navbar-sticky' ),
                $window   = $( window ),
                windowTop = $window.scrollTop();

            if ( ! $nav.length ) {
                return;
            }

            $nav.each( function() {

                var $this           = $( this ),
                    $isSticky       = false,
                    $stickyEndPoint = $this.data( 'sticky-endpoint' ) ? $( $this.data( 'sticky-endpoint' ) ) : '';

                // Add sticky wrap
                var $stickyWrap = $( '<div class="vcex-navbar-sticky-wrapper not-sticky"></div>' );
                $this.wrapAll( $stickyWrap );
                $stickyWrap = $this.parent( '.vcex-navbar-sticky-wrapper' );

                // Check sticky offSet based on other sticky elements
                function getStickyOffset() {

                    var offset = 0,
                        items  = '';

                    if ( $this.data( 'vcex-sticky-offset-items' ) ) {
                        items = $this.data( 'vcex-sticky-offset-items' );
                    } else {
                        items = '#top-bar-wrap-sticky-wrapper.wpex-can-sticky,#site-header-sticky-wrapper.wpex-can-sticky #site-header,#site-navigation-sticky-wrapper.wpex-can-sticky,#wpex-mobile-menu-fixed-top,#wpadminbar';
                    }

                    if ( ! items ) {
                        return;
                    }

                    items = items.split( ',' );

                    $.each( items, function( index, value ) {
                        var $this = $( value );
                        if ( $this.is( ':visible' ) ) {
                            offset = parseInt( offset ) + parseInt( $this.outerHeight() );
                        }
                    } );

                    return offset;

                }

                // Set sticky
                function setSticky( $offset ) {

                    // Return if hidden
                    if ( ! $this.is( ':visible' ) ) {
                        destroySticky(); // make sure to destroy if hidden
                        return;
                    }

                    // Already sticky or hidden
                    if ( $isSticky ) {
                        $this.css( {
                            'top' : getStickyOffset() // recalculate for shrink sticky elements
                        } );
                        return;
                    }

                    // Set placeholder
                    $stickyWrap
                        .css( 'height', $this.outerHeight() )
                        .removeClass( 'not-sticky' )
                        .addClass( 'is-sticky' );

                    // Position Fixed nav
                    $this.css( {
                        'top'   : $offset,
                        'width' : $stickyWrap.width()
                    } );

                    // Update sticky var
                    $isSticky = true;

                }

                // Un-Shrink header function
                function destroySticky() {

                    // Not sticky
                    if ( ! $isSticky ) {
                        return;
                    }

                    // Remove sticky wrap height and toggle sticky class
                    $stickyWrap
                        .css( 'height', '' )
                        .removeClass( 'is-sticky' )
                        .addClass( 'not-sticky' );

                    // Remove navbar width
                    $this.css( {
                        'width' : '',
                        'top'   : ''
                    } );

                    // Update shrunk var
                    $isSticky = false;

                }

                // On scroll function
                function stickyCheck() {

                    var windowTop     = $( window ).scrollTop(),
                        stickyOffset  = getStickyOffset(),
                        stickyWrapTop = $stickyWrap.offset().top,
                        setStickyPos  = stickyWrapTop - stickyOffset;

                    if ( windowTop > setStickyPos && 0 !== windowTop ) {
                        setSticky( stickyOffset );
                        if ( $stickyEndPoint.length && $stickyEndPoint.is( ':visible' ) ) {
                            if ( windowTop > ( $stickyEndPoint.offset().top - stickyOffset - $this.outerHeight() ) ) {
                                $stickyWrap.addClass( 'sticky-hidden' );
                            } else {
                                $stickyWrap.removeClass( 'sticky-hidden' );
                            }
                        }
                    } else {
                        destroySticky();
                    }

                }

                // On resize function
                function onResize() {

                    // Should it be sticky?
                    stickyCheck();

                    // Sticky fixes
                    if ( $isSticky ) {

                        // Destroy if hidden
                        if ( ! $this.is( ':visible' ) ) {
                            destroySticky();
                        }

                        // Set correct height on wrapper
                        $stickyWrap.css( 'height', $this.outerHeight() );

                        // Set correct width and offset value on sticky element
                        $this.css( {
                            'top'   : getStickyOffset(),
                            'width' : $stickyWrap.width()
                        } );

                    }

                    // Should it become sticky?
                    else {
                        stickyCheck();
                    }

                }

                // Fire on init
                stickyCheck();

                // Fire onscroll event
                $window.scroll( function() {
                    stickyCheck();
                } );

                // Fire onResize
                $window.resize( function() {
                    onResize();
                } );

                // Fire resize on flip
                $window.on( 'orientationchange', function( e ) {
                    destroySticky();
                    stickyCheck();
                } );

            } ); // End each

        };

    }

    /* Sticky Navbar
    ---------------------------------------------------------- */
    if ( 'function' !== typeof window.vcexNavbarMobileSelect ) {
        window.vcexNavbarMobileSelect = function ( $context ) {

            var $selects = $( '.vcex-navbar-mobile-select' );

            if ( ! $selects.length ) {
                return;
            }

            $selects.each( function() {

                var $this   = $( this );
                var $select = $( this ).find( 'select' );
                var $navbar = $this.parent( '.vcex-navbar' ).find( '.vcex-navbar-inner' );

                $select.change( function() {

                    var val = $( this ).val();

                    if ( val ) {
                        $navbar.find( 'a[href="' + val + '"]' ).get(0).click();
                    }

                } );

            } );

        };

    }


} ) ( jQuery );