<?php
/**
 * Footer Callout functions.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if callout is enabled.
 *
 * @since 4.0
 */
function wpex_has_footer_callout() {

	// Check if enabled by default
	if ( wpex_elementor_location_exists( 'footer_callout' ) ) {
		$check = true;
	} else {
		$check = get_theme_mod( 'callout', true );
	}

	// Apply filters before meta check so meta always overrides filters
	$check = apply_filters( 'wpex_callout_enabled', $check );

	// Get current post ID
	$post_id = wpex_get_current_post_id();

	// Check page settings
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_disable_footer_callout', true ) ) {
		if ( 'on' == $meta ) {
			$check = false;
		} elseif ( 'enable' == $meta ) {
			$check = true;
		}
	}

	// Return bool value
	return $check;

}

/**
 * Returns callout content.
 *
 * @since 4.0
 */
function wpex_footer_callout_content() {

	// Default content var
	$content = '';

	// Get post ID
	$post_id = wpex_get_current_post_id();

	// Get post meta content
	$meta = $post_id ? get_post_meta( $post_id, 'wpex_callout_text', true ) : '';

	// Return content defined in meta
	if ( $meta && '<p><br data-mce-bogus="1"></p>' != $meta && '<p>&nbsp;<br></p>' != $meta ) {
		$content = $meta;
	}

	// Return Customzier content
	else {

		// Get content from theme mod
		$content = wpex_get_translated_theme_mod( 'callout_text', 'I am the footer call-to-action block, here you can add some relevant/important information about your company or product. I can be disabled in the Customizer.' );

		// Apply filters if meta is not defined since meta should always override
		$content = apply_filters( 'wpex_get_footer_callout_content', $content );

	}

	// If page content is numeric and it's a post return the post content
	if ( $content && is_numeric( $content ) ) {
		$post_id = wpex_parse_obj_id( $content, get_post_type( $content ) );
		$post    = get_post( $post_id );
		if ( $post && ! is_wp_error( $post ) ) {
			$content = wpex_parse_vc_content( $post->post_content );
		}
	}

	// Return content
	echo do_shortcode( wp_kses_post( $content ) );

}

/**
 * Check if the footer callout has content.
 *
 * @since 5.0
 */
function wpex_has_footer_callout_content( ) {
	return (bool) wpex_callout_content();
}

/**
 * Check if the footer callout has a button.
 *
 * @since 5.0
 */
function wpex_has_footer_callout_button( ) {
	if ( wpex_footer_callout_button_link() && wpex_footer_callout_button_text() ) {
		return true;
	}
	return false;
}

/**
 * Get footer callout button link.
 *
 * @since 5.0
 */
function wpex_footer_callout_button_link() {
	$post_id = wpex_get_current_post_id();
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_callout_link', true ) ) {
		$link = $meta;
	} else {
		$link = wpex_translate_theme_mod( 'callout_link', get_theme_mod( 'callout_link', '#' ) );
	}
	return esc_url( apply_filters( 'wpex_footer_callout_button_link', $link ) );
}

/**
 * Get footer callout button text.
 *
 * @since 5.0
 */
function wpex_footer_callout_button_text() {

	$post_id = wpex_get_current_post_id();

	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_callout_link_txt', true ) ) {
		$text = $meta;
	} else {
		$text = get_theme_mod( 'callout_link_txt', 'Get In Touch' );
		$text = wpex_translate_theme_mod( 'callout_link_txt', $text );
	}

	$text = apply_filters( 'wpex_footer_callout_button_text', $text );

	return $text;
}

/**
 * Get footer callout button icon.
 *
 * @since 5.0
 */
function wpex_footer_callout_button_icon() {
	$icon = apply_filters( 'wpex_footer_callout_button_icon', get_theme_mod( 'callout_button_icon' ) );
	if ( 'none' !== $icon ) {
		return $icon;
	}
}

/**
 * Footer Callout wrap class.
 *
 * @since 5.0
 */
function wpex_footer_callout_wrap_class() {

	$classes = array();

	if ( wpex_has_footer_callout_content() ) {
		$classes[] = 'wpex-bg-gray-100';
		$classes[] = 'wpex-py-30';
		$classes[] = 'wpex-border-solid';
		$classes[] = 'wpex-border-gray-200';
		$classes[] = 'wpex-border-y';
		$classes[] = 'wpex-text-gray-700';
	} else {
		$classes[] = 'btn-only';
	}

	$visibility = get_theme_mod( 'callout_visibility' );

	if ( $visibility && 'always-visible' !== $visibility ) {
		$classes[] = $visibility;
	}

	if ( get_theme_mod( 'footer_callout_bg_img' ) && $bg_style = get_theme_mod( 'footer_callout_bg_img_style' ) ) {
		$classes[] = 'bg-' . sanitize_html_class( $bg_style );
	}

	$classes = (array) apply_filters( 'wpex_footer_callout_wrap_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Footer Callout class.
 *
 * @since 5.0
 */
function wpex_footer_callout_class() {

	$classes = array();

	if ( wpex_has_footer_callout_content() ) {

		$classes[] = 'container';

		if ( wpex_has_footer_callout_button() ) {

			$bk = wpex_get_mod( 'footer_callout_breakpoint', 'md', true );
			$bk = sanitize_html_class( $bk );

			$classes[] = 'wpex-' . $bk . '-flex';
			$classes[] = 'wpex-' . $bk . '-items-center';

		}

	}

	$classes = (array) apply_filters( 'wpex_footer_callout_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Footer Callout content class.
 *
 * @since 5.0
 */
function wpex_footer_callout_left_class() {

	$classes = array(
		'footer-callout-content',
		'wpex-text-xl',
	);

	if ( wpex_has_footer_callout_button() ) {

		$bk = wpex_get_mod( 'footer_callout_breakpoint', 'md', true );
		$bk_escaped = sanitize_html_class( $bk );

		$classes[] = 'wpex-' . $bk_escaped . '-flex-grow';
		$classes[] = 'wpex-' . $bk_escaped . '-w-75';

	}

	$classes = (array) apply_filters( 'wpex_footer_callout_left_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Footer Callout button class.
 *
 * @since 5.0
 */
function wpex_footer_callout_right_class() {

	$classes = array(
		'footer-callout-button',
		'wpex-mt-20',
		'wpex-clr',
	);

	$bk = sanitize_html_class( wpex_get_mod( 'footer_callout_breakpoint', 'md', true ) );

	if ( $bk ) {
		$bk_escaped = sanitize_html_class( $bk );
		$classes[] = 'wpex-' . $bk_escaped . '-w-25';
		$classes[] = 'wpex-' . $bk_escaped . '-pl-20';
		$classes[] = 'wpex-' . $bk_escaped . '-mt-0';
	}

	$classes = (array) apply_filters( 'wpex_footer_callout_right_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Footer Callout button.
 *
 * @since 5.0
 */
function wpex_footer_callout_button() {

	$button_style = get_theme_mod( 'callout_button_style' );
	$button_color = get_theme_mod( 'callout_button_color' );

	$classes = array( wpex_get_button_classes( $button_style, $button_color ) );
	$classes[] = 'wpex-block';
	$classes[] = 'wpex-text-center';
	$classes[] = 'wpex-py-15';
	$classes[] = 'wpex-px-20';
	$classes[] = 'wpex-m-0';

	if ( wpex_has_footer_callout_content() ) {
		$classes[] = 'wpex-text-lg';
	} else {
		$classes[] = 'wpex-text-xl';
		$classes[] = 'wpex-rounded-0';
	}

	$classes = apply_filters( 'wpex_footer_callout_button_class', $classes );

	// Define callout button attributes
	$attrs = array(
		'href'   => wpex_footer_callout_button_link(),
		'class'  => $classes,
		'target' => get_theme_mod( 'callout_button_target', 'blank' ),
		'rel'    => get_theme_mod( 'callout_button_rel' ),
	);

	$attrs = apply_filters( 'wpex_callout_button_attributes', $attrs ); // @todo deprecate
	$attrs = apply_filters( 'wpex_footer_callout_button_attributes', $attrs );

	$text = wpex_footer_callout_button_text();
	$text_escaped = wp_kses_post( $text );

	$icon = wpex_footer_callout_button_icon();

	if ( $icon ) {

		$icon_position = get_theme_mod( 'callout_button_icon_position' );

		switch ( $icon_position ) {
			case 'before_text':
				$text_escaped = wpex_get_theme_icon_html( $icon, 'theme-button-icon-left' ) . $text_escaped;
				break;
			default:
				$text_escaped = $text_escaped . wpex_get_theme_icon_html( $icon, 'theme-button-icon-right' );
				break;
		}

	}

	echo wpex_parse_html( 'a', $attrs, $text_escaped );

}

