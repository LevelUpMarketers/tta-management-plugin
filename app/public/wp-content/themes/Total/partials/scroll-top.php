<?php
/**
 * The Scroll-Top Button
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Set default arrow
$default_arrow = 'chevron-up';

// Get style
$style = ( $style = get_theme_mod( 'scroll_top_style' ) ) ? $style : 'default';

// Define arrow classnames
$class = array(
	'wpex-block',
	'wpex-fixed',
	'wpex-round',
	'wpex-text-center',
	'wpex-box-content',
	'wpex-transition-all',
	'wpex-duration-200',
	'wpex-bottom-0',
	'wpex-right-0',
	'wpex-mr-25',
	'wpex-mb-25',
	'wpex-no-underline',
);

// Add style based classes
switch ( $style ) {

	case 'default' :

		$class[] = 'wpex-bg-gray-100';
		$class[] = 'wpex-text-gray-500';
		$class[] = 'wpex-hover-bg-accent';
		$class[] = 'wpex-hover-text-white';

	break;

	case 'black' :

		$class[] = 'wpex-bg-black';
		$class[] = 'wpex-text-white';
		$class[] = 'wpex-hover-bg-accent';
		$class[] = 'wpex-hover-text-white';

	break;

	case 'accent' :

		$class[] = 'wpex-bg-accent';
		$class[] = 'wpex-text-white';
		$class[] = 'wpex-hover-bg-accent_alt';
		$class[] = 'wpex-hover-text-white';

	break;

}

// Add filters to site scroll class for quick child theme edits
$class = apply_filters( 'wpex_scroll_top_class', $class );

// Get arrow
$arrow = ( $arrow = get_theme_mod( 'scroll_top_arrow' ) ) ? $arrow : $default_arrow;

// Get local scroll speed
$speed = get_theme_mod( 'scroll_top_speed' );
$speed = ( $speed || '0' === $speed ) ? absint( $speed ) : wpex_get_local_scroll_speed();

// Get easing
$easing = wpex_get_local_scroll_easing();

// Get offset
$offset = 100;

if ( $breakpoint = get_theme_mod( 'scroll_top_breakpoint' ) ) {
	echo '<div class="' . wpex_utl_visibility_class( 'hide', $breakpoint ) . '">';
}

?>

<a href="#outer-wrap" id="site-scroll-top" class="<?php echo esc_attr( implode( ' ', $class ) ); ?>" data-scroll-speed="<?php echo esc_attr( $speed ); ?>" data-scroll-offset="<?php echo esc_attr( $offset ); ?>" data-scroll-easing="<?php echo esc_attr( $easing ); ?>"<?php wpex_aria_landmark( 'scroll_top' ); ?>>
	<span class="ticon ticon-<?php echo sanitize_html_class( $arrow ); ?>" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Back To Top', 'total' ); ?></span>
</a>

<?php
if ( $breakpoint ) {
	echo '</div>';
}