<?php
/**
 * Adds a Post Type Editor Panel for defined Post Types
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class PostTypeEditorPanel {
	private $types;
	private $post_type;

	/**
	 * Our single PostTypeEditorPanel instance.
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
	 * Create or retrieve the instance of PostTypeEditorPanel.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new PostTypeEditorPanel;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		$this->types = apply_filters( 'wpex_post_type_editor_types', array( 'portfolio', 'staff', 'testimonials' ) );

		if ( empty( $this->types ) ) {
			return;
		}

		$this->post_type = ! empty( $_GET['post_type'] ) ? $_GET['post_type'] : '';

		add_action( 'admin_menu', array( $this, 'add_submenu_pages' ), 40 );
		add_action( 'admin_init', array( $this, 'register_page_options' ), 40 );
		add_action( 'admin_notices', array( $this, 'setting_notice' ), 40 );
	}

	/**
	 * Enqueue scripts for the Post Type Editor Panel.
	 */
	public function enqueue_scripts() {

		// Chosen select
		wp_enqueue_style( 'wpex-chosen' );
		wp_enqueue_script( 'wpex-chosen' );
		wp_enqueue_script( 'wpex-chosen-icon' );

		// Theme admin panel styles
		wp_enqueue_style( 'wpex-admin-pages' );
		wp_enqueue_script( 'wpex-admin-pages' );

	}

	/**
	 * Return array of settings.
	 */
	public function get_settings( $type ) {
		return array(
			'page' => array(
				'label' => esc_html__( 'Main Page', 'total' ),
				'type'  => 'wp_dropdown_pages',
			),
			'admin_icon' => array(
				'label' => esc_html__( 'Admin Icon', 'total' ),
				'type'  => 'dashicon',
				'default' => array(
					'staff'        => 'businessman',
					'portfolio'    => 'portfolio',
					'testimonials' => 'testimonial',
				),
			),
			'has_archive' => array(
				'label' => esc_html__( 'Enable Auto Archive?', 'total' ),
				'type'  => 'checkbox',
				'description' => esc_html__( 'Disabled by default so you can create your archive page using a page builder.', 'total' ),
			),
			'has_single' => array(
				'label' => esc_html__( 'Enable Single Post?', 'total' ),
				'type'  => 'checkbox',
				'default' => true,
			),
			'show_in_rest' => array(
				'label' => esc_html__( 'Show in Rest?', 'total' ),
				'type'  => 'checkbox',
				'default' => false,
				'description' => esc_html__( 'Enables support for the Gutenberg Editor.', 'total' ),
			),
			'custom_sidebar' => array(
				'label' => esc_html__( 'Enable Custom Sidebar?', 'total' ),
				'type'  => 'checkbox',
				'default' => true,
			),
			'search' => array(
				'label' => esc_html__( 'Include in Search Results?', 'total' ),
				'type'  => 'checkbox',
				'default' => true,
			),
			'labels' => array(
				'label' => esc_html__( 'Post Type: Name', 'total' ),
				'type'  => 'text',
			),
			'singular_name' => array(
				'label' => esc_html__( 'Post Type: Singular Name', 'total' ),
				'type'  => 'text',
			),
			'slug' => array(
				'label' => esc_html__( 'Post Type: Slug', 'total' ),
				'type'  => 'text',
			),
			'categories' => array(
				'label' => esc_html__( 'Enable Categories?', 'total' ),
				'type'  => 'checkbox',
				'default' => true,
			),
			'cat_labels' => array(
				'label' => esc_html__( 'Categories: Label', 'total' ),
				'type'  => 'text',
			),
			'cat_slug' => array(
				'label' => esc_html__( 'Categories: Slug', 'total' ),
				'type'  => 'text',
			),
			'tags' => array(
				'label' => esc_html__( 'Enable Tags?', 'total' ),
				'type'  => 'checkbox',
				'default' => true,
				'exclusive' => array( 'portfolio', 'staff' ),
			),
			'tag_labels' => array(
				'label' => esc_html__( 'Tag: Label', 'total' ),
				'type'  => 'text',
				'conditional' => 'has_tags',
				'exclusive' => array( 'portfolio', 'staff' ),
			),
			'tag_slug' => array(
				'label' => esc_html__( 'Tag: Slug', 'total' ),
				'type'  => 'text',
				'conditional' => 'has_tags',
				'exclusive' => array( 'portfolio', 'staff' )
			),
		);
	}

	/**
	 * Get default value.
	 */
	public function get_default( $field ) {
		if ( ! empty( $field['default'] ) ) {
			if ( is_array( $field['default'] ) && isset( $field['default'][$this->post_type] ) ) {
				return $field['default'][$this->post_type];
			}
			return $field['default'];
		}
	}

	/**
	 * Add sub menu page for the Staff Editor.
	 */
	public function add_submenu_pages() {

		foreach ( $this->types as $type ) {

			$post_type_obj = get_post_type_object( $type );

			if ( ! is_object( $post_type_obj ) ) {
				continue;
			}

			$submenu_page = add_submenu_page(
				'edit.php?post_type=' . $type,
				$post_type_obj->labels->name . ' ' . esc_html__( 'Settings', 'total' ),
				__( 'Settings', 'total' ),
				'administrator',
				'wpex-' . $type . '-editor',
				array( $this, 'create_admin_page' )
			);

			add_action( 'load-' . $submenu_page, array( $this, 'flush_rewrite_rules' ) );

		}

	}

	/**
	 * Flush re-write rules.
	 */
	public function flush_rewrite_rules() {
		if ( in_array( $this->post_type, $this->types ) ) {
			flush_rewrite_rules();
		}
	}

	/**
	 * Function that will register the staff editor admin page.
	 */
	public function register_page_options() {
		foreach ( $this->types as $type ) {
			register_setting(
				'wpex_' . $type . '_editor_options',
				'wpex_' . $type . '_editor',
				array(
					'type'              => 'array',
					'sanitize_callback' => array( $this, 'save_settings' ),
				)
			);
		}
	}

	/**
	 * Displays saved message after settings are successfully saved.
	 */
	public function setting_notice() {
		foreach ( $this->types as $type ) {
			settings_errors( 'wpex_' . $type . '_editor_options' );
		}
	}

	/**
	 * Save settings.
	 */
	public function save_settings( $options ) {

		if ( empty( $options ) || empty( $options[ 'post_type'] ) ) {
			return;
		}

		$post_type = $options[ 'post_type'];

		if ( ! in_array( $post_type, $this->types ) ) {
			return;
		}

		$settings = $this->get_settings( $post_type );

		foreach ( $settings as $k => $v ) {

			if ( isset( $v['exclusive'] ) && ! in_array( $post_type, $v['exclusive'] ) ) {
				continue;
			}

			if ( 'has_single' === $k && 'testimonials' !== $post_type ) {
				continue;
			}

			$mod_name = $post_type . '_' . $k;
			$type     = $v[ 'type' ];
			$default  = $this->get_default( $v );
			$value    = isset( $options[$mod_name] ) ? $options[$mod_name] : '';

			if ( 'checkbox' === $type ) {

				if ( $default ) {

					if ( $value ) {
						remove_theme_mod( $mod_name );
					} else {
						set_theme_mod( $mod_name, false );
					}

				} else {

					if ( $value ) {
						set_theme_mod( $mod_name, true );
					} else {
						remove_theme_mod( $mod_name );
					}

				}

			} else {

				if ( $value ) {
					set_theme_mod( $mod_name, $value );
				} else {
					remove_theme_mod( $mod_name );
				}

			}

		}

		// Add notice
		add_settings_error(
			'wpex_' . $post_type . '_editor_options',
			esc_attr( 'settings_updated' ),
			__( 'Settings saved and rewrite rules flushed.', 'total' ),
			'updated'
		);

		// Lets delete the options as we are saving them into theme mods
		$options = '';
		return $options;

	}

	/**
	 * Output for the actual Staff Editor admin page.
	 */
	public function create_admin_page() {

		if ( ! in_array( $this->post_type, $this->types ) ) {
			wp_die();
		}

		$post_type_obj = get_post_type_object( $this->post_type );

		$this->enqueue_scripts();

		?>

		<div class="wrap">

			<h2><?php echo ucfirst( esc_html( $post_type_obj->labels->name ) ); ?> <?php esc_html_e( 'Settings', 'total' ); ?></h2>

			<form method="post" action="options.php">

				<table class="form-table">

					<?php

					settings_fields( 'wpex_' . $this->post_type . '_editor_options' );

					$settings = $this->get_settings( $this->post_type );

					foreach ( $settings as $field_id => $field ) :

						if ( isset( $field['exclusive'] ) && ! in_array( $this->post_type, $field['exclusive'] ) ) {
							continue;
						}

						if ( 'has_single' === $field_id && 'testimonials' !== $this->post_type  ) {
							continue;
						}

						$method = 'field_' . $field['type'];

						if ( method_exists( $this, $method ) ) {

							$mod_name         = $this->post_type . '_' . $field_id;
							$field['default'] = $this->get_default( $field );
							$field['id']      = 'wpex_' . $this->post_type . '_editor[' . $mod_name . ']';
							$mod_v            = get_theme_mod( $mod_name, $field['default'] );

							if ( 'checkbox' === $field['type'] ) {
								$field['value'] = ( $mod_v && 'off' !== $mod_v ) ? true : false;
							} else {
								$field['value'] = $mod_v;
							}

							?>

							<tr valign="top">

								<th scope="row"><label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>

								<td>
									<?php echo $this->$method( $field ); // @codingStandardsIgnoreLine ?>
									<?php if ( ! empty( $field['description'] ) ) { ?>
										<span class="description" style="padding-left:5px;">
											<?php echo esc_html( $field['description'] ); ?>
										</span>
									<?php } ?>
								</td>

							</tr>

						<?php } ?>

					<?php endforeach; ?>

				</table>

				<input type="hidden" name="wpex_<?php echo esc_attr( $this->post_type ); ?>_editor[post_type]" value="<?php echo esc_attr( $this->post_type ); ?>" />

				<?php submit_button(); ?>

			</form>

		</div>

	<?php }

	/**
	 * Return wp_dropdown_pages field.
	 */
	private function field_wp_dropdown_pages( $field ) {

		return wp_dropdown_pages( array(
			'echo'             => false,
			'selected'         => $field['value'],
			'name'             => $field['id'],
			'id'               => $field['id'],
			'class'            => 'wpex-chosen',
			'show_option_none' => esc_html__( 'None', 'total' ),
		) );

	}

	/**
	 * Return text field.
	 */
	private function field_text( $field ) {

		$output = '';

		$output .= '<input type="text"';

		$output .= ' id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '"';

		if ( isset( $field['size'] ) ) {
			$output .= ' size="' . esc_attr( $field['size'] ) . '" ';
		}

		$output .= ' />';

		return $output;

	}

	/**
	 * Return checkbox field.
	 */
	private function field_checkbox( $field ) {

		$output = '';

		$output .= '<input type="checkbox"';

			if ( isset( $field['class'] ) ) {
				$output .= ' class="' . esc_attr( $field['class'] ) . '"';
			}

			$output .= ' id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '"';

			$output .= ' ' . checked( $field['value'], true, false );

		$output .= ' />';

		return $output;

	}

	/**
	 * Return dashicon field
	 *
	 * @since 4.8
	 *
	 * @access private
	 * @return string
	 */
	private function field_dashicon( $field ) {

		$output = '';

		$dashicons = wpex_get_dashicons_array();

		$output .= '<select name="' . esc_attr( $field['id'] ) . '"  id="' . esc_attr( $field['id'] ) . '" class="wpex-chosen-icon-select">';

			foreach ( $dashicons as $k => $v ) {

				$class = $field['value'] == $k ? 'button-primary' : 'button';

				$output .= '<option value="' . esc_attr( $k ) . '" data-icon="dashicons dashicons-' . sanitize_html_class( $k ) . '" ' . selected( $k, $field['value'], false ) . '>' . esc_html( $k ) . '</option>';

			}

		$output .= '</select>';

		return $output;

	}

}
PostTypeEditorPanel::instance();