<?php
/**
 * Media Functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Custom filter that returns custom content after any entry.
 *
 * @since 4.5.4
 * @todo remove instance.
 */
function wpex_get_entry_media_after( $instance = '' ) {
	return apply_filters( 'wpex_get_entry_media_after', '', $instance );
}

/**
 * Outputs entry media after hook content.
 *
 * @since 4.5.4
 * @todo remove instance.
 */
function wpex_entry_media_after( $instance = '' ) {
	echo wpex_get_entry_media_after( $instance );
}

/**
 * Returns correct post media position.
 *
 * @since 4.3
 */
function wpex_get_custom_post_media_position( $post_id = '', $context = '' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$value = null;
	if ( 'post' == get_post_type() ) {
		$value = get_theme_mod( 'blog_post_media_position_above' );
	}
	if ( $post_id ) {
		$meta = get_post_meta( $post_id, 'wpex_post_media_position', true );
		$value = $meta ? $meta : $value;
	}
	return apply_filters( 'wpex_get_custom_post_media_position', $value );
}

/**
 * Echo post media.
 *
 * @since 5.0
 */
function wpex_post_media( $post_id = '', $args = array() ) {
	echo wpex_get_post_media( $post_id, $args );
}

/**
 * Returns correct post media.
 *
 * @since 4.3
 */
function wpex_get_post_media( $post_id = '', $args = array() ) {

	$post_type = get_post_type();

	if ( 'post' == $post_type ) {
		$post_type = 'blog'; // we use blog to referance standard posts
	}

	$defaults = array(
		'thumbnail_args' => array(
			'size' => $post_type . '_post',
		),
		'lightbox' => false,
	);

	$output = '';
	$args   = wp_parse_args( $args, $defaults );

	if ( $video = wpex_get_post_video( $post_id ) ) {
		$output = wpex_get_post_video_html( $video );
	} elseif ( $audio = wpex_get_post_audio( $post_id ) ) {
		$output = wpex_get_post_audio_html( $audio );
	} elseif ( $gallery = wpex_get_gallery_ids( $post_id ) ) {
		$args['attachments'] = $gallery;
		$output = wpex_get_post_media_gallery_slider( $post_id, $args );
	} elseif ( has_post_thumbnail( $post_id ) ) {
		if ( $args['lightbox'] ) {
			wpex_enqueue_lightbox_scripts();
			$output .= '<a href="' . wpex_get_lightbox_image( get_post_thumbnail_id( $post_id ) ) . '" class="wpex-lightbox">';
			$output .= wpex_get_post_thumbnail( $args['thumbnail_args'] );
			$output .= '</a>';
		} else {
			$output = wpex_get_post_thumbnail( $args['thumbnail_args'] );
		}
	}

	return apply_filters( 'wpex_get_post_media', $output, $post_id, $args );

}

/**
 * Displays post media gallery.
 *
 * @since 5.0
 */
function wpex_post_media_gallery_slider( $post_id = '', $args = array() ) {
	echo wpex_get_post_media_gallery_slider( $post_id, $args );
}

/**
 * Returns post media gallery.
 *
 * @since 4.3
 */
function wpex_get_post_media_gallery_slider( $post_id = '', $args = array() ) {

	$post_id = $post_id ? $post_id : get_the_ID();

	$defaults = array(
		'before'         => '',
		'after'          => '',
		'slider_data'    => apply_filters( 'wpex_get_post_media_gallery_slider_data', null ),
		'thumbnail_args' => array(
			'size' => 'full',
		),
		'attachments'    => '',
		'lightbox'       => wpex_gallery_is_lightbox_enabled( $post_id ),
		'lightbox_title' => true,
		'thumbnails'     => true, // Old deprecated setting. Thumbnail check no in slider_data parameter since v4.4.1
		'captions'       => true,
		'class'          => '',
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'wpex_get_post_media_gallery_args', $args ); // Remove all other filters? Maybe we can make a single function that hooks into this filter for all the fallbacks? We really should only have 1 end filter

	extract( $args );

	$attachments = $attachments ? $attachments : wpex_get_gallery_ids( $post_id );

	if ( ! $attachments ) {
		return;
	}

	// Sanitize extra class
	if ( is_array( $class ) ) {
		$class = array_map( 'esc_attr', $class );
		$class = implode( ' ', $class );
	}

	// Enqueue slider scripts
	wpex_enqueue_slider_pro_scripts();

	// Get slider data
	$slider_data = wpex_get_post_slider_settings( $slider_data ); // parses with default values

	// Check if thumbnails are enabled
	$thumbnails  = ( $thumbnails && isset( $slider_data['thumbnails'] ) && 'true' == $slider_data['thumbnails'] ) ? true : false;

	// Update slider data with current thumbnail display
	$slider_data['thumbnails'] = wp_validate_boolean( $thumbnails ) ? 'true' : 'false';

	$output = '';
	$thumbnails_html = '';

	// Display preloader image
	$preloader_class = 'wpex-slider-preloaderimg';
	if ( $class ) {
		$preloader_class .= ' ' . trim( $class );
	}
	$output .= '<div class="' . esc_attr( $preloader_class ) . '">';

		$thumbnail_args['attachment'] = $attachments[0];
		$thumbnail_args['attributes'] = array( 'data-no-lazy' => 1 ); // !!! important !!!
		$thumbnail_args['alt']        = get_post_meta( $attachments[0], '_wp_attachment_image_alt', true );

		$output .= wpex_get_post_thumbnail( $thumbnail_args );

	$output .= '</div>';

	// Slider wrap attrs
	$wrap_attrs = array(
		'class' => 'wpex-slider slider-pro',
		'data'  => wpex_get_slider_data( $slider_data ),
	);

	if ( $class ) {
		$wrap_attrs['class'] .= ' ' . trim( esc_attr( $class ) );
	}

	// Open slider wrap element
	$output .= '<div ' . trim( wpex_parse_attrs( $wrap_attrs ) ) . '>';


		// Slider attrs
		$slides_attrs = array(
			'class' => 'wpex-slider-slides sp-slides',
		);

		if ( $lightbox ) {
			wpex_enqueue_lightbox_scripts();
			$slides_attrs[ 'class' ] .= ' wpex-lightbox-group';
			if ( ! $lightbox_title ) {
				$slides_attrs['data-show_title'] = 'false';
			}
		}

		// Open inner slider element
		$output .= '<div ' . trim( wpex_parse_attrs( $slides_attrs ) ) . '>';

			// Loop through attachments
			foreach ( $attachments as $attachment ) :

				// Get attachment data
				$attachment_data    = wpex_get_attachment_data( $attachment );
				$attachment_alt     = $attachment_data['alt'];
				$attachment_video   = $attachment_data['video'];
				$attachment_caption = $attachment_data['caption'];

				// Get image output
				$thumbnail_args['attachment'] = $attachment;
				$thumbnail_args['alt']        = $attachment_alt;
				$attachment_html = wpex_get_post_thumbnail( $thumbnail_args );

				// Add html to thumbnails
				if ( $thumbnails ) {
					$small_thumb_args = $thumbnail_args;
					$small_thumb_args['class'] = 'wpex-slider-thumbnail sp-thumbnail';
					$thumbnails_html .= wpex_get_post_thumbnail( $small_thumb_args );
				}

				// Generate video
				if ( $attachment_video ) {

					$attachment_video = wpex_video_oembed( $attachment_video, 'sp-video', array(
						'youtube' => array(
							'enablejsapi' => '1',
						)
					) );

				}

				$output .= '<div class="wpex-slider-slide sp-slide">';

					// Display attachment video
					if ( $attachment_video ) :

						$output .= '<div class="wpex-slider-video">';

							$output .= $attachment_video;

						$output .= '</div>';

					// Display attachment image
					else :

						$output .= '<div class="wpex-slider-media wpex-clr">';

							// Display with lightbox
							if ( $lightbox ) {

								$lightbox_link_attrs = array(
									'href'      => wpex_get_lightbox_image( $attachment ), // already escaped,
									'title'     => esc_attr( $attachment_alt ),
									'class'     => 'wpex-lightbox-group-item',
									'data-type' => 'image',
								);

								if ( $lightbox_title ) {
									$lightbox_link_attrs[ 'data-title' ] = esc_attr( $attachment_alt );
								}

								$output .= wpex_parse_html( 'a', $lightbox_link_attrs, $attachment_html );

							}

							// Display single image
							else {

								$output .= $attachment_html;

							}

							// Display captions
							if ( $captions && $attachment_caption ) {

								$output .= '<div class="wpex-slider-caption sp-layer sp-black sp-padding wpex-clr" data-position="bottomCenter" data-show-transition="up" data-hide-transition="down" data-width="100%" data-show-delay="500">';

									$output .= wp_kses_post( $attachment_caption );

								$output .= '</div>';

							}

						$output .= '</div>';

					endif;

				$output .= '</div>';

			endforeach;

		$output .= '</div>';

		// Show thumbnails if enabled
		if ( $thumbnails && $thumbnails_html ) {

			$output .= '<div class="wpex-slider-thumbnails sp-thumbnails">';

				$output .= $thumbnails_html;

			$output .= '</div>';

		}

	$output .= '</div>';

	if ( $output ) {
		return $before . $output . $after;
	}

}