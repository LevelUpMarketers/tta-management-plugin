<?php
/**
 * WPEX Font Manager
 *
 * @version 5.0
 * @copyright WPExplorer.com - All rights reserved.
 *
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Font_Manager' ) ) {

	final class WPEX_Font_Manager {

		/**
		 * Post type used to store custom fonts.
		 */
		public $post_type = 'wpex_font';

		/**
		 * Check if we have gotten registered fonts or not.
		 */
		public static $get_registered_fonts = false;

		/**
		 * Holds array of registered fonts.
		 */
		public static $registered_fonts = array();

		/**
		 * Our single WPEX_Font_Manager instance.
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
		 */
		final public function __wakeup() {
			throw new Exception( 'You\'re doing things wrong.' );
		}

		/**
		 * Create or retrieve the instance of WPEX_Font_Manager.
		 */
		public static function instance() {
			if ( is_null( static::$instance ) ) {
				static::$instance = new WPEX_Font_Manager;
				static::$instance->init_hooks();
			}

			return static::$instance;
		}

		/**
		 * Hook into actions and filters.
		 */
		public function init_hooks() {

			if ( ! function_exists( 'wp_parse_list' ) ) {
				return; // This function was added in WP 5.1 - @todo remove at some point in time
			}

			add_action( 'init', array( $this, 'register_type' ) );
			add_action( 'init', array( $this, 'get_registered_fonts' ) );

			if ( $this->is_request( 'admin' ) ) {

				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_head', array( $this, 'remove_admin_column_filter' ) );
				add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'set_admin_columns' ) );
				add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'show_admin_columns' ), 10, 2 );

				add_action( 'admin_head-post.php', array( $this, 'add_back_button' ) );

				if ( class_exists( 'Vc_Manager' ) ) {
					add_filter( 'vc_is_valid_post_type_be', array( $this, 'disable_wpbakery' ), 10, 2 );
					add_filter( 'vc_show_button_fe', array( $this, 'remove_wpbakery_button_fe' ), 10, 3 );
				}

				add_action( 'admin_init', array( $this, 'register_metaboxes' ) );

				add_filter( 'upload_mimes', array( $this, 'add_fonts_to_allowed_mimes' ) );

			}

		}

		/**
		 * Check current request.
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return wp_doing_ajax();
				case 'frontend':
					return ( ! is_admin() || wp_doing_ajax() ); // @todo should we check ! wp_doing_cron() ?
			}
		}

		/**
		 * Register wpex_fonts type.
		 */
		public function register_type() {

			register_post_type( $this->post_type, array(
				'labels' => array(
					'name'               => esc_html__( 'Font Manager', 'total-theme-core' ),
					'singular_name'      => esc_html__( 'Font', 'total-theme-core' ),
					'add_new'            => esc_html__( 'Add New Font' , 'total-theme-core' ),
					'add_new_item'       => esc_html__( 'Add New Font' , 'total-theme-core' ),
					'edit_item'          => esc_html__( 'Edit Font' , 'total-theme-core' ),
					'new_item'           => esc_html__( 'New Font' , 'total-theme-core' ),
					'view_item'          => esc_html__( 'View Font', 'total-theme-core' ),
					'search_items'       => esc_html__( 'Search Fonts', 'total-theme-core' ),
					'not_found'          => esc_html__( 'No Fonts found', 'total-theme-core' ),
					'not_found_in_trash' => esc_html__( 'No Fonts found in Trash', 'total-theme-core' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'_builtin'        => false,
				'capability_type' => 'page',
				'hierarchical'    => false,
				'rewrite'         => false,
				'supports'        => array( 'title' ),
				'show_in_menu'    => 'edit.php?post_type=wpex_fonts',
			) );

		}

		/**
		 * Register new admin menu.
		 */
		public function admin_menu() {

			$parent_slug = defined( 'WPEX_THEME_PANEL_SLUG' ) ? WPEX_THEME_PANEL_SLUG : 'themes.php';

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Font Manager', 'total-theme-core' ),
				esc_html__( 'Font Manager', 'total-theme-core' ),
				'administrator',
				'edit.php?post_type=' . $this->post_type
			);

		}

		/**
		 * Remove the admin columns sort filter.
		 */
		public function remove_admin_column_filter() {

			$screen = get_current_screen();

			if ( $this->post_type == $screen->post_type ) {
				add_filter( 'months_dropdown_results', '__return_empty_array' );
			}

		}

		/**
		 * Set admin columns.
		 */
		public function set_admin_columns( $columns ) {

			$columns['font_name'] = esc_html__( 'Font Name', 'total-theme-core' );
			$columns['type']      = esc_html__( 'Type', 'total-theme-core' );
			$columns['fallback']  = esc_html__( 'Fallback', 'total-theme-core' );
			$columns['assign_to'] = esc_html__( 'Assigned To', 'total-theme-core' );
			$columns['is_global'] = esc_html__( 'Global?', 'total-theme-core' );

			unset( $columns['date'] );

			return $columns;

		}

		/**
		 * Show admin columns.
		 */
		public function show_admin_columns( $column, $post_id ) {

			$inline_style = 'font-size:24px;line-height:normal;';

			switch( $column ) {

				case 'font_name' :

					$font_name = get_post_meta( $post_id, 'name', true );
					$type = get_post_meta( $post_id, 'type', true );
					$parsed_font_name = self::sanitize_font_name( $font_name, $type );

					if ( 'custom' == $type ) {
						if ( function_exists( 'wpex_render_custom_font_css' ) ) {
							$custom_font_css = wpex_render_custom_font_css( $font_name );
							if ( $custom_font_css ) {
								echo '<style>' . wp_strip_all_tags( $custom_font_css ) . '</style>';
							}
						}
						$inline_style .= 'font-family:"' . esc_attr( self::sanitize_font_name( $font_name ) ) . '";';
					} elseif ( 'google' == $type || 'adobe' == $type ) {
						if ( function_exists( 'wpex_enqueue_font' ) && function_exists( 'wpex_sanitize_font_family' ) ) {
							wpex_enqueue_font( $parsed_font_name );
							$inline_style .= 'font-family:' . esc_attr( wpex_sanitize_font_family( $parsed_font_name ) ) . ';';
						}
					}

					echo '<div style="' . esc_attr( $inline_style ) . '">' . wp_strip_all_tags( $font_name ) . '</div>';

				break;

				case 'type' :

					$type = wp_strip_all_tags( get_post_meta( $post_id, 'type', true ) );

					$font_types = $this->choices_font_types();

					if ( $type && isset( $font_types[ $type ]) ) {
						echo esc_html( $font_types[$type] );
					}

				break;

				case 'fallback' :

					$fallback = wp_strip_all_tags( get_post_meta( $post_id, 'fallback', true ) );

					if ( $fallback ) {
						echo esc_html( $fallback );
					} else {
						echo '&#8212;';
					}

				break;

				case 'assign_to' :

					$assign_to = wp_parse_list( wp_strip_all_tags( get_post_meta( $post_id, 'assign_to', true ) ) );

					if ( $assign_to ) {
						foreach( $assign_to as $el ) {
							echo '<p><code>' . esc_html( $el ) . '</code></p>';
						}
					} else {
						echo '&#8212;';
					}

				break;

				case 'is_global' :

					$is_global = (bool) get_post_meta( $post_id, 'is_global', true );
					$has_assigned_to = (bool) get_post_meta( $post_id, 'assign_to', true );

					if ( $is_global || $has_assigned_to ) {
						echo '<span class="dashicons dashicons-yes" aria-hidden="true" style="color:green;"><div class="screen-reader-text">' . esc_html__( 'Yes', 'total-theme-core' ) . '</div>';
					} else {
						echo '<span class="dashicons dashicons-no-alt" aria-hidden="true" style="color:red;"></span><div class="screen-reader-text">' . esc_html__( 'No', 'total-theme-core' ) . '</div>';
					}

				break;

			}

		}

		/**
		 * Add a back button to the Font Manager main page.
		 */
		public function add_back_button() {

			global $current_screen;

			if ( 'wpex_font' !== $current_screen->post_type ) {
				return;
			}

			wp_enqueue_script( 'jQuery' );

			?>

		    <script>
			    jQuery( function() {
					jQuery( 'body.post-type-wpex_font .wrap h1' ).append( '<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wpex_font' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Back to Font Manager', 'total-theme-core' ); ?></a>' );
				} );
		    </script>

		    <?php
		}

		/**
		 * Disable wpbakery builder from post type.
		 */
		public function disable_wpbakery( $check, $type ) {
			if ( $this->post_type == $type ) {
				return false;
			}
			return $check;
		}

		/**
		 * Removes the edit with wpbakery button from the admin screen.
		 */
		public function remove_wpbakery_button_fe( $result, $post_id, $type ) {
			if ( $this->post_type == $type ) {
				return false;
			}
			return $result;
		}

		/**
		 * Register metaboxes.
		 */
		public function register_metaboxes() {

			if ( class_exists( 'WPEX_Meta_Factory' ) ) {

				new WPEX_Meta_Factory( $this->general_meta_box() );

				new WPEX_Meta_Factory( $this->google_meta_box() );

				new WPEX_Meta_Factory( $this->adobe_meta_box() );

				new WPEX_Meta_Factory( $this->custom_meta_box() );

				new WPEX_Meta_Factory( $this->assign_meta_box() );

			}


		}

		/**
		 * General Metabox
		 */
		public function general_meta_box() {

			return array(
				'id'       => 'general',
				'title'    => esc_html__( 'Font Settings', 'total-theme-core' ),
				'screen'   => array( $this->post_type ),
				'context'  => 'normal',
				'priority' => 'high',
				'scripts'  => array(
					array(
						'wpex-font-manager',
						plugin_dir_url( __FILE__ ) . 'assets/wpex-font-manager.min.js',
						array( 'jquery' ),
						'1.0',
						true
					)
				),
				'fields'   => array(
					array(
						'name'     => esc_html__( 'Type', 'total-theme-core' ),
						'id'       => 'type',
						'type'     => 'select',
						'required' => true,
						'desc'     => esc_html__( 'Select your font type.', 'total-theme-core' ),
						'choices'  => $this->choices_font_types(),
						'after_hook' => '<a href="https://fonts.google.com/" target="_blank" rel="nofollow noopener noreferrer" class="wpex-visit-google-btn wpex-mf-hidden button button-primary">' . esc_html__( 'Visit Google Fonts', 'total-theme-core' ) . '</a><a href="https://fonts.adobe.com/fonts" target="_blank" rel="nofollow noopener noreferrer" class="wpex-visit-adobe-btn wpex-mf-hidden button button-primary">' . esc_html__( 'Visit Adobe Fonts', 'total-theme-core' ) . '</a>',
					),
					array(
						'name'       => esc_html__( 'Font Name', 'total-theme-core' ),
						'id'         => 'name',
						'type'       => 'text',
						'desc'       => esc_html__( 'Your exact font name (case sensitive).', 'total-theme-core' ),
						'required'   => true
					),
					array(
						'name'    => esc_html__( 'Font Display', 'total-theme-core' ),
						'id'      => 'display',
						'type'    => 'select',
						'desc'    => esc_html__( 'Select your font-display value.', 'total-theme-core' ),
						'choices' => $this->choices_font_display(),
					),
					array(
						'name'    => esc_html__( 'Fallback', 'total-theme-core' ),
						'id'      => 'fallback',
						'type'    => 'select',
						'desc'    => esc_html__( 'Select your fallback font.', 'total-theme-core' ),
						'choices' => $this->choices_fallback_fonts(),
					),
					array(
						'name'    => esc_html__( 'Load Font Site Wide?', 'total-theme-core' ),
						'id'      => 'is_global',
						'type'    => 'checkbox',
						'desc'    => esc_html__( 'Check the box to load this font on the entire site.', 'total-theme-core' ),
						'default' => false,
					),
				)
			);

		}

		/**
		 * Upload Metabox
		 */
		public function google_meta_box() {

			return array(
				'id'         => 'google',
				'title'      => esc_html__( 'Google Font Settings', 'total-theme-core' ),
				'screen'     => array( $this->post_type ),
				'context'    => 'normal',
				'priority'   => 'default',
				'fields'     => array(
					array(
						'name' => esc_html__( 'Load Italics', 'total-theme-core' ),
						'id'   => 'google_italic',
						'type' => 'checkbox',
						'desc' => esc_html__( 'Load italic styles for this font?', 'total-theme-core' ),
					),
					array(
						'name'    => esc_html__( 'Font Weights', 'total-theme-core' ),
						'id'      => 'google_font_weights',
						'type'    => 'multi_select',
						'desc'    => esc_html__( 'Select the font weights to load. Make sure to only select font weights available for the desired font family.', 'total-theme-core' ),
						'choices' => array(
							'100' => '100',
							'200' => '200',
							'300' => '300',
							'400' => '400',
							'500' => '500',
							'600' => '600',
							'700' => '700',
							'800' => '800',
							'900' => '900',
						),
					),
					array(
						'name'    => esc_html__( 'Font Subsets', 'total-theme-core' ),
						'id'      => 'google_subsets',
						'type'    => 'multi_select',
						'desc'    => esc_html__( 'Select the font subsets to load for browsers that do not suppot unicode-range.', 'total-theme-core' ),
						'choices' => array(
							'latin'        => 'latin',
							'latin-ext'    => 'latin-ext',
							'cyrillic'     => 'cyrillic',
							'cyrillic-ext' => 'cyrillic-ext',
							'greek'        => 'greek',
							'greek-ext'    => 'greek-ext',
							'vietnamese'   => 'vietnamese',
						),
					),
				)
			);

		}

		/**
		 * Adobe Metabox.
		 */
		public function adobe_meta_box() {

			return array(
				'id'         => 'adobe',
				'title'      => esc_html__( 'Adobe Settings', 'total-theme-core' ),
				'screen'     => array( $this->post_type ),
				'context'    => 'normal',
				'priority'   => 'default',
				'fields'     => array(
					array(
						'name' => esc_html__( 'Project ID', 'total-theme-core' ),
						'id'   => 'adobe_project_id',
						'type' => 'text',
						'desc' => esc_html__( 'Enter your adobe project ID.', 'total-theme-core' ),
					),
				)
			);

		}

		/**
		 * Upload Files Metabox.
		 */
		public function custom_meta_box() {

			return array(
				'id'         => 'custom',
				'title'      => esc_html__( 'Custom Fonts', 'total-theme-core' ),
				'screen'     => array( $this->post_type ),
				'context'    => 'normal',
				'priority'   => 'default',
				'fields'     => array(
					array(
						'id'		  => 'custom_fonts',
						'type'        => 'group',
						'name'        => esc_html__( 'Font Variations', 'total-theme-core' ),
						'desc'        => esc_html__( 'Upload or select your custom font files from the Media Library.', 'total-theme-core' ),
						'group_title' => esc_html__( 'Variation', 'total-theme-core' ),
						'fields'      => array(
							array(
								'name' => esc_html__( 'Font Weight', 'total-theme-core' ),
								'id'   => 'weight',
								'type' => 'select',
								'choices' => array(
									'100' => '100',
									'200' => '200',
									'300' => '300',
									'400' => '400',
									'500' => '500',
									'600' => '600',
									'700' => '700',
									'800' => '800',
									'900' => '900',
								),
							),
							array(
								'name' => esc_html__( 'Font Style', 'total-theme-core' ),
								'id'   => 'style',
								'type' => 'select',
								'choices' => array(
									'normal' => esc_html__( 'Normal', 'total-theme-core' ),
									'italic' => esc_html__( 'Italic', 'total-theme-core' ),
								),
							),
							array(
								'name' => esc_html__( 'WOFF2 File', 'total-theme-core' ),
								'id'   => 'woff2',
								'type' => 'upload',
							),
							array(
								'name' => esc_html__( 'WOFF File (optional)', 'total-theme-core' ),
								'id'   => 'woff',
								'type' => 'upload',
							),
						),
					),
				)
			);

		}

		/**
		 * Assign Font
		 */
		public function assign_meta_box() {

			return array(
				'id'         => 'assign',
				'title'      => esc_html__( 'Target Elements (Optional)', 'total-theme-core' ),
				'screen'     => array( $this->post_type ),
				'context'    => 'normal',
				'priority'   => 'default',
				'fields'     => array(
					array(
						'name' => esc_html__( 'Assign Font to Elements', 'total-theme-core' ),
						'id'   => 'assign_to',
						'type' => 'textarea',
						'desc' => esc_html__( 'Enter a list of ID\'s, classnames or element tags to target with this Font Family. Hit enter after each element or separate using commas.', 'total-theme-core' ),
					),
				)
			);

		}

		/**
		 * Return array of font types.
		 */
		public function choices_font_types() {
			return array(
				''        => '&#8212; ' . esc_html__( 'Select', 'total-theme-core' ) . ' &#8212;',
				'google'  => 'Google',
				'adobe'   => 'Adobe',
				'custom'  => esc_html__( 'Custom/Upload', 'total-theme-core' ),
				'other'   => esc_html__( 'Child Theme or Other', 'total-theme-core' ),
			);
		}

		/**
		 * Return fallback font choices.
		 */
		public function choices_fallback_fonts() {
			$fallback_fonts = array(
				''           => esc_html__( 'No Fallback', 'total-theme-core' ),
				'sans-serif' => 'sans-serif',
				'serif'      => 'serif',
				'monospace'  => 'monospace',
				'cursive'    => 'cursive',
			);
			return apply_filters( 'wpex_font_manager_choices_fallback_fonts', $fallback_fonts );
		}

		/**
		 * Return font-display choices.
		 */
		public function choices_font_display() {
			return array(
				'swap'     => 'swap',
				'auto'     => 'auto',
				'block'    => 'block',
				'fallback' => 'fallback',
				'optional' => 'optional',
			);
		}

		/**
		 * Return all registered fonts.
		 */
		public static function get_registered_fonts() {

			if ( self::$get_registered_fonts ) {
				return self::$registered_fonts;
			}

			$fonts = get_posts( array(
				'numberposts' 	   => 50,
				'post_type' 	   => 'wpex_font',
				'post_status'      => 'publish',
				'suppress_filters' => false,
				'fields'           => 'ids',
			) );

			if ( ! $fonts ) {
				return;
			}

			foreach ( $fonts as $font ) {

				$type = wp_strip_all_tags( get_post_meta( $font, 'type', true ) );

				if ( empty( $type ) ) {
					continue;
				}

				$name = self::sanitize_font_name( get_post_meta( $font, 'name', true ), $type );

				if ( empty( $name ) ) {
					continue;
				}

				$font_args = array(
					'type' => $type,
				);

				$fallback = wp_strip_all_tags( get_post_meta( $font, 'fallback', true ) );

				if ( $fallback ) {
					$font_args['fallback'] = $fallback;
				}

				$is_global = wp_validate_boolean( get_post_meta( $font, 'is_global', true ) );

				$assign_to = wp_parse_list( wp_strip_all_tags( get_post_meta( $font, 'assign_to', true ) ) );

				if ( $assign_to ) {
					$font_args['assign_to'] = $assign_to;
				}

				switch( $type ) {

					case 'google';

						$font_args['display'] = self::sanitize_font_display( get_post_meta( $font, 'display', true ) );

						$font_args['italic']  = (bool) get_post_meta( $font, 'google_italic', true );

						if ( $weights = get_post_meta( $font, 'google_font_weights', true ) ) {
							$font_args['weights'] = (array) $weights;
						}

						if ( $is_global ) {
							$font_args['is_global'] = true;
						}

					break;

					case 'adobe';

						if ( $project_id = get_post_meta( $font, 'adobe_project_id', true ) ) {
							$font_args['project_id'] = trim( wp_strip_all_tags( $project_id ) );
						}

						if ( $is_global ) {
							$font_args['is_global'] = true;
						}

					break;

					case 'custom';

						$files = get_post_meta( $font, 'custom_fonts', true );

						if ( ! empty( $files ) && is_array( $files ) ) {
							$font_args['custom_fonts'] = $files;
						}

						$font_args['display'] = self::sanitize_font_display( get_post_meta( $font, 'display', true ) );

					break;

				}

				self::$registered_fonts[$name] = $font_args;

			} // end foreach

			self::$get_registered_fonts = true; // prevent extra get_posts checks if there aren't any custom fonts added

			//var_dump( self::$registered_fonts );

			return self::$registered_fonts;

		}

		/**
		 * Validate font-display.
		 */
		public static function sanitize_font_display( $display = null, $fallback = 'swap' ) {

			if ( ! $display && $fallback ) {
				return $fallback;
			}

			$allowed_displays = array(
				'swap'     => 'swap',
				'auto'     => 'auto',
				'block'    => 'block',
				'fallback' => 'fallback',
				'optional' => 'optional',
			);

			if ( in_array( $display, $allowed_displays ) ) {
				return $display;
			}

		}

		/**
		 * Sanitize Font Name
		 */
		public static function sanitize_font_name( $font_name, $type = '' ) {

			switch ( $type ) {
				case 'adobe':
					$font_name = strtolower( str_replace( ' ', '-', $font_name ) );
					break;
			}

			return wp_strip_all_tags( $font_name );

		}

		/**
		 * Allowed mime types and file extensions.
		 *
		 * @since 1.2
		 */
		public static function add_fonts_to_allowed_mimes( $mimes ) {
			$mimes['woff2'] = 'application/x-font-woff2';
			$mimes['woff']  = 'application/x-font-woff';
			//$mimes['ttf'] = 'application/x-font-ttf';
			return $mimes;
		}

	}

	WPEX_Font_Manager::instance();

}