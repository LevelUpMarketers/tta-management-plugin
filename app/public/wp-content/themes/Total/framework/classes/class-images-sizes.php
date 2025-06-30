<?php
/**
 * Add image sizes and image size settings panel.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class ImageSizes {

	/**
	 * Our single ImageSizes instance.
	 *
	 * @var ImageSizes
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
	 * Create or retrieve the instance of ImageSizes.
	 *
	 * @return ImageSizes
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new ImageSizes;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Define and add image sizes
		// Can't run earlier cause it comflicts with WooCommerce updated in v 4.5.5 from priority 1 to 40
		add_action( 'init', array( $this, 'add_sizes' ), 40 );

		// Prevent images from cropping when on the fly is enabled
		if ( get_theme_mod( 'image_resizing', true ) ) {
			add_filter( 'intermediate_image_sizes_advanced', array( $this, 'do_not_crop_on_upload' ) );
		}

		// Admin only functions
		if ( wpex_is_request( 'admin' ) && apply_filters( 'wpex_image_sizes_panel', true ) ) {
			add_action( 'admin_menu', array( $this, 'add_admin_submenu_page' ), 10 );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}

		// Disable WP responsive images
		if ( $this->maybe_disable_responsive_images() ) {
			add_filter( 'wp_calculate_image_srcset', '__return_false', PHP_INT_MAX ); /* Causes issues with Retina functions */
		}

	}

	/**
	 * Return array of image sizes used by the theme.
	 */
	public function get_sizes() {
		return apply_filters( 'wpex_image_sizes', array(
			'lightbox'    => array(
				'label'   => esc_html__( 'Lightbox Images', 'total' ),
				'width'   => 'lightbox_image_width',
				'height'  => 'lightbox_image_height',
				'crop'    => 'lightbox_image_crop',
				'section' => 'other',
			),
			'search_results' => array(
				'label'      => esc_html__( 'Search', 'total' ),
				'width'      => 'search_results_image_width',
				'height'     => 'search_results_image_height',
				'crop'       => 'search_results_image_crop',
				'section'    => 'other',
			),
			'blog_entry'  => array(
				'label'   => esc_html__( 'Blog Entry', 'total' ),
				'width'   => 'blog_entry_image_width',
				'height'  => 'blog_entry_image_height',
				'crop'    => 'blog_entry_image_crop',
				'section' => 'blog',
			),
			'blog_post'   => array(
				'label'   => esc_html__( 'Blog Post', 'total' ),
				'width'   => 'blog_post_image_width',
				'height'  => 'blog_post_image_height',
				'crop'    => 'blog_post_image_crop',
				'section' => 'blog',
			),
			'blog_post_full' => array(
				'label'      => esc_html__( 'Blog Post: Full-Width', 'total' ),
				'width'      => 'blog_post_full_image_width',
				'height'     => 'blog_post_full_image_height',
				'crop'       => 'blog_post_full_image_crop',
				'section'    => 'blog',
			),
			'blog_related' => array(
				'label'    => esc_html__( 'Blog Post: Related', 'total' ),
				'width'    => 'blog_related_image_width',
				'height'   => 'blog_related_image_height',
				'crop'     => 'blog_related_image_crop',
				'section'  => 'blog',
			),
		) );
	}

	/**
	 * Filter the image sizes automatically generated when uploading an image.
	 */
	public function do_not_crop_on_upload( $sizes ) {

		// Get image sizes
		$get_sizes = $this->get_sizes();

		// Remove my image sizes from cropping if image resizing is enabled
		if ( ! empty ( $get_sizes ) ) {
			foreach( $get_sizes as $size => $args ) {
				unset( $sizes[$size] );
			}
		}

		// Return $meta
		return $sizes;

	}

	/**
	 * Register image sizes in WordPress.
	 */
	public function add_sizes() {

		// Get sizes array
		$sizes = $this->get_sizes();

		// Loop through sizes
		foreach ( $sizes as $size => $args ) {

			// Extract args
			extract( $args );

			// Get defaults
			$defaults       = ! empty( $args['defaults'] ) ? $args['defaults'] : '';
			$default_width  = isset( $defaults['width'] ) ? $defaults['width'] : '9999';
			$default_height = isset( $defaults['height'] ) ? $defaults['height'] : '9999';
			$default_crop   = isset( $defaults['crop'] ) ? $defaults['crop'] : 'center-center';

			// Get theme mod values
			$width  = get_theme_mod( $width, $default_width );
			$height = get_theme_mod( $height, $default_height );
			$crop   = get_theme_mod( $crop, $default_crop  );
			$crop   = $crop ? $crop : 'center-center'; // Sanitize crop

			// Set crop to false depending on height value
			if ( ! $height || $height >= 9999 ) {
				$crop = false;
			}

			// Turn crop into array
			if ( $crop ) {
				$crop = ( 'center-center' == $crop ) ? 1 : explode( '-', $crop );
			}

			// If image resizing is disabled and a width or height is defined add image size
			if ( $width || $height ) {
				add_image_size( $size, $width, $height, $crop );
			}

		}

	}

	/**
	 * Add sub menu page.
	 */
	public function add_admin_submenu_page() {
		add_submenu_page(
			WPEX_THEME_PANEL_SLUG,
			esc_html__( 'Image Sizes', 'total' ),
			esc_html__( 'Image Sizes', 'total' ),
			'administrator',
			WPEX_THEME_PANEL_SLUG . '-image-sizes',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Register a setting and its sanitization callback.
	 */
	public function register_settings() {
		register_setting(
			'wpex_image_sizes',
			'wpex_image_sizes',
			array( $this, 'admin_sanitize' )
		);
	}

	/**
	 * Main Sanitization callback.
	 */
	public function admin_sanitize( $options ) {

		// Check options first
		if ( ! is_array( $options ) || empty( $options ) || ( false === $options ) ) {
			return array();
		}

		// Save checkboxes
		$checkboxes = array(
			'image_resizing'               => true,
			'post_thumbnail_lazy_loading'  => true,
			'retina'                       => false,
			'disable_wp_responsive_images' => false,
			'woo_dynamic_image_resizing'   => false,
		);

		foreach ( $checkboxes as $theme_mod_name => $theme_mod_default ) {

			$checked = isset( $options[$theme_mod_name] );

			if ( $checked ) {
				if ( $theme_mod_default ) {
					remove_theme_mod( $theme_mod_name );
				} else {
					set_theme_mod( $theme_mod_name, 1 );
				}
			} else {
				if ( $theme_mod_default ) {
					set_theme_mod( $theme_mod_name, 0 );
				} else {
					remove_theme_mod( $theme_mod_name );
				}
			}

		}

		// Standard options
		foreach( $options as $key => $value ) {
			if ( array_key_exists( $key, $checkboxes ) ) {
				continue; // checkboxes already done
			}
			if ( ! empty( $value ) ) {
				set_theme_mod( $key, sanitize_text_field( $value ) );
			} else {
				remove_theme_mod( $key );
			}
		}

		// No need to save in options table
		$options = '';
		return $options;

	}

	/**
	 * Settings page output.
	 */
	public function create_admin_page() {

		wp_enqueue_style( 'wpex-admin-pages' );
		wp_enqueue_script( 'wpex-admin-pages' );

		$sizes = $this->get_sizes();

		$crop_locations = wpex_image_crop_locations();

		?>

		<div class="wrap">

			<h1><?php esc_html_e( 'Image Sizes', 'total' ); ?></h1>

			<p><?php esc_html_e( 'Define the exact cropping for all the featured images on your site. Leave the width and height empty to display the full image. Set any height to "9999" or empty to disable cropping and simply resize the image to the corresponding width. All image sizes defined below will be added to the list of WordPress image sizes.', 'total' ); ?></p>

			<hr />

			<h2 class="nav-tab-wrapper wpex-panel-js-tabs" style="margin-top:20px;">
				<?php
				// Image sizes tabs
				$tabs = apply_filters( 'wpex_image_sizes_tabs', array(
					'general' => esc_html__( 'General', 'total' ),
					'blog'    => esc_html__( 'Blog', 'total' ),
				) );

				// Add 'other' tab after filter so it's always at end
				$tabs['other'] = esc_html__( 'Other', 'total' );

				// Loop through tabs and display them
				$count = 0;
				foreach ( $tabs as $key => $val ) {
					$count ++;
					$classes = 'nav-tab';
					if ( 1 == $count ) {
						$classes .=' nav-tab-active';
					}
					echo '<a href="#'. esc_attr( $key ) .'" class="'. esc_attr( $classes ) .'">'. esc_html( $val ) .'</a>';
				} ?>

			</h2>

			<form method="post" action="options.php">

				<?php settings_fields( 'wpex_image_sizes' ); ?>

				<table class="form-table wpex-image-sizes-admin-table">

					<tr valign="top" class="wpex-tab-content wpex-general">
						<th scope="row"><?php esc_html_e( 'Dynamic Resizing', 'total' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input id="wpex_image_resizing" type="checkbox" name="wpex_image_sizes[image_resizing]" <?php checked( get_theme_mod( 'image_resizing', true ) ); ?>>
										<?php esc_html_e( 'Enable on-the-fly dynamic image resizing for featured images displayed by the theme.', 'total' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-general">
						<th scope="row"><?php esc_html_e( 'Lazy Loading', 'total' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input id="wpex_lazy_loading" type="checkbox" name="wpex_image_sizes[post_thumbnail_lazy_loading]" <?php checked( get_theme_mod( 'post_thumbnail_lazy_loading', true ), true ); ?>> <?php esc_html_e( 'Enables native browser lazy loading for featured images displayed by the theme.', 'total' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>

					<tr valign="top" class="wpex-tab-content wpex-general">
						<th scope="row"><?php esc_html_e( 'Retina', 'total' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input id="wpex_retina" type="checkbox" name="wpex_image_sizes[retina]" <?php checked( get_theme_mod( 'retina' ), true ); ?>> <?php esc_html_e( 'Enable retina support for images generated by the theme.', 'total' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>

					<?php
					// Disable srcset
					$mod = wp_validate_boolean( get_theme_mod( 'disable_wp_responsive_images', false ) ); ?>
					<tr valign="top" class="wpex-tab-content wpex-general">
						<th scope="row"><?php esc_html_e( 'Disable WordPress srcset Images', 'total' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="checkbox" name="wpex_image_sizes[disable_wp_responsive_images]" <?php checked( $mod, true ); ?>> <?php esc_html_e( 'Disable the srcset responsive images in WordPress core (automatically disabled if theme retina support is enabled to cause conflicts).', 'total' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>

					<?php
					// WooCommerce Image Sizing
					if ( defined( 'WPEX_WOOCOMMERCE_ACTIVE' )
						&& WPEX_WOOCOMMERCE_ACTIVE
						&& wpex_has_woo_mods()
						&& wpex_woo_version_supported()
					) {

						$mod = wp_validate_boolean( get_theme_mod( 'woo_dynamic_image_resizing', false ) ); ?>

						<tr valign="top" class="wpex-tab-content wpex-general">
							<th scope="row"><?php esc_html_e( 'Use WooCommerce Native Image Sizing?', 'total' ); ?></th>
							<td>
								<fieldset>
									<label>
										<input id="wpex_woo_support" type="checkbox" name="wpex_image_sizes[woo_dynamic_image_resizing]" <?php checked( $mod, true ); ?>> <?php esc_html_e( 'By default the Total theme makes use of it\'s own image resizing functions for WooCommerce, if you rather use the native WooCommerce image sizing functions you can do so by enabling this setting.', 'total' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>

					<?php } ?>

					<?php
					// Loop through all sizes
					foreach ( $sizes as $size => $args ) : ?>

						<?php
						// Extract args
						extract( $args );

						// Label is required
						if ( ! $label ) {
							continue;
						}

						// Admin panel section
						$section = isset( $args['section'] ) ? $args['section'] : 'other';

						// Get defaults
						$defaults       = ! empty( $args['defaults'] ) ? $args['defaults'] : '';
						$default_width  = isset( $defaults['width'] ) ? $defaults['width'] : null;
						$default_height = isset( $defaults['height'] ) ? $defaults['height'] : null;
						$default_crop   = isset( $defaults['crop'] ) ? $defaults['crop'] : null;

						// Get values
						$width_value  = get_theme_mod( $width, $default_width );
						$height_value = get_theme_mod( $height, $default_height );
						$crop_value   = get_theme_mod( $crop, $default_crop ); ?>

						<tr valign="top" class="wpex-tab-content wpex-<?php echo esc_attr( $section ); ?>">
							<th scope="row"><?php echo strip_tags( $label ); ?></th>
							<td>
								<label for="<?php echo esc_attr( $width ); ?>"><?php esc_html_e( 'Width', 'total' ); ?></label>
								<input name="wpex_image_sizes[<?php echo esc_attr( $width ); ?>]" type="number" step="1" min="0" value="<?php echo esc_attr( $width_value ); ?>" class="small-text" />
								&nbsp;
								<label for="<?php echo esc_attr( $height ); ?>"><?php esc_html_e( 'Height', 'total' ); ?></label>
								<input name="wpex_image_sizes[<?php echo esc_attr( $height ); ?>]" type="number" step="1" min="0" value="<?php echo esc_attr( $height_value ); ?>" class="small-text" />
								&nbsp;
								<label for="<?php echo esc_attr( $crop ); ?>"><?php esc_html_e( 'Crop Location', 'total' ); ?></label>
								<select name="wpex_image_sizes[<?php echo esc_attr( $crop ); ?>]">
									<?php foreach ( $crop_locations as $key => $label ) { ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $crop_value, true ); ?>><?php echo strip_tags( $label ); ?></option>
									<?php } ?>
								</select>

							</td>
						</tr>

					<?php endforeach; ?>

				</table>

				<?php submit_button(); ?>

			</form>

		</div><!-- .wrap -->

	<?php
	}

	/**
	 * Check if WP responsive images should be disabled.
	 */
	public function maybe_disable_responsive_images() {

		$bool = ( get_theme_mod( 'disable_wp_responsive_images', false ) || wpex_is_retina_enabled() ) ? true : false;

		return apply_filters( 'wpex_disable_wp_responsive_images', $bool );

	}

}
ImageSizes::instance();