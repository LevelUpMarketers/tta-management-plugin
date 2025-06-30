<?php
/**
 * vcex_social_share shortcode
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_social_share';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

if ( ! function_exists( 'wpex_social_share_list' )
	|| ! function_exists( 'wpex_social_share_data' )
	|| ! function_exists( 'wpex_social_share_list' )
) {
	return;
}

$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

if ( ! empty( $atts[ 'sites' ] ) ) {
	$sites = (array) vcex_vc_param_group_parse_atts( $atts[ 'sites' ] );
}

if ( empty( $sites ) || ! is_array( $sites ) ) {
	return;
}

$sites_array = array();
foreach ( $sites as $k => $v ) {
	if ( is_array( $v ) && isset( $v[ 'site' ] ) ) {
		$sites_array[] = $v[ 'site' ];
	}
}

$args = array(
	'style'    => $atts['style'],
	'position' => 'horizontal',
);

$class = 'vcex-module vcex-social-share';

if ( $atts['bottom_margin'] ) {
	$class .= ' ' . vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
}

?>

<div class="<?php echo esc_attr( $class ); ?>">

	<div <?php wpex_social_share_class( $args ); ?> <?php wpex_social_share_data( vcex_get_the_ID(), $sites_array ); ?>>

		<?php
		// Display social share items
		wpex_social_share_list( $args, $sites_array ); ?>

	</div>

</div>