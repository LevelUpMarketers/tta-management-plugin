/**
 * Js for Total theme admin panels
 */
( function( $ ) {

	'use strict';

	// Enable/disable theme panel modules
	function wpexPanelEnableDisableModules() {

		// Show notice
		$( '.wpex-checkbox' ).click( function() {
			$( '.wpex-theme-panel-updated' ).show();
		} );

		$( '.wpex-theme-panel .manage-right input[type="text"]' ).change( function() {
			$( '.wpex-theme-panel-updated' ).show();
		} );

		// Save on link click
		$( '.wpex-theme-panel-updated a' ).click( function( e ) {
			e.preventDefault();
			$( '#wpex-theme-panel-form #submit' ).click();
		} );

		// Checkbox change function - tweak classes
		$( '.wpex-checkbox' ).change(function() {
			var $this = $( this ),
				$parentTr = $this.parent( 'th' ).parent( '.wpex-module' );
			if ( $parentTr.hasClass( 'wpex-disabled' ) ) {
				$parentTr.removeClass( 'wpex-disabled' );
			} else {
				$parentTr.addClass( 'wpex-disabled' );
			}
		} );

		$( '.wpex-theme-panel-module-link' ).click( function() {
			$( '.wpex-theme-panel-updated' ).show();
			var $this     = $( this ),
				$ref      = $this.attr( 'href' ),
				$checkbox = $( $ref ).find( '.wpex-checkbox' ),
				$parentTr = $this.parents( '.wpex-module' );
			if ( $checkbox.is( ':checked' ) ) {
				$checkbox.attr( 'checked', false );
			} else {
				$checkbox.attr( 'checked', true );
			}
			if ( $parentTr.hasClass( 'wpex-disabled' ) ) {
				$parentTr.removeClass( 'wpex-disabled' );
			} else {
				$parentTr.addClass( 'wpex-disabled' );
			}
			return false;
		} );

	}

	// Panel toggles
	function wpexPanelInfoToggle() {

		var $toggle = $( '.wpex-module .column-name .wpex-toggle-icon' );

		$toggle.each( function() {
			var $this = $( this );
			var $parent = $this.parent( '.column-name' );
			$parent.css( {
				'cursor' : 'pointer'
			} );
			$parent.click( function() {
				var $this = $( this );
				$( '.wpex-module .column-name' ).not( $this ).removeClass( 'wpex-show-description' );
				$( '.wpex-toggle-icon' ).show();
				if ( $this.hasClass( 'wpex-show-description' ) ) {
					$this.removeClass( 'wpex-show-description' );
					$parent.find( '.wpex-toggle-icon' ).show();
				} else {
					$this.toggleClass( 'wpex-show-description' );
					$parent.find( '.wpex-toggle-icon' ).toggle();
				}
				return false;
			} );
		} );

	}

	// Filter
	function wpexPanelFilter() {
		var $filter_buttons = $( '.wpex-filter-active button' );
		$filter_buttons.click( function() {
			var $filterBy = $( this ).data( 'filter-by' );
			$filter_buttons.removeClass( 'active' );
			$( this ).addClass( 'active' );
			$( '.wpex-module' ).removeClass( 'wpex-filterby-hide' );
			if ( 'active' === $filterBy ) {
				$( '.wpex-module' ).each( function() {
					if ( $( this ).hasClass( 'wpex-disabled' ) ) {
						$( this ).addClass( 'wpex-filterby-hide' );
					}
				} );
			} else if ( 'inactive' === $filterBy ) {
				$( '.wpex-module' ).each( function() {
					if ( ! $( this ).hasClass( 'wpex-disabled' ) ) {
						$( this ).addClass( 'wpex-filterby-hide' );
					}
				} );
			}
			return false;
		} );
	}

	// Sort
	function wpexPanelSort() {
		$( '.wpex-theme-panel-sort a' ).click( function() {
			var $data = $( this ).data( 'category' );
			$( '.wpex-theme-panel-sort a' ).removeClass( 'wpex-active-category' );
			$( this ).addClass( 'wpex-active-category' );
			if ( 'all' === $data ) {
				$( '.wpex-module' ).removeClass( 'wpex-sort-hide' );
			} else {
				$( '.wpex-module' ).addClass( 'wpex-sort-hide' );
				$( '.wpex-category-'+ $data ).each( function() {
					$( this ).removeClass( 'wpex-sort-hide' );
				} );
			}
			return false;
		} );
	}

	// Active and non active counters
	function wpexPanelActiveCounters() {
		var activeCount   = $( '.wpex-module.wpex-active' ).length;
		var inactiveCount = $( '.wpex-module.wpex-disabled' ).length;
		$( '.wpex-active-items-btn > span' ).text( '(' + activeCount + ')' );
		$( '.wpex-inactive-items-btn > span' ).text( '(' + inactiveCount + ')' );
	}

	// Chosen dropdowns
	function wpexChosenSelect() {

		if ( 'undefined' === typeof $.fn.chosen ) {
			return;
		}

		$( '.wpex-chosen' ).chosen( {
			disable_search_threshold: 10
		} );

		$( '.wpex-chosen-multiselect' ).chosen( {
			search_contains: true
		} );

		if ( 'undefined' !== typeof $.fn.wpexChosenIcon ) {
			$( '.wpex-chosen-icon-select' ).wpexChosenIcon( {
				search_contains: true,
				disable_search_threshold: 10
			} );
		}

		$( '#wpex_header_builder_select_chosen, #wpex_footer_builder_select_chosen' ).css( 'width', '300' );

	}

	// Color picker
	function wpexColorPicker() {
		if ( 'undefined' === typeof $.fn.wpColorPicker ) {
			return;
		}
		$( '.wpex-color-field' ).wpColorPicker();
	}

	// JS tabs
	function wpexPanelTabs() {
		var $tabs = $( '.wpex-panel-js-tabs a' );
		if ( ! $tabs.length ) {
			return;
		}
		var $firstTab     = $( '.wpex-panel-js-tabs a.nav-tab-active' );
		var $firstTabHash = $firstTab.attr( 'href' ).substring(1);
		$( '.wpex-' + $firstTabHash ).show();
		$( $tabs ).each( function() {
			var $this = $( this );
			$this.click( function( e ) {
				e.preventDefault();
				$tabs.removeClass( 'nav-tab-active' );
				$this.addClass( 'nav-tab-active' );
				var $hash = $( this ).attr( 'href' ).substring(1);
				$( '.wpex-tab-content' ).hide();
				$( '.wpex-' + $hash ).show();
			} );
		} );
	}

	// Theme license activation
	function wpexLicenseAjax() {

		var $licenseForm = $( '#wpex-theme-license-form' );

		if ( ! $licenseForm.length ) {
			return;
		}

		$licenseForm.submit( function( e ) {
			e.preventDefault();

			var $form            = $( this );
			var $submit          = $form.find( '#submit' );
			var $spinner         = $form.find( '.wpex-spinner' );
			var actionProcess    = $submit.hasClass ( 'activate' ) ? 'activate' : 'deactivate';
			var $licenseField    = $form.find( 'input#wpex_license' );
			var $devlicenseField = $form.find( 'input#wpex_dev_license' );

			$( '.wpex-admin-ajax-notice' ).hide().removeClass( 'notice-warning updated notice-error' );

			$.ajax( {
				type : 'POST',
				url  : ajaxurl,
				data : {
					action     : 'wpex_theme_license_form',
					process    : actionProcess,
					license    : $form.find( 'input#wpex_license' ).val(),
					devlicense : $devlicenseField.is( ':checked' ) ? 'checked' : 0,
					nonce      : $form.find( 'input#wpex_theme_license_form_nonce' ).val()
				},
				beforeSend : function() {
					$spinner.css( 'opacity', '1' );
					$submit.prop('disabled', true );
				},
				success : function( response ) {

					$spinner.css( 'opacity', '0' );

					$submit.prop('disabled', false );

					//console.log( response );

					if ( response.success ) {

						$devlicenseField.parent().hide();

						if ( 'activate' === actionProcess ) {
							$licenseField.attr( 'readonly', 'readonly' );
							$submit.removeClass( 'activate' ).addClass( 'deactivate' ).val( $submit.data( 'deactivate' ) );
						} else if ( 'deactivate' === actionProcess ) {
							$licenseField.attr( 'placeholder', '' ).removeAttr( 'readonly' );
							$licenseField.val( '' );
							$submit.removeClass( 'deactivate' ).addClass( 'activate' ).val( $submit.data( 'activate' ) );
							if ( response.clearLicense ) {
								var curr_page = window.location.href,
									next_page = '';
								if ( curr_page.indexOf( '?' ) > -1 ) {
									next_page = curr_page + '&license-cleared=1';
								} else {
									next_page = curr_page + '?license-cleared=1';
								}
								window.location = next_page;
							} else {
								location.reload();
							}
						}

					}

					if ( response.message ) {
						$( '.wpex-admin-ajax-notice' ).addClass( response.messageClass ).html( '<p>' + response.message + '</p>' ).show();
					}
				}

			} );

		} );

	}

	// Media upload
	function wpexMediaUpload() {

		// Select & insert image
		$( '.wpex-media-upload-button' ).click( function( e ) {
			e.preventDefault();

			var button   = $( this );
			var $input   = button.prev();
			var $preview = button.parent().find( '.wpex-media-live-preview img' );
			var $remove  = button.parent().find( '.wpex-media-remove' );

			var image = wp.media( {
					library  : {
						type : 'image'
					},
					multiple: false
			} ).on( 'select', function( e ) {
				var selected = image.state().get( 'selection' ).first();
				var imageID  = selected.toJSON().id;
				var imageURL = selected.toJSON().url;

				if ( $remove.length ) {
					$remove.addClass( 'wpex-show' );
				}

				if ( $preview.length ) {
					$preview.attr( 'src', imageURL );
				} else {
					$preview = button.parent().find('.wpex-media-live-preview' );
					var $imgSize = $preview.data( 'image-size' ) ? $preview.data( 'image-size' ) : 'auto';
					$preview.append( '<img src="'+ imageURL +'" style="height:'+ $imgSize +'px;width:'+ $imgSize +'px;" />' );
				}

				$input.val( imageID ).trigger( 'change' );

			} )
			.open();
		} );

		$( '.wpex-media-remove' ).each( function() {
			var $button   = $( this );
			var $input    = $button.parent().find( '.wpex-media-input' );
			var $inputVal = $input.val();
			var $preview  = $button.parent().find( '.wpex-media-live-preview' );
			if ( $inputVal ) {
				$button.addClass( 'wpex-show' );
			}
			$button.on('click', function() {
				$input.val( '' );
				$preview.find( 'img' ).remove();
				$button.removeClass( 'wpex-show' );
				return false;
			} );
			$input.on( 'keyup change', function() {
				if ( ! $( this ).val() ) {
					$preview.find( 'img' ).remove();
					$button.removeClass( 'wpex-show' );
					return false;
				}
			} );
		} );

	}

	// Custom CSS remember to save
	function wpexPanelCustomCSS() {

		// Show notice
		$( '.wpex-custom-css-panel-wrap .form-table' ).click( function() {
			$( '.wpex-remember-to-save' ).show();
		} );

		// Save on link click
		$( '.wpex-custom-css-panel-wrap .wpex-remember-to-save a' ).click( function( e ) {
			e.preventDefault();
			$( '.wpex-custom-css-panel-wrap form #submit' ).click();
		} );

	}

	// Dashicons Select
	function wpexDashiconSelect() {
		var $buttons = $( '#wpex-dashicon-select a' );
		$buttons.click( function() {
			var $activeButton = $( '#wpex-dashicon-select a.button-primary' );
			$activeButton.removeClass( 'button-primary' ).addClass( 'button-secondary' );
			$( this ).addClass( 'button-primary' );
			$( this ).parents( '#wpex-dashicon-select' ).next( 'input' ).val( $( this ).data( 'value' ) );
			return false;
		} );
	}

	// Header/Footer ajax links
	function wpexLayoutBuilderTemplateSelector() {
		var	$select = $( '#wpex-header-builder-select, #wpex-footer-builder-select' );

		if ( ! $select.length ) {
			return;
		}

		var $tableTr   = $( '#wpex-admin-page table tr' );
		var $selectTr  = $select.parents( 'tr' );
		var $editLinks = $( '.wpex-edit-template-links-ajax' );
		var $spinner = $( '.wpex-edit-template-links-spinner' );

		// Check initial val
		if ( $select.val() ) {
			$editLinks.show();
		} else {
			$( '.wpex-create-new-template' ).show();
			$tableTr.not( $selectTr ).hide();
		}

		// Check on change
		$( $select ).change( function () {
			var val = $( this ).val();
			$editLinks.hide();
			if ( val ) {
				$tableTr.show();
				$( '.wpex-create-new-template' ).hide();
				ajaxEditLinks( val );
			} else {
				$tableTr.not( $selectTr ).hide();
				$editLinks.hide();
				$( '.wpex-create-new-template' ).show();
			}
		} );

		function ajaxEditLinks( val ) {
			var data = {
				action      : $editLinks.data( 'action' ),
				nonce       : $editLinks.data( 'nonce' ),
				template_id : val
			};

			$spinner.show();

			$.post( ajaxurl, data, function( response ) {
				if ( response ) {
					$editLinks.html( response );
					$editLinks.show();
				}
				$spinner.hide();
			} );
		}

	}

	// Image sizes panel
	function wpexImageSizes() {

		var $onFly = $( '#wpex_image_resizing' ), onFlyChecked, $deps;

		if ( ! $onFly.length) {
			return;
		}

		onFlyChecked = $onFly.prop( 'checked' );
		$deps = $( '#wpex_retina,#wpex_lazy_loading,#wpex_woo_support' );

		// Check on change
		$( $onFly ).change( function () {
			var $checked = $( this ).prop( 'checked' );
			if ( $checked ) {
				$deps.each( function() {
					var $this = $( this );
					$this.closest( 'tr' ).show();
				} );
			} else {
				$deps.each( function() {
					var $this = $( this );
					$this.closest( 'tr' ).hide();
				} );
			}
		} ).change();

	}

	// Import export panel
	function wpexImportExport() {

		var $page = $( '#wpex-import-export-admin-page' );

		if ( ! $page.length) {
			return;
		}


		$( '.wpex-highlight-options' ).click( function() {
			$( '#wpex-customizer-export' ).focus().select();
			return false;
		} );
		$( '.wpex-delete-options' ).click( function() {
			$(this).hide();
			$( '.wpex-delete-options-warning, .wpex-cancel-delete-options' ).show();
			$( '.wpex-submit-form' ).val( wpextp.confirmReset );
			$( '#wpex-reset-hidden' ).val( '-1' );
			return false;
		} );
		$( '.wpex-cancel-delete-options' ).click( function() {
			$(this).hide();
			$( '.wpex-delete-options-warning' ).hide();
			$( '.wpex-delete-options' ).show();
			$( '.wpex-submit-form' ).val( wpextp.importOptions );
			$( '#wpex-reset-hidden' ).val( '' );
			return false;
		} );
	}

	function wpexCustomizerManager() {

		var $page = $( '#wpex-customizer-manager-admin-page' );

		if ( ! $page.length) {
			return;
		}

		$( '.wpex-customizer-check-all' ).click( function() {
			$('.wpex-customizer-editor-checkbox').each( function() {
				this.checked = true;
			} );
			return false;
		} );

		$( '.wpex-customizer-uncheck-all' ).click( function() {
			$('.wpex-customizer-editor-checkbox').each( function() {
				this.checked = false;
			} );
			return false;
		} );

	}

	// Run functions on doc ready
	$( document ).ready( function() {
		wpexPanelEnableDisableModules();
		wpexPanelFilter();
		wpexPanelSort();
		wpexPanelInfoToggle();
		wpexPanelActiveCounters();
		wpexChosenSelect();
		wpexColorPicker();
		wpexPanelTabs();
		wpexMediaUpload();
		wpexLicenseAjax();
		wpexPanelCustomCSS();
		wpexDashiconSelect();
		wpexLayoutBuilderTemplateSelector();
		wpexImageSizes();
		wpexImportExport();
		wpexCustomizerManager();
	} );

} ) ( jQuery );