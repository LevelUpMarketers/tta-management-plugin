<?php
/**
 * Customizer Templates Select Control
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Customizer_Dropdown_Templates' ) ) {

	class WPEX_Customizer_Dropdown_Templates extends WP_Customize_Control {

		/**
		 * The control type.
		 */
		public $type = 'wpex-dropdown-templates';

		/**
		 * Render the content
		 */
		public function render_content() {

			$this->choices = wpex_choices_dynamic_templates();

			$input_id                 = '_customize-input-' . $this->id;
			$description_id           = '_customize-description-' . $this->id;
			$describedby_attr_escaped = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';

			?>

			<?php if ( ! empty( $this->label ) ) : ?>
				<label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
			<?php endif; ?>

			<?php if ( ! empty( $this->description ) ) : ?>
				<span id="<?php echo esc_attr( $description_id ); ?>" class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>

			<select id="<?php echo esc_attr( $input_id ); ?>" <?php echo $describedby_attr_escaped; ?> <?php $this->link(); ?>>

				<?php
				foreach ( $this->choices as $value => $label ) {

					if ( empty( $value ) && 0 === $this->value() ) {
						$value = '0';
					}

					echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' . esc_html( $label ) . '</option>';

				}
				?>

			</select>

		<?php }

	}

}