<?php
/**
 * Helper functions for cards
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.4
 *
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns array of card styles.
 *
 * @since 5.0
 */
function wpex_get_card_styles() {

	$card_categories = array(
		'simple' => array(
			'name' => esc_html__( 'Simple', 'Total' ),
			'count' => 6,
		),
		'blog' => array(
			'name' => esc_html__( 'Blog', 'Total' ),
			'count' => 12,
		),
		'blog-list' => array(
			'name' => esc_html__( 'Blog List', 'Total' ),
			'count' => 12,
		),
		'magazine' => array(
			'name' => esc_html__( 'Magazine', 'Total' ),
			'count' => 2,
		),
		'magazine-list' => array(
			'name' => esc_html__( 'Magazine List', 'Total' ),
			'count' => 2,
		),
		'numbered-list' => array(
			'name' => esc_html__( 'Numbered List', 'Total' ),
			'count' => 4,
		),
		'testimonial' => array(
			'name' => esc_html__( 'Testimonial', 'Total' ),
			'count' => 5,
		),
		'staff' => array(
			'name' => esc_html__( 'Staff', 'Total' ),
			'count' => 6,
		),
		'portfolio' => array(
			'name' => esc_html__( 'Portfolio', 'Total' ),
			'count' => 3,
		),
		/* - WIP, COMING SOON!
		'product' => array(
			'name' => esc_html__( 'Product', 'Total' ),
			'count' => 5,
		),*/
		'icon-box' => array(
			'name' => esc_html__( 'Icon Box', 'Total' ),
			'count' => 6,
		),
		'image' => array(
			'name' => esc_html__( 'Image', 'Total' ),
			'count' => 1,
		),
		'video' => array(
			'name' => esc_html__( 'Video', 'Total' ),
			'count' => 1,
		),
	);

	$card_styles = array();

	foreach( $card_categories as $key => $val ) {

		$x = 1;

		while( $x <= $val['count'] ) {

			if ( 1 === $val['count'] ) {
				$name = esc_html( $val['name'] );
			} else {
				$name = esc_html( $val['name'] . ' ' . $x );
			}

			$card_styles[sanitize_key( $key . '_' . $x )] = array(
				'name' => $name
			);

			$x++;

		}

	}

	return (array) apply_filters( 'wpex_card_styles', $card_styles );

}

/**
 * Return an array of card styles.
 *
 * @since 5.0
 */
function wpex_choices_card_styles() {

	$choices = array(
		'' => '&#8211; ' . esc_html__( 'Select', 'total' ) . ' &#8211;',
	);

	$card_styles = wpex_get_card_styles();

	foreach( $card_styles as $card_id => $card_settings ) {
		$choices[$card_id] = $card_settings['name'];
	}

	return $choices;

}

/**
 * Return dropdown select of card styles.
 *
 * @since 5.0
 */
function wpex_card_select( $args = array() ) {

	$defaults = array(
		'name'     => 'card_style',
		'selected' => '',
		'id'       => 'wpex_card_style',
		'class'    => 'wpex-card-select',
		'label'    => 0,
	);

	$parsed_args = wp_parse_args( $args, $defaults );

	$select = '';

	if ( $parsed_args['label'] ) {
		$select .= '<label for="' . esc_attr( $parsed_args['name'] ) . '">' . esc_html__( 'Select a card', 'Total' ) . ':</label>';
	}

	$select .= '<select name="' . esc_attr( $parsed_args['name'] ) . '"';

		if ( $parsed_args['id'] ) {
			$select .= ' id="' . esc_attr( $parsed_args['id'] ) . '"';
		}

		if ( $parsed_args['class'] ) {
			$select .= ' class="' . esc_attr( $parsed_args['class'] ) . '"';
		}

	$select .= '>';

	$choices = wpex_choices_card_styles();

	if ( $choices ) {

		foreach( $choices as $name => $label ) {

			$select .= '<option value="' . esc_attr( $name ) . '" ' . selected( $name, $parsed_args['selected'], false ) . '>' . esc_html__( $label ) . '</option>';


		}

	}

	$select .= '</select>';

	return $select;
}

/**
 * Display card.
 *
 * @since 5.0
 */
function wpex_card( $args = array() ) {
	echo wpex_get_card( $args );
}

/**
 * Get card.
 *
 * @since 5.0
 */
function wpex_get_card( $args = array() ) {
	$card = new WPEX_Card( $args );
	return $card->render();
}