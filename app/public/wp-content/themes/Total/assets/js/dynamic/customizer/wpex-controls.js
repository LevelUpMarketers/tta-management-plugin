/**
 * Total Theme Customizer controls
 */

( function( api, $, window, document, undefined ) {

	"use strict";

	if ( ! wp || ! wp.customize ) {
		console.log( 'wp or wp.customize objects not found.' );
		return;
	}

	/**
	 * Custom Selects
	 */
	var controls = [
		'wpex-dropdown-pages',
		'wpex-font-family',
		'wpex-card-select',
	];

	_.each( controls, function( control ) {

		api.controlConstructor[control] = api.Control.extend( {

			ready: function() {

				if ( 'undefined' !== typeof $.fn.chosen ) {

					this.container.find( 'select' ).chosen( {
						width                    : '100%',
						search_contains          : true,
						disable_search_threshold : 5
					} );

				}

			}

		} );

	} );

	 /**
	 * Icon Selects
	 */
	var controls = [
		'wpex-fa-icon-select'
	];

	_.each( controls, function( control ) {

		api.controlConstructor[control] = api.Control.extend( {

			ready: function() {

				if ( 'undefined' !== typeof $.fn.wpexChosenIcon ) {

					this.container.find( 'select' ).wpexChosenIcon( {
						width                    : '100%',
						search_contains          : true,
						disable_search_threshold : 10
					} );

				}

			}

		} );

	} );

	/**
	 * Custom Columns
	 *
	 */
	api.controlConstructor['wpex-columns'] = api.Control.extend( {

		ready: function() {

			var control = this;

			control.container.find( '.wpex-cols-select' ).change( function( e ) {
				control.updateValue();
			} );

			control.container.find( '.wpex-toggle-settings' ).on( 'click', function() {
				control.container.find( '.wpex-customizer-columns-field > li.wpex-extra' ).toggleClass( 'wpex-hidden' );
				if ( 'false' == $( this ).attr( 'aria-expanded' ) ) {
					$( this ).attr( 'aria-expanded', 'true' );
					control.container.find( '.wpex-customizer-columns-field li.wpex-extra:first select' ).focus();
				} else {
					$( this ).attr( 'aria-expanded', 'false' );
				}
				return false;
			} );

		},

		updateValue: function() {

			var control = this,
				newValue = {},
				valCount = 0,
				$hiddenInput = control.container.find( '.wpex-hidden-input' );

			$hiddenInput.trigger( 'change' );

			control.container.find( '.wpex-cols-select' ).each( function( index, el ) {

				var $this = $( this ),
					val = $this.children( 'option:selected' ).val();

				if ( val ) {
					valCount++;
					newValue[$this.attr( 'data-name' )] = val;
				}

			} );

			if ( valCount == 0 ) {
				newValue = '';
			} else if ( valCount == 1 ) {
				newValue = newValue['d'];
			}

			control.setting.set( newValue );

		}

	} );

	/**
	 * Responsive Fields
	 */
	api.controlConstructor['wpex-responsive-field'] = api.Control.extend( {

		ready: function() {

			var control = this;

			control.container.find( '.wpex-crf-input' ).on( 'input', function( e ) {
				control.updateValue();
			} );

		},

		updateValue: function() {

			var control = this,
				newValue = {},
				valCount = 0;

			control.container.find( '.wpex-crf-input' ).each( function( index, el ) {

				var $this = $( this ),
					val = $this.val();

				if ( val ) {
					valCount++;
					newValue[$this.attr( 'data-name' )] = val;
				}

			} );

			if ( valCount == 0 ) {
			   control.setting.set( '' );
			} else {
				control.setting.set( JSON.stringify( newValue ) );
			}

		}

	} );

	/**
	 * Show/Hide Controls
	 */
	var wpexControlDisplay = {

		/**
		 * Get and loop through control display settings
		 *
		 * @since 4.4
		 */
		init : function() {

			if ( typeof wpexControlVisibility === 'undefined' ) {
				return;
			}

			var settings = wpexControlVisibility;

			_.each( settings, function( settings, id ) {

				var check = settings.check;
				var value = settings.value;

				api( check, function( setting ) {

					var isEnabled, linkSettingValueToControlActiveState;

					isEnabled = function() {
						var getSetting = setting.get();
						if ( settings.multiCheck ) {
							return ( $.inArray( getSetting, value ) != -1 ) ? true : false;
						} else {
							if ( 'true' == value ) {
								return getSetting;
							} else if ( 'false' == value ) {
								return getSetting ? false : true;
							} else {
								return value == getSetting ? true : false;
							}
						}
					};

					linkSettingValueToControlActiveState = function( control ) {

						var setActiveState = function() {
							control.active.set( isEnabled() );
						};

						// FYI: With the following we can eliminate all of our PHP active_callback code.
						control.active.validate = isEnabled;

						// Set initial active state.
						setActiveState();

						/*
						 * Update activate state whenever the setting is changed.
						 * Even when the setting does have a refresh transport where the
						 * server-side active callback will manage the active state upon
						 * refresh, having this JS management of the active state will
						 * ensure that controls will have their visibility toggled
						 * immediately instead of waiting for the preview to load.
						 * This is especially important if the setting has a postMessage
						 * transport where changing the setting wouldn't normally cause
						 * the preview to refresh and thus the server-side active_callbacks
						 * would not get invoked.
						 */
						setting.bind( setActiveState );

					};

					// Call linkSettingValueToControlActiveState on the site title and tagline controls when they exist.
					api.control( id, linkSettingValueToControlActiveState );

				} );

			} );

		}

	};

	wpexControlDisplay.init();

} ( wp.customize, jQuery, window, document ) );