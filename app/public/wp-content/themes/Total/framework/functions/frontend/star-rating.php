<?php
/**
 * Star rating functions.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get star rating.
 *
 * @since 4.0
 */
if ( ! function_exists( 'wpex_get_star_rating' ) ) {
	function wpex_get_star_rating( $rating = '', $post_id = '', $before = '', $after = '' ) {

		// Post id
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		// Define rating
		if ( ! $rating ) {
			$rating = get_post_meta( $post_id, 'wpex_post_rating', true );
		}

		// Return if no rating
		if ( empty( $rating ) ) {
			return false;
		}

		// Store original rating
		$og_rating = $rating;

		// Sanitize rating
		$rating = abs( $rating );

		$output = '';

		// Star fonts
		$full_star  = '<span class="ticon ticon-star" aria-hidden="true"></span>';
		$half_star  = '<span class="ticon ticon-star-half-empty" aria-hidden="true"></span>';
		$empty_star = '<span class="ticon ticon-star-empty" aria-hidden="true"></span>';

		// Max rating
		$max_rating = (int) apply_filters( 'wpex_star_rating_max_value', 5, $post_id );

		// Integers
		if ( ( is_numeric( $rating ) && ( intval( $rating ) == floatval( $rating ) ) ) ) {
			$output = str_repeat( $full_star, $rating );
			if ( $rating < $max_rating ) {
				$output .= str_repeat( $empty_star, $max_rating - $rating );
			}

		// Fractions
		} else {
			$rating = intval( $rating );
			$output = str_repeat( $full_star, $rating );
			$output .= $half_star;
			if ( $rating < $max_rating ) {
				$output .= str_repeat( $empty_star, ( $max_rating - 1 ) - $rating );
			}
		}

		// Add screen-reader text
		$output .= '<span class="screen-reader-text">' . esc_html__( 'Rating', 'total' ) . ': ' . esc_html( $og_rating ) . '</span>';

		// Apply filters
		$output = apply_filters( 'wpex_get_star_rating', $output, $rating );

		// Return output
		if ( $output ) {
			return $before . $output . $after;
		}

	}

}