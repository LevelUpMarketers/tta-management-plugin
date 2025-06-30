<?php
/**
 * Find template based on theme location
 *
 * @package Total WordPress theme
 * @subpackage Theme Builder
 * @version 5.0
 */

namespace TotalTheme\ThemeBuilder;

defined( 'ABSPATH' ) || exit;

class Location_Template {

	/**
	 * Template to return for specific location.
	 */
	public $template = 0;

	/**
	 * Start things.
	 */
	public final function __construct( $location ) {

		if ( method_exists( $this, $location ) ) {
			$this->$location();
		}

	}

	/**
	 * Archive template.
	 */
	public function archive() {

		if ( is_search() ) {
			$this->search_archive_template(); // search must be first.
		}

		if ( is_tax() ) {
			$this->taxonomy_archive_template();
		}

		if ( is_post_type_archive() ) {
			$this->post_type_archive_template();
		}

		if ( wpex_is_blog_query() ) {
			$this->blog_template();
		}

	}

	/**
	 * Taxonomy template.
	 */
	public function taxonomy_archive_template() {

		$template = '';

		$taxonomy = get_query_var( 'taxonomy' );

		if ( is_tax( array( 'staff_category', 'staff_tag' ) ) ) {
			$template = get_theme_mod( 'staff_archive_template_id' );
		}

		if ( is_tax( array( 'portfolio_category', 'portfolio_tag' ) ) ) {
			$template = get_theme_mod( 'portfolio_archive_template_id' );
		}

		if ( is_tax( array( 'testimonials_category', 'testimonials_tag' ) ) ) {
			$template = get_theme_mod( 'testimonials_archive_template_id' );
		}

		// Check theme mod defined template
		if ( empty( $template ) ) {
			$template = get_theme_mod( $taxonomy . '_template_id' );
		}

		// Check Post Types Unlimited Template (last)
		if ( WPEX_PTU_ACTIVE ) {
			$ptu_check = wpex_get_ptu_tax_mod( $taxonomy, 'template_id' );
			if ( $ptu_check ) {
				$template = $ptu_check;
			}
		}

		$this->template = apply_filters( 'wpex_taxonomy_template_id', $template );

	}

	/**
	 * Blog template.
	 */
	public function blog_template() {
		$template = get_theme_mod( 'blog_archive_template_id' );
		$this->template = apply_filters( 'wpex_blog_archive_template_id', $template );
	}

	/**
	 * Search template.
	 */
	public function search_archive_template() {
		$template = get_theme_mod( 'search_archive_template_id' );
		$this->template = apply_filters( 'wpex_search_archive_template_id', $template );
	}

	/**
	 * Post Type archive template.
	 */
	public function post_type_archive_template() {

		// Check theme mod defined template
		$template = get_theme_mod( get_query_var( 'post_type' ) . '_archive_template_id' );

		// Check Post Types Unlimited Template (last)
		if ( WPEX_PTU_ACTIVE ) {
			$ptu_check = wpex_get_ptu_type_mod( get_query_var( 'post_type' ), 'archive_template_id' );
			if ( $ptu_check ) {
				$template = $ptu_check;
			}
		}

		$this->template = apply_filters( 'wpex_post_type_archive_template_id', $template );

	}

}