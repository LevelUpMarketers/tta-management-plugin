<?php
/**
 * Footer Builder
 *
 * @package Total WordPress theme
 * @subpackage Theme Builder
 * @version 5.0
 */

namespace TotalTheme;

use WP_Query;

defined( 'ABSPATH' ) || exit;

final class FooterBuilder {

	/**
	 * Start things up.
	 *
	 * @since 2.0.0
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
			add_action( 'wp_ajax_wpex_footer_builder_edit_links', array( $this, 'ajax_edit_links' ) );

		}

		// Run actions and filters if footer_builder ID is defined
		if ( $builder_post_id = self::get_template_id() || ! empty( $_GET[ 'wpex_inline_footer_template_editor' ] ) ) {

			// Do not register footer sidebars if disabled
			if ( ! get_theme_mod( 'footer_builder_footer_widgets', false ) ) {
				add_filter( 'wpex_register_footer_sidebars', '__return_false' );
			}

			// Admin edits
			if ( wpex_is_request( 'admin' ) ) {
				add_filter( 'wpex_customizer_panels', array( $this, 'remove_customizer_panels' ) );
				add_filter( 'wpex_customizer_sections', array( $this, 'alter_customizer_settings' ) );
				add_filter( 'wpex_typography_settings', array( $this, 'remove_typography_settings' ) );
			}

			if ( wpex_is_request( 'frontend' ) ) {

				// Alter the footer
				add_action( 'wp', array( $this, 'alter_footer' ) );

				// Include ID for Visual Composer custom CSS
				add_filter( 'wpex_vc_css_ids', array( $this, 'wpex_vc_css_ids' ) );

				// CSS
				add_filter( 'wpex_head_css', array( $this, 'wpex_head_css' ), 99 );

			}

			// Alter template for live editing
			if ( wpex_vc_is_inline() ) {
				add_filter( 'template_include', array( $this, 'builder_template' ), 9999 );
			}

		}

	}

	/**
	 * Returns footer template ID.
	 *
	 * @since 5.0
	 */
	public static function get_template_id() {
		$id = intval( apply_filters( 'wpex_footer_builder_page_id', get_theme_mod( 'footer_builder_page_id' ) ) );
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
	 * @since 2.0.0
	 */
	public function add_admin_submenu_page() {
		add_submenu_page(
			WPEX_THEME_PANEL_SLUG,
			esc_html__( 'Footer Builder', 'total' ),
			esc_html__( 'Footer Builder', 'total' ),
			'administrator',
			WPEX_THEME_PANEL_SLUG . '-footer-builder',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Function that will register admin page options.
	 *
	 * @since 2.0.0
	 */
	public function register_page_options() {

		// Register settings
		register_setting(
			'wpex_footer_builder',
			'footer_builder',
			array( $this, 'sanitize' )
		);

		// Add main section to our options page
		add_settings_section( 'wpex_footer_builder_main', false, array( $this, 'section_main_callback' ), 'wpex-footer-builder-admin' );

		// Custom Page ID
		add_settings_field(
			'footer_builder_page_id',
			esc_html__( 'Footer Builder page', 'total' ),
			array( $this, 'content_id_field_callback' ),
			'wpex-footer-builder-admin',
			'wpex_footer_builder_main'
		);

		// Footer Bottom
		add_settings_field(
			'footer_builder_footer_bottom',
			esc_html__( 'Footer Bottom', 'total' ),
			array( $this, 'footer_builder_footer_bottom_field_callback' ),
			'wpex-footer-builder-admin',
			'wpex_footer_builder_main'
		);

		// Footer Widgets
		add_settings_field(
			'footer_builder_footer_widgets',
			esc_html__( 'Footer Widgets', 'total' ),
			array( $this, 'footer_widgets_field_callback' ),
			'wpex-footer-builder-admin',
			'wpex_footer_builder_main'
		);

		// Fixed Footer
		add_settings_field(
			'fixed_footer',
			esc_html__( 'Fixed Footer', 'total' ),
			array( $this, 'fixed_footer_field_callback' ),
			'wpex-footer-builder-admin',
			'wpex_footer_builder_main'
		);

		// Footer Reveal
		add_settings_field(
			'footer_reveal',
			esc_html__( 'Footer Reveal', 'total' ),
			array( $this, 'footer_reveal_field_callback' ),
			'wpex-footer-builder-admin',
			'wpex_footer_builder_main'
		);

		// Bg
		add_settings_field(
			'bg',
			esc_html__( 'Background Color', 'total' ),
			array( $this, 'bg_field_callback' ),
			'wpex-footer-builder-admin',
			'wpex_footer_builder_main'
		);

		// BG img
		add_settings_field(
			'bg_img',
			esc_html__( 'Background Image', 'total' ),
			array( $this, 'bg_img_field_callback' ),
			'wpex-footer-builder-admin',
			'wpex_footer_builder_main'
		);

		// BG img style
		add_settings_field(
			'bg_img_style',
			esc_html__( 'Background Image Style', 'total' ),
			array( $this, 'bg_img_style_field_callback' ),
			'wpex-footer-builder-admin',
			'wpex_footer_builder_main'
		);

	}

	/**
	 * Sanitization callback.
	 *
	 * @since 2.0.0
	 */
	public function sanitize( $options ) {

		// Return if options are empty
		if ( empty( $options ) ) {
			return;
		}

		// Update footer builder page ID
		if ( ! empty( $options['content_id'] ) ) {
			set_theme_mod( 'footer_builder_page_id', $options['content_id'] );
		} else {
			remove_theme_mod( 'footer_builder_page_id' );
		}

		// Footer Bottom - Disabled by default
		if ( empty( $options['footer_builder_footer_bottom'] ) ) {
			remove_theme_mod( 'footer_builder_footer_bottom' );
		} else {
			set_theme_mod( 'footer_builder_footer_bottom', 1 );
		}

		// Footer Widgets - Disabled by default
		if ( empty( $options['footer_builder_footer_widgets'] ) ) {
			remove_theme_mod( 'footer_builder_footer_widgets' );
		} else {
			set_theme_mod( 'footer_builder_footer_widgets', 1 );
		}

		// Update fixed footer - Disabled by default
		if ( empty( $options['fixed_footer'] ) ) {
			remove_theme_mod( 'fixed_footer' );
		} else {
			set_theme_mod( 'fixed_footer', 1 );
		}

		// Update footer Reveal - Disabled by default
		if ( empty( $options['footer_reveal'] ) ) {
			remove_theme_mod( 'footer_reveal' );
		} else {
			set_theme_mod( 'footer_reveal', true );
		}

		// Update bg
		if ( empty( $options['bg'] ) ) {
			remove_theme_mod( 'footer_builder_bg' );
		} else {
			set_theme_mod( 'footer_builder_bg', wp_strip_all_tags( $options['bg'] ) );
		}

		// Update bg img
		if ( empty( $options['bg_img'] ) ) {
			remove_theme_mod( 'footer_builder_bg_img' );
		} else {
			set_theme_mod( 'footer_builder_bg_img', wp_strip_all_tags( $options['bg_img'] ) );
		}

		// Update bg img style
		if ( empty( $options['bg_img_style'] ) ) {
			remove_theme_mod( 'footer_builder_bg_img_style' );
		} else {
			set_theme_mod( 'footer_builder_bg_img_style', wp_strip_all_tags( $options['bg_img_style'] ) );
		}

		// Dont save anything in the options table
		$options = '';
		return;
	}

	/**
	 * Main Settings section callback.
	 *
	 * @since 2.0.0
	 */
	public function section_main_callback( $options ) {
		// Leave blank
	}

	/**
	 * Fields callback functions.
	 *
	 * @since 2.0.0
	 */

	// Footer Builder Page ID
	public function content_id_field_callback() {

		// Get footer builder page ID
		$page_id = get_theme_mod( 'footer_builder_page_id' ); ?>

		<select name="footer_builder[content_id]" id="wpex-footer-builder-select" class="wpex-chosen">

			<?php
			// Missing page
			if ( $page_id && FALSE === get_post_status( $page_id ) ) { ?>
				<option value="">-</option>
			<?php } ?>

			<option value=""><?php esc_html_e( 'None - Display Widgetized Footer', 'total' ); ?></option>

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

							echo '<option value="' . intval( get_the_ID() ) . '"' . selected( $page_id, get_the_ID(), false ) . '>' . esc_html( get_the_title() ) . '</option>';

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
						echo '<option value="' . intval( $page->ID ) . '"' . selected( $page_id, $page->ID, false )  .'>' . esc_html( $page->post_title ) . '</option>';
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

		<div class="wpex-edit-template-links-ajax" data-nonce="<?php echo wp_create_nonce( 'wpex_footer_builder_edit_links_nonce' ); ?>" data-action="wpex_footer_builder_edit_links"><?php $this->edit_links( $page_id ); ?></div>

	<?php }

	/**
	 * Footer Bottom Callback.
	 *
	 * @since 2.0.0
	 */
	public function footer_builder_footer_bottom_field_callback() {

		$val = get_theme_mod( 'footer_builder_footer_bottom', false ) ? 'on' : false;

		?>

		<input type="checkbox" name="footer_builder[footer_builder_footer_bottom]" id="wpex-footer-builder-footer-bottom" <?php checked( $val, 'on' ); ?>>

		<?php
	}

	/**
	 * Fixed Footer Callback.
	 *
	 * @since 2.0.0
	 */
	public function fixed_footer_field_callback() {

		$val = get_theme_mod( 'fixed_footer', false ) ? 'on' : false;

		?>

		<input type="checkbox" name="footer_builder[fixed_footer]" id="wpex-footer-builder-fixed" <?php checked( $val, 'on' ); ?>>

		<?php
	}

	/**
	 * Footer Reveal Callback.
	 *
	 * @since 2.0.0
	 */
	public function footer_reveal_field_callback() {

		$val = get_theme_mod( 'footer_reveal' ) ? 'on' : false;

		?>

		<input type="checkbox" name="footer_builder[footer_reveal]" id="wpex-footer-builder-reveal" <?php checked( $val, 'on' ); ?>>

		<?php
	}

	/**
	 * Footer Widgets Callback.
	 *
	 * @since 2.0.0
	 */
	public function footer_widgets_field_callback() {

		$val = get_theme_mod( 'footer_builder_footer_widgets', false ) ? 'on' : false;

		?>

		<input type="checkbox" name="footer_builder[footer_builder_footer_widgets]" id="wpex-footer-builder-widgets" <?php checked( $val, 'on' ); ?>>

		<?php
	}

	// Background Setting
	public function bg_field_callback() {

		// Get background
		$bg = get_theme_mod( 'footer_builder_bg' );

		?>

		<input id="background_color" type="text" name="footer_builder[bg]" value="<?php echo esc_attr( $bg ); ?>" class="wpex-color-field">

	<?php }

	// Background Image Setting
	public function bg_img_field_callback() {

		$bg = get_theme_mod( 'footer_builder_bg_img' );

		?>

		<div class="uploader">
			<input class="wpex-media-input" type="text" name="footer_builder[bg_img]" value="<?php echo esc_attr( $bg ); ?>">
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

		$style = get_theme_mod( 'footer_builder_bg_img_style' );

		?>

		<select name="footer_builder[bg_img_style]">
			<?php
			$bg_styles = wpex_get_bg_img_styles();
			foreach ( $bg_styles as $key => $val ) { ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $style, $key, true ); ?>>
					<?php echo strip_tags( $val ); ?>
				</option>
			<?php } ?>
		</select>

	<?php }

	/**
	 * Settings page output.
	 *
	 * @since 2.0.0
	 */
	public function create_admin_page() {

		wp_enqueue_media();

		wp_enqueue_style( 'wpex-chosen' );
		wp_enqueue_script( 'wpex-chosen' );

		wp_enqueue_style( 'wpex-admin-pages' );
		wp_enqueue_script( 'wpex-admin-pages' );

		?>

		<div id="wpex-admin-page" class="wrap">

			<h1><?php esc_html_e( 'Footer Builder', 'total' ); ?></h1>

			<p><?php echo esc_html__( 'By default the footer consists of a simple widgetized area which you can control via the WordPress Customizer. For more complex layouts you can use the option below to select a template and create your own custom footer layout from scratch.', 'total' ); ?></p>

			<hr />

			<?php
			// Warning if footer builder page doesn't exist
			$page_id = get_theme_mod( 'footer_builder_page_id' );
			if ( $page_id && FALSE === get_post_status( $page_id ) ) {

				echo '<div class="notice notice-warning"><p>' . esc_html__( 'It appears the page you had selected has been deleted, please re-save your settings to prevent issues.', 'total' ) . '</p></div>';

			} ?>

			<form method="post" action="options.php">

				<?php settings_fields( 'wpex_footer_builder' ); ?>
				<?php do_settings_sections( 'wpex-footer-builder-admin' ); ?>
				<?php submit_button(); ?>
			</form>

		</div>

	<?php }

	/**
	 * Alters get template.
	 *
	 * @since 3.5.0
	 */
	public function builder_template( $template ) {
		$redirect = false;
		$current_post = wpex_get_current_post_id();
		if ( isset( $_GET[ 'wpex_footer_template' ] ) && $_GET[ 'wpex_footer_template' ] == $current_post ) {
			$redirect = true;
		} elseif ( self::get_template_id() == $current_post ) {
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
	 * Alter the footer.
	 *
	 * @since 5.0
	 */
	public function alter_footer() {

		// Insert footer builder to site via theme hooks
		$this->insert_hook     = apply_filters( 'wpex_footer_builder_insert_hook', 'wpex_hook_footer_before' );
		$this->insert_priority = apply_filters( 'wpex_footer_builder_insert_priority', 40 );

		add_action( $this->insert_hook, array( $this, 'get_part' ), $this->insert_priority );

	}

	/**
	 * Add footer builder to array of ID's with CSS to load site-wide.
	 *
	 * @since 2.0.0
	 */
	public function wpex_vc_css_ids( $ids ) {
		$footer_builder_id = self::get_template_id(); // Get translated footer ID
		if ( $footer_builder_id ) {
			$ids[] = $footer_builder_id;
		}
		return $ids;
	}

	/**
	 * Remove customizer panels.
	 *
	 * @since 2.0.0
	 */
	public function remove_customizer_panels( $panels ) {
		if ( ! get_theme_mod( 'footer_builder_footer_widgets', false ) ) {
			unset( $panels['footer_widgets'] );
		}
		if ( ! get_theme_mod( 'footer_builder_footer_bottom', false ) ) {
			unset( $panels['footer_bottom'] );
		}
		return $panels;
	}

	/**
	 * Remove customizer settings.
	 *
	 * @since 2.0.0
	 */
	public function alter_customizer_settings( $sections ) {
		if ( get_theme_mod( 'footer_builder_footer_widgets', false ) ) {
			unset( $sections['wpex_footer_widgets']['settings']['footer_widgets'] );
			unset( $sections['wpex_footer_widgets']['settings']['fixed_footer'] );
			unset( $sections['wpex_footer_widgets']['settings']['footer_reveal'] );
		}
		if ( get_theme_mod( 'footer_builder_footer_bottom', false ) ) {
			unset( $sections['wpex_footer_bottom']['settings']['footer_bottom'] );
		}
		return $sections;
	}

	/**
	 * Remove typography settings.
	 *
	 * @since 4.7.1
	 */
	public function remove_typography_settings( $settings ) {
		if ( ! get_theme_mod( 'footer_builder_footer_widgets', false ) ) {
			unset( $settings['footer_widgets'] );
			unset( $settings['footer_widget_title'] );
		}
		if ( ! get_theme_mod( 'footer_builder_footer_bottom', false ) ) {
			unset( $settings['copyright'] );
			unset( $settings['footer_menu'] );
		}
		return $settings;
	}

	/**
	 * Gets the footer builder template part if the footer is enabled.
	 *
	 * @since 2.0.0
	 */
	public function get_part() {
		if ( wpex_has_footer() ) {
			get_template_part( 'partials/footer/footer-builder' );
		}
	}

	/**
	 * Custom CSS for footer builder.
	 *
	 * @since 3.5.0
	 */
	public function wpex_head_css( $css ) {
		$add_css = '';
		if ( $bg = get_theme_mod( 'footer_builder_bg' ) ) {
			$add_css .= 'background-color:' . wp_strip_all_tags( $bg ) . ';';
		}
		if ( $bg_img = wpex_get_image_url( get_theme_mod( 'footer_builder_bg_img' ) ) ) {
			$add_css .= 'background-image:url(' . esc_url( $bg_img ) . ');';
		}
		if ( $bg_img && $bg_img_style = wpex_sanitize_data( get_theme_mod( 'footer_builder_bg_img_style' ), 'background_style_css' ) ) {
			$add_css .= $bg_img_style;
		}
		if ( $add_css ) {
			$add_css = '#footer-builder{ ' . $add_css . '}';
			$css .= '/*FOOTER BUILDER*/' . $add_css;
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

		&vert; <a href="<?php echo esc_url( admin_url( 'post.php?vc_action=vc_inline&post_id=' . $template_id . '&post_type=' . get_post_type( $template_id ) . '&wpex_inline_footer_template_editor=' . $template_id ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Frontend Edit', 'total' ); ?></a>

		<?php } ?>

	<?php }

	/**
	 * Return correct edit links.
	 *
	 * @since 4.9
	 */
	public function ajax_edit_links() {

		if ( empty( $_POST['template_id'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpex_footer_builder_edit_links_nonce' ) ) {
			wp_die();
		}

		$this->edit_links( $_POST['template_id'] );

		wp_die();

	}


}
new FooterBuilder();