<?php
/**
 * CPT single post series
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Make sure this post has the taxonomy associated with it
$taxonomy_objects = get_object_taxonomies( get_post_type(), 'names' );

// Return if post_series not in array
if ( is_wp_error( $taxonomy_objects ) || ! in_array( 'post_series', $taxonomy_objects ) ) {
	return;
}

// Get post series template part
wpex_get_template_part( 'post_series' );