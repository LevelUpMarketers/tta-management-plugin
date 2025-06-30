( function( $ ) {
	'use strict';

	/********************************
	/***** Chosen Selects ***********
	/********************************/
	if ( $.fn.chosen !== undefined ) {

		$( '.vcex-chosen' ).chosen( {
			search_contains: true,
			inherit_select_classes: true
		} );

		$( '.vcex-chosen-alt' ).chosen( {
			disable_search: true,
			inherit_select_classes: true
		} );

	}

	/********************************
	/***** Alignment Select *********
	/********************************/
	var $alignOpt = $( '.vcex-alignments-param .vcex-alignment-opt' );

	$alignOpt.on( 'click', function() {

		var $this        = $( this ),
			$parent      = $this.parent( '.vcex-alignments-param' ),
			$hiddenInput = $parent.find( '.vcex-hidden-input' );

		$parent.find( '.vcex-alignment-opt' ).removeClass( 'vcex-active' );
		$this.addClass( 'vcex-active' );
		$hiddenInput.val( $this.data( 'value' ) ).trigger( 'change' );

	} );

	/********************************
	/***** Number option prevent incorrect input *********
	/********************************/
	var $numberOpt = $( '.vcex-number-param' );

	$numberOpt.change( function() {
		var val = $( this ).val();
		var min = parseInt( $( this ).attr( 'min' ) );
		var max = parseInt( $( this ).attr( 'max' ) );
		if ( min && max && val ) {
			if ( val > max ) {
				$( this ).val( max );
			} else if ( val < min ) {
				$( this ).val( min );
			}
		}
	} );

	/********************************
	/***** Custom Selects *********
	/********************************/
	var $customSelect = $( '.vcex-custom-select .vcex-opt' );

	$customSelect.on( 'click', function() {

		var $this        = $( this ),
			$parent      = $this.parent( '.vcex-custom-select' ),
			$hiddenInput = $parent.find( '.vcex-hidden-input' );

		$parent.find( '.vcex-opt' ).removeClass( 'vcex-active' );
		$this.addClass( 'vcex-active' );
		$hiddenInput.val( $this.data( 'value' ) ).trigger( 'change' );

	} );


	/********************************
	/***** On/Off Switch ************
	/********************************/
	var $switch = $( '.vcex-ofswitch .vcex-btn' );

	$switch.on( 'click', function() {

		var $this        = $( this ),
			$parent      = $this.parent( '.vcex-ofswitch' ),
			$hiddenInput = $parent.find( '.vcex-hidden-input' );

		$parent.find( '.vcex-btn' ).removeClass( 'vcex-active' );
		$this.addClass( 'vcex-active' );
		$hiddenInput.val( $this.data( 'value' ) ).trigger( 'change' );

	} );


	/********************************
	/***** Sorter ***********
	/********************************/
	if ( $.fn.sortable !== undefined ) {

		// Define variables
		var sorterParam = $( '.vcex-sorter-param' );

		sorterParam.each( function() {

			var $this = $( this );

			// Create sortable
			$this.sortable();
			$this.disableSelection();

			// Update values on sortstop
			$this.on( 'sortstop', function( event, ui ) {
				vcexUpdateSortable( $this );
			} );

			// Toggle classes
			$this.find( 'li > a' ).click( function() {
				$( this ).find( '.ticon' ).toggleClass( 'ticon-rotate-180' );
				$( this ).parents( 'li:eq(0)' ).toggleClass( 'vcex-disabled' );
				vcexUpdateSortable( $this );
			} );

		} );

	}

	// Update sorter hidden field
	function vcexUpdateSortable( el ) {
		var values = [];
		el.find( 'li' ).each( function() {
			if ( ! $( this ).hasClass( 'vcex-disabled' ) ) {
				values.push( $( this ).attr( 'data-value' ) );
			}
		} );
		$( el ).next( 'input.wpb_vc_param_value' ).val( values ).trigger( 'change' );
	}

	/********************************
	/***** Responsive Columns *******
	/********************************/
	$( '.vcex-responsive-column-select' ).on( 'change', function( e ) {

		var valArray = [];

		var $parent      = $( this ).closest( '.vcex-responsive-columns-param' ),
			$allSettings = $parent.find( 'select' ),
			$hiddenInput = $parent.find( 'input.wpb_vc_param_value' );

			$allSettings.each( function( index, el ) {

				var $this = $( this ),
					val   = $this.val();

				if ( val ) {
					var parsed = $this.attr( 'name' ) + ':' + val;
					valArray.push( parsed );
				}

			} );

			$hiddenInput.val( valArray.join('|') );

	} );



	/********************************
	/***** Responsive Sizes *******
	/********************************/

	// Responsive font sizes check
	$( ".vc_shortcode-param[data-vc-shortcode-param-name='responsive_text'], .vc_shortcode-param[data-vc-shortcode-param-name='responsive_font_size']" ).each( function() {

		var $this                    = $( this ),
			$input                   = $this.find( '.wpb-input' ),
			$prevFontSize            = $this.prev(),
			$prevFontSizeInputs      = $prevFontSize.find( '.vcex-input' ),
			$prevFontSizeItems       = $prevFontSize.find( '.vcex-item' ),
			$prevFontSizeFirstItem   = $prevFontSize.find( '.vcex-item-1' ),
			$prevFontSizeHiddenField = $prevFontSize.find( 'input.vcex_responsive_sizes_field' );

		if ( $input.val() == 'true' ) {
			$prevFontSizeItems.hide();
			$prevFontSizeFirstItem.show();
			$prevFontSizeHiddenField.val( $prevFontSizeFirstItem.find( '.vcex-input' ).val() );
		}

		$input.on( 'input change', function( e ) {

			if ( $input.val() == 'true' ) {

				$prevFontSizeItems.hide();
				$prevFontSizeFirstItem.show();
				$prevFontSizeHiddenField.val( $prevFontSizeFirstItem.find( '.vcex-input' ).val() );

			} else {
				if ( $prevFontSizeFirstItem.find( '.vcex-input' ).val() ) {
					$prevFontSizeItems.show();
				}
				$prevFontSizeHiddenField.val( vcexGetResponsiveSizeValue( $prevFontSizeInputs ) );

			}

		} );

	} );

	// Update top/right/bottom/left fields hidden input
	$( '.vcex-trbl-param .vcex-input' ).on( 'input', function( e ) {

		var $this        = $( this ),
			$parent      = $this.closest( '.vcex-trbl-param' ),
			$allSettings = $parent.find( '.vcex-input' ),
			$hiddenInput = $parent.find( 'input.vcex-hidden-input' ),
			valArray     = [];

		$allSettings.each( function( index, el ) {

			var $this = $( this ),
				val = $this.val();

			if ( val ) {

				/*if ( val.indexOf( 'px' ) == -1
                    && val.indexOf( 'em' ) == -1
                    && val.indexOf( '%' ) == -1
                     && val.indexOf( 'auto' ) == -1
                ) {

                    val = parseInt( val ); // set to integer
                    val = val + 'px'; // Add px
                   // $this.val( val );

                }*/

                var parsed = $this.attr( 'name' ) + ':' + val;
				valArray.push( parsed );
			}

		} );

		$hiddenInput.val( valArray.join( '|' ) );

	} );

	// Update Responsive font size hidden field with correct values
	$( '.vcex-rs-param .vcex-input' ).on( 'input', function( e ) {

		var $this                  = $( this ),
			$parent                = $this.closest( '.vcex-rs-param' ),
			$topParent             = $parent.closest( '.wpb_el_type_vcex_responsive_sizes' ),
			$nextResponsiveSetting = $topParent.next().find( 'select.responsive_text' ),
			$allSettings           = $parent.find( '.vcex-input' ),
			$hiddenInput           = $parent.find( 'input.vcex_responsive_sizes_field' );

			if ( $this.parent().hasClass( 'vcex-item-1' ) ) {

				if ( $this.val() ) {
					$parent.find( '.vcex-item' ).show();
				} else {
					$parent.find( '.vcex-item' ).not( '.vcex-item-1' ).hide();
					$hiddenInput.val( '' );
					return;
				}

				if ( $nextResponsiveSetting && 'true' == $nextResponsiveSetting.val() ) {
					$parent.find( '.vcex-item' ).not( '.vcex-item-1' ).hide();
					$hiddenInput.val( $this.val() );
					return;
				}

			}

			$hiddenInput.val( vcexGetResponsiveSizeValue( $allSettings ) );

	} );

	function vcexGetResponsiveSizeValue( $allSettings ) {

		var valArray = [],
			$firstInput = '';

		$allSettings.each( function( index, el ) {

			var $this = $( this ),
				val   = $this.val();

			if ( $this.parent().hasClass( 'vcex-item-1' ) ) {
				$firstInput = $this;
			}

			if ( val ) {
				var parsed = $this.attr( 'name' ) + ':' + val;
				valArray.push( parsed );
			}

		} );

		if ( valArray.length == 1 && $firstInput.val() ) {
			return $firstInput.val();
		} else {
			return valArray.join('|');
		}

	}

} ( jQuery ) );