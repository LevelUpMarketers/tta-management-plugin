<?php
/**
 * Single blog meta
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$sections = wpex_blog_single_meta_sections();

if ( empty( $sections ) ) {
	return;
}

?>

<ul <?php wpex_blog_single_meta_class(); ?>>

	<?php
	// Loop through meta sections
	foreach ( $sections as $key => $val ) : ?>

		<?php
		// Display Date
		if ( 'date' === $val ) : ?>

			<li class="meta-date"><span class="ticon ticon-clock-o" aria-hidden="true"></span><time class="updated" datetime="<?php the_date('Y-m-d'); ?>"<?php wpex_schema_markup( 'publish_date' ); ?>><?php echo get_the_date(); ?></time></li>

		<?php
		// Display Author
		elseif ( 'author' === $val ) : ?>

			<li class="meta-author"><span class="ticon ticon-user-o" aria-hidden="true"></span><span class="vcard author"<?php wpex_schema_markup( 'author_name' ); ?>><span class="fn"><?php the_author_posts_link(); ?></span></span></li>

		<?php
		// Display Categories
		elseif ( 'categories' === $val ) : ?>

			<li class="meta-category"><span class="ticon ticon-folder-o" aria-hidden="true"></span><?php wpex_list_post_terms( 'category', true ); ?></li>

		<?php
		// Display First Category
		elseif ( 'first_category' === $val ) : ?>

			<?php wpex_first_term_link( array(
				'taxonomy' => 'category',
				'before'   => '<li class="meta-category"><span class="ticon ticon-folder-o" aria-hidden="true"></span>',
				'after'    => '</li>',
				'instance' => 'blog_single_meta',
			) ); ?>

		<?php
		// Display Comments Count
		elseif ( 'comments' === $val ) : ?>

			<?php if ( comments_open() && ! post_password_required() ) { ?>

				<li class="meta-comments comment-scroll"><span class="ticon ticon-comment-o" aria-hidden="true"></span><?php comments_popup_link( esc_html__( '0 Comments', 'total' ), esc_html__( '1 Comment',  'total' ), esc_html__( '% Comments', 'total' ), 'comments-link' ); ?></li>

			<?php } ?>

		<?php
		// Display Custom Meta Block
		elseif ( $key !== 'meta' ) :

			// Note: Callable check needs to be here because of 'date'
			if ( is_callable( $val ) ) { ?>

				<li class="meta-<?php echo esc_attr( $key ); ?>"><?php echo call_user_func( $val ); ?></li>

			<?php } else { ?>

				<li class="meta-<?php echo esc_attr( $val ); ?>"><?php get_template_part( 'partials/meta/' . $val ); ?></li>

			<?php } ?>

		<?php endif; ?>

	<?php endforeach; ?>

</ul>