/**
 * Project: Total WordPress Theme
 * Description: Initialize all scripts and add custom js
 * Author: WPExplorer
 * Theme URI: http://www.wpexplorer.com
 * Author URI: http://www.wpexplorer.com
 * License: Custom
 * License URI: http://themeforest.net/licenses
 * Version 5.0.1
 */

var wpex = {};

( function( $, l10n ) {

	'use strict';

	wpex = {

		/**
		 * Main init function.
		 */
		init : function() {
			this.config();
			this.bindEvents();
		},

		/**
		 * Define vars for caching.
		 */
		config : function() {

			this.config = {
				localScrollOffset   : 0,
				localScrollSections : []
			};

		},

		/**
		 * Bind Events.
		 */
		bindEvents : function() {
			var self = this;

			/*** Run on Document Ready ***/
			$( document ).ready( function() {

				var bodyClass = 'wpex-docready';

				if ( self.retinaCheck() ) {
					bodyClass += ' wpex-is-retina';
				}

				if ( self.mobileCheck() ) {
					bodyClass += ' wpex-is-mobile-device';
				}

				$( 'body' ).addClass( bodyClass );

				self.localScrollSections();
				self.superfish();
				self.mobileMenu();
				self.navNoClick();
				self.hideEditLink();
				self.menuWidgetAccordion();
				self.inlineHeaderLogo();
				self.menuSearch();
				self.headerCart();
				self.backTopLink();
				self.smoothCommentScroll();
				self.toggleBar();
				self.localScrollLinks();
				self.customSelects();
				self.lightbox();
				self.masonryGrids();
				self.overlaysMobileSupport();
				self.vcAccessability();

			} );

			/*** Run on Window Load ***/
			$( window ).on( 'load', function() {

				// Add window loaded css tag to body
				$( 'body' ).addClass( 'wpex-window-loaded' );

				// Main
				self.megaMenusWidth();
				self.megaMenusTop();
				self.sliderPro();
				self.parallax();
				self.stickyTopBar();
				self.vcTabsTogglesJS();
				self.overlayHovers();
				self.headerOverlayOffset(); // Add before sticky header ( important )
				self.equalHeights();

				// Sticky functions
				self.stickyHeader();
				self.stickyHeaderMenu();

				// Set localScrollOffset after site is loaded to make sure it includes dynamic items including sticky elements
				self.parseLocalScrollOffset( 'init' );

				// Run methods after sticky header
				self.footerReveal(); // Footer Reveal => Must run before fixed footer!!!
				self.fixedFooter(); // Fixed Footer => Must run after footerReveal!!!

				// Scroll to hash (must be last)
				if ( l10n.scrollToHash ) {
					window.setTimeout( function() {
						self.scrollToHash( self );
					}, parseInt( l10n.scrollToHashTimeout ) );
				}

			} );

			/*** Run on Window Resize ***/
			$( window ).resize( function() {

				self.megaMenusWidth();
				self.overlayHovers();

			} );

			/*** Run on Window Scroll ***/
			$( window ).scroll( function() {

				self.localScrollHighlight();

			} );

			/*** Run on Orientation Change ***/
			$( window ).on( 'orientationchange', function() {

				self.megaMenusWidth();
				self.overlayHovers();
				self.masonryGrids(); // @todo maybe remove?

			} );

		},

		/**
		 * Updates config whenever the window is resized.
		 */
		widthResizeUpdateConfig: function() {
			this.parseLocalScrollOffset( 'resize' );
		},

		/**
		 * Retina Check.
		 */
		retinaCheck: function() {
			var mediaQuery = '(-webkit-min-device-pixel-ratio: 1.5), (min--moz-device-pixel-ratio: 1.5), (-o-min-device-pixel-ratio: 3/2), (min-resolution: 1.5dppx)';
			if ( window.devicePixelRatio > 1 ) {
				return true;
			}
			if ( window.matchMedia && window.matchMedia( mediaQuery ).matches ) {
				return true;
			}
			return false;
		},

		/**
		 * Mobile Check.
		 */
		mobileCheck: function() {
			if ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test( navigator.userAgent ) ) {
				return true;
			}
		},

		/**
		 * Viewport width.
		 */
		viewportWidth: function() {
			var e = window, a = 'inner';
			if ( ! ( 'innerWidth' in window ) ) {
				a = 'client';
				e = document.documentElement || document.body;
			}
			return e[ a+'Width' ];
		},

		/**
		 * Superfish menus.
		 */
		superfish: function() {

			if ( 'undefined' === typeof $.fn.superfish ) {
				return;
			}

			// @todo pass a full object to the superFish settings so customers can change anything.
			$( '#site-navigation ul.sf-menu' ).superfish( {
				delay       : l10n.superfishDelay,
				speed       : l10n.superfishSpeed,
				speedOut    : l10n.superfishSpeedOut,
				cssArrows   : false,
				disableHI   : false,
				animation   : {
					opacity : 'show'
				},
				animationOut : {
					opacity  : 'hide'
				}
			} );

		},

		 /**
		 * MegaMenus Width.
		 */
		megaMenusWidth: function() {

			if ( ! l10n.megaMenuJS ) {
				return;
			}

			var $navWrap = $( '#site-header.header-one .container #site-navigation-wrap' );

			if ( ! $navWrap.length || ! $navWrap.is( ':visible' ) ) {
				return;
			}

			var $megamenu = $navWrap.find( '.megamenu > ul' );

			if ( ! $megamenu.length ) {
				return;
			}

			var containerWidth = $( '#site-header.header-one .container' ).outerWidth();
			var navWidth = $navWrap.outerWidth();
			var navPos = parseInt( $navWrap.css( 'right' ) );

			if ( 'auto' == navPos ) {
				navPos = 0;
			}

			$megamenu.css( {
				'width'       : containerWidth,
				'margin-left' : -(containerWidth-navWidth-navPos)
			} );

		},

		/**
		 * MegaMenus Top Position.
		 */
		megaMenusTop: function() {

			if ( ! l10n.megaMenuJS ) {
				return;
			}

			var $navWrap = $( '#site-header.header-one #site-navigation-wrap:not(.wpex-flush-dropdowns)' );

			if ( ! $navWrap.length ) {
				return;
			}

			var $megamenu = $navWrap.find( '.megamenu > ul' );

			if ( ! $megamenu.length ) {
				return;
			}

			var $header = $( '#site-header.header-one' );

			function run() {
				if ( $navWrap.is( ':visible' ) ) {
					var headerHeight = $header.outerHeight();
					var navHeight    = $navWrap.outerHeight();
					var megaMenuTop  = headerHeight - navHeight;
					$megamenu.css( {
						'top' : megaMenuTop/2 + navHeight
					} );
				}
			}

			run();

			$( window ).scroll( function() {
				run();
			} );

			$( window ).resize( function() {
				run();
			} );

			$( '.megamenu > a', $navWrap ).hover( function() {
				run();
			} );

		},

		/**
		 * Mobile Menu.
		 */
		mobileMenu: function() {
			if ( 'sidr' == l10n.mobileMenuStyle ) {
				if ( 'undefined' !== typeof l10n.sidrSource ) {
					this.mobileMenuSidr();
				}
			} else if ( 'toggle' == l10n.mobileMenuStyle ) {
				this.mobileMenuToggle();
			} else if ( 'full_screen' == l10n.mobileMenuStyle ) {
				this.mobileMenuFullScreen();
			}
		},

		/**
		 * Mobile Menu.
		 */
		mobileMenuSidr: function() {

			if ( 'undefined' === typeof $.fn.sidr ) {
				return;
			}

			var self = this;
			var $body = $( 'body' );
			var $toggleBtn = $( 'a.mobile-menu-toggle, li.mobile-menu-toggle > a' );
			var $mobileAlt = $( '#mobile-menu-alternative' );

			// Add dark overlay to content
			$body.append( '<div class="wpex-sidr-overlay wpex-hidden"></div>' );
			var $sidrOverlay = $( '.wpex-sidr-overlay' );

			// Add active class to toggle button
			$toggleBtn.click( function() {
				$( this ).toggleClass( 'wpex-active' );
			} );

			// Add sidr
			$toggleBtn.sidr( {
				name     : 'sidr-main',
				source   : l10n.sidrSource,
				side     : l10n.sidrSide,
				displace : l10n.sidrDisplace,
				speed    : parseInt( l10n.sidrSpeed ),
				renaming : true,
				bind     : 'click',
				onOpen: function() {

					// Change aria labels
					$toggleBtn.attr( 'aria-expanded', 'true' );
					$sidrClosebtn.attr( 'aria-expanded', 'true' );

					// Add extra classname
					$( '#sidr-main' ).addClass( 'wpex-mobile-menu' );

					// Prevent body scroll
					if ( l10n.sidrBodyNoScroll ) {
						$body.addClass( 'wpex-noscroll' );
					}

					// FadeIn Overlay
					$sidrOverlay.removeClass( 'wpex-hidden' );
					$sidrOverlay.addClass( 'wpex-custom-cursor' );

					// Set focus styles
					self.focusOnElement( $( '#sidr-main' ) );

				},
				onClose: function() {

					// Alter aria labels
					$toggleBtn.attr( 'aria-expanded', 'false' );
					$sidrClosebtn.attr( 'aria-expanded', 'false' );

					// Remove active class
					$toggleBtn.removeClass( 'wpex-active' );

					// Remove body noscroll class
					if ( l10n.sidrBodyNoScroll ) {
						$body.removeClass( 'wpex-noscroll' );
					}

				},
				onCloseEnd: function() {

					// Remove active dropdowns
					$( '.sidr-class-menu-item-has-children.active' ).removeClass( 'active' ).find( 'ul' ).hide();

					// Re-trigger stretched rows to prevent issues if browser was resized while
					// sidr was open
					if ( 'undefined' !== typeof window.vc_rowBehaviour ) {
						window.vc_rowBehaviour();
					}

					// FadeOut overlay
					$sidrOverlay.removeClass( 'wpex-custom-cursor' ).addClass( 'wpex-hidden' );

				}

			} );

			// Cache sidebar el
			var $sidrMain = $( '#sidr-main' );

			// Insert mobile menu extras
			$sidrMain.prepend( $( '<div class="sidr-class-wpex-close"><a href="#" aria-expanded="false" role="button" aria-label="' + l10n.mobileMenuCloseAriaLabel + '">&times;</a></div>' ) );
			self.insertExtras( $( '.wpex-mobile-menu-top' ), $( '.sidr-inner', $sidrMain ), 'prepend' );
			self.insertExtras( $( '.wpex-mobile-menu-bottom' ), $( '.sidr-inner', $sidrMain ), 'append' );

			// Cache close button
			var $sidrClosebtn = $( '.sidr-class-wpex-close > a', $sidrMain );

			// Make sure dropdown-menu is included in sidr-main which may not be included in certain header styles like dev header style
			$sidrMain.find( '.sidr-class-main-navigation-ul' ).addClass( 'sidr-class-dropdown-menu' );

			// Sidr dropdown toggles
			var $sidrMenu = $( '.sidr-class-dropdown-menu', $sidrMain );

			// Create menuAccordion
			self.menuAccordion( $sidrMenu );

			// Re-name font Icons to correct classnames
			// @todo can we optimize this? Maybe instead of renaming have list of classes to exclude from prefix in sidr.js
			$( "[class*='sidr-class-fa']", $sidrMain ).attr( 'class', function( i, c ) {
				c = c.replace( 'sidr-class-fa', 'fa' );
				c = c.replace( 'sidr-class-fa-', 'fa-' );
				return c;
			} );
			$( "[class*='sidr-class-ticon']", $sidrMain ).attr( 'class', function( i, c ) {
				c = c.replace( 'sidr-class-ticon', 'ticon' );
				c = c.replace( 'sidr-class-ticon-', 'ticon-' );
				return c;
			} );

			// Close sidr when clicking toggle
			$sidrClosebtn.on( 'click', function() {
				$.sidr( 'close', 'sidr-main' );
				$toggleBtn.focus();
				return false;
			} );

			// Close on resize past mobile menu breakpoint
			$( window ).resize( function() {
				if ( self.viewportWidth() >= l10n.mobileMenuBreakpoint ) {
					$.sidr( 'close', 'sidr-main' );
				}
			} );

			// Close sidr when clicking local scroll link
			$( 'li.sidr-class-local-scroll > a', $sidrMain ).click( function() {
				var $hash = this.hash;
				if ( $.inArray( $hash, self.config.localScrollSections ) > -1 ) {
					$.sidr( 'close', 'sidr-main' );
					self.scrollTo( $hash );
					return false;
				}
			} );

			// Close sidr when clicking on overlay
			$sidrOverlay.on( 'click', function() {
				$.sidr( 'close', 'sidr-main' );
				if ( 'undefined' !== typeof window.vc_rowBehaviour ) {
					window.vc_rowBehaviour(); // fixes bug with clicking overlay...@todo revise/remove
				}
				return false;
			} );

			// Close when clicking esc
			$sidrMain.keydown( function( e ) {
				if ( e.keyCode === 27 ) {
					$.sidr( 'close', 'sidr-main' );
					$toggleBtn.focus();
				}
			} );

			// Remove mobile menu alternative if on page to prevent duplicate links
			if ( $mobileAlt.length ) {
				$mobileAlt.remove();
			}

		},

		/**
		 * Toggle Mobile Menu.
		 *
		 */
		mobileMenuToggle: function() {
			var self = this;

			var $position     = l10n.mobileToggleMenuPosition;
			var $classes      = 'mobile-toggle-nav wpex-mobile-menu wpex-clr wpex-togglep-' + $position;
			var $mobileAlt    = $( '#mobile-menu-alternative' );
			var $mobileSearch = $( '#mobile-menu-search' );
			var $appendTo     = $( '#site-header' );
			var $toggleBtn    = $( 'a.mobile-menu-toggle, li.mobile-menu-toggle > a' );

			// Insert nav in fixed_top mobile menu
			if ( 'fixed_top' == l10n.mobileMenuToggleStyle ) {
				$appendTo = $( '#wpex-mobile-menu-fixed-top' );
				if ( $appendTo.length ) {
					$appendTo.append( '<nav class="' + $classes + '" aria-label="' + l10n.mobileMenuAriaLabel + '"></nav>' );
				}
			}

			// Absolute position
			else if ( 'absolute' == $position ) {
				if ( 'navbar' == l10n.mobileMenuToggleStyle ) {
					$appendTo = $( '#wpex-mobile-menu-navbar' );
					if ( $appendTo.length ) {
						$appendTo.append( '<nav class="'+ $classes +'" aria-label="' + l10n.mobileMenuAriaLabel + '"></nav>' );
					}
				} else if ( $appendTo.length ) {
					$appendTo.append( '<nav class="'+ $classes +'" aria-label="' + l10n.mobileMenuAriaLabel + '"></nav>' );
				}
			}

			// Insert afterSelf
			else if ( 'afterself' == $position ) {
				$appendTo = $( '#wpex-mobile-menu-navbar' );
				$( '<nav class="' + $classes + '" aria-label="' + l10n.mobileMenuAriaLabel + '"></nav>' ).insertAfter( $appendTo );
			}
			// Normal toggle insert (static)
			else {
				$( '<nav class="' + $classes + '" aria-label="' + l10n.mobileMenuAriaLabel + '"></nav>' ).insertAfter( $appendTo );
			}

			// Grab all content from menu and add into mobile-toggle-nav element
			var $mobileMenuContents = '';
			if ( $mobileAlt.length ) {
				$mobileMenuContents = $( '.dropdown-menu', $mobileAlt ).html();
				$mobileAlt.remove();
			} else {
				$mobileMenuContents = $( '.main-navigation-ul' ).html();
			}

			// Create mobile menu
			var $mobileToggleNav = $( '.mobile-toggle-nav' );
			$mobileToggleNav.html( '<div class="mobile-toggle-nav-inner container"><ul class="mobile-toggle-nav-ul">' + $mobileMenuContents + '</ul></div>' );

			// Remove all styles
			$( '.mobile-toggle-nav-ul, .mobile-toggle-nav-ul *' ).children().each( function() {
				$( this ).removeAttr( 'style' );
			} );

			// Remove ID's for accessibility reasons
			$( '.mobile-toggle-nav-ul, .mobile-toggle-nav-ul *' ).removeAttr( 'id' );

			// Add search to toggle menu
			if ( $mobileSearch.length ) {
				$( '.mobile-toggle-nav-inner', $mobileToggleNav ).append( '<div class="mobile-toggle-nav-search"></div>' );
				$( '.mobile-toggle-nav-search' ).append( $mobileSearch );
				$mobileSearch.removeClass( 'wpex-hidden' );
			}

			// Insert mobile menu extras
			self.insertExtras( $( '.wpex-mobile-menu-top' ), $( '.mobile-toggle-nav-inner', $mobileToggleNav ), 'prepend' );
			self.insertExtras( $( '.wpex-mobile-menu-bottom' ), $( '.mobile-toggle-nav-inner', $mobileToggleNav ), 'append' );

			// Create menuAccordion
			self.menuAccordion( $mobileToggleNav );

			// On Show
			function openToggle( $button ) {
				if ( l10n.animateMobileToggle ) {
					$mobileToggleNav.stop( true, true ).slideDown( 'fast', function() {
						self.focusOnElement( $mobileToggleNav );
					} ).addClass( 'visible' );
				} else {
					$mobileToggleNav.addClass( 'visible' );
					self.focusOnElement( $mobileToggleNav );
				}
				$button.addClass( 'wpex-active' ).attr( 'aria-expanded', 'true' );
			}

			// On Close
			function closeToggle( $button ) {
				if ( l10n.animateMobileToggle ) {
					$mobileToggleNav.stop( true, true ).slideUp( 'fast' ).removeClass( 'visible' );
				} else {
					$mobileToggleNav.removeClass( 'visible' );
				}
				$mobileToggleNav.find( 'li.active > ul' ).stop( true, true ).slideUp( 'fast' );
				$mobileToggleNav.find( '.active' ).removeClass( 'active' );
				$button.removeClass( 'wpex-active' ).attr( 'aria-expanded', 'false' );
			}

			// Show/Hide
			$toggleBtn.on( 'click', function() {
				if ( $mobileToggleNav.hasClass( 'visible' ) ) {
					closeToggle( $( this ) );
				} else {
					openToggle( $( this ) );
				}
				return false;
			} );

			// Close when clicking esc
			$mobileToggleNav.keydown( function( e ) {
				if ( e.keyCode === 27 && $mobileToggleNav.hasClass( 'visible' ) ) {
					closeToggle( $toggleBtn );
					$toggleBtn.focus();
				}
			} );

			// Close on resize
			$( window ).resize( function() {
				if ( self.viewportWidth() >= l10n.mobileMenuBreakpoint && $mobileToggleNav.hasClass( 'visible' ) ) {
					closeToggle( $toggleBtn );
				}
			} );

		},

		/**
		 * Overlay Mobile Menu.
		 */
		mobileMenuFullScreen: function() {

			var self           = this;
			var $style         = l10n.fullScreenMobileMenuStyle || false; // prevent undefined class
			var $mainMenu      = $( '#site-navigation .main-navigation-ul' );
			var $mobileMenuAlt = $( '#mobile-menu-alternative' );
			var $mobileSearch  = $( '#mobile-menu-search' );

			// Check and grab nav content
			var menuHTML = '';
			if ( $mobileMenuAlt.length ) {
				menuHTML = $( '.dropdown-menu', $mobileMenuAlt ).html();
				$mobileMenuAlt.remove();
			} else if ( $mainMenu.length ) {
				menuHTML = $mainMenu.html();
			}

			// No menu, bail.
			if ( ! menuHTML ) {
				return;
			}

			// Insert new nav
			$( 'body' ).append( '<div class="full-screen-overlay-nav wpex-mobile-menu wpex-clr ' + $style + '"><button class="full-screen-overlay-nav-close">&times;</button><div class="full-screen-overlay-nav-content"><div class="full-screen-overlay-nav-content-inner"><nav class="full-screen-overlay-nav-menu"><ul></ul></nav></div></div></div>' );

			var $navUL = $( '.full-screen-overlay-nav-menu > ul' );

			$navUL.html( menuHTML );

			// Cache elements
			var $nav        = $( '.full-screen-overlay-nav' );
			var $menuButton = $( '.mobile-menu-toggle' );

			// Add initial aria attributes
			$nav.attr( 'aria-expanded', 'false' );

			// Remove all styles
			$( '.full-screen-overlay-nav, .full-screen-overlay-nav *' ).children().each( function() {
				$( this ).removeAttr( 'style' );
				$( this ).removeAttr( 'id' );
			} );

			// Add mobile menu extras
			self.insertExtras( $( '.wpex-mobile-menu-top' ), $( '.wpex-mobile-menu .full-screen-overlay-nav-content-inner' ), 'prepend' );
			self.insertExtras( $( '.wpex-mobile-menu-bottom' ), $( '.wpex-mobile-menu .full-screen-overlay-nav-content-inner' ), 'append' );

			// Add search to toggle menu
			if ( $mobileSearch.length ) {
				$navUL.append( $mobileSearch );
				$mobileSearch.wrap( '<li class="wpex-search"></li>' );
				$mobileSearch.removeClass( 'wpex-hidden' );
			}

			// Loop through parent items and add to dropdown if they have a link
			var parseDropParents = false;
			if ( ! parseDropParents ) {

				var $parents = $nav.find( 'li.menu-item-has-children > a' );

				$parents.each( function() {

					var $this = $( this );

					if ( $this && $this.attr( 'href' ) && '#' != $this.attr( 'href' ) ) {
						var $parent = $this.parent( 'li' ),
							el      = $parent.clone();
						$parent.removeClass( 'local-scroll' );
						$this.removeAttr( 'data-ls_linkto' );
						el.removeClass( 'menu-item-has-children' );
						el.find( 'ul' ).remove().end().prependTo( $this.next( 'ul' ) );
					}

				} );

				parseDropParents = true;

			}

			// Add toggle click event
			var $dropdownTargetEl = $nav.find( 'li.menu-item-has-children > a' );
			$dropdownTargetEl.on( 'click', function() {

				var $parentEl = $( this ).parent( 'li' );

				if ( ! $parentEl.hasClass( 'wpex-active' ) ) {
					var $allParentLis = $parentEl.parents( 'li' );
					$nav.find( '.menu-item-has-children' )
						.not( $allParentLis )
						.removeClass( 'wpex-active' )
						.children( 'ul' )
						.stop( true, true )
						.slideUp( 'fast' );
					$parentEl.addClass( 'wpex-active' ).children( 'ul' ).stop( true, true ).slideDown( {
						duration: 'normal',
						easing: 'easeInQuad'
					} );
				} else {
					$parentEl.removeClass( 'wpex-active' );
					$parentEl.find( 'li' ).removeClass( 'wpex-active' ); // Remove active from sub-drops
					$parentEl.find( 'ul' ).stop( true, true ).slideUp( 'fast' ); // Hide all drops
				}

				// Return false
				return false;

			} );

			// Show
			$menuButton.on( 'click', function() {

				// Toggle aria
				$nav.attr( 'aria-expanded', 'true' );
				$menuButton.attr( 'aria-expanded', 'true' );

				// Add visible class
				$nav.addClass( 'visible' );

				// Add no scroll to browser window
				$( 'body' ).addClass( 'wpex-noscroll' );

				// Focus on the menu
				var $navTransitionDuration = $nav.css( 'transition-duration' ) || '';
				if ( $navTransitionDuration ) {
					setTimeout( function() {
						self.focusOnElement( $nav );
					}, $navTransitionDuration.replace( 's', '' ) * 1000 );
				} else {
					self.focusOnElement( $nav );
				}

				// Return false on button click
				return false;

			} );

			// Hide overlay when clicking local scroll links
			$( '.local-scroll > a', $nav ).click( function() {
				var $hash = this.hash;
				if ( $.inArray( $hash, self.config.localScrollSections ) > -1 ) {
					onHide();
					return false;
				}
			} );

			// Hide when clicking close button
			$( '.full-screen-overlay-nav-close' ).on( 'click', function() {
				onHide();
				$menuButton.focus();
				return false;
			} );

			// Close when clicking esc
			$nav.keydown( function( e ) {
				if ( e.keyCode === 27 && $nav.hasClass( 'visible' ) ) {
					onHide();
					$menuButton.focus();
				}
			} );

			// Hide actions
			function onHide() {
				$nav.removeClass( 'visible' );
				$nav.attr( 'aria-expanded', 'false' );
				$menuButton.attr( 'aria-expanded', 'false' );
				$nav.find( 'li.wpex-active > ul' ).stop( true, true ).slideUp( 'fast' );
				$nav.find( '.wpex-active' ).removeClass( 'wpex-active' );
				$( 'body' ).removeClass( 'wpex-noscroll' );
			}

		},

		/**
		 * Prevent clickin on links.
		 */
		navNoClick: function() {
			$( 'li.nav-no-click > a, li.sidr-class-nav-no-click > a' ).on( 'click', function() {
				return false;
			} );
		},

		/**
		 * Header Search.
		 *
		 * @todo seperate each style into it's own method.
		 */
		menuSearch: function() {
			var self = this;

			var toggle_target, $toggleEl;
			var $wrapEl = $( '.header-searchform-wrap' );

			// Alter search placeholder & autocomplete
			if ( $wrapEl.length ) {
				if ( $wrapEl.data( 'placeholder' ) ) {
					$wrapEl.find( 'input[type="search"]' ).attr( 'placeholder', $wrapEl.data( 'placeholder' ) );
				}
				if ( $wrapEl.data( 'disable-autocomplete' ) ) {
					$wrapEl.find( 'input[type="search"]' ).attr( 'autocomplete', 'off' );
				}
			}

			switch ( l10n.menuSearchStyle ) {

				case 'drop_down':

					toggle_target = 'a.search-dropdown-toggle, a.mobile-menu-search';
					$toggleEl = $( toggle_target );
					var $searchDropdownForm = $( '#searchform-dropdown' );

					if ( $searchDropdownForm.length ) {

						$( 'body' ).on( 'click', toggle_target, function() {

							// Display search form
							$searchDropdownForm.toggleClass( 'show' );

							// Active menu item
							$toggleEl.parent( 'li' ).toggleClass( 'active' );

							// Focus
							var $transitionDuration = $searchDropdownForm.css( 'transition-duration' );
							$transitionDuration = $transitionDuration.replace( 's', '' ) * 1000;
							if ( $transitionDuration ) {
								setTimeout( function() {
									$searchDropdownForm.find( 'input[type="search"]' ).focus();
								}, $transitionDuration );
							}

							// Hide other things
							$( 'div#current-shop-items-dropdown' ).removeClass( 'show' );
							$( 'li.toggle-header-cart' ).removeClass( 'active' );

							// Return false
							return false;

						} );

						// Close on doc click
						$( document ).on( 'click', function( e ) {
							if ( ! $( e.target ).closest( '#searchform-dropdown.show' ).length ) {
								$toggleEl.parent( 'li' ).removeClass( 'active' );
								$searchDropdownForm.removeClass( 'show' );
							}
						} );

					}

					break;

				case 'overlay':

					toggle_target = 'a.search-overlay-toggle, a.mobile-menu-search, li.search-overlay-toggle > a';
					$toggleEl = $( toggle_target );

					var $overlayEl = $( '#wpex-searchform-overlay' );
					var $inner = $overlayEl.find( '.wpex-inner' );

					$( 'body' ).on( 'click', toggle_target, function() {
						$overlayEl.toggleClass( 'active' );
						$overlayEl.find( 'input[type="search"]' ).val( '' );
						if ( $overlayEl.hasClass( 'active' ) ) {
							var $overlayElTransitionDuration = $overlayEl.css( 'transition-duration' );
							$overlayElTransitionDuration = $overlayElTransitionDuration.replace( 's', '' ) * 1000;
							setTimeout( function() {
								$overlayEl.find( 'input[type="search"]' ).focus();
							}, $overlayElTransitionDuration );
						}
						return false;
					} );

					// Close searchforms
					$inner.click( function( e ) {
						e.stopPropagation();
					} );

					$overlayEl.click( function() {
						$overlayEl.removeClass( 'active' );
					} );

					$overlayEl.keydown( function( e ) {
						if ( 27 === e.keyCode || 9 ===  e.keyCode ) {
							$overlayEl.removeClass( 'active' );
							$toggleEl.focus();
						}
					} );

					break;

				case 'header_replace':

					toggle_target = 'a.search-header-replace-toggle, a.mobile-menu-search';
					$toggleEl = $( toggle_target );
					var $headerReplace = $( '#searchform-header-replace' );

					// Show
					$( 'body' ).on( 'click', toggle_target, function() {

						// Display search form
						$headerReplace.toggleClass( 'show' );

						// Focus
						var $transitionDuration = $headerReplace.css( 'transition-duration' );
						$transitionDuration = $transitionDuration.replace( 's', '' ) * 1000;
						if ( $transitionDuration ) {
							setTimeout( function() {
								$headerReplace.find( 'input[type="search"]' ).focus();
							}, $transitionDuration );
						}

						// Return false
						return false;

					} );

					// Close on click
					$( '#searchform-header-replace-close' ).click( function() {
						$headerReplace.removeClass( 'show' );
						return false;
					} );

					// Close on doc click
					$( document ).on( 'click', function( e ) {
						if ( ! $( e.target ).closest( $( '#searchform-header-replace.show' ) ).length ) {
							$headerReplace.removeClass( 'show' );
						}
					} );

					break;
			}

		},

		/**
		 * Header Cart.
		 */
		headerCart: function() {

			if ( $( 'a.wcmenucart' ).hasClass( 'go-to-shop' ) ) {
				return;
			}

			var $toggle = 'a.toggle-cart-widget, li.toggle-cart-widget > a, li.toggle-header-cart > a';

			if ( ! $( $toggle.length ) ) {
				return;
			}

			switch ( l10n.wooCartStyle ) {

				 case 'drop_down':

					var $dropdown = $( 'div#current-shop-items-dropdown' );

					// Display cart dropdown
					$( 'body' ).on( 'click', $toggle, function() {
						$( '#searchform-dropdown' ).removeClass( 'show' );
						$( 'a.search-dropdown-toggle' ).parent( 'li' ).removeClass( 'active' );
						$dropdown.toggleClass( 'show' );
						$( this ).toggleClass( 'active' );
						return false;
					} );

					// Hide cart dropdown
					$dropdown.click( function( e ) {
						if ( ! $( e.target ).is( 'a.remove_from_cart_button' ) ) {
							e.stopPropagation();
						}
					} );

					$( document ).click( function( e ) {
						if ( ! $( e.target ).is( 'a.remove_from_cart_button' ) ) {
							$dropdown.removeClass( 'show' );
							$( $toggle ).removeClass( 'active' );
						}
					} );

					break;

				 case 'overlay':

					var $overlayEl = $( '#wpex-cart-overlay' );
					var $inner     = $overlayEl.find( '.wpex-inner' );

					$( 'body' ).on( 'click', $toggle, function() {
						$overlayEl.toggleClass( 'active' );
						return false;
					} );

					// Close searchforms
					$inner.click( function( e ) {
						if ( ! $( e.target ).is( 'a.remove_from_cart_button' ) ) {
							e.stopPropagation();
						}
					} );
					$overlayEl.click( function( e ) {
						if ( ! $( e.target ).is( 'a.remove_from_cart_button' ) ) {
							$overlayEl.removeClass( 'active' );
						}
					} );

					break;

			}

		},

		/**
		 * Automatically add padding to row to offset header.
		 */
		headerOverlayOffset: function() {
			var $offset_element = $( '.add-overlay-header-offset' );
			if ( $offset_element.length ) {
				var $header = $( '#site-header' );
				if ( $header.length ) {
					var $height = $header.outerHeight();
					var $offset = $( '<div class="overlay-header-offset-div" style="height:' + $height + 'px"></div>' );
					$offset_element.prepend( $offset );
					$( window ).resize( function() {
						$offset.css( 'height', $height );
					} );
				}
			}
		},

		/**
		 * Hide post edit link.
		 */
		hideEditLink: function() {
			$( 'a.hide-post-edit', $( '#content' ) ).click( function() {
				$( 'div.post-edit' ).hide();
				return false;
			} );
		},

		/**
		 * Custom menu widget accordion.
		 */
		menuWidgetAccordion: function() {

			if ( ! l10n.menuWidgetAccordion ) {
				return;
			}

			var self = this;

			// Open toggle for active page
			$( '#sidebar .widget_nav_menu .current-menu-ancestor, .widget_nav_menu_accordion .widget_nav_menu .current-menu-ancestor' ).addClass( 'active' ).children( 'ul' ).show();

			// Toggle items
			$( '#sidebar .widget_nav_menu, .widget_nav_menu_accordion  .widget_nav_menu' ).each( function() {
				var $hasChildren = $( this ).find( '.menu-item-has-children' );
				$hasChildren.each( function() {
					$( this ).addClass( 'parent' );
					var $links = $( this ).children( 'a' );
					$links.on( 'click', function() {
						var $linkParent = $( this ).parent( 'li' );
						var $allParents = $linkParent.parents( 'li' );
						if ( ! $linkParent.hasClass( 'active' ) ) {
							$hasChildren.not( $allParents ).removeClass( 'active' ).children( '.sub-menu' ).slideUp( 'fast' );
							$linkParent.addClass( 'active' ).children( '.sub-menu' ).stop( true, true ).slideDown( 'fast' );
						} else {
							$linkParent.removeClass( 'active' ).children( '.sub-menu' ).stop( true, true ).slideUp( 'fast' );
						}
						return false;
					} );
				} );
			} );

		},

		/**
		 * Header 5 - Inline Logo.
		 */
		inlineHeaderLogo: function() {
			var self = this;

			var $centeredHeader = $( '#site-header.header-five' );

			if ( ! $centeredHeader.length ) {
				return;
			}

			// Define vars
			var $logo = $( '#site-header-inner > .header-five-logo', $centeredHeader );
			var $nav  = $( '.navbar-style-five', $centeredHeader );

			if ( ! $nav.length ) {
				return;
			}

			var $navLiCount = $( '.navbar-style-five .main-navigation-ul' ).children( 'li' ).length;
			var navBeforeMiddleLi = Math.round( $navLiCount / 2 ) - parseInt( l10n.headerFiveSplitOffset );

			// Insert Logo into Menu
			function onInit() {

				if ( ( self.viewportWidth() > l10n.mobileMenuBreakpoint ) && $logo.length && $nav.length ) {
					$( '<li class="menu-item-logo"></li>' ).insertAfter( $nav.find( '.main-navigation-ul > li:nth(' + navBeforeMiddleLi + ')' ) );
					$logo.appendTo( $nav.find( '.menu-item-logo' ) );
				}

				$logo.addClass( 'display' );

			}

			// Move logo
			function onResize() {

				var $inlineLogo = $( '.menu-item-logo .header-five-logo' );

				if ( self.viewportWidth() <= l10n.mobileMenuBreakpoint ) {
					if ( $inlineLogo.length ) {
						$inlineLogo.prependTo( $( '#site-header-inner' ) );
						$( '.menu-item-logo' ).remove();
					}
				} else if ( ! $inlineLogo.length ) {
					onInit(); // Insert logo to menu
				}
			}

			// On init
			onInit();

			// Move logo on resize
			$( window ).resize( function() {
				onResize();
			} );

		},

		/**
		 * Back to top link.
		 */
		backTopLink: function() {
			var $scrollTopLink = $( 'a#site-scroll-top' );

			if ( ! $scrollTopLink.length ) {
				return;
			}

			var speed  = this.pData( $scrollTopLink.data( 'scrollSpeed' ), 1000 );
			var offset = this.pData( $scrollTopLink.data( 'scrollOffset' ), 100 );
			var easing = this.pData( $scrollTopLink.data( 'scrollEasing' ), 'easeInOutExpo' );

			$( window ).scroll( function() {
				if ( $( this ).scrollTop() > offset ) {
					$scrollTopLink.addClass( 'show' );
				} else {
					$scrollTopLink.removeClass( 'show' );
				}
			} );

			$scrollTopLink.on( 'click', function() {
				$( 'html, body' ).stop( true, true ).animate( {
					scrollTop : 0
				}, speed, easing );
				return false;
			} );

		},

		/**
		 * Smooth Comment Scroll.
		 */
		smoothCommentScroll: function() {
			var self = this;
			$( '.single li.comment-scroll a' ).click( function() {
				var $target = $( '#comments' );
				var $offset = $target.offset().top - self.config.localScrollOffset - 20;
				self.scrollTo( $target, $offset );
				return false;
			} );
		},

		/**
		 * Togglebar toggle.
		 */
		toggleBar: function() {

			var self           = this;
			var $toggleBarWrap = $( '#toggle-bar-wrap' );

			if ( ! $toggleBarWrap.length ) {
				return;
			}

			var $toggleBtn     = $( 'a.toggle-bar-btn, a.togglebar-toggle, .togglebar-toggle > a' );
			var $toggleBtnIcon = $toggleBtn.find( '.ticon' );

			$toggleBtn.on( 'click', function() {
				if ( $toggleBtnIcon.length ) {
					$toggleBtnIcon.toggleClass( $toggleBtn.data( 'icon' ) );
					$toggleBtnIcon.toggleClass( $toggleBtn.data( 'icon-hover' ) );
				}
				$toggleBarWrap.toggleClass( 'active-bar' );
				return false;
			} );

			// Close on doc click
			$( document ).on( 'click', function( e ) {
				if ( ( $toggleBarWrap.hasClass( 'active-bar' ) && $toggleBarWrap.hasClass( 'close-on-doc-click' ) ) && ! $( e.target ).closest( '#toggle-bar-wrap' ).length ) {
					$toggleBarWrap.removeClass( 'active-bar' );
					if ( $toggleBtnIcon.length ) {
						$toggleBtnIcon.removeClass( $toggleBtn.data( 'icon-hover' ) ).addClass( $toggleBtn.data( 'icon' ) );
					}
				}
			} );

		},

		/**
		 * Sliders
		 */
		sliderPro: function( $context ) {

			if ( 'undefined' === typeof $.fn.sliderPro ) {
				return;
			}

			function dataValue( name, fallback ) {
				return ( 'undefined' !== typeof name ) ? name : fallback;
			}

			function getTallestEl( el ) {
				var tallest;
				var first = 1;
				el.each( function() {
					var $this = $( this );
					if ( first == 1 ) {
						tallest = $this;
						first = 0;
					} else {
						if ( tallest.height() < $this.height()) {
							tallest = $this;
						}
					}
				} );
				return tallest;
			}

			// Loop through each slider
			$( '.wpex-slider', $context ).each( function() {

				// Declare vars
				var $slider = $( this );
				var $data   = $slider.data();
				var $slides = $slider.find( '.sp-slide' );

				// Lets show things that were hidden to prevent flash
				$slider.find( '.wpex-slider-slide, .wpex-slider-thumbnails.sp-thumbnails,.wpex-slider-thumbnails.sp-nc-thumbnails' ).css( {
					'opacity' : 1,
					'display' : 'block'
				} );

				// Main checks
				var $autoHeight              = dataValue( $data.autoHeight, true );
				var $preloader               = $slider.prev( '.wpex-slider-preloaderimg' );
				var $height                  = ( $preloader.length && $autoHeight ) ? $preloader.outerHeight() : null;
				var $heightAnimationDuration = dataValue( $data.heightAnimationDuration, 600 );
				var $loop                    = dataValue( $data.loop, false );
				var $autoplay                = dataValue( $data.autoPlay, true );
				var $counter                 = dataValue( $data.counter, false );

				// Get height based on tallest item if autoHeight is disabled
				if ( ! $autoHeight && $slides.length ) {
					var $tallest = getTallestEl( $slides );
					$height = $tallest.height();
				}

				// TouchSwipe
				var $touchSwipe = true;

				if ( 'undefined' !== typeof $data.touchSwipeDesktop && ! /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test( navigator.userAgent ) ) {
					$touchSwipe = false;
				}

				// Run slider
				$slider.sliderPro( {

					//supportedAnimation      : 'JavaScript', //(CSS3 2D, CSS3 3D or JavaScript)
					aspectRatio             : -1,
					width                   : '100%',
					height                  : $height,
					responsive              : true,
					fade                    : dataValue( $data.fade, 600 ),
					fadeDuration            : dataValue( $data.animationSpeed, 600 ),
					slideAnimationDuration  : dataValue( $data.animationSpeed, 600 ),
					autoHeight              : $autoHeight,
					heightAnimationDuration : parseInt( $heightAnimationDuration ),
					arrows                  : dataValue( $data.arrows, true ),
					fadeArrows              : dataValue( $data.fadeArrows, true ),
					autoplay                : $autoplay,
					autoplayDelay           : dataValue( $data.autoPlayDelay, 5000 ),
					buttons                 : dataValue( $data.buttons, true ),
					shuffle                 : dataValue( $data.shuffle, false ),
					orientation             : dataValue( $data.direction, 'horizontal' ),
					loop                    : $loop,
					keyboard                : dataValue( $data.keyboard, false ),
					fullScreen              : dataValue( $data.fullscreen, false ),
					slideDistance           : dataValue( $data.slideDistance, 0 ),
					thumbnailsPosition      : 'bottom',
					thumbnailHeight         : dataValue( $data.thumbnailHeight, 70 ),
					thumbnailWidth          : dataValue( $data.thumbnailWidth, 70 ),
					thumbnailPointer        : dataValue( $data.thumbnailPointer, false ),
					updateHash              : dataValue( $data.updateHash, false ),
					touchSwipe              : $touchSwipe,
					thumbnailArrows         : false,
					fadeThumbnailArrows     : false,
					thumbnailTouchSwipe     : true,
					fadeCaption             : dataValue( $data.fadeCaption, true ),
					captionFadeDuration     : 600,
					waitForLayers           : true,
					autoScaleLayers         : true,
					forceSize               : dataValue( $data.forceSize, 'false' ),
					reachVideoAction        : dataValue( $data.reachVideoAction, 'playVideo' ),
					leaveVideoAction        : dataValue( $data.leaveVideoAction, 'pauseVideo' ),
					endVideoAction          : dataValue( $data.leaveVideoAction, 'nextSlide' ),
					fadeOutPreviousSlide    : true, // If disabled testimonial/content slides are bad
					autoplayOnHover         : dataValue( $data.autoplayOnHover, 'pause' ),
					init: function( e ) {

						// Remove preloader image
						$slider.prev( '.wpex-slider-preloaderimg' ).remove();

						// Add tab index and role attribute to slider arrows and buttons
						var $navItems = $slider.find( '.sp-arrow, .sp-button, .sp-nc-thumbnail-container, .sp-thumbnail-container' );

						$navItems.attr( 'tabindex', '0' );
						$navItems.attr( 'role', 'button' );

						// Add aria-label to bullets and thumbnails
						var $bullets = $slider.find( '.sp-button, .sp-thumbnail-container, .sp-nc-thumbnail-container' );
						$bullets.each( function( index, val ) {
							var slideN = parseInt( index + 1 );
							$( this ).attr( 'aria-label', wpexSliderPro.i18n.GOTO + ' ' + slideN );
						} );

						// Add label to next arrow
						$slider.find( '.sp-previous-arrow' ).attr( 'aria-label', wpexSliderPro.i18n.PREV );

						// Add label to prev arrow
						$slider.find( '.sp-next-arrow' ).attr( 'aria-label', wpexSliderPro.i18n.NEXT );

					},
					gotoSlide: function( e ) {

						// Stop autoplay when loop is disabled and we've reached the last slide
						if ( ! $loop && $autoplay && e.index === $slider.find( '.sp-slide' ).length - 1 ) {
							$slider.data( 'sliderPro' ).stopAutoplay();
						}

						// Update counter
						if ( $counter ) {
							$slider.find( '.sp-counter .sp-active' ).text( e.index + 1 );
						}

					}

				} ); // end sliderPro

				// Get slider Data
				var slider = jQuery( this ).data( 'sliderPro' );

				// Add counter pagination
				if ( $counter ) {
					$( '.sp-slides-container', $slider ).append( '<div class="sp-counter"><span class="sp-active">' + ( parseInt( slider.getSelectedSlide() ) + 1 ) + '</span>/' + slider.getTotalSlides() + '</div>' );
				}

				// Accessability click events for bullets, arrows and no carousel thumbs
				var $navItems = $slider.find( '.sp-arrow, .sp-button, .sp-nc-thumbnail-container, .sp-thumbnail-container' );
				$navItems.keypress( function( e ) {
					if ( e.keyCode == 13 ) {
						$( this ).trigger( 'click' );
					}
				} );

				// Accessability click events for thumbnails
				var $thumbs = $( '.sp-thumbnail-container' );
				$thumbs.keypress( function( e ) {
					if ( e.keyCode == 13 ) {
						$( this ).closest( '.wpex-slider' ).sliderPro( 'gotoSlide', $( this ).index() );
					}
				} );

			} ); // End each

			// WooCommerce: Prevent clicking on Woo entry slider
			$( '.woo-product-entry-slider' ).click( function() {
				return false;
			} );

		},

		/**
		 * Advanced Parallax.
		 */
		parallax: function( $context ) {

			if ( 'undefined' === typeof $.fn.scrolly2 ) {
				return;
			}

			$( '.wpex-parallax-bg', $context ).each( function() {
				var $this = $( this );
				$this.scrolly2().trigger( 'scroll' );
				$this.css( {
					'opacity' : 1
				} );
			} );

		},

		/**
		 * Local Scroll Offset.
		 */
		parseLocalScrollOffset: function( instance ) {
			var self    = this;
			var offset = 0;

			// Array of items to check
			var items = '.wpex-ls-offset, #wpadminbar, #top-bar-wrap-sticky-wrapper.wpex-can-sticky,#site-navigation-sticky-wrapper.wpex-can-sticky, #wpex-mobile-menu-fixed-top, .vcex-navbar-sticky-offset';

			// Return custom offset
			if ( l10n.localScrollOffset ) {
				self.config.localScrollOffset = l10n.localScrollOffset;
				return self.config.localScrollOffset;
			}

			// Adds extra offset via filter
			if ( l10n.localScrollExtraOffset ) {
				offset = parseInt( offset ) + parseInt( l10n.localScrollExtraOffset );
			}

			// Fixed header
			var $stickyHeader = $( '#site-header.fixed-scroll' );
			if ( $stickyHeader.length ) {

				// Return 0 for small screens if mobile fixed header is disabled
				if ( ! l10n.hasStickyMobileHeader && $( window ).width() <= l10n.stickyHeaderBreakPoint ) {
					offset = parseInt( offset ) + 0;
				}

				// Return header height
				else {

					// Shrink header
					if ( $stickyHeader.hasClass( 'shrink-sticky-header' ) ) {
						if ( 'init' == instance || $stickyHeader.is( ':visible' ) ) {
							offset = parseInt( offset ) + parseInt( l10n.shrinkHeaderHeight );
						}
					}

					// Standard header
					else {
						offset = parseInt( offset ) + parseInt( $stickyHeader.outerHeight() );
					}

				}

			}

			// Loop through extra items
			$( items ).each( function() {
				var $this = $( this );
				if ( $this.length && $this.is( ':visible' ) ) {
					offset = parseInt( offset ) + parseInt( $this.outerHeight() );
				}
			} );

			// Add 1 extra decimal to prevent cross browser rounding issues (mostly firefox)
			offset = offset ? offset - 1 : 0;

			// Update offset
			self.config.localScrollOffset = offset;

			// Return offset
			return self.config.localScrollOffset;

		},

		/**
		 * Scroll to function.
		 */
		scrollTo: function( hash, offset, callback ) {

			if ( ! hash ) {
				return;
			}

			var self          = this;
			var $target       = null;
			var $isLsDataLink = false;
			var localSection  = self.getLocalSection( hash );
			var lsOffset      = self.config.localScrollOffset;
			var lsSpeed       = parseInt( l10n.localScrollSpeed );

			if ( localSection ) {
				$target       = localSection;
				$isLsDataLink = true;
			}

			// Check for element with ID
			else {
				if ( typeof hash == 'string' ) {
					$target = $( hash );
				} else {
					$target = hash;
				}
			}

			// Target check
			if ( $target.length ) {

				// Sanitize offset (target required)
				offset = offset ? offset : $target.offset().top - lsOffset;

				// Update hash
				if ( hash && $isLsDataLink && l10n.localScrollUpdateHash ) {
					window.location.hash = hash;
				}

				/* @todo Remove hash on site top click
				if ( '#site_top' == hash && l10n.localScrollUpdateHash && window.location.hash ) {
					history.pushState( '', document.title, window.location.pathname);
				}*/

				// Mobile toggle Menu needs it's own code so it closes before the event fires
				// to make sure we end up in the right place
				var $mobileToggleNav = $( '.mobile-toggle-nav' );
				if ( $mobileToggleNav.hasClass( 'visible' ) ) {
					$( 'a.mobile-menu-toggle, li.mobile-menu-toggle > a' ).removeClass( 'wpex-active' );
					if ( l10n.animateMobileToggle ) {
						$mobileToggleNav.slideUp( 'fast', function() {
							$mobileToggleNav.removeClass( 'visible' );
							$( 'html, body' ).stop( true, true ).animate( {
								scrollTop: $target.offset().top - lsOffset
							}, lsSpeed, l10n.localScrollEasing );
						} );
					} else {
						$mobileToggleNav.hide().removeClass( 'visible' );
						$( 'html, body' ).stop( true, true ).animate( {
							scrollTop: $target.offset().top - lsOffset
						}, lsSpeed, l10n.localScrollEasing );
					}
				}

				// Scroll to target
				else {
					$( 'html, body' ).stop( true, true ).animate( {
						scrollTop: offset
					}, lsSpeed, l10n.localScrollEasing );
				}

			}

		},

		/**
		 * Scroll to Hash.
		 */
		scrollToHash: function( self ) {

			var hash, $target, $offset;

			hash = location.hash;

			// Security check
			//hash = '#<img src=x onerror=alert("not secure")>';

			// Hash needed
			if ( hash == '' || hash == '#' || hash == undefined ) {
				return;
			}

			// Scroll to comments
			if ( '#view_comments' == hash || '#comments_reply' == hash ) {
				$target = $( '#comments' );
				$offset = $target.offset().top - self.config.localScrollOffset - 20;
				if ( $target.length ) {
					self.scrollTo( $target, $offset );
				}
				return;
			}

			// Scroll to specific comment, fix for sticky header
			if ( $( '#site-header.fixed-scroll' ).length && hash.indexOf( 'comment-' ) != -1 ) {
				$( '#comments .comment' ).each( function() {
					var id = $( this ).attr( 'id' );
					if ( hash.slice(1) == id ) {
						$target = $( this );
						$offset = $target.offset().top - self.config.localScrollOffset - 20;
						self.scrollTo( $target, $offset );
						return false;
					}
				} );
				return;
			}

			// Remove localscroll- from hash (older method)
			if ( hash.indexOf( 'localscroll-' ) != -1 ) {
				hash = hash.replace( 'localscroll-', '' );
			}

			// Check elements with data attributes
			self.scrollTo( hash );

		},

		/**
		 * Scroll to Hash.
		 */
		getLocalSection: function( sectionID, offset ) {
			var section;
			$( '[data-ls_id]' ).each( function() {
				var data = $( this ).data( 'ls_id' );
				if ( sectionID == data ) {
					section = $( this );
					return false;
				}
			} );
			return section;
		},

		/**
		 * Local scroll links array.
		 */
		localScrollSections: function() {

			var self = this;

			// Add local-scroll class to links in menu with localscroll- prefix (if on same page)
			// Add data-ls_linkto attr
			$( '.main-navigation-ul li.menu-item a' ).each( function() {
				var $this = $( this );
				var href = $this.attr( 'href' );
				if ( href && href.indexOf( 'localscroll-' ) != -1 ) {
					var parentLi = $this.parent( 'li' );
					parentLi.addClass( 'local-scroll' );
					parentLi.removeClass( 'current-menu-item' );
					var withoutHash = href.substr( 0, href.indexOf( '#' ) );
					var currentPage = location.href;
					currentPage = location.hash ? currentPage.substr( 0, currentPage.indexOf( '#' ) ) : location.href;
					if ( withoutHash == currentPage ) {
						var hash = href.substring( href.indexOf( '#' ) + 1 );
						$this.attr( 'data-ls_linkto', '#' + hash.replace( 'localscroll-', '' ) );
					}
				}
			} );

			// Define main vars
			var array = [];
			var $links = $( l10n.localScrollTargets );

			// Loop through links
			for ( var i=0; i < $links.length; i++ ) {

				// Add to array and save hash
				var $link    = $links[i];
				var $linkDom = $( $link );
				var $href    = $( $link ).attr( 'href' );
				var $hash    = $href ? '#' + $href.replace( /^.*?(#|$)/, '' ) : null;

				// Hash required
				if ( $hash && '#' != $hash ) {

					// Add custom data attribute to each
					if ( ! $linkDom.attr( 'data-ls_linkto' ) ) {
						$linkDom.attr( 'data-ls_linkto', $hash );
					}

					// Data attribute targets
					if ( $( '[data-ls_id="'+ $hash +'"]' ).length ) {
						if ( $.inArray( $hash, array ) == -1 ) {
							array.push( $hash );
						}
					}

					// Standard ID targets
					else if ( $( $hash ).length ) {
						if ( $.inArray( $hash, array ) == -1 ) {
							array.push( $hash );
						}
					}

				}

			}

			self.config.localScrollSections = array;

			return self.config.localScrollSections;

		},

		/**
		 * Local Scroll link.
		 */
		localScrollLinks: function() {
			var self = this;

			// Local Scroll - Menus
			$( l10n.localScrollTargets ).on( 'click', function() {
				var $this = $( this );
				var $hash = $this.attr( 'data-ls_linkto' );
				$hash = $hash ? $hash : this.hash; // Fallback
				if ( $.inArray( $hash, self.config.localScrollSections ) > -1 ) {
					$this.parent().removeClass( 'sfHover' );
					self.scrollTo( $hash );
					return false;
				}
			} );

			// Local Scroll - Logo
			$( 'a.wpex-scroll-top, .wpex-scroll-top a' ).on( 'click', function() {
				self.scrollTo( '#site_top' );
				return false;
			} );

			// Local Scroll - Woocommerce Reviews
			$( 'a.woocommerce-review-link', $( 'body.single div.entry-summary' ) ).click( function() {
				var $target = $( '.woocommerce-tabs' );
				if ( $target.length ) {
					$( '.reviews_tab a' ).click();
					var $offset = $target.offset().top - self.config.localScrollOffset;
					self.scrollTo( $target, $offset );
				}
				return false;
			} );

		},

		/**
		 * Local Scroll Highlight on scroll.
		 */
		localScrollHighlight: function() {

			// Return if disabled
			if ( ! l10n.localScrollHighlight ) {
				return;
			}

			// Define main vars
			var self = this,
				localScrollSections = self.config.localScrollSections;

			// Return if there aren't any local scroll items
			if ( ! localScrollSections.length ) {
				return;
			}

			// Define vars
			var $windowPos = $( window ).scrollTop(),
				$divPos,
				$divHeight,
				$higlight_link,
				$targetDiv;

			// Highlight active items
			for ( var i=0; i < localScrollSections.length; i++ ) {

				// Get section
				var $section = localScrollSections[i];

				// Data attribute targets
				if ( $( '[data-ls_id="' + $section + '"]' ).length ) {
					$targetDiv     = $( '[data-ls_id="' + $section + '"]' );
					$divPos        = $targetDiv.offset().top - self.config.localScrollOffset - 1;
					$divHeight     = $targetDiv.outerHeight();
					$higlight_link = $( '[data-ls_linkto="' + $section + '"]' );
				}

				// Standard element targets
				else if ( $( $section ).length ) {
					$targetDiv     = $( $section );
					$divPos        = $targetDiv.offset().top - self.config.localScrollOffset - 1;
					$divHeight     = $targetDiv.outerHeight();
					$higlight_link = $( '[data-ls_linkto="' + $section + '"]' );
				}

				// Higlight items
				if ( $windowPos >= $divPos && $windowPos < ( $divPos + $divHeight ) ) {
					$( '.local-scroll.menu-item' ).removeClass( 'current-menu-item' ); // prevent any sort of duplicate local scroll active links
					$higlight_link.addClass( 'active' );
					$targetDiv.addClass( 'wpex-ls-inview' );
					$higlight_link.parent( 'li' ).addClass( 'current-menu-item' );
				} else {
					$targetDiv.removeClass( 'wpex-ls-inview' );
					$higlight_link.removeClass( 'active' );
					$higlight_link.parent( 'li' ).removeClass( 'current-menu-item' );
				}

			}

			/* @todo: Highlight last item if at bottom of page or last item clicked - needs major testing now.
			var $docHeight   = $( document ).height();
			var windowHeight = $( window ).height();
			var $lastLink = localScrollSections[localScrollSections.length-1];
			if ( $windowPos + windowHeight == $docHeight ) {
				$( '.local-scroll.current-menu-item' ).removeClass( 'current-menu-item' );
				$( "li.local-scroll a[href='" + $lastLink + "']" ).parent( 'li' ).addClass( 'current-menu-item' );
			}*/

		},

		/**
		 * Equal heights function => Must run before isotope method.
		 */
		equalHeights: function( $context ) {

			if ( 'undefined' === typeof $.fn.wpexEqualHeights ) {
				return;
			}

			// Add equal heights grid
			$( '.match-height-grid', $context ).wpexEqualHeights( {
				children: '.match-height-content'
			} );

			// Columns
			$( '.match-height-row', $context ).wpexEqualHeights( {
				children: '.match-height-content'
			} );

			// Feature Box
			$( '.vcex-feature-box-match-height', $context ).wpexEqualHeights( {
				children: '.vcex-match-height'
			} );

			// Blog entries
			$( '.blog-equal-heights', $context ).wpexEqualHeights( {
				children: '.blog-entry-inner'
			} );

			// Row => @deprecated in v4.0
			$( '.wpex-vc-row-columns-match-height', $context ).wpexEqualHeights( {
				children: '.vc_column-inner'
			} );

			// Manual equal heights
			$( '.vc_row', $context ).wpexEqualHeights( {
				children: '.equal-height-column'
			} );
			$( '.vc_row', $context ).wpexEqualHeights( {
				children: '.equal-height-content'
			} );

		},

		/**
		 * Footer Reveal Display on Load.
		 */
		footerReveal: function() {
			var self = this;

			// Footer reveal
			var $footerReveal = $( '.footer-reveal-visible' );
			var $wrap = $( '#wrap' );
			var $main = $( '#main' );

			if ( ! $footerReveal.length || ! $wrap.length || ! $main.length ) {
				return;
			}

			function showHide() {

				// Disabled under 960
				if ( self.viewportWidth() < 960 ) {
					if ( $footerReveal.hasClass( 'footer-reveal' ) ) {
						$footerReveal.toggleClass( 'footer-reveal footer-reveal-visible' );
						$wrap.css( 'margin-bottom', '' );
					}
					return;
				}

				var $hideFooter  = false;
				var revealHeight = $footerReveal.outerHeight();
				var windowHeight = $( window ).height();
				var $heightCheck = 0;

				if ( $footerReveal.hasClass( 'footer-reveal' ) ) {
					$heightCheck = $wrap.outerHeight() + self.config.localScrollOffset;
				} else {
					$heightCheck = $wrap.outerHeight() + self.config.localScrollOffset - revealHeight;
				}

				// Check window height
				if ( ( windowHeight > revealHeight ) && ( $heightCheck > windowHeight ) ) {
					$hideFooter = true;
				}

				// Footer Reveal
				if ( $hideFooter ) {
					if ( $footerReveal.hasClass( 'footer-reveal-visible' ) ) {
						$wrap.css( {
							'margin-bottom': revealHeight
						} );
						$footerReveal.removeClass( 'footer-reveal-visible' );
						$footerReveal.addClass( 'footer-reveal' );
					}
				} else {
					if ( $footerReveal.hasClass( 'footer-reveal' ) ) {
						$wrap.css( 'margin-bottom', '' );
						$footerReveal.removeClass( 'footer-reveal' );
						$footerReveal.removeClass( 'wpex-visible' );
						$footerReveal.addClass( 'footer-reveal-visible' );
					}
				}

			}

			function reveal() {
				if ( $footerReveal.hasClass( 'footer-reveal' ) ) {
					if ( self.scrolledToBottom( $main ) ) {
						$footerReveal.addClass( 'wpex-visible' );
					} else {
						$footerReveal.removeClass( 'wpex-visible' );
					}
				}
			}

			// Fire right away
			showHide();
			reveal();

			// Fire onscroll event
			$( window ).scroll( function() {
				reveal();
			} );

			// Fire onResize
			$( window ).resize( function() {
				showHide();
			} );

		},

		/**
		 * Set min height on main container to prevent issue with extra space below footer.
		 */
		fixedFooter: function() {

			if ( ! $( 'body' ).hasClass( 'wpex-has-fixed-footer' ) ) {
				return;
			}

			var $main 	= $( '#main' ),
				$window = $( window );

			function run() {
				$main.css( 'min-height', $main.outerHeight() + ( $window.height() - $( 'html' ).height() ) );
			}

			// Run on doc ready
			run();

			// Run on resize
			$window.resize( function() {
				run();
			} );

		},

		/**
		 * Custom Selects.
		 */
		customSelects: function( $context ) {

			$( l10n.customSelects, $context ).each( function() {
				var $this   = $( this );
				var elID    = $this.attr( 'id' );
				var elClass = elID ? ' wpex-' + elID : '';
				if ( $this.is( ':visible' ) ) {
					if ( $this.attr( 'multiple' ) ) {
						$this.wrap( '<div class="wpex-multiselect-wrap' + elClass + '"></div>' );
					} else {
						$this.wrap( '<div class="wpex-select-wrap' + elClass + '"></div>' );
					}
				}
			} );

			$( '.wpex-select-wrap', $context ).append( '<span class="ticon ticon-angle-down" aria-hidden="true"></span>' );

			if ( 'undefined' !== typeof $.fn.select2 ) {
				$( '#calc_shipping_country' ).select2();
			}

		},

		/**
		 * Masonry Grids.
		 */
		masonryGrids: function() {

			// Make sure scripts are loaded
			if ( 'undefined' === typeof $.fn.imagesLoaded || 'undefined' === typeof $.fn.isotope ) {
				return;
			}

			// Define main vars
			var self      = this;
			var $archives = $( '.wpex-masonry-grid' );

			// Loop through archives
			$archives.each( function() {

				var $container = $( this );
				var $data      = $container.data();

				// Load isotope after images loaded
				$container.imagesLoaded( function() {

					$container.isotope( {
						itemSelector       : '.wpex-masonry-col',
						transformsEnabled  : true,
						isOriginLeft       : l10n.isRTL ? false : true,
						transitionDuration : self.pData( $data.transitionDuration, '0.4' ) + 's',
						layoutMode         : self.pData( $data.layoutMode, 'masonry' )
					} );

				} );

			} );

		},

		/**
		 * Lightbox wrapper method that calls all sub-lightbox methods.
		 * Note : This method only needs to run 1x on the site, otherwise you could end up with duplicate lightbox.
		 */
		lightbox: function( $context ) {
			this.autoLightbox();
			this.lightboxSingle( $context );
			this.lightboxInlineGallery( $context );
			this.lightboxGallery( $context );
			this.lightboxCarousels( $context );
		},

		/**
		 * Automatic Lightbox for images.
		 */
		autoLightbox: function() {
			if ( 'undefined' === typeof l10n.autoLightbox || ! l10n.autoLightbox ) {
				return;
			}
			var self     = this,
				imageExt = ['bmp', 'gif', 'jpeg', 'jpg', 'png', 'tiff', 'tif', 'jfif', 'jpe'];
			$( l10n.autoLightbox ).each( function() {
				var $this = $( this );
				var href  = $this.attr( 'href' );
				var ext   = self.getUrlExtension( href );
				if ( href && imageExt.indexOf( ext ) !== -1 ) {
					if ( ! $this.parents( '.woocommerce-product-gallery' ).length ) {
						$this.addClass( 'wpex-lightbox' );
					}
				}
			} );
		},

		/**
		 * Single lightbox.
		 */
		lightboxSingle: function( $context ) {

			var self = this,
				targets = '.wpex-lightbox, .wpex-lightbox-video, .wpb_single_image.video-lightbox a, .wpex-lightbox-autodetect, .wpex-lightbox-autodetect a';

			$context = $context || $( 'body' );

			$context.on( 'click', targets, function( e ) {

				e.preventDefault();

				var $this = $( this );

				if ( ! $this.is( 'a' ) ) {
					$this = $this.find( 'a' );
				}

				if ( $this.hasClass( 'wpex-lightbox-group-item' ) ) {
					return;
				}

				var customSettings = {};
				var opts           = $this.data() || {};
				var src            = $this.attr( 'href' ) || $this.data( 'src' ) || '';
				var type           = $this.data( 'type' ) || '';
				var caption        = $this.data( 'caption' ) || '';
				var show_title     = $this.attr( 'data-show_title' ) || true;
				var oldOpts        = $this.data( 'options' ) && self.parseObjectLiteralData( $this.data( 'options' ) ) || '';

				if ( ! opts.parsedOpts ) {

					if ( oldOpts ) {

						if ( $this.data( 'type' ) && 'iframe' == $this.data( 'type' ) ) {
							if ( oldOpts.width && oldOpts.height ) {
								opts.width  = oldOpts.width;
								opts.height = oldOpts.height;
							}
						}

						if ( oldOpts.iframeType && 'video' == oldOpts.iframeType ) {
							type = '';
						}

					}

					if ( 'iframe' == type && opts.width && opts.height ) {
						opts.iframe = {
							css : {
								'width'  : opts.width,
								'height' : opts.height
							}
						};
					}

					if ( 'false' !== show_title ) {
						var title = $this.data( 'title' ) || '';
						if ( title.length ) {
							var titleClass = 'fancybox-caption__title';
							if ( caption.length ) {
								titleClass = titleClass + ' fancybox-caption__title-margin';
							}
							caption = '<div class="' + titleClass + '">' + title + '</div>' + caption;
						}
					}

					if ( caption.length ) {
						opts.caption = caption;
					}

					opts.parsedOpts = true; // prevent duplicating caption since we are storing new caption in data

				}

				if ( $this.hasClass( 'wpex-lightbox-iframe' ) ) {
					type = 'iframe'; // for use with random modules
				}

				if ( $this.hasClass( 'wpex-lightbox-inline' ) ) {
					type = 'inline'; // for use with random modules
				}

				if ( $this.hasClass( 'rev-btn' ) ) {
					type = '';
					opts = {}; // fixes rev slider issues.
				}

				$.fancybox.open( [ {
					src  : src,
					opts : opts,
					type : type
				} ], $.extend( {}, wpexLightboxSettings, customSettings ) );

			} );

		},

		/**
		 * Inline Lightbox Gallery.
		 */
		lightboxInlineGallery: function( $context ) {

			var self = this;

			$context = $context || $( document );

			$context.on( 'click', '.wpex-lightbox-gallery', function( e ) {

				e.preventDefault();

				var $this   = $( this );
				var gallery = $this.data( 'gallery' ) || '';
				var items   = [];

				if ( gallery.length && 'object' === typeof gallery ) {

					$.each( gallery, function( index, val ) {
						var opts    = {};
						var title   = val.title || '';
						var caption = val.caption || '';
						if ( title.length ) {
							var titleClass = 'fancybox-caption__title';
							if ( caption.length ) {
								titleClass = titleClass + ' fancybox-caption__title-margin';
							}
							caption = '<div class="' + titleClass + '">' + title + '</div>' + caption;
						}
						if ( caption.length ) {
							opts.caption = caption;
						}
						opts.thumb = val.thumb || val.src;
						items.push( {
							src  : val.src,
							opts : opts
						} );
					} );

					$.fancybox.open( items, wpexLightboxSettings );

				}

			} );

		},

		/**
		 * Gallery lightbox
		 */
		lightboxGallery: function( $context ) {

			var self = this;
			$context = $context || $( document );

			$( 'a.wpex-lightbox-group-item' ).removeClass( 'wpex-lightbox' ); // Prevent conflicts (can't be a group item and a single lightbox item

			$context.on( 'click', 'a.wpex-lightbox-group-item', function( e ) {

				e.preventDefault();

				$( '.wpex-lightbox-group-item' ).removeAttr( 'data-lb-index' ); // Remove all lb-indexes to prevent issues with filterable grids or hidden items

				var $this          = $( this );
				var $group         = $this.closest( '.wpex-lightbox-group' );
				var $groupItems    = $group.find( 'a.wpex-lightbox-group-item:visible' );
				var customSettings = {};
				var items          = [];
				var activeIndex    = 0;

				$groupItems.each( function( index ) {

					var $item      = $( this );
					var opts       = $item.data() || {};
					var src        = $item.attr( 'href' ) || $item.data( 'src' ) || '';
					var title      = '';
					var show_title = $item.attr( 'data-show_title' ) || true;
					var caption    = $item.data( 'caption' ) || '';
					var oldOpts    = $item.data( 'options' ) && self.parseObjectLiteralData( '({' + $item.data( 'options' ) + '})' ) || '';

					if ( ! opts.parsedOpts ) {

						opts.thumb = $item.data( 'thumb' ) || src;

						if ( oldOpts ) {
							opts.thumb = oldOpts.thumbnail || opts.thumb;
							if ( oldOpts.iframeType && 'video' == oldOpts.iframeType ) {
								opts.type = '';
							}
						}

						if ( 'false' !== show_title ) {
							title = $item.data( 'title' ) || $item.attr( 'title' ) || '';
							if ( title.length ) {
								var titleClass = 'fancybox-caption__title';
								if ( caption.length ) {
									titleClass = titleClass + ' fancybox-caption__title-margin';
								}
								caption = '<div class="' + titleClass + '">' + title + '</div>' + caption;
							}
						}

						if ( caption.length ) {
							opts.caption = caption;
						}

						opts.parsedOpts = true;

					}

					if ( src ) {

						$item.attr( 'data-lb-index', index );

						if ( $this[0] == $item[0] ) {
							activeIndex = index;
						}

						items.push( {
							src  : src,
							opts : opts
						} );

					}

				} );

				$.fancybox.open( items, $.extend( {}, wpexLightboxSettings, customSettings ), activeIndex );

			} );

		},

		/**
		 * Carousel Lightbox.
		 *
		 * @todo place code in it's own file and load conditionally only when needed.
		 */
		lightboxCarousels: function( $context ) {

			var self = this;
			$context = $context || $( document );

			$context.on( 'click', '.wpex-carousel-lightbox-item', function( e ) {

				e.preventDefault();

				var $this          = $( this );
				var $parent        = $this.parents( '.wpex-carousel' );
				var $owlItems      = $parent.find( '.owl-item' );
				var items          = [];
				var customSettings = {
					loop : true // carousels should always loop so it's not strange when clicking an item after scrolling.
				};

				$owlItems.each( function() {

					if ( ! $( this ).hasClass( 'cloned' ) ) {

						var $item = $( this ).find( '.wpex-carousel-lightbox-item' );

						if ( $item.length ) {

							var opts       = {};
							var src        = $item.attr( 'href' ) || $item.data( 'src' ) || '';
							var title      = $item.data( 'title' ) || $item.attr( 'title' ) || '';
							var caption    = $item.data( 'caption' ) || '';
							var show_title = $item.attr( 'data-show_title' ) || true;

							if ( 'false' !== show_title && title.length ) {
								var titleClass = 'fancybox-caption__title';
								if ( caption.length ) {
									titleClass = titleClass + ' fancybox-caption__title-margin';
								}
								caption = '<div class="' + titleClass + '">' + title + '</div>' + caption;
							}

							if ( caption.length ) {
								opts.caption = caption;
							}

							opts.thumb = $item.data( 'thumb' ) || src;

							items.push( {
								src  : src,
								opts : opts
							} );

						}

					}

				} );

				if ( items.length && 'object' === typeof items ) {
					var activeIndex = $this.data( 'count' ) - 1 || 0;
					$.fancybox.open( items, $.extend( {}, wpexLightboxSettings, customSettings ), activeIndex );
				}

			} );

		},

		/**
		 * Overlay Mobile Support.
		 */
		overlaysMobileSupport: function() {

			if ( ! this.mobileCheck() ) {
				return;
			}

			// Remove overlays completely if mobile support is disabled
			$( '.overlay-parent.overlay-hh' ).each( function() {
				if ( ! $( this ).hasClass( 'overlay-ms' ) ) {
					$( this ).find( '.theme-overlay' ).remove();
				}
			} );

			// Prevent click on touchstart
			$( 'a.overlay-parent.overlay-ms.overlay-h, .overlay-parent.overlay-ms.overlay-h > a' ).on( 'touchstart', function( e ) {

				var $this = $( this );
				var $overlayParent = $this.hasClass( 'overlay-parent' ) ? $this : $this.parent( '.overlay-parent' );

				if ( $overlayParent.hasClass( 'wpex-touched' ) ) {
					return true;
				} else {
					$overlayParent.addClass( 'wpex-touched' );
					$( '.overlay-parent' ).not($overlayParent).removeClass( 'wpex-touched' );
					e.preventDefault();
					return false;
				}

			} );

			// Hide overlay when clicking outside
			$( document ).on( 'touchstart', function( e ) {
				if ( ! $( e.target ).closest( '.wpex-touched' ).length ) {
					$( '.wpex-touched' ).removeClass( 'wpex-touched' );
				}
			} );

		},

		/**
		 * Overlay Hovers.
		 *
		 * @todo move into it's own JS file and load conditionally? Would need to update all the loadmore/ajax functions accordingly.
		 */
		overlayHovers: function() {

			// Overlay title push up.
			$( '.overlay-parent-title-push-up' ).each( function() {

				// Define vars
				var $this        = $( this ),
					$title       = $this.find( '.overlay-title-push-up' ),
					$child       = $this.find( 'a' ),
					$img         = $child.find( 'img' ),
					$titleHeight = $title.outerHeight();

				// Position title
				$title.css( {
					'bottom' : - $titleHeight
				} );

				// Add height to child
				$child.css( {
					'height' : $img.outerHeight()
				} );

				// Position image
				$img.css( {
					'position' : 'absolute',
					'top'      : '0',
					'left'     : '0',
					'height'   : 'auto'
				} );

				// Animate image on hover
				$this.hover( function() {
					$img.css( {
						'top' : -20
					} );
					$title.css( {
						'bottom' : 0
					} );
				}, function() {
					$img.css( {
						'top' : '0'
					} );
					$title.css( {
						'bottom' : - $titleHeight
					} );
				} );

			} );

		},

		/**
		 * Sticky Topbar.
		 */
		stickyTopBar: function() {

			var self           = this,
				$isSticky      = false,
				$offset        = 0,
				$window        = $( window ),
				$stickyTopbar  = $( '#top-bar-wrap.wpex-top-bar-sticky' ),
				$brkPoint      = l10n.stickyTopBarBreakPoint,
				$mobileMenu    = $( '#wpex-mobile-menu-fixed-top' ),
				$stickyWrap    = $( '<div id="top-bar-wrap-sticky-wrapper" class="wpex-sticky-top-bar-holder not-sticky"></div>' );

			if ( ! $stickyTopbar.length ) {
				return;
			}

			// Set sticky wrap to new wrapper
			$stickyTopbar.wrapAll( $stickyWrap );
			$stickyWrap = $( '#top-bar-wrap-sticky-wrapper' );

			// Get offset
			function getOffset() {
				$offset = 0; // Reset offset for resize
				var $wpToolbar = $( '#wpadminbar' );
				if ( $wpToolbar.is( ':visible' ) && $wpToolbar.css( 'position' ) === 'fixed' ) {
					$offset = $offset + $wpToolbar.outerHeight();
				}
				if ( $mobileMenu.is( ':visible' ) ) {
					$offset = $offset + $mobileMenu.outerHeight();
				}
				return $offset;
			}

			// Stick the TopBar
			function setSticky() {

				if ( $isSticky ) {
					return;
				}

				// Add wrap class and toggle sticky class
				$stickyWrap
					.css( 'height', $stickyTopbar.outerHeight() )
					.removeClass( 'not-sticky' )
					.addClass( 'is-sticky' );

				// Add CSS to topbar
				$stickyTopbar.css( {
					'top'   : getOffset(),
					'width' : $stickyWrap.width()
				} );

				// Set sticky to true
				$isSticky = true;

			}

			// Unstick the TopBar
			function destroySticky() {

				if ( ! $isSticky ) {
					return;
				}

				// Remove sticky wrap height and toggle sticky class
				$stickyWrap
					.css( 'height', '' )
					.removeClass( 'is-sticky' )
					.addClass( 'not-sticky' );

				// Remove topbar css
				$stickyTopbar.css( {
					'width' : '',
					'top'   : ''
				} );

				// Set sticky to false
				$isSticky = false;

			}

			// Runs on load and resize
			function initSticky() {

				if ( ! l10n.hasStickyTopBarMobile && ( self.viewportWidth() < $brkPoint ) ) {
					$stickyWrap.removeClass( 'wpex-can-sticky' );
					destroySticky();
					return;
				}

				$stickyWrap.addClass( 'wpex-can-sticky' );

				if ( $isSticky ) {

					$stickyWrap.css( 'height', $stickyTopbar.outerHeight() );

					$stickyTopbar.css( {
						'top'   : getOffset(),
						'width' : $stickyWrap.width()
					} );

				} else {

					// Set sticky based on original offset
					$offset = $stickyWrap.offset().top - getOffset();

					// Set or destroy sticky
					if ( $window.scrollTop() > $offset ) {
						setSticky();
					} else {
						destroySticky();
					}

				}

			}

			// On scroll actions for sticky topbar
			function onScroll() {

				var windowTop = $window.scrollTop();

				// Set or destroy sticky based on offset
				if ( ( 0 !== windowTop ) && ( windowTop >= ( $stickyWrap.offset().top - getOffset() ) ) ) {
					setSticky();
				} else {
					destroySticky();
				}

			}

			// Fire on init
			initSticky();

			// Fire onscroll event
			$window.scroll( function() {
				if ( $stickyWrap.hasClass( 'wpex-can-sticky' ) ) {
					onScroll();
				}
			} );

			// Fire onResize
			$window.resize( function() {
				initSticky();
			} );

			// On orientation change destroy sticky and recalculate
			$window.on( 'orientationchange' , function( e ) {
				destroySticky();
				initSticky();
			} );

		},

		/**
		 * Get correct offSet for the sticky header and sticky header menu.
		 *
		 * @todo rename to stickyHeaderOffset
		 */
		stickyOffset: function() {
			var $offset       = 0;
			var $mobileMenu   = $( '#wpex-mobile-menu-fixed-top' );
			var $stickyTopbar = $( '#top-bar-wrap-sticky-wrapper.wpex-can-sticky' );
			var $wpToolbar    = $( '#wpadminbar' );

			// Offset sticky topbar
			if ( $stickyTopbar.is( ':visible' ) ) {
				$offset = $offset + $stickyTopbar.outerHeight();
			}

			// Offset mobile menu
			if ( $mobileMenu.is( ':visible' ) ) {
				$offset = $offset + $mobileMenu.outerHeight();
			}

			// Offset adminbar
			if ( $wpToolbar.is( ':visible' ) && $wpToolbar.css( 'position' ) === 'fixed' ) {
				$offset = $offset + $wpToolbar.outerHeight();
			}

			// Added offset via child theme
			if ( l10n.addStickyHeaderOffset ) {
				$offset = $offset + l10n.addStickyHeaderOffset;
			}

			// Return correct offset
			return $offset;

		},

		/**
		 * Sticky header custom start point.
		 */
		stickyHeaderCustomStartPoint: function() {
			var $startPosition = l10n.stickyHeaderStartPosition;
			if ( $.isNumeric( $startPosition ) ) {
				$startPosition = $startPosition;
			} else if ( $( $startPosition ).length ) {
				$startPosition = $( $startPosition ).offset().top;
			} else {
				$startPosition = 0;
			}
			return $startPosition;
		},

		/**
		 * New Sticky Header.
		 */
		stickyHeader: function() {

			if ( 'standard' !== l10n.stickyHeaderStyle
				&& 'shrink' !== l10n.stickyHeaderStyle
				&& 'shrink_animated' !== l10n.stickyHeaderStyle
			) {
				return;
			}

			var $header = $( '#site-header.fixed-scroll' );

			if ( ! $header.length ) {
				return;
			}

			var self           = this,
				$isSticky      = false,
				$isShrunk      = false,
				$isLogoSwapped = false;

			// Define header
			var $headerHeight  = $header.outerHeight();
			var $headerBottom  = $header.offset().top + $header.outerHeight();

			// Add sticky wrap
			var $stickyWrap = $( '<div id="site-header-sticky-wrapper" class="wpex-sticky-header-holder not-sticky"></div>' );
			$header.wrapAll( $stickyWrap );
			$stickyWrap     = $( '#site-header-sticky-wrapper' ); // Cache newly added element as dom object

			// Define main vars for sticky function
			var $window        = $( window );
			var $brkPoint      = l10n.stickyHeaderBreakPoint;
			var $mobileSupport = l10n.hasStickyMobileHeader;
			var $customStart   = self.stickyHeaderCustomStartPoint();

			// Custom sticky logo
			var $logo    = $( '#site-logo img.logo-img' );
			var $logoSrc = $logo.length ? $logo.attr( 'src' ) : ''; // store orignal logo

			// Shrink support
			var maybeShrink = ( 'shrink' == l10n.stickyHeaderStyle || 'shrink_animated' == l10n.stickyHeaderStyle ) ? true : false;

			// Custom shrink logo
			var $stickyLogo = l10n.stickyheaderCustomLogo;
			if ( $stickyLogo && l10n.stickyheaderCustomLogoRetina && self.retinaCheck() ) {
				$stickyLogo = l10n.stickyheaderCustomLogoRetina;
			}

			// Check if we are on mobile size
			function pastBreakPoint() {
				return ( self.viewportWidth() < $brkPoint ) ? true : false;
			}

			// Check if we are past the header
			function pastheader() {
				var bottomCheck = 0;
				if ( $( '#overlay-header-wrap' ).length ) {
					bottomCheck = $headerBottom;
				} else {
					bottomCheck = $stickyWrap.offset().top + $stickyWrap.outerHeight();
				}
				if ( $( window ).scrollTop() > bottomCheck ) {
					return true;
				}
				return false;
			}

			// Check start position
			function start_position() {
				var $startPosition = $customStart;
				$startPosition = $startPosition ? $startPosition : $stickyWrap.offset().top;
				return $startPosition - self.stickyOffset();
			}

			// Transform
			function transformPrepare() {
				if ( $isSticky ) {
					$header.addClass( 'transform-go' ); // prevent issues when scrolling
				}
				if ( 0 === $( window ).scrollTop() ) {
					$header.removeClass( 'transform-prepare' );
				} else if ( pastheader() ) {
					$header.addClass( 'transform-prepare' );
				} else {
					$header.removeClass( 'transform-prepare' );
				}
			}

			// Sticky logo swap
			function swapLogo() {

				if ( $isLogoSwapped ) {
					$logo.attr( 'src', $logoSrc );
					$isLogoSwapped = false;

				} else {
					$logo.attr( 'src', $stickyLogo );
					$isLogoSwapped = true;
				}

			}

			// Shrink/unshrink header
			function shrink() {

				var checks = maybeShrink;

				if ( pastBreakPoint() ) {
					if ( $mobileSupport && ( 'icon_buttons' == l10n.mobileMenuToggleStyle || 'fixed_top' == l10n.mobileMenuToggleStyle ) ) {
						checks = true;
					} else {
						checks = false;
					}
				}

				if ( checks && pastheader() ) {

					if ( ! $isShrunk && $isSticky ) {
						$header.addClass( 'sticky-header-shrunk' );
						$isShrunk = true;
					}

				} else {

					$header.removeClass( 'sticky-header-shrunk' );
					$isShrunk = false;

				}

			}

			// Set sticky
			function setSticky() {

				// Already stuck
				if ( $isSticky ) {
					return;
				}

				// Custom Sticky logo
				if ( $stickyLogo && $logo ) {
					swapLogo();
				}

				// Add wrap class and toggle sticky class
				$stickyWrap
					.css( 'height', $headerHeight )
					.removeClass( 'not-sticky' )
					.addClass( 'is-sticky' );

				// Tweak header
				$header.removeClass( 'dyn-styles' ).css( {
					'top'       : self.stickyOffset(),
					'width'     : $stickyWrap.width()
				} );

				// Add transform go class
				if ( $header.hasClass( 'transform-prepare' ) ) {
					$header.addClass( 'transform-go' );
				}

				// Set sticky to true
				$isSticky = true;

			}

			// Destroy actions
			function destroyActions() {

				// Reset sticky logo
				if ( $stickyLogo && $logo ) {
					swapLogo();
				}

				// Remove sticky wrap height and toggle sticky class
				$stickyWrap.removeClass( 'is-sticky' ).addClass( 'not-sticky' );

				// Do not remove height on sticky header for shrink header incase animation isn't done yet
				if ( ! $header.hasClass( 'shrink-sticky-header' ) ) {
					$stickyWrap.css( 'height', '' );
				}

				// Reset header
				$header.addClass( 'dyn-styles' ).css( {
					'width' : '',
					'top'   : ''
				} ).removeClass( 'transform-go' );

				// Set sticky to false
				$isSticky = false;

				// Make sure shrink header is removed
				$header.removeClass( 'sticky-header-shrunk' ); // Fixes some bugs with really fast scrolling
				$isShrunk = false;

			}

			// Destroy sticky
			function destroySticky() {

				// Already unstuck
				if ( ! $isSticky ) {
					return;
				}

				if ( $customStart ) {
					$header.removeClass( 'transform-go' );
					if ( $isShrunk ) {
						$header.removeClass( 'sticky-header-shrunk' );
						$isShrunk = false;
					}
				} else {
					$header.removeClass( 'transform-prepare' );
				}

				destroyActions();

			}

			// On load check
			function initResizeSetSticky() {

				var windowTop = $( window ).scrollTop();

				if ( ! $mobileSupport && pastBreakPoint() ) {
					destroySticky();
					$stickyWrap.removeClass( 'wpex-can-sticky' );
					$header.removeClass( 'transform-prepare' );
					return;
				}

				//$header.addClass( 'transform-go' );
				$stickyWrap.addClass( 'wpex-can-sticky' );

				if ( $isSticky ) {

					if ( ! $header.hasClass( 'shrink-sticky-header' ) ) {
						$stickyWrap.css( 'height', $header.outerHeight() ); // @todo revise if this is even needed
					}

					$header.css( {
						'top'   : self.stickyOffset(),
						'width' : $stickyWrap.width()
					} );

				} else {

					if ( 0 !== windowTop && windowTop > start_position() ) {
						setSticky();
					} else {
						destroySticky();
					}

				}

				if ( maybeShrink ) {
					shrink();
				}

			}

			// On scroll function
			function onScroll() {

				var windowTop = $( window ).scrollTop();

				// Disable on mobile devices
				if ( ! $stickyWrap.hasClass( 'wpex-can-sticky' ) ) {
					return;
				}

				// Animate scroll with custom start
				if ( $customStart ) {
					transformPrepare();
				}

				// Set or destroy sticky
				if ( 0 != windowTop && windowTop >= start_position() ) {
					setSticky();
				} else {
					destroySticky();
				}

				// Shrink
				if ( maybeShrink ) {
					shrink();
				}

			}

			// Fire on init
			initResizeSetSticky();

			// Fire onscroll event
			$window.scroll( function() {
				onScroll();
			} );

			// Fire onResize
			$window.resize( function() {
				initResizeSetSticky();
			} );

			// Destroy and run onResize function on orientation change
			$window.on( 'orientationchange' , function() {
				destroySticky();
				initResizeSetSticky();
			} );

		},

		/**
		 * Sticky Header Menu.
		 */
		stickyHeaderMenu: function() {

			var $stickyNav = $( '#site-navigation-wrap.fixed-nav' );

			if ( ! $stickyNav.length ) {
				return;
			}

			var self        = this,
				$isSticky   = false,
				$window     = $( window ),
				$stickyWrap = $( '<div id="site-navigation-sticky-wrapper" class="wpex-sticky-navigation-holder not-sticky"></div>' );

			// Define sticky wrap
			$stickyNav.wrapAll( $stickyWrap );
			$stickyWrap = $( '#site-navigation-sticky-wrapper' );

			// Add offsets
			var $stickyWrapTop = $stickyWrap.offset().top,
				$stickyOffset  = self.stickyOffset(),
				$setStickyPos  = $stickyWrapTop - $stickyOffset;

			// Shrink header function
			function setSticky() {

				// Already sticky
				if ( $isSticky ) {
					return;
				}

				// Add wrap class and toggle sticky class
				$stickyWrap
					.css( 'height', $stickyNav.outerHeight() )
					.removeClass( 'not-sticky' )
					.addClass( 'is-sticky' );

				// Add CSS to topbar
				$stickyNav.css( {
					'top'   : self.stickyOffset(),
					'width' : $stickyWrap.width()
				} );

				// Remove header dynamic styles
				$( '#site-header' ).removeClass( 'dyn-styles' );

				// Update shrunk var
				$isSticky = true;

			}

			// Un-Shrink header function
			function destroySticky() {

				// Not shrunk
				if ( ! $isSticky ) {
					return;
				}

				// Remove sticky wrap height and toggle sticky class
				$stickyWrap
					.css( 'height', '' )
					.removeClass( 'is-sticky' )
					.addClass( 'not-sticky' );

				// Remove navbar width
				$stickyNav.css( {
					'width' : '',
					'top'   : ''
				} );

				// Re-add dynamic header styles
				$( '#site-header' ).addClass( 'dyn-styles' );

				// Update shrunk var
				$isSticky = false;

			}

			// On load check
			function initResizeSetSticky() {

				if ( self.viewportWidth() <= l10n.stickyNavbarBreakPoint ) {
					destroySticky();
					$stickyWrap.removeClass( 'wpex-can-sticky' );
					return;
				}

				var windowTop = $window.scrollTop();

				$stickyWrap.addClass( 'wpex-can-sticky' );

				if ( $isSticky ) {
					$stickyNav.css( 'width', $stickyWrap.width() );
				} else {
					if ( windowTop >= $setStickyPos && 0 !== windowTop ) {
						setSticky();
					} else {
						destroySticky();
					}

				}

			}

			// Sticky check / enable-disable
			function onScroll() {

				if ( ! $stickyWrap.hasClass( 'wpex-can-sticky' ) ) {
					return;
				}

				var windowTop = $window.scrollTop();

				// Sticky menu
				if ( 0 !== windowTop && windowTop >= $setStickyPos ) {
					setSticky();
				} else {
					destroySticky();
				}

			}

			// Fire on init
			initResizeSetSticky();

			// Fire onscroll event
			$window.scroll( function() {
				onScroll();
			} );

			// Fire onResize
			$window.resize( function() {
				initResizeSetSticky();
			} );

			// Fire resize on flip
			$window.on( 'orientationchange' , function() {
				destroySticky();
				initResizeSetSticky();
			} );

		},

		/**
		 * WPBAKERY Slider & Accordions.
		 *
		 * @todo move into their own file and load conditionally.
		 */
		vcTabsTogglesJS: function() {

			if ( ! $( 'body' ).hasClass( 'wpb-js-composer' ) ) {
				return;
			}

			var self = this;

			function onShow( e ) {

				if ( 'undefined' === typeof e ) {
					return;
				}

				var $this, tab;

				$this = $( this );
				tab = $this.data( 'vc.accordion' );

				if ( tab ) {

					tab = tab.getTarget();

					if ( tab.length ) {

						if ( 'undefined' !== typeof $.fn.sliderPro ) {
							tab.find( '.wpex-slider' ).each( function() {
								$( this ).sliderPro( 'update' );
							} );
						}

						if ( 'undefined' !== typeof $.fn.isotope ) {
							tab.find( '.vcex-isotope-grid, .wpex-masonry-grid' ).each( function() {
								$( this ).isotope( 'layout' );
							} );
						}

						if ( 'undefined' !== typeof window.vcexNavbarFilterLinks ) {
							window.vcexNavbarFilterLinks( tab );
						}

					}

				}

			}

			//$( '.vc_tta-tabs' ).on( 'show.vc.tab', onShow ); // not used since 5.0 causes double trigger since show.vc.accordion is also triggered
			$( '[data-vc-accordion]' ).on( 'show.vc.accordion', onShow );

		},

		/**
		 * WPBAKERY Accessability fixes.
		 *
		 * @todo move into their own file and load conditionally.
		 */
		vcAccessability: function() {

			if ( ! $( 'body' ).hasClass( 'wpb-js-composer' ) ) {
				return;
			}

			var self = this;

			// Add tab index to toggles and toggle on enter
			var $toggles = $( '.vc_toggle .vc_toggle_title' );
			$toggles.each( function( index ) {
				var $this = $( this );
				$this.attr( 'tabindex', 0 );
				$this.on( 'keydown', function( e ) {
					if ( 13 == e.which ) {
						$this.trigger( 'click' );
					}
				} );
			} );

			// Add tabs aria and role attributes
			$( '.vc_tta-tabs-list' ).attr( 'role', 'tablist' );
			$( '.vc_tta-tab > a' ).attr( 'role', 'tab' ).attr( 'aria-selected', 'false' );
			$( '.vc_tta-tab.vc_active > a' ).attr( 'aria-selected', 'true' );
			$( '.vc_tta-panel-body' ).attr( 'role', 'tabpanel' );

			// Change arias on click
			$( document ).on( 'click.vc.tabs.data-api', '[data-vc-tabs]', function( e ) {
				var $this;
				$this = $( this );
				$this.closest( '.vc_tta-tabs-list' ).find( '.vc_tta-tab > a' ).attr( 'aria-selected', 'false' );
				$this.parent( '.vc_tta-tab' ).find( '> a').attr( 'aria-selected', 'true' );
			} );

			// Add Tab arrow navigation support
			var $tabContainers = $( '.vc_tta-container' );

			var tabClick = function( $thisTab, $allTabs, $tabPanels, i ) {
				$allTabs.attr( 'tabindex', -1 );
				$thisTab.attr( 'tabindex', 0 ).focus().click();
			};

			$tabContainers.each( function() {

				var $tabContainer = $( this ),
					$tabs         = $tabContainer.find( '.vc_tta-tab > a' ),
					$panels       = $tabContainer.find( '.vc_tta-panels' );

				$tabs.each( function( index ) {

					var $tab = $( this );

					if ( 0 == index ) {
						$tab.attr( 'tabindex', 0 );
					} else {
						$tab.attr( 'tabindex', -1 );
					}

					$tab.on( 'keydown', function( e ) {

						var $this        = $( this ),
							keyCode      = e.which,
							$nextTab     = $this.parent().next().is( 'li.vc_tta-tab' ) ? $this.parent().next().find( 'a' ) : false,
							$previousTab = $this.parent().prev().is( 'li.vc_tta-tab' ) ? $this.parent().prev().find( 'a' ) : false,
							$firstTab    = $this.parent().parent().find( 'li.vc_tta-tab:first' ).find( 'a' ),
							$lastTab     = $this.parent().parent().find( 'li.vc_tta-tab:last' ).find( 'a' );

						switch( keyCode ) {

							// Left/Up
							case 37 :
							case 38 :
								e.preventDefault();
								e.stopPropagation();
								if ( ! $previousTab) {
									tabClick( $lastTab, $tabs, $panels );
								} else {
									tabClick( $previousTab, $tabs, $panels );
								}
							break;

							// Right/Down
							case 39 :
							case 40 :
								e.preventDefault();
								e.stopPropagation();
								if ( ! $nextTab ) {
									tabClick( $firstTab, $tabs, $panels );
								} else {
									tabClick( $nextTab, $tabs, $panels );
								}
							break;

							// Home
							case 36 :
								e.preventDefault();
								e.stopPropagation();
								tabClick( $firstTab, $tabs, $panels );
								break;

							// End
							case 35 :
								e.preventDefault();
								e.stopPropagation();
								tabClick( $lastTab, $tabs, $panels );
							break;

							// Enter/Space
							case 13 :
							case 32 :
								e.preventDefault();
								e.stopPropagation();
							break;

						} // end switch

					} );

				} );

			} );

		},

		/**
		 * Creates accordion menu.
		 */
		menuAccordion: function( el ) {

			var dropDownParents = el.find( '.menu-item-has-children, .sidr-class-menu-item-has-children' );

			// Add toggle arrows
			dropDownParents.each( function() {
				var $link = $( this ).children( 'a' ),
					ariaOpen = l10n.i18n.openSubmenu.replace( '%s', $link.text() );
				$link.append( '<div class="wpex-open-submenu" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false" aria-label="' + ariaOpen + '"><span class="ticon ticon-angle-down" aria-hidden="true"></span></div>' );
			} );

			// Dropdowns
			var $subToggleBtn = $( '.menu-item-has-children > a > .wpex-open-submenu, .sidr-class-menu-item-has-children > a > .wpex-open-submenu', el );

			function subDropdowns( $arrow ) {

				var $parentLi = $arrow.closest( 'li' ),
					$link     = $parentLi.children( 'a' ),
					linkText  = $link.text();

				// Close items
				if ( $parentLi.hasClass( 'active' ) ) {
					$arrow.attr( 'aria-expanded', 'false' ).attr( 'aria-label', l10n.i18n.openSubmenu.replace( '%s', linkText ) );
					$parentLi.removeClass( 'active' );
					$parentLi.find( 'li' ).removeClass( 'active' );
					$parentLi.find( 'ul' ).stop( true, true ).slideUp( 'fast' );
				}

				// Open items
				else {
					$arrow.attr( 'aria-expanded', 'true' ).attr( 'aria-label', l10n.i18n.closeSubmenu.replace( '%s', linkText ) );
					var $allParentLis = $parentLi.parents( 'li' );
					$( '.menu-item-has-children', el )
						.not( $allParentLis )
						.removeClass( 'active' )
						.children( 'ul' )
						.stop( true, true )
						.slideUp( 'fast' );
					$parentLi.addClass( 'active' ).children( 'ul' ).stop( true, true ).slideDown( 'fast' );
				}

			}

			$subToggleBtn.on( 'click', function() {
				subDropdowns( $( this ) );
				return false;
			} );

			// Toggle on enter
			$subToggleBtn.on( 'keydown', function( e ) {
				if ( ( e.keyCode === 13 && ! e.shiftKey ) ) {
					subDropdowns( $( this ) );
				}
			} );

		},

		/**
		 * Set correct focus states for custom elements.
		 *
		 * @param {HTMLElement} el
		 */
		focusOnElement: function( el ) {

			var focusableItems     = el.find( 'select, input, textarea, button, a' ).filter( ':visible' ),
				firstFocusableItem = focusableItems.first(),
				lastFocusableItem  = focusableItems.last();

			// Add initial focus
			firstFocusableItem.focus();

			// Redirect last tab to first input.
			lastFocusableItem.on( 'keydown', function ( e ) {
				if ( ( e.keyCode === 9 && ! e.shiftKey ) ) {
					e.preventDefault();
					firstFocusableItem.focus();
				}
			} );

			// Redirect first shift+tab to last input.
			firstFocusableItem.on( 'keydown', function ( e ) {
				if ( ( e.keyCode === 9 && e.shiftKey ) ) {
					e.preventDefault();
					lastFocusableItem.focus();
				}
			} );

		},

		/**
		 * Parses data to check if a value is defined in the data attribute and if not returns the fallback.
		 */
		pData: function( val, fallback ) {
			return ( 'undefined' !== typeof val ) ? val : fallback;
		},

		/**
		 * Make sure el exists and isn't empty.
		 */
		isEmpty: function( el ) {
			return ! el.length || ! $.trim( el.html() ).length;
		},

		/**
		 * Grabs content and inserts into another element.
		 */
		insertExtras: function( el, target, method ) {
			if ( ! target.length || this.isEmpty( el ) ) {
				return;
			}
			switch ( method ) {
				case 'append':
					target.append( el );
					break;
				case 'prepend':
					target.prepend( el );
					break;
			}
			el.removeClass( 'wpex-hidden' );
		},

		/**
		 * Returns extension from URL.
		 */
		getUrlExtension: function( url ) {
			var ext = url.split( '.' ).pop().toLowerCase();
			var extra = ext.indexOf( '?' ) !== -1 ? ext.split( '?' ).pop() : '';
			ext = ext.replace( extra, '' );
			return ext.replace( '?', '' );
		},

		/**
		 * Check if window has scrolled to bottom of element.
		 */
		scrolledToBottom: function( elem ) {
			return $( window ).scrollTop() >= elem.offset().top + elem.outerHeight() - window.innerHeight;
		},

		/**
		 * Parses data attribute and returns object.
		 */
		parseObjectLiteralData: function( data ) {
			var properties = data.split( ',' );
			var obj = {};
			$.each(properties, function(index, item) {
				var tup = item.split(':');
				obj[tup[0]] = tup[1];
			} );
			return obj;

		}

	};

	// Start things up
	wpex.init();

} ) ( jQuery, wpexLocalize );