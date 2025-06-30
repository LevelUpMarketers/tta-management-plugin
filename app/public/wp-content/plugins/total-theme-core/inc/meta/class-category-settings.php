<?php
/**
 * Adds custom settings for post categories
 *
 * @todo Update to use term_meta instead of get_option and apply fallbacks.
 * @todo Allow settings to be added to custom taxonomies via filter?
 * @todo Use a loop to display settings (see TotalThemeCore\TermMeta)
 *
 * @package TotalThemeCore
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

final class Category_Settings {

	/**
	 * Our single Category_Settings instance.
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
	 * Create or retrieve the instance of Category_Settings.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Category_Settings;
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
	 * Adds new category fields.
	 */
	public function admin_init() {

		if ( ! apply_filters( 'wpex_category_settings', true ) ) {
			return;
		}

		add_action ( 'category_edit_form_fields', array( $this, 'category_edit_form_fields' ) );
		add_action ( 'edited_category', array( $this, 'edited_category' ) );

	}

	/**
	 * Adds new category fields.
	 */
	public function category_edit_form_fields( $term ) {

		// Get term id
		$term_meta = get_option( 'category_' . sanitize_key( $term->term_id ) );

		// Layout
		$layout = ! empty( $term_meta['wpex_term_layout'] ) ? $term_meta['wpex_term_layout'] : '' ; ?>
		<tr class="form-field wpex_term_layout">
		<th scope="row" valign="top"><label for="wpex_term_layout"><?php esc_html_e( 'Layout', 'total' ); ?></label></th>
		<td>
			<select name="term_meta[wpex_term_layout]">
				<option value="" <?php selected( $layout ) ?>><?php esc_html_e( 'Default', 'total' ); ?></option>
				<option value="right-sidebar" <?php selected( $layout, 'right-sidebar', true ) ?>><?php esc_html_e( 'Right Sidebar', 'total' ); ?></option>
				<option value="left-sidebar" <?php selected( $layout, 'left-sidebar', true ) ?>><?php esc_html_e( 'Left Sidebar', 'total' ); ?></option>
				<option value="full-width" <?php selected( $layout, 'full-width', true ) ?>><?php esc_html_e( 'No Sidebar', 'total' ); ?></option>
			</select>
		</td>
		</tr>

		<?php
		// Style
		if ( ! get_theme_mod( 'blog_entry_card_style' ) ) {
			$style = ! empty( $term_meta['wpex_term_style'] ) ? $term_meta['wpex_term_style'] : '' ; ?>
			<tr class="form-field wpex_term_style">
			<th scope="row" valign="top"><label for="wpex_term_style"><?php esc_html_e( 'Style', 'total' ); ?></label></th>
			<td>
				<select name="term_meta[wpex_term_style]">
					<option value="" <?php selected( $style, '', true ); ?>><?php esc_html_e( 'Default', 'total' ); ?></option>
					<option value="large-image" <?php selected( $style, 'large-image', true ); ?>><?php esc_html_e( 'Large Image', 'total' ); ?></option>
					<option value="thumbnail" <?php selected( $style, 'thumbnail', true ); ?>><?php esc_html_e( 'Left Thumbnail', 'total' ); ?></option>
					<option value="grid" <?php selected( $style, 'grid', true ); ?>><?php esc_html_e( 'Grid', 'total' ); ?></option>
				</select>
			</td>
			</tr>
		<?php } ?>

		<?php
		// Grid Columns
		$grid_cols = ! empty( $term_meta['wpex_term_grid_cols'] ) ? $term_meta['wpex_term_grid_cols'] : ''; ?>
		<tr class="form-field wpex_term_grid_cols">
		<th scope="row" valign="top"><label for="wpex_term_grid_cols"><?php esc_html_e( 'Grid Columns', 'total' ); ?></label></th>
		<td>
			<select name="term_meta[wpex_term_grid_cols]">
				<option value=""  <?php selected( $grid_cols, '', true ); ?>><?php esc_html_e( 'Default', 'total' ); ?></option>
				<option value="1" <?php selected( $grid_cols, 1, true ) ?>>1</option>
				<option value="2" <?php selected( $grid_cols, 2, true ) ?>>2</option>
				<option value="3" <?php selected( $grid_cols, 3, true ) ?>>3</option>
				<option value="4" <?php selected( $grid_cols, 4, true ) ?>>4</option>
				<option value="5" <?php selected( $grid_cols, 5, true ) ?>>5</option>
				<option value="6" <?php selected( $grid_cols, 6, true ) ?>>6</option>
			</select>
		</td>
		</tr>

		<?php
		// Grid Style
		$grid_style = ! empty( $term_meta['wpex_term_grid_style'] ) ? $term_meta['wpex_term_grid_style'] : '' ; ?>
		<tr class="form-field wpex_term_grid_style">
		<th scope="row" valign="top"><label for="wpex_term_grid_style"><?php esc_html_e( 'Grid Style', 'total' ); ?></label></th>
		<td>
			<select name="term_meta[wpex_term_grid_style]">
				<option value="" <?php selected( $grid_style, '', true ) ?>><?php esc_html_e( 'Default', 'total' ); ?></option>
				<option value="fit-rows" <?php selected( $grid_style, 'fit-rows', true ) ?>><?php esc_html_e( 'Fit Rows', 'total' ); ?></option>
				<option value="masonry" <?php selected( $grid_style, 'masonry', true ) ?>><?php esc_html_e( 'Masonry', 'total' ); ?></option>
			</select>
		</td>
		</tr>

		<?php
		// Grid Gap
		if ( function_exists( 'wpex_column_gaps' ) ) {
			$gap = ! empty( $term_meta['wpex_term_grid_gap'] ) ? $term_meta['wpex_term_grid_gap'] : '' ;
			?>
			<tr class="form-field wpex_term_grid_gap">
			<th scope="row" valign="top"><label for="wpex_term_grid_gap"><?php esc_html_e( 'Grid Gap', 'total' ); ?></label></th>
			<td>
				<select name="term_meta[wpex_term_grid_gap]">
					<?php $gaps = wpex_column_gaps();
					foreach ( $gaps as $gapk => $gapv ) { ?>
						<option value="<?php echo esc_attr( $gapk ); ?>" <?php selected( $gap, $gapk, true ) ?>><?php echo esc_html( $gapv ); ?></option>
					<?php } ?>
				</select>
			</td>
			</tr>
		<?php } ?>

		<?php
		// Pagination Type
		$pagination = ! empty( $term_meta['wpex_term_pagination'] ) ? $term_meta['wpex_term_pagination'] : ''; ?>
		<tr class="form-field wpex_term_pagination">
		<th scope="row" valign="top"><label for="wpex_term_pagination"><?php esc_html_e( 'Pagination', 'total' ); ?></label></th>
		<td>
			<select name="term_meta[wpex_term_pagination]">
				<option value="" <?php selected( $pagination, '', true ) ?>><?php esc_html_e( 'Default', 'total' ); ?></option>
				<option value="standard" <?php selected( $pagination, 'standard', true ) ?>><?php esc_html_e( 'Standard', 'total' ); ?></option>
				<option value="load_more" <?php selected( $pagination, 'load_more', true ) ?>><?php esc_html_e( 'Load More', 'total' ); ?></option>
				<option value="infinite_scroll" <?php selected( $pagination, 'infinite_scroll', true ) ?>><?php esc_html_e( 'Inifinite Scroll', 'total' ); ?></option>
				<option value="next_prev" <?php selected( $pagination, 'next_prev', true ) ?>><?php esc_html_e( 'Next/Previous', 'total' ); ?></option>
			</select>
		</td>
		</tr>

		<?php
		// Excerpt length
		$excerpt_length = ! empty( $term_meta['wpex_term_excerpt_length'] ) ? intval( $term_meta['wpex_term_excerpt_length'] ) : ''; ?>
		<tr class="form-field wpex_term_excerpt_length">
		<th scope="row" valign="top"><label for="wpex_term_excerpt_length"><?php esc_html_e( 'Excerpt Length', 'total' ); ?></label></th>
			<td>
			<input type="number" name="term_meta[wpex_term_excerpt_length]" size="3" value="<?php echo esc_attr( $excerpt_length ); ?>">
			</td>
		</tr>

		<?php
		// Posts Per Page
		$posts_per_page = ! empty( $term_meta['wpex_term_posts_per_page'] ) ? intval( $term_meta['wpex_term_posts_per_page'] ) : ''; ?>
		<tr class="form-field wpex_term_posts_per_page">
		<th scope="row" valign="top"><label for="wpex_term_posts_per_page"><?php esc_html_e( 'Posts Per Page', 'total' ); ?></label></th>
			<td>
			<input type="number" name="term_meta[wpex_term_posts_per_page]" size="3" value="<?php echo esc_attr( $posts_per_page ); ?>">
			</td>
		</tr>

		<?php
		// Image Width
		$wpex_term_image_width = ! empty( $term_meta['wpex_term_image_width'] ) ? intval( $term_meta['wpex_term_image_width'] ) : '';?>
		<tr class="form-field wpex_term_image_width">
		<th scope="row" valign="top"><label for="wpex_term_image_width"><?php esc_html_e( 'Image Width', 'total' ); ?></label></th>
			<td>
			<input type="number" name="term_meta[wpex_term_image_width]" size="3" value="<?php echo esc_attr( $wpex_term_image_width ); ?>">
			</td>
		</tr>

		<?php
		// Image Height
		$wpex_term_image_height = ! empty( $term_meta['wpex_term_image_height'] ) ? intval( $term_meta['wpex_term_image_height'] ) : ''; ?>
		<tr class="form-field wpex_term_image_height">
		<th scope="row" valign="top"><label for="wpex_term_image_height"><?php esc_html_e( 'Image Height', 'total' ); ?></label></th>
			<td>
			<input type="number" name="term_meta[wpex_term_image_height]" size="3" value="<?php echo esc_attr( $wpex_term_image_height ); ?>">
			</td>
		</tr>

		<?php wp_nonce_field( 'wpex_category_settings', 'wpex_category_settings' ); ?>

	<?php  }

	/**
	 * Saves new category fields.
	 */
	public function edited_category( $term_id ) {

		// Nonce check
		if ( ! isset( $_POST['wpex_category_settings'] )
			|| ! wp_verify_nonce( $_POST['wpex_category_settings'], 'wpex_category_settings' )
		) {
			return;
		}

		// Save meta settings
		if ( isset( $_POST['term_meta'] ) ) {
			$term_id_escaped = 'category_' . sanitize_key( $term_id );
			$term_meta = get_option( $term_id_escaped );
			$cat_keys  = array_keys( $_POST['term_meta'] );
			foreach ( $cat_keys as $key ) {
				if ( isset( $_POST['term_meta'][$key] ) ) {
					$term_meta[sanitize_key($key)] = wp_strip_all_tags( $_POST['term_meta'][$key] );
				}
			}
			if ( is_array( $term_meta ) ) {
				$term_meta_escaped = array_map( 'wp_strip_all_tags', $term_meta );
				update_option( $term_id_escaped, $term_meta_escaped );
			}
		}
	}

}
Category_Settings::instance();