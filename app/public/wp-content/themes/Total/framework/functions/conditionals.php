<?php
/**
 * Conditonal functions.
 * These functions load before anything else in the main theme class so they can be used
 * early on in pretty much any hook.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if WooCommerce is enabled.
 *
 * @since 4.9.5
 */
function wpex_is_woocommerce_active() {
	return (bool) WPEX_WOOCOMMERCE_ACTIVE;
}

/**
 * Check if WPBakery is enabled.
 *
 * @since 4.9.5
 * @todo rename to wpex_is_wpbakery_active
 */
function wpex_is_wpbakery_enabled() {
	return (bool) WPEX_VC_ACTIVE;
}

/**
 * Checks if the Total Portfolio post type is enabled.
 *
 * @since 4.9
 */
function wpex_is_total_portfolio_enabled() {
	if ( defined( 'WPEX_PORTFOLIO_IS_ACTIVE' ) && WPEX_PORTFOLIO_IS_ACTIVE ) {
		return true;
	}
}

/**
 * Checks if the Total Staff post type is enabled.
 *
 * @since 4.9
 */
function wpex_is_total_staff_enabled() {
	if ( defined( 'WPEX_STAFF_IS_ACTIVE' ) && WPEX_STAFF_IS_ACTIVE ) {
		return true;
	}
}

/**
 * Checks if the Total Testimonials post type is enabled.
 *
 * @since 4.9
 */
function wpex_is_total_testimonials_enabled() {
	if ( defined( 'WPEX_TESTIMONIALS_IS_ACTIVE' ) && WPEX_TESTIMONIALS_IS_ACTIVE ) {
		return true;
	}
}

/**
 * Check if current site is dev environment.
 *
 * @since 4.5
 */
function wpex_is_dev_environment() {
	$site = site_url();

	$chunks = explode( '.', $site );

	if ( 1 === count( $chunks ) ) {
		return true;
	}

	if ( in_array( end( $chunks ), array(
		'local',
		'dev',
		'wp',
		'test',
		'example',
		'localhost',
		'invalid',
		'staging',
	) ) ) {
		return true;
	}

	if ( preg_match( '/^[0-9\.]+$/', $site ) ) {
		return true;
	}

	return false;

}

/**
 * Check if a specific customizer section is enabled.
 *
 * @since 4.9.5
 */
function wpex_has_customizer_panel( $section ) {
	$disabled_panels = (array) get_option( 'wpex_disabled_customizer_panels' );
	return ( $disabled_panels && in_array( $section, $disabled_panels ) ) ? false : true;
}

/**
 * Check if responsiveness is enabled.
 *
 * @since 4.0
 */
function wpex_is_layout_responsive() {
	return (bool) apply_filters( 'wpex_is_layout_responsive', true );
}

/**
 * Check if the post edit links should display on the page.
 *
 * @since 2.0.0
 * @todo rename to wpex_has_retina_support
 */
function wpex_is_retina_enabled() {
	if ( get_theme_mod( 'image_resizing', true ) && get_theme_mod( 'retina', false ) ) {
		return true;
	}
	return false;
}

/**
 * Check if metadata exists.
 *
 * @since 5.0
 */
function wpex_has_post_meta( $key = '' ) {
	return wpex_validate_boolean( get_post_meta( wpex_get_current_post_id(), $key, true ) );
}

/**
 * Check if google services are disabled.
 *
 * @since 3.2.0
 */
function wpex_disable_google_services() {
	return apply_filters( 'wpex_disable_google_services', get_theme_mod( 'disable_gs', false ) );
}

/**
 * Check if a post has media (used for entry classes)
 *
 * @since 4.4.1
 * @todo rename to wpex_has_post_media
 */
function wpex_post_has_media( $post_id = '', $deprecated = false ) {

	$post_id = $post_id ? $post_id : get_the_ID();
	$type = get_post_type( $post_id );

	$check = false;

	switch ( $type ) {
		case 'post':
			$media_type = wpex_blog_entry_media_type();
			break;
		case 'portfolio':
			$media_type = wpex_portfolio_entry_media_type();
			break;
		case 'staff':
			$media_type = wpex_staff_entry_media_type();
			break;
		default:
			$media_type = wpex_cpt_entry_media_type();
			break;
	}

	if ( $media_type ) {
		$check = true;
	}

	return (bool) apply_filters( 'wpex_post_has_media', $check, $post_id );

}

/**
 * Check if a post has a redirection.
 *
 * @since 5.0
 */
function wpex_has_post_redirection( $post_id = '' ) {
	return (bool) wpex_get_custom_permalink( $post_id );
}

/**
 * Check if the post edit links should display on the page.
 *
 * @since 2.0.0
 */
function wpex_has_post_edit() {

	if ( ! get_theme_mod( 'edit_post_link_enable', true ) ) {
		return false; // return if disabled completely via theme settings
	}

	// If not singular or in front-end editor we can bail completely
	if ( ! is_singular() || wpex_vc_is_inline() ) {
		return false;
	}

	// Not needed for these woo commerce pages
	// @todo move to WooCommerce config?
	if ( WPEX_WOOCOMMERCE_ACTIVE && ( is_cart() || is_checkout() ) ) {
		return false;
	}

	// Apply filters and return
	return apply_filters( 'wpex_has_post_edit', true );

}

/**
 * Check if the next/previous links should display.
 *
 * @since 2.0.0
 */
function wpex_has_next_prev() {

	// Check if it's singular
	$singular = is_singular();

	// Only for singular
	if ( ! $singular ) {
		return;
	}

	// Get post type
	$post_type = get_post_type();
	$post_type = ( 'post' == $post_type ) ? 'blog' : $post_type;

	// Not needed for pages or attachments
	if ( in_array( $post_type, array( 'page', 'attachment', 'templatera', 'elementor_library' ) ) ) {
		return false;
	}

	// Global check
	$check = get_theme_mod( 'next_prev', true );

	// WooCommerce check
	if ( wpex_is_woocommerce_active() && is_singular( 'product' ) && is_woocommerce() ) {
		$check =  get_theme_mod( 'woo_next_prev', true );
	}

	// Check if enabled for specific post type
	$check = get_theme_mod( $post_type . '_next_prev', $check );

	if ( WPEX_PTU_ACTIVE ) {

		$ptu_check = wpex_get_ptu_type_mod( $post_type, 'next_prev' );

		if ( isset( $ptu_check ) ) {
			$check = wp_validate_boolean( $ptu_check );
		}

	}

	// Apply filters & return
	return apply_filters( 'wpex_has_next_prev', $check, $post_type );

}

/**
 * Check if the readmore button should display.
 *
 * @since 2.1.2
 */
function wpex_has_readmore() {
	$check = true;

	if ( post_password_required() ) {
		$check = false;
	} elseif ( 'post' == get_post_type()
		&& ! strpos( get_the_content(), 'more-link' )
		&& ! get_theme_mod( 'blog_exceprt', true ) ) {
		$check = false;
	}

	return apply_filters( 'wpex_has_readmore', $check );
}

/**
 * Check if the breadcrumbs is enabled.
 *
 * @since 3.6.0
 */
function wpex_has_breadcrumbs() {

	// Check default value in Customizer
	$check = get_theme_mod( 'breadcrumbs', true );

	// Get current post ID
	$post_id = wpex_get_current_post_id();

	// Check page settings
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_disable_breadcrumbs', true ) ) {

		if ( 'on' == $meta ) {
			$check = false;
		} elseif ( 'enable' == $meta ) {
			$check = true;
		}

	}

	// Disable on homepage
	if ( is_front_page() ) {
		$check = false;
	}

	// Apply filters and return
	return (bool) apply_filters( 'wpex_has_breadcrumbs', $check );

}

/**
 * Check if current page has a sidebar.
 *
 * @since 4.3
 */
function wpex_has_sidebar( $post_id = '' ) {
	$check = in_array( wpex_content_area_layout( $post_id ), array( 'left-sidebar', 'right-sidebar' ) ) ? true : false;
	return (bool) apply_filters( 'wpex_has_sidebar', $check );
}

/**
 * Returns site frame border width.
 *
 * @since 4.3
 */
function wpex_has_site_frame_border() {
	return get_theme_mod( 'site_frame_border', false ) ? true : false;
}

/**
 * Check if Google Services are enabled.
 *
 * @since 4.3
 */
function wpex_has_google_services_support() {
	return wpex_disable_google_services() ? false : true;
}

/**
 * Check if gallery lightbox is enabled.
 *
 * @since 1.0.0
 */
function wpex_gallery_is_lightbox_enabled( $post_id = '' ) {
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();
	return ( 'on' == get_post_meta( $post_id, '_easy_image_gallery_link_images', true ) ) ?  true : false;
}

/**
 * Check if the current post has a dynamic template.
 *
 * @since 4.9
 */
function wpex_post_has_dynamic_template() {
	if ( wpex_get_singular_template_content() ) {
		return true;
	}
}

/**
 * Check if the custom wp gallery output is supported.
 *
 * @since 4.9
 * @todo rename to wpex_has_custom_wp_gallery
 */
function wpex_custom_wp_gallery_supported() {
	$check = WPEX_ELEMENTOR_ACTIVE ? false : get_theme_mod( 'custom_wp_gallery_enable', true );
	return (bool) apply_filters( 'wpex_custom_wp_gallery_supported', $check );
}

/**
 * Checks if the current post is part of a post series.
 *
 * @since 2.0.0
 */
function wpex_is_post_in_series() {

	if ( ! taxonomy_exists( 'post_series' ) ) {
		return false;
	}

	$terms = get_the_terms( get_the_id(), 'post_series' );

	if ( $terms ) {
		return true;
	}

	return false;

}

/**
 * Checks if on a theme portfolio taxonomy archive.
 *
 * @since 1.6.0
 */
function wpex_is_portfolio_tax() {
	$check = false;

	if ( ( is_tax( 'portfolio_category' ) || is_tax( 'portfolio_tag' ) ) && ! is_search() ) {
		$check = true;
	}

	return (bool) apply_filters( 'wpex_is_portfolio_tax', $check );
}

/**
 * Checks if on a theme staff taxonomy archive.
 *
 * @since 1.6.0
 */
function wpex_is_staff_tax() {
	$check = false;

	if ( ! is_search() && ( is_tax( 'staff_category' ) || is_tax( 'staff_tag' ) ) ) {
		$check = true;
	}

	return (bool) apply_filters( 'wpex_is_staff_tax', $check );
}

/**
 * Checks if on a theme testimonials taxonomy archive.
 *
 * @since 1.6.0
 */
function wpex_is_testimonials_tax() {
	$check = false;

	if ( ! is_search() && ( is_tax( 'testimonials_category' ) || is_tax( 'testimonials_tag' ) ) ) {
		$check = true;
	}

	return (bool) apply_filters( 'wpex_is_testimonials_tax', $check );
}

/**
 * Check if a post has terms/categories.
 *
 * This function is used for the next and previous posts so if a post is in a category it
 * will display next and previous posts from the same category.
 *
 * @since 1.0.0
 * @todo rename to wpex_has_post_terms()
 */
if ( ! function_exists( 'wpex_post_has_terms' ) ) {
	function wpex_post_has_terms( $post_id = '', $post_type = '' ) {
		$check = has_term( '', wpex_get_post_primary_taxonomy() );
		return (bool) apply_filters( 'wpex_post_has_terms', $check );
	}
}

/**
 * Check if the theme has custom WooCommerce mods.
 *
 * @since 4.1
 * @todo rename function and filter to wpex_has_woocommerce_integration
 */
function wpex_has_woo_mods() {
	$check = get_theme_mod( 'woocommerce_integration', true );
	return (bool) apply_filters( 'wpex_has_woo_mods', $check );
}

/**
 * Check if the current WooCommerce version is supported
 *
 * @since 4.1
 */
function wpex_woo_version_supported() {
	$check = false;

	if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
		$check = true;
	}

	return (bool) apply_filters( 'wpex_woo_version_supported', $check );
}

/**
 * Checks if on the WooCommerce shop page.
 *
 * @since 1.6.0
 */
function wpex_is_woo_shop() {
	if ( WPEX_WOOCOMMERCE_ACTIVE && is_shop() ) {
		return true;
	}
	return false;
}

/**
 * Check if WooCommerce default output should be disabled
 *
 * @since 4.6.5
 * @todo rename to wpex_has_woo_archive_loop
 */
function wpex_woo_archive_has_loop() {
	$check = true;

	if ( get_theme_mod( 'woo_shop_disable_default_output', false ) && wpex_is_woo_shop() && ! is_search() ) {
		$check = false;
	}

	return (bool) apply_filters( 'wpex_woo_archive_has_loop', $check );
}

/**
 * Checks if on a WooCommerce tax.
 *
 * @since 1.6.0
 */
if ( ! function_exists( 'wpex_is_woo_tax' ) ) {
	function wpex_is_woo_tax() {
		if ( ! wpex_is_woocommerce_active() ) {
			return false;
		}

		$check = false;

		if ( is_product_category() || is_product_tag() ) {
			$check = true;
		}

		return apply_filters( 'wpex_is_woo_tax', $check );
	}
}

/**
 * Checks if on singular WooCommerce product post.
 *
 * @since 1.6.0
 */
function wpex_is_woo_single() {
	if ( ! WPEX_WOOCOMMERCE_ACTIVE ) {
		return false;
	} elseif ( is_woocommerce() && is_singular( 'product' ) ) {
		return true;
	}
}

/**
 * Check if product is in stock.
 *
 * @since 1.0.0
 */
function wpex_woo_product_instock() {
	if ( 'yes' === get_option( 'woocommerce_manage_stock', 'yes' ) ) {
		global $product;
		if ( ! $product || ( $product && $product->is_in_stock() ) ) {
			return true;
		}
		return false;
	}
	return true; // always true if manage stock is disabled
}

/**
 * Check if current user has social profiles defined.
 *
 * @since 1.0.0
 */
function wpex_author_has_social( $user = '' ) {

	if ( ! $user ) {
		global $post;
		$user = ! empty( $post->post_author ) ? $post->post_author : '';
	}

	if ( ! $user ) {
		return;
	}

	$profiles = wpex_get_user_social_profile_settings_array();

	foreach( $profiles as $k => $v ) {
		if ( get_the_author_meta( 'wpex_' . $k, $user ) ) {
			return true; // we only need 1 to validate
		}
	}

}

/**
 * Check if VC theme mode is enabled.
 *
 * @since 4.9
 */
function wpex_vc_theme_mode_check() {
	$theme_mode = get_theme_mod( 'visual_composer_theme_mode', true );
	if ( function_exists( 'vc_license' ) && vc_license()->isActivated() ) {
		$theme_mode = false; // disable if VC is active
	}
	return $theme_mode;
}

/**
 * Check if the theme has custom VC mods + extensions.
 *
 * @since 4.1
 */
function wpex_has_vc_mods() {
	return (bool) apply_filters( 'wpex_has_vc_mods', true );
}

/**
 * Check if the current visual composer plugin is active and supported.
 *
 * @since 3.3.4
 */
function wpex_vc_is_supported() {
	if ( defined( 'WPB_VC_VERSION' ) && version_compare( WPB_VC_VERSION, WPEX_VC_SUPPORTED_VERSION, '>=' ) ) {
		return true;
	}
}

/**
 * Check if a specific post is using the Visual Composer.
 *
 * @since 1.0.0
 */
function wpex_has_post_wpbakery_content( $post_id = null ) {

	if ( ! WPEX_VC_ACTIVE ) {
		return false;
	}

	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	if ( ! $post_id ) {
		return false;
	}

	$post_content = get_post_field( 'post_content', $post_id );

	if ( $post_content && false !== strpos( $post_content, 'vc_row' ) ) {
		return true;
	}

	return false;

}

/**
 * Check if user is currently editing in front-end editor mode.
 *
 * @since 1.0.0
 */
function wpex_vc_is_inline() {
	if ( function_exists( 'vc_is_inline' ) ) {
		return vc_is_inline();
	}
}

/**
 * Check if an elementor theme builder location exists for a specific location.
 *
 * @since 4.9.5
 */
function wpex_elementor_location_exists( $location ) {
	if ( function_exists( 'elementor_location_exits' ) && elementor_location_exits( $location, true ) ) {
		return true;
	}
}

/**
 * Check if user is currently editing in front-end editor mode.
 *
 * Note: This function exists only at init 0
 *
 * @since 4.8.3
 */
function wpex_elementor_is_preview_mode() {
	if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
		return true;
	}
}

/**
 * Check if an attachment exists.
 *
 * @since 4.4.1
 */
function wpex_attachment_exists( $attachment = '' ) {
	if ( 'attachment' == get_post_type( $attachment ) ) {
		return $attachment;
	}
}
