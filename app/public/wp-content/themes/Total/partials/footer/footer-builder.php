<?php
/**
 * Footer builder output
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get footer builder ID
$id = wpex_footer_builder_id();

// Return if no id defined or not in editor mode
if ( ! $id && empty( $_GET[ 'wpex_inline_footer_template_editor' ] ) ) {
	return;
}

?>

<footer id="footer-builder" class="footer-builder wpex-clr"<?php wpex_schema_markup( 'footer' ); ?>>
	<div class="footer-builder-content container entry wpex-clr">
		<?php
		if ( wpex_is_footer_builder_page() && ( wpex_vc_is_inline() || wpex_elementor_is_preview_mode() ) ) {
			while ( have_posts() ) : the_post();
				the_content();
			endwhile;
		} else {
			if ( 'elementor_library' == get_post_type( $id ) && class_exists( 'Elementor\Frontend' ) ) {
				echo wpex_get_elementor_content_for_display( $id );
			} else {
				$content = wpex_parse_vc_content( get_post_field( 'post_content', $id ) ); // remove weird p tags and extra code
				$content = wp_kses_post( $content ); // security sanitization
				$content = do_shortcode( $content ); // parse shortcodes
				echo apply_filters( 'wpex_footer_builder_content', $content ); // apply filters and return content
			}
		} ?>
	</div>
</footer>