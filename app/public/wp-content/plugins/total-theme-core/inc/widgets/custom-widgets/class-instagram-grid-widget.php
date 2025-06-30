<?php
/**
 * Instagram Grid widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 *
 * @deprecated Total v5.0
 */

namespace TotalThemeCore;

use WP_Error;

defined( 'ABSPATH' ) || exit;

class InstagramGridWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_insagram_slider',
			'name'    => $this->branding() . esc_html__( 'Instagram Grid (Deprecated)', 'total-theme-core' ),
			'options' => array(
				'customize_selective_refresh' => true,
			),
			'fields'  => array(
				array(
					'id' => 'important_notice',
					'description' => esc_html__( 'This widget currently works by scanning your instagram page to find recent photos and then display them on your site. In 2019 Instagram started blocking shared IP addresses from known hosting companies so unfortunately if your site is hosted on one of these servers the widget may not be able to display your feed. There are 3rd party widget plugins that use other methods of displaying your feeds which you could try if needed. But because of their official statement stating they were not going to allow you to display feeds on 3rd party sites we have decided to deprecate this functionality from the theme.', 'total-theme-core' ),
					'type'  => 'notice',
				),
				array(
					'id'    => 'title',
					'label' => esc_html__( 'Title', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'username',
					'label' => esc_html__( 'Username', 'total-theme-core' ),
					'type'  => 'text',
					'description' => esc_html__( 'Important: The Instagram feed is refreshed every 2 hours to prevent your site from slowing down.', 'total-theme-core' ),
				),
				array(
					'id'      => 'size',
					'label'   => esc_html__( 'Size', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => array(
						'thumbnail' => esc_html__( 'Thumbnail', 'total-theme-core' ),
						'small'     => esc_html__( 'Small', 'total-theme-core' ),
						'large'     => esc_html__( 'Large', 'total-theme-core' ),
						'original'  => esc_html__( 'Original', 'total-theme-core' ),
					),
					'default' => 'thumbnail',
				),
				array(
					'id'      => 'columns',
					'label'   => esc_html__( 'Columns', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'grid_columns',
					'default' => '3',
				),
				array(
					'id'      => 'gap',
					'label'   => esc_html__( 'Gap', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'grid_gaps',
					'default' => '10',
				),
				array(
					'id'    => 'responsive',
					'label' => esc_html__( 'Responsive', 'total-theme-core' ),
					'type'  => 'checkbox',
					'std'   => 'on',
				),
				array(
					'id'          => 'number',
					'label'       => esc_html__( 'Number', 'total-theme-core' ),
					'type'        => 'number',
					'default'     => 9,
					'description' => esc_html__( 'Max 12 items.', 'total-theme-core' ),
				),
				array(
					'id'      => 'target',
					'label'   => esc_html__( 'Open links in', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'link_target',
					'default' => '_self',
				),
			),
		);

		$this->create_widget( $this->args );

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @since 1.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// Parse and extract widget settings
		extract( $this->parse_instance( $instance ) );

		// Before widget hook
		echo wp_kses_post( $args['before_widget'] );

		// Display widget title
		$this->widget_title( $args, $instance );

		// Define widget output
		$output = '';

		// Display notice for username not added
		if ( ! $username ) {

			$output .= '<p>' . esc_html__( 'Please enter an instagram username for your widget.', 'total-theme-core' ) . '</p>';

		} else {

			// Get instagram images
			$media_array = $this->fetch_instagram_feed( $username, $number );

			// Display error message
			if ( is_wp_error( $media_array ) ) {

				$output .= strip_tags( $media_array->get_error_message() );

			}

			// Display instagram feed
			elseif ( is_array( $media_array ) ) {

				$target = ( 'blank' == $target || '_blank' == $target ) ? ' target="_blank"' : '';

				$output .= '<div class="wpex-instagram-grid-widget wpex-clr">';

					$output .= '<ul class="wpex-row gap-' . esc_attr( $gap ) . ' wpex-clr">';

					$count = 0;

					$columns_class = function_exists( 'wpex_grid_class' ) ? wpex_grid_class( $columns ) : 'span_1_of_' . $columns;

					foreach ( $media_array as $item ) {

						$image = isset( $item['display_src'] ) ? $item['display_src'] : '';

						if ( 'thumbnail' == $size ) {
							$image = ! empty( $item['thumbnail_src'] ) ? $item['thumbnail_src'] : $image;
							$image = ! empty( $item['thumbnail'] ) ? $item['thumbnail'] : $image;
						} elseif ( 'small' == $size ) {
							$image = ! empty( $item['small'] ) ? $item['small'] : $image;
						} elseif ( 'large' == $size ) {
							$image = ! empty( $item['large'] ) ? $item['large'] : $image;
						} elseif ( 'original' == $size ) {
							$image = ! empty( $item['original'] ) ? $item['original'] : $image;
						}

						if ( $image ) {

							$count++;

							if ( strpos( $item['link'], 'http' ) === false ) {
								$item['link'] = str_replace( '//', 'https://', $item['link'] );
							}

							$classes = $columns_class . ' count-' . esc_attr( $count ) . ' wpex-clr';

							if ( $responsive && 'false' !== $responsive ) {
								$classes .= ' col';
							} else {
								$classes .= ' nr-col';
							}

							$output .= '<li class="' . esc_attr( $classes ) . '">';

								$output .= '<a href="' . esc_url( $item['link'] ) . '" title="' . esc_attr( $item['description'] ) . '"' . $target . '>';

										$output .= '<img class="wpex-align-bottom wpex-hover-opacity-80" src="' . esc_url( $image ) . '"  alt="' . esc_attr( $item['description'] ) . '" />';

									$output .= '</a>';

								$output .= '</li>';

							if ( $columns == $count ) {
								$count = 0;
							}

						}
					}

					$output .= '</ul>';

				$output .= '</div>';

			}

		}

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @since 1.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function fetch_instagram_feed( $username = '', $slice = 4 ) {

	// Sanitize input and get transient
	$username       = trim( strtolower( $username ) );
	$transient_name = 'wpex-instagram-feed-' . sanitize_title_with_dashes( $username ) . '-' . $slice;
	$instagram      = get_transient( $transient_name );

	// Clear transient
	if ( ! empty( $_GET['wpex_clear_transients'] ) ) {
		$instagram = '';
	}

	// Fetch instagram items
	if ( ! $instagram ) {

		switch ( substr( $username, 0, 1 ) ) {
			case '#':
				$url = 'https://instagram.com/explore/tags/' . str_replace( '#', '', $username );
				break;

			default:
				$url = 'https://instagram.com/' . str_replace( '@', '', $username );
				break;
		}

		$remote = wp_remote_get( $url );

		if ( is_wp_error( $remote ) ) {
			return new WP_Error( 'site_down', esc_html__( 'Unable to communicate with Instagram.', 'total-theme-core' ) );
		}

		if ( 200 !== wp_remote_retrieve_response_code( $remote ) ) {
			return new WP_Error( 'invalid_response', esc_html__( 'Instagram did not return a 200.', 'total-theme-core' ) );
		}

		$shards      = explode( 'window._sharedData = ', $remote['body'] );
		$insta_json  = explode( ';</script>', $shards[1] );
		$insta_array = json_decode( $insta_json[0], true );

		if ( ! $insta_array ) {
			return new WP_Error( 'bad_json', esc_html__( 'Instagram has returned invalid data. Most likely instagram is blocking access to your IP address.', 'total-theme-core' ) );
		}

		if ( isset( $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] ) ) {
			$images = $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
		} elseif ( isset( $insta_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'] ) ) {
			$images = $insta_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'];
		} else {
			return new WP_Error( 'bad_json_2', esc_html__( 'Instagram has returned invalid data.', 'wp-instagram-widget' ) );
		}

		if ( ! is_array( $images ) ) {
			return new WP_Error( 'bad_array', esc_html__( 'Instagram has returned invalid data.', 'total-theme-core' ) );
		}

		$instagram = array();

		foreach ( $images as $image ) {
			if ( true === $image['node']['is_video'] ) {
				$type = 'video';
			} else {
				$type = 'image';
			}

			$caption = esc_html__( 'Instagram Image', 'wp-instagram-widget' );
			if ( ! empty( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'] ) ) {
				$caption = wp_kses( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'], array() );
			}

			$instagram[] = array(
				'description' => $caption,
				'link'        => trailingslashit( '//instagram.com/p/' . $image['node']['shortcode'] ),
				'time'        => $image['node']['taken_at_timestamp'],
				'comments'    => $image['node']['edge_media_to_comment']['count'],
				'likes'       => $image['node']['edge_liked_by']['count'],
				'thumbnail'   => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][0]['src'] ),
				'small'       => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][2]['src'] ),
				'large'       => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][4]['src'] ),
				'original'    => preg_replace( '/^https?\:/i', '', $image['node']['display_url'] ),
				'type'        => $type,
			);
		} // End foreach().

		// Set transient if not empty
		if ( ! empty( $instagram ) ) {
			$instagram = serialize( $instagram );
			set_transient(
				$transient_name,
				$instagram,
				apply_filters( 'wpex_instagram_widget_cache_time', HOUR_IN_SECONDS*2 )
			);
		}

	}

	// Return array
	if ( ! empty( $instagram )  ) {
		if ( ! is_array( $instagram ) && 1 != $instagram ) {
			$instagram = unserialize( $instagram );
		}
		if ( is_array( $instagram ) ) {
			return array_slice( $instagram, 0, $slice );
		}
	}

	// No images returned
	else {

		return new WP_Error( 'no_images', esc_html__( 'Instagram did not return any images.', 'total-theme-core' ) );

	}

}

}
register_widget( 'TotalThemeCore\InstagramGridWidget' );