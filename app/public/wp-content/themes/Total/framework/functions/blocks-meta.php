<?php
/**
 * Meta blocks
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns array of blocks for the entry meta.
 *
 * @since 3.6.0
 */
function wpex_meta_blocks() {

	$blocks = array(
		'date',
		'author',
		'categories',
		'comments'
	);

	$instance = wpex_get_loop_instance();

	if ( WPEX_PTU_ACTIVE ) {

		if ( is_singular() && is_main_query() ) {

			$ptu_check = wpex_get_ptu_type_mod( get_post_type(), 'single_meta_blocks' );

			if ( $ptu_check ) {
				$blocks = $ptu_check;
			}

		} else {

			$ptu_check = wpex_get_ptu_type_mod( get_post_type(), 'entry_meta_blocks' );

			if ( $ptu_check ) {
				$blocks = $ptu_check;
			}

		}

	}

	return apply_filters( 'wpex_meta_blocks', $blocks, get_post_type() );
}
