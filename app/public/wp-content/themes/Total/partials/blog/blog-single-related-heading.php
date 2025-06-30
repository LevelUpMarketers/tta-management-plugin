<?php
/**
 * Blog single related heading
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

wpex_heading( array(
	'tag'           => 'h4',
	'content'		=> wpex_blog_related_heading(),
	'classes'		=> array( 'related-posts-title' ),
	'apply_filters'	=> 'blog_related',
) );
