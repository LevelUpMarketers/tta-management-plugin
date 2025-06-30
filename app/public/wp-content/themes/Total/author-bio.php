<?php
/**
 * The template for displaying Author bios.
 *
 * @package Total WordPress Theme
 * @subpackage Templates
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$data = (array) wpex_get_author_box_data();

if ( empty( $data ) ) {
	return;
}

extract( $data );

?>

<section class="author-bio wpex-sm-flex wpex-boxed wpex-mb-40 wpex-text-center wpex-sm-text-left">

	<?php if ( ! empty( $avatar ) ) { ?>

		<div class="author-bio-avatar wpex-flex-shrink-0 wpex-sm-mr-20 wpex-mb-15 wpex-sm-mb-0">

			<?php if ( ! empty( $posts_url ) ) { ?>

				<a href="<?php echo esc_url( $posts_url ); ?>" title="<?php esc_attr_e( 'Visit Author Page', 'total' ); ?>"><?php echo wpex_sanitize_data( $avatar, 'img' ); ?></a>

			<?php } else { ?>

				<?php echo wpex_sanitize_data( $avatar, 'img' ); ?>

			<?php } ?>

		</div>

	<?php } ?>

	<div class="author-bio-content wpex-flex-grow wpex-last-mb-0">

		<?php if ( ! empty( $author_name ) ) { ?>

			<h4 class="author-bio-title wpex-m-0 wpex-mb-10 wpex-text-lg">

				<?php if ( ! empty( $posts_url ) ) { ?>

					<a href="<?php echo esc_url( $posts_url ); ?>" title="<?php esc_attr_e( 'Visit Author Page', 'total' ); ?>"><?php echo strip_tags( $author_name ); ?></a>

				<?php } else { ?>

					<?php echo strip_tags( $author_name ); ?>

				<?php } ?>

			</h4>

		<?php } ?>

		<?php
		// Outputs the author description if one exists
		if ( ! empty( $description ) ) { ?>

			<div class="author-bio-description wpex-mb-15 wpex-last-mb-0"><?php
				echo wpautop( do_shortcode( wp_kses_post( $description ) ) );
			?></div>

		<?php } ?>

		<?php
		// Display author social links if there are social links defined
		wpex_author_box_social_links( $post_author ); ?>

	</div>

</section>