<?php
/**
 * Theme Settings Metabox
 *
 * @package TotalThemeCore
 * @version 1.2
 */

namespace TotalThemeCore;

use WP_Query;

defined( 'ABSPATH' ) || exit;

final class MetaBoxes {

	/*
	 * Array of post types to display metabox on.
	 *
	 * @var array()
	 */
	public $post_types;

	/*
	 * Array of metabox settings.
	 *
	 * @var array()
	 */
	public $settings;

	/**
	 * Our single MetaBoxes instance.
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
	 * Create or retrieve the instance of MetaBoxes.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new MetaBoxes;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Start things up on init so we can hook into the various filters.
	 */
	public function admin_init() {

		if ( ! apply_filters( 'wpex_metaboxes', true ) ) {
			return;
		}

		// Post types to add the metabox to
		$this->post_types = apply_filters( 'wpex_main_metaboxes_post_types', array(
			'post'         => 'post',
			'page'         => 'page',
			'portfolio'    => 'portfolio',
			'staff'        => 'staff',
			'testimonials' => 'testimonials',
			'product'      => 'product',
		) );

		// Toolset support
		if ( defined( 'TYPES_VERSION' ) ) {
			$this->add_toolset_types();
		}

		// Loop through post types and add metabox to corresponding post types.
		if ( $this->post_types ) {
			foreach( $this->post_types as $key => $val ) {
				add_action( 'add_meta_boxes_' . $val, array( $this, 'post_meta' ), 11 );
			}
		}

		// Save meta
		add_action( 'save_post', array( $this, 'save_meta_data' ) );

	}

	/**
	 * The function responsible for creating the actual meta box.
	 */
	public function post_meta( $post ) {

		// Disable on footer builder
		$footer_builder_page = get_theme_mod( 'footer_builder_page_id' );
		if ( $footer_builder_page && 'page' == get_post_type( $post->ID ) && $footer_builder_page == $post->ID ) {
			return;
		}

		// Check if settings are empty
		if ( ! $this->meta_array( $post ) ) {
			return;
		}

		// Add metabox
		add_meta_box(
			'wpex-metabox',
			esc_html__( 'Theme Settings', 'total-theme-core' ),
			array( $this, 'display_meta_box' ),
			$post->post_type,
			'normal',
			'high'
		);

	}

	/**
	 * Enqueue scripts and styles needed for the metaboxes.
	 */
	public function load_scripts() {

		// Enqueue metabox css
		wp_enqueue_style(
			'wpex-post-metabox',
			TTC_PLUGIN_DIR_URL . 'assets/css/metabox.css',
			array(),
			'1.0'
		);

		// Enqueue media js
		wp_enqueue_media();

		// Enqueue color picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Load alpa color picker if Nextgen is not active because it breaks things
		if ( apply_filters( 'wpex_metabox_alpha_color_picker', true ) && ! class_exists( 'C_NextGEN_Bootstrap' ) ) {

			wp_enqueue_script( 'wp-color-picker-alpha',
				TTC_PLUGIN_DIR_URL . 'assets/js/wp-color-picker-alpha.min.js',
				array( 'wp-color-picker' ),
				'2.1.4',
				true
			);

		}

		// Enqueue metabox js
		wp_enqueue_script(
			'wpex-post-metabox',
			TTC_PLUGIN_DIR_URL . 'assets/js/metabox.min.js',
			array( 'jquery', 'wp-color-picker' ),
			'1.0',
			true
		);

		wp_localize_script( 'wpex-post-metabox', 'wpexMB', array(
			'reset'  => esc_html__(  'Reset Settings', 'total-theme-core' ),
			'cancel' => esc_html__(  'Cancel Reset', 'total-theme-core' ),
		) );

	}

	/**
	 * Renders the content of the meta box.
	 */
	public function display_meta_box( $post ) {

		// Get current post data
		$post_id   = $post->ID;
		$post_type = get_post_type();

		// Get tabs
		$tabs = $this->meta_array( $post );

		// Make sure tabs aren't empty
		if ( empty( $tabs ) ) {
			return;
		}

		// What tab should be open by default
		$open_tab = '';

		// Store tabs that should display on this specific page in an array for use later
		$active_tabs = array();
		foreach ( $tabs as $tab_key => $tab ) {

			$tab_post_type = $this->get_tab_post_type( $tab );

			if ( ! $tab_post_type || in_array( $post_type, $tab_post_type ) ) {
				$active_tabs[$tab_key] = $tab;
			}

			if ( $tab_post_type && in_array( $post_type, $tab_post_type ) ) {
				$open_tab = $tab_key;
			}

		}

		// No active tabs
		if ( empty( $active_tabs ) ) {
			echo $empty_notice;
			return;
		}

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'wpex_metabox', 'wpex_metabox_nonce' );

		// Enqueue scripts needed for metabox
		$this->load_scripts();

		// Get tab count
		$tab_count = count( $active_tabs );

		if ( $tab_count > 1 ) {

			$tabs_output = '';

			$tabs_output .= '<ul class="wp-tab-bar">';

				$count = 0;
				foreach ( $active_tabs as $tab_key => $tab ) {

					$count++;

					$tab_title = isset( $tab['title'] ) ? $tab['title'] : esc_html__( 'Other', 'total-theme-core' );
					$tab_post_type = $this->get_tab_post_type( $tab );
					$is_open_tab   = $has_active_tab = false;

					if ( ! $has_active_tab ) {
						if ( $open_tab ) {
							if ( $open_tab === $tab_key ) {
								$is_open_tab = true;
							}
						} elseif ( 1 === $count ) {
							$is_open_tab = true;
						}
					}

					$tabs_output .= '<li';

						if ( $is_open_tab && ! $has_active_tab ) {
							$tabs_output .= ' class="wp-tab-active"';
							$has_active_tab = true;
						}

					$tabs_output .= '>';

						$tabs_output .= '<a href="javascript:;" data-tab="#wpex-mb-tab-' . $count . '">';

							if ( isset( $tab['icon'] ) ) {

								$tabs_output .= '<span class="' . esc_attr( $tab['icon'] ) .'"></span>';

							}

							$tabs_output .= esc_html( $tab_title );

						$tabs_output .= '</a>';

					$tabs_output .= '</li>';

				}

			$tabs_output .= '</ul>';

			echo $tabs_output;

		}

		// Output tab sections
		$count = 0;
		foreach ( $active_tabs as $tab_key => $tab ) {
			$count++;

			$is_open_tab = $has_active_tab = false;

			if ( ! $has_active_tab ) {
				if ( $open_tab ) {
					if ( $open_tab === $tab_key ) {
						$is_open_tab = true;
					}
				} elseif ( 1 == $count ) {
					$is_open_tab = true;
				}
			}

			$tab_class = 'wpex-mb-tab-panel';

			if ( $tab_count > 1 ) {
				$tab_class .= ' wp-tab-panel';
				if ( $is_open_tab && ! $has_active_tab ) {
					$tab_class .= ' wp-tab-panel-active';
					$has_active_tab = true;
				}
			}

			?>

			<div id="wpex-mb-tab-<?php echo absint( $count ); ?>" class="<?php echo esc_attr( $tab_class ); ?>">

				<table class="form-table">
					<?php
					foreach ( $tab['settings'] as $setting ) {

						if ( isset( $setting['condition'] ) && ! $setting['condition'] ) {
							continue;
						}

						$meta_id     = $setting['id'];
						$title       = $setting['title'];
						$hidden      = isset( $setting['hidden'] ) ? $setting['hidden'] : false;
						$type        = isset( $setting['type'] ) ? $setting['type'] : 'text';
						$default     = isset( $setting['default'] ) ? $setting['default'] : '';
						$description = isset( $setting['description'] ) ? $setting['description'] : '';
						$meta_value  = get_post_meta( $post_id, $meta_id, true );
						$meta_value  = $meta_value ? $meta_value : $default;

						?>

						<tr id="<?php echo esc_attr( $meta_id ); ?>_tr"<?php if ( $hidden ) echo ' class="wpex-mb-hidden"'; ?>>

							<th>
								<label for="wpex_main_layout"><strong><?php echo $title; ?></strong></label>
								<?php
								// Display field description
								if ( $description ) { ?>
									<p class="wpex-mb-description"><?php echo $description; ?></p>
								<?php } ?>
							</th>

							<?php
							switch ( $type ) {

								// Text Field
								case 'text':

								?>

									<td><input class="wpex-input" name="<?php echo esc_attr( $meta_id ); ?>" type="text" value="<?php echo esc_attr( $meta_value ); ?>"></td>

								<?php
								break;

								// Button Group
								case 'button_group':

									$options = isset ( $setting['options'] ) ? $setting['options'] : '';

									if ( is_array( $options ) ) { ?>

										<td>

											<div class="wpex-mb-btn-group">

												<?php foreach ( $options as $option_value => $option_name ) {

													$class = 'wpex-mb-btn wpex-mb-' . esc_attr( $option_value );

													if ( $option_value == $meta_value ) {
														$class .= ' active';
													}  ?>

													<button type="button" class="<?php echo esc_attr( $class ); ?>" data-value="<?php echo esc_attr( $option_value ); ?>"><?php echo esc_html( $option_name ); ?></button>

												<?php } ?>

												<input name="<?php echo esc_attr( $meta_id ); ?>" type="hidden" value="<?php echo esc_attr( $meta_value ); ?>" class="wpex-mb-hidden">

											</div>

										</td>

									<?php }

								break;

								// Enable Disable button group
								case 'button_group_ed':

									$options = isset ( $setting['options'] ) ? $setting['options'] : '';

									if ( is_array( $options ) ) { ?>

										<td>

											<div class="wpex-mb-btn-group">

												<?php
												// Default
												$active = ! $meta_value ? 'wpex-mb-btn wpex-default active' : 'wpex-mb-btn wpex-default'; ?>

												<button type="button" class="<?php echo esc_attr( $active ); ?>" data-value=""><?php echo esc_html_e( 'Default', 'total-theme-core' ); ?></button>

												<?php
												// Enable
												$active = ( $options['enable'] == $meta_value ) ? 'wpex-mb-btn wpex-on active' : 'wpex-mb-btn wpex-on'; ?>

												<button type="button" class="<?php echo esc_attr( $active ); ?>" data-value="<?php echo esc_attr( $options['enable'] ); ?>"><?php echo esc_html_e( 'Enable', 'total-theme-core' ); ?></button>

												<?php
												// Disable
												$active = ( $options['disable'] == $meta_value ) ? 'wpex-mb-btn wpex-off active' : 'wpex-mb-btn wpex-off'; ?>

												<button type="button" class="<?php echo esc_attr( $active ); ?>" data-value="<?php echo esc_attr( $options['disable'] ); ?>"><?php echo esc_html_e( 'Disable', 'total-theme-core' ); ?></button>

												<input name="<?php echo esc_attr( $meta_id ); ?>" type="hidden" value="<?php echo esc_attr( $meta_value ); ?>" class="wpex-mb-hidden">

											</div>

										</td>

									<?php }

								break;

								// Date Field
								case 'date':

									$meta_value = $meta_value ? date( get_option( 'date_format' ), $meta_value ) : '';

									wp_enqueue_script( 'jquery-ui' );

									wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery-ui' ) );

									if ( function_exists( 'wp_localize_jquery_ui_datepicker' ) ) {
										wp_localize_jquery_ui_datepicker();
									}

									wp_enqueue_style(
										'jquery-ui-datepicker-style',
										'//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css'
									); ?>

									<td><input class="wpex-input wpex-date-meta" name="<?php echo esc_attr( $meta_id ); ?>" type="text" value="<?php echo esc_attr( $meta_value ); ?>"></td>

								<?php
								break;

								// Number Field
								case 'number':

									$step = isset( $setting['step'] ) ? $setting['step'] : '1';
									$min  = isset( $setting['min'] ) ? $setting['min'] : '1';
									$max  = isset( $setting['max'] ) ? $setting['max'] : '10'; ?>

									<td>
										<input class="wpex-input" name="<?php echo esc_attr( $meta_id ); ?>" type="number" value="<?php echo esc_attr( $meta_value ); ?>" step="<?php echo floatval( $step ); ?>" min="<?php echo floatval( $min ); ?>" max="<?php echo floatval( $max ); ?>">
									</td>

								<?php
								break;

								// HTML Text
								case 'text_html':

								?>

									<td><input class="wpex-input" name="<?php echo esc_attr( $meta_id ); ?>" type="text" value="<?php echo esc_html( $meta_value ); ?>"></td>

								<?php
								break;

								// Link field
								case 'link':

									// Sanitize
									$meta_value = ( 'home_url' === $meta_value ) ? esc_html( $meta_value ) : esc_url( $meta_value ); ?>

									<td><input class="wpex-input" name="<?php echo esc_attr( $meta_id ); ?>" type="text" value="<?php echo $meta_value; ?>"></td>

								<?php

								break;

								// Textarea Field
								case 'textarea':

									$rows = isset ( $setting['rows'] ) ? absint( $setting['rows'] ) : 4; ?>

									<td>
										<textarea rows="<?php echo esc_attr( $rows ); ?>" cols="1" name="<?php echo esc_attr( $meta_id ); ?>" type="text" class="wpex-mb-textarea"><?php echo esc_textarea( $meta_value ); ?></textarea>
									</td>

								<?php
								break;


								// Code Field
								case 'code':

									$rows = isset ( $setting['rows'] ) ? absint( $setting['rows'] ) : 1; ?>

									<td>
										<pre><textarea rows="<?php echo esc_attr( $rows ); ?>" cols="1" name="<?php echo esc_attr( $meta_id ); ?>" type="text" class="wpex-mb-textarea-code"><?php echo $meta_value; ?></textarea></pre>
									</td>

								<?php
								break;

								// Checkbox
								case 'checkbox':

									$meta_value = ( 'on' != $meta_value ) ? false : true;

									?>

									<td><input name="<?php echo esc_attr( $meta_id ); ?>" type="checkbox" <?php checked( $meta_value, true, true ); ?>></td>

								<?php
								break;

								// Select
								case 'select':

									if ( isset( $setting['options_callback'] ) && is_callable( $setting['options_callback'] ) ) {
										$setting['options'] = call_user_func( $setting['options_callback'] );
									}

									$options = isset( $setting['options'] ) ? $setting['options'] : '';

									if ( ! empty( $options ) && is_array( $options ) ) { ?>

										<td><select id="<?php echo esc_attr( $meta_id ); ?>" name="<?php echo esc_attr( $meta_id ); ?>">

										<?php foreach ( $options as $option_value => $option_name ) { ?>

											<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $meta_value, $option_value, true ); ?>><?php echo esc_attr( $option_name ); ?></option>

										<?php } ?>

										</select></td>

									<?php } else { ?>

										<td><input name="<?php echo esc_attr( $meta_id ); ?>" type="text" value="<?php echo esc_attr( $meta_value ); ?>"></td>

									<?php }

								break;

								// Color
								case 'color': ?>

									<td><input name="<?php echo esc_attr( $meta_id ); ?>" type="text" value="<?php echo esc_attr( $meta_value ); ?>" class="wpex-mb-color-field" data-alpha="true"></td>

								<?php
								break;

								// Image
								case 'image':

									// Validate data if array - old Redux cleanup
									if ( is_array( $meta_value ) ) {
										if ( ! empty( $meta_value['url'] ) ) {
											$meta_value = $meta_value['url'];
										} else {
											$meta_value = '';
										}
									} ?>

									<td>
										<div class="wpex-image-select">
											<input class="wpex-input" type="text" name="<?php echo esc_attr( $meta_id ); ?>" value="<?php echo esc_attr( $meta_value ); ?>">
											<button class="wpex-mb-uploader button-primary" name="<?php echo esc_attr( $meta_id ); ?>" type="button"><?php esc_html_e( 'Select', 'total-theme-core' ); ?></button>
										</div>
										<div class="wpex-img-holder">
											<?php if ( $meta_value ) {
												if ( is_numeric( $meta_value ) && wp_attachment_is_image( $meta_value ) ) {
													echo wp_get_attachment_image( $meta_value, 'thumbnail' );
												} else {
													echo '<img src="'. $meta_value . '" />';
												}
											} ?>
										</div>
									</td>

								<?php
								break;

								// Media
								case 'media':

									// Validate data if array - old Redux cleanup
									if ( is_array( $meta_value ) ) {
										if ( ! empty( $meta_value['url'] ) ) {
											$meta_value = $meta_value['url'];
										} else {
											$meta_value = '';
										}
									}

									?>

									<td>
										<div class="uploader">
										<input class="wpex-input" type="text" name="<?php echo esc_attr( $meta_id ); ?>" value="<?php echo esc_attr( $meta_value ); ?>">
										<input class="wpex-mb-uploader button-secondary" name="<?php echo esc_attr( $meta_id ); ?>" type="button" value="<?php esc_html_e( 'Upload', 'total-theme-core' ); ?>" />
										</div>
									</td>

								<?php
								break;

								// Editor
								case 'editor':

									$teeny= isset( $setting['teeny'] ) ? $setting['teeny'] : false;
									$rows = isset( $setting['rows'] ) ? $setting['rows'] : '10';
									$media_buttons= isset( $setting['media_buttons'] ) ? $setting['media_buttons'] : true; ?>
									<td><?php wp_editor( $meta_value, $meta_id, array(
										'textarea_name' => $meta_id,
										'teeny'         => $teeny,
										'textarea_rows' => $rows,
										'media_buttons' => $media_buttons,
									) ); ?></td>

								<?php
								break;

							} // End switch ?>

						</tr>

					<?php } ?>

				</table>

			</div>

		<?php } ?>

		<div class="wpex-mb-reset">
			<a class="button button-secondary wpex-reset-btn"><?php esc_html_e( 'Reset Settings', 'total-theme-core' ); ?></a>
			<div class="wpex-reset-checkbox"><input type="checkbox" name="wpex_metabox_reset"> <?php esc_html_e( 'Are you sure? Check this box, then update your post to reset all settings.', 'total-theme-core' ); ?></div>
		</div>

		<div class="clear"></div>

	<?php }

	/**
	 * Save metabox data.
	 */
	public function save_meta_data( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['wpex_metabox_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['wpex_metabox_nonce'], 'wpex_metabox' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

		}

		/* OK, it's safe for us to save the data now. Now we can loop through fields */

		// Check reset field
		$reset = isset( $_POST['wpex_metabox_reset'] ) ? $_POST['wpex_metabox_reset'] : '';

		// Get array of settings to save
		$tabs = $this->meta_array( get_post( $post_id ) );

		// No tabs so lets bail
		if ( ! $tabs ) {
			return;
		}

		// Get current post type
		$post_type = get_post_type( $post_id );

		// Loop through tabs to locate the ones active on the post
		$active_tabs = array();
		foreach ( $tabs as $tab_key => $tab ) {
			$tab_post_type = $this->get_tab_post_type( $tab );
			if ( ! $tab_post_type || in_array( $post_type, $tab_post_type ) ) {
				$active_tabs[$tab_key] = $tab;
			}
		}

		// Loop through tabs to grab all settings
		$settings = array();
		foreach( $active_tabs as $tab ) {
			foreach ( $tab['settings'] as $setting ) {
				$settings[] = $setting;
			}
		}

		// No settings to check
		if ( empty( $settings ) ) {
			return;
		}

		// Loop through settings and validate
		foreach ( $settings as $setting ) {

			$id = $setting['id'];

			if ( 'on' === $reset ) {
				delete_post_meta( $post_id, $id );
				continue;
			}

			// Vars
			$value = isset( $_POST[ $id ] ) ? $_POST[ $id ] : '';
			$type  = isset( $setting['type'] ) ? $setting['type'] : 'text';

			switch ( $type ) {

				case 'checkbox':
					$value = $value ? 'on' : null;
					break;

				case 'text':
				case 'text_html':
					if ( $value ) {
						$value = wp_kses_post( $value );
					}
					break;

				case 'date':
					if ( $value ) {
						$value = strtotime( wp_strip_all_tags( $value ) );
					}
					break;

				case 'textarea':
					if ( $value ) {
						$value = esc_html( $value );
					}
					break;

				case 'link':
					if ( $value ) {
						$value = esc_url( $value );
					}
					break;

				case 'select':

					if ( 'default' === $value ) {
						$value = ''; // the default value should save as empty.
					}

					if ( $value ) {
						$value = wp_strip_all_tags( $value ); // @todo compare to available options.
					}

					break;

				case 'media':
				case 'image':

					// Move old wpex_post_self_hosted_shortcode_redux to wpex_post_self_hosted_media
					if ( 'wpex_post_self_hosted_media' === $id && empty( $value )
						&& $old = get_post_meta( $post_id, 'wpex_post_self_hosted_shortcode_redux', true )
					) {
						$value = $old;
						delete_post_meta( $post_id, 'wpex_post_self_hosted_shortcode_redux' );
					}

					if ( $value ) {
						$value = sanitize_text_field( $value );
					}
					break;

					case 'editor':

						$value = ( '<p><br data-mce-bogus="1"></p>' === $value ) ? '' : $value;

						if ( $value ) {
							$value = wp_kses_post( $value );
						}

					break;

				default:
					$value = sanitize_text_field( $value );
					break;

			} // end switch

			// Update meta value
			if ( $value ) {
				update_post_meta( $post_id, $id, $value );
			} else {
				delete_post_meta( $post_id, $id );
			}

		} // end foreach

	}

	/**
	 * Get menus.
	 */
	public function get_menus() {
		$menus = array( esc_html__( 'Default', 'total-theme-core' ) );
		$get_menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
		foreach ( $get_menus as $menu) {
			$menus[$menu->term_id] = $menu->name;
		}
		return $menus;
	}

	/**
	 * Get title styles.
	 */
	public function get_title_styles() {
		return apply_filters( 'wpex_title_styles', array(
			''                 => esc_html__( 'Default', 'total-theme-core' ),
			'centered'         => esc_html__( 'Centered', 'total-theme-core' ),
			'centered-minimal' => esc_html__( 'Centered Minimal', 'total-theme-core' ),
			'background-image' => esc_html__( 'Background Image', 'total-theme-core' ),
			'solid-color'      => esc_html__( 'Solid Color & White Text', 'total-theme-core' ),
		) );
	}

	/**
	 * Get widget areas.
	 */
	public function get_widget_areas() {
		$widget_areas = array( esc_html__( 'Default', 'total-theme-core' ) );
		if ( function_exists( 'wpex_get_widget_areas' ) ) {
			$widget_areas = $widget_areas + wpex_get_widget_areas();
		}
		return $widget_areas;
	}

	/**
	 * Get templatera templates.
	 */
	public function get_templatera_templates() {
		$templates = array( esc_html__( 'Default', 'total-theme-core' ) );
		if ( ! post_type_exists( 'templatera' ) ) {
			return $templates;
		}
		$get_templates = new WP_Query( array(
			'posts_per_page' => -1,
			'post_type'      => 'templatera',
			'fields'         => 'ids',
		) );
		if ( $get_templates = $get_templates->posts ) {
			foreach ( $get_templates as $template ) {
				$templates[$template] = wp_strip_all_tags( get_the_title( $template ) );
			}
		}
		return $templates;
	}

	/**
	 * Settings Array.
	 */
	private function meta_array( $post = null ) {

		// We've already got settings
		if ( $this->settings ) {
			return $this->settings;
		}

		// Check if Total is enabled
		$total_active = defined( 'TOTAL_THEME_ACTIVE' ) ? true : false;

		// Prefix
		$prefix = 'wpex_';

		// Define array
		$array = array();

		// Header styles
		$header_styles = array(
			'' => esc_html__( 'Default', 'total-theme-core' ),
		);
		if ( function_exists( 'wpex_get_header_styles' ) ) {
			$header_styles = $header_styles + wpex_get_header_styles();
		}

		// Get active settings
		$header_style = get_theme_mod( 'header_style' );

		// Main Tab
		$array['main'] = array(
			'title' => esc_html__( 'Main', 'total-theme-core' ),
			'settings' => array(
				'post_link' => array(
					'title' => esc_html__( 'Redirect', 'total-theme-core' ),
					'id' => $prefix . 'post_link',
					'type' => 'link',
					'description' => esc_html__( 'Enter a URL to redirect this post or page.', 'total-theme-core' ),
				),
				'main_layout' =>array(
					'title' => esc_html__( 'Site Layout', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'main_layout',
					'description' => esc_html__( 'This option should only be used in very specific cases since there is a global setting available in the Customizer.', 'total-theme-core' ),
					'options_callback' => 'wpex_get_site_layouts',
				),
				'post_layout' => array(
					'title' => esc_html__( 'Content Layout', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'post_layout',
					'description' => esc_html__( 'Select your custom layout for this page or post content.', 'total-theme-core' ),
					'options_callback' => 'wpex_get_post_layouts',
				),
				'singular_template'    => array(
					'title' => esc_html__( 'Dynamic Template', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'singular_template',
					'description' => esc_html__( 'Select a dynamic templatera template to override this page. If selected it will disable the front-end editor.', 'total-theme-core' ),
					'options_callback' => array( $this, 'get_templatera_templates' ),
				),
				'sidebar' => array(
					'title' => esc_html__( 'Sidebar', 'total-theme-core' ),
					'type' => 'select',
					'id' => 'sidebar',
					'description' => esc_html__( 'Select your a custom sidebar for this page or post.', 'total-theme-core' ),
					'options_callback' => array( $this, 'get_widget_areas' ),
				),
				'disable_toggle_bar'   => array(
					'title' => esc_html__( 'Toggle Bar', 'total-theme-core' ),
					'id' => $prefix . 'disable_toggle_bar',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options'          => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'disable_top_bar' => array(
					'title' => esc_html__( 'Top Bar', 'total-theme-core' ),
					'id' => $prefix . 'disable_top_bar',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'disable_breadcrumbs' => array(
					'title' => esc_html__( 'Breadcrumbs', 'total-theme-core' ),
					'id' => $prefix . 'disable_breadcrumbs',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'disable_social' => array(
					'title' => esc_html__( 'Social Share', 'total-theme-core' ),
					'id' => $prefix . 'disable_social',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'secondary_thumbnail' => array(
					'title' => esc_html__( 'Secondary Image', 'total-theme-core' ),
					'id' => $prefix . 'secondary_thumbnail',
					'type' => 'image',
					'description' => esc_html__( 'Used for the secondary Image Swap overlay style.', 'total-theme-core' ),
				),
			),
		);

		// Header Tab
		$array['header'] = array(
			'title' => esc_html__( 'Header', 'total-theme-core' ),
			'settings' => array(
				'disable_header' => array(
					'title' => esc_html__( 'Header', 'total-theme-core' ),
					'id' => $prefix . 'disable_header',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'header_style' => array(
					'title' => esc_html__( 'Header Style', 'total-theme-core' ),
					'id' => $prefix . 'header_style',
					'type' => 'select',
					'description' => esc_html__( 'Override default header style.', 'total-theme-core' ),
					'options' => $header_styles,
					'condition' => $header_style == 'dev' ? false : true,
				),
				'sticky_header' => array(
					'title' => esc_html__( 'Sticky Header', 'total-theme-core' ),
					'id' => $prefix . 'sticky_header',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'disable' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'logo_scroll_top' => array(
					'title' => esc_html__( 'Scroll Up When Clicking Logo', 'total-theme-core' ),
					'id' => $prefix . 'logo_scroll_top',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'disable' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'header_menu' => array(
					'title' => esc_html__( 'Menu', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'header_menu',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'on' => esc_html__( 'Enable', 'total-theme-core' ),
						'off' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'custom_menu' => array(
					'title' => esc_html__( 'Custom Menu', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'custom_menu',
					'description' => esc_html__( 'Select a custom menu for this page or post.', 'total-theme-core' ),
					'options' => $this->get_menus(),
				),
				'overlay_header' => array(
					'title' => esc_html__( 'Overlay (Transparent) Header', 'total-theme-core' ),
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'id' => $prefix . 'overlay_header',
					'type' => 'select',
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'on' => esc_html__( 'Enable', 'total-theme-core' ),
						'off' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'overlay_header_style' => array(
					'title' => esc_html__( 'Overlay Header Style', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'overlay_header_style',
					'description' => esc_html__( 'Select your overlay header style', 'total-theme-core' ),
					'options_callback' => 'wpex_header_overlay_styles',
					'default' => '',
					'condition' => $header_style == 'dev' ? false : true,
				),
				'overlay_header_background' => array(
					'title' => esc_html__( 'Overlay Header Background', 'total-theme-core' ),
					'id' => $prefix . 'overlay_header_background',
					'description' => esc_html__( 'Select a color to enable a background for your header (optional)', 'total-theme-core' ),
					'type' => 'color',
				),
				'overlay_header_dropdown_style' => array(
					'title' => esc_html__( 'Overlay Header Dropdown Style', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'overlay_header_dropdown_style',
					'description' => esc_html__( 'Select your overlay header style', 'total-theme-core' ),
					'options_callback' => 'wpex_get_menu_dropdown_styles',
					//'default' => 'black', // @deprecated 1.0.4
					'condition' => $header_style == 'dev' ? false : true,
				),
				'overlay_header_font_size' => array(
					'title' => esc_html__( 'Overlay Header Menu Font Size', 'total-theme-core'),
					'id' => $prefix . 'overlay_header_font_size',
					'description' => esc_html__( 'Enter a size in px.', 'total-theme-core' ),
					'type' => 'number',
					'max' => '99',
					'min' => '8',
					'condition' => $header_style == 'dev' ? false : true,
				),
				'overlay_header_logo' => array(
					'title' => esc_html__( 'Overlay Header Logo', 'total-theme-core'),
					'id' => $prefix . 'overlay_header_logo',
					'type' => 'image',
					'description' => esc_html__( 'Select a custom logo (optional) for the overlay header.', 'total-theme-core' ),
				),
				'overlay_header_logo_retina' => array(
					'title' => esc_html__( 'Overlay Header Logo: Retina', 'total-theme-core'),
					'id' => $prefix . 'overlay_header_logo_retina',
					'type' => 'image',
					'description' => esc_html__( 'Retina version for the overlay header custom logo.', 'total-theme-core' ),
				),
				'overlay_header_retina_logo_height' => array(
					'title' => esc_html__( 'Overlay Header Retina Logo Height', 'total-theme-core'),
					'id' => $prefix . 'overlay_header_logo_retina_height',
					'description' => esc_html__( 'Enter a size in px.', 'total-theme-core' ),
					'type' => 'number',
					'max' => '999',
					'min' => '1',
				),
			),
		);

		// Title Tab
		$array['title'] = array(
			'title' => esc_html__( 'Title', 'total-theme-core' ),
			'settings' => array(
				'disable_title' => array(
					'title' => esc_html__( 'Title', 'total-theme-core' ),
					'id' => $prefix . 'disable_title',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'post_title' => array(
					'title' => esc_html__( 'Custom Title', 'total-theme-core' ),
					'id' => $prefix . 'post_title',
					'type' => 'text',
					'description' => esc_html__( 'Alter the main title display.', 'total-theme-core' ),
				),
				'disable_header_margin' => array(
					'title' => esc_html__( 'Title Margin', 'total-theme-core' ),
					'id' => $prefix . 'disable_header_margin',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'post_subheading' => array(
					'title' => esc_html__( 'Subheading', 'total-theme-core' ),
					'type' => 'text_html',
					'id' => $prefix . 'post_subheading',
					'description' => esc_html__( 'Enter your page subheading. Shortcodes & HTML is allowed.', 'total-theme-core' ),
				),
				'post_title_style' => array(
					'title' => esc_html__( 'Title Style', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'post_title_style',
					'description' => esc_html__( 'Select a custom title style for this page or post.', 'total-theme-core' ),
					'options' => $this->get_title_styles(),
				),
				'post_title_background_color' => array(
					'title' => esc_html__( 'Background Color', 'total-theme-core' ),
					'description' => esc_html__( 'Select a color.', 'total-theme-core' ),
					'id' => $prefix .'post_title_background_color',
					'type' => 'color',
					'hidden' => true,
				),
				'post_title_background_redux' => array(
					'title' => esc_html__( 'Background Image', 'total-theme-core'),
					'id' => $prefix . 'post_title_background_redux', //@todo remove _redux
					'type' => 'image',
					'description' => esc_html__( 'Select a custom header image for your main title.', 'total-theme-core' ),
					'hidden' => true,
				),
				'post_title_height' => array(
					'title' => esc_html__( 'Background Height', 'total-theme-core' ),
					'type' => 'text',
					'id' => $prefix . 'post_title_height',
					'description' => esc_html__( 'Select your custom height for your title background.', 'total-theme-core' ),
					'hidden' => true,
				),
				'post_title_background_style' => array(
					'title' => esc_html__( 'Background Style', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'post_title_background_image_style',
					'description' => esc_html__( 'Select the style.', 'total-theme-core' ),
					'options_callback' => 'wpex_get_bg_img_styles',
					'hidden' => true,
				),
				'post_title_background_position' => array(
					'title' => esc_html__( 'Background Position', 'total-theme-core' ),
					'type' => 'text',
					'id' => $prefix . 'post_title_background_position', // @todo rename to post_title_background_image_position
					'description' => esc_html__( 'Enter a custom position for your background image.', 'total-theme-core' ),
					'hidden' => true,
				),
				'post_title_background_overlay' => array(
					'title' => esc_html__( 'Background Overlay', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'post_title_background_overlay',
					'description' => esc_html__( 'Select an overlay for the title background.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'None', 'total-theme-core' ),
						'dark' => esc_html__( 'Dark', 'total-theme-core' ),
						'dotted' => esc_html__( 'Dotted', 'total-theme-core' ),
						'dashed' => esc_html__( 'Diagonal Lines', 'total-theme-core' ),
						'bg_color' => esc_html__( 'Background Color', 'total-theme-core' ),
					),
					'hidden' => true,
				),
				'post_title_background_overlay_opacity' => array(
					'id' => $prefix . 'post_title_background_overlay_opacity',
					'type' => 'number',
					'title' => esc_html__( 'Background Overlay Opacity', 'total-theme-core' ),
					'description' => esc_html__( 'Enter a custom opacity for your title background overlay.', 'total-theme-core' ),
					'default' => '',
					'hidden' => true,
					'step' => 0.01,
					'min' => 0,
					'max' => 1,
				),
			),
		);

		// Slider tab
		$array['slider'] = array(
			'title' => esc_html__( 'Slider', 'total-theme-core' ),
			'settings' => array(
				'post_slider_shortcode' => array(
					'title' => esc_html__( 'Slider Shortcode', 'total-theme-core' ),
					'type' => 'code',
					'rows' => 2,
					'id' => $prefix . 'post_slider_shortcode',
					'description' => esc_html__( 'Enter a slider shortcode here to display a slider at the top of the page.', 'total-theme-core' ),
				),
				'post_slider_shortcode_position' => array(
					'title' => esc_html__( 'Slider Position', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'post_slider_shortcode_position',
					'description' => esc_html__( 'Select the position for the slider shortcode.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'below_title' => esc_html__( 'Below Title', 'total-theme-core' ),
						'above_title' => esc_html__( 'Above Title', 'total-theme-core' ),
						'above_menu' => esc_html__( 'Above Menu (Header 2 or 3)', 'total-theme-core' ),
						'above_header' => esc_html__( 'Above Header', 'total-theme-core' ),
						'above_topbar' => esc_html__( 'Above Top Bar', 'total-theme-core' ),
					),
				),
				'post_slider_bottom_margin' => array(
					'title' => esc_html__( 'Slider Bottom Margin', 'total-theme-core' ),
					'description' => esc_html__( 'Enter a bottom margin for your slider in pixels.', 'total-theme-core' ),
					'id' => $prefix . 'post_slider_bottom_margin',
					'type' => 'text',
				),
				'contain_post_slider' => array(
					'title' => esc_html__( 'Contain Slider?', 'total-theme-core' ),
					'id' => $prefix . 'contain_post_slider',
					'type' => 'select',
					'description' => esc_html__( 'Adds the container wrapper around the slider to center it with the rest of the content.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Disable', 'total-theme-core' ),
						'on' => esc_html__( 'Enable', 'total-theme-core' ),
					),
				),
				'disable_post_slider_mobile' => array(
					'title' => esc_html__( 'Slider On Mobile', 'total-theme-core' ),
					'id' => $prefix . 'disable_post_slider_mobile',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable slider display for mobile devices.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'post_slider_mobile_alt' => array(
					'title' => esc_html__( 'Slider Mobile Alternative', 'total-theme-core' ),
					'type' => 'media',
					'id' => $prefix . 'post_slider_mobile_alt',
					'description' => esc_html__( 'Select an image.', 'total-theme-core' ),
					'type' => 'image',
				),
				'post_slider_mobile_alt_url' => array(
					'title' => esc_html__( 'Slider Mobile Alternative URL', 'total-theme-core' ),
					'id' => $prefix . 'post_slider_mobile_alt_url',
					'description' => esc_html__( 'URL for the mobile slider alternative.', 'total-theme-core' ),
					'type' => 'text',
				),
				'post_slider_mobile_alt_url_target' => array(
					'title' => esc_html__( 'Slider Mobile Alternative URL Target', 'total-theme-core' ),
					'id' => $prefix . 'post_slider_mobile_alt_url_target',
					'description' => esc_html__( 'Select your link target window.', 'total-theme-core' ),
					'type' => 'select',
					'options' => array(
						'' => esc_html__( 'Same Window', 'total-theme-core' ),
						'blank' => esc_html__( 'New Window', 'total-theme-core' ),
					),
				),
			),
		);

		// Background tab
		$array['background'] = array(
			'title' => esc_html__( 'Background', 'total-theme-core' ),
			'settings' => array(
				'page_background_color' => array(
					'title' => esc_html__( 'Background Color', 'total-theme-core' ),
					'description' => esc_html__( 'Select a color.', 'total-theme-core' ),
					'id' => $prefix . 'page_background_color',
					'type' => 'color',
				),

				// @todo remove _redux
				'page_background_image_redux' => array(
					'title' => esc_html__( 'Background Image', 'total-theme-core' ),
					'id' => $prefix . 'page_background_image_redux',
					'description' => esc_html__( 'Select an image.', 'total-theme-core' ),
					'type' => 'image',
				),
				'page_background_image_style' => array(
					'title' => esc_html__( 'Background Style', 'total-theme-core' ),
					'type' => 'select',
					'id' => $prefix . 'page_background_image_style',
					'description' => esc_html__( 'Select the style.', 'total-theme-core' ),
					'options_callback' => 'wpex_get_bg_img_styles',
				),
			),
		);

		// Footer tab
		$array['footer'] = array(
			'title' => esc_html__( 'Footer', 'total-theme-core' ),
			'settings' => array(
				'disable_footer' => array(
					'title' => esc_html__( 'Footer', 'total-theme-core' ),
					'id' => $prefix . 'disable_footer',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'disable_footer_widgets' => array(
					'title' => esc_html__( 'Footer Widgets', 'total-theme-core' ),
					'id' => $prefix . 'disable_footer_widgets',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'footer_reveal' => array(
					'title' => esc_html__( 'Footer Reveal', 'total-theme-core' ),
					'description' => esc_html__( 'Enable the footer reveal style. The footer will be placed in a fixed postion and display on scroll. This setting is for the "Full-Width" layout only and desktops only.', 'total-theme-core' ),
					'id' => $prefix . 'footer_reveal',
					'type' => 'select',
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'on' => esc_html__( 'Enable', 'total-theme-core' ),
						'off' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'footer_bottom' => array(
					'title' => esc_html__( 'Footer Bottom', 'total-theme-core' ),
					'description' => esc_html__( 'Enable the footer bottom area (copyright section).', 'total-theme-core' ),
					'id' => $prefix . 'footer_bottom',
					'type' => 'select',
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'on' => esc_html__( 'Enable', 'total-theme-core' ),
						'off' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
			),
		);

		// Callout Tab
		$array['callout'] = array(
			'title' => esc_html__( 'Callout', 'total-theme-core' ),
			//'icon' => 'dashicons dashicons-megaphone',
			'settings' => array(
				'disable_footer_callout' => array(
					'title' => esc_html__( 'Callout', 'total-theme-core' ),
					'id' => $prefix . 'disable_footer_callout',
					'type' => 'select',
					'description' => esc_html__( 'Enable or disable this element on this page or post.', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'enable' => esc_html__( 'Enable', 'total-theme-core' ),
						'on' => esc_html__( 'Disable', 'total-theme-core' ),
					),
				),
				'callout_link' => array(
					'title' => esc_html__( 'Callout Link', 'total-theme-core' ),
					'id' => $prefix . 'callout_link',
					'type' => 'link',
					'description' => esc_html__( 'Enter a valid link.', 'total-theme-core' ),
				),
				'callout_link_txt' => array(
					'title' => esc_html__( 'Callout Link Text', 'total-theme-core' ),
					'id' => $prefix . 'callout_link_txt',
					'type' => 'text',
					'description' => esc_html__( 'Enter your text.', 'total-theme-core' ),
				),
				'callout_text' => array(
					'title' => esc_html__( 'Callout Text', 'total-theme-core' ),
					'id' => $prefix . 'callout_text',
					'type' => 'editor',
					'rows' => '5',
					'teeny' => true,
					'media_buttons' => false,
					'description' => esc_html__( 'Override the default callout text and if your callout box is disabled globally but you have content here it will still display for this page or post.', 'total-theme-core' ),
				),
			),
		);

		// Media tab
		$array['media'] = array(
			'title' => esc_html__( 'Media', 'total-theme-core' ),
			'post_type' => array( 'post' ),
			'settings' => array(
				'post_media_position' => array(
					'title' => esc_html__( 'Media Display/Position', 'total-theme-core' ),
					'id' => $prefix . 'post_media_position',
					'type' => 'select',
					'description' => esc_html__( 'Select your preferred position for your post\'s media (featured image or video).', 'total-theme-core' ),
					'options' => array(
						'' => esc_html__( 'Default', 'total-theme-core' ),
						'above' => esc_html__( 'Full-Width Above Content', 'total-theme-core' ),
						'hidden' => esc_html__( 'None (Do Not Display Featured Image/Video)', 'total-theme-core' ),
					),
				),
				'post_oembed' => array(
					'title' => esc_html__( 'oEmbed URL', 'total-theme-core' ),
					'description' => esc_html__( 'Enter a URL that is compatible with WP\'s built-in oEmbed feature. This setting is used for your video and audio post formats.', 'total-theme-core' ) .'<br /><a href="http://codex.wordpress.org/Embeds" target="_blank">'. esc_html__( 'Learn More', 'total-theme-core' ) .' &rarr;</a>',
					'id' => $prefix . 'post_oembed',
					'type' => 'text',
				),
				'post_self_hosted_shortcode_redux' => array(
					'title' => esc_html__( 'Self Hosted', 'total-theme-core' ),
					'description' => esc_html__( 'Insert your self hosted video or audio URL here.', 'total-theme-core' ) .'<br /><a href="http://make.wordpress.org/core/2013/04/08/audio-video-support-in-core/" target="_blank">'. esc_html__( 'Learn More', 'total-theme-core' ) .' &rarr;</a>',
					'id' => $prefix . 'post_self_hosted_media',
					'type' => 'media',
				),
				'post_video_embed' => array(
					'title' => esc_html__( 'Embed Code', 'total-theme-core' ),
					'description' => esc_html__( 'Insert your embed/iframe code.', 'total-theme-core' ),
					'id' => $prefix . 'post_video_embed',
					'type' => 'code',
					'rows' => 4,
				),
			),
		);

		// Staff Tab
		// @todo change into a repeatable field so you can add/remove items instead of having the manual options?
		if ( get_theme_mod( 'staff_enable', true ) && function_exists( 'wpex_staff_social_meta_array' ) ) {

			$staff_meta_array = wpex_staff_social_meta_array();
			$staff_meta_array['position'] = array(
				'title' => esc_html__( 'Position', 'total-theme-core' ),
				'id'    => $prefix . 'staff_position',
				'type'  => 'text',
			);
			$obj = get_post_type_object( 'staff' );
			$tab_title= $obj->labels->singular_name;
			$array['staff'] = array(
				'title'     => $tab_title,
				'post_type' => array( 'staff' ),
				'settings'  => $staff_meta_array,
			);

		}

		// Portfolio Tab
		if ( get_theme_mod( 'portfolio_enable', true ) ) {

			$obj= get_post_type_object( 'portfolio' );
			$tab_title = $obj->labels->singular_name;
			$array['portfolio'] = array(
				'title' => $tab_title,
				'post_type' => array( 'portfolio' ),
				'settings' => array(
					'featured_video' => array(
						'title' => esc_html__( 'oEmbed URL', 'total-theme-core' ),
						'description' => esc_html__( 'Enter a URL that is compatible with WP\'s built-in oEmbed feature. This setting is used for your video and audio post formats.', 'total-theme-core' ) . '<br /><a href="http://codex.wordpress.org/Embeds" target="_blank">' . esc_html__( 'Learn More', 'total-theme-core' ) . ' &rarr;</a>',
						'id' => $prefix . 'post_video',
						'type' => 'text',
					),
					'post_video_embed' => array(
						'title' => esc_html__( 'Embed Code', 'total-theme-core' ),
						'description'  => esc_html__( 'Insert your embed/iframe code.', 'total-theme-core' ),
						'id' => $prefix . 'post_video_embed',
						'type' => 'code',
						'rows' => 4,
					),
				),
			);

		}

		// Apply filters and set class variable
		$this->settings = apply_filters( 'wpex_metabox_array', $array, $post );

		// Return settings
		return $this->settings;

	}

	/**
	 * Get toolset types.
	 */
	private function add_toolset_types() {
		$types = (array) get_option( 'wpcf-custom-types' );
		if ( $types ) {
			$public_types = array();
			foreach( $types as $type => $params ) {
				if ( ! empty( $params['public'] ) ) {
					$this->post_types[$type] = $type;
				}
			}
		}
	}

	/**
	 * Get tab screen (post_type)
	 */
	public function get_tab_post_type( $tab ) {
		$type = isset( $tab['post_type'] ) ? $tab['post_type'] : array();
		if ( is_string( $type ) ) {
			return str_split( $type );
		}
		return $type;
	}

}
MetaBoxes::instance();