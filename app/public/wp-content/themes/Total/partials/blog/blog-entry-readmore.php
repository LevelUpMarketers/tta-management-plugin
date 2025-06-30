<?php
/**
 * Blog entry layout
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 * @todo create helper function for showing/hiding readmore legacy arrow
 */

defined( 'ABSPATH' ) || exit;

// Vars
$text = wpex_get_translated_theme_mod( 'blog_entry_readmore_text' );
$text = $text ? $text : esc_html__( 'Read More', 'total' );

// Apply filters for child theming
$text = apply_filters( 'wpex_post_readmore_link_text', $text );

// Return if no text
if ( ! $text ) {
	return;
}

?>

<div <?php wpex_blog_entry_button_wrap_class(); ?>>
	<a href="<?php wpex_permalink(); ?>" <?php wpex_blog_entry_button_class(); ?>><?php echo esc_html( $text ); ?><span class="readmore-rarr wpex-hidden wpex-ml-10">&rarr;</span></a>
</div>