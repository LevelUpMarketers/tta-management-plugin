<?php
/**
 * Staff single related
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get related posts
$wpex_related_query = wpex_staff_single_related_query();

// Display related posts
if ( $wpex_related_query && ! is_wp_error( $wpex_related_query ) && $wpex_related_query->have_posts() ) : ?>

	<div id="staff-single-related" <?php wpex_staff_single_related_class(); ?>>

		<?php
		// Display heading
		wpex_staff_single_related_heading(); ?>

		<div <?php wpex_staff_single_related_row_class(); ?>>

			<?php
			// Set loop instance
			wpex_set_loop_instance( 'related' );

			// Set counter var
			wpex_set_loop_counter();

			// Loop through posts
			foreach( $wpex_related_query->posts as $post ) : setup_postdata( $post );

				// Add to running count
				wpex_increment_loop_running_count();

				// Add to counter
				wpex_increment_loop_counter();

				// Include template part
				get_template_part( 'partials/staff/staff-entry' );

				// Reset counter
				wpex_maybe_reset_loop_counter( wpex_get_array_first_value( get_theme_mod( 'staff_related_columns', '3' ) ) );

			// End loop
			endforeach; ?>

		</div>

	</div>

	<?php
	// Reset data
	wpex_reset_loop_query_vars();
	wp_reset_postdata();

// End have_posts check
endif;