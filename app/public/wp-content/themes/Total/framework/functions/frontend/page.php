<?php
/**
 * Page functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Page content class.
 *
 * @since 5.0
 */
function wpex_page_single_content_class() {

	$class = array(
		'single-page-content',
		'single-content',
		'entry',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_page_single_content_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Page single blocks class.
 *
 * @since 5.0
 */
function wpex_page_single_blocks_class() {

	$class = array(
		'single-page-article',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_page_single_blocks_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Get Page single supported media types.
 *
 * @since 5.0
 */
function wpex_page_single_supported_media() {

	return (array) apply_filters( 'wpex_page_single_supported_media', array(
		'video',
		'audio',
		'thumbnail',
	) );

}

/**
 * Get Post type single format.
 *
 * @since 5.0
 */
function wpex_page_single_media_type() {

	$supported_media = wpex_page_single_supported_media();

	if ( in_array( 'video', $supported_media ) && wpex_has_post_video() ) {
		$type = 'video';
	} elseif ( in_array( 'audio', $supported_media ) && wpex_has_post_audio() ) {
		$type = 'audio';
	} elseif ( in_array( 'gallery', $supported_media ) && wpex_has_post_gallery() ) {
		$type = 'gallery';
	} elseif ( in_array( 'thumbnail', $supported_media ) && has_post_thumbnail() ) {
		$type = 'thumbnail';
	} else {
		$type = ''; //important
	}

	return apply_filters( 'wpex_page_single_media_type', $type );

}

/**
 * Page single media class.
 *
 * @since 5.0
 */
function wpex_page_single_media_class() {

	$class = array(
		'single-media',
		'wpex-mb-30',
	);

	$class = (array) apply_filters( 'wpex_page_single_media_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Page single header class.
 *
 * @since 5.0
 */
function wpex_page_single_header_class() {

	$class = array(
		'single-page-header',
	);

	if ( 'full-screen' == wpex_content_area_layout() ) {
		$class[] = 'container';
	}

	$class = (array) apply_filters( 'wpex_page_single_header_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Page single title class.
 *
 * @since 5.0
 */
function wpex_page_single_title_class() {

	$class = array(
		'single-page-title',
		'entry-title',
		'wpex-text-3xl',
		'wpex-mb-20',
	);

	$class = (array) apply_filters( 'wpex_page_single_title_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}