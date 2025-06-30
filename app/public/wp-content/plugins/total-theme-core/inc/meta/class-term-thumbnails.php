<?php
/**
 * Adds thumbnail options to taxonomies
 *
 * @package TotalThemeCore
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

final class Term_Thumbnails {

	/**
	 * Our single Term_Thumbnails instance.
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
	 * Create or retrieve the instance of Term_Thumbnails.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Term_Thumbnails;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Admin functions
		if ( is_request( 'admin' ) ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}

		// Display term thumbnail on the front-end
		if ( is_request( 'frontend' ) && apply_filters( 'wpex_enable_term_page_header_image', true ) ) {
			add_filter( 'wpex_page_header_style', array( $this, 'page_header_style' ) );
			add_filter( 'wpex_page_header_background_image', array( $this, 'page_header_bg' ) );
		}

	}

	/**
	 * Get things started in the backend to add/save the settings.
	 */
	public function admin_init() {

		// Get taxonomies
		$taxonomies = apply_filters( 'wpex_thumbnail_taxonomies', get_taxonomies( array(
			'public' => true,
		) ) );

		// Return if no taxonomies are defined
		if ( ! $taxonomies ) {
			return;
		}

		// Loop through taxonomies
		foreach ( $taxonomies as $taxonomy ) {

			// Add forms
			add_action( $taxonomy . '_add_form_fields', array( $this, 'add_form_fields' ), 10 );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ), 10, 2 );

			// Add columns
			if ( 'product_cat' != $taxonomy ) {
				add_filter( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'admin_columns' ) );
				add_filter( 'manage_' . $taxonomy . '_custom_column', array( $this, 'admin_column' ), 10, 3 );
			}

			// Save forms
			add_action( 'created_' . $taxonomy, array( $this, 'save_forms' ), 10, 3 );
			add_action( 'edit_' . $taxonomy, array( $this, 'save_forms' ), 10, 3 );

		}

	}

	/**
	 * Add Thumbnail field to add form fields.
	 */
	public function add_form_fields( $taxonomy ) {

		wp_nonce_field( 'wpex_term_thumbnail_meta_nonce', 'wpex_term_thumbnail_meta_nonce' );

		// Options not needed for Woo
		if ( 'product_cat' != $taxonomy ) : ?>

			<div class="form-field">

				<label for="term-thumbnail"><?php esc_html_e( 'Thumbnail', 'total-theme-core' ); ?></label>

				<div>

					<?php $this->enqueue_admin_scripts(); ?>

					<input type="hidden" id="wpex_term_thumbnail" name="wpex_term_thumbnail">

					<button id="wpex-add-term-thumbnail" class="button-secondary"><?php esc_attr_e( 'Select', 'total' ); ?></button>

					<button id="wpex-term-thumbnail-remove" class="button-secondary" style="display:none;"><?php esc_html_e( 'Remove', 'total' ); ?></button>

					<div id="wpex-term-thumbnail-preview" data-image-size="80"></div>

				</div>

				<div class="clear"></div>

			</div>

		<?php endif; ?>

	<?php
	}

	/**
	 * Add Thumbnail field to edit form fields.
	 */
	public function edit_form_fields( $term, $taxonomy ) {

		wp_nonce_field( 'wpex_term_thumbnail_meta_nonce', 'wpex_term_thumbnail_meta_nonce' );

		// Get current taxonomy
		$term_id = $term->term_id;

		// Get page header setting
		$page_header_bg = $this->get_term_meta( $term_id, 'page_header_bg', true );

		?>

		<tr class="form-field">

			<th scope="row" valign="top"><label><?php esc_html_e( 'Page Header Thumbnail', 'total-theme-core' ); ?></label></th>

			<td>
				<select id="wpex_term_page_header_image" name="wpex_term_page_header_image" class="postform">
					<option value="" <?php selected( $page_header_bg, '', true ); ?>><?php esc_html_e( 'Default', 'total-theme-core' ); ?></option>
					<option value="false" <?php selected( $page_header_bg, 'false', true ); ?>><?php esc_html_e( 'No', 'total-theme-core' ); ?></option>
					<option value="true" <?php selected( $page_header_bg, 'true', true ); ?>><?php esc_html_e( 'Yes', 'total-theme-core' ); ?></option>
				</select>
			</td>

		</tr>

		<?php
		// Options not needed for Woo
		if ( 'product_cat' != $taxonomy ) :

			// Get thumbnail
			$thumbnail_id  = $this->get_term_thumbnail_id( $term_id );
			if ( $thumbnail_id ) {
				$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail', false );
				$thumbnail_url = ! empty( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';
			}

			?>

			<tr class="form-field">

				<th scope="row" valign="top">
					<label for="term-thumbnail"><?php esc_html_e( 'Thumbnail', 'total-theme-core' ); ?></label>
				</th>

				<td>

					<?php $this->enqueue_admin_scripts(); ?>

					<input type="hidden" id="wpex_term_thumbnail" name="wpex_term_thumbnail" value="<?php echo esc_attr( $thumbnail_id ); ?>" />

					<button id="wpex-add-term-thumbnail" class="button-secondary"><?php esc_attr_e( 'Select', 'total' ); ?></button>

					<button id="wpex-term-thumbnail-remove" class="button-secondary"<?php if ( ! $thumbnail_id ) echo ' style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'total' ); ?></button>

					<div id="wpex-term-thumbnail-preview" data-image-size="80">
						<?php if ( ! empty( $thumbnail_url ) ) { ?>
							<img class="wpex-term-thumbnail-img" src="<?php echo esc_url( $thumbnail_url ); ?>" width="80" height="80" style="margin-top:10px;" />
						<?php } ?>
					</div>

				</td>

			</tr>

		<?php endif; ?>

		<?php

	}

	/**
	 * Enqueue Admin scripts for uploading/selecting thumbnails.
	 */
	public function enqueue_admin_scripts() {

		wp_enqueue_media();

		wp_enqueue_script(
			'wpex-term-thumbnails',
			TTC_PLUGIN_DIR_URL . 'assets/js/term-thumbnails.min.js',
			array( 'jquery' ),
			'1.0',
			true
		);

	}

	/**
	 * Saves term data in database.
	 */
	public function add_term_data( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		update_term_meta( $term_id, $meta_key, wp_strip_all_tags( $meta_value ), $prev_value );
	}

	/**
	 * Delete term data from database.
	 */
	public function remove_term_data( $term_id, $key ) {

		if ( empty( $term_id ) || empty( $key ) ) {
			return;
		}

		delete_term_meta( $term_id, $key );

	}

	/**
	 * Delete term data from database.
	 */
	public function remove_deprecated_term_data( $term_id, $key ) {

		// Validate data
		if ( empty( $term_id ) || empty( $key ) ) {
			return;
		}

		// Get deprecated data
		$term_data = get_option( 'wpex_term_data' );

		// Add to options
		if ( isset( $term_data[$term_id][$key] ) ) {
			unset( $term_data[$term_id][$key] );
		}

		// Update option
		update_option( 'wpex_term_data', $term_data );

	}

	/**
	 * Update thumbnail value.
	 */
	public function update_thumbnail( $term_id, $thumbnail_id ) {

		// Add thumbnail
		if ( ! empty( $thumbnail_id ) ) {
			$this->add_term_data( $term_id, 'thumbnail_id', $thumbnail_id );
		}

		// Delete thumbnail
		else {
			$this->remove_term_data( $term_id, 'thumbnail_id' );
		}

		// Remove old data
		$this->remove_deprecated_term_data( $term_id, 'thumbnail' );

	}

	/**
	 * Update page header image option.
	 */
	public function update_page_header_img( $term_id, $display ) {

		// Turn into string since we can't check if false is empty
		if ( is_bool( $display ) ) {
			$display = $display ? 'true' : 'false';
		}

		// Add option
		if ( isset( $display ) && '' != $display ) {
			$this->add_term_data( $term_id, 'page_header_bg', $display );
		}

		// Remove option
		else {
			$this->remove_term_data( $term_id, 'page_header_bg' );
		}

		// Remove old data
		$this->remove_deprecated_term_data( $term_id, 'page_header_bg' );

	}

	/**
	 * Save Forms.
	 */
	public function save_forms( $term_id, $tt_id = '', $taxonomy = '' ) {

		// Nonce check
		if ( ! isset( $_POST['wpex_term_thumbnail_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['wpex_term_thumbnail_meta_nonce'], 'wpex_term_thumbnail_meta_nonce' )
		) {
			return;
		}

		if ( isset( $_POST['wpex_term_thumbnail'] ) ) {
			$this->update_thumbnail( $term_id, $_POST['wpex_term_thumbnail'] );
		}

		if ( isset( $_POST['wpex_term_page_header_image'] ) ) {
			$this->update_page_header_img( $term_id, $_POST['wpex_term_page_header_image'] );
		}

	}

	/**
	 * Thumbnail column added to category admin.
	 */
	public function admin_columns( $columns ) {
		$columns['wpex-term-thumbnail-col'] = esc_attr__( 'Thumbnail', 'total-theme-core' );
		return $columns;
	}

	/**
	 * Thumbnail column value added to category admin.
	 */
	public function admin_column( $columns, $column, $id ) {

		// Add thumbnail to columns
		if ( 'wpex-term-thumbnail-col' === $column ) {

			$thumbnail = '';

			if ( $thumbnail_id = $this->get_term_thumbnail_id( $id ) ) {
				$thumbnail = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
			}

			if ( $thumbnail ) {
				$columns .= '<img loading="lazy" src="' . esc_url( $thumbnail[0] ) . '" alt="' . esc_attr__( 'Thumbnail', 'total-theme-core' ) . '" class="wp-post-image" height="80" width="80" />';
			} else {
				$columns .= '&#8212;';
			}

		}

		// Return columns
		return $columns;

	}

	/**
	 * Get term meta with fallback
	 */
	public function get_term_meta( $term_id = null, $key = '', $single = true ) {

		$term_id = $term_id ? $term_id : get_queried_object()->term_id;
		$value   = '';

		if ( $term_id ) {

			$value = get_term_meta( $term_id, $key, $single );

			if ( isset( $value ) ) {
				return $value;
			}

			$term_data = get_option( 'wpex_term_data' );
			$term_data = ! empty( $term_data[ $term_id ] ) ? $term_data[ $term_id ] : '';

			if ( $term_data && ! empty( $term_data[ $key ] ) ) {
				return $term_data[ $key ];
			}

		}

		return $value;

	}

	/**
	 * Retrieve term thumbnail for admin panel.
	 */
	public function get_term_thumbnail_id( $term_id = null ) {

		$term_id      = $term_id ? $term_id : get_queried_object()->term_id;
		$thumbnail_id = '';

		// Get thumbnail ID from term
		if ( $term_id ) {

			$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );

			// Check option
			if ( empty( $thumbnail_id ) ) {

				// Get data
				$term_data = get_option( 'wpex_term_data' );
				$term_data = ! empty( $term_data[ $term_id ] ) ? $term_data[ $term_id ] : '';

				// Return thumbnail ID
				if ( $term_data && ! empty( $term_data[ 'thumbnail' ] ) ) {
					return $term_data[ 'thumbnail' ];
				}

			}

		}

		// Return thumbnail
		return $thumbnail_id;

	}

	/**
	 * Check if the term page header should have a background image.
	 */
	public function page_header_style( $style ) {
		if ( $this->is_tax_archive()
			&& wpex_term_page_header_image_enabled()
			&& $term_thumbnail = wpex_get_term_thumbnail_id()
		) {
			$style = 'background-image';
		}
		return $style;
	}

	/**
	 * Sets correct page header background.
	 */
	public function page_header_bg( $image ) {
		if ( $this->is_tax_archive()
			&& wpex_term_page_header_image_enabled()
			&& $term_thumbnail = wpex_get_term_thumbnail_id()
		) {
			$image = wp_get_attachment_image_src( $term_thumbnail, 'full' );
			$image = $image[0];
		}
		return $image;
	}

	/**
	 * Check if on a tax archive.
	 */
	public function is_tax_archive() {
		if ( ! is_search() && ( is_tax() || is_category() || is_tag() ) ) {
			return true;
		}
	}

}
Term_Thumbnails::instance();