<?php
/**
 * Main sidebar area containing your defined widgets.
 * You shouldn't have to edit this file ever since things are added via hooks.
 *
 * @package Total WordPress Theme
 * @subpackage Templates
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hook: wpex_hook_sidebar_before.
 */
wpex_hook_sidebar_before();

?>

<aside id="sidebar" <?php wpex_sidebar_class(); ?><?php wpex_schema_markup( 'sidebar' ); ?><?php wpex_aria_landmark( 'sidebar' ); ?>>

	<?php
	/**
	 * Hook: wpex_hook_sidebar_top.
	 */
	wpex_hook_sidebar_top(); ?>

	<div id="sidebar-inner" <?php wpex_sidebar_inner_class(); ?>><?php

		/**
		 * Hook: wpex_hook_sidebar_inner.
		 *
		 * @hooked wpex_display_sidebar - 10
		 */
		wpex_hook_sidebar_inner();

	?></div>

	<?php
	/**
	 * Hook: wpex_hook_sidebar_bottom.
	 */
	wpex_hook_sidebar_bottom(); ?>

</aside>

<?php
/**
 * Hook: wpex_hook_sidebar_after.
 */
wpex_hook_sidebar_after(); ?>