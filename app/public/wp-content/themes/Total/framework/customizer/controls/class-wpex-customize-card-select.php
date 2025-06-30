<?php
/**
 * Customizer Card Style Select
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Customizer_Dropdown_Card_Styles' ) ) {

	class WPEX_Customizer_Dropdown_Card_Styles extends WP_Customize_Control {

		/**
		 * The control type.
		 *
		 * @access public
		 * @var string
		 */
		public $type = 'wpex-card-select';

		/**
		 * Render the content
		 *
		 * @access public
		 */
		public function render_content() {
			$value = $this->value(); ?>

			<label class="customize-control-select">

			<?php if ( ! empty( $this->label ) ) : ?>

				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

			<?php endif;

			// Description
			if ( ! empty( $this->description ) ) { ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php } ?>

			<div class="wpex-customizer-chosen-select">

				<?php
				$select = wpex_card_select( array(
					'name'     => '_customize-dropdown-pages-' . $this->id,
					'selected' =>  $this->value(),
					'echo'     => 0,
				) );

				// Hackily add in the data link parameter.
				echo str_replace( '<select', '<select ' . $this->get_link(), $select );
				?>

			</div>

		<?php }
	}

}