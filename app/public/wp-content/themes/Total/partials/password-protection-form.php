<?php
/**
 * Custom WordPress password protection form output
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Add label based on post ID
global $post;
$post  = get_post( $post );
$label = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );

// Main classes
$classes = 'password-protection-box wpex-boxed wpex-mb-40 wpex-clr';

// Add container for full-screen layout to center it
if ( 'full-screen' == wpex_content_area_layout() ) {
	$classes .= ' container';
}

// IMPORTANT NOTE: You can't add spacing between the input/button because WP will add <p> tags to each element

?>

<div class="<?php echo esc_attr( $classes ); ?>">

	<form class="password-protection-box-form wpex-last-mb-0" action="<?php echo esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ); ?>" method="post">

		<div class="wpex-heading wpex-text-lg wpex-mb-5"><?php esc_html_e( 'Password Protected', 'total' ); ?></div>

		<p class="wpex-mb-15"><?php esc_html_e( 'This content is password protected. To view it please enter your password below:', 'total' ); ?></p>

		<div class="wpex-md-flex"><input class="wpex-flex-grow wpex-w-100 wpex-md-mr-15 wpex-mb-15 wpex-md-mb-0" name="post_password" id="<?php echo esc_attr( $label ); ?>" type="password" size="20" maxlength="20" placeholder="<?php esc_attr_e( 'Password', 'total' ); ?>" /><input class="wpex-w-100 wpex-md-w-25" type="submit" name="Submit" value="<?php esc_attr_e( 'Submit', 'total' ); ?>" /></div>

	</form>

</div>