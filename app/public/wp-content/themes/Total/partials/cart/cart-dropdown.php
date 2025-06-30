<?php
/**
 * Header cart dropdown
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="current-shop-items-dropdown" <?php wpex_header_drop_widget_class(); ?>>
	<div id="current-shop-items-inner">
		<?php the_widget(
			'WC_Widget_Cart',
			array(),
			array(
				'before_title' => '<span class="widgettitle screen-reader-text">',
				'after_title' => '</span>'
			)
		); ?>
	</div>
</div>