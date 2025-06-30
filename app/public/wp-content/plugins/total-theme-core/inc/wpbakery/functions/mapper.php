<?php
/**
 * Mapper functions.
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parse shortcode attributes.
 */
function vcex_vc_map_get_attributes( $shortcode = '', $atts = '', $class = '' ) {
	// Fix inline shortcodes - @see WPBakeryShortCode => prepareAtts()
	if ( is_array( $atts ) ) {
		foreach ( $atts as $key => $val ) {
			$atts[ $key ] = str_replace( array(
				'`{`',
				'`}`',
				'``',
			), array(
				'[',
				']',
				'"',
			), $val );
		}
	}
	if ( function_exists( 'vc_map_get_attributes' ) ) {
		return vc_map_get_attributes( $shortcode, $atts );
	}
	$atts = shortcode_atts( vcex_get_shortcode_class_attrs( $class ), $atts, $shortcode );
	return apply_filters( 'vc_map_get_attributes', $atts, $shortcode );
}

/**
 * Returns all shortcode atts and default values.
 */
function vcex_get_shortcode_class_attrs( $class ) {
	$atts = array();
	$map = $class->map();
	$params = $map[ 'params' ];
	if ( $params ) {
		foreach( $params as $k => $v ) {
			$value = '';
			if ( isset( $v[ 'std' ] ) ) {
				$value = $v[ 'std' ];
			} elseif ( isset( $v[ 'value' ] ) ) {
				if ( is_array( $v[ 'value' ] ) ) {
					$value = reset( $v[ 'value' ] );
				} else {
					$value = $v[ 'value' ];
				}
			}
			$atts[ $v[ 'param_name' ] ] = $value;
		}
	}
	return $atts;
}

/**
 * Helper function returns a shortcode attribute with a fallback.
 */
function vcex_shortcode_att( $atts, $att, $default = '' ) {
	return isset( $atts[$att] ) ? $atts[$att] : $default;
}

/**
 * Returns array of carousel settings
 */
function vcex_vc_map_carousel_settings( $dependency = array(), $group = '' ) {
	$settings = array(
		array(
			'type' => 'vcex_ofswitch',
			'heading' => esc_html__( 'Arrows', 'total-theme-core' ),
			'param_name' => 'arrows',
			'std' => 'true',
		),
		array(
			'type' => 'vcex_carousel_arrow_styles',
			'heading' => esc_html__( 'Arrows Style', 'total-theme-core' ),
			'param_name' => 'arrows_style',
			'dependency' => array( 'element' => 'arrows', 'value' => 'true' ),
		),
		array(
			'type' => 'vcex_carousel_arrow_positions',
			'heading' => esc_html__( 'Arrows Position', 'total-theme-core' ),
			'param_name' => 'arrows_position',
			'dependency' => array( 'element' => 'arrows', 'value' => 'true' ),
			'std' => 'default',
		),
		array(
			'type' => 'vcex_ofswitch',
			'heading' => esc_html__( 'Dot Navigation', 'total-theme-core' ),
			'param_name' => 'dots',
			'std' => 'false',
		),
		array(
			'type' => 'vcex_ofswitch',
			'heading' => esc_html__( 'Auto Play', 'total-theme-core' ),
			'param_name' => 'auto_play',
			'std' => 'false',
		),
		array(
			'type' => 'textfield',
			'heading' => esc_html__( 'Autoplay interval timeout.', 'total-theme-core' ),
			'param_name' => 'timeout_duration',
			'value' => '5000',
			'description' => esc_html__( 'Time in milliseconds between each auto slide. Default is 5000.', 'total-theme-core' ),
			'dependency' => array( 'element' => 'auto_play', 'value' => 'true' ),
		),
		array(
			'type' => 'vcex_ofswitch',
			'heading' => esc_html__( 'Infinite Loop', 'total-theme-core' ),
			'param_name' => 'infinite_loop',
			'std' => 'true',
		),
		array(
			'type' => 'vcex_ofswitch',
			'heading' => esc_html__( 'Center Item', 'total-theme-core' ),
			'param_name' => 'center',
			'std' => 'false',
		),
		array(
			'type' => 'textfield',
			'heading' => esc_html__( 'Animation Speed', 'total-theme-core' ),
			'param_name' => 'animation_speed',
			'value' => '250',
			'description' => esc_html__( 'Default is 250 milliseconds. Enter 0.0 to disable.', 'total-theme-core' ),
		),
		array(
			'type' => 'vcex_ofswitch',
			'std' => 'false',
			'heading' => esc_html__( 'Auto Width', 'total-theme-core' ),
			'param_name' => 'auto_width',
			'description' => esc_html__( 'If enabled the carousel will display items based on their width showing as many as possible.', 'total-theme-core' ),
		),
		array(
			'type' => 'textfield',
			'heading' => esc_html__( 'Items To Display', 'total-theme-core' ),
			'param_name' => 'items',
			'value' => '4',
			'dependency' => array( 'element' => 'auto_width', 'value' => 'false' ),
		),
		array(
			'type' => 'vcex_ofswitch',
			'std' => 'false',
			'heading' => esc_html__( 'Auto Height?', 'total-theme-core' ),
			'param_name' => 'auto_height',
			'dependency' => array( 'element' => 'items', 'value' => '1' ),
			'description' => esc_html__( 'Allows the carousel to change height based on the active item. This setting is used only when you are displaying 1 item per slide.', 'total-theme-core' ),
		),
		array(
			'type' => 'textfield',
			'heading' => esc_html__( 'Items To Scrollby', 'total-theme-core' ),
			'param_name' => 'items_scroll',
			'value' => '1',
		),
		array(
			'type' => 'textfield',
			'heading' => esc_html__( 'Tablet: Items To Display', 'total-theme-core' ),
			'param_name' => 'tablet_items',
			'value' => '3',
			'dependency' => array( 'element' => 'auto_width', 'value' => 'false' ),
		),
		array(
			'type' => 'textfield',
			'heading' => esc_html__( 'Mobile Landscape: Items To Display', 'total-theme-core' ),
			'param_name' => 'mobile_landscape_items',
			'value' => '2',
			'dependency' => array( 'element' => 'auto_width', 'value' => 'false' ),
		),
		array(
			'type' => 'textfield',
			'heading' => esc_html__( 'Mobile Portrait: Items To Display', 'total-theme-core' ),
			'param_name' => 'mobile_portrait_items',
			'value' => '1',
			'dependency' => array( 'element' => 'auto_width', 'value' => 'false' ),
		),
		array(
			'type' => 'textfield',
			'heading' => esc_html__( 'Margin Between Items', 'total-theme-core' ),
			'param_name' => 'items_margin',
			'value' => '15',
		),
	);

	if ( $dependency ) {
		foreach ( $settings as $key => $value ) {
			if ( empty( $settings[$key]['dependency'] ) ) {
				$settings[$key]['dependency'] = $dependency;
			}
		}
	}

	if ( $group ) {
		foreach ( $settings as $key => $value ) {
			$settings[$key]['group'] = $group;
		}
	}

	return $settings;
}

/**
 * Returns array for adding CSS Animation to VC modules.
 */
function vcex_vc_map_add_css_animation( $args = array() ) {

	// Fallback pre VC 5.0
	if ( ! function_exists( 'vc_map_add_css_animation' ) ) {

		$animations = apply_filters( 'wpex_css_animations', array(
			''              => esc_html__( 'None', 'total') ,
			'top-to-bottom' => esc_html__( 'Top to bottom', 'total' ),
			'bottom-to-top' => esc_html__( 'Bottom to top', 'total' ),
			'left-to-right' => esc_html__( 'Left to right', 'total' ),
			'right-to-left' => esc_html__( 'Right to left', 'total' ),
			'appear'        => esc_html__( 'Appear from center', 'total' ),
		) );

		return array(
			'type' => 'dropdown',
			'heading' => esc_html__( 'Appear Animation', 'total-theme-core' ),
			'param_name' => 'css_animation',
			'value' => array_flip( $animations ),
			'dependency' => array( 'element' => 'filter', 'value' => 'false' ),
		);

	}

	// New since VC 5.0
	$defaults = array(
		'type' => 'animation_style',
		'heading' => esc_html__( 'CSS Animation', 'total-theme-core' ),
		'param_name' => 'css_animation',
		'value' => 'none',
		'std' => 'none',
		'settings' => array(
			'type' => 'in',
			'custom' => array(
				array(
					'label' => esc_html__( 'Default', 'total-theme-core' ),
					'values' => array(
						__( 'Top to bottom', 'total-theme-core' )      => 'top-to-bottom',
						__( 'Bottom to top', 'total-theme-core' )      => 'bottom-to-top',
						__( 'Left to right', 'total-theme-core' )      => 'left-to-right',
						__( 'Right to left', 'total-theme-core' )      => 'right-to-left',
						__( 'Appear from center', 'total-theme-core' ) => 'appear',
					),
				),
			),
		),
		'description' => esc_html__( 'Select a CSS animation for when the element "enters" the browser\'s viewport. Note: Animations will not work with grid filters as it creates a conflict with re-arranging items.', 'total-theme-core' ) ,
	);
	$args = wp_parse_args( $args, $defaults );
	return apply_filters( 'vc_map_add_css_animation', $args );
}