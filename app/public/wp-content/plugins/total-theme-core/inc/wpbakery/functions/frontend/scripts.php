<?php
/**
 * Total Theme Core WPBakery Extension Scripts.
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue lightbox scripts.
 * This is a Total exclusive script.
 */
function vcex_enqueue_lightbox_scripts() {
	if ( function_exists( 'wpex_enqueue_lightbox_scripts' ) ) {
		wpex_enqueue_lightbox_scripts();
	} elseif ( function_exists( 'wpex_enqueue_ilightbox_scripts' ) ) {
		wpex_enqueue_ilightbox_scripts();
	}
}

/**
 * Enqueue slider scripts.
 */
function vcex_enqueue_slider_scripts( $noCarouselThumbnails = false ) {
	if ( function_exists( 'wpex_enqueue_slider_pro_scripts' ) ) {
		wpex_enqueue_slider_pro_scripts( $noCarouselThumbnails );
	}
}

/**
 * Enqueue carousel scripts.
 */
function vcex_enqueue_carousel_scripts() {

	wp_enqueue_style(
		'wpex-owl-carousel',
		vcex_asset_url( 'css/jquery.owlCarousel.min.css' ),
		array(),
		'2.3.4'
	);

	wp_enqueue_script(
		'wpex-owl-carousel',
		vcex_asset_url( 'js/lib/wpex.owl.carousel.min.js' ),
		array( 'jquery' ),
		TTC_VERSION,
		true
	);

	wp_localize_script(
		'wpex-owl-carousel',
		'wpexCarousel',
		array(
			'i18n' => array(
				'NEXT' => esc_html__( 'next slide', 'total-theme-core' ),
				'PREV' => esc_html__( 'previous slide', 'total-theme-core' ),
			),
		)
	);

	wp_enqueue_script( 'imagesloaded' );

}

/**
 * Enqueue isotope scripts.
 */
function vcex_enqueue_isotope_scripts() {
	wp_enqueue_script( 'imagesloaded' );
	wp_enqueue_script(
		'isotope',
		vcex_asset_url( 'js/lib/isotope.pkgd.min.js' ),
		array( 'jquery', 'imagesloaded' ),
		'3.0.6',
		true
	);
}

/**
 * Enqueue navbar filter scripts.
 */
function vcex_enqueue_navbar_filter_scripts() {
	vcex_enqueue_isotope_scripts();
}

/**
 * Enqueue Google Fonts.
 */
function vcex_enqueue_google_font( $font_family = '' ) {
	if ( $font_family && function_exists( 'wpex_enqueue_google_font' ) ) {
		wpex_enqueue_google_font( $font_family );
	}
}

/**
 * Enqueue Fonts.
 */
function vcex_enqueue_font( $font_family = '' ) {
	if ( $font_family && function_exists( 'wpex_enqueue_font' ) ) {
		wpex_enqueue_font( $font_family );
	}
}

/**
 * Enqueue justified gallery scripts.
 */
function vcex_enqueue_justified_gallery_scripts() {

	wp_enqueue_script(
		'justifiedGallery',
		vcex_asset_url( 'js/lib/jquery.justifiedGallery.min.js' ),
		array( 'jquery' ),
		'3.8.1',
		true
	);

	wp_enqueue_script(
		'vcex-justified-gallery',
		vcex_asset_url( 'js/vcex-justified-gallery.min.js' ),
		array( 'jquery', 'justifiedGallery' ),
		TTC_VERSION,
		true
	);

	wp_enqueue_style(
		'vcex-justified-gallery',
		vcex_asset_url( 'css/vcex-justified-gallery.css' ),
		array(),
		TTC_VERSION
	);
}