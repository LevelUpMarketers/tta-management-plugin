<?php
/**
 * Taxonomy Terms Widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class Widget_Taxonomy_Terms extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_taxonomy_terms',
			'name'    => $this->branding() . esc_html__( 'Taxonomy Terms', 'total-theme-core' ),
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
					'id'      => 'taxonomy',
					'label'   => esc_html__( 'Taxonomy', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'taxonomies',
				),
				array(
					'id'      => 'name_font_size',
					'label'   => esc_html__( 'Name Font Size', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'utl_font_size',
				),
				array(
					'id'      => 'description',
					'label'   => esc_html__( 'Show Description?', 'total-theme-core' ),
					'type'    => 'checkbox',
					'default' => 1,
				),
				array(
					'id'      => 'desc_font_size',
					'label'   => esc_html__( 'Description Font Size', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'utl_font_size',
				),
				array(
					'id'      => 'count',
					'label'   => esc_html__( 'Show Count?', 'total-theme-core' ),
					'type'    => 'checkbox',
					'default' => 1,
				),
				array(
					'id'      => 'count_accent_bg',
					'label'   => esc_html__( 'Use Accent Color for Count?', 'total-theme-core' ),
					'type'    => 'checkbox',
					'default' => 0,
				),
				array(
					'id'      => 'hide_empty',
					'label'   => esc_html__( 'Hide Empty Categories?', 'total-theme-core' ),
					'type'    => 'checkbox',
					'default' => 1,
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

		// Sanitize some widget args
		$name_font_size = ! empty( $name_font_size ) ? $name_font_size : 'lg';
		$desc_font_size = ! empty( $desc_font_size ) ? $desc_font_size : 'sm';

		// Check if Taxonomy exists
		if ( $taxonomy && taxonomy_exists( $taxonomy ) ) :

			$terms = get_terms( array(
				'taxonomy' => $taxonomy,
				'hide_empty' => wp_validate_boolean( $hide_empty ),
			) );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) : ?>

				<ul class="wpex-taxonomy-terms-widget">

					<?php foreach ( $terms as $term ) : ?>

						<li>
							<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" title="<?php echo esc_html( $term->name ); ?>" class="wpex-inherit-color wpex-no-underline wpex-block wpex-p-10 wpex-hover-bg-gray-100">

								<div class="wpex-taxonomy-terms-widget-title wpex-flex wpex-justify-between wpex-items-center">

									<div class="wpex-taxonomy-terms-widget-name wpex-text-<?php echo sanitize_html_class( $name_font_size ); ?> wpex-font-medium"><?php echo esc_html( $term->name ); ?></div>

									<?php
									// Display Count
									if ( wp_validate_boolean( $count ) ) {

										$count_class = array(
											'wpex-inline-block',
											'wpex-text-white',
											'wpex-rounded',
											'wpex-px-5',
											'wpex-leading-normal',
										);

										if ( wp_validate_boolean( $count_accent_bg ) ) {
											$count_class[] = 'wpex-bg-accent';
										} else {
											$count_class[] = 'wpex-bg-gray-600';
										} ?>

										<div class="wpex-taxonomy-terms-widget-count wpex-ml-15"><span class="<?php echo esc_attr( implode( ' ', $count_class ) ) ?>"><?php echo absint( $term->count ); ?></span></div>

									<?php } ?>

								</div>

								<?php
								// Display description
								if ( wp_validate_boolean( $description ) ) { ?>
									<div class="wpex-taxonomy-terms-widget-desc wpex-text-<?php echo sanitize_html_class( $desc_font_size ); ?>"><?php echo wp_strip_all_tags( $term->description ); ?></div>
								<?php } ?>

							</a>
						</li>

					<?php endforeach; ?>

				</ul>


			<?php

			// End terms check
			endif;

		// End taxonomy check
		endif;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\Widget_Taxonomy_Terms' );