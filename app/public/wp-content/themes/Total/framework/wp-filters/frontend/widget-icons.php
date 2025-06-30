<?php
/**
 * Add icons to various widgets.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 *
 * @todo remove file since we are using CSS.
 */

defined( 'ABSPATH' ) || exit;

if ( ! get_theme_mod( 'has_widget_icons', true ) ) {
	return;
}

/**
 * Archive widget icons.
 *
 * @since 5.0
 */
function wpex_widget_archives_icons( $args ) {
	if ( $icon = apply_filters( 'wpex_widget_archives_icon', 'file-o' ) ) {
		$args['before'] = wpex_get_theme_icon_html( $icon, 'wpex-mr-10' );
	}
	return $args;
}
add_filter( 'widget_archives_args', 'wpex_widget_archives_icons' );

/**
 * Categories widget.
 *
 * @since 5.0
 */
function wpex_widget_categories_icon( $output ) {
	remove_filter( 'wp_list_categories', 'wpex_widget_categories_icon' );
	if ( $icon = apply_filters( 'wpex_widget_categories_icon', 'folder-o' ) ) {
    	$output = str_replace( '<a', wpex_get_theme_icon_html( $icon, 'wpex-mr-10' ) . '<a', $output );
	}
	return $output;
}

function wpex_widget_categories_icons( $cat_args ) {
    add_filter( 'wp_list_categories', 'wpex_widget_categories_icon' );
    return $cat_args;
}
add_filter( 'widget_categories_args', 'wpex_widget_categories_icons' );