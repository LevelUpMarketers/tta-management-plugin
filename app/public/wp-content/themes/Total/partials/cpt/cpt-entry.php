<?php
/**
 * CTP entry
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

?>

<article id="post-<?php the_ID(); ?>" <?php wpex_cpt_entry_class(); ?>>
	<?php if ( ! wpex_cpt_entry_card() ) { ?>
		<div <?php wpex_cpt_entry_inner_class(); ?>>
			<?php
			// Get layout blocks
			$blocks = wpex_entry_blocks();

			// Make sure blocks aren't empty and it's an array
			if ( ! empty( $blocks ) && is_array( $blocks ) ) :

				// Loop through blocks and get template part
				foreach ( $blocks as $block ) :

					// Callable output
					if ( 'the_content' !== $block && is_callable( $block ) ) {

						call_user_func( $block );

					} else {

						get_template_part( 'partials/cpt/cpt-entry-' . $block, get_post_type() );

					}

				endforeach;

			endif; ?>
		</div>
		<?php wpex_cpt_entry_divider(); ?>
	<?php } ?>
</article>