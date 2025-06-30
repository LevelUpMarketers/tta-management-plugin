<?php
/**
 * vcex_author_bio shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_author_bio';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

extract( $atts );

if ( function_exists( 'wpex_get_template_part' ) ) {
	wpex_get_template_part( 'author_bio' );
}