<?php
/**
 * Contains a list of all custom WPBakery modules.
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return array of vcex modules.
 *
 * @todo rename to vcex_shortcodes_list
 */
function vcex_builder_modules() {

	$modules = array(

		// Standard shortcodes
		'heading',
		'button',
		'divider',
		'wpex_post_cards',
		'alert',
		'animated_text',
		'author_bio',
		'blog_carousel',
		'blog_grid',
		'breadcrumbs',
		'bullets',
		'callout',
		'countdown',
		'column_side_border',
		'custom_field',
		'divider_dots',
		'divider_multicolor',
		'feature_box',
		'form_shortcode',
		'icon_box',
		'icon',
		'image',
		'image_banner',
		'image_before_after',
		'image_carousel',
		'image_flexslider',
		'image_galleryslider',
		'image_grid',
		'image_swap',
		'leader',
		'list_item',
		'login_form',
		'milestone',
		'multi_buttons',
		'navbar',
		'newsletter_form',
		'portfolio_carousel',
		'portfolio_grid',
		'post_type_archive',
		'post_type_carousel',
		'post_type_grid',
		'post_type_slider',
		'pricing',
		'recent_news',
		'searchbar',
		'shortcode',
		'skillbar',
		'social_links',
		'spacing',
		'staff_carousel',
		'staff_grid',
		'staff_social',
		'teaser',
		'terms_carousel',
		'terms_grid',
		'testimonials_carousel',
		'testimonials_grid',
		'testimonials_slider',
		'users_grid',

		// Dynamic post modules
		'page_title',
		'post_comments',
		'post_content',
		'post_media',
		'post_meta',
		'post_next_prev',
		'post_series',
		'post_terms',
		'social_share',

		// Dynamic archive modules
		'term_description',

		// Custom Grid items
		'grid_item-post_excerpt',
		'grid_item-post_meta',
		'grid_item-post_terms',
		'grid_item-post_video',

	);

	if ( class_exists( 'WooCommerce' ) ) {
		$modules[] = 'woocommerce_carousel';
		$modules[] = 'woocommerce_loop_carousel';
	}

	return (array) apply_filters( 'vcex_builder_modules', $modules );

}