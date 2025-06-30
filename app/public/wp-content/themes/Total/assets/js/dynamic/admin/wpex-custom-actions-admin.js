window.wpexCustomActionsAdmin = window.wpexCustomActionsAdmin || {};

( function( $, ac ) {

	'use strict';

	/* Function Calls
	--------------------------------------------------------------------------------------------------- */
	$( document ).ready( function() {
		ac.toggles.init();
	} );

	/* Toggles
	--------------------------------------------------------------------------------------------------- */
	ac.toggles = {

		init: function() {

			$( '.wpex-ca-admin-list-item-header' ).click( function( event ) {
				event.preventDefault();
				var $this = $( this );
				var $parent = $this.parent();
				var $button = $this.find( '.wpex-ca-admin-toggle' );

				if ( $parent.hasClass( 'wpex-ca-closed' ) ) {
					$parent.removeClass( 'wpex-ca-closed' );
					$button.attr( 'aria-expanded', 'true' );
				} else {
					$parent.addClass( 'wpex-ca-closed' );
					$button.attr( 'aria-expanded', 'false' );
				}

				$( '.wpex-ca-admin-list-item' ).not( $parent ).addClass( 'wpex-ca-closed' );
				$( '.wpex-ca-admin-toggle' ).not( $button ).attr( 'aria-expanded', 'false' );

			} );

		}

	};

} ) ( jQuery, wpexCustomActionsAdmin );