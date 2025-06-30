<?php
/**
 * Term description
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! apply_filters( 'wpex_has_term_description', true ) ) {
	return;
}

$term_description = term_description();

if ( ! empty( $term_description ) ) {
	printf( '<div class="term-description entry wpex-clr">%s</div>', $term_description ); // WPCS: XSS ok, sanitization ok.
}
