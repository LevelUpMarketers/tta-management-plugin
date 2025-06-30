<?php
/**
 * Custom Login Page Design
 *
 * @package Total WordPress theme
 * @subpackage Framework
 * @version 5.0
 *
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class CustomLogin {

	/**
	 * Our single CustomLogin instance.
	 *
	 * @var CustomLogin
	 */
	private static $instance;

	/**
	 * Disable instantiation.
	 */
	private function __construct() {
		// Private to disable instantiation.
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
	 * Create or retrieve the instance of CustomLogin.
	 *
	 * @return CustomLogin
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new CustomLogin;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.6.0
	 */
	public function init_hooks() {

		if ( wpex_is_request( 'admin' ) ) {
			$this->admin_actions();
		}

		if ( wpex_is_request( 'frontend' ) ) {
			$this->frontend_actions();
		}

	}

	/**
	 * Admin hooks.
	 *
	 * @since 5.0
	 */
	public function admin_actions() {
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Frontend hooks.
	 *
	 * @since 5.0
	 */
	public function frontend_actions() {
		add_action( 'login_head', array( $this, 'output_css' ) );
		add_action( 'login_headerurl', array( $this, 'login_headerurl' ) );
		//add_filter( 'login_headertitle',array( $this, 'login_headertitle' ) ); // @deprecated in WP 5.2
		add_filter( 'login_headertext',array( $this, 'login_headertext' ) ); // added in 5.2
	}

	/**
	 * Returns custom login page settings.
	 *
	 * @since 3.5.0
	 */
	public function options() {
		return wpex_get_mod( 'login_page_design', array(
			'enabled'                 => true,
			'logo'                    => null,
			'logo_url_home'           => true,
			'logo_url'                => null,
			'logo_url_title'          => null,
			'logo_height'             => null,
			'background_color'        => null,
			'background_img'          => null,
			'background_style'        => null,
			'form_background_color'   => null,
			'form_background_opacity' => null,
			'form_text_color'         => null,
			'form_top'                => null,
			'form_border_radius'      => null,
		) );
	}

	/**
	 * Add sub menu page
	 *
	 * @since 1.6.0
	 */
	public function add_submenu_page() {
		add_submenu_page(
			WPEX_THEME_PANEL_SLUG,
			esc_html__( 'Custom Login', 'total' ),
			esc_html__( 'Custom Login', 'total' ),
			'administrator',
			WPEX_THEME_PANEL_SLUG . '-admin-login',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Register a setting and its sanitization callback.
	 *
	 * @since 1.6.0
	 */
	public function register_settings() {
		register_setting( 'wpex_custom_login', 'login_page_design', array( $this, 'sanitize' ) );
	}

	/**
	 * Sanitization callback
	 *
	 * @since 1.6.0
	 */
	public function sanitize( $options ) {

		// If we have options lets save them in the theme_mods
		if ( $options ) {

			// Loop through options to prevent empty vars from saving
			foreach ( $options as $key => $val ) {
				if ( empty( $val ) ) {
					unset( $options[$key] );
				}
				if ( 'background_img' == $key && empty( $val ) ) {
					unset( $options['background_style'] );
				}
				if ( 'logo' == $key && empty( $val ) ) {
					unset( $options['logo_height'] );
				}
			}

			// Save theme_mod
			set_theme_mod( 'login_page_design', $options );

		}

		// Clear options
		$options = '';

		// Return nothing since we are storing in theme_mods not options table
		return;

	}

	/**
	 * Settings page output
	 *
	 * @since 1.6.0
	 */
	public function create_admin_page() {

		wp_enqueue_media();

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style( 'wpex-admin-pages' );
		wp_enqueue_script( 'wpex-admin-pages' );

		?>

		<div class="wrap">

			<h1><?php esc_html_e( 'Custom Login', 'total' ); ?></h1>

			<p><?php esc_html_e( 'Customize the default WordPress login screen with your own custom logo and colors.', 'total' ); ?></p>

			<hr />

			<h2 class="nav-tab-wrapper wpex-panel-js-tabs">
				<a href="#main" class="nav-tab nav-tab-active"><?php esc_html_e( 'Main', 'total' ); ?></a>
				<a href="#background" class="nav-tab"><?php esc_html_e( 'Background', 'total' ); ?></a>
				<a href="#form" class="nav-tab"><?php esc_html_e( 'Form', 'total' ); ?></a>
				<a href="#button" class="nav-tab"><?php esc_html_e( 'Button', 'total' ); ?></a>
			</h2>

			<?php $theme_mod = $this->options(); ?>

			<form method="post" action="options.php">

				<?php settings_fields( 'wpex_custom_login' ); ?>

				<table class="form-table wpex-tabs-wrapper">

					<tr valign="top" class="wpex-tab-content wpex-main">
						<th scope="row"><label for="login_page_design[enabled]"><?php esc_html_e( 'Enable', 'total' ); ?></label></th>
						<td>
							<?php $enabled = isset ( $theme_mod['enabled'] ) ? $theme_mod['enabled'] : ''; ?>
							<input id="login_page_design[enabled]" type="checkbox" name="login_page_design[enabled]" <?php checked( $enabled, 'on' ); ?>>
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-main">
						<th scope="row"><label for="login_page_design[logo]"><?php esc_html_e( 'Logo', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['logo'] ) ? $theme_mod['logo'] : ''; ?>
							<input id="login_page_design[logo]" class="wpex-media-input" type="text" name="login_page_design[logo]" value="<?php echo esc_attr( $option ); ?>">
							<button class="wpex-media-upload-button button-primary"><?php esc_attr_e( 'Select', 'total' ); ?></button>
							<button class="wpex-media-remove button-secondary"><?php esc_html_e( 'Remove', 'total' ); ?></button>
							<?php $preview = wpex_get_image_url( $option ); ?>
							<div class="wpex-media-live-preview" style="width:320px">
								<?php if ( $preview ) { ?>
									<img src="<?php echo esc_url( $preview ); ?>" alt="<?php esc_html_e( 'Preview Image', 'total' ); ?>" />
								<?php } ?>
							</div>
							<p class="description"><?php esc_html_e( 'The WordPress login logo displays at 320px wide so please upload an image that is this size or smaller to prevent your logo from being cropped off the sides.', 'total' ); ?></p>
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-main">
						<th scope="row"><label for="login_page_design[logo_height]"><?php esc_html_e( 'Logo Height', 'total' ); ?></label></th>
						<td>
							<?php $option = ! empty( $theme_mod['logo_height'] ) ? intval( $theme_mod['logo_height'] ) : ''; ?>
							<input id="login_page_design[logo_height]" type="number" name="login_page_design[logo_height]" value="<?php echo esc_attr( $option ); ?>">
							<p class="description"><?php esc_html_e( 'Enter a value in pixels.', 'total' ); ?></p>
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-main">
						<th scope="row"><label for="login_page_design[logo_url_home]"><?php esc_html_e( 'Link Logo to Homepage?', 'total' ); ?></label></th>
						<td>
							<?php $enabled = isset ( $theme_mod['logo_url_home'] ) ? $theme_mod['logo_url_home'] : ''; ?>
							<input id="login_page_design[logo_url_home]" type="checkbox" name="login_page_design[logo_url_home]" <?php checked( $enabled, 'on' ); ?>>
							<p class="description"><?php esc_html_e( 'By default the login logo in WordPress links to their website, enable this setting so that the logo links to your own site homepage.', 'total' ); ?></p>
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-main">
						<th scope="row"><label for="login_page_design[logo_url]"><?php esc_html_e( 'Custom Logo URL', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['logo_url'] ) ? $theme_mod['logo_url'] : ''; ?>
							<input id="login_page_design[logo_url]" type="text" name="login_page_design[logo_url]" value="<?php echo esc_attr( $option ); ?>">
							<p class="description"><?php esc_html_e( 'Enter a custom URL for when clicking on the logo (optional).', 'total' ); ?></p>
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-main">
						<th scope="row"><label for="login_page_design[logo_url_title]"><?php esc_html_e( 'Logo Screen Reader Text', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['logo_url_title'] ) ? $theme_mod['logo_url_title'] : ''; ?>
							<input id="login_page_design[logo_url_title]" type="text" name="login_page_design[logo_url_title]" value="<?php echo esc_attr( $option ); ?>">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-background">
						<th scope="row"><label for="login_page_design[background_color]"><?php esc_html_e( 'Background Color', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['background_color'] ) ? $theme_mod['background_color'] : ''; ?>
							<input id="login_page_design[background_color]" type="text" name="login_page_design[background_color]" value="<?php echo esc_attr( $option ); ?>" class="wpex-color-field">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-background">
						<th scope="row"><label for="login_page_design[background_img]"><?php esc_html_e( 'Background Image', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['background_img'] ) ? $theme_mod['background_img'] : ''; ?>
							<div class="uploader">
								<input id="login_page_design[background_img]" class="wpex-media-input" type="text" name="login_page_design[background_img]" value="<?php echo esc_attr( $option ); ?>">
								<button class="wpex-media-upload-button button-primary"><?php esc_attr_e( 'Select', 'total' ); ?></button>
								<button class="wpex-media-remove button-secondary"><?php esc_html_e( 'Remove', 'total' ); ?></button>
								<?php $preview = wpex_get_image_url( $option ); ?>
								<div class="wpex-media-live-preview">
									<?php if ( $preview ) { ?>
										<img src="<?php echo esc_url( $preview ); ?>" alt="<?php esc_html_e( 'Preview Image', 'total' ); ?>" />
									<?php } ?>
								</div>
							</div>
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-background">
						<th scope="row"><label for="login_page_design[background_style]"><?php esc_html_e( 'Background Image Style', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['background_style'] ) ? $theme_mod['background_style'] : ''; ?>
							<select id="login_page_design[background_style]" name="login_page_design[background_style]">
								<?php
								$bg_styles = array(
									'stretched' => esc_html__( 'Stretched', 'total' ),
									'repeat'    => esc_html__( 'Repeat', 'total' ),
									'fixed'     => esc_html__( 'Center Fixed', 'total' ),
								);
								foreach ( $bg_styles as $key => $val ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $option, $key, true ); ?>>
										<?php echo strip_tags( $val ); ?>
									</option>
								<?php } ?>
							</select>
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-form">
						<th scope="row"><label for="login_page_design[form_background_color]"><?php esc_html_e( 'Form Background Color', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['form_background_color'] ) ? $theme_mod['form_background_color'] : ''; ?>
							<input id="login_page_design[form_background_color]" type="text" name="login_page_design[form_background_color]" value="<?php echo esc_attr( $option ); ?>" class="wpex-color-field">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-form">
						<th scope="row"><label for="login_page_design[form_background_opacity]"><?php esc_html_e( 'Form Background Opacity', 'total' ); ?></label></th>
						<td>
							<?php $option = ! empty( $theme_mod['form_background_opacity'] ) ? floatval( $theme_mod['form_background_opacity'] ) : ''; ?>
							<input id="login_page_design[form_background_opacity]" type="number" name="login_page_design[form_background_opacity]" value="<?php echo esc_attr( $option ); ?>" min="0" max="1" step="0.1">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-form">
						<th scope="row"><label for="login_page_design[form_text_color]"><?php esc_html_e( 'Form Text Color', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['form_text_color'] ) ? $theme_mod['form_text_color'] : ''; ?>
							<input id="login_page_design[form_text_color]" type="text" name="login_page_design[form_text_color]" value="<?php echo esc_attr( $option ); ?>" class="wpex-color-field">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-form">
						<th scope="row"><label for="login_page_design[form_input_bg]"><?php esc_html_e( 'Form Input Background', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['form_input_bg'] ) ? $theme_mod['form_input_bg'] : ''; ?>
							<input id="login_page_design[form_input_bg]" type="text" name="login_page_design[form_input_bg]" value="<?php echo esc_attr( $option ); ?>" class="wpex-color-field">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-form">
						<th scope="row"><label for="login_page_design[form_input_color]"><?php esc_html_e( 'Form Input Color', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['form_input_color'] ) ? $theme_mod['form_input_color'] : ''; ?>
							<input id="login_page_design[form_input_color]" type="text" name="login_page_design[form_input_color]" value="<?php echo esc_attr( $option ); ?>" class="wpex-color-field">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-form">
						<th scope="row"><label for="login_page_design[form_top]"><?php esc_html_e( 'Form Top Margin', 'total' ); ?></label></th>
						<td>
							<?php $option = ! empty( $theme_mod['form_top'] ) ? wpex_sanitize_data( $theme_mod['form_top'], 'px_pct' ) : ''; ?>
							<input id="login_page_design[form_top]" type="text" name="login_page_design[form_top]" value="<?php echo esc_attr( $option ); ?>">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-form">
						<th scope="row"><label for="login_page_design[form_border_radius]"><?php esc_html_e( 'Form Border Radius', 'total' ); ?></label></th>
						<td>
							<?php $option = ! empty( $theme_mod['form_border_radius'] ) ? intval( $theme_mod['form_border_radius'] ) : ''; ?>
							<input id="login_page_design[form_border_radius]" type="text" name="login_page_design[form_border_radius]" value="<?php echo esc_attr( $option ); ?>">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-button">
						<th scope="row"><label for="login_page_design[form_button_bg]"><?php esc_html_e( 'Form Button Background', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['form_button_bg'] ) ? $theme_mod['form_button_bg'] : ''; ?>
							<input id="login_page_design[form_button_bg]" type="text" name="login_page_design[form_button_bg]" value="<?php echo esc_attr( $option ); ?>" class="wpex-color-field">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-button">
						<th scope="row"><label for="login_page_design[form_button_color]"><?php esc_html_e( 'Form Button Color', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['form_button_color'] ) ? $theme_mod['form_button_color'] : ''; ?>
							<input id="login_page_design[form_button_color]" type="text" name="login_page_design[form_button_color]" value="<?php echo esc_attr( $option ); ?>" class="wpex-color-field">
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-button">
						<th scope="row"><label for="login_page_design[form_button_bg_hover]"><?php esc_html_e( 'Form Button Background: Hover', 'total' ); ?></label></th>
						<td>
							<?php $option = isset( $theme_mod['form_button_bg_hover'] ) ? $theme_mod['form_button_bg_hover'] : ''; ?>
							<input id="login_page_design[form_button_bg_hover]" type="text" name="login_page_design[form_button_bg_hover]" value="<?php echo esc_attr( $option ); ?>" class="wpex-color-field">
						</td>
					</tr>

				</table>

				<?php submit_button(); ?>

			</form>

		</div>

	<?php }

	/**
	 * RGBA to HEX conversions
	 *
	 * @since 1.6.0
	 */
	public function hex2rgba( $color, $opacity = false ) {

		// Define default rgba
		$default = 'rgb(0,0,0)';

		//Return default if no color provided
		if( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided
		if ( $color[0] == '#' ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters and get values
		if ( strlen( $color ) == 6) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		// Convert hexadec to rgb
		$rgb =  array_map( 'hexdec', $hex );

		//Check if opacity is set(rgba or rgb)
		if( $opacity ) {
			if( abs ( $opacity ) > 1 )
				$opacity = 1.0;
			$output = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ",", $rgb ) . ')';
		}

		//Return rgb(a) color string
		return $output;
	}

	/**
	 * Outputs the CSS for the custom login page
	 *
	 * @since 1.6.0
	 */
	public function output_css() {

		// Get options
		$options = $this->options();

		// Do nothing if disabled
		if ( empty( $options['enabled'] ) ) {
			return;
		}

		// Sanitize data
		$logo                    = wpex_get_image_url( $this->parseOption( 'logo' ) );
		$logo_height             = $this->parseOption( 'logo_height', '84px' );
		$logo_height             = intval( $logo_height ) . 'px';
		$background_img          = wpex_get_image_url( $this->parseOption( 'background_img' ) );
		$background_style        = $this->parseOption( 'background_style' );
		$background_color        = $this->parseOption( 'background_color' );
		$form_background_color   = $this->parseOption( 'form_background_color' );
		$form_background_opacity = $this->parseOption( 'form_background_opacity' );
		$form_text_color         = $this->parseOption( 'form_text_color' );
		$form_top                = $this->parseOption( 'form_top', '150px' );
		$form_input_bg           = $this->parseOption( 'form_input_bg' );
		$form_input_color        = $this->parseOption( 'form_input_color' );
		$form_border_radius      = $this->parseOption( 'form_border_radius' );
		$form_button_bg          = $this->parseOption( 'form_button_bg' );
		$form_button_bg_hover    = $this->parseOption( 'form_button_bg_hover' );
		$form_button_color       = $this->parseOption( 'form_button_color' );

		// Output Styles
		$output = '';

			// Logo
			if ( $logo ) {
				$output .='body.login div#login h1 a {';
					$output .='background: url("' . esc_url( $logo ) . '") center center no-repeat;';
					$output .='height: ' . intval( $logo_height ) . 'px;';
					$output .='width: 100%;';
					$output .='display: block;';
					$output .='margin: 0 auto 30px;';
				$output .='}';
			}

			// Background image
			if ( $background_img ) {
				if ( 'stretched' == $background_style ) {
					$output .= 'body.login { background: url(' . esc_url( $background_img ) . ') no-repeat center center fixed; -webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover; }';
				} elseif ( 'repeat' == $background_style ) {
					$output .= 'body.login { background: url(' . esc_url( $background_img ) . ') repeat; }';
				} elseif ( 'fixed' == $background_style ) {
					$output .= 'body.login { background: url(' . esc_url( $background_img ) . ') center top fixed no-repeat; }';
				}
			}

			// Background color
			if ( $background_color ) {
				$output .='body.login { background-color: '. $background_color . '; }';
			}

			// Form top
			if ( $form_top ) {
				$output .= 'body.login div#login { padding-top: 0; position: relative; top:'. wpex_sanitize_data( $form_top, 'px_pct' ) .'; }';
			}

			// Form Background Color
			if ( $form_background_color ) {
				$form_bg_color_rgba = $this->hex2rgba( $form_background_color, $form_background_opacity );
				$output .='.login form { background: none; -webkit-box-shadow: none; box-shadow: none; padding: 0 0 20px; border: 0; outline: 0 } #backtoblog { display: none; } .login #nav { text-align: center; }';
				$output .='body.login div#login { background: '. $form_background_color .'; background: '. $form_bg_color_rgba .';height:auto;left:50%;margin: 0 0 0 -200px;padding:40px;position:absolute;width:320px; max-width:90%; border-radius: 5px; }';
			} else {
				$output .= 'body.login div#login { opacity:'. $form_background_opacity .'; }';
			}

			// Form top
			if ( $form_border_radius ) {
				if ( $form_background_color ) {
					$output .= 'body.login div#login { border-radius:'. intval( $form_border_radius ) .'px; }';
				} else {
					$output .= 'body.login div#login #loginform { border-radius:'. intval( $form_border_radius ) .'px; }';
				}
			}

			// Form input
			if ( $form_input_bg ) {
				$output .='body.login div#login input.input { background: '. $form_input_bg .'; border: 0; box-shadow: none; }';
			}
			if ( $form_input_color ) {
				$output .='body.login form .input { color: '. $form_input_color .'; }';
			}

			// Text Color
			if ( $form_text_color ) {
				$output .='.login label, .login #nav a, .login #backtoblog a, .login #nav { color: '. $form_text_color .'; }';
			}

			// Button background
			if ( $form_button_bg ) {
				$output .='body.login div#login .button { background: '. $form_button_bg .'; border:0; outline: 0; }';
			}

			// Button background
			if ( $form_button_color ) {
				$output .='body.login div#login .button { color: '. $form_button_color .'; }';
			}

			// Button background Hover
			if ( $form_button_bg_hover ) {
				$output .='body.login div#login .button:hover { background: '. $form_button_bg_hover .'; border:0; outline: 0; }';
			}

			// Remove box-shadow
			if ( $form_button_bg || $form_button_bg_hover ) {
				$output .= 'body.login div#login .button{ box-shadow: none !important; }';
			}

			// Remove text-shadow
			if ( $form_button_color || $form_button_bg ) {
				$output .= 'body.login div#login .button{ text-shadow:none; }';
			}

		// Echo output
		if ( $output ) {
			echo '<style>' . wp_strip_all_tags( $output ) . '</style>';
		}

	}

	/**
	 * Parses data
	 *
	 * @since 1.6.0
	 */
	public function parseOption( $option_id, $default = '' ) {
		$options = $this->options();
		return ! empty( $options[$option_id] ) ? $options[$option_id] : $default;
	}

	/**
	 * Custom login page logo URL
	 *
	 * @since 1.6.0
	 */
	public function login_headerurl( $url ) {
		$options = $this->options();
		if ( ! empty( $options['logo_url'] ) ) {
			$url = esc_url( $options['logo_url'] );
		} elseif( ! empty( $options['logo_url_home'] ) ) {
			$url = esc_url( home_url( '/' ) );
		}
		return $url;
	}

	/**
	 * Custom login page logo URL title attribute
	 *
	 * @since 4.9
	 */
	public function login_headertext( $title ) {
		$options = $this->options();
		$title   = isset( $options['logo_url_title'] ) ? $options['logo_url_title'] : $title;
		return esc_attr( $title );
	}

}
CustomLogin::instance();