<?php
/**
 * Header Builder
 *
 * @package Total WordPress theme
 * @subpackage Theme Builder
 * @version 5.0
 */

namespace TotalTheme;

use WP_Query;

defined( 'ABSPATH' ) || exit;

class HeaderBuilder {

	/**
	 * Start things up.
	 *
	 * @since 3.5.0
	 */
	public function __construct() {

		$this->init_hooks();

	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		if ( wpex_is_request( 'admin' ) ) {

			// Add admin page
			add_action( 'admin_menu', array( $this, 'add_admin_submenu_page' ), 20 );

			// Register admin options
			add_action( 'admin_init', array( $this, 'register_page_options' ) );

			// Edit links
			add_action( 'wp_ajax_wpex_header_builder_edit_links', array( $this, 'ajax_edit_links' ) );

		}

		// Run actions and filters if header_builder ID is defined
		if ( self::get_template_id() || ! empty( $_GET[ 'wpex_inline_header_template_editor' ] ) ) {

			if ( wpex_is_request( 'frontend' ) ) {

				// Alter the header
				add_action( 'wp', array( $this, 'alter_header' ) );

				// Include ID for Visual Composer custom CSS
				add_filter( 'wpex_vc_css_ids', array( $this, 'wpex_vc_css_ids' ) );

			}

			// Alter template for live editing
			if ( wpex_vc_is_inline() ) {
				add_filter( 'template_include', array( $this, 'builder_template' ), 9999 );
			}

			// Remove header customizer settings
			add_filter( 'wpex_customizer_sections', array( $this, 'remove_customizer_settings' ) );
			add_filter( 'wpex_typography_settings', array( $this, 'remove_typography_settings' ) );

			// Remove meta options
			// @todo use conditional callback functions instead for meta options so instead of removing we never add.
			if ( wpex_is_request( 'admin' ) ) {
				add_filter( 'wpex_metabox_array', array( $this, 'remove_meta' ), 99, 2 );
			}

			// Custom header design CSS output
			add_filter( 'wpex_head_css', array( $this, 'custom_css' ), 99 );

		}

	}

	/**
	 * Returns header template ID.
	 *
	 * @since 5.0
	 */
	public static function get_template_id() {
		$id = intval( apply_filters( 'wpex_header_builder_page_id', get_theme_mod( 'header_builder_page_id' ) ) );
		if ( ! empty( $id ) && is_numeric( $id ) ) {
			$id = ( $translated_id = wpex_parse_obj_id( $id, 'page' ) ) ? $translated_id : $id; // if not translated return original ID
			if ( 'publish' == get_post_status( $id ) ) {
				return $id;
			}
		}
	}

	/**
	 * Add sub menu page.
	 *
	 * @since 3.5.0
	 */
	public function add_admin_submenu_page() {
		add_submenu_page(
			WPEX_THEME_PANEL_SLUG,
			esc_html__( 'Header Builder', 'total' ),
			esc_html__( 'Header Builder', 'total' ),
			'administrator',
			WPEX_THEME_PANEL_SLUG .'-header-builder',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Returns settings array.
	 *
	 * @since 3.5.0
	 */
	public function settings() {
		return array(
			'page_id'      => esc_html__( 'Header Builder page', 'total' ),
			'bg'           => esc_html__( 'Background Color', 'total' ),
			'bg_img'       => esc_html__( 'Background Image', 'total' ),
			'bg_img_style' => esc_html__( 'Background Image Style', 'total' ),
			'top_bar'      => esc_html__( 'Top Bar', 'total' ),
			'sticky'       => esc_html__( 'Sticky Header', 'total' ),
		);
	}

	/**
	 * Function that will register admin page options.
	 *
	 * @since 3.5.0
	 */
	public function register_page_options() {

		// Register settings
		register_setting(
			'wpex_header_builder',
			'header_builder',
			array( $this, 'sanitize' )
		);

		// Register setting section
		add_settings_section(
			'wpex_header_builder_main',
			false,
			array( $this, 'section_main_callback' ),
			'wpex-header-builder-admin'
		);

		// Add settings
		$settings = $this->settings();
		foreach ( $settings as $key => $val ) {
			add_settings_field(
				$key,
				$val,
				array( $this, $key .'_field_callback' ),
				'wpex-header-builder-admin',
				'wpex_header_builder_main'
			);
		}

	}

	/**
	 * Sanitization callback.
	 *
	 * @since 3.5.0
	 */
	public function sanitize( $options ) {

		$settings = $this->settings();

		foreach ( $settings as $key => $val ) {

			$key = wp_strip_all_tags( $key );

			if ( 'top_bar' == $key ) {
				if ( empty( $options['top_bar'] ) ) {
					set_theme_mod( 'top_bar', false );
				} else {
					remove_theme_mod( 'top_bar' );
				}
				continue;
			}

			if ( 'sticky' == $key ) {
				if ( ! empty( $options['header_builder_sticky'] ) ) {
					set_theme_mod( 'header_builder_sticky', true );
				} else {
					remove_theme_mod( 'header_builder_sticky' );
				}
				continue;
			}

			if ( empty( $options[$key] ) ) {
				remove_theme_mod( 'header_builder_' . $key );
			} else {
				set_theme_mod( 'header_builder_' . $key, wp_strip_all_tags( $options[$key] ) );
			}

		}

		$options = '';
		return;

	}

	/**
	 * Main Settings section callback.
	 *
	 * @since 3.5.0
	 */
	public function section_main_callback( $options ) {
		// not needed
	}

	/**
	 * Fields callback functions.
	 *
	 * @since 3.5.0
	 */

	// Header Builder Page ID
	public function page_id_field_callback() {

		// Get header builder page ID
		$page_id = get_theme_mod( 'header_builder_page_id' ); ?>

		<select name="header_builder[page_id]" id="wpex-header-builder-select" class="wpex-chosen">

			<?php
			// Missing page
			if ( $page_id && FALSE === get_post_status( $page_id ) ) { ?>
				<option value="">-</option>
			<?php } ?>

			<option value=""><?php esc_html_e( 'None', 'total' ); ?></option>

			<?php if ( post_type_exists( 'templatera' ) ) {

				$templates = new WP_Query( array(
					'posts_per_page' => -1,
					'post_type'      => 'templatera',
				) );
				if ( $templates->have_posts() ) { ?>

					<optgroup label="<?php esc_html_e( 'WPBakery Templates', 'total' ); ?>">

						<?php while ( $templates->have_posts() ) {

							$templates->the_post();

							echo '<option value="' . intval( get_the_ID() ) . '"' . selected( $page_id, get_the_ID(), false ) . '>' . esc_html( get_the_title() ) . '</option>';

						}
						wp_reset_postdata(); ?>
					</optgroup>

				<?php }

			} ?>

			<?php if ( post_type_exists( 'elementor_library' ) ) {

				$templates = new WP_Query( array(
					'posts_per_page' => -1,
					'post_type'      => 'elementor_library',
				) );
				if ( $templates->have_posts() ) { ?>

					<optgroup label="<?php esc_html_e( 'Elementor Templates', 'total' ); ?>">

						<?php while ( $templates->have_posts() ) {

							$templates->the_post();

							echo '<option value="' . get_the_ID() . '"' . selected( $page_id, get_the_ID(), false ) . '>' . esc_html( get_the_title() ) . '</option>';

						}
						wp_reset_postdata(); ?>
					</optgroup>

				<?php }

			} ?>

			<optgroup label="<?php esc_html_e( 'Pages', 'total' ); ?>">
				<?php
				$pages = get_pages( array(
					'exclude' => get_option( 'page_on_front' ),
				) );
				if ( $pages ) {
					foreach ( $pages as $page ) {
						echo '<option value="' . intval( $page->ID ) . '"' . selected( $page_id, $page->ID, false ) . '>' . esc_html( $page->post_title ) . '</option>';
					}
				} ?>
			</optgroup>

		</select>

		<br /><br />

		<?php if ( WPEX_VC_ACTIVE ) { ?>
			<div class="wpex-create-new-template">
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=templatera' ) ); ?>"><?php echo esc_html__( 'Create new template', 'total' ); ?></a>
			</div>
		<?php } ?>

		<img src="<?php echo esc_url( includes_url( 'images/spinner.gif' ) ); ?>" class="wpex-edit-template-links-spinner" width="20" height="20" alt="<?php esc_html( 'Loading&hellip;', 'total' ); ?>" />

		<div class="wpex-edit-template-links-ajax" data-nonce="<?php echo wp_create_nonce( 'wpex_header_builder_edit_links_nonce' ); ?>" data-action="wpex_header_builder_edit_links"><?php $this->edit_links( $page_id ); ?></div>

	<?php }

	// Background Setting
	public function bg_field_callback() {

		// Get background
		$bg = get_theme_mod( 'header_builder_bg' ); ?>

		<input id="background_color" type="text" name="header_builder[bg]" value="<?php echo esc_attr( $bg ); ?>" class="wpex-color-field">

	<?php }

	// Background Image Setting
	public function bg_img_field_callback() {

		// Get background
		$bg = get_theme_mod( 'header_builder_bg_img' ); ?>

		<div class="uploader">
			<input class="wpex-media-input" type="text" name="header_builder[bg_img]" value="<?php echo esc_attr( $bg ); ?>">
			<button class="wpex-media-upload-button button-primary"><?php esc_attr_e( 'Select', 'total' ); ?></button>
			<button class="wpex-media-remove button-secondary"><?php esc_html_e( 'Remove', 'total' ); ?></button>
			<div class="wpex-media-live-preview">
				<?php if ( $preview = wpex_get_image_url( $bg ) ) { ?>
					<img src="<?php echo esc_url( $preview ); ?>" alt="<?php esc_html_e( 'Preview Image', 'total' ); ?>" />
				<?php } ?>
			</div>
		</div>

	<?php }

	// Background Image Style Setting
	public function bg_img_style_field_callback() {

		// Get setting
		$style = get_theme_mod( 'header_builder_bg_img_style' ); ?>

			<select name="header_builder[bg_img_style]">
			<?php
			$bg_styles = wpex_get_bg_img_styles();
			foreach ( $bg_styles as $key => $val ) { ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $style, $key, true ); ?>>
					<?php echo strip_tags( $val ); ?>
				</option>
			<?php } ?>
		</select>

	<?php }

	// Top bar setting callback
	public function top_bar_field_callback() {

		// Get theme mod val
		$val = get_theme_mod( 'top_bar', true ) ? 'on' : false; ?>

		<input type="checkbox" name="header_builder[top_bar]" id="wpex-header-builder-top-bar" <?php checked( $val, 'on' ); ?>>

	<?php }

	// Sticky setting callback
	public function sticky_field_callback() {

		// Get theme mod val
		$val = get_theme_mod( 'header_builder_sticky', false ) ? 'on' : false; ?>

		<input type="checkbox" name="header_builder[header_builder_sticky]" id="wpex-header-builder-sticky" <?php checked( $val, 'on' ); ?>>

	<?php }

	/**
	 * Settings page output.
	 *
	 * @since 3.5.0
	 */
	public function create_admin_page() {

		wp_enqueue_media();

		wp_enqueue_style( 'wpex-chosen' );
		wp_enqueue_script( 'wpex-chosen' );

		wp_enqueue_style( 'wpex-admin-pages' );
		wp_enqueue_script( 'wpex-admin-pages' );

		?>

		<div id="wpex-admin-page" class="wrap">

			<h1><?php esc_html_e( 'Header Builder', 'total' ); ?></h1>

			<p>
			<?php echo esc_html__( 'Use this setting to replace the default theme header with content created with WPBakery or other page builder. When enabled all Customizer settings for the Header will be removed. This is an advanced functionality so if this is the first time you use the theme we recommend you first test out the built-in header which can be customized at Appearance > Customize > Header.', 'total' ); ?>
			</p>

			<hr />

			<?php
			// Warning if builder page has been deleted
			$page_id = get_theme_mod( 'header_builder_page_id' );
			if ( $page_id && FALSE === get_post_status( $page_id ) ) {
				echo '<div class="notice notice-warning"><p>' . esc_html__( 'It appears the page you had selected has been deleted, please re-save your settings to prevent issues.', 'total' ) . '</p></div>';
			} ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'wpex_header_builder' ); ?>
				<?php do_settings_sections( 'wpex-header-builder-admin' ); ?>
				<?php submit_button(); ?>
			</form>

		</div>

	<?php }

	/**
	 * Remove the header and add custom header if enabled.
	 *
	 * @since 3.5.0
	 */
	public function alter_header() {

		// Remove all actions in header
		$hooks = wpex_theme_hooks();
		if ( isset( $hooks['header']['hooks'] ) ) {
			foreach( $hooks['header']['hooks'] as $hook ) {
				if ( 'wpex_hook_header_before' == $hook || 'wpex_hook_header_after' == $hook ) {
					continue;
				}
				remove_all_actions( $hook, false );
			}
		}

		// Insert header template to site via theme hooks
		add_action(
			apply_filters( 'wpex_header_builder_insert_hook', 'wpex_hook_header_inner' ),
			array( $this, 'get_part' ),
			apply_filters( 'wpex_header_builder_insert_priority', 0 )
		);

	}

	/**
	 * Alters get template.
	 *
	 * @since 3.5.0
	 */
	public function builder_template( $template ) {
		$redirect = false;
		$current_post = wpex_get_current_post_id();
		if ( isset( $_GET[ 'wpex_inline_header_template_editor' ] ) && $_GET[ 'wpex_inline_header_template_editor' ] == $current_post ) {
			$redirect = true;
		} elseif ( $current_post === self::get_template_id() ) {
			$redirect = true;
		}
		if ( $redirect ) {
			$new_template = locate_template( array( 'single-templatera.php' ) );
			if ( $new_template ) {
				return $new_template;
			}
		}
		return $template;
	}

	/**
	 * Add header builder to array of ID's with CSS to load site-wide.
	 *
	 * @since 3.5.0
	 */
	public function wpex_vc_css_ids( $ids ) {
		if ( $header_builder_id = self::get_template_id() ) {
			$ids[] = $header_builder_id;
		}
		return $ids;
	}

	/**
	 * Remove header customizer sections.
	 *
	 * @since 3.5.0
	 */
	public function remove_customizer_settings( $sections ) {
		unset( $sections['wpex_header_general'] );
		unset( $sections['wpex_header_logo'] );
		unset( $sections['wpex_header_logo_icon'] );
		unset( $sections['wpex_header_fixed'] );
		unset( $sections['wpex_header_menu'] );
		unset( $sections['wpex_menu_search'] );
		unset( $sections['wpex_fixed_menu'] );
		unset( $sections['wpex_header_mobile_menu'] );
		unset( $sections['wpex_header_overlay']['settings']['overlay_header_style'] );
		return $sections;
	}

	/**
	 * Remove typography settings.
	 *
	 * @since 4.7.1
	 */
	public function remove_typography_settings( $settings ) {
		unset( $settings['logo'] );
		unset( $settings['header_aside'] );
		unset( $settings['menu'] );
		unset( $settings['menu_dropdown'] );
		unset( $settings['mobile_menu'] );
		return $settings;
	}

	/**
	 * Gets the header builder template part if the header is enabled
	 *
	 * @since 3.5.0
	 */
	public function get_part() {
		if ( wpex_has_header() || wpex_vc_is_inline() ) {
			get_template_part( 'partials/header/header-builder' );
		}
	}

	/**
	 * Remove header meta that isn't needed anymore.
	 *
	 * @since 3.5.0
	 */
	public function remove_meta( $array, $post ) {
		if ( $post && $post->ID === self::get_template_id() ) {
			$array = ''; // remove on actual builderpage
		} else {
			unset( $array['header']['settings']['custom_menu'] );
			unset( $array['header']['settings']['overlay_header_style'] );
			unset( $array['header']['settings']['overlay_header_dropdown_style'] );
			unset( $array['header']['settings']['overlay_header_font_size'] );
			unset( $array['header']['settings']['overlay_header_logo'] );
			unset( $array['header']['settings']['overlay_header_logo_retina'] );
			unset( $array['header']['settings']['overlay_header_retina_logo_height'] );
		}
		return $array;
	}

	/**
	 * Custom CSS for header builder.
	 *
	 * @since 3.5.0
	 */
	public function custom_css( $css ) {
		$header_css = '';
		if ( $bg = get_theme_mod( 'header_builder_bg' ) ) {
			$header_css .= 'background-color:' . esc_attr( $bg ) . ';';
			$css .= '#site-header-sticky-wrapper.is-sticky #site-header{background-color:' . esc_url( $bg ) . ';}';
		}
		if ( $bg_img = wpex_get_image_url( get_theme_mod( 'header_builder_bg_img' ) ) ) {
			$header_css .= 'background-image:url(' . esc_url( $bg_img ) . ');';
		}
		if ( $bg_img && $bg_img_style = wpex_sanitize_data( get_theme_mod( 'header_builder_bg_img_style' ), 'background_style_css' ) ) {
			$header_css .= $bg_img_style;
		}
		if ( $header_css ) {
			$css .= '/*HEADER BUILDER*/#site-header.header-builder{ ' . $header_css . '}';
		}
		return $css;
	}

	/**
	 * Get edit links.
	 *
	 * @since 4.9
	 */
	public function edit_links( $template_id = '' ) {

		if ( ! $template_id ) {
			return;
		} ?>

		<a href="<?php echo esc_url( admin_url( 'post.php?post=' . intval( $template_id ) . '&action=edit' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Backend Edit', 'total' ); ?></a>

		<?php if ( WPEX_VC_ACTIVE && 'templatera' == get_post_type( $template_id ) ) { ?>

		&vert; <a href="<?php echo esc_url( admin_url( 'post.php?vc_action=vc_inline&post_id=' . $template_id . '&post_type=' . get_post_type( $template_id ) . '&wpex_inline_header_template_editor=' . $template_id ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Frontend Edit', 'total' ); ?></a>

		<?php } ?>

	<?php }

	/**
	 * Return correct edit links.
	 *
	 * @since 4.9
	 */
	public function ajax_edit_links() {

		if ( empty( $_POST['template_id'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpex_header_builder_edit_links_nonce' ) ) {
			wp_die();
		}

		$this->edit_links( $_POST['template_id'] );

		wp_die();

	}

}
new HeaderBuilder();