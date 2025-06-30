<?php
/**
 * Customizer Pages Select Control
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Customizer_Dropdown_Pages' ) ) {

	class WPEX_Customizer_Dropdown_Pages extends WP_Customize_Control {

		/**
		 * Define control type.
		 */
		public $type = 'wpex-dropdown-pages';

		/**
		 * Whether to include templates in the dropdown or not.
		 */
		public $include_templates = false;

		/**
		 * Render the content
		 */
		public function render_content() { ?>

			<label class="customize-control-select">

			<?php if ( ! empty( $this->label ) ) { ?>

				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

			<?php } ?>

			<?php
			// Description
			if ( ! empty( $this->description ) ) { ?>

				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>

			<?php } ?>

			<div class="wpex-customizer-chosen-select">

				<?php if ( $this->include_templates && post_type_exists( 'templatera' ) ) { ?>

					<select <?php $this->link(); ?>>

						<option value="">&#8211; <?php esc_html_e( 'Select', 'total' ); ?> &#8211;</option>

						<?php
						// Add Templatera templates
						$templatera_templates = new WP_Query( array(
							'posts_per_page' => -1,
							'post_type'      => 'templatera',
						) );

						if ( $templatera_templates->have_posts() ) { ?>

							<optgroup label="<?php esc_html_e( 'WPBakery Templates', 'total' ); ?>">

								<?php while ( $templatera_templates->have_posts() ) {

									$templatera_templates->the_post();

									echo '<option value="' . intval( get_the_ID() ) . '"' . selected( $this->value(), get_the_ID(), false ) . '>' . esc_html( get_the_title() ) . '</option>';

								}
								wp_reset_postdata(); ?>

							</optgroup>

						<?php } ?>

						<optgroup label="<?php esc_html_e( 'Pages', 'total' ); ?>">
							<?php
							$pages = get_pages( array(
								'exclude' => get_option( 'page_on_front' ),
							) );
							if ( $pages ) {
								foreach ( $pages as $page ) {
									echo '<option value="' . absint( $page->ID ) . '"' . selected( $this->value(), $page->ID, false ) . '>' . esc_html( $page->post_title ) . '</option>';
								}
							} ?>
						</optgroup>

					</select>

				<?php } else {

					$dropdown = wp_dropdown_pages( array(
						'name'              => '_customize-dropdown-pages-' . $this->id,
						'echo'              => 0,
						'show_option_none'  => '&mdash; ' . esc_html__( 'Select', 'total' ) . ' &mdash;',
						'option_none_value' => '0',
						'selected'          => $this->value(),
					) );

					// Hackily add in the data link parameter.
					echo str_replace( '<select', '<select ' . $this->get_link(), $dropdown );

				} ?>

			</div>

		<?php }
	}

}