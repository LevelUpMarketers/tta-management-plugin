<?php
/**
 * Page links
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Link pages when using <!--nextpage-->
wp_link_pages( array(
	'before'      => '<div class="page-links wpex-clr">',
	'after'       => '</div>',
	'link_before' => '<span>',
	'link_after'  => '</span>'
) );