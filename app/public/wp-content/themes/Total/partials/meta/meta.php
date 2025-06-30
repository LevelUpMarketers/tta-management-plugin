<?php
/**
 * Post meta (date, author, comments, etc) for custom post types.
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get meta blocks
$blocks = wpex_meta_blocks();

// Make sure we have blocks and it's an array
if ( ! empty( $blocks ) && is_array( $blocks ) ) : ?>

	<ul <?php wpex_cpt_meta_class(); ?>>

		<?php
		// Loop through meta sections
		foreach ( $blocks as $key => $val ) : ?>

			<?php
			// Date
			if ( 'date' == $val ) : ?>

				<li class="meta-date"><span class="ticon ticon-clock-o" aria-hidden="true"></span><time class="updated" datetime="<?php esc_attr( the_date( 'Y-m-d' ) ); ?>"<?php wpex_schema_markup( 'publish_date' ); ?>><?php echo get_the_date(); ?></time></li>

			<?php
			// Author
			elseif ( 'author' == $val ) : ?>

				<li class="meta-author"><span class="ticon ticon-user-o" aria-hidden="true"></span><span class="vcard author"<?php wpex_schema_markup( 'author_name' ); ?>><span class="fn"><?php the_author_posts_link(); ?></span></span></li>

			<?php
			// Categories
			elseif ( 'categories' == $val ) : ?>

				<?php if ( $taxonomy = apply_filters( 'wpex_meta_categories_taxonomy', wpex_get_post_type_cat_tax() ) ) { ?>

					<?php wpex_list_post_terms( array(
						'taxonomy' => $taxonomy,
						'before'   => '<li class="meta-category"><span class="ticon ticon-folder-o" aria-hidden="true"></span>',
						'after'    => '</li>',
						'instance' => 'meta',
					) ); ?>

				<?php } ?>

			<?php
			// Comments
			elseif ( 'comments' == $val ) : ?>

				<?php if ( comments_open() && ! post_password_required() ) { ?>

					<li class="meta-comments comment-scroll"><span class="ticon ticon-comment-o" aria-hidden="true"></span><?php comments_popup_link( esc_html__( '0 Comments', 'total' ), esc_html__( '1 Comment',  'total' ), esc_html__( '% Comments', 'total' ), 'comments-link' ); ?></li>

				<?php } ?>

			<?php
			// Custom meta block (can not be named "meta" or it will cause infinite loop)
			elseif ( $key != 'meta' ) :

				if ( is_callable( $val ) ) { ?>

					<li class="meta-<?php echo esc_attr( $key ); ?>"><?php echo call_user_func( $val ); ?></li>

				<?php } else { ?>

					<li class="meta-<?php echo esc_attr( $val ); ?>"><?php get_template_part( 'partials/meta/'. $val ); ?></li>

				<?php } ?>

			<?php endif; ?>

		<?php endforeach; ?>

	</ul>

<?php endif; ?>