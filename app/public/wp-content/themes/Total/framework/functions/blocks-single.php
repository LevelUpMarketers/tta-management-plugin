<?php
/**
 * Single blocks
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns array of blocks for the single post type layout.
 *
 * @since 3.2.0
 *
 * @todo Update so all post types to pass through the wpex_single_blocks filter.
 * @todo And update files so all post types use the wpex_single_blocks function.
 */
function wpex_single_blocks( $post_type = '' ) {

	// Define empty blocks array
	$blocks = array(
		'media',
		'title',
		'meta',
		'post-series',
		'content',
		'page-links',
		'share',
		'comments'
	);

	// Get post type if not defined
	$post_type = $post_type ? $post_type : get_post_type();

	// Get correct blocks by post type
	switch ( $post_type ) {
		case 'page':
			$blocks = get_theme_mod( 'page_composer', array( 'content' ) );
			break;
		case 'elementor_library':
			return array( 'content' );
			break;
		case 'post':
			return wpex_blog_single_layout_blocks();
			break;
		case 'portfolio':
			return wpex_portfolio_single_blocks();
			break;
		case 'staff':
			return wpex_staff_single_blocks();
			break;
		case 'testimonials':
			// Oh, so lonely...
			break;
		default:
			$blocks = get_theme_mod( $post_type . '_single_blocks', $blocks );
			break;
	}

	if ( $post_type && WPEX_PTU_ACTIVE ) {

		$ptu_check = wpex_get_ptu_type_mod( $post_type, 'single_blocks' );

		if ( $ptu_check ) {
			$blocks = $ptu_check;
		}

	}

	// Convert to array if not already (for customizer settings)
	if ( is_string( $blocks ) ) {
		$blocks = explode( ',', $blocks );
	}

	// Set keys equal to values for easier filter removal
	// MUST RUN BEFORE FILTERS !!!
	$blocks = $blocks ? array_combine( $blocks, $blocks ) : array();

	// Check toolset customizer settings
	if ( defined( 'TYPES_VERSION' ) && $blocks ) {
		foreach( $blocks as $block ) {
			if ( ! get_theme_mod( 'cpt_single_block_' . $block . '_enabled', true ) ) {
				unset( $blocks[$block] );
			}
		}
	}

	// Type specific filter //@todo remove extra filter and update snippets/docs
	$blocks = apply_filters( 'wpex_' . $post_type . '_single_blocks', $blocks, $post_type ); // This one is deprecated used filter defined below

	// Needed because of plugins using archives such as bbPress - @todo deprecate previous filter and use this one only?
	$blocks = apply_filters( 'wpex_single_blocks', $blocks, $post_type );

	// Sanitize & return blocks
	return $blocks;

}