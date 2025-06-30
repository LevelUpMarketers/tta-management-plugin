<?php
/**
 * Footer bottom content
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get footer menu location and apply filters for child theming
$menu_location = apply_filters( 'wpex_footer_menu_location', 'footer_menu' );

// Menu is required
if ( ! has_nav_menu( $menu_location ) ) {
	return;
}

?>

<div id="footer-bottom-menu" <?php wpex_footer_bottom_menu_class(); ?><?php wpex_aria_landmark( 'footer_bottom_menu' ); ?><?php wpex_aria_label( 'footer_bottom_menu' ); ?>>
	<?php wp_nav_menu( array(
		'theme_location' => $menu_location,
		'sort_column'    => 'menu_order',
		'fallback_cb'    => false,
	) ); ?>
</div>