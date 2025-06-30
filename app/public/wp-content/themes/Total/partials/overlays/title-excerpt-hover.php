<?php
/**
 * Title Excerpt Hover Overlay
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Only used for inside position
if ( 'inside_link' != $position ) {
	return;
}

// Get excerpt length
$excerpt_length = isset( $args['overlay_excerpt_length'] ) ? $args['overlay_excerpt_length'] : 15;

// Get title
$title = isset( $args['post_title'] ) ? $args['post_title'] : get_the_title();

// Animation speed
$speed = wpex_overlay_speed( 'title-excerpt-hover' );

?>

<div class="overlay-title-excerpt-hover overlay-hide theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo intval( $speed ); ?> wpex-overflow-hidden wpex-flex wpex-items-center wpex-justify-center wpex-text-center">
	<div class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'title-excerpt-hover' ); ?> wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'title-excerpt-hover', '70' ); ?>"></div>
	<div class="overlay-content overlay-scale wpex-relative wpex-text-white wpex-p-15 wpex-duration-<?php echo intval( $speed ); ?> wpex-transition-transform wpex-clr">
		<div class="overlay-title wpex-text-lg"><?php echo esc_html( $title ); ?></div>
		<?php
		if ( isset( $args['overlay_excerpt'] ) ) {
			echo wp_kses_post( $args['overlay_excerpt'] );
		} else {
			wpex_excerpt( array(
				'length'               => $excerpt_length,
				'trim_custom_excerpts' => apply_filters( 'wpex_title_excerpt_hover_overlay_trim_custom_excerpts', true ), // trims custom excerpts
				'before'               => '<div class="overlay-excerpt wpex-opacity-80 wpex-italic wpex-mt-10 wpex-last-mb-0">',
				'after'                => '</div>',
				'context'              => 'overlay_title_excerpt_hover',
			) );
		} ?>
	</div>
</div>