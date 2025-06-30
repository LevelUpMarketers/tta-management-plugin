<?php
/**
 * Adds span around widgets that display counters for easier styling.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 *
 * @todo revise, can this be done in a different manner?
 */

defined( 'ABSPATH' ) || exit;

/**
 * Archives link.
 */
if ( ! function_exists( 'wpex_get_archives_link' ) ) :
	function wpex_get_archives_link( $link_html ) {
		if ( false === strpos( $link_html, 'span' ) ) {
			$link_html = str_replace( '</a>&nbsp;(', '</a> <span class="get_archives_link-span">(', $link_html );
			$link_html = str_replace( ')', ')</span>', $link_html );
		}
		return $link_html;
	}
	add_filter( 'get_archives_link', 'wpex_get_archives_link' );
endif;

/**
 * Categories list.
 */
if ( ! function_exists( 'wpex_wp_list_categories_args' ) ) :
	function wpex_wp_list_categories_args( $output ) {
		if ( false === strpos( $output, 'span' ) ) {
			$output = str_replace( '</a> (', '</a> <span class="cat-count-span">(', $output );
			$output = str_replace( ')', ')</span>', $output );
		}
		return $output;
	}
	add_filter( 'wp_list_categories', 'wpex_wp_list_categories_args' );
endif;