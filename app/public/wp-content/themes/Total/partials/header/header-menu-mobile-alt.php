<?php
/**
 * Mobile Menu alternative.
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="mobile-menu-alternative" class="wpex-hidden"<?php wpex_aria_landmark( 'mobile_menu_alt' ); ?><?php wpex_aria_label( 'mobile_menu_alt' ); ?>>
	<?php wp_nav_menu( array(
		'theme_location' => 'mobile_menu_alt',
		'menu_class'     => 'dropdown-menu',
		'fallback_cb'    => false,
	) ); ?>
</div>