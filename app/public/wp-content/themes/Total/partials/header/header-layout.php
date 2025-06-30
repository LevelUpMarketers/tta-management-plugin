<?php
/**
 * Site header
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<?php wpex_hook_header_before(); ?>

<?php if ( ! wpex_theme_do_location( 'header' ) ) : ?>

	<header id="site-header" class="<?php echo wpex_header_classes(); ?>"<?php wpex_schema_markup( 'header' ); ?><?php wpex_aria_landmark( 'header' ); ?>>

		<?php wpex_hook_header_top(); ?>

		<div id="site-header-inner" class="container wpex-clr"><?php

			wpex_hook_header_inner();

		?></div>

		<?php wpex_hook_header_bottom(); ?>

	</header>

<?php endif; ?>

<?php wpex_hook_header_after(); ?>