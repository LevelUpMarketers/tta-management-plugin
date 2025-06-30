<?php
/**
 * CTP entry button
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

?>

<div <?php wpex_cpt_entry_button_wrap_class(); ?>>
	<a href="<?php wpex_permalink(); ?>" <?php wpex_cpt_entry_button_class(); ?>><?php wpex_cpt_entry_button_text(); ?></a>
</div>