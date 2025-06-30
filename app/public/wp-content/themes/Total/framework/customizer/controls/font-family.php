<?php
/**
 * Customizer Font Family Control
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Fonts_Dropdown_Custom_Control' ) ) {

	class WPEX_Fonts_Dropdown_Custom_Control extends WP_Customize_Control {

		/**
		 * The control type.
		 *
		 * @access public
		 * @var string
		 */
		public $type = 'wpex-font-family';

		/**
		 * Render the content
		 *
		 * @access public
		 */
		public function render_content() {

			$this_val = $this->value();

			$value_exists = false;

			?>

			<label><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span></label>

			<div class="wpex-customizer-chosen-select">

				<select <?php $this->link(); ?>>

					<option value="" <?php if ( ! $this_val ) echo 'selected="selected"'; ?>><?php esc_html_e( 'Default', 'total' ); ?></option>

					<?php
					// User-defined fonts
					$user_fonts = wpex_get_registered_fonts();
					if ( ! empty( $user_fonts ) ) { ?>
						<optgroup label="<?php esc_html_e( 'My Fonts', 'total' ); ?>">
							<?php foreach ( $user_fonts as $font_name => $font_settings ) {
								if ( $font_name === $this_val ) {
									$value_exists = true;
								} ?>
								<option value="<?php echo esc_attr( $font_name ); ?>" <?php selected( esc_attr( $font_name ), $this_val, true ); ?>><?php echo esc_html( $font_name ); ?></option>
							<?php } ?>
						</optgroup>
					<?php } ?>

					<?php
					// Add custom fonts from child themes
					$fonts = wpex_add_custom_fonts();
					if ( $fonts && is_array( $fonts ) ) { ?>
						<optgroup label="<?php esc_html_e( 'Custom Fonts', 'total' ); ?>">
							<?php foreach ( $fonts as $font ) {
								if ( $font === $this_val ) {
									$value_exists = true;
								} ?>
								<option value="<?php echo esc_attr( $font ); ?>" <?php selected( esc_attr( $font ), $this_val, true ); ?>><?php echo esc_html( $font ); ?></option>
							<?php } ?>
						</optgroup>
					<?php } ?>

					<?php if ( ! wpex_has_registered_fonts() ) { ?>

						<?php
						// Get Standard font options
						if ( $std_fonts = wpex_standard_fonts() ) { ?>
							<optgroup label="<?php esc_html_e( 'Standard Fonts', 'total' ); ?>">
								<?php
								// Loop through font options and add to select
								foreach ( $std_fonts as $font ) {
									if ( $font === $this_val ) {
										$value_exists = true;
									} ?>
									<option value="<?php echo esc_attr( $font ); ?>" <?php selected( esc_attr( $font ), $this_val, true ); ?>><?php echo esc_html( $font ); ?></option>
								<?php } ?>
							</optgroup>
						<?php } ?>

						<?php
						// Google font options
						if ( $google_fonts = wpex_google_fonts_array() ) { ?>

							<optgroup label="<?php esc_html_e( 'Google Fonts', 'total' ); ?>">
								<?php
								// Loop through font options and add to select
								foreach ( $google_fonts as $font ) {
									if ( $font === $this_val ) {
										$value_exists = true;
									} ?>
									<option value="<?php echo esc_attr( $font ); ?>" <?php selected( esc_attr( $font ), $this_val, true ); ?>><?php echo esc_html( $font ); ?></option>
								<?php } ?>
							</optgroup>

						<?php } ?>

					<?php } ?>

					<?php if ( ! empty( $this_val ) && false === $value_exists ) { ?>
						<optgroup label="<?php esc_html_e( 'Non Registered Fonts', 'total' ); ?>">
							<option value="<?php echo esc_attr( $this_val ); ?>" selected="selected"><?php echo esc_html( $this_val ); ?></option>
						</optgroup>
					<?php } ?>

				</select>

				<?php if ( ! empty( $this->description ) ) : ?>

					<span class="description customize-control-description"><?php echo wp_strip_all_tags( $this->description ); ?></span>

				<?php endif; ?>

			</div>

		<?php }
	}

}