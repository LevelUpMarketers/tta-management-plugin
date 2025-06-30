( function( $ ) {

	'use strict';

	if ( 'function' !== typeof $.fn.wpexChosenIcon ) {
		$.fn.wpexChosenIcon = function( options ) {

			return this.each( function() {

				var $select = $( this ),
					iconMap = {},
					$chosen;

				// Retrieve icon class from data attribute and build object for each list item
				$select.find( 'option' ).filter( function () {
					return $(this).text();
				} ).each( function (i) {
					var iconClass = $(this).attr( 'data-icon' );
					iconMap[i] = $.trim( iconClass );
				} );

				// Execute chosen plugin
				$select.chosen( options );

				// Store chosen element
				$chosen = $select.next( '.chosen-container' ).addClass( 'wpex-chosen-icon-container' );

				// Add icon to dropdown items
				$select.on( 'chosen:showing_dropdown chosen:activate', function( evt, params ) {
					params.chosen.container.find( '.chosen-results li' ).each( function( i ) {
						var $this = $( this );
						var icon = iconMap[i];
						if ( icon && ! $this.find( '.wpex-chosen-icon-i' ).length ) {
							$this.prepend( icon_html( icon ) );
						}
					} );
				} );

				// Update on search
				$chosen.find( '.chosen-search-input' ).keyup( function() {
					setTimeout( function () {
						$chosen.find( '.chosen-results li' ).each( function() {
							var $this = $( this );
							var index = $this.data( 'option-array-index' )
							var icon = iconMap[index];
							if ( icon && ! $this.find( '.wpex-chosen-icon-i' ).length ) {
								$this.prepend( icon_html( icon ) );
							}
						} );
					}, 0 );
				} );

				// Display icon for selected option
				$select.change( function () {
					var icon = ( $select.find( 'option:selected' ).attr('data-icon') )
						? $select.find( 'option:selected' ).attr( 'data-icon' ) : null;
					if ( icon ) {
						$chosen.find( '.chosen-single' ).css( {
							'height'      : '30px',
							'line-height' : '29px'
						} );
						if ( ! $chosen.find( '.chosen-single .wpex-chosen-icon-i' ).length ) {
							$chosen.find( '.chosen-single span' ).prepend( icon_html( icon ) );
						}
					}

				} );

				$select.trigger( 'change' );

				function icon_html( icon ) {
					var dir = 'right';
					if ( document.dir == 'rtl' ) {
						dir = 'left';
					}
					return '<i class="' + icon + ' wpex-chosen-icon-i" aria-hidden="true" style="margin-' + dir +':8px;line-height:inherit;"></i>';
				}

			} );

		};

	}


} ) ( jQuery );