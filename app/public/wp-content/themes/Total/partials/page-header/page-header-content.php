<?php
/**
 * Page header content
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div <?php wpex_page_header_content_class(); ?>><?php

	/**
	 * Hook: wpex_hook_page_header_content.
	 *
	 * @hooked wpex_page_header_title - 10
	 * @hooked wpex_page_header_subheading - 10
	 * @hooked wpex_display_breadcrumbs - 20
	 */
	wpex_hook_page_header_content();

?></div>