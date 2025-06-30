<?php
/**
 * Single related posts
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! get_theme_mod( 'blog_related', true ) ) {
	return;
}

$wpex_related_query = wpex_blog_single_related_query();

// If the custom query returns post display related posts section
if ( $wpex_related_query && ! is_wp_error( $wpex_related_query ) && $wpex_related_query->have_posts() ) : ?>

	<div <?php wpex_blog_single_related_class(); ?>>

		<?php get_template_part( 'partials/blog/blog-single-related', 'heading' ); ?>

		<div <?php wpex_blog_single_related_row_class(); ?>>

			<?php
			// Set loop instance
			wpex_set_loop_instance( 'related' );

			// Set counter var
			wpex_set_loop_counter();

			// Loop through entries
			foreach( $wpex_related_query->posts as $post ) : setup_postdata( $post );

				// Add to running count
				wpex_increment_loop_running_count();

				// Add to counter
				wpex_increment_loop_counter();

				// Include related entry template
				get_template_part( 'partials/blog/blog-single-related-entry' );

				// Reset counter
				wpex_maybe_reset_loop_counter( wpex_get_array_first_value( wpex_blog_single_related_columns() ) );

			// End loop
			endforeach;

			?>

		</div>

	</div>

	<?php
	// Reset data
	wpex_reset_loop_query_vars();
	wp_reset_postdata();

// End have_posts check
endif;