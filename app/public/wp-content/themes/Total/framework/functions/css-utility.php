<?php
/**
 * CSS Utility helper functions
 *
 * @package Total Theme Core
 * @subpackage Framework
 * @version 1.1.3
 */

/**
 * Return utility font sizes.
 *
 * @since 5.0
 */
function wpex_utl_font_sizes() {
	return (array) apply_filters( 'wpex_utl_font_sizes', array(
		''     => esc_html__( 'Default', 'total' ),
		'base' => esc_html__( 'Base (1em)', 'total' ),
		'xs'   => esc_html__( 'Extra Small', 'total' ),
		'sm'   => esc_html__( 'Small', 'total' ),
		'md'   => esc_html__( 'Medium', 'total' ),
		'lg'   => esc_html__( 'Large', 'total' ),
		'xl'   => esc_html__( 'x Large', 'total' ),
		'2xl'  => esc_html__( '2x Large', 'total' ),
		'3xl'  => esc_html__( '3x Large', 'total' ),
		'4xl'  => esc_html__( '4x Large', 'total' ),
		'5xl'  => esc_html__( '5x Large', 'total' ),
		'6xl'  => esc_html__( '6x Large', 'total' ),
		'7xl'  => esc_html__( '7x Large', 'total' ),
	) );
}

/**
 * Return utility percentage widths.
 *
 * @since 5.0
 */
function wpex_utl_percent_widths() {
	return (array) apply_filters( 'wpex_utl_precentage_widths', array(
		''    => esc_html__( 'Default', 'total' ),
		'20'  => '20%',
		'25'  => '25%',
		'30'  => '30%',
		'33'  => '33%',
		'40'  => '40%',
		'50'  => '50%',
		'60'  => '60%',
		'67'  => '67%',
		'70'  => '70%',
		'75'  => '75%',
		'80'  => '80%',
		'100' => '100%',
	) );
}

/**
 * Return utility border radius.
 *
 * @since 5.0
 */
function wpex_utl_border_radius() {
	return (array) apply_filters( 'wpex_utl_border_radius', array(
		''             => esc_html__( 'Default', 'total' ),
		'rounded-sm'   => esc_html__( 'Small', 'total' ),
		'rounded'      => esc_html__( 'Average', 'total' ),
		'rounded-md'   => esc_html__( 'Medium', 'total' ),
		'rounded-lg'   => esc_html__( 'Large', 'total' ),
		'rounded-full' => esc_html__( 'Full', 'total' ),
		'rounded-0'    => esc_html__( 'None', 'total' ),
	) );
}

/**
 * Return utility border width types.
 *
 * @since 5.0
 */
function wpex_utl_border_widths() {
	return (array) apply_filters( 'wpex_utl_border_widths', array(
		''  => esc_html__( 'Default', 'total' ),
		'1' => '1px',
		'2' => '2px',
		'3' => '3px',
		'4' => '4px',
	) );
}

/**
 * Return utility paddings.
 *
 * @since 5.0
 */
function wpex_utl_paddings() {
	return (array) apply_filters( 'wpex_utl_paddings', array(
		''     => esc_html__( 'Default', 'total' ),
		'0px'  => '0px',
		'5px'  => '5px',
		'10px' => '10px',
		'15px' => '15px',
		'20px' => '20px',
		'25px' => '25px',
		'30px' => '30px',
		'40px' => '40px',
		'50px' => '50px',
	) );
}

/**
 * Return utility margins.
 *
 * @since 5.0
 */
function wpex_utl_margins() {
	return (array) apply_filters( 'wpex_utl_margins', array(
		''     => esc_html__( 'Default', 'total' ),
		'0px'  => '0px',
		'5px'  => '5px',
		'10px' => '10px',
		'15px' => '15px',
		'20px' => '20px',
		'25px' => '25px',
		'30px' => '30px',
		'40px' => '40px',
		'50px' => '50px',
	) );
}

/**
 * Get utility shadows.
 *
 * @since 5.0
 */
function wpex_utl_shadows() {
	return (array) apply_filters( 'wpex_utl_shadows', array(
		''		      => esc_html__( 'Default', 'total' ),
		'shadow-none' => esc_html__( 'None', 'total' ),
		'shadow-xs'   => esc_html__( 'Extra Small', 'total' ),
		'shadow-sm'   => esc_html__( 'Small', 'total' ),
		'shadow'      => esc_html__( 'Average', 'total' ),
		'shadow-md'   => esc_html__( 'Medium', 'total' ),
		'shadow-lg'   => esc_html__( 'Large', 'total' ),
		'shadow-xl'   => esc_html__( 'Extra Large', 'total' ),
		'shadow-2xl'  => esc_html__( '2x Large', 'total' ),
	) );
}

/**
 * Get utility divider styles.
 *
 * @since 5.0
 */
function wpex_utl_divider_styles() {
	return (array) apply_filters( 'wpex_utl_divider_styles', array(
		''       => esc_html__( 'Default', 'total' ),
		'solid'  => esc_html__( 'Solid', 'total' ),
		'dotted' => esc_html__( 'Dotted', 'total' ),
		'dashed' => esc_html__( 'Dashed', 'total' ),
	) );
}

/**
 * Get utility opacity.
 *
 * @since 5.0
 */
function wpex_utl_opacities() {
	return (array) apply_filters( 'wpex_utl_opacities', array(
		''	  => esc_html__( 'Default', 'total' ),
		'10'  => '10',
		'20'  => '20',
		'30'  => '30',
		'40'  => '40',
		'50'  => '50',
		'60'  => '60',
		'70'  => '70',
		'80'  => '80',
		'90'  => '90',
		'100' => '100',
	) );
}

/**
 * Get utility breakpoints.
 *
 * @since 5.0
 */
function wpex_utl_breakpoints() {
	return (array) apply_filters( 'wpex_utl_breakpoints', array(
		''   => esc_html__( 'Default', 'total' ),
		'sm' => esc_html__( 'sm - 640px', 'total' ),
		'md' => esc_html__( 'md - 768px', 'total' ),
		'lg' => esc_html__( 'lg - 1024px', 'total' ),
		'xl' => esc_html__( 'xl - 1280px', 'total' ),
	) );
}

/**
 * Return visibility classes.
 *
 * @since 5.0
 */
function wpex_utl_visibility_class( $show_hide = 'hide', $screen = '' ) {

	if ( empty( $screen ) && ! array_key_exists( $screen, wpex_utl_breakpoints() ) ) {
		return;
	}

	$class = '';

	switch ( $show_hide ) {
		case 'hide':
			$class = 'wpex-hidden wpex-' . sanitize_html_class( $screen ) . '-block';
			break;
		case 'show':
			$class = 'wpex-' . sanitize_html_class( $screen ) . '-hidden';
			break;
	}

	return $class;

}

/**
 * Sanitize utility font size.
 *
 * @since 5.0
 */
function wpex_sanitize_utl_font_size( $size ) {

	$sizes = wpex_utl_font_sizes();

	if ( ! array_key_exists( $size, $sizes ) ) {
		return;
	}

	$font_size = sanitize_html_class( $size );

	switch ( $font_size ) {
		case '5xl':
		case '6xl':
		case '7xl':
			$font_size = 'wpex-text-4xl wpex-md-text-' . $font_size;
			break;
		default:
			$font_size = 'wpex-text-' . $font_size;
			break;
	}

	return apply_filters( 'wpex_utl_font_size_class', $font_size, $size );

}