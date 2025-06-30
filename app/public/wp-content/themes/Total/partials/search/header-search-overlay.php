<?php
/**
 * Header Overlay Search
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="wpex-searchform-overlay" class="header-searchform-wrap wpex-fs-overlay" data-placeholder="<?php echo esc_attr( wpex_get_header_menu_search_form_placeholder() ); ?>" data-disable-autocomplete="true">
	<div class="wpex-close">&times;<span class="screen-reader-text"><?php esc_html_e( 'Close search', 'total' ); ?></span></div>
	<div class="wpex-inner wpex-scale">
		<?php wpex_hook_header_search_overlay_top(); ?>
		<div class="wpex-title"><?php esc_html_e( 'Search', 'total' ); ?></div>
		<?php echo wpex_get_header_menu_search_form(); ?>
		<?php wpex_hook_header_search_overlay_bottom(); ?>
	</div>
</div>