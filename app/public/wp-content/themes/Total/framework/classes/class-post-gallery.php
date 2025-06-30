<?php
/**
 * Create custom gallery output for the WP gallery shortcode
 *
 * @package Total WordPress theme
 * @subpackage Classes
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class PostGallery {

	/**
	 * Our single PostGallery instance.
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
	 * Create or retrieve the instance of PostGallery.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new PostGallery;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'on_init' ), 10 );
	}

	/**
	 * Get things started...adds extra check via filter that we can use for vendor integrations.
	 */
	public function on_init() {

		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'wpex_image_sizes', array( $this, 'add_image_sizes' ), 999 );

		if ( wpex_is_request( 'frontend' ) ) {
			add_filter( 'post_gallery', array( $this, 'output' ), 10, 2 );
		}

	}

	/**
	 * Check if enabled on init (this allowes for conditionally disabling after WP has loaded)
	 */
	public function is_enabled() {
		return apply_filters( 'wpex_custom_wp_gallery', true );
	}

	/**
	 * Adds image sizes for your galleries to the image sizes panel.
	 */
	public function add_image_sizes( $sizes ) {
		$sizes['gallery'] = array(
			'label'   => esc_html__( 'WordPress Gallery', 'total' ),
			'width'   => 'gallery_image_width',
			'height'  => 'gallery_image_height',
			'crop'    => 'gallery_image_crop',
			'section' =>  'other',
		);
		return $sizes;
	}

	/**
	 * Tweaks the default WP Gallery Output.
	 */
   public function output( $output, $attr ) {
		$post = get_post();
    	static $instance = 0;
    	$instance++;

		// 'ids' is explicitly ordered, unless you specify otherwise.
		if ( ! empty( $attr['ids'] ) ) {
			if ( empty( $attr['orderby'] ) ) {
				$attr['orderby'] = 'post__in';
			}
			$attr['include'] = $attr['ids'];
	    }

		// Sanitize orderby statement
		if ( isset( $attr['orderby'] ) ) {
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
			if ( ! $attr['orderby'] ) {
				unset( $attr['orderby'] );
			}
		}

		// Get shortcode attributes
		extract( shortcode_atts( array(
			'order'      => 'ASC',
            'orderby'    => 'menu_order ID',
			'id'         => $post->ID,
			'columns'    => 3,
			'gap'        => apply_filters( 'wpex_wp_gallery_shortcode_gap', '20' ),
			'include'    => '',
			'exclude'    => '',
			'img_height' => '',
			'img_width'  => '',
			'size'       => '',
			'crop'       => '',
		), $attr ) );

		// Sanitize gap
		$gap = ( $gap = absint( $gap ) ) ? (string) $gap : '20';

		// Get post ID
		$id = intval( $id );

		if ( 'RAND' == $order ) {
			$orderby = 'none';
		}

		if ( ! empty( $include ) ) {
			$include      = preg_replace( '/[^0-9,]+/', '', $include );
			$_attachments = get_posts(
				array(
					'include'        => $include,
					'post_status'    => '',
					'inherit'        => '',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $order,
					'orderby'        => $orderby
				)
			);

		$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif ( ! empty( $exclude ) ) {
			$exclude     = preg_replace( '/[^0-9,]+/', '', $exclude );
			$attachments = get_children( array(
				'post_parent'    => $id,
				'exclude'        => $exclude,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => $order,
				'orderby'        => $orderby) );
		} else {
			$attachments = get_children( array(
				'post_parent'    => $id,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => $order,
				'orderby'        => $orderby
			) );
		}

		if ( empty( $attachments ) ) {
        	return '';
    	}

		if ( is_feed() ) {
			$output = "\n";
			$size   = $size ? $size : 'thumbnail';
			foreach ( $attachments as $attachment_id => $attachment )
				$output .= wp_get_attachment_link( $attachment_id, $size, true ) . "\n";
			return $output;
		}

		// Get columns #
		$columns = intval( $columns );

		// Set cropping sizes
		if ( $columns > 1 ) {
			$img_width  = $img_width ? $img_width : get_theme_mod( 'gallery_image_width' );
			$img_height = $img_height ? $img_height : get_theme_mod( 'gallery_image_height' );
		}

		// Sanitize cropping
		$size = $size ? $size : 'large';
		$size = ( $img_width || $img_height ) ? 'wpex_custom' : $size;
		$crop = $crop ? $crop : 'center-center';

		// Load lightbox scripts
		wpex_enqueue_lightbox_scripts();

		// Gallery class
		$gallery_class = array(
			'wpex-gallery',
			'wpex-row',
			'gap-' . sanitize_html_class( $gap ),
			'wpex-lightbox-group',
			'wpex-clr',
		);

		// Add some extra margin for small gap grids
		if ( '1' == $gap ) {
			$gallery_class[] = 'wpex-mb-20';
		} elseif ( '5' == $gap ) {
			$gallery_class[] = 'wpex-mb-15';
		} elseif ( '10' == $gap ) {
			$gallery_class[] = 'wpex-mb-10';
		} elseif ( '15' == $gap ) {
			$gallery_class[] = 'wpex-mb-5';
		}

		// Begin output
		$output = '<div id="gallery-' . esc_attr( $instance ) . '" class="' . esc_attr( implode( ' ', $gallery_class ) )  . '">';

			// Begin Loop
			$count  = 0;
			foreach ( $attachments as $attachment_id => $attachment ) {

				// Increase counter for clearing floats
				$count++;

				// Attachment Vars
				$attachment_id   = $attachment->ID;
				$alt             = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
				$video           = get_post_meta( $attachment_id, '_video_url', true );

				// Define lightbox data var
				$lightbox_data   = '';

				// Sanitize Video URL
				if ( $video ) {
					$video = ( $video_embed_url = wpex_get_video_embed_url( $video ) ) ? $video_embed_url : $video;
				}

				// Get lightbox image
				$lightbox_image = wpex_get_lightbox_image( $attachment_id );

				// Set correct lightbox URL
				$lightbox_url = $video ? $video : $lightbox_image;

				// Set correct data values
				if ( $video ) {
					$lightbox_data .= ' data-thumb="'. esc_url( $lightbox_image ) .'"';
				} elseif ( trim( $attachment->post_excerpt ) ) {
					$lightbox_data .= ' data-caption="' . htmlspecialchars( wp_kses_post( $attachment->post_excerpt ) ) . '"';
				}

				// Add title for lightbox
				if ( get_theme_mod( 'lightbox_titles', true ) && $alt ) {
					$lightbox_data .= ' data-title="' . esc_attr( $alt ) . '"';
				}

				// Entry classes
				$entry_classes = array( 'gallery-item' );
				$entry_classes[] = wpex_grid_class( $columns );
				$entry_classes[] = 'nr-col';
				$entry_classes[] = 'col-' . sanitize_html_class( $count );
				$entry_classes   = apply_filters( 'wpex_wp_gallery_entry_classes', $entry_classes );

				// Start Gallery Item
				$output .= '<figure class="' . esc_attr( implode( ' ', $entry_classes ) ) . '">';

					// Display image
					$output .= '<a href="' . esc_url( $lightbox_url ) . '" class="wpex-lightbox-group-item"' . $lightbox_data . '>';

						$output .= wpex_get_post_thumbnail( array(
							'attachment' => $attachment_id,
							'size'       => $size,
							'width'      => $img_width,
							'height'     => $img_height,
							'crop'       => $crop,
							'alt'        => $alt,
						) );

					$output .= '</a>';

					// Display Caption
					if ( trim( $attachment->post_excerpt ) ) {

						$output .= '<figcaption class="gallery-caption wpex-last-mb-0">';

							$output .= wp_kses_post( wptexturize( $attachment->post_excerpt ) );

						$output .= '</figcaption>';

					}

				// Close gallery item div
				$output .= '</figure>';

				// Reset counter
				if ( $count == intval( $columns ) ) {
					$count = 0;
				}

			}

		// Close gallery div
		$output .= "</div>\n";

		return $output;
	}

}
PostGallery::instance();