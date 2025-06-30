<?php
/**
 * The page header displays at the top of all single pages, posts and archives.
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hook: wpex_hook_page_header_before.
 *
 * @hooked wpex_post_slider - 10
 */
wpex_hook_page_header_before();

?>

<header <?php wpex_page_header_class(); ?>>

	<?php
	/**
	 * Hook: wpex_hook_page_header_top.
	 *
	 * @hooked wpex_page_header_overlay - 0
	 */
	wpex_hook_page_header_top(); ?>

	<div <?php wpex_page_header_inner_class(); ?>><?php

		/**
		 * Hook: wpex_hook_page_header_inner.
		 *
		 * @hooked wpex_page_header_content - 10
		 * @hooked wpex_page_header_aside - 10
		 */
		wpex_hook_page_header_inner();

	?></div>

	<?php wpex_hook_page_header_bottom(); ?>

</header>

<?php
/**
 * Hook: wpex_hook_page_header_after.
 */
wpex_hook_page_header_after();