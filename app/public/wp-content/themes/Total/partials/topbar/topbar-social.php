<?php
/**
 * Topbar social profiles
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0.1
 */

defined( 'ABSPATH' ) || exit;

// Return if disabled
if ( ! wpex_has_topbar_social() ) {
	return;
}

// Get alt content
$social_alt = wpex_topbar_social_alt_content();

// Display Social alternative
if ( ! empty( $social_alt ) ) : ?>

	<div id="top-bar-social-alt" <?php wpex_topbar_social_class(); ?>><?php

		echo do_shortcode( $social_alt );

	?></div>

<?php
// If social alternative is defined lets bail
return;

// End social alternative check
endif; ?>

<div id="top-bar-social" <?php wpex_topbar_social_class(); ?>>
	<?php wpex_hook_topbar_social_top(); ?>
	<?php wpex_topbar_social_list(); // you can override with the wpex_topbar_social_links_output filter ?>
	<?php wpex_hook_topbar_social_bottom(); ?>
</div>
