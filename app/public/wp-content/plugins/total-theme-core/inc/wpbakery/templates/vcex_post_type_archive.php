<?php
/**
 * vcex_post_type_archive shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_post_type_archive';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Store orginal atts value for use in non-builder params
$og_atts = $atts;

// Define entry counter
$entry_count = ! empty( $og_atts['entry_count'] ) ? $og_atts['entry_count'] : 0;

// Set entry counter
if ( function_exists( 'wpex_set_loop_counter' ) ) {
	wpex_set_loop_counter( $entry_count );
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

// Add paged attribute for load more button (used for WP_Query)
if ( ! empty( $og_atts['paged'] ) ) {
	$atts['paged'] = $og_atts['paged'];
}

// Build the WordPress query
$vcex_query = vcex_build_wp_query( $atts );

// Output posts
if ( $vcex_query->have_posts() ) :

	// Define loop type
	$loop_type = $post_type;

	// If loop_type is "post" rename to "blog"
	if ( 'post' == $post_type ) {
		$loop_type = 'blog';
	}

	// Wrapper classes
	$wrap_classes = array(
		'vcex-module',
		'vcex-post-type-archive',
		'wpex-clr',
	);

	if ( $bottom_margin ) {
		$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
	}

	if ( $css_animation && 'none' != $css_animation ) {
   		$wrap_classes[] = vcex_get_css_animation( $atts['css_animation'] );
	}

	if ( $classes ) {
	    $wrap_classes[] = vcex_get_extra_class( $classes );
	}

	if ( $visibility ) {
	    $wrap_classes[] = sanitize_html_class( $visibility );
	}

	$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts ); ?>

	<div class="<?php echo esc_attr( $wrap_classes ); ?>"<?php vcex_unique_id( $atts['unique_id'] ); ?>>

		<?php
		//Heading
		if ( ! empty( $heading ) ) {
			echo vcex_get_module_header( array(
				'style'   => isset( $header_style ) ? $header_style : '',
				'content' => $heading,
				'classes' => array(
					'vcex-module-heading',
					'vcex_post_type_archive-heading'
				),
			) );
		}

		// Get loop top
		get_template_part( 'partials/loop/loop-top', $loop_type );

			// Loop through posts
			while ( $vcex_query->have_posts() ) :

				// Get post from query
				$vcex_query->the_post();

				// Create new post object.
				$post = new stdClass();

					// Get content template part
					get_template_part( 'partials/loop/loop', $loop_type );

			// End loop
			endwhile;

		// Get loop bottom
		get_template_part( 'partials/loop/loop-bottom', $loop_type );

		/*--------------------------------*/
		/* [ Pagination ]
		/*--------------------------------*/

		// Load more button
		if ( vcex_validate_boolean( $pagination_loadmore ) ) {

			if ( ! empty( $vcex_query->max_num_pages ) ) {

				vcex_loadmore_scripts();

				if ( function_exists( 'wpex_get_loop_counter' ) ) {
					$og_atts['entry_count'] = wpex_get_loop_counter(); // pass counter to ajax
				}

				echo vcex_get_loadmore_button( $shortcode_tag, $og_atts, $vcex_query );

			}

		}

		// Standard pagination
		elseif ( 'true' == $pagination || ( 'true' == $custom_query && ! empty( $vcex_query->query['pagination'] ) ) ) {

			echo vcex_pagination( $vcex_query, false );

		}

		?>

	</div>

	<?php
	// Reset the post data to prevent conflicts with WP globals
	wp_reset_postdata(); ?>

<?php
// If no posts are found display message
else : ?>

	<?php
	// Display no posts found error if function exists
	echo vcex_no_posts_found_message( $atts ); ?>

<?php
// End post check
endif;