<?php
/**
 * Returns the page header title.
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$args = wpex_page_header_title_args();

// If string is empty there is no title to display.
if ( empty( $args['string'] ) ) {
	return;
}

$tag_escaped = ! empty( $args['html_tag'] ) ? tag_escape( $args['html_tag'] ) : 'div';

?>

<?php wpex_hook_page_header_title_before(); ?>

<<?php echo $tag_escaped; ?> <?php wpex_page_header_title_class(); ?><?php echo wp_strip_all_tags( $args['schema_markup'] ); ?>>

	<span><?php echo do_shortcode( wp_kses_post( $args['string'] ) ); ?></span>

</<?php echo $tag_escaped; ?>>

<?php wpex_hook_page_header_title_after(); ?>