<?php
/**
 * Page subheading output
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$subheading = wpex_page_header_subheading_content();

if ( ! $subheading ) {
	return;
}

?>

<div <?php wpex_page_header_subheading_class(); ?>><?php echo do_shortcode( wp_kses_post( $subheading ) ); ?></div>