<?php
/**
 * Cart overlay
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 4.9.9
 */ ?>

<div id="wpex-cart-overlay" class="wpex-fs-overlay">
	<div class="wpex-close"><span class="screen-reader-text"><?php esc_html_e( 'Close search', 'total' ); ?></span></div>
	<div class="wpex-inner wpex-scale">
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