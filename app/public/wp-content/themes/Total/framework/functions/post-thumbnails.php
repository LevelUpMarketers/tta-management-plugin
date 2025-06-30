<?php
/**
 * Helper functions for returning/generating post thumbnails
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns thumbnail sizes.
 *
 * @since 2.0.0
 */
function wpex_get_thumbnail_sizes( $size = '' ) {

	global $_wp_additional_image_sizes;

	$sizes = array(
		'full'  => array(
			'width'  => '9999',
			'height' => '9999',
			'crop'   => 0,
		),
	);
	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach( $get_intermediate_image_sizes as $_size ) {

		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

			$sizes[ $_size ]['width']   = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height']  = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop']    = (bool) get_option( $_size . '_crop' );

		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

			$sizes[ $_size ] = array(
				'width'     => $_wp_additional_image_sizes[ $_size ]['width'],
				'height'    => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'      => $_wp_additional_image_sizes[ $_size ]['crop']
			);

		}

	}

	// Get only 1 size if found
	if ( $size ) {
		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}
	}

	// Return sizes
	return $sizes;
}

/**
 * Generates a retina image.
 *
 * @since 2.0.0
 */
function wpex_generate_retina_image( $attachment, $width, $height, $crop, $size = '' ) {
	return wpex_image_resize( array(
		'attachment' => $attachment,
		'width'      => $width,
		'height'     => $height,
		'crop'       => $crop,
		'return'     => 'url',
		'retina'     => true,
		'size'       => $size, // Used to update metadata accordingly
	) );
}

/**
 * Echo post thumbnail url.
 *
 * @since 2.0.0
 */
function wpex_post_thumbnail_url( $args = array() ) {
	echo wpex_get_post_thumbnail_url( $args );
}

/**
 * Return post thumbnail url.
 *
 * @since 2.0.0
 */
function wpex_get_post_thumbnail_url( $args = array() ) {
	$args['return'] = 'url';
	return wpex_get_post_thumbnail( $args );
}

/**
 * Return post thumbnail src.
 *
 * @since 4.0
 */
function wpex_get_post_thumbnail_src( $args = array() ) {
	$args['return'] = 'src';
	return wpex_get_post_thumbnail( $args );
}

/**
 * Outputs the img HTMl thubmails used in the Total VC modules.
 *
 * @since 2.0.0
 */
function wpex_post_thumbnail( $args = array() ) {
	echo wpex_get_post_thumbnail( $args );
}

/**
 * Returns correct HTMl for post thumbnails.
 *
 * @since 2.0.0
 */
function wpex_get_post_thumbnail( $args = array() ) {

	// Default args
	$defaults = array(
		'post'           => null,
		'attachment'     => '',
		'size'           => '',
		'width'          => '',
		'height'         => '',
		'crop'           => '',
		'return'         => 'html',
		'style'          => '',
		'alt'            => '',
		'class'          => '',
		'before'         => '',
		'after'          => '',
		'attributes'     => array(),
		'retina'         => wpex_is_retina_enabled(),
		'retina_data'    => 'rjs',
		'add_image_dims' => true,
		'schema_markup'  => false,
		'placeholder'    => false,
		'apply_filters'  => '',
		'filter_arg1'    => '',
	);

	// Parse args
	$args = wp_parse_args( $args, $defaults );

	// Apply filters = Must run here !!
	if ( $args['apply_filters'] ) {
		$args = apply_filters( $args['apply_filters'], $args, $args['filter_arg1'] );
	}

	// If attachment is empty get attachment from current post
	if ( empty( $args['attachment'] ) ) {
		$args['attachment'] = get_post_thumbnail_id( $args['post'] );
	}

	// Custom output if you want to hook in before doing anything else
	$custom_output = apply_filters( 'wpex_get_post_thumbnail_custom_output', null, $args );

	if ( $custom_output ) {
		return $custom_output;
	}

	// Extract args
	extract( $args );

	// Check if return has been set to null via filter
	if ( null === $return ) {
		return;
	}

	// Return placeholder image
	if ( $placeholder || 'placeholder' === $attachment ) {
		return ( $placeholder = wpex_placeholder_img_src() ) ? '<img src="' . esc_url( $placeholder ) . '" />' : '';
	}

	// If size is empty but width/height are defined set size to wpex_custom
	if ( ! $size && ( $width || $height ) ) {
		$size = 'wpex_custom';
	} else {
		$size = $size ? $size : 'full'; // default size should be full if not defined
	}

	// Set size var to null if set to custom
	$size = ( 'wpex-custom' == $size || 'wpex_custom' == $size ) ? null : $size;

	// If image width and height equal '9999' set image size to full
	if ( '9999' == $width && '9999' == $height ) {
		$size = $size ? $size : 'full';
	}

	// Extra attributes for html return
	if ( 'html' == $return ) {

		// Define attributes for html output
		$attr = $attributes;

		// Prevent 3rd party lazy loading for certain images such as those in sliders/carousels
		if ( ! empty( $attr['data-no-lazy'] ) ) {
			if ( is_array( $class ) ) {
				$class[] = 'skip-lazy';
			} else {
				$class = $class ? $class . ' skip-lazy' : 'skip-lazy';
			}
		}

		// Add native browser lazy loading support for theme featured images
		if ( empty( $attr['data-no-lazy'] ) ) {
			$has_lazy_loading = get_theme_mod( 'post_thumbnail_lazy_loading', true );
			$has_lazy_loading = apply_filters( 'wpex_has_post_thumbnail_lazy_loading', $has_lazy_loading );
			if ( $has_lazy_loading ) {
				$attr['loading'] = 'lazy';
			}
		}

		// Add custom class if defined
		if ( $class ) {
			if ( is_array( $class ) ) {
				$class = array_map( 'esc_attr', $class );
				$class = implode( ' ', $class ); // important for wp_get_attachment_image
			}
			$attr['class'] = $class;
		}

		// Add style
		if ( $style ) {
			$attr['style'] = $style;
		}

		// Add schema markup
		if ( $schema_markup ) {
			$attr['itemprop'] = 'image';
		}

		// Add alt
		if ( $alt ) {
			$attr['alt'] = $alt;
		}

	}

	// On demand resizing
	// Custom Total output (needs to run even when image_resizing is disabled for custom image cropping in VC and widgets)
	if ( 'full' != $size && ( get_theme_mod( 'image_resizing', true ) || ( $width || $height ) ) ) {

		// Crop standard image
		$image = wpex_image_resize( array(
			'attachment' => $attachment,
			'size'       => $size,
			'width'      => $width,
			'height'     => $height,
			'crop'       => $crop,
		) );

		// Generate retina version
		if ( $retina ) {
			$retina_img = apply_filters( 'wpex_get_post_thumbnail_retina', '', $attachment, $size ); // filter for child mods.
			$retina_img = $retina_img ? $retina_img : wpex_generate_retina_image( $attachment, $width, $height, $crop, $size );
		}

		// Return image
		if ( $image ) {

			// Return image URL
			if ( 'url' == $return ) {
				return $image['url'];
			}

			// Return src
			if ( 'src' == $return ) {
				return array(
					$image['url'],
					$image['width'],
					$image['height'],
					$image['is_intermediate'],
				);
			}

			// Return image HTMl
			elseif ( 'html' == $return ) {

				// Add src tag
				$attr['src'] = esc_url( $image['url'] );

				// Check for custom alt if no alt is defined manually
				if ( ! $alt ) {
					$alt = trim( strip_tags( get_post_meta( $attachment, '_wp_attachment_image_alt', true ) ) );
				}

				// Add alt attribute (add empty if none is found)
				$attr['alt'] = $alt ? ucwords( $alt ) : '';

				// Add retina attributes
				if ( ! empty( $retina_img ) ) {
					$attr['data-' . $retina_data] = $retina_img;
					if ( ! apply_filters( 'wpex_retina_resize', true ) ) {
						$attr['data-no-resize'] = '';
						$add_image_dims = false;
					}
				}

				// Add width and height
				if ( $add_image_dims ) {
					if ( isset( $image['width'] ) ) {
						$attr['width'] = intval( $image['width'] );
					}
					if ( isset( $image['height'] ) ) {
						$attr['height'] = intval( $image['height'] );
					}
				}

				// Filter attributes
				$attr = apply_filters( 'wpex_get_post_thumbnail_image_attributes', $attr, $attachment, $args );

				// Apply filters
				$img_html = apply_filters( 'wpex_post_thumbnail_html', '<img ' . wpex_parse_attrs( $attr ) . ' />' );

				// Return image html
				if ( $img_html ) {
					return $before . $img_html . $after;
				}

			}

		}

	}

	// Return image from add_image_size
	// If on-the-fly is disabled for defined sizes or image size is set to "full"
	else {

		// Return image URL
		if ( 'url' == $return ) {
			$src = wp_get_attachment_image_src( $attachment, $size, false );
			if ( $src && ! empty( $src[0] ) ) {
				return $src[0];
			}
		}

		// Return src
		elseif ( 'src' == $return ) {
			return wp_get_attachment_image_src( $attachment, $size, false );
		}

		// Return image HTML
		// Should this use get_the_post_thumbnail instead?
		elseif ( 'html' == $return ) {
			$img_html = apply_filters( 'wpex_post_thumbnail_html', wp_get_attachment_image( $attachment, $size, false, $attr ) );
			if ( $img_html ) {
				return $before . $img_html . $after;
			}
		}

	}

}

/**
 * Returns secondary thumbnail.
 *
 * @since 4.5.5
 */
function wpex_get_secondary_thumbnail( $post_id = '' ) {

	$post_id = $post_id ? $post_id : get_the_ID();

	$meta_thumb = get_post_meta( $post_id, 'wpex_secondary_thumbnail', true );

	if ( ! empty( $meta_thumb ) ) {
		return $meta_thumb;
	}

	$gallery_ids = wpex_get_gallery_ids( $post_id );

	if ( empty( $gallery_ids ) || ! is_array( $gallery_ids ) ) {
		return;
	}

	if ( $gallery_ids[0] != get_post_thumbnail_id() ) {
		return $gallery_ids[0];
	}

	if ( ! empty( $gallery_ids[1] ) ) {
		return $gallery_ids[1];
	}

}