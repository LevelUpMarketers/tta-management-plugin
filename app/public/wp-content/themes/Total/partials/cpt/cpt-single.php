<?php
/**
 * CPT single
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

// Custom template design
if ( $template_content = wpex_get_singular_template_content( get_post_type() ) ) {
	wpex_singular_template( $template_content );
	return;
}

?>

<div id="single-blocks" <?php wpex_cpt_single_blocks_class(); ?>>

	<?php
	// Get layout blocks
	$blocks = wpex_single_blocks();

	// Make sure we have blocks
	if ( ! empty( $blocks ) && is_array( $blocks ) ) :

		// Loop through blocks and get template part
		foreach ( $blocks as $block ) :

			// Media not needed for this position
			if ( 'media' == $block && wpex_get_custom_post_media_position() ) {
				continue;
			}

			// Callable output
			if ( 'the_content' != $block && is_callable( $block ) ) {

				call_user_func( $block );

			}

			// Get template part output
			else {

				get_template_part( 'partials/cpt/cpt-single-' . $block, get_post_type() );

			}

		endforeach;

	endif; ?>

</div>