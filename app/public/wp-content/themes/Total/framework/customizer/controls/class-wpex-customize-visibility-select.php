<?php
/**
 * Customizer Visibility Select Control
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Customizer_Visibility_Select' ) ) {

	class WPEX_Customizer_Visibility_Select extends WP_Customize_Control {

		/**
		 * The control type.
		 *
		 * @access public
		 * @var string
		 */
		public $type = 'wpex-visibility-select';

		/**
		 * Render the content
		 *
		 * @access public
		 */
		public function render_content() {

			$input_id                 = '_customize-input-' . $this->id;
			$description_id           = '_customize-description-' . $this->id;
			$describedby_attr_escaped = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';
			$this->choices            = wpex_visibility();

			$value = $this->value();

			/*
			// Causes issues with customizer, can't use.
			if ( $value ) {
				$value = wpex_sanitize_visibility( $value ); // deprecate old visibility settings
			}*/

			?>

			<?php if ( ! empty( $this->label ) ) : ?>
				<label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
			<?php endif; ?>

			<?php if ( ! empty( $this->description ) ) : ?>
				<span id="<?php echo esc_attr( $description_id ); ?>" class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>

			<select id="<?php echo esc_attr( $input_id ); ?>" <?php echo $describedby_attr_escaped; ?> <?php $this->link(); ?>>

				<?php
				foreach ( $this->choices as $choice_value => $choice_label ) {
					echo '<option value="' . esc_attr( $choice_value ) . '"' . selected( $value, $choice_value, false ) . '>' . esc_html( $choice_label ) . '</option>';
				}
				?>

			</select>

		<?php }
	}

}