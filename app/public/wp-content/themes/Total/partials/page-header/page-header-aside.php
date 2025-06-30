<?php
/**
 * Page Header Aside
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

?>

<div <?php wpex_page_header_aside_class(); ?>><?php

	/**
	 * Hook: wpex_hook_page_header_aside.
	 *
	 * @hooked wpex_display_breadcrumbs - 10
	 */
	wpex_hook_page_header_aside();

?></div>