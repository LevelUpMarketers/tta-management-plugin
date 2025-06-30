<?php
/**
 * Entry blocks
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns array of blocks for cpt entries.
 *
 * @since 3.2.0
 */
function wpex_entry_blocks() {

	// Default blocks
	$blocks = array(
		'media'    => 'media',
		'title'    => 'title',
		'meta'     => 'meta',
		'content'  => 'content',
		'readmore' => 'readmore',
	);

	// Get post type
	$post_type = get_post_type();

	// Get post type based options
	if ( $post_type ) {

		$blocks = get_theme_mod( $post_type . '_entry_blocks', $blocks );

		if ( WPEX_PTU_ACTIVE ) {

			$ptu_check = wpex_get_ptu_type_mod( $post_type, 'entry_blocks' );

			if ( $ptu_check ) {
				$blocks = $ptu_check;
			}

		}

	}

	// Make sure blocks are an array
	if ( is_string( $blocks ) ) {
		$blocks = explode( ',', $blocks );
	}

	// Apply filters
	$blocks = apply_filters( 'wpex_' . $post_type . '_entry_blocks', $blocks, $post_type ); // @todo deprecate this older filter?
	$blocks = apply_filters( 'wpex_entry_blocks', $blocks, $post_type );

	// Return blocks
	return $blocks;

}