<?php
/**
 * Navbar Header Menu Mobile Toggle Style
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$text = ( $text = wpex_get_translated_theme_mod( 'mobile_menu_toggle_text' ) ) ? $text : esc_html__( 'Menu', 'total' );
$text = apply_filters( 'wpex_mobile_menu_navbar_open_text', $text );

?>

<div id="wpex-mobile-menu-navbar" <?php wpex_mobile_menu_toggle_class(); ?>>
	<div class="container">
		<div class="wpex-flex wpex-items-center wpex-justify-between wpex-text-white wpex-child-inherit-color wpex-text-md">
			<?php wpex_hook_mobile_menu_toggle_top(); ?>
			<div id="wpex-mobile-menu-navbar-toggle-wrap" class="wpex-flex-grow">
				<a href="#mobile-menu" class="mobile-menu-toggle wpex-no-underline" role="button" aria-expanded="false"<?php wpex_aria_label( 'mobile_menu_toggle' ); ?>><?php echo apply_filters( 'wpex_mobile_menu_navbar_open_icon', '<span class="ticon ticon-navicon wpex-mr-10" aria-hidden="true"></span>' ); ?><span class="wpex-text"><?php echo wp_kses_post( $text ); ?></span></a>
			</div>
			<?php wpex_mobile_menu_toggle_extra_icons(); ?>
			<?php wpex_hook_mobile_menu_toggle_bottom(); ?>
		</div>
	</div>
</div>