<?php
/**
 * Custom excerpt functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get or generate excerpts
 *
 * @since 1.0.0
 */
function wpex_excerpt( $args ) {
	echo wpex_get_excerpt( $args );
}

/**
 * Get or generate excerpts
 *
 * @since 2.0.0
 */
function wpex_get_excerpt( $args = array() ) {

	// Fallback for old method
	if ( ! is_array( $args ) ) {
		$args = array(
			'length' => $args,
		);
	}

	// Setup default arguments
	$defaults = array(
		'post_id'              => '',
		'output'               => '',
		'length'               => '30',
		'before'               => '',
		'after'                => '',
		'trim_type'            => 'words',
 		'readmore'             => false,
		'readmore_link'        => '',
		'more'                 => '&hellip;',
		'custom_output'        => null,
		'context'              => '', // @todo deprecate context
		'custom_excerpts'      => true,
		'trim_custom_excerpts' => false,
		'custom_excerpts_more' => false,
		'post_password_check'  => true,
	);

	// Parse arguments
	$args = wp_parse_args( $args, $defaults );

	// Filter args
	$args = apply_filters( 'wpex_excerpt_args', $args, $args['context'] );

	// Extract args
	extract( $args );

	// Return custom output if defined as an argument
	// @todo Should we add a filter around the custom output to allow us to bail early?
	if ( $custom_output ) {
		return $custom_output;
	}

	// Sanitize data
	$excerpt = intval( $length );

	// If length is empty or zero return
	if ( empty( $length ) || '0' == $length || false == $length ) {
		return;
	}

	// Get global post
	$post = get_post( $post_id );

	// Display password protected notice
	if ( $args['post_password_check'] && $post->post_password ) {

		return '<p>' . esc_html__( 'This is a password protected post.', 'total' ) . '</p>';

	}

	// Get post data
	$post_id      = $post->ID;
	$post_content = $post->post_content;
	$post_excerpt = $custom_excerpts ? $post->post_excerpt : '';

	// Return the content including more tag
	// Note: We can NOT display the full post if we are also viewing the post or we will get a memory error.
	if ( '9999' == $length && $post_id != wpex_get_current_post_id() ) {
		return apply_filters( 'the_content', get_the_content( '', '&hellip;' ) );
	}

	// Return the content excluding more tag
	if ( '-1' == $length ) {
		return apply_filters( 'the_content', $post_content );
	}

	// Custom Excerpts
	if ( $post_excerpt ) {

		// Apply core filters
		$post_excerpt = apply_filters( 'get_the_excerpt', $post_excerpt );

		if ( $post_excerpt ) {
			if ( $trim_custom_excerpts && '-1' != $length ) {
				$get_excerpt_length = str_word_count( wp_strip_all_tags( $post_excerpt ) );
				if ( $get_excerpt_length > $length ) {
					$post_excerpt = wp_trim_words( $post_excerpt, absint( $length ) );
				}
			}

			// Get output
			$output = do_shortcode( $post_excerpt );

			// Mainly for testimonials slider
			if ( $custom_excerpts_more ) {
				$output .= $args['more'];
			}

		}

	// Create Excerpt
	} else {

		// Check for text shortcode in post
		if ( strpos( $post_content, '[vc_column_text' ) !== false ) {
			//$pattern = '/\[vc_column_text[^\]]*](.*)\[\/vc_column_text[^\]]*]/uis';
			$pattern = '{\[vc_column_text.*?\](.*?)\[/vc_column_text\]}is';
			preg_match( $pattern, $post_content, $matches );
			if ( isset( $matches[1] ) ) {
				$content = strip_shortcodes( $matches[1] );
			} else {
				$content = strip_shortcodes( $post_content );
			}
		}

		// No text shortcode so lets strip out shortcodes and return the content
		else {
			$content = strip_shortcodes( $post_content );
		}

		// Trim the content we found
		if ( 'words' == $trim_type ) {
			$excerpt = wp_trim_words( $content, absint( $length ), $more );
		} else {
			if ( function_exists( 'mb_strimwidth' ) ) {
				$content = wp_strip_all_tags( $content );
				$content = trim( preg_replace( "/[\n\r\t ]+/", ' ', $content ), ' ' );
				$excerpt = mb_strimwidth( $content, 0, $length, $more );
			} else {
				$excerpt = wp_trim_words( $content, absint( $length ), $more );
			}
		}

		// Add excerpt to output
		if ( $excerpt ) {
			$output .= '<p>'. trim( $excerpt ) .'</p>'; // Already sanitized
		}

	}

	// Add readmore link to output if enabled
	if ( $readmore ) {

		$read_more_text = isset( $args['read_more_text'] ) ? $args['read_more_text'] : esc_html__( 'Read more', 'total' );
		$output .= '<a href="' . get_permalink( $post_id ) . '" class="wpex-readmore theme-button">' . esc_html( $read_more_text ) . ' <span class="wpex-readmore-rarr">&rarr;</span></a>';

	}

	// Apply filters
	$output = apply_filters( 'wpex_excerpt_output', $output, $args );

	// Return output
	if ( $output ) {
		return $before . $output . $after;
	}

}

/**
 * Custom excerpt length for standard posts.
 *
 * @since 1.0.0
 */
function wpex_excerpt_length() {

	// Theme panel length setting
	$length = get_theme_mod( 'blog_excerpt_length', '40' );

	// Taxonomy setting
	if ( is_category() ) {

		// Get taxonomy meta
		$term       = get_query_var( 'cat' );
		$term_data  = get_option( "category_$term" );
		if ( ! empty( $term_data['wpex_term_excerpt_length'] ) ) {
			$length = $term_data['wpex_term_excerpt_length'];
		}
	}

	// Return length and add filter for child theme mods
	return intval( apply_filters( 'wpex_excerpt_length', $length ) );

}

/**
 * Change default read more style
 *
 * @since 1.0.0
 */
function wpex_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'wpex_excerpt_more', 10 );

/**
 * Change default excerpt length
 *
 * @since 1.0.0
 * @todo Do we really need this?
 */
function wpex_custom_excerpt_length( $length ) {
	if ( ! defined( 'ELEMENTOR_VERSION' )  ) {
		$length = '40';
	}
	return $length;
}
add_filter( 'excerpt_length', 'wpex_custom_excerpt_length', 999 );

/**
 * Prevent Page Scroll When Clicking the More Link
 * http://codex.wordpress.org/Customizing_the_Read_More
 *
 * @since 1.0.0
 */
function wpex_remove_more_link_scroll( $link ) {
	$link = preg_replace( '|#more-[0-9]+|', '', $link );
	return $link;
}
add_filter( 'the_content_more_link', 'wpex_remove_more_link_scroll' );