<?php
/**
 * Lightbox Buttons Overlay
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Only used for outside position
if ( 'outside_link' != $position ) {
	return;
}

wpex_enqueue_lightbox_scripts();

// Lightbox
$lightbox_link = ! empty( $args['lightbox_link'] ) ? $args['lightbox_link'] : wpex_get_lightbox_image();
$lightbox_data = '';
if ( ! empty( $args['lightbox_data'] ) ) {
	$lightbox_data = is_array( $args['lightbox_data'] ) ? ' ' . implode( ' ', $args['lightbox_data'] ) : $args['lightbox_data'];
}
$lightbox_class = 'wpex-lightbox'; // can't use galleries in this overlay style due to duplicate links

// Custom Link
$link = isset( $args['overlay_link'] ) ? $args['overlay_link'] : wpex_get_permalink();

// Define link target
$target = '';
if ( isset( $args['link_target'] ) && ( 'blank' == $args['link_target'] || '_blank' == $args['link_target'] ) ) {
    $target = 'blank';
}

// Apply filters
$link   = apply_filters( 'wpex_lightbox_buttons_button_overlay_link', $link, $args );
$target = apply_filters( 'wpex_button_overlay_target', $target, $args );

// Sanitize Data
$link          =  $link;
$lightbox_link = $lightbox_link;

?>

<div class="overlay-view-lightbox-buttons overlay-hide theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo wpex_overlay_speed( 'view-lightbox-buttons' ); ?> wpex-flex wpex-items-center wpex-justify-center">
	<span class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'view-lightbox-buttons' ); ?> wpex-block wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'view-lightbox-buttons' ); ?>"></span>
	<div class="overlay-content wpex-relative wpex-p-20">
		<?php
		$button_class = array(
			'wpex-inline-block',
			'wpex-text-white',
			'wpex-hover-text-black',
			'wpex-hover-bg-white',
			'wpex-border-2',
			'wpex-border-solid',
			'wpex-border-white',
			'wpex-leading-snug',
			'wpex-no-underline',
			'wpex-px-10',
			'wpex-py-5',
			'wpex-semi-rounded',
			'wpex-transition-colors',
			'wpex-duration-200',
		);

		$button1_class   = $button_class;
		$button1_class[] = $lightbox_class;
		$button1_class[] = 'wpex-mr-5';

		$button_one_attrs = array(
			'href'   => esc_url( $lightbox_link ),
			'class'  => $button1_class,
			'data'   => $lightbox_data,
		); ?>
		<a <?php echo wpex_parse_attrs( $button_one_attrs ); ?>><span class="ticon ticon-search" aria-hidden="true"></span></a>
		<?php
		$button2_class = $button_class;
		$button2_class[] = 'view-post';

		$button_two_attrs = array(
			'href'   => esc_url( $link ),
			'class'  => $button2_class,
		);
		if ( 'blank' == $target || '_blank' == $target ) {
			$button_two_attrs['target'] = 'blank';
			$button_two_attrs['rel']    = 'noopener noreferrer';
		} ?>
		<a <?php echo wpex_parse_attrs( $button_two_attrs ); ?>><span class="ticon ticon-arrow-right" aria-hidden="true"></span></a>
	</div>
</div>