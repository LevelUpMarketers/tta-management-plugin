<?php
/**
 * Header aside content used in Header Style Two, Three and Four
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get header style
$header_style = wpex_header_style();

// Get content
$content = wpex_header_aside_content();

// Display header aside if content exists or it's header style 2 and the main search is enabled
if ( $content || ( get_theme_mod( 'main_search', true ) && 'two' == $header_style ) ) :

	// Add classes
	$classes = 'wpex-clr';
	if ( $visibility = get_theme_mod( 'header_aside_visibility', 'visible-desktop' ) ) {
		$classes .= ' ' . $visibility;
	}
	if ( $header_style ) {
		$classes .= ' header-' . sanitize_html_class( $header_style ) . '-aside';
	}

	// Placeholder
	$placeholder = esc_attr( apply_filters( 'wpex_get_header_aside_search_form_placeholder', esc_html__( 'search', 'total' ) ) ); ?>

	<aside id="header-aside" class="<?php echo esc_attr( $classes ); ?>">
		<div class="header-aside-content wpex-clr"><?php echo do_shortcode( wp_kses_post( $content ) ); ?></div>
		<?php
		// Show header search field if enabled in the theme options panel and it's header style 2
		if ( 'two' == $header_style && get_theme_mod( 'header_aside_search', true ) ) : ?>
			<div id="header-two-search" class="wpex-clr">
				<form method="get" class="header-two-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label>
						<span class="screen-reader-text"><?php echo esc_attr( $placeholder ); ?></span>
						<input type="search" id="header-two-search-input" name="s" placeholder="<?php echo esc_attr( $placeholder ); ?>">
					</label>
					<?php if ( defined( 'ICL_LANGUAGE_CODE' ) ) : ?>
						<input type="hidden" name="lang" value="<?php echo( ICL_LANGUAGE_CODE ); ?>"/>
					<?php endif; ?>
					<?php if ( WPEX_WOOCOMMERCE_ACTIVE && get_theme_mod( 'woo_header_product_searchform', false ) ) { ?>
						<input type="hidden" name="post_type" value="product" />
					<?php } ?>
					<?php $button_text = apply_filters( 'wpex_header_aside_search_button_text', '<span class="ticon ticon-search" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'Search', 'total' ) . '</span>' ); ?>
					<button type="submit" id="header-two-search-submit"><?php echo wp_kses_post( $button_text ); ?></button>
				</form>
			</div>
		<?php endif; ?>
	</aside>

<?php endif;