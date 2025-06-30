<?php
/**
 * Footer Widgets
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get footer widgets columns
$columns    = get_theme_mod( 'footer_widgets_columns', '4' );
$grid_class = apply_filters( 'wpex_footer_widget_col_classes', array( wpex_grid_class( $columns ) ) );
$grid_class = is_array( $grid_class ) ? implode( ' ', $grid_class ) : $grid_class; ?>

<div id="footer-widgets" class="<?php echo esc_attr( wpex_footer_widgets_class() ); ?>">

	<?php do_action( 'wpex_hook_footer_widgets_top' ); ?>

	<div class="footer-box <?php echo esc_attr( $grid_class ); ?> col col-1"><?php dynamic_sidebar( 'footer_one' ); ?></div>

	<?php if ( $columns > '1' ) : ?>

		<div class="footer-box <?php echo esc_attr( $grid_class ); ?> col col-2"><?php dynamic_sidebar( 'footer_two' ); ?></div>

	<?php endif; ?>

	<?php if ( $columns > '2' ) : ?>

		<div class="footer-box <?php echo esc_attr( $grid_class ); ?> col col-3"><?php dynamic_sidebar( 'footer_three' ); ?></div>

	<?php endif; ?>

	<?php if ( $columns > '3' ) : ?>

		<div class="footer-box <?php echo esc_attr( $grid_class ); ?> col col-4"><?php dynamic_sidebar( 'footer_four' ); ?></div>

	<?php endif; ?>

	<?php if ( $columns > '4' ) : ?>

		<div class="footer-box <?php echo esc_attr( $grid_class ); ?> col col-5"><?php dynamic_sidebar( 'footer_five' ); ?></div>

	<?php endif; ?>

	<?php do_action( 'wpex_hook_footer_widgets_bottom' ); ?>

</div>