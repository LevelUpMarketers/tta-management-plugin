<?php
/**
 * Customizer Patterns Control
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Customizer_BG_Patterns_Control' ) ) {

	class WPEX_Customizer_BG_Patterns_Control extends WP_Customize_Control {

		/**
		 * The control type
		 *
		 * @access public
		 * @var string
		 */
		public $type = 'wpex-bg-patterns';

		/**
		 * The control template
		 *
		 * @since 4.0
		 */
		public function render_content() {

			$this_val = $this->value(); ?>

			<label class="customize-control-select">

				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

				<select <?php $this->link(); ?>>
					<option value="" <?php selected( $this_val, '', true ); ?>><?php esc_html_e( 'None', 'total' ); ?></option>
					<?php if ( $patterns = wpex_get_background_patterns() ) {
						foreach ( $patterns as $key => $val ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $this_val, $key, true ); ?>><?php echo esc_html( $val['label'] ); ?></option>
						<?php }
					} ?>
				</select>

			</label>

		<?php }

	}

}