<?php
/**
 * Register meta options for theme cards.
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'WPEX_Card_Meta' ) ) {

	class WPEX_Card_Meta {

		/**
		 * Our single WPEX_Card_Meta instance.
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
		 * Create or retrieve the instance of WPEX_Card_Meta.
		 */
		public static function instance() {
			if ( is_null( static::$instance ) ) {
				static::$instance = new WPEX_Card_Meta;
				static::$instance->init_hooks();
			}

			return static::$instance;
		}

		/**
		 * Hook into actions and filters.
		 */
		public function init_hooks() {
			add_action( 'admin_init', array( $this, 'register_metabox' ) ); // lower priority so it's not at the very top
		}

		/**
		 * Register card metabox.
		 */
		public function register_metabox() {

			if ( apply_filters( 'wpex_has_card_metabox', true ) && class_exists( 'WPEX_Meta_Factory' ) ) {
				new WPEX_Meta_Factory( $this->card_metabox() );
			}

		}

		/**
		 * Card metabox settings.
		 */
		public function card_metabox() {

			$post_types = apply_filters( 'wpex_card_metabox_post_types', array(
				'post'         => 'post',
				'portfolio'    => 'portfolio',
				'staff'        => 'staff',
				'testimonials' => 'testimonials',
			) );

			return array(
				'id'       => 'card',
				'title'    => esc_html__( 'Card Settings', 'total-theme-core' ),
				'screen'   => $post_types,
				'context'  => 'normal',
				'priority' => 'default',
				'fields'   => array(
					array(
						'name' => esc_html__( 'Link Target', 'total-theme-core' ),
						'id'   => 'wpex_card_link_target',
						'type' => 'select',
						'choices' => array(
							'' => esc_html__( 'Default', 'total-theme-core' ),
							'_blank' => esc_html__( 'New Tab', 'total-theme-core' ),
						),
					),
					array(
						'name' => esc_html__( 'Link URL', 'total-theme-core' ),
						'id'   => 'wpex_card_url',
						'type' => 'text',
					),
					array(
						'name' => esc_html__( 'Thumbnail', 'total-theme-core' ),
						'id'   => 'wpex_card_thumbnail',
						'type' => 'upload',
						'return' => 'id',
						'desc' => esc_html__( 'Select a custom thumbnail to override the featured image.', 'total-theme-core' ),
					),
					array(
						'name' => esc_html__( 'Font Icon', 'total-theme-core' ),
						'id'   => 'wpex_card_icon',
						'type' => 'icon_select',
						'choices' => $this->choices_icons(),
						'desc' => esc_html__( 'Enter your custom Font Icon classname or click the button to select from the available theme icons.', 'total-theme-core' ),
					),
				)
			);

		}

		/**
		 * Icon choices.
		 */
		public function choices_icons() {

			$icons_list = array();

			if ( function_exists( 'wpex_ticons_list' ) ) {

				$ticons = wpex_ticons_list();

				if ( $ticons && is_array( $ticons ) ) {

					foreach( $ticons as $ticon ) {
						if ( 'none' == $ticon || '' == $ticon ) {
							$icons_list[] = esc_html__( 'Default', 'total' );
						} else {
							$icons_list['ticon ticon-' . trim( $ticon )] = $ticon;
						}
					}

				}

			}

			return (array) apply_filters( 'wpex_card_meta_choices_icons', $icons_list );

		}

	}

	WPEX_Card_Meta::instance();

}