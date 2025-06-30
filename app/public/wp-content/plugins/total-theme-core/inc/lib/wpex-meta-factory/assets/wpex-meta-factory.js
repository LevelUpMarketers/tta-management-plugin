window.wpexMetaFactory = window.wpexMetaFactory || {};

( function( $, mf, l10n ) {

	'use strict';

	var $document = $( document );

	$document.ready( function() {

		mf.init();

	} );

	mf.init = function() {

		var $metabox = mf.metabox();

		mf.fieldSelectIcon( $metabox );

		$metabox.on( 'keydown', mf.handleKeydown );
		$metabox.on( 'change', 'input[type="text"]', mf.handleUploadChange );
		$metabox.on( 'click', '.wpex-mf-upload', mf.handleUploadClick );
		$metabox.on( 'click', '.wpex-mf-clone-group', mf.cloneGroup );
		$metabox.on( 'click', '.wpex-mf-remove-group', mf.removeGroup );

	};

	mf.metabox = function() {
		if ( mf.$metabox ) {
			return mf.$metabox;
		}
		mf.$metabox = $( '.wpex-mf-metabox' );
		return mf.$metabox;
	};

	mf.fieldSelectIcon = function( $metabox ) {

		$metabox.on( 'click', '.wpex-mf-icon-select-wrap button', function( e ) {
			e.preventDefault();
			var $this   = $( this );
			var $parent = $this.parent( '.wpex-mf-icon-select-wrap' );
			var $modal  = $parent.find( '.wpex-mf-icon-select-modal' );
			$modal.addClass( 'wpex-mf-active' );
			$modal.find( '.wpex-mf-icon-select-search' ).focus();
		} );

		$metabox.on( 'click', '.wpex-mf-icon-select-modal-choices a', function( e ) {
			e.preventDefault();
			var $this = $( this );
			var $parent = $this.closest( '.wpex-mf-icon-select-wrap' );
			var val = $this.attr( 'data-value' );
			val = ( 0 == val ) ? '' : val;
			$parent.find( 'input[type="text"]' ).val( val );
			$this.closest( '.wpex-mf-icon-select-modal' ).removeClass( 'wpex-mf-active' );
			$this.closest( '.wpex-mf-icon-select-wrap' ).find( '.wpex-mf-icon-select-preview > span' ).attr( 'class', val );
		} );

		$metabox.on( 'click', '.wpex-mf-icon-select-modal button.wpex-mf-close', function( e ) {
			e.preventDefault();
			$( this ).closest( '.wpex-mf-icon-select-modal' ).removeClass( 'wpex-mf-active' );
		} );

		$( '.wpex-mf-icon-select-search' ).on( 'keyup', function() {
			var $this  = $( this );
			var value  = $this.val().toLowerCase();
			var $icons = $this.next().find( 'a' );
			$icons.filter( function() {
				$(this).toggle( $(this ).attr( 'data-value' ).toLowerCase().indexOf( value ) > -1 );
			} );
		} );

		$( '.wpex-mf-icon-select-wrap > input[type="text"]' ).on( 'keyup', function() {
			var $this = $( this );
			var val   = $this.val().toLowerCase();
			$this.closest( '.wpex-mf-icon-select-wrap' ).find( '.wpex-mf-icon-select-preview > span' ).attr( 'class', val );
		} );

	};

	mf.handleKeydown = function( e ) {

		if ( event.keyCode == 27 ) {
			var $modal = $( '.wpex-mf-icon-select-modal' );
			if ( $modal.hasClass( 'wpex-mf-active' ) ) {
				$modal.removeClass( 'wpex-mf-active' );
			}
		}

	};

	mf.handleUploadChange = function( e ) {

		var $this = $( this );
		var $preview = $this.closest( 'td' ).find( '.wpex-mf-upload-preview' );

		if ( $preview.length && ! $( this ).val() ) {
			$preview.empty();
		}

	};

	mf.handleUploadClick = function( e ) {
		e.preventDefault();

		var $el       = $( this ),
			$td       = $el.closest( 'td' ),
			$field    = $td.find( 'input[type="text"]' );

		mf.fieldUpload( $field );

	};

	mf.fieldUpload = function( field ) {
		if ( ! wp ) {
			return;
		}

		var file_frame;

		if ( undefined !== file_frame ) {
			file_frame.open();
			return;
		}

		file_frame = wp.media.frames.file_frame = wp.media( {
			id            : 'wpex-mf-metabox-upload',
			frame         : 'post',
			state         : 'insert',
			filterable    : 'uploaded',
			multiple      : false,
			syncSelection : false,
			autoSelect    : true
		} );

		var $preview = field.closest( 'td' ).find( '.wpex-mf-upload-preview' );

		file_frame.on( 'insert', function() {
			var getSelection = file_frame.state().get( 'selection' ).first().toJSON();
			field.val( getSelection[field.data( 'selection' )] );
			if ( $preview.length ) {
				$preview.html( '<img src="' + getSelection.url  + '">' );
			}
		} );

		file_frame.open();
	};

	mf.cloneGroup = function( e ) {

		e.preventDefault();

		var $el       = $( this );
		var $groupSet = $el.prev( '.wpex-mf-group-set' );
		var $group    = $groupSet.find( '.wpex-mf-group:last' );
		var $clone    = $group.clone( 'true' );

		$clone.appendTo( $groupSet );

		mf.resetFields( $clone );

		mf.updateIndexes( $groupSet );

		mf.setFocus( $clone );

	};

	mf.resetFields = function( el ) {
		el.find( 'input' ).each( function() {
			$( this ).val( '' );
		} );
		el.find( 'select' ).each( function() {
			$( this ).val( $( this ).find( 'option:first-child' ).val() );
		} );
	};

	mf.updateIndexes = function( el ) {

		var $groups = el.find( '.wpex-mf-group' );

		$groups.each( function( i ) {

			var $this = $( this );

			$this.find( '.wpex-mf-group-set-index' ).text( (i+1).toString() );

			$this.find( 'label.wpex-mf-label' ).each( function() {
				var $this = $( this );
				$( this ).attr( 'for', $this.attr( 'for' ).replace( /-(\d+)-/, '-' + i + '-' ) );
			} );

			$this.find( 'input, select' ).each( function() {
				var $this = $( this );
				$this.attr( 'id', $this.attr( 'id' ).replace( /-(\d+)-/, '-' + i + '-' ) );
				$this.attr( 'name', $this.attr( 'name' ).replace( /\[(\d+)\]/, '[' + i + ']' ) );
			} );

		} );

	};

	mf.removeGroup = function( e ) {

		e.preventDefault();

		if ( confirm( l10n.delete_group_confirm ) ) {

			var $this = $( this );
			var $thisGroup = $this.closest( '.wpex-mf-group' );
			var index = parseInt( $thisGroup.find( '.wpex-mf-group-set-index' ).text() );
			var $groupSet = $( this ).closest( '.wpex-mf-group-set' );
			var $groups = $groupSet.find( '.wpex-mf-group' );

			// Delete group if we have more than 1
			if ( $groups.length > 1 ) {
				$thisGroup.remove();
				mf.updateIndexes( $groupSet, index );
			}

			// We only have one group, lets clear inputs instead
			else {
				mf.resetFields( $thisGroup );
			}

		}

	};

	mf.setFocus = function( el) {
		el.find( 'select, input, textarea, button, a' ).filter( ':visible' ).first().focus();
	};

} ) ( jQuery, window.wpexMetaFactory, wpexMetaFactoryL10n );