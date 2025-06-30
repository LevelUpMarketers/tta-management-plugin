<?php
/**
 * Visual Composer WooCommerce Loop Carousel
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 *
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_woocommerce_loop_carousel';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// WooCommerce Only
if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

// Helps speed up rendering in backend of VC
if ( is_admin() && ! wp_doing_ajax() ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Define vars
$atts['post_type'] = 'product';
$atts['taxonomy']  = 'product_cat';
$atts['tax_query'] = '';

// Custom query_products_by argument
if ( $atts['query_products_by'] ) {
	if ( 'featured' == $atts['query_products_by'] ) {
		$atts['featured_products_only'] = true;
	} elseif ( 'on_sale' == $atts['query_products_by'] ) {
		if ( function_exists( 'wc_get_product_ids_on_sale' ) ) {
			$atts['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		}
	}
}

// Extract attributes
extract( $atts );

if ( 'woo_top_rated' == $atts['orderby'] ) {
	add_filter( 'posts_clauses', array( 'WC_Shortcodes', 'order_by_rating_post_clauses' ) );
}

// Build the WordPress query
$vcex_query = vcex_build_wp_query( $atts );

if ( 'woo_top_rated' == $atts['orderby'] ) {
	remove_filter( 'posts_clauses', array( 'WC_Shortcodes', 'order_by_rating_post_clauses' ) );
}

// Output posts
if ( $vcex_query->have_posts() ) :

	// Enqueue scripts
	vcex_enqueue_carousel_scripts();

	// Wrap Classes
	$wrap_classes = array(
		'vcex-module',
		'wpex-carousel',
		'wpex-carousel-woocommerce-loop',
		'owl-carousel',
		'products',
		'wpex-clr'
	);

	if ( 'true' == $arrows ) {
		$wrap_classes[] = $arrows_style ? 'arrwstyle-' . $arrows_style : 'arrwstyle-default';
		if ( $arrows_position && 'default' != $arrows_position ) {
			$wrap_classes[] = 'arrwpos-' . $arrows_position;
		}
	}

	if ( $visibility ) {
		$wrap_classes[] = $visibility;
	}

	if ( $css_animation && 'none' != $css_animation ) {
		$wrap_classes[] = vcex_get_css_animation( $css_animation );
	}

	if ( $classes ) {
		$wrap_classes[] = vcex_get_extra_class( $classes );
	}

	// Disable autoplay
	if ( vcex_vc_is_inline() || '1' == count( $vcex_query->posts ) ) {
		$atts['auto_play'] = false;
	}

	// VC filter
	$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), 'vcex_woocommerce_carousel', $atts ); ?>

	<div class="woocommerce clr">

		<ul class="<?php echo esc_attr( $wrap_classes ); ?>" data-wpex-carousel="<?php echo vcex_get_carousel_settings( $atts, $shortcode_tag ); ?>"<?php vcex_unique_id( $unique_id ); ?>>

			<?php
			// Loop through posts
			$count=0;
			while ( $vcex_query->have_posts() ) :
				$count++;

				// Get post from query
				$vcex_query->the_post();

				// Create new post object.
				$post = new stdClass();

				// Get post data
				$get_post = get_post(); ?>

				<?php
				// Get woocommerce template part
				if ( function_exists( 'wc_get_template_part' ) ) {
					wc_get_template_part( 'content', 'product' );
				} ?>

			<?php endwhile; ?>

		</ul>

	</div>

	<?php
	// Remove post object from memory
	$post = null;

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
endif; ?>