<?php
/**
 * Customizer Font Family Control
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Font_Awesome_Icon_Select' ) ) {

	class WPEX_Font_Awesome_Icon_Select extends WP_Customize_Control {

		/**
		 * The control type.
		 *
		 * @access public
		 * @var string
		 */
		public $type = 'wpex-fa-icon-select';

		/**
		 * Render the content
		 *
		 * @access public
		 */
		public function render_content() {

			$this_val = $this->value();

			if ( 'none' == $this_val ) {
				$this_val = ''; // legacy fix
			}

			$icons = (array) wpex_ticons_list();

			if ( empty( $icons ) ) {
				return;
			}

			?>

			<label><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span></label>

			<div class="wpex-customizer-chosen-select">

				<select <?php $this->link(); ?>>

					<?php foreach ( $icons as $icon ) {

						switch ( $icon ) {

							case 'none': ?>

								<option value="" <?php selected( $icon, $this_val, true ); ?>><?php echo esc_html__( 'None', 'total' ); ?></option>

								<?php break;

							default: ?>

								<option value="<?php echo esc_attr( $icon ); ?>" data-icon="ticon ticon-<?php echo esc_attr( $icon ); ?>" <?php selected( $icon, $this_val, true ); ?>><?php echo esc_html( $icon ); ?></option>

								<?php
								break;
						}

					} ?>

				</select>

			</div>

		<?php }

	}

}