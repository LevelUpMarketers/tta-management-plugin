<?php
/**
 * Display format icons over featured images
 *
 * @package Total WordPress Theme
 * @subpackage Classes
 * @version 5.0
 *
 * @todo change to wpex_hook_entry_media_after for better consistency
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class ThumbnailFormatIcons {

	/**
	 * Our single ThumbnailFormatIcons instance.
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
	 *
	 * @return void
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of ThumbnailFormatIcons.
	 *
	 * @return ThumbnailFormatIcons
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new ThumbnailFormatIcons;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_filter( 'wpex_get_entry_media_after', array( $this, 'icon_html' ) );
	}

	/**
	 * Check if the thumbnail format icon html is enabled
	 *
	 * @since 4.5.4
	 */
	public function enabled() {
		$bool = ( 'post' == get_post_type() ) ? true : false;
		$bool = apply_filters( 'wpex_thumbnails_have_format_icons', $bool ); // @todo deprecate
		return (bool) apply_filters( 'wpex_has_post_thumbnail_format_icon', $bool );
	}

	/**
	 * Return correct icon class
	 *
	 * @since 4.5.4
	 */
	public function icon_class( $format = '' ) {
		$icon = 'ticon ticon-file-text-o';
		if ( 'video' == $format ) {
			$icon = 'ticon ticon-play';
		} elseif ( 'audio' == $format ) {
			$icon = 'ticon ticon-music';
		} elseif ( 'gallery' == $format ) {
			$icon = 'ticon ticon-file-photo-o';
		} elseif ( 'quote' == $format ) {
			$icon = 'ticon ticon-quote-left';
		}
		return apply_filters( 'wpex_get_thumbnail_format_icon_class', $icon, $format ); // @todo rename filter?

	}

	/**
	 * Get thumbnail format icon
	 *
	 * @since 4.5.4
	 */
	public function icon_html( $media_after = '' ) {

		if ( ! $this->enabled() ) {
			return $media_after;
		}

		$post_format = get_post_format();

		$icon = $this->icon_class( $post_format );

		if ( ! $icon ) {
			return $media_after;
		}

		$icon = '<span class="' . esc_attr( $icon ) . '"></span>';

		$icon = apply_filters( 'wpex_get_thumbnail_format_icon_html', $icon ); //@todo rename filter?

		if ( $icon ) {

			$class = array(
				'wpex-thumbnail-format-icon',
				'wpex-block',
				'wpex-right-0',
				'wpex-bottom-0',
				'wpex-mr-15',
				'wpex-mb-15',
				'wpex-absolute',
				'wpex-text-white',
				'wpex-text-center',
				'wpex-leading-none',
				'wpex-opacity-0',
				'wpex-onload-opacity-100',
			);

			$class = apply_filters( 'wpex_post_thumbnail_format_icon_class', $class );

			return  $media_after . '<i class="' . esc_attr( implode( ' ', $class ) ) . '" aria-hidden="true">' . $icon . '</i>';

		}

	}

}
ThumbnailFormatIcons::instance();