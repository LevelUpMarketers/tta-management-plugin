<?php
/**
 * Helper function for adding aria landmarks
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

function wpex_aria_landmark( $location ) {
	echo wpex_get_aria_landmark( $location );
}

// @todo change to switch statement so it reads cleaner
function wpex_get_aria_landmark( $location ) {

	if ( ! get_theme_mod( 'aria_landmarks_enable', false ) ) {
		return;
	}

	$landmark = '';

	switch ( $location ) {
		case 'header':
			$landmark = 'role="banner"';
			break;
		case 'skip_to_content':
			$landmark = 'role="navigation"';
			break;
		case 'breadcrumbs':
			$landmark = 'role="navigation"';
			break;
		case 'site_navigation':
			$landmark = 'role="navigation"';
			break;
		case 'searchform':
			$landmark = 'role="search"';
			break;
		case 'main':
			$landmark = 'role="main"';
			break;
		case 'sidebar':
			$landmark = 'role="complementary"'; // @todo remove?
			break;
		case 'copyright':
			$landmark = 'role="contentinfo"';
			break;
		case 'footer_callout':
			$landmark = 'role="navigation"'; // @todo is this correct?
			break;
		case 'footer_bottom_menu':
			$landmark = 'role="navigation"';
			break;
		case 'scroll_top':
			$landmark = 'role="navigation"';
			break;
		case 'mobile_menu_alt':
			$landmark = 'role="navigation"';
			break;
	}

	$landmark = apply_filters( 'wpex_get_aria_landmark', $landmark, $location );

	if ( ! empty( $landmark ) ) {
		return ' ' . trim( $landmark );
	}

}