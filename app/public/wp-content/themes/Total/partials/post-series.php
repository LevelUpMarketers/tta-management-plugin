<?php
/**
 * Post Series
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Return if taxonomy doesn't exist
if ( ! taxonomy_exists( 'post_series' ) ) {
	return;
}

// Get post ID
$post_id = get_the_ID();

// Return if pass protected
if ( post_password_required( $post_id ) ) {
	return;
}

// Get post terms
$terms = get_the_terms( $post_id, 'post_series' );

// Return if not term found
if ( empty( $terms ) || is_wp_error( $terms ) ) {
	return;
}

// Post query args
$args = apply_filters( 'wpex_post_series_query_args', array(
	'post_type'        => get_post_type(),
	'posts_per_page'   => -1,
	'orderby'          => 'Date',
	'order'            => 'ASC',
	'no_found_rows'    => true,
	'tax_query'        => array( array(
		'taxonomy' => 'post_series',
		'field'    => 'id',
		'terms'    => $terms[0]->term_id
	) ),
) );

// Get all posts in series
$wpex_query = new wp_query( $args );

// Display series if posts are found
if ( $wpex_query->have_posts() ) : ?>

	<div class="wpex-post-series-toc wpex-boxed wpex-p-30">

		<div class="wpex-post-series-toc-header wpex-text-xl wpex-font-semibold wpex-mb-15"><a class="wpex-text-gray-900 wpex-no-underline" href="<?php echo esc_url( get_term_link( $terms[0], 'post_series' ) ); ?>"><?php echo esc_html( $terms[0]->name ); ?></a></div>

		<div class="wpex-post-series-toc-list wpex-last-mb-0"><?php

			// Define counter var
			$count=0;

			// Loop through posts
			foreach( $wpex_query->posts as $post ) : setup_postdata( $post );

				// Add to counter
				$count++;

				// Check if we are on the current item
				$is_current = ( $post->ID == $post_id ) ? true : false;

				// Entry classes
				$entry_classes = array(
					'wpex-post-series-toc-entry',
					'wpex-mb-5',
				);

				if ( $is_current ) {
					$entry_classes[] = 'wpex-active';
				}

				?>

				<div class="<?php echo esc_attr( implode( ' ', $entry_classes ) ); ?>">
					<span class="post-series-count wpex-font-medium"><?php echo absint( $count ); ?>.</span>
					<?php if ( $is_current ) { ?>
						<?php the_title(); ?>
					<?php } else { ?>
						<a href="<?php wpex_permalink(); ?>" title="<?php wpex_esc_title(); ?>"><?php the_title(); ?></a>
					<?php } ?>
				</div>

			<?php endforeach;

		?></div>

	</div>

<?php endif; ?>

<?php
// Reset post data to prevent conflicts with other queries
wp_reset_postdata();