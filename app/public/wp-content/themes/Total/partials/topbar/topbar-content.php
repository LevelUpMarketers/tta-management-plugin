<?php
/**
 * Topbar content
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get topbar content
$content = wpex_topbar_content();

// Display topbar content
if ( $content || has_nav_menu( 'topbar_menu' ) ) : ?>

	<div id="top-bar-content" <?php wpex_topbar_content_class(); ?>>

		<?php
		// Get topbar menu
		get_template_part( 'partials/topbar/topbar-menu' ); ?>

		<?php
		// Check if there is content for the topbar
		if ( $content ) : ?>

			<?php
			// Display top bar content
			echo do_shortcode( wp_kses_post( $content ) ); ?>

		<?php endif; ?>

	</div>

<?php endif; ?>