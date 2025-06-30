;( function( api, $, window, document, undefined ) {

    "use strict"

    // Bail if customizer object isn't in the DOM.
    if ( ! wp || ! wp.customize ) {
        console.log( 'wp or wp.customize objects not found.' );
        return;
    }

    var wpexCustomizerTypography = {},
        body                     = $( 'body' ),
        head                     = $( 'head' ),
        stdFonts                 = wpexTypo.stdFonts,
        customFonts              = wpexTypo.customFonts,
        googleFontsUrl           = wpexTypo.googleFontsUrl,
        googleFontsSuffix        = wpexTypo.googleFontsSuffix;


    // Font Smoothing
    api( 'enable_font_smoothing', function( value ) {
        value.bind( function( newval ) {
            if ( newval ) {
                body.addClass( 'wpex-antialiased' );
            } else {
                body.removeClass( 'wpex-antialiased' );
            }
        } );
    } );

    /**
     * Live Typography CSS
     *
     */
    wpexCustomizerTypography = {

        /**
         * Get and loop through inline css options
         *
         * @since 4.1
         */
        init : function() {

            if ( typeof wpexTypo === 'undefined' ) {
                return;
            }

            var attributes = wpexTypo.attributes;

            _.each( wpexTypo.settings, function( settings, key ) {

                var target   = settings['target'],
                    excludes = settings.exclude ? settings.exclude : [];

                _.each( attributes, function( attribute ) {

                    if ( $.inArray( attribute, excludes ) > -1 ) {
                        return;
                    }

                    var settingID = key + '_typography[' + attribute + ']';

                    wpexCustomizerTypography.setStyle( key, settingID, attribute, target );

                } );

            } );

        },

        /**
         * Set styles
         *
         * @since 4.1
         */
        setStyle : function( key, settingID, attribute, target ) {

            var self = this,
                styleId  = 'wpex-customizer-' + key + '-' + attribute,
                target   = target,
                property = attribute;

            if ( Object.prototype.toString.call( target ) === '[object Array]' ) {
                target = target.toString(); // Convert target arrays to strings
            }

            api( settingID, function( value ) {

                value.bind( function( newval ) {

                    var style = '';

                    // Load Google font scripts
                    if ( 'font-family' == attribute ) {
                        self.setGoogleFonts( key, newval );
                        if ( 'system-ui' == newval ) {
                            newval = 'apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
                        }
                    }

                    // Remove style
                    if ( newval === '' || typeof newval === "undefined" ) {
                        $( '#' + styleId ).remove();
                    }

                    // Build style
                    else {

                        if ( typeof property === 'string' ) {

                            // Font size needs to do it's own thing for responsiveness.
                            if ( 'font-size' === attribute ) {
                                if ( self.isJSON( newval ) ) {
                                    newval = JSON.parse( newval );
                                    $.each( newval, function( index, value ) {
                                        if ( 'd' == index ) {
                                            style += target + '{' + property + ':' + self.parseFontSize( value ) + ';}';
                                        } else if ( 'tl' == index ) {
                                            style += '@media(max-width:1024px){' + target + '{' + property + ':' + self.parseFontSize( value ) + ';}}';
                                        } else if ( 'tp' == index ) {
                                            style += '@media(max-width:959px){' + target + '{' + property + ':' + self.parseFontSize( value ) + ';}}';
                                        } else if ( 'pl' == index ) {
                                            // Technically the media query should be at 767px but this is  a fix because of the Customizer preview responsive toggles.
                                            style += '@media(max-width:600px){' + target + '{' + property + ':' + self.parseFontSize( value ) + ';}}';
                                        } else if ( 'pp' == index ) {
                                            style += '@media(max-width:479px){' + target + '{' + property + ':' + self.parseFontSize( value ) + ';}}';
                                        }
                                    } );
                                } else {
                                    style += target + '{' + property + ':' + self.parseFontSize( newval ) + ';}';
                                }
                            }

                            // All other fields
                            else {

                                // Letter Spacing sanitization
                                if ( 'letter-spacing' === attribute ) {

                                    if ( $.isNumeric( newval ) ) {
                                        newval = newval + 'px'; // add px
                                    }

                                }

                                // Add style
                                style += target + '{' + property + ':' + newval + ';}';

                            }

                        } else {

                            $.each( property, function( index, value ) {
                                style += target + '{' + value + ':' + newval + ';}';
                            } );

                        }

                        if ( style ) {
                            style = '<style id="' + styleId + '">' + style + '</style>';
                        }

                        // Update previewer.
                        if ( $( '#' + styleId ).length !== 0 ) {
                            $( '#' + styleId ).replaceWith( style );
                        } else {
                            $( style ).appendTo( head );
                        }

                    }

                } );

            } );

        },

        /**
         * Parse font size
         *
         * @since 4.9.5
         */
        isJSON : function( str ) {
            try {
                return (JSON.parse(str) && !!str);
            } catch (e) {
                return false;
            }
        },

        /**
         * Parse font size
         *
         * @since 4.9.5
         */
        parseFontSize : function( val ) {

            if ( $.isNumeric( val ) ) {
                val = val + 'px';
            }

            return val;

        },

        /**
         * Load Google Font
         *
         * @since 4.1
         */
        setGoogleFonts : function( key, newval ) {

            var fontScriptID = 'wpex-customizer-' + key + '-font-stylesheet';
            var link = $( '#' + fontScriptID );

            // Remove script if it already exists
            if ( link.length ) {
                link.remove();
            }

            // Return if value is empty
            if ( ! newval ) {
                return;
            }

            // Custom or standard fonts
            if ( ( $.inArray( newval, customFonts ) > -1 ) || ( $.inArray( newval, stdFonts ) > -1 ) ) {
                return;
            }

            // Google font handle + href
            var fontHandle     = newval.trim().toLowerCase().replace( " ", "-" ),
                fontScriptHref = newval.replace( " ", "%20" );

            fontScriptHref = fontScriptHref.replace( ",", "%2C" );
            fontScriptHref = wpexTypo.googleFontsUrl + "/css?family=" + newval +  ":" + wpexTypo.googleFontsSuffix;

            // Append Google Font if newval isn't empty
            if ( newval ) {
                head.append( '<link id="' + fontScriptID +'" rel="stylesheet" href="'+ fontScriptHref +'">' );
            }

        }

    };

    wpexCustomizerTypography.init();

}( wp.customize, jQuery, window, document ) );