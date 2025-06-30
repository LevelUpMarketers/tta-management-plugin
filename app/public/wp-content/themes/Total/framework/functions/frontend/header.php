<?php
/**
 * Site Header Helper Functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*

	# General
	# Logo
	# Overlay/Transparent Header
	# Sticky
	# Header Aside

/*-------------------------------------------------------------------------------*/
/* [ General ]
/*-------------------------------------------------------------------------------*/

/**
 * Check if site header is enabled.
 *
 * @since 4.0
 */
function wpex_has_header( $post_id = '' ) {

	// Check if enabled by default
	if ( wpex_has_custom_header() || wpex_elementor_location_exists( 'header' ) ) {
		$bool = true;
	} else {
		$bool = get_theme_mod( 'enable_header', true );
	}

	// Get current post ID
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	// Check meta
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_disable_header', true ) ) {
		if ( 'on' == $meta ) {
			$bool = false;
		} elseif ( 'enable' == $meta ) {
			$bool = true;
		}
	}

	// Apply filters and bool value
	return apply_filters( 'wpex_display_header', $bool ); // @todo rename to wpex_has_header for consistency

}

/**
 * Get header style.
 *
 * @since 4.0
 */
function wpex_header_style( $post_id = '' ) {

	// Return if header is disabled
	if ( ! wpex_has_header() ) {
		return 'disabled';
	}

	// Check if builder is enabled
	if ( wpex_header_builder_id() ) {
		return 'builder';
	}

	// Get header style from customizer setting
	$style = get_theme_mod( 'header_style', 'one' );

	// Overlay Header supported styles
	$supported_overlay_header_styles = array( 'one', 'five', 'dev', 'flex' ); // @todo add filter?

	// Overlay header supports certain header styles only
	if ( ! in_array( $style, $supported_overlay_header_styles ) && wpex_has_overlay_header() ) {
		$style = 'one';
	}

	// Get current post ID
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	// Check for custom header style defined in meta options => Overrides all
	if ( 'dev' !== $style
		&& $post_id
		&& $meta = get_post_meta( $post_id, 'wpex_header_style', true ) ) {
		$style = $meta;
	}

	// Sanitize style to make sure it isn't empty
	$style = $style ? $style : 'one';

	// Apply filters and return
	return apply_filters( 'wpex_header_style', $style );

}

/**
 * Check if the header style is in dev mode.
 *
 * @since 4.9.4
 */
function wpex_has_dev_style_header() {
	return ( 'dev' == wpex_header_style() ) ? true : false;
}

/**
 * Check if the header style is not in dev mode.
 *
 * @since 4.9.4
 */
function wpex_hasnt_dev_style_header() {
	return ( wpex_has_dev_style_header() ) ? false : true;
}

/**
 * Check if the header is set to vertical.
 *
 * @since 4.0
 */
function wpex_has_vertical_header() {
	$check = in_array( wpex_header_style(), array( 'six' ) ) ? true : false;
	return apply_filters( 'wpex_has_vertical_header', $check );
}

/**
 * Add classes to the header wrap.
 *
 * @since 1.5.3
 */
function wpex_header_classes() {

	// Vars
	$post_id      = wpex_get_current_post_id();
	$header_style = wpex_header_style( $post_id );

	// Setup classes array
	$classes = array();

	// Main header style
	$classes['header_style'] = 'header-' . sanitize_html_class( $header_style );

	// Non-Builder classes
	if ( 'builder' != $header_style ) {

		// Full width header
		if ( 'full-width' == wpex_site_layout() && get_theme_mod( 'full_width_header' ) ) {
			$classes[] = 'wpex-full-width';
		}

		// Non-dev classes
		if ( 'dev' != $header_style ) {

			// Flex header style two
			if ( 'two' == $header_style && get_theme_mod( 'header_flex_items', false ) ) {
				$classes[] = 'wpex-header-two-flex-v';
			}

			// Dropdown style (must be added here so we can target shop/search dropdowns)
			$dropdown_style = wpex_get_header_menu_dropdown_style();
			if ( $dropdown_style && 'default' != $dropdown_style ) {
				$classes[] = 'wpex-dropdown-style-' . sanitize_html_class( $dropdown_style );
			}

			// Dropdown shadows
			if ( $shadow = get_theme_mod( 'menu_dropdown_dropshadow' ) ) {
				$classes[] = 'wpex-dropdowns-shadow-' . sanitize_html_class( $shadow );
			}

		}

	}

	// Sticky Header
	if ( wpex_has_sticky_header() ) {

		// Fixed header style
		$fixed_header_style = wpex_sticky_header_style();

		// Main fixed class
		$classes['fixed_scroll'] = 'fixed-scroll'; // @todo rename this at some point?
		if ( wpex_has_shrink_sticky_header() ) {
			$classes['shrink-sticky-header'] = 'shrink-sticky-header';
			if ( 'shrink_animated' == $fixed_header_style ) {
				$classes['anim-shrink-header'] = 'anim-shrink-header';
			}
		}

	}

	// Header Overlay Style
	if ( wpex_has_overlay_header() ) {

		// Add overlay header class
		$classes[] = 'overlay-header';

		// Add overlay header style class
		$overlay_style = wpex_overlay_header_style();
		if ( $overlay_style ) {
			$classes[] = sanitize_html_class( $overlay_style ) . '-style';
		}

	}

	// Custom bg
	if ( get_theme_mod( 'header_background' ) ) {
		$classes[] = 'custom-bg';
	}

	// Background style
	if ( wpex_header_background_image() ) {
		$bg_style = get_theme_mod( 'header_background_image_style' );
		$bg_style = $bg_style ? $bg_style : '';
		$bg_style = apply_filters( 'wpex_header_background_image_style', $bg_style );
		if ( $bg_style ) {
			$classes[] = 'bg-' . sanitize_html_class( $bg_style );
		}
	}

	// Dynamic style class
	$classes[] = 'dyn-styles';

	// Clearfix class
	$classes[] = 'wpex-clr';

	// Sanitize classes
	$classes = array_map( 'esc_attr', $classes );

	// Set keys equal to vals
	$classes = array_combine( $classes, $classes );

	// Apply filters for child theming
	$classes = apply_filters( 'wpex_header_classes', $classes );

	// Turn classes into space seperated string
	$classes = implode( ' ', $classes );

	// return classes
	return $classes;

}

/**
 * Get site header background image
 *
 * @since 4.5.5.1
 */
function wpex_header_background_image() {

	// Get default Customizer value
	$image = get_theme_mod( 'header_background_image' );

	// Apply filters before meta checks => meta should always override
	$image = apply_filters( 'wpex_header_background_image', $image );

	// Check meta for bg image
	$post_id = wpex_get_current_post_id();
	if ( $post_id && $meta_image = get_post_meta( $post_id, 'wpex_header_background_image', true ) ) {
		$image = $meta_image;
	}

	// Return image
	return wpex_get_image_url( $image );
}

/**
 * Returns header logo image
 *
 * @since 4.0
 */
function wpex_header_logo_img() {
	$img = apply_filters( 'wpex_header_logo_img_url', wpex_get_translated_theme_mod( 'custom_logo' ) );
	if ( $img ) {
		return wpex_get_image_url( $img );
	}
}

/**
 * Check if the site is using a text logo
 *
 * @since 4.3
 */
function wpex_header_has_text_logo() {
	return wpex_header_logo_img() ? false : true;
}

/**
 * Returns header logo icon
 *
 * @since 2.0.0
 */
function wpex_header_logo_icon() {

	// Get logo img from admin panel
	$icon = esc_html( get_theme_mod( 'logo_icon' ) );

	// Apply filter for child theming
	$icon = apply_filters( 'wpex_header_logo_icon', $icon );

	// Apply an empty/hidden icon in the customizer for postMessage support
	if ( 'none' == $icon && is_customize_preview() ) {
		$icon = 'wpex-hidden';
	}

	// Return icon
	if ( $icon && 'none' !== $icon ) {
		return '<span id="site-logo-fa-icon" class="ticon ticon-' . esc_attr( $icon ) . '" aria-hidden="true"></span>';
	}

}

/**
 * Returns header logo title
 *
 * @since 2.0.0
 */
function wpex_header_logo_title() {
	return apply_filters( 'wpex_logo_title', get_bloginfo( 'name' ) ); // @todo rename to wpex_header_logo_title
}

/**
 * Check if the header logo should scroll up on click.
 *
 * @since 4.5.3
 */
function wpex_header_logo_scroll_top() {
	$bool = apply_filters( 'wpex_header_logo_scroll_top', false );
	if ( $post_id = wpex_get_current_post_id() ) {
		$meta = get_post_meta( $post_id, 'wpex_logo_scroll_top', true );
		if ( 'enable' == $meta ) {
			$bool = true;
		} elseif ( 'disable' == $meta ) {
			$bool = false;
		}
	}
	return $bool;
}

/**
 * Returns header logo URL.
 *
 * @since 2.0.0
 */
function wpex_header_logo_url() {
	$url = '';
	if ( wpex_header_logo_scroll_top() ) {
		$url = '#';
	} elseif ( wpex_vc_is_inline() ) {
		$url = get_permalink();
	}
	$url = $url ? $url : home_url( '/' );
	return apply_filters( 'wpex_logo_url', $url ); // @todo rename to wpex_header_logo_url
}

/**
 * Header logo classes.
 *
 * @since 2.0.0
 */
function wpex_header_logo_classes() {

	// Define classes array
	$classes = array(
		'site-branding',
	);

	// Default class
	$classes[] = 'header-' . sanitize_html_class( wpex_header_style() ) . '-logo';

	// Get custom overlay logo
	if ( wpex_has_post_meta( 'wpex_overlay_header' ) && wpex_overlay_header_logo_img() && wpex_has_overlay_header() ) {
		$classes[] = 'has-overlay-logo';
	}

	// Scroll top
	if ( wpex_header_logo_scroll_top() ) {
		$classes[] = 'wpex-scroll-top';
	}

	// Clear floats
	$classes[] = 'wpex-clr';

	// Sanitize classes
	$classes = array_map( 'esc_attr', $classes );

	// Apply filters for child theming
	$classes = apply_filters( 'wpex_header_logo_classes', $classes );

	// Turn classes into space seperated string
	$classes = implode( ' ', $classes );

	// Return classes
	return $classes;

}

/*-------------------------------------------------------------------------------*/
/* [ Logo ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns correct header logo height.
 *
 * @since 4.0
 */
function wpex_header_logo_img_height() {
	$height = apply_filters( 'logo_height', get_theme_mod( 'logo_height' ) ); // @todo rename to wpex_header_logo_height unless this is also a setting coming from WordPress
	return $height ? $height : '';  // can't be empty or 0
}

/**
 * Returns correct header logo width.
 *
 * @since 4.0
 */
function wpex_header_logo_img_width() {
	$width = apply_filters( 'logo_width', get_theme_mod( 'logo_width' ) );
	return $width ? $width : ''; // can't be empty or 0
}

/**
 * Returns correct heeader logo retina img.
 *
 * @since 4.0
 */
function wpex_header_logo_img_retina() {

	// Overlay header custom logo retina version
	if ( wpex_has_overlay_header() && wpex_overlay_header_logo_img() ) {
		$logo = wpex_overlay_header_logo_img_retina();
	}

	// Default retina logo
	else {
		$logo = wpex_get_translated_theme_mod( 'retina_logo' );
	}

	// Apply filters
	$logo = apply_filters( 'wpex_retina_logo_url', $logo ); // // @todo deprecate using apply_filters_deprecated
	$logo = apply_filters( 'wpex_header_logo_img_retina_url', $logo );

	// Set correct scheme and return
	return wpex_get_image_url( $logo );

}

/**
 * Returns correct heeader logo retina img height.
 *
 * @since 4.0
 */
function wpex_header_logo_img_retina_height() {

	$height = wpex_get_translated_theme_mod( 'logo_height' );

	if ( wpex_has_post_meta( 'wpex_overlay_header' ) && wpex_has_overlay_header() ) {
		$overlay_logo_height = wpex_overlay_header_logo_img_retina_height();
		if ( $overlay_logo_height ) {
			$height = $overlay_logo_height;
		}
	}

	$height = (int) apply_filters( 'wpex_retina_logo_height', $height );

	if ( $height ) {
		return $height;
	}

}

/**
 * Adds js for the retina logo.
 *
 * @since 1.1.0
 * @todo move into JS file instead of inline
 */
function wpex_header_logo_img_retina_js() {

	// Not needed in admin or if there is a custom header
	if ( is_admin() || wpex_has_custom_header() ) {
		return;
	}

	// Get retina logo url
	$logo_url = wpex_header_logo_img_retina();

	// Logo url is required
	if ( ! $logo_url ) {
		return;
	}

	// Get logo height
	$logo_height = wpex_header_logo_img_retina_height();

	// Logo height is required
	if ( empty( $logo_height ) ) {
		return;
	}

	?>

	<!-- Retina Logo -->
	<script>
		jQuery( function( $ ){
			if ( window.devicePixelRatio >= 2 ) {
				$( "#site-logo img.logo-img" ).attr( "src", "<?php echo esc_url( $logo_url ); ?>" ).css( "max-height","<?php echo absint( $logo_height ); ?>px" );
			}
		} );
	</script>

<?php }
add_action( 'wp_head', 'wpex_header_logo_img_retina_js' );

/*-------------------------------------------------------------------------------*/
/* [ Overlay/Transparent Header ]
/*-------------------------------------------------------------------------------*/

/**
 * Checks if the overlay header is enabled.
 *
 * @since 4.0
 */
function wpex_has_overlay_header() {

	// Return false if header is disabled @todo is this check really needed?
	if ( ! wpex_has_header() ) {
		return false;
	}

	// Check if enabled globally.
	$mod_check = get_theme_mod( 'overlay_header', false );

	// Define check based on theme_mod
	$check = $mod_check;

	// Get current post id
	$post_id = wpex_get_current_post_id();

	// Return true if enabled via the post meta
	// NOTE: The overlay header meta can still be filtered it's not hard set.
	if ( $post_id ) {
		$meta = get_post_meta( $post_id, 'wpex_overlay_header', true );
		if ( $meta ) {
			$check = wpex_validate_boolean( $meta );
		}
	}

	// Return false if not enabled globally and page is password protected and the page header is disabled
	if ( ! $mod_check && post_password_required() && ! wpex_has_page_header() ) {
		$check = false;
	}

	// Apply filters and return
	return (bool) apply_filters( 'wpex_has_overlay_header', $check );

}

/**
 * Returns overlay header style.
 *
 * @since 4.0
 */
function wpex_overlay_header_style() {

	// Define style based on theme_mod
	$style = get_theme_mod( 'overlay_header_style' );

	// Get overlay style based on meta option if hard enabled on the post.
	if ( wpex_has_overlay_header() && wpex_has_post_meta( 'wpex_overlay_header' ) ) {
		$meta = get_post_meta( wpex_get_current_post_id(), 'wpex_overlay_header_style', true );
		if ( $meta ) {
			$style = wp_strip_all_tags( trim( $meta ) );
		}
	}

	// White is the default/fallback style.
	$style = $style ? $style : 'white';

	// Apply filters and return
	return apply_filters( 'wpex_header_overlay_style', $style );
}

/**
 * Returns correct logo image for the overlay header image.
 *
 * @since 4.0
 */
function wpex_overlay_header_logo_img() {

	$logo = get_post_meta( wpex_get_current_post_id(), 'wpex_overlay_header_logo', true );

	// redux fallbacks
	if ( is_array( $logo ) ) {
		if ( ! empty( $logo['url'] ) ) {
			$logo = $logo['url'];
		} else {
			$logo = false;
		}
	}

	$url = apply_filters( 'wpex_header_overlay_logo', $logo ); // can be string or int

	if ( $url ) {
		return wpex_get_image_url( $url );
	}

}

/**
 * Returns correct retina logo image for the overlay header image.
 *
 * @since 4.0
 */
function wpex_overlay_header_logo_img_retina() {
	$logo = get_post_meta( wpex_get_current_post_id(), 'wpex_overlay_header_logo_retina', true );
	$logo = apply_filters( 'wpex_header_overlay_logo_retina', $logo );
	if ( $logo ) {
		return wpex_get_image_url( $logo );
	}
}

/**
 * Returns correct retina logo image height for the overlay header image.
 *
 * @since 4.0
 */
function wpex_overlay_header_logo_img_retina_height() {
	$meta = get_post_meta( wpex_get_current_post_id(), 'wpex_overlay_header_logo_retina_height', true );
	if ( $meta ) {
		return absint( $meta );
	}
}

/*-------------------------------------------------------------------------------*/
/* [ Sticky Header ]
/*-------------------------------------------------------------------------------*/

/**
 * Check if sticky header is enabled.
 *
 * @since 4.0
 */
function wpex_has_sticky_header() {

	// Disable in live editor
	if ( wpex_vc_is_inline() ) {
		return;
	}

	// Disabled by default
	$return = false;

	// Get current post id
	$post_id = wpex_get_current_post_id();

	// Check meta first it should override any filter!
	if ( $post_id && 'disable' == get_post_meta( $post_id, 'wpex_sticky_header', true ) ) {
		return false;
	}

	// Get header style
	$header_style = wpex_header_style( $post_id );

	// Sticky header for builder
	if ( 'builder' == $header_style ) {
		$return = get_theme_mod( 'header_builder_sticky', false );
	}

	// Standard sticky header
	else {

		// Return false if sticky header style is set to disabled
		if ( 'disabled' == wpex_sticky_header_style() ) {
			$return = false;
		}

		// Otherwise check if the current header style supports sticky.
		elseif ( in_array( $header_style, wpex_get_header_styles_with_sticky_support() ) ) {
			$return = true;
		}

	}

	// Apply filters and return
	return apply_filters( 'wpex_has_fixed_header', $return ); // @todo rename to wpex_has_sticky_header

}

/**
 * Get sticky header style.
 *
 * @since 4.0
 */
function wpex_sticky_header_style() {

	if ( 'builder' == wpex_header_style() ) {
		return 'standard'; // Header builder only supports standard
	}

	// Get default style from customizer
	$style = get_theme_mod( 'fixed_header_style', 'standard' );

	// If disabled in Customizer but enabled in meta set to "standard" style
	if ( 'disabled' == $style && 'enable' == get_post_meta( wpex_get_current_post_id(), 'wpex_sticky_header', true ) ) {
		$style = 'standard';
	}

	// Sanitize
	$style = $style ? $style : 'standard';

	// Return style
	return apply_filters( 'wpex_sticky_header_style', $style );

}

/**
 * Returns correct sticky header logo img.
 *
 * @since 4.0
 * @todo add as data attribute to prevent the need for added checks for builder header.
 */
function wpex_sticky_header_logo_img() {

	if ( 'builder' == wpex_header_style() ) {
		return ''; // Not needed for the sticky header builder
	}

	// Get fixed header logo from the Customizer
	$logo = get_theme_mod( 'fixed_header_logo' );

	// Set sticky logo to header logo for overlay header when custom overlay logo is set
	// This way you can have a white logo on overlay but the default on sticky.
	if ( empty( $logo )
		&& wpex_has_post_meta( 'wpex_overlay_header' ) // check if overlay header is force enabled via metadata
		&& wpex_overlay_header_logo_img() // check for custom overlay header logo
		&& wpex_has_overlay_header() // check if overlay header is enabled
	) {
		$header_logo = wpex_header_logo_img();
		if ( $header_logo ) {
			$logo = $header_logo;
		}
	}

	// Apply filters and return image URL
	return wpex_get_image_url( apply_filters( 'wpex_fixed_header_logo', $logo ) );

}

/**
 * Returns correct sticky header logo img retina version.
 *
 * @since 4.0
 */
function wpex_sticky_header_logo_img_retina() {
	return wpex_get_image_url( apply_filters( 'wpex_fixed_header_logo_retina', wpex_get_translated_theme_mod( 'fixed_header_logo_retina' ) ) );
}

/**
 * Returns correct sticky header logo img retina version.
 *
 * @since 4.0
 * @todo  deprecate as it's no longer used.
 */
function wpex_sticky_header_logo_img_retina_height() {

	// Get height and apply filters
	$height = apply_filters( 'wpex_fixed_header_logo_retina_height', get_theme_mod( 'fixed_header_logo_retina_height' ) );

	// Sanitize
	$height = $height ? intval( $height ) : null;

	// Return height
	return $height;

}

/**
 * Check if shrink sticky header is enabled.
 *
 * @since 4.0
 */
function wpex_has_shrink_sticky_header() {
	$bool = false;
	if ( wpex_has_sticky_header()
		&& in_array( wpex_header_style(), wpex_get_header_styles_with_sticky_support() )
		&& in_array( wpex_sticky_header_style(), array( 'shrink', 'shrink_animated' ) ) ) {
		$bool = true;
	}
	return apply_filters( 'wpex_has_shrink_sticky_header', $bool );
}


/**
 * Return correct starting position for the sticky header.
 *
 * @since 4.6.5
 */
function wpex_sticky_header_start_position() {
	$position = get_theme_mod( 'fixed_header_start_position' );
	if ( is_singular() ) {
		$meta_position = get_post_meta( get_the_ID(), 'fixed_header_start_position', true );
		if ( $meta_position ) {
			$position = $meta_position;
		}
	}
	return apply_filters( 'wpex_sticky_header_start_position', $position );
}

/*-------------------------------------------------------------------------------*/
/* [ Header Aside ]
/*-------------------------------------------------------------------------------*/

/**
 * Check if the current header supports aside content.
 *
 * @since 3.0.0
 */
function wpex_header_supports_aside( $header_style = '' ) {
	$bool = false;
	$header_style = $header_style ? $header_style : wpex_header_style();
	if ( in_array( $header_style, wpex_get_header_styles_with_aside_support() ) ) {
		$bool = true;
	}
	return apply_filters( 'wpex_header_supports_aside', $bool );
}

/**
 * Get Header Aside content.
 *
 * @since 4.0
 */
function wpex_header_aside_content() {

	// Get header aside content
	$content = wpex_get_translated_theme_mod( 'header_aside' );

	// Check if content is a page ID and get page content
	if ( is_numeric( $content ) ) {
		$post_id = wpex_parse_obj_id( $content, 'page' );
		$post = get_post( $post_id );
		if ( $post && ! is_wp_error( $post ) ) {
			$content = $post->post_content;
		}
	}

	// Apply filters and return content
	return apply_filters( 'wpex_header_aside_content', $content );

}
