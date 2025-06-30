<?php
/**
 * Get correct source value
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

class VCEX_Source_Value {
	public $value = '';
	private $atts = '';

	/**
	 * Class Constructor.
	 */
	public function __construct( $source, $atts ) {

		if ( ! empty( $source ) && method_exists( $this, $source ) ) {
			$this->atts = $atts;
			$this->$source();
		}

	}

	/**
	 * Post Title.
	 */
	private function post_title() {
		$this->value = vcex_get_the_title();
	}

	/**
	 * Post Date.
	 */
	private function post_date() {
		$this->value = get_the_date( '', vcex_get_the_ID() );
	}

	/**
	 * Post Modified Date.
	 */
	private function post_modified_date() {
		$this->value = get_the_modified_date( '', vcex_get_the_ID() );
	}

	/**
	 * Post Author.
	 */
	private function post_author() {
		$author = get_the_author();
		if ( empty( $author ) ) {
			$post_tmp = get_post( vcex_get_the_ID() );
			if ( $user = get_userdata( $post_tmp->post_author ) ) {
				$author = $user->data->display_name;
			}
		}
		$this->value = $author;
	}

	/**
	 * Current User.
	 */
	private function current_user() {
		$this->value = wp_get_current_user()->display_name;
	}

	/**
	 * Custom Field.
	 */
	private function custom_field() {
		if ( ! empty( $this->atts[ 'custom_field' ] ) ) {
			$this->value = get_post_meta( vcex_get_the_ID(), $this->atts[ 'custom_field' ], true );
		}
	}

	/**
	 * Callback function.
	 */
	private function callback_function() {
		if ( ! empty( $this->atts[ 'callback_function' ] ) && function_exists( $this->atts[ 'callback_function' ] ) ) {
			$this->value = call_user_func( $this->atts[ 'callback_function' ] );
		}
	}

	/**
	 * Return value.
	 */
	public function get_value() {
		return $this->value;
	}

} // End Class