<?php
/**
 * Return social links.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Echo user social links.
 *
 * @since 4.9
 */
function wpex_user_social_links( $args = array() ) {
	echo wpex_get_user_social_links( $args );
}

/**
 * Display user social links.
 *
 * @since 4.0
 */
function wpex_get_user_social_links( $user_id = '', $display = 'icons', $attr = '', $before = '', $after = '' ) {

	if ( ! $user_id ) {
		return;
	}

	// Allow array for arg 1 since 4.9
	if ( is_array( $user_id ) ) {
		$defaults = array(
			'before'          => '',
			'after'           => '',
			'user_id'         => '',
			'display'         => '',
			'link_attributes' => '',
		);
		extract( wp_parse_args( $user_id, $defaults ) );
		$attr = $link_attributes; // nicer name when passing array as args
	}

	$output = '';

	$settings = wpex_get_user_social_profile_settings_array();

	foreach ( $settings as $id => $val ) {

		$url = get_the_author_meta( 'wpex_' . $id, $user_id );

		if ( ! $url ) {
			continue;
		}

		$link_content = '';

		$label = isset( $val['label'] ) ? $val['label'] : $val; // Fallback for pre 4.5

		$default_attr = array(
			'href'  => esc_url( $url ),
			'class' => array(), // reset class for each item.
		);

		$attrs = apply_filters( 'wpex_get_user_social_link_attrs', wp_parse_args( $attr, $default_attr ), $id );

		// Make sure class is an array
		$attrs['class' ] = wpex_parse_list( $attrs['class' ] );

		if ( 'icons' == $display ) {

			if ( isset( $val['icon_class'] ) ) {
				$icon = $val['icon_class'];
			} elseif ( isset( $val['icon'] ) ) {
				$icon = $val['icon'];
			} else {
				$icon = '';
			}

			if ( $icon ) {

				$link_content = '<span class="' . esc_attr( $icon ) . '" aria-hidden="true"></span>';

				if ( $label ) {
					$link_content .= '<span class="screen-reader-text">' . esc_html( $label ) . '</span>';
				}

			}

			$attrs['class'][] = 'wpex-' . sanitize_html_class( $id );

		} elseif ( $label ) {

			$link_content = strip_tags( $label );

		}

		if ( $link_content ) {

			// Remove duplicate classnames
			$attrs['class'] = array_unique( $attrs['class'] );

			// Sanitize class
			$attrs['class'] = array_map( 'esc_attr', $attrs['class'] );

			// Output link
			$output .= wpex_parse_html( 'a', $attrs, $link_content );

		}

	} // endforeach

	$output = apply_filters( 'wpex_get_user_social_links', $output );

	return $before . $output . $after;

}