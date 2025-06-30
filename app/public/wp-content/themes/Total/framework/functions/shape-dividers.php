<?php
/**
 * Shape Dividers
 *
 * @package Total WordPress Theme
 * @version 5.0.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shape Divider Styles
 *
 * @since 4.9.3
 */
function wpex_get_shape_divider_types() {
	return apply_filters( 'wpex_get_section_shape_divider_types', array(
		''                      => esc_html__( 'None', 'total' ),
		'tilt'                  => esc_html__( 'Tilt', 'total' ),
		'triangle'              => esc_html__( 'Triangle', 'total' ),
		'triangle_asymmetrical' => esc_html__( 'Triangle Asymmetrical', 'total' ),
		'arrow'                 => esc_html__( 'Arrow', 'total' ),
		'curve'                 => esc_html__( 'Curve', 'total' ),
		'waves'                 => esc_html__( 'Waves', 'total' ),
		'clouds'                => esc_html__( 'Clouds', 'total' ),
	) );
}

/**
 * Shape Divider Styles
 *
 * @since 4.9.3
 */
function vcex_insert_top_shape_divider( $content, $atts ) {
	if ( ! empty( $atts['wpex_shape_divider_top'] ) ) {
		$content .= wpex_get_shape_divider( 'top', $atts['wpex_shape_divider_top'], wpex_get_shape_divider_settings( 'top', $atts ) );
	}
	return $content;
}

/**
 * Shape Divider Styles
 *
 * @since 4.9.3
 */
function vcex_insert_bottom_shape_divider( $content, $atts ) {
	if ( ! empty( $atts['wpex_shape_divider_bottom'] ) ) {
		$content .= wpex_get_shape_divider( 'bottom', $atts['wpex_shape_divider_bottom'], wpex_get_shape_divider_settings( 'bottom', $atts ) );
	}
	return $content;
}

/**
 * Print row section divider
 */
function wpex_get_shape_divider_settings( $position = 'top', $atts = array() ) {

	$settings = array(
		'color'      => '',
		'width'      => '',
		'height'     => '',
		'flip'       => false,
		'invert'     => false,
		'visibility' => '',
	);

	foreach( $settings as $k => $v ) {
		$atts_setting_k = 'wpex_shape_divider_' . $position . '_' . $k;
		if ( isset( $atts[ $atts_setting_k ] ) ) {
			$settings[ $k ] = $atts[ $atts_setting_k ];
		}
	}

	return apply_filters( 'wpex_get_shape_divider_settings', $settings, $atts );
}

/**
 * Print row section divider
 */
function wpex_get_shape_divider( $position = 'top', $type = 'triangle', $settings = array() ) {

	$classes = array(
		'wpex-shape-divider',
		'wpex-shape-divider-' . esc_attr( $type ),
		'wpex-shape-divider-' . esc_attr( $position ),
	);

	$rotate = false;
	$flip   = isset( $settings['flip'] ) && 'true' == $settings['flip'] ? true : false;
	$invert = isset( $settings['invert'] ) && 'true' == $settings['invert'] ? true : false;

	if ( $flip ) {
		$classes[] = 'wpex-shape-divider-flip';
	}

	if ( wpex_shape_divider_rotate( $position, $type, $invert ) ) {
		$classes[] = 'wpex-shape-divider-rotate';
	}

	if ( ! empty( $settings['visibility'] ) ) {
		$classes[] = esc_attr( $settings['visibility'] );
	}

	$classes = array_unique( array_map( 'esc_attr', $classes ) );

	return '<div class="' . implode( ' ', $classes ) .'"> ' . wpex_get_shape_dividers_svg( $type, $settings ) . '</div>';

}

/**
 * Check if shape needs rotating
 */
function wpex_shape_divider_rotate( $position, $type, $invert ) {

	if ( 'top' == $position ) {
		if ( $invert && in_array( $type, array( 'triangle', 'triangle_asymmetrical', 'arrow', 'clouds', 'curve', 'waves' ) ) ) {
			return true;
		}
	}

	if ( 'bottom' == $position ) {
		if ( ! $invert && in_array( $type, array( 'tilt', 'triangle', 'triangle_asymmetrical', 'arrow', 'clouds', 'curve', 'waves' ) ) ) {
			return true;
		}
	}

}

/**
 * Array of shape dividers
 */
function wpex_get_shape_dividers_svg( $type = '', $settings = array() ) {

	$svg = '';

	// Get inline Styles
	$svg_styles = array();
	$svg_styles_html = '';

	if ( ! empty( $settings['height'] ) ) {
		$svg_styles['height'] = absint( $settings['height'] ) . 'px';
	}

	if ( ! empty( $settings['width'] ) ) {
		$svg_styles['width'] = 'calc(' . absint( $settings['width'] ) . '% + 1.3px)';
	}

	if ( $svg_styles ) {

		$svg_styles_html = 'style="';

		$svg_styles = array_map( 'esc_attr', $svg_styles );

		foreach ( $svg_styles as $name => $value ) {
			$svg_styles_html .= ' ' . $name . ':' . $value . ';';
		}

		$svg_styles_html .= '"';

	}


	// Set inline path attributes
	$path_attrs = array();
	$path_attrs_html = '';

	if ( ! empty( $settings['color'] ) ) {
		$path_attrs['fill'] = $settings['color'];
	} else {
		$path_attrs['fill'] = '#fff';
	}

	if ( $path_attrs ) {

		$path_attrs = array_map( 'esc_attr', $path_attrs );

		foreach ( $path_attrs as $name => $value ) {
			$path_attrs_html .= ' ' . $name . '="' . $value . '"';
		}

	}

	/* Tilt */
	if ( 'tilt' == $type ) {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M0,6V0h1000v100L0,6z"' . $path_attrs_html . ' /></svg>';
	}

	/* Triangle */
	elseif ( 'triangle' == $type ) {

		if ( ! empty( $settings[ 'invert' ] ) && 'true' == $settings[ 'invert' ] ) {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M500.2,94.7L0,0v100h1000V0L500.2,94.7z"' . $path_attrs_html . ' /></svg>';

		} else {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="m500 98.9-500-92.8v-6.1h1000v6.1z"' . $path_attrs_html . ' /></svg>';

		}

	}

	/* Triangle Asymetrical */
	elseif ( 'triangle_asymmetrical' == $type ) {

		if ( ! empty( $settings[ 'invert' ] ) && 'true' == $settings[ 'invert' ] ) {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M737.9,94.7L0,0v100h1000V0L737.9,94.7z"' . $path_attrs_html . ' /></svg>';

		} else {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M738,99l262-93V0H0v5.6L738,99z"' . $path_attrs_html . ' /></svg>';

		}

	}

	/* Arrow */
	elseif ( 'arrow' == $type ) {

		if ( ! empty( $settings[ 'invert' ] ) && 'true' == $settings[ 'invert' ] ) {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 700 10" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M360 0L350 9.9 340 0 0 0 0 10 700 10 700 0"' . $path_attrs_html . ' /></svg>';

		} else {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 700 10" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M350,10L340,0h20L350,10z"' . $path_attrs_html . ' /></svg>';

		}
	}

	/* Curve */
	elseif ( 'curve' == $type ) {

		if ( ! empty( $settings[ 'invert' ] ) && 'true' == $settings[ 'invert' ] ) {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M500,97C126.7,96.3,0.8,19.8,0,0v100l1000,0V1C1000,19.4,873.3,97.8,500,97z"' . $path_attrs_html . ' /></svg>';

		} else {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M1000,4.3V0H0v4.3C0.9,23.1,126.7,99.2,500,100S1000,22.7,1000,4.3z"' . $path_attrs_html . ' /></svg>';

		}
	}

	/* Waves */
	elseif ( 'waves' == $type ) {

		if ( ! empty( $settings[ 'invert' ] ) && 'true' == $settings[ 'invert' ] ) {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M790.5,93.1c-59.3-5.3-116.8-18-192.6-50c-29.6-12.7-76.9-31-100.5-35.9c-23.6-4.9-52.6-7.8-75.5-5.3
	c-10.2,1.1-22.6,1.4-50.1,7.4c-27.2,6.3-58.2,16.6-79.4,24.7c-41.3,15.9-94.9,21.9-134,22.6C72,58.2,0,25.8,0,25.8V100h1000V65.3
	c0,0-51.5,19.4-106.2,25.7C839.5,97,814.1,95.2,790.5,93.1z"' . $path_attrs_html . ' /></svg>';

		} else {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M421.9,6.5c22.6-2.5,51.5,0.4,75.5,5.3c23.6,4.9,70.9,23.5,100.5,35.7c75.8,32.2,133.7,44.5,192.6,49.7
	c23.6,2.1,48.7,3.5,103.4-2.5c54.7-6,106.2-25.6,106.2-25.6V0H0v30.3c0,0,72,32.6,158.4,30.5c39.2-0.7,92.8-6.7,134-22.4
	c21.2-8.1,52.2-18.2,79.7-24.2C399.3,7.9,411.6,7.5,421.9,6.5z"' . $path_attrs_html . ' /></svg>';

		}
	}

	/* Triangle */
	elseif ( 'clouds' == $type ) {

		if ( ! empty( $settings[ 'invert' ] ) && 'true' == $settings[ 'invert' ] ) {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 283.5 27.8" preserveAspectRatio="xMidYMax slice"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M265.8 3.5c-10.9 0-15.9 6.2-15.9 6.2s-3.6-3.5-9.2-.9c-9.1 4.1-4.4 13.4-4.4 13.4s-1.2.2-1.9.9c-.6.7-.5 1.9-.5 1.9s-1-.5-2.3-.2c-1.3.3-1.6 1.4-1.6 1.4s.4-3.4-1.5-5c-3.9-3.4-8.3-.2-8.3-.2s-.6-.7-.9-.9c-.4-.2-1.2-.2-1.2-.2s-4.4-3.6-11.5-2.6-10.4 7.9-10.4 7.9-.5-3.3-3.9-4.9c-4.8-2.4-7.4 0-7.4 0s2.4-4.1-1.9-6.4-6.2 1.2-6.2 1.2-.9-.5-2.1-.5-2.3 1.1-2.3 1.1.1-.7-1.1-1.1c-1.2-.4-2 0-2 0s3.6-6.8-3.5-8.9c-6-1.8-7.9 2.6-8.4 4-.1-.3-.4-.7-.9-1.1-1-.7-1.3-.5-1.3-.5s1-4-1.7-5.2c-2.7-1.2-4.2 1.1-4.2 1.1s-3.1-1-5.7 1.4-2.1 5.5-2.1 5.5-.9 0-2.1.7-1.4 1.7-1.4 1.7-1.7-1.2-4.3-1.2c-2.6 0-4.5 1.2-4.5 1.2s-.7-1.5-2.8-2.4c-2.1-.9-4 0-4 0s2.6-5.9-4.7-9c-7.3-3.1-12.6 3.3-12.6 3.3s-.9 0-1.9.2c-.9.2-1.5.9-1.5.9S99.4 3 94.9 3.9c-4.5.9-5.7 5.7-5.7 5.7s-2.8-5-12.3-3.9-11.1 6-11.1 6-1.2-1.4-4-.7c-.8.2-1.3.5-1.8.9-.9-2.1-2.7-4.9-6.2-4.4-3.2.4-4 2.2-4 2.2s-.5-.7-1.2-.7h-1.4s-.5-.9-1.7-1.4-2.4 0-2.4 0-2.4-1.2-4.7 0-3.1 4.1-3.1 4.1-1.7-1.4-3.6-.7c-1.9.7-1.9 2.8-1.9 2.8s-.5-.5-1.7-.2c-1.2.2-1.4.7-1.4.7s-.7-2.3-2.8-2.8c-2.1-.5-4.3.2-4.3.2s-1.7-5-11.1-6c-3.8-.4-6.6.2-8.5 1v21.2h283.5V11.1c-.9.2-1.6.4-1.6.4s-5.2-8-16.1-8z"' . $path_attrs_html . ' /></svg>';

		} else {

			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 283.5 27.8" preserveAspectRatio="xMidYMax slice"' . $svg_styles_html . '><path class="wpex-shape-divider-path" d="M0 0v6.7c1.9-.8 4.7-1.4 8.5-1 9.5 1.1 11.1 6 11.1 6s2.1-.7 4.3-.2c2.1.5 2.8 2.6 2.8 2.6s.2-.5 1.4-.7c1.2-.2 1.7.2 1.7.2s0-2.1 1.9-2.8c1.9-.7 3.6.7 3.6.7s.7-2.9 3.1-4.1 4.7 0 4.7 0 1.2-.5 2.4 0 1.7 1.4 1.7 1.4h1.4c.7 0 1.2.7 1.2.7s.8-1.8 4-2.2c3.5-.4 5.3 2.4 6.2 4.4.4-.4 1-.7 1.8-.9 2.8-.7 4 .7 4 .7s1.7-5 11.1-6c9.5-1.1 12.3 3.9 12.3 3.9s1.2-4.8 5.7-5.7c4.5-.9 6.8 1.8 6.8 1.8s.6-.6 1.5-.9c.9-.2 1.9-.2 1.9-.2s5.2-6.4 12.6-3.3c7.3 3.1 4.7 9 4.7 9s1.9-.9 4 0 2.8 2.4 2.8 2.4 1.9-1.2 4.5-1.2 4.3 1.2 4.3 1.2.2-1 1.4-1.7 2.1-.7 2.1-.7-.5-3.1 2.1-5.5 5.7-1.4 5.7-1.4 1.5-2.3 4.2-1.1c2.7 1.2 1.7 5.2 1.7 5.2s.3-.1 1.3.5c.5.4.8.8.9 1.1.5-1.4 2.4-5.8 8.4-4 7.1 2.1 3.5 8.9 3.5 8.9s.8-.4 2 0 1.1 1.1 1.1 1.1 1.1-1.1 2.3-1.1 2.1.5 2.1.5 1.9-3.6 6.2-1.2 1.9 6.4 1.9 6.4 2.6-2.4 7.4 0c3.4 1.7 3.9 4.9 3.9 4.9s3.3-6.9 10.4-7.9 11.5 2.6 11.5 2.6.8 0 1.2.2c.4.2.9.9.9.9s4.4-3.1 8.3.2c1.9 1.7 1.5 5 1.5 5s.3-1.1 1.6-1.4c1.3-.3 2.3.2 2.3.2s-.1-1.2.5-1.9 1.9-.9 1.9-.9-4.7-9.3 4.4-13.4c5.6-2.5 9.2.9 9.2.9s5-6.2 15.9-6.2 16.1 8.1 16.1 8.1.7-.2 1.6-.4V0H0z"' . $path_attrs_html . ' /></svg>';

		}

	}

	/* Return SVG Code */
	return apply_filters( 'wpex_get_shape_dividers_svg', $svg, $type, $settings, $svg_styles_html, $path_attrs_html );
}