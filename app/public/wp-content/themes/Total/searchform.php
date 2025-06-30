<?php
/**
 * The template for displaying search forms
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$placeholder = apply_filters( 'wpex_search_placeholder_text', esc_html__( 'Search', 'total' ), 'main' );
$action      = apply_filters( 'wpex_search_action', esc_url( home_url( '/' ) ), 'main' ); ?>

<form method="get" class="searchform" action="<?php echo esc_attr( $action ); ?>"<?php wpex_aria_landmark( 'searchform' ); ?>>
	<label>
		<span class="screen-reader-text"><?php echo esc_html( $placeholder ); ?></span>
		<input type="search" class="field" name="s" placeholder="<?php echo esc_attr( $placeholder ); ?>" />
	</label>
	<?php if ( defined( 'ICL_LANGUAGE_CODE' ) ) : ?>
		<input type="hidden" name="lang" value="<?php echo( ICL_LANGUAGE_CODE ); ?>"/>
	<?php endif; ?>
	<?php do_action( 'wpex_searchform_fields' ); ?>
	<button type="submit" class="searchform-submit"><span class="ticon ticon-search" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Submit', 'total' ); ?></span></button>
</form>