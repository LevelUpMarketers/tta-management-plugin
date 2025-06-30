<?php
/**
 * Moves the WordPress Comment form fields back to their original spot.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 *
 * @todo remove or make optional?
 */

defined( 'ABSPATH' ) || exit;

function wpex_move_comment_form_fields( $fields ) {
	if ( ! is_singular( 'product' ) ) {
		$comment_field = $fields['comment'];
		unset( $fields['comment'] );
		$fields['comment'] = $comment_field;
	}
	return $fields;
}
add_filter( 'comment_form_fields', 'wpex_move_comment_form_fields' );