<?php
/**
 * Portfolio single layout
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

// Custom template
if ( $template_content = wpex_get_singular_template_content( 'portfolio' ) ) {
	wpex_singular_template( $template_content );
	return;
}

?>

<div id="single-blocks" <?php wpex_portfolio_single_blocks_class(); ?>>

	<?php
	// Single layout blocks
	$blocks = wpex_portfolio_single_blocks();

	// Make sure we have blocks
	if ( $blocks && is_array( $blocks ) ) :

		// Loop through blocks and get template part
		foreach ( $blocks as $block ) :

			// Callable output
			if ( 'the_content' != $block && is_callable( $block ) ) {

				call_user_func( $block );

			}

			// Template part output
			else {

				get_template_part( 'partials/portfolio/portfolio-single-' . $block );

			}

		endforeach;

	endif; ?>

</div>