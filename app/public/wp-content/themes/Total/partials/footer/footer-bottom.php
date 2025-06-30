<?php
/**
 * Footer bottom content
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<?php wpex_hook_footer_bottom_before(); ?>

<?php if ( ! wpex_theme_do_location( 'footer_bottom' ) ) : ?>

	<div id="footer-bottom" <?php wpex_footer_bottom_class(); ?><?php wpex_schema_markup( 'footer_bottom' ); ?>>

		<?php wpex_hook_footer_bottom_top(); ?>

		<div id="footer-bottom-inner" class="container"><?php

			wpex_hook_footer_bottom_inner();

		?></div>

		<?php wpex_hook_footer_bottom_bottom(); ?>

	</div>

<?php endif; ?>

<?php wpex_hook_footer_bottom_after(); ?>