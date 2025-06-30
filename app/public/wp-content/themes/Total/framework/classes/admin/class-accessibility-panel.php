<?php
/**
 * Adds custom CSS to the site to tweak the main accent colors
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class AccessibilityPanel {

	/**
	 * Our single AccessibilityPanel instance.
	 */
	private static $instance;

	/**
	 * Disable instantiation.
	 */
	private function __construct() {
		// Private to disabled instantiation.
	}

	/**
	 * Disable the cloning of this class.
	 *
	 * @return void
	 */
	final public function __clone() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Disable the wakeup of this class.
	 *
	 * @return void
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of AccessibilityPanel.
	 *
	 * @return AccessibilityPanel
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new AccessibilityPanel;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_page' ), 50 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add sub menu page.
	 *
	 * @since 4.6.5
	 */
	public function add_page() {
		add_submenu_page(
			WPEX_THEME_PANEL_SLUG,
			esc_attr__( 'Accessibility', 'total' ),
			esc_attr__( 'Accessibility', 'total' ),
			'manage_options',
			WPEX_THEME_PANEL_SLUG . 'wpex-accessibility',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Register a setting and its sanitization callback.
	 *
	 * @since 4.6.5
	 */
	public function register_settings() {
		register_setting(
			'wpex_accessibility_settings',
			'wpex_accessibility_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'save_options' ),
			)
		);
	}

	/**
	 * Sanitization callback.
	 *
	 * @since 4.6.5
	 */
	public function save_options( $options ) {

		$settings = $this->get_settings();

		if ( empty( $settings ) ) {
			return;
		}

		foreach ( $settings as $k => $v ) {

			$type    = isset( $v['type'] ) ? $v['type'] : 'input';
			$default = isset( $v['default'] ) ? $v['default'] : null;

			switch ( $type ) {

				case 'checkbox':

					if ( isset( $options[$k] ) ) {
						if ( ! $default ) {
							set_theme_mod( $k, true );
						} else {
							remove_theme_mod( $k );
						}
					} else {
						if ( $default ) {
							set_theme_mod( $k, false );
						} else {
							remove_theme_mod( $k );
						}
					}

					break;

				case 'aria_label':

					$aria_labels = get_theme_mod( 'aria_labels' );

					if ( empty( $options[$k] ) ) {
						unset( $aria_labels[$k] );
					} else {

						$defaults = wpex_aria_label_defaults();

						if ( ! isset( $defaults[$k] ) || ( $defaults[$k] !== $options[$k] ) ) {
							$aria_labels[$k] = $options[$k];
						}

					}

					if ( ! empty( $aria_labels ) ) {
						set_theme_mod( 'aria_labels', $aria_labels );
					} else {
						remove_theme_mod( 'aria_labels' );
					}

					break;

				default:

					if ( ! empty( $options[$k] ) && $default != $options[$k] ) {
						set_theme_mod( $k, wp_strip_all_tags( $options[$k] ) );
					} else {
						remove_theme_mod( $k );
					}

					break;

			} // end switch

		} // end foreach

		$options = ''; // don't store in options, only in theme mod

		return;

	}

	/**
	 * Return array of settings
	 *
	 * @since 4.6.5
	 */
	public function get_settings() {
		return array(
			'skip_to_content' => array(
				'name'        => esc_html__( 'Skip to content link', 'total' ),
				'default'     => true,
				'type'        => 'checkbox',
				'description' => esc_html__( 'Enables the skip to content link when clicking tab as soon as your site loads.', 'total' ),
			),
			'skip_to_content_id' => array(
				'name'    => esc_html__( 'Skip to content ID', 'total' ),
				'default' => '#content',
				'type'    => 'text',
			),
			'remove_menu_ids' => array(
				'name'        => esc_html__( 'Remove Menu ID attributes', 'total' ),
				'default'     => false,
				'type'        => 'checkbox',
				'description' => esc_html__( 'Removes the ID attributes added by default in WordPress to each item in your menu.', 'total' ),
			),
			'aria_landmarks_enable' => array(
				'name'        => esc_html__( 'Aria Landmarks', 'total' ),
				'default'     => false,
				'type'        => 'checkbox',
				'description' => esc_html__( 'Enables the aria landmark tags in the theme which are disabled by default as they generate errors in the W3C checker.', 'total' ),
			),
			'site_navigation' => array(
				'name'    => esc_html__( 'Main Menu Aria Label', 'total' ),
				'type'    => 'aria_label',
			),
			'mobile_menu_toggle' => array(
				'name'    => esc_html__( 'Mobile Menu Toggle Aria Label', 'total' ),
				'type'    => 'aria_label',
			),
			'mobile_menu' => array(
				'name'    => esc_html__( 'Mobile Menu Aria Label', 'total' ),
				'type'    => 'aria_label',
			),
			'footer_callout' => array(
				'name' => esc_html__( 'Footer Callout Aria Label', 'total' ),
				'type' => 'aria_label',
			),
			'footer_bottom_menu' => array(
				'name' => esc_html__( 'Footer Menu Aria Label', 'total' ),
				'type' => 'aria_label',
			),

			// @todo remove this setting and figure something else out...
			'disable_mobile_menu_focus_styles' => array(
				'name'        => esc_html__( 'Disable Mobile Menu Focus Styles', 'total' ),
				'default'     => true,
				'type'        => 'checkbox',
				'description' => esc_html__( 'Disables the focus styles for mobile devices so you don\'t see the outline when using a phone or tablet and opening and closing your mobile menu.', 'total' ),
			),

		);
	}

	/**
	 * Settings page output
	 *
	 * @since 4.6.5
	 */
	public function create_admin_page() { ?>

		<div class="wrap">

			<h1><?php esc_html_e( 'Accessibility', 'total' ); ?></h1>

			<form method="post" action="options.php">

				<?php settings_fields( 'wpex_accessibility_settings' ); ?>

				<table class="form-table">

					<?php foreach ( $this->get_settings() as $k => $v ) {

						$type        = isset( $v[ 'type' ] ) ? $v[ 'type' ] : 'input';
						$default     = isset( $v[ 'default' ] ) ? $v[ 'default' ] : null;
						$description = ! empty( $v[ 'description' ] ) ? $v[ 'description' ] : null;

						?>

						<tr valign="top">

							<th scope="row">
								<?php if ( 'checkbox' == $type ) {
									echo esc_html( $v['name'] );
								} else { ?>
									<label for="wpex_accessibility_settings[<?php echo esc_attr( $k ); ?>]"><?php echo esc_html( $v['name'] ); ?></label>
								<?php } ?>
							</th>

							<td>

								<?php
								switch ( $type ) {

									case 'checkbox':

										$theme_mod = get_theme_mod( $k, $default );

										?>

										<?php if ( $description ) { ?>
											<label for="wpex_accessibility_settings[<?php echo esc_attr( $k ); ?>]">
										<?php } ?>

										<input id="wpex_accessibility_settings[<?php echo esc_attr( $k ); ?>]" type="checkbox" name="wpex_accessibility_settings[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $theme_mod ); ?>" <?php checked( $theme_mod, true ); ?> class="wpex-checkbox">

										<?php if ( $description ) { ?>
											<?php echo esc_html( $description ); ?>
											</label>
										<?php } ?>

										<?php break;

									case 'aria_label':

										$aria_label = wpex_get_aria_label( $k );

										?>

										<input type="text" id="wpex_accessibility_settings[<?php echo esc_attr( $k ); ?>]" name="wpex_accessibility_settings[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $aria_label ); ?>" />

										<?php if ( $description ) { ?>
											<p class="description"><?php echo esc_html( $description ); ?></p>
										<?php } ?>

										<?php break;

									default:

										$theme_mod = get_theme_mod( $k, $default );

										?>

										<input type="text" id="wpex_accessibility_settings[<?php echo esc_attr( $k ); ?>]" name="wpex_accessibility_settings[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $theme_mod ); ?>" />

										<?php if ( $description ) { ?>
											<p class="description"><?php echo esc_html( $description ); ?></p>
										<?php } ?>

										<?php break;

								} // end switch

								?>
							</td>

						</tr>

					<?php } ?>

				</table>

				<?php submit_button(); ?>

			</form>

		</div>

	<?php }

}
AccessibilityPanel::instance();