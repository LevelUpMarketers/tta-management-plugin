<?php
/**
 * Adds schema markup to the authors post link
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

function wpex_author_posts_link_schema( $link ) {

	if ( $schema = wpex_get_schema_markup( 'author_link' ) ) {
		$link = str_replace( 'rel="author"', 'rel="author"' . $schema, $link );
	}

	return $link;

}
add_filter( 'the_author_posts_link', 'wpex_author_posts_link_schema' );