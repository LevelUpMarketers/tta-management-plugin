<?php
/**
 * Alters the default WordPress tag cloud widget arguments.
 * Makes sure all font sizes for the cloud widget are set to 1em.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

function wpex_widget_tag_cloud_args( $args ) {
	$args['largest']  = '1';
	$args['smallest'] = '1';
	$args['unit']     = 'em';
	return $args;
}
add_filter( 'widget_tag_cloud_args', 'wpex_widget_tag_cloud_args' );