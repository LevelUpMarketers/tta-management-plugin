<?php
/**
 * Real Media Library Configuration Class
 *
 * @package Total WordPress Theme
 * @subpackage Real_Media_Library
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class Real_Media_Library {
	private $vc_supported_modules = array();

	/**
	 * Start things up
	 */
	public function __construct() {

		if ( WPEX_VC_ACTIVE && function_exists( 'vc_request_param' ) ) {

			$this->vc_supported_modules = array(
				'vcex_image_grid',
				'vcex_image_carousel',
				'vcex_image_flexslider',
				'vcex_image_galleryslider',
			);

			add_action( 'init', array( $this, 'add_vc_params' ) );

			$vc_action = vc_request_param( 'action' );

			if ( 'vc_get_autocomplete_suggestion' === $vc_action || 'vc_edit_form' === $vc_action ) {

				foreach ( $this->vc_supported_modules as $module ) {

					$module = sanitize_key( $module );

					add_filter( 'vc_autocomplete_' . $module . '_rml_folder_callback', array( $this, 'suggest_folders' ), 10, 1 );

					add_filter( 'vc_autocomplete_' . $module . '_rml_folder_render', array( $this, 'render_folders' ), 10, 1 );
				}

			}

		}

	}

	/**
	 * Add new parameters to VC modules
	 */
	public function add_vc_params() {

		$settings = array(
			'type'       => 'autocomplete',
			'heading'    => esc_html__( 'Real Media Library Folder', 'total' ),
			'param_name' => 'rml_folder',
			'group'      => esc_html__( 'Gallery', 'total' ),
			'settings'   => array(
				'multiple'       => false,
				'min_length'     => 1,
				'groups'         => true,
				'unique_values'  => true,
				'display_inline' => true,
				'delay'          => 0,
				'auto_focus'     => true,
			),
		);

		foreach ( $this->vc_supported_modules as $module ) {

			vc_add_param( $module, $settings );

			if ( 'vcex_image_grid' != $module ) {

				vc_add_param( $module, array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Count', 'total' ),
					'param_name' => 'posts_per_page',
					'value' => '12',
					'description' => esc_html__( 'How many images to grab from this folder. Enter -1 to display all of them.', 'total' ),
				) );

			}

		}

	}

	/**
	 * Suggest folders for VC auto complete
	 */
	public function suggest_folders() {
		$folders = array();
		$get_folders = $this->folders_array();
		if ( $get_folders ) {
			foreach ( $get_folders as $id => $name ) {
				$folders[] = array(
					'label' => $name,
					'value' => $id,
				);
			}
		}
		return $folders;
	}

	/**
	 * Renders folders for vc autocomplete
	 */
	public function render_folders( $data ) {
		$value = $data['value'];
		if ( function_exists( 'wp_rml_get_by_id' ) ) {
			$get_folder = wp_rml_get_by_id( $value );
			if ( is_object( $get_folder ) ) {
				if ( version_compare( RML_VERSION, '2.8' ) > 0 ) {
					return array(
						'label' => $get_folder->getName(),
						'value' => $value,
					);
				} else {
					return array(
						'label' => $get_folder->name,
						'value' => $value,
					);
				}
			}
		}
		return $data;
	}

	/**
	 * Suggest folders for VC auto complete
	 *
	 * @since 4.0
	 */
	public function folders_array( $include_empty = true, $rec_childs = null, &$folders = array() ) {
		if ( ! function_exists( 'wp_rml_root_childs' ) ) {
			return false;
		}
		if ( $include_empty ) {
			$folders[] = esc_html__( 'Select', 'total' );
		}
		$get_folders = is_array( $rec_childs ) ? $rec_childs : wp_rml_root_childs();
		if ( $get_folders ) {
			if ( version_compare( RML_VERSION, '2.8' ) <= 0 ) {
				foreach ( $get_folders as $parent_folder ) {
					$folders[$parent_folder->id] = $parent_folder->name;
					if ( ! empty( $parent_folder->children ) ) {
						$this->folders_array( false, $parent_folder->children, $folders );
					}
				}
			} else {
				foreach ( $get_folders as $parent_folder ) {
					$folders[$parent_folder->getId()] = $parent_folder->getName();
					if ( method_exists( 'MatthiasWeb\RealMediaLibrary\folder\Folder', 'getChildrens' ) ) {
						$childs = $parent_folder->getChildrens();
					} else {
						$childs = $parent_folder->getChildren();
					}
					if ( ! empty( $childs ) ) {
						$this->folders_array( false, $childs, $folders );
					}
				}
			}
		}
		return $folders;
	}

}
new Real_Media_Library();