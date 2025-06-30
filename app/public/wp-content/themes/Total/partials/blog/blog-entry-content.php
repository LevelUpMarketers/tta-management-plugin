<?php
/**
 * Blog entry excerpt
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div <?php wpex_blog_entry_excerpt_class(); ?>>

	<?php
	// Display excerpt if auto excerpts are enabled in the admin
	if ( get_theme_mod( 'blog_exceprt', true ) ) :

		// Check if the post tag is using the "more" tag
		if ( apply_filters( 'wpex_check_more_tag', true ) && strpos( get_the_content(), 'more-link' ) ) :

			// Display the content up to the more tag
			the_content( '', '&hellip;' );

		// Otherwise display custom excerpt
		else :

			// Display custom excerpt
			wpex_excerpt( array(
				'length' => wpex_excerpt_length(),
			) );

		endif;

	// If excerpts are disabled, display full content
	else :

		the_content( '', '&hellip;' );

	endif; ?>

</div>