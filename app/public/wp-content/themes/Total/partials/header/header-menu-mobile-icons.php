<?php
/**
 * Mobile Icons Header Menu
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="mobile-menu" <?php wpex_mobile_menu_toggle_class(); ?>>
	<div class="wpex-inline-flex wpex-items-center">
		<?php wpex_hook_mobile_menu_toggle_top(); ?>
		<?php wpex_mobile_menu_toggle_extra_icons(); ?>
		<?php echo wpex_get_mobile_menu_toggle_icon(); ?>
		<?php wpex_hook_mobile_menu_toggle_bottom(); ?>
	</div>
</div>