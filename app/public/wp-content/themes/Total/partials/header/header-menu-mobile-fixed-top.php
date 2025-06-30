<?php
/**
 * Fixed Top Header Menu Mobile Toggle Style
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 *
 * @todo update so the toggle icon is not part of the filter @see navbar style to compare.
 */

defined( 'ABSPATH' ) || exit;

$text = ( $text = wpex_get_translated_theme_mod( 'mobile_menu_toggle_text' ) ) ? $text : esc_html__( 'Menu', 'total' );

?>

<div id="wpex-mobile-menu-fixed-top" <?php wpex_mobile_menu_toggle_class(); ?>>
	<div class="container">
		<div class="wpex-flex wpex-items-center wpex-justify-between wpex-text-white wpex-child-inherit-color wpex-text-md">
			<div id="wpex-mobile-menu-fixed-top-toggle-wrap" class="wpex-flex-grow">
				<?php wpex_hook_mobile_menu_toggle_top(); ?>
				<a href="#mobile-menu" class="mobile-menu-toggle wpex-no-underline" role="button" aria-expanded="false"<?php wpex_aria_label( 'mobile_menu_toggle' ); ?>><?php echo apply_filters( 'wpex_mobile_menu_open_button_text', '<span class="ticon ticon-navicon wpex-mr-10" aria-hidden="true"></span>' ); ?><span class="wpex-text"><?php echo wp_kses_post( $text ); ?></span></a>
			</div>
			<?php wpex_mobile_menu_toggle_extra_icons(); ?>
			<?php wpex_hook_mobile_menu_toggle_bottom(); ?>
		</div>
	</div>
</div>