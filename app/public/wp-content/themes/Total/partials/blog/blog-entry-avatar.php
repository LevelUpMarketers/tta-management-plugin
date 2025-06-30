<?php
/**
 * Blog entry avatar
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$entry_style = wpex_blog_entry_style();

$avatar_size = 75;

if ( 'grid-entry-style' == $entry_style ) {
	$avatar_size = 60;
}

?>

<div <?php wpex_blog_entry_avatar_class(); ?>>
	<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" title="<?php esc_attr_e( 'Visit Author Page', 'total' ); ?>">
		<?php echo get_avatar(
			get_the_author_meta( 'user_email' ),
			apply_filters( 'wpex_blog_entry_author_avatar_size', $avatar_size ),
			'',
			'',
			array(
				'class' => 'wpex-round',
			)
		); ?>
	</a>
</div>