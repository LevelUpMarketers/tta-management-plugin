<?php
/**
 * Site header search dropdown HTML
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="searchform-dropdown" data-placeholder="<?php echo esc_attr( wpex_get_header_menu_search_form_placeholder() ); ?>" data-disable-autocomplete="true" <?php wpex_header_drop_widget_search_class(); ?>>
	<?php echo wpex_get_header_menu_search_form(); ?>
</div>