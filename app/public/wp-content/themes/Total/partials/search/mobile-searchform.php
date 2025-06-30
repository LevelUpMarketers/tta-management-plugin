<?php
/**
 * Searchform for the mobile sidebar menu
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$placeholder = apply_filters( 'wpex_mobile_searchform_placeholder', esc_html__( 'Search', 'total' ), 'mobile' );
$action      = apply_filters( 'wpex_search_action', esc_url( home_url( '/' ) ), 'mobile' );

?>

<div id="mobile-menu-search" class="wpex-hidden wpex-clr">
	<form method="get" action="<?php echo esc_attr( $action ); ?>" class="mobile-menu-searchform">
		<input type="search" name="s" autocomplete="off" placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php wpex_aria_label( 'mobile_menu_search' ); ?> />
		<?php if ( defined( 'ICL_LANGUAGE_CODE' ) ) { ?>
			<input type="hidden" name="lang" value="<?php echo( ICL_LANGUAGE_CODE ); ?>"/>
		<?php } ?>
		<?php if ( WPEX_WOOCOMMERCE_ACTIVE && get_theme_mod( 'woo_header_product_searchform', false ) ) { ?>
			<input type="hidden" name="post_type" value="product" />
		<?php } ?>
		<button type="submit" class="searchform-submit"<?php wpex_aria_label( 'search_submit' ); ?>><span class="ticon ticon-search"></span></button>
	</form>
</div>