<?php
/**
 * Visual Composer Custom Parameters
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$path = TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/shortcode-params/';

require_once $path . 'vcex-sorter.php';
require_once $path . 'vcex-attach-images.php';
require_once $path . 'vcex-select-buttons.php';
require_once $path . 'vcex-menu-select.php';
require_once $path . 'vcex-orderby-select.php';
require_once $path . 'vcex-font-family-select.php';
require_once $path . 'vcex-image-sizes-select.php';
require_once $path . 'vcex-overlay-select.php';
require_once $path . 'vcex-visibility-select.php';
require_once $path . 'vcex-font-weights-select.php';
require_once $path . 'vcex-shadow-select.php';
require_once $path . 'vcex-social-button-styles-select.php';
require_once $path . 'vcex-hover-css-animations-select.php';

require_once $path . 'vcex-responsive-sizes.php';
require_once $path . 'vcex-ofswitch.php';
require_once $path . 'vcex-trbl-field.php';
require_once $path . 'vcex-number.php';
require_once $path . 'vcex-notice.php';

require_once $path . 'vcex-image-hovers-select.php';
require_once $path . 'vcex-image-filters-select.php';
require_once $path . 'vcex-image-crop-locations-select.php';

require_once $path . 'vcex-grid-columns-select.php';
require_once $path . 'vcex-grid-columns-responsive.php';
require_once $path . 'vcex-grid-columns-gap-select.php';

require_once $path . 'vcex-text-transforms-select.php';
require_once $path . 'vcex-text-alignments-select.php';

require_once $path . 'vcex-button-styles-select.php';
require_once $path . 'vcex-button-colors-select.php';

require_once $path . 'vcex-carousel-arrow-styles-select.php';
require_once $path . 'vcex-carousel-arrow-positions-select.php';

require_once $path . 'vcex-sorter.php';

if ( get_theme_mod( 'cards_enable', true ) ) {
	require_once $path . 'vcex-wpex-card-select.php';
}

if ( defined( 'WPCF7_VERSION' ) ) {
	require_once $path . 'vcex-contact-form-7-select.php';
}