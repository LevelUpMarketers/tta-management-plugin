<?php
/**
 * Class for easily adding term meta settings
 *
 * @package TotalThemeCore
 * @version 1.2.3
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

final class Term_Meta {

	/**
	 * Our single Term_Meta instance.
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
	 * Create or retrieve the instance of Term_Meta.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Term_Meta;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Register meta options
		// Not needed since it only is used for sanitization which we do ourselves
		//add_action( 'init', array( $this, 'register_meta' ) );

		// Admin init
		add_action( 'admin_init', array( $this, 'meta_form_fields' ), 40 );

	}

	/**
	 * Array of meta options.
	 */
	public function meta_options() {
		$options = array(
			/* Category Color - WIP - coming soon!
			'wpex_accent_color'  => array(
				'label'          => esc_html__( 'Accent Color', 'total-theme-core' ),
				'type'           => 'color',
				'has_admin_col'  => true,
				'show_on_create' => true,
				'args'           => array(
					'type'              => 'color',
					'single'            => true,
					'sanitize_callback' => 'sanitize_hex_color',
				),
			),*/
			// Card style
			'wpex_entry_card_style' => array(
				'label'     => esc_html__( 'Entry Card Style', 'total-theme-core' ),
				'type'      => 'select',
				'choices'   => 'wpex_choices_card_styles',
				'args'      => array(
					'type'              => 'string',
					'single'            => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
			// Redirect
			'wpex_redirect' => array(
				'label'     => esc_html__( 'Redirect', 'total-theme-core' ),
				'type'      => 'wp_dropdown_pages',
				'args'      => array(
					'type'              => 'integer',
					'single'            => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
			// Sidebar select
			'wpex_sidebar' => array(
				'label'    => esc_html__( 'Sidebar', 'total-theme-core' ),
				'type'     => 'select',
				'choices'  => 'wpex_choices_widget_areas',
				'args'     => array(
					'type'              => 'string',
					'single'            => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		);
		return apply_filters( 'wpex_term_meta_options', $options );
	}

	/**
	 * Add meta form fields.
	 */
	public function meta_form_fields() {

		// Get taxonomies
		$taxonomies = apply_filters( 'wpex_term_meta_taxonomies', get_taxonomies( array(
			'public' => true,
		) ) );

		// Return if no taxes defined
		if ( ! $taxonomies ) {
			return;
		}

		// Loop through taxonomies
		foreach ( $taxonomies as $taxonomy ) {

			// Add fileds to add new term page
			add_action( $taxonomy . '_add_form_fields', array( $this, 'add_form_fields' ) );

			// Add fields to edit term page
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ) );

			// Save fields
			add_action( 'created_' . $taxonomy, array( $this, 'save_forms' ), 10, 3 );
			add_action( 'edited_' . $taxonomy, array( $this, 'save_forms' ), 10, 3 );

			// Show fields in admin columns
			add_filter( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'admin_columns' ) );
			add_filter( 'manage_' . $taxonomy . '_custom_column', array( $this, 'admin_column' ), 10, 3 );

		}

	}

	/**
	 * Register meta options.
	 */
	public function register_meta() {
		foreach( $this->meta_options() as $key => $val ) {
			$args = isset( $val['args'] ) ? $val['args'] : array();
			register_meta( 'term', $key, $args );
		}
	}

	/**
	 * Adds new category fields.
	 */
	public function add_form_fields( $taxonomy ) {
		$has_fields = false;

		// Get term options
		$meta_options = $this->meta_options();

		// Make sure options aren't empty/disabled
		if ( ! empty( $meta_options ) && is_array( $meta_options ) ) {

			// Loop through options
			foreach ( $meta_options as $key => $val ) {

				if ( empty( $val['show_on_create'] ) ) {
					continue;
				}

				if ( false === $has_fields ) {
					$has_fields = true;
				}

				$label = isset( $val['label'] ) ? $val['label'] : '';

				?>

				<div class="form-field">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
					<?php $this->meta_form_field( $key, $val, '' ); ?>
				</div>

			<?php }

			// Add security nonce only if fields are shown
			if ( $has_fields ) {
				wp_nonce_field( 'wpex_term_meta_nonce', 'wpex_term_meta_nonce' );
			}

		}

	}

	/**
	 * Adds new category fields.
	 */
	public function edit_form_fields( $term ) {

		// Security nonce
		wp_nonce_field( 'wpex_term_meta_nonce', 'wpex_term_meta_nonce' );

		// Get term options
		$meta_options = $this->meta_options();

		// Make sure options aren't empty/disabled
		if ( ! empty( $meta_options ) && is_array( $meta_options ) ) {

			// Loop through options
			foreach ( $meta_options as $key => $val ) {

				$label = isset( $val['label'] ) ? $val['label'] : '';

				?>

				<tr class="form-field">
					<th scope="row" valign="top"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
					<td><?php $this->meta_form_field( $key, $val, $term ); ?></td>
				</tr>

			<?php }

		}

	}

	/**
	 * Saves meta fields.
	 */
	public function save_forms( $term_id ) {

		// Make sure everything is secure
		if ( empty( $_POST['wpex_term_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['wpex_term_meta_nonce'], 'wpex_term_meta_nonce' )
		) {
			return;
		}

		// Get options
		$meta_options = $this->meta_options();

		// Make sure options aren't empty/disabled
		if ( ! empty( $meta_options ) && is_array( $meta_options ) ) {

			// Loop through options
			foreach ( $meta_options as $key => $val ) {

				// Check option value
				$value = isset( $_POST[$key] ) ? $_POST[$key] : '';

				// Save setting
				if ( $value ) {
					update_term_meta( $term_id, $key, sanitize_text_field( $value ) );
				}

				// Delete setting
				else {
					delete_term_meta( $term_id, $key );
				}

			}

		}

	}

	/**
	 * Add new admin columns for specific fields.
	 */
	public function admin_columns( $columns ) {

		$meta_options = $this->meta_options();

		if ( ! empty( $meta_options ) && is_array( $meta_options ) ) {
			foreach ( $meta_options as $key => $val ) {
				if ( ! empty( $val['has_admin_col'] ) ) {
					$columns[$key] = esc_html( $val['label'] );
				}
			}
		}

		return $columns;
	}

	/**
	 * Display certain field vals in admin columns.
	 */
	public function admin_column( $columns, $column, $term_id ) {

		$meta_options = $this->meta_options();

		if ( ! empty( $meta_options[$column] ) && ! empty( $meta_options[$column]['has_admin_col'] ) ) {

			$meta = get_term_meta( $term_id, $column, true );

			if ( $meta ) {
				$field_type = $meta_options[$column]['type'];

				switch ( $field_type ) {
					case 'color':
						$columns .= '<span style="background:' . esc_attr( $meta ) . ';width:20px;height:20px;display:inline-block;border-radius:999px;"></span>';
						break;
					default:
						$columns .= esc_html( $meta );
						break;
				}

			} else {
				$columns .= '&#8212;';
			}

		}

		return $columns;

	}

	/**
	 * Outputs the form field.
	 */
	public function meta_form_field( $key, $val, $term = '' ) {

		$type    = isset( $val['type'] ) ? $val['type'] : 'text';
		$term_id = ( ! empty( $term ) && is_object( $term ) ) ? $term->term_id : '';
		$value   = get_term_meta( $term_id, $key, true );

		// Text
		switch ( $type ) {

			case 'select':

				$choices = ! empty( $val['choices'] ) ? $val['choices'] : false;

				if ( $choices ) {

					if ( is_string( $choices ) && function_exists( $choices ) ) {
						$choices = call_user_func( $choices );
					}

					?>

					<select name="<?php echo esc_attr( $key ); ?>">
						<?php foreach ( $choices as $key => $val ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ) ?>><?php echo esc_html( $val ); ?></option>
						<?php endforeach; ?>
					</select>

				<?php
				}
				break;

			case 'color':

				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );

				wp_enqueue_script(
					'wpex-wp-color-picker-init',
					TTC_PLUGIN_DIR_URL . 'assets/js/wpColorPicker-init.min.js',
					array( 'jquery', 'wp-color-picker' ),
					true
				);

				?>

					<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" class="wpex-color-field" />

				<?php
				break;

			case 'wp_dropdown_pages':

				$args = array(
					'name'             => $key,
					'selected'         => $value,
					'show_option_none' => esc_html__( 'None', 'total-theme-core' )
				);

				wp_dropdown_pages( $args );

				break;

			case 'text':
			default: ?>

				<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" /></td>

			<?php
			break;

		} // end switch type

	}

}
Term_Meta::instance();