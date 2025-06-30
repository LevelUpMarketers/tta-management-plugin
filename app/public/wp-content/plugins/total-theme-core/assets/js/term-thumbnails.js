var wpexTermThumbnails = wpexTermThumbnails || {};

( function( $ ) {

	'use strict';

	var $doc = $( document );

	/*	-----------------------------------------------------------------------------------------------
		Function Calls
	--------------------------------------------------------------------------------------------------- */
	$doc.ready( function() {

		wpexTermThumbnails.selectThumbnail.init();

	} );

	/*	-----------------------------------------------------------------------------------------------
		Check if user agent is a mobile device
	--------------------------------------------------------------------------------------------------- */
	wpexTermThumbnails.selectThumbnail = {

		init: function() {
			this.addButton();
			this.removeButton();
		},

		addButton: function() {

			$( document ).on( 'click', '#wpex-add-term-thumbnail', function( e ) {
				e.preventDefault();

				var $preview_img = $( '#wpex-term-thumbnail-preview img' );

				var image = wp.media( {
					library  : {
						type : 'image'
					},
					multiple: false
				} ).on( 'select', function( e ) {
					var selected = image.state().get( 'selection' ).first();
					var imageID  = selected.toJSON().id;
					var imageURL = selected.toJSON().url;

					$( '#wpex-term-thumbnail-remove' ).show();

					if ( $preview_img.length ) {
						$preview_img.attr( 'src', imageURL );
					} else {
						var $preview = $( '#wpex-term-thumbnail-preview' );
						var $imgSize = $preview.data( 'image-size' ) ? $preview.data( 'image-size' ) : '80';
						$preview.append( '<img src="'+ imageURL +'" height="' + $imgSize + '" width="'+ $imgSize + '" style="margin-top:10px;" />' );
					}

					$( '#wpex_term_thumbnail' ).val( imageID ).trigger( 'change' );

				} )
				.open();
			} );

		},

		removeButton: function() {
			$( document ).on( 'click', '#wpex-term-thumbnail-remove', function( e ) {
				e.preventDefault();
				var $this = $( this );
				$( '#wpex_term_thumbnail' ).val( '' );
				$( '#wpex-term-thumbnail-preview' ).find( 'img' ).remove();
				$this.hide();
			} );
		}

	};

} ) ( jQuery );