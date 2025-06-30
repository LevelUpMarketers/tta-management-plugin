<?php
/**
 * Post With Format Icons widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;
use WP_Query;

defined( 'ABSPATH' ) || exit;

class PostsWithFormatIcons extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_recent_posts_icons',
			'name' => $this->branding() . esc_html__( 'Posts With Icons', 'total-theme-core' ),
			'options' => array(
				'customize_selective_refresh' => true,
			),
			'fields'  => array(
				array(
					'id'    => 'title',
					'label' => esc_html__( 'Title', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'      => 'display_icon',
					'label'   => esc_html__( 'Display Icon', 'total-theme-core' ),
					'type'    => 'checkbox',
					'default' => 'on',
				),
				array(
					'id'      => 'number',
					'label'   => esc_html__( 'Number', 'total-theme-core' ),
					'type'    => 'number',
					'default' => '5',
				),
				array(
					'id'      => 'order',
					'label'   => esc_html__( 'Order', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'query_order',
					'default' => 'DESC',
				),
				array(
					'id'      => 'orderby',
					'label'   => esc_html__( 'Order by', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'query_orderby',
					'default' => 'date',
				),
				array(
					'id'      => 'category',
					'label'   => esc_html__( 'Category', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'categories',
				),
				array(
					'id'       => 'post_type',
					'label'    => esc_html__( 'Post Type', 'total-theme-core' ),
					'type'     => 'select',
					'choices'  => 'post_types',
					'default'  => 'post',
				),
				array(
					'id'      => 'taxonomy',
					'label'   => esc_html__( 'Query By Taxonomy', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'taxonomies',
				),
				array(
					'id'          => 'terms',
					'label'       => esc_html__( 'Include Terms', 'total-theme-core' ),
					'type'        => 'text',
					'description' => esc_html__( 'Enter a comma seperated list of terms.', 'total-theme-core' ),
				),
				array(
					'id'          => 'terms_exclude',
					'label'       => esc_html__( 'Exclude Terms', 'total-theme-core' ),
					'type'        => 'text',
					'description' => esc_html__( 'Enter a comma seperated list of terms.', 'total-theme-core' ),
				),
			),
		);

		$this->create_widget( $this->args );

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @since 1.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// Parse and extract widget settings
		extract( $this->parse_instance( $instance ) );

		// Before widget hook
		echo wp_kses_post( $args['before_widget'] );

		// Display widget title
		$this->widget_title( $args, $instance );

		// Define widget output
		$output = '';

		// Query Args
		$query_args = array(
			'post_type'           => ! empty( $post_type ) ? $post_type : 'post',
			'posts_per_page'      => $number,
			'orderby'             => $orderby,
			'order'               => $order,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => 1,
			'tax_query'      => array(
				'relation' => 'AND',
			),
		);

		// Exclude current post
		if ( is_singular() ) {
			$query_args['post__not_in'] = array( get_the_ID() );
		}

		// Query by category
		if ( 'post' == $post_type && ! empty( $category ) && 'all' != $category ) {
			$query_args['tax_query'] = array( array(
				'taxonomy' => 'category',
				'field'    => 'id',
				'terms'    => $category,
			) );
		}

		// Tax Query
		if ( ! empty( $taxonomy ) ) {

			// Include Terms
			if (  ! empty( $terms ) ) {

				// Sanitize terms and convert to array
				$terms = str_replace( ', ', ',', $terms );
				$terms = explode( ',', $terms );

				// Add to query arg
				$query_args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $terms,
					'operator' => 'IN',
				);

			}

			// Exclude Terms
			if ( ! empty( $terms_exclude ) ) {

				// Sanitize terms and convert to array
				$terms_exclude = str_replace( ', ', ',', $terms_exclude );
				$terms_exclude = explode( ',', $terms_exclude );

				// Add to query arg
				$query_args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $terms_exclude,
					'operator' => 'NOT IN',
				);

			}

		}

		// Get posts
		$wpex_query = new WP_Query( $query_args );

		// Loop through posts
		if ( $wpex_query->have_posts() ) {

			$output .= '<ul class="widget-recent-posts-icons wpex-bordered-list wpex-clr">';

				$count = 0;

				while ( $wpex_query->have_posts() ) : $wpex_query->the_post();

					$count ++;

					$post_link = function_exists( 'wpex_get_permalink' ) ? wpex_get_permalink() : get_the_permalink();

					$output .= '<li class="widget-recent-posts-icons-li">';

						$output .= '<a class="wpex-flex" href="' . esc_url( $post_link ) . '">';

							if ( $display_icon && function_exists( 'wpex_get_post_format_icon' ) ) {

								$output .= '<div class="widget-recent-posts-icons-icon wpex-flex-shrink-0 wpex-mr-10">';

									$output .= '<span class="' . esc_attr( wpex_get_post_format_icon() ) . '" aria-hidden="true"></span>';

								$output .= '</div>';

							}

							$output .= '<div class="widget-recent-posts-icons-title">' . esc_html( get_the_title() ) . '</div>';

						$output .= '</a>';

					$output .= '</li>';

				endwhile;

			$output .= '</ul>';

			// Reset post data
			wp_reset_postdata();

		}

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\PostsWithFormatIcons' );