<?php
/**
 * Topbar layout
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<?php wpex_hook_topbar_before(); ?>

	<?php if ( ! wpex_theme_do_location( 'topbar' ) ) : ?>

		<div id="top-bar-wrap" <?php wpex_topbar_wrap_class(); ?>>

			<div id="top-bar" <?php wpex_topbar_class(); ?>><?php

				wpex_hook_topbar_inner();

			?></div>

		</div>

	<?php endif; ?>

<?php wpex_hook_topbar_after(); ?>