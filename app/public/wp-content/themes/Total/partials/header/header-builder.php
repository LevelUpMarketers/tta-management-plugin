<?php
/**
 * Header Builder Content
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Live builder
if ( wpex_is_header_builder_page() && ( wpex_vc_is_inline() || wpex_elementor_is_preview_mode() ) ) :

	while ( have_posts() ) : the_post();

		the_content();

	endwhile;

// Front end
else :

	$id = wpex_header_builder_id();

	if ( $id ) {

		if ( 'elementor_library' == get_post_type( $id ) && class_exists( 'Elementor\Frontend' ) ) {
			echo wpex_get_elementor_content_for_display( $id );
		} else {
			$content = wpex_parse_vc_content( get_post_field( 'post_content', $id ) ); // remove weird p tags and extra code
			$content = wp_kses_post( $content ); // security sanitization
			$content = do_shortcode( $content ); // parse shortcodes
			echo apply_filters( 'wpex_header_builder_content', $content ); // apply filters and return content
		}

	}

endif;