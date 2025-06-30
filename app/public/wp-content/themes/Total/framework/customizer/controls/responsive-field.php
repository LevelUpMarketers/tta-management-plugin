<?php
/**
 * Responsive Field Customizer Control
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Responsive_Field_Custom_Control' ) ) {

	class WPEX_Responsive_Field_Custom_Control extends WP_Customize_Control {

		/**
		 * The control type.
		 *
		 * @access public
		 * @var string
		 */
		public $type = 'wpex-responsive-field';

		/**
		 * Render the content
		 *
		 * @access public
		 */
		public function render_content() {

			$field_val = $this->value();

			$medias = array(
				'd'  => array(
					'label' => esc_html__( 'Desktop', 'total' ),
					'icon'  => 'dashicons dashicons-desktop',
				),
				'tl' => array(
					'label' => esc_html__( 'Tablet Landscape', 'total' ),
					'icon'  => 'dashicons dashicons-tablet',
				),
				'tp' => array(
					'label' => esc_html__( 'Tablet Portrait', 'total' ),
					'icon'  => 'dashicons dashicons-tablet',
				),
				'pl' => array(
					'label' => esc_html__( 'Phone Landscape', 'total' ),
					'icon'  => 'dashicons dashicons-smartphone',
				),
				'pp' => array(
					'label' => esc_html__( 'Phone Portrait', 'total' ),
					'icon'  => 'dashicons dashicons-smartphone',
				),
			);

			// Setup default values
			$defaults = array();
			foreach ( $medias as $key => $val ) {
				$defaults[$key] = '';
			}

			// If field val isn't an array then it's a single desktop font-size
			if ( ! is_array( $field_val ) ) {
				$field_val = array(
					'd' => $field_val,
				);
			}

			// Parse field
			$field_val = wp_parse_args( $field_val, $defaults ); ?>

			<label><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span></label>

			<?php if ( ! empty( $this->description ) ) : ?>

				<span class="description customize-control-description">
					<?php echo wp_strip_all_tags( $this->description ); ?>
				</span>

			<?php endif; ?>


			<ul class="wpex-customizer-responsive-field">

				<?php
				// Loop through medias and display fields
				foreach ( $medias as $key => $val ) : ?>

					<li>

						<label for="<?php echo esc_attr( $this->id ); ?>_<?php echo esc_attr( $key ); ?>" class="screen-reader-text"><?php echo esc_attr( $val['label'] ); ?></label>

						<input class="wpex-crf-input" name="<?php echo esc_attr( $this->id ); ?>_<?php echo esc_attr( $key ); ?>" data-name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $field_val[$key] ); ?>" type="text" placeholder="-">

						<?php
						// Display icon if defined
						if ( isset( $val['icon'] ) ) {

							$icon_classes = 'wpex-crf-icon';

							if ( 'pl' == $key || 'tl' == $key ) {
								$icon_classes .= ' wpex-crf-icon-flip';
							} ?>

							<span class="<?php echo esc_attr( $icon_classes ); ?>" aria-hidden="true"><span class="<?php echo esc_attr( $val['icon'] ); ?>"></span></span>

						<?php } ?>

					</li>

				<?php endforeach; ?>

			</ul>

		<?php }
	}

}