<?php
/**
 * No posts found
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( is_search() ) {
	$text = esc_html__( 'No results were found for this query.', 'total' );
} else {
	$text = esc_html__( 'No Posts found.', 'total' );
}

?>

<div class="wpex-no-posts-found wpex-text-md wpex-mb-20"><?php echo apply_filters( 'wpex_no_posts_found_text', $text ); ?></div>