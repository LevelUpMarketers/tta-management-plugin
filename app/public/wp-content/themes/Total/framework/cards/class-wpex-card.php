<?php
/**
 * WPEX_Card Class
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.4
 *
 * @copyright WPExplorer.com
 * @license All Rights Reserved. This is proprietary code. Do not copy, share or redistribute without permission.
 *
 */

defined( 'ABSPATH' ) || exit;

final class WPEX_Card {

	/**
	 * The card arguments.
	 *
	 * @since 5.0
	 * @var array
	 */
	public $args = array();

	/**
	 * The card style.
	 *
	 * @since 5.0
	 * @var string
	 */
	public $style = 'blog_1';

	/**
	 * Post ID for dynamic cards.
	 *
	 * @since 5.0
	 * @var int
	 */
	public $post_id = 0;

	/**
	 * Unique card ID.
	 *
	 * @since 5.0
	 * @var int
	 */
	public $unique_id = 0;

	/**
	 * Current element args.
	 *
	 * @since 5.0
	 * @var array
	 */
	public $el_args = array();

	/**
	 * Initialization.
	 *
	 * @since 5.0
	 */
	public function __construct( $args ) {

		if ( ! is_array( $args ) || empty( $args ) ) {
			return;
		}

		$this->args = $args;

		foreach( get_object_vars( $this ) as $key => $value ) {
			if ( isset( $this->args[$key] ) ) {
				$this->$key = $this->args[$key];
			}
		}

	}

	/**
	 * Render card final output.
	 *
	 * @since 5.0
	 */
	final public function render() {

		$template = $this->locate_template();

		if ( empty( $template ) ) {
			return;
		}

		$template_content = require( $template );

		$this_class = 'wpex-card-' . sanitize_html_class( $this->style );

		if ( ! empty( $this->args['el_class'] ) ) {
			$this_class .= ' ' . $this->args['el_class'];
		}

		if ( function_exists( 'vcex_get_css_animation' )
			&& ! empty( $this->args['css_animation'] )
			&& 'none' !== $this->args['css_animation'] ) {
			$css_animation = vcex_get_css_animation( $this->args['css_animation'] );
			if ( $css_animation ) {
				$this_class .= ' ' . trim( $css_animation );
			}
		}

		$output = '';

			$output .= '<div class="'. esc_attr( $this_class ) . '">';

				$output .= $template_content;

				if ( 'modal' === $this->get_var( 'link_type' ) ) {
					$output .= $this->get_modal( $this );
				}

			$output .= '</div>';

		return $output;

	}

	/**
	 * Locate card template.
	 *
	 * @since 5.0
	 */
	final protected function locate_template() {

		if ( ! array_key_exists( $this->style, wpex_get_card_styles() ) ) {
			return;
		}

		if ( ! empty( $this_styles[$this->style]['template'] ) ) {

			$template = $this_styles[$this->style]['template'];

		} else {

			$category = strstr( $this->style, '_', true );

			$path = 'cards/' . trim( $category ) . '/' . trim( $this->style ) . '.php';

			$template = locate_template( $path, false );

		}

		return $template;
	}

	/**
	 * Get unique_id.
	 *
	 * @since 5.0
	 */
	protected function get_unique_id() {

		if ( $this->post_id ) {
			$this->unique_id = $this->post_id;
		} else {
			$this->unique_id = uniqid();
		}

		return $this->unique_id;

	}

	/**
	 * Check if card is featured.
	 *
	 * @since 5.0
	 */
	public function is_featured() {

		$check = false;

		if ( ! empty( $this->args['featured'] ) && true === $this->args['featured'] ) {
			$check = true;
		}

		return $check;

	}

	/**
	 * Enqueue lightbox scripts.
	 *
	 * @since 5.0
	 */
	public function enqueue_lightbox() {
		if ( function_exists( 'wpex_enqueue_lightbox_scripts' ) ) {
			wpex_enqueue_lightbox_scripts();
		}
	}

	/**
	 * Get card meta.
	 *
	 * @since 5.0
	 */
	public function get_card_meta( $key = '', $check_common = false ) {
		if ( $this->post_id && $key ) {
			$meta = get_post_meta( $this->post_id, 'wpex_card_' . $key, true );
			if ( empty( $meta ) && $check_common ) {
				$meta = get_post_meta( $this->post_id, $key, true ); // check common named meta.
			}
			return $meta;
		}
	}

	/**
	 * Get card breakpoint.
	 *
	 * @since 5.0
	 */
	public function get_breakpoint() {
		$breakpoint = 'md';
		if ( ! empty( $this->args['breakpoint'] ) ) {
			$breakpoint = trim( wp_strip_all_tags( $this->args['breakpoint'] ) );
		}
		return apply_filters( 'wpex_card_breakpoint', $breakpoint, $this );
	}

	/**
	 * Get card post type.
	 *
	 * @since 5.0
	 */
	public function get_post_type() {
		if ( $this->post_id ) {
			return get_post_type( $this->post_id );
		}
	}

	/**
	 * Get card post format.
	 *
	 * @since 5.0
	 */
	public function get_post_format() {
		if ( $this->post_id ) {
			return apply_filters( 'wpex_card_post_format', get_post_format( $this->post_id ), $this );
		}
	}

	/**
	 * Get card object variable.
	 *
	 * @since 5.0
	 */
	public function get_var( $var, $args = '' ) {

		if ( ! empty( $this->$var ) ) {
			return $this->$var;
		}

		$method_name = 'get_' . $var;

		if ( method_exists( $this, $method_name ) ) {
			if ( $args ) {
				return $this->$method_name( $args );
			}
			return $this->$method_name();
		}

	}

	/**
	 * Get card custom field.
	 *
	 * @since 5.0
	 */
	public function get_custom_field( $args = array() ) {

		if ( empty( $args['key'] ) || ! $this->post_id ) {
			return;
		}

		$args['content'] = get_post_meta( $this->post_id, $args['key'], true );

		unset( $args['key'] );

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Card Modals ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Return card modal.
	 *
	 * @since 5.0
	 */
	public function get_modal() {

		$output = '<div id="wpex-card-modal-' . sanitize_html_class( $this->get_var( 'unique_id' ) ) . '" class="wpex-card-modal wpex-hidden wpex-shadow-lg wpex-rounded-sm">';

			// Modal title
			$title = $this->get_modal_title();

			if ( $title ) {

				$output .= '<div class="wpex-card-modal-title wpex-p-20 wpex-border-b wpex-border-solid wpex-border-gray-200 wpex-text-xl wpex-text-black wpex-text-center wpex-font-bold">';
					$output .= esc_html( $title );
				$output .= '</div>';

			}

			// Modal content
			$content = $this->get_modal_content();

			if ( $content ) {

				$output .= '<div class="wpex-card-modal-body wpex-py-40 wpex-px-20 wpex-last-mb-0">';

					if ( function_exists( 'wpex_the_content' ) ) {
						$output .= wpex_the_content( wp_kses_post( $content ) );
					} else {
						$output .= do_shortcode( wp_kses_post( $content ) );
					}

				$output .= '</div>';

			}

			$output .= '<div class="wpex-card-modal-footer wpex-p-20 wpex-text-right wpex-border-t wpex-border-solid wpex-border-gray-200">';

				$output .= '<button href="javascript:;" data-fancybox-close class="wpex-rounded-full">';
					$output .= esc_html__( 'Close', 'total' );
				$output .= '</button>';

			$output .= '</div>';

		$output .= '</div>';

		return $output;

	}

	/**
	 * Return card modal.
	 *
	 * @since 5.0
	 */
	public function get_modal_settings() {

		$settings = array(
			'type'       => 'inline',
		//	'buttons'    => 'false',
			'small-btn'  => 'true',
			'auto-focus' => 'false',
		);

		$this->modal_settings = (array) apply_filters( 'wpex_card_modal_settings', $settings, $this );

		return $this->modal_settings;

	}

	/**
	 * Return card modal content.
	 *
	 * @since 5.0
	 */
	public function get_modal_content() {

		$content = '';

		if ( ! empty( $this->args['modal_content'] ) ) {
			$content = $this->args['modal_content'];
		} elseif ( $this->post_id ) {

			$post = get_post( $this->post_id );

			if ( ! empty( $post ) && ! is_wp_error( $post ) ) {

				if ( function_exists( 'wpex_get_current_post_id' ) ) {
					$current_post = wpex_get_current_post_id();
				} else {
					$current_post = get_queried_object_id();
				}

				// !!! Important check to prevent infinite loops !!!!
				if ( $current_post !== $this->post_id ) {
					$content = $post->post_content;
				}

			}

		}

		return apply_filters( 'wpex_card_modal_content', $content, $this );

	}

	/**
	 * Return card modal title.
	 *
	 * @since 5.0
	 */
	public function get_modal_title() {

		$title = '';

		if ( ! empty( $this->args['modal_title'] ) ) {
			$title = $this->args['modal_title'];
		} elseif ( $this->post_id ) {
			$post = get_post( $this->post_id );
			if ( ! empty( $post ) && ! is_wp_error( $post ) ) {
				$title = get_the_title( $this->post_id );
			}
		}

		return apply_filters( 'wpex_card_modal_title', $title, $this );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Links ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Check if a card has a link.
	 *
	 * @since 5.0
	 */
	public function has_link() {
		return (bool) $this->get_var( 'url' );
	}

	/**
	 * Get card url.
	 *
	 * @since 5.0
	 */
	public function get_url() {

		$link = '';

		$type = $this->get_var( 'link_type' );

		if ( 'none' === $type ) {
			return;
		}

		$url = '';

		$meta_url = $this->get_card_meta( 'url' );

		if ( $meta_url ) {
			$url = $meta_url;
		} else {
			switch ( $type ) {
				case 'modal':
					$url = '#wpex-card-modal-' . sanitize_html_class( $this->get_var( 'unique_id' ) );
					break;
				case 'lightbox':
					$url = $this->get_var( 'lightbox_url' );
					break;
				default:
					if ( $this->post_id && is_post_type_viewable( $this->get_post_type() ) ) {
						if ( function_exists( 'wpex_get_permalink' ) ) {
							$url = wpex_get_permalink( $this->post_id );
						} else {
							$url = get_permalink( $this->post_id );
						}
					}
					break;
			}
		}

		$url = apply_filters( 'wpex_card_url', $url, $this );

		if ( $url ) {
			$url = esc_url( $url );
		}

		$this->url = $url;

		return $this->url;

	}

	/**
	 * Get card link type.
	 *
	 * @since 5.0
	 */
	public function get_link_type() {

		$type = '';

		if ( ! empty( $this->args['link_type'] ) ) {
			$type = $this->args['link_type'];
		} elseif ( $this->post_id ) {
			$type = get_post_meta( $this->post_id, 'wpex_card_link_type', true );
		}

		$this->link_type = apply_filters( 'wpex_card_link_type', $type, $this );

		return $this->link_type;


	}

	/**
	 * Get card link target.
	 *
	 * @since 5.0
	 */
	public function get_link_target() {

		$target = '_self';

		if ( ! empty( $this->args['link_target'] ) ) {
			$target = $this->args['link_target'];
		} elseif ( $this->post_id ) {
			$target = get_post_meta( $this->post_id, 'wpex_card_link_target', true );
		}

		$this->link_target = apply_filters( 'wpex_card_link_target', $target, $this );

		return $this->link_target;

	}

	/**
	 * Get card link rel attribute.
	 *
	 * @since 5.0
	 */
	public function get_link_rel() {

		$rel = '';

		if ( ! empty( $this->args['link_rel'] ) ) {
			$rel = $this->args['link_rel'];
		} elseif( $this->post_id ) {
			$rel = get_post_meta( $this->post_id, 'wpex_card_link_rel', true );
		}

		$this->link_rel = apply_filters( 'wpex_card_link_rel', $rel, $this );

		return $this->link_rel;

	}

	/**
	 * Get custom card link data attributes.
	 *
	 * @since 5.0
	 */
	public function get_link_data() {

		$data = array();

		if ( ! empty( $this->args['link_data'] ) ) {
			$data = $this->args['link_data'];
			if ( function_exists( 'wp_parse_list' ) ) {
				$data = wp_parse_list( $data );
			}
		}

		$this->link_data = (array) apply_filters( 'wpex_card_link_data', $data, $this );

		return $this->link_data;

	}

	/**
	 * Get link attributes.
	 *
	 * @since 5.0
	 */
	public function get_link_attributes() {

		$attrs = array(
			'href' => $this->get_var( 'url' ),
		);

		$type   = $this->get_var( 'link_type' );
		$target = $this->get_var( 'link_target' );
		$rel    = $this->get_var( 'link_rel' );
		$data   = $this->get_var( 'link_data' );

		$class = array();

		switch ( $type ) {
			case 'local':
				$class[] = 'local-scroll-link';
				break;
			case 'modal':
			case 'lightbox':
				$lightbox_type = $this->get_var( 'lightbox_type' );
				$class[] = $this->get_var( 'lightbox_class' );
				$lightbox_data = $this->get_var( 'lightbox_data' );
				if ( ! empty( $lightbox_data ) && is_array( $lightbox_data ) ) {
					foreach( $lightbox_data as $data_key => $data_val ) {
						$data[$data_key] = $data_val;
					}
				}
				$this->enqueue_lightbox();
				break;
			default:
				break;
		}

		switch ( $target ) {
			case 'blank':
			case '_blank':

				$attrs['target'] = '_blank';

				$targeted_rel = apply_filters( 'wpex_targeted_link_rel', 'noopener noreferrer', $attrs['href'] );

				if ( $rel ) {
					$attrs['rel'] = $rel . ' ' . $targeted_rel;
				} else {
					$attrs['rel'] = $targeted_rel;
				}


				break;

			default:

				if ( $rel ) {
					$attrs['rel'] = $rel;
				}

				break;
		}

		if ( $data && is_array( $data ) ) {

			foreach( $data as $datak => $datav ) {
				$attrs['data-' . $datak] = $datav;
			}

		}

		if ( $class ) {
			$attrs['class'] = implode( ' ', $class );
		}

		$this->link_attributes = (array) apply_filters( 'wpex_card_link_attributes', $attrs, $this );

		return $this->link_attributes;

	}

	/**
	 * Get card link open tag.
	 *
	 * @since 5.0
	 */
	public function get_link_open( $args = array() ) {

		if ( ! $this->has_link() ) {
			return;
		}

		$default_args = array(
			'class' => '',
		);

		$args = wp_parse_args( $args, $default_args );

		$attrs = $this->get_var( 'link_attributes' );

		if ( $args['class'] ) {
			if ( empty( $attrs['class'] ) ) {
				$attrs['class'] = $args['class'];
			} else {
				$attrs['class'] .= ' ' . trim( $args['class'] );
			}
		}

		$attrs = array_map( 'esc_attr', $attrs );

		$html = '<a';

			foreach ( $attrs as $name => $value ) {

				if ( ! empty( $value ) ) {
					$html .= ' ' . $name . '="' . $value . '"';
				}

			}

		$html .= '>';

		$this->link_open = $html;

		return $this->link_open;

	}

	/**
	 * Get card link close tag.
	 *
	 * @since 5.0
	 */
	public function get_link_close() {
		if ( $this->has_link() ) {
			return '</a>';
		}
	}

	/*-------------------------------------------------------------------------------*/
	/* [ Lightbox ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card lightbox url.
	 *
	 * @since 5.0
	 */
	public function get_lightbox_url() {

		$lightbox_url = '';

		if ( isset( $this->args['lightbox_url'] ) ) {
			$lightbox_url = $this->args['lightbox_url'];
		} elseif ( isset( $this->args['url'] ) ) {
			$lightbox_url = $this->args['url'];
		} else {

			if ( $this->post_id ) {

				$lightbox_type = $this->get_var( 'lightbox_type' );

				switch ( $lightbox_type ) {
					case 'video':
						$lightbox_url = wpex_get_post_video_oembed_url( $this->post_id );
						break;
					case 'gallery':
						$lightbox_url = '#';
						break;
					case 'thumbnail':
					default:
						$thumbnail_id = $this->get_var( 'thumbnail_id' );
						if ( $thumbnail_id ) {
							$lightbox_url = wpex_get_lightbox_image( $thumbnail_id );
						}
						break;
				}

			} else {
				$lightbox_url = $this->get_var( 'thumbnail_url' );
			}

		}

		$this->lightbox_url = apply_filters( 'wpex_card_lightbox_url', $lightbox_url, $this );

		return $this->lightbox_url;

	}

	/**
	 * Get card lightbox type.
	 *
	 * @since 5.0
	 */
	public function get_lightbox_type() {

		$lightbox_type = 'thumbnail';

		if ( isset( $this->args['lightbox_type'] ) ) {
			$lightbox_type = $this->args['lightbox_type'];
		} elseif ( $this->post_id ) {

			$link_type = $this->get_var( 'link_type' );

			switch ( $link_type ) {
				case 'modal':
					if ( $this->get_card_meta( 'url' ) ) {
						$lightbox_type = 'iframe';
					} else {
						$lightbox_type = 'modal';
					}
					break;
				default:
					$video_check = true;
					if ( 'post' == $this->get_post_type() && 'video' != $this->get_post_format() ) {
						$video_check = false;
					}
					if ( $video_check && wpex_get_post_video_oembed_url( $this->post_id ) ) {
						$lightbox_type = 'video';
					} elseif ( wpex_has_post_gallery() ) {
						$lightbox_type = 'gallery';
					}
					break;
			}

		}

		$this->lightbox_type = apply_filters( 'wpex_card_lightbox_type', $lightbox_type, $this );

		return $this->lightbox_type;

	}

	/**
	 * Get lightbox class.
	 *
	 * @since 5.0
	 */
	public function get_lightbox_class() {

		$lightbox_class = '';

		$lightbox_type = $this->get_var( 'lightbox_type' );

		switch ( $lightbox_type ) {
			case 'gallery':
				$lightbox_class = 'wpex-lightbox-gallery';
				break;
			case 'modal':
			case 'iframe':
			default:
				$lightbox_class = 'wpex-lightbox';
				break;
		}

		$this->lightbox_class = apply_filters( 'wpex_card_lightbox_class', $lightbox_class, $this );

		return $this->lightbox_class;

	}

	/**
	 * Get lightbox data.
	 *
	 * @since 5.0
	 */
	public function get_lightbox_data() {

		$lightbox_data = array();

		$lightbox_type = $this->get_var( 'lightbox_type' );

		switch ( $lightbox_type ) {
			case 'modal':
				$modal_settings = $this->get_var( 'modal_settings' );
				foreach( $modal_settings as $msk => $msv ) {
					$lightbox_data[$msk] = $msv;
				}
				break;
			case 'gallery':
				$lightbox_data['gallery'] = $this->get_var( 'lightbox_gallery_data' );
				break;
			case 'iframe':
				$lightbox_data['type'] = 'iframe';
				break;
			default:
				// No data needed here.
				break;
		}

		$this->lightbox_data = (array) apply_filters( 'wpex_card_lightbox_data', $lightbox_data, $this );

		return $lightbox_data;

	}

	/**
	 * Get lightbox gallery data.
	 *
	 * @since 5.0
	 */
	public function get_lightbox_gallery_data() {
		$this->lightbox_gallery_data = wpex_parse_inline_lightbox_gallery( wpex_get_gallery_ids( $this->post_id ) );
		return $this->lightbox_gallery_data;
	}

	/*-------------------------------------------------------------------------------*/
	/* [ Element ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card element.
	 *
	 * @since 5.0
	 */
	public function get_element( $args = array() ) {

		$default_args = array(
			'name'             => '',
			'class'            => '',
			'link'             => false,
			'link_class'       => '',
			'link_rel'         => '', // used for custom links only
			'link_target'      => '', // used for custom links only
			'content'          => '',
			'sanitize_content' => true,
			'html_tag'         => 'div',
			'before'           => '',
			'after'            => '',
			'icon'             => '',
			'prefix'           => '',
			'suffix'           => '',
			'css'              => '',
			'data'             => '',
			'overlay'          => false,
		);

		$args = wp_parse_args( $args, $default_args );

		$this->el_args = $args;

		$content = $this->parse_element_content();

		if ( empty( $content ) ) {
			return;
		}

		$class = $this->parse_element_class();

		$content_out = '';

		if ( true === $args['link'] ) {

			$content_out .= $this->get_link_open( array(
				'class' => $args['link_class'],
			) );

		} elseif ( ! empty( $args['link'] ) ) {

			$link_attrs = array(
				'href'   => esc_url( $args['link'] ),
				'class'  => esc_attr( $args['link_class'] ),
				'rel'    => esc_attr( $args['link_rel'] ),
				'target' => esc_attr( $args['link_target'] ),
			);

			$content_out .= '<a ' . wpex_parse_attrs( $link_attrs ) . '>';

		}

		if ( ! empty( $args['icon'] ) ) {
			$content_out .= '<span class="' . esc_attr( $args['icon'] ) . '" aria-hidden="true"></span>';
		}

		$content_out .= $args['prefix'] . $content . $args['suffix'];

		$content_out .= $this->get_overlay( $args['overlay'], 'inside_link' );

		if ( true === $args['link'] ) {
			$content_out .= $this->get_link_close();
		} elseif ( ! empty( $args['link'] ) ) {
			$content_out .= '</a>';
		}

		$content_out .= $this->get_overlay( $args['overlay'], 'outside_link' );

		if ( ! empty( $content_out ) ) {

			$el_css = $this->parse_element_css( $args );

			$output = $args['before'];

				$output .= wpex_parse_html( $this->parse_element_html_tag(), array(
					'class' => $class,
					'style' => $el_css,
				), $content_out );

			$output .= $args['after'];

			$this->el_args = null;

			return $output;

		}

		$this->el_args = null;

	}

	/**
	 * Get card empty element.
	 *
	 * @since 5.0.4
	 */
	public function get_empty_element( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'class'    => '',
			'html_tag' => 'div',
			'css'      => '',
		) );

		return wpex_parse_html( $args['html_tag'], array(
			'class' => $args['class'],
			'style' => $args['css'],
		), '' );

	}

	/**
	 * Get current element name.
	 *
	 * @since 5.0
	 */
	protected function get_element_name() {
		if ( ! empty( $this->el_args['name'] ) ) {
			return $this->el_args['name'];
		}
		return '';
	}

	/**
	 * Parse element html tag.
	 *
	 * @since 5.0
	 */
	protected function parse_element_html_tag() {

		$html_tag = 'div';

		if ( ! empty( $this->el_args['html_tag'] ) ) {
			$html_tag = tag_escape( $this->el_args['html_tag'] );
		}

		if ( 'title' == $this->get_element_name() ) {
			$html_tag = $this->get_var( 'title_tag' );
		}

		return $html_tag;

	}

	/**
	 * Parse element class.
	 *
	 * @since 5.0
	 */
	protected function parse_element_class( $args = array() ) {

		if ( ! empty( $args ) ) {
			$this->el_args = $args;
		}

		$args = $this->el_args;

		$element_name = $this->get_element_name();

		$class = array();

		if ( $element_name ) {
			$class[] = 'wpex-card-' . $element_name;
		} else {
			$class[] = 'wpex-card-element';
		}

		switch ( $element_name ) {
			case 'excerpt':
				$class[] = 'wpex-last-mb-0';
				break;
			case 'icon':
				if ( ! empty( $args['size'] ) ) {
					$class[] = 'wpex-icon-' . sanitize_html_class( $args['size'] );
				}
				break;
			case 'thumbnail':
				$class[] = 'wpex-relative';
				if ( $this->get_var( 'thumbnail_hover' ) ) {
					$class[] = wpex_image_hover_classes( $this->get_var( 'thumbnail_hover' ) );
				}
				if ( $this->get_var( 'thumbnail_filter' ) ) {
					$class[] = wpex_image_filter_class( $this->get_var( 'thumbnail_filter' ) );
				}
				break;
		}

		if ( ! empty( $args['class'] ) ) {
			$class_vals = wpex_parse_list( $args['class'] );
			if ( ! empty( $class_vals ) && is_array( $class_vals ) ) {
				foreach( $class_vals as $val ) {
					$class[] = $val;
				}
			}
		}

		if ( $args['overlay'] && 'none' !== $args['overlay'] ) {
			$class[] = trim( wpex_overlay_classes( $args['overlay'] ) );
		}

		if ( $element_name && ! empty( $this->args[$element_name . '_class'] ) ) {
			$custom_class = $this->args[$element_name . '_class'];
			if ( is_array( $custom_class ) ) {
				foreach( $custom_class as $val ) {
					$class[] = $val;
				}
			} else {
				$class[] = $custom_class;
			}
		}

		if ( $element_name && ! empty( $this->args[$element_name . '_font_size'] ) ) {
			$custom_font_size = $this->args[$element_name . '_font_size'];
			if ( $custom_font_size ) {
				$class = $this->modify_element_font_size( $custom_font_size, $class );
			}
		}

		if ( ! empty( $this->args['media_width'] ) && ( 'media' === $element_name || 'thumbnail' == $element_name ) ) {
			$custom_width = $this->args['media_width'];
			if ( $custom_width ) {
				$class = $this->modify_element_width( $custom_width, $class );
			}
		}

		$class = array_map( 'esc_attr', $class );

		return $class;

	}

	/**
	 * Parse element content.
	 *
	 * @since 5.0
	 */
	protected function parse_element_content() {

		if ( empty( $this->el_args['content'] ) ) {
			return;
		}

		$content = $this->el_args['content'];

		if ( $this->el_args['sanitize_content'] ) {
			$content = do_shortcode( wp_kses_post( $content ) );
		}

		return $content;

	}

	/**
	 * Parse element css.
	 *
	 * @since 5.0
	 */
	protected function parse_element_css() {

		$css = '';
		$args = $this->el_args;

		if ( ! empty( $args['css'] ) ) {
			$css = $args['css'];
		}

		if ( ! empty( $args['name'] ) && ! empty( $this->args[$args['name'] . '_css'] ) ) {
			$custom_css = $this->args[$args['name'] . '_css'];
			if ( is_array( $custom_css ) ) {
				$custom_css = implode( ' ', $custom_css );
			}
			$css .= ' ' . trim( $custom_css );
		}

		if ( $css ) {
			$css = ' style="' . esc_attr( trim( $css ) ) . '"';
		}

		return $css;

	}

	/**
	 * Modifies an element font size.
	 *
	 * @since 5.0
	 */
	protected function modify_element_font_size( $custom_font_size = '', $classes = array() ) {

		if ( empty( $classes ) ) {
			return $classes;
		}

		$custom_font_size = wpex_sanitize_utl_font_size( $custom_font_size );

		if ( ! $custom_font_size ) {
			return $classes;
		}

		$font_sizes = wpex_utl_font_sizes();

		if ( $font_sizes ) {
			foreach ( $font_sizes as $key => $value) {
				$font_sizes[$key] = 'wpex-text-' . $key;
			}
			foreach ( $classes as $key => $val ) {
				if ( in_array( $val, $font_sizes ) ) {
					$classes[$key] = $custom_font_size;
				} else {
					$classes[] = $custom_font_size;
				}
			}
		}

		return $classes;

	}

	/**
	 * Modifies an element width.
	 *
	 * @since 5.0
	 */
	protected function modify_element_width( $custom_width = '', $classes = array() ) {

		if ( empty( $classes ) || empty( $custom_width ) ) {
			return $classes;
		}

		$widths = wpex_utl_percent_widths();

		if ( $widths ) {

			// Loop through element classes
			foreach ( $classes as $key => $val ) {

				// Alter width classes
				$class = str_replace( 'wpex-w-', '', $val );
				if ( ! empty( $class ) && array_key_exists( $class, $widths ) ) {
					$classes[$key] = 'wpex-w-' . absint( $custom_width );
					continue;
				}

				// Alter responsive width classes
				$bk_class = str_replace( 'wpex-' . $this->get_breakpoint() . '-w-', '', $val );
				if ( ! empty( $bk_class ) && array_key_exists( $bk_class, $widths ) ) {
					$classes[$key] = 'wpex-' . $this->get_breakpoint() . '-w-' . absint( $custom_width );
				}

			}

		}

		return $classes;

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Media ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card media.
	 *
	 * @since 5.0
	 */
	public function get_media( $args = array() ) {

		$default_args = array(
			'class'          => '',
			'before'         => '',
			'after'          => '',
			'link'           => true,
			'overlay'        => true,
			'class'          => '',
			'image_class'    => '',
			'image_size'     => '',
			'thumbnail_args' => array(),
		);

		$args = apply_filters( 'wpex_card_media_args', wp_parse_args( $args, $default_args ), $this );

		if ( ! empty( $args['image_class'] ) ) {
			$args['thumbnail_args']['image_class'] = $args['image_class'];
		}

		if ( ! empty( $args['image_size'] ) ) {
			$args['thumbnail_args']['size'] = $args['image_size'];
		}

		if ( false === $args['link'] ) {
			$args['thumbnail_args']['link'] = false;
		}

		if ( false === $args['overlay'] ) {
			$args['thumbnail_args']['overlay'] = false;
		}

		$media_type = $this->get_var( 'media_type' );

		switch ( $media_type ) {
			case 'video':
				$media = $this->get_video();
				break;
			case 'audio':
				$media = $this->get_audio();
				break;
			case 'gallery':
				$media = $this->get_gallery_slider( array(
					'thumbnail_args' => $args['thumbnail_args'],
				) );
				break;
			case 'thumbnail':
			default:
				$media = $this->get_thumbnail( $args['thumbnail_args'] );
				break;
		}

		$media = apply_filters( 'wpex_card_media', $media, $this, $args );

		if ( empty( $media ) ) {
			return;
		}

		$args['name'] = 'media';
		$class = $this->parse_element_class( $args );

		// Important - this is a wrapper element, don't use $this->get_element
		$output = $args['before'];

			$output .= '<div class="' . esc_attr( implode( ' ', $class ) ) . '">';

				$output .= $media;

			$output .= '</div>';

		$output .= $args['after'];

		return $output;

	}

	/**
	 * Card media type
	 *
	 * @since 5.0
	 */
	public function get_media_type() {

		$allowed_media = array();

		if ( ! empty( $this->args['display_video'] ) && true === $this->display_video ) {
			$allowed_media[] = 'video';
		}

		if ( ! empty( $this->args['display_audio'] ) && true === $this->display_audio ) {
			$allowed_media[] = 'audio';
		}

		if ( ! empty( $this->args['display_gallery'] ) && true === $this->display_gallery ) {
			$allowed_media[] = 'gallery';
		}

		$allowed_media = apply_filters( 'wpex_card_allowed_media', $allowed_media, $this );

		$type = 'thumbnail';

		if ( $this->post_id ) {

			if ( in_array( 'video', $allowed_media ) && wpex_has_post_video() ) {
				$type = 'video';
			} elseif ( in_array( 'audio', $allowed_media ) && wpex_has_post_audio() ) {
				$type = 'audio';
			} elseif( in_array( 'gallery', $allowed_media ) && wpex_has_post_gallery() ) {
				$type = 'gallery';
			}

		}

		$this->media_type = apply_filters( 'wpex_card_media_type', $type );

		return $this->media_type;

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Thumbnail ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card thumbnail ID.
	 *
	 * @since 5.0
	 */
	public function get_thumbnail_id() {

		$attachment = '';

		if ( ! empty( $this->args['thumbnail_id'] ) ) {
			$attachment = $this->args['thumbnail_id'];
		} elseif ( $this->post_id ) {
			$meta = absint( $this->get_card_meta( 'thumbnail' ) );
			if ( ! empty( $meta ) ) {
				$attachment = $meta;
			} else {
				$attachment = get_post_thumbnail_id( $this->post_id );
			}
		}

		$this->thumbnail_id = apply_filters( 'wpex_card_thumbnail_id', $attachment, $this );

		return $this->thumbnail_id;

	}

	/**
	 * Get card thumbnail url.
	 *
	 * @since 5.0
	 */
	public function get_thumbnail_url( $size = '' ) {

		$attachment = $this->get_var( 'thumbnail_id' );

		if ( ! $attachment ) {
			return false;
		}

		$thumbnail_url = '';

		if ( ! $size ) {
			$size = $this->get_var( 'thumbnail_size', $size );
		}

		$thumbnail_args = array(
			'attachment' => $attachment,
		);

		if ( is_array( $size ) ) {
			$thumbnail_args['width']  = isset( $size[0] ) ? $size[0] : '';
			$thumbnail_args['height'] = isset( $size[1] ) ? $size[1] : '';
			$thumbnail_args['crop']   = isset( $size[2] ) ? $size[2] : '';
		} else {
			$thumbnail_args['size'] = $size;
		}

		if ( function_exists( 'wpex_get_post_thumbnail_url' ) ) {
			$thumbnail_url = wpex_get_post_thumbnail_url( $thumbnail_args );
		} else {
			$thumbnail_url = wp_get_attachment_url( $attachment );
		}

		$this->thumbnail_url = apply_filters( 'wpex_card_thumbnail_url', $thumbnail_url, $this );

		return $this->thumbnail_url;

	}

	/**
	 * Get card thumbnail.
	 *
	 * @since 5.0
	 */
	public function get_thumbnail( $args = array() ) {

		$default_args = array(
			'overlay'     => true,   // overlays are enabled by default.
			'link'        => true,   // all thumbnails have links by default
			'class'       => '',     // class added to the wpex-card-thumbnail el
			'image_class' => '',     // class added directly to the img tag
			'size'        => 'full', // default image size fallback
		);

		$args = apply_filters( 'wpex_card_thumbnail_args', wp_parse_args( $args, $default_args ), $this );

		$size = $this->get_var( 'thumbnail_size', $args['size'] );
		$alt  = isset( $this->thumbnail_alt ) ? $this->thumbnail_alt : '';

		$attachment = $this->get_var( 'thumbnail_id' );

		if ( empty( $attachment ) ) {
			return;
		}

		$image_class = 'wpex-align-middle';

		if ( ! empty( $args['image_class'] ) ) {
			$image_class .= ' ' . trim( $args['image_class'] );
		}

		if ( function_exists( 'wpex_get_post_thumbnail' ) ) {

			$thumbnail_args = array(
				'attachment' => $attachment,
				'alt'        => $alt,
				'class'      => $image_class,
			);

			if ( is_array( $size ) ) {
				$thumbnail_args['width']  = isset( $size[0] ) ? $size[0] : '';
				$thumbnail_args['height'] = isset( $size[1] ) ? $size[1] : '';
				$thumbnail_args['crop']   = isset( $size[2] ) ? $size[2] : '';
			} else {
				$thumbnail_args['size']   = $size;
			}

			$thumbnail = wpex_get_post_thumbnail( $thumbnail_args );

		} else {

			$thumbnail = wp_get_attachment_image( $attachment, $size, array(
				'alt'   => $alt,
				'class' => $image_class,
			) );

		}

		$thumbnail = apply_filters( 'wpex_card_thumbnail', $thumbnail, $this, $args );

		if ( empty( $thumbnail ) ) {
			return;
		}

		if ( $this->post_id
			&& function_exists( 'wpex_get_entry_media_after' )
			&& 'post' === $this->get_post_type()
		) {
			$thumbnail .= wpex_get_entry_media_after( 'card' );
		}

		$args['name']             = 'thumbnail';
		$args['content']          = $thumbnail;
		$args['sanitize_content'] = false;

		if ( ! empty( $args['overlay'] ) ) {
			if ( is_string( $args['overlay'] ) ) {
				$args['overlay'] = $args['overlay'];
			} else {
				$args['overlay'] = $this->get_thumbnail_overlay_style();
			}
		}

		return $this->get_element( $args );

	}

	/**
	 * Get card thumbnail size.
	 *
	 * @since 5.0
	 */
	public function get_thumbnail_size( $default_size = '' ) {

		$size = '';

		if ( isset( $this->args['thumbnail_size'] ) ) {
			$size = $this->args['thumbnail_size'];
		} else {
			$size = $default_size;
		}

		$this->thumbnail_size = apply_filters( 'wpex_card_thumbnail_size', $size, $this );

		return $this->thumbnail_size;

	}

	/**
	 * Get card thumbnail hover.
	 *
	 * @since 5.0
	 */
	public function get_thumbnail_hover() {

		$hover = '';

		if ( isset( $this->args['thumbnail_hover'] ) ) {
			$hover = $this->args['thumbnail_hover'];
		}

		$this->thumbnail_hover = apply_filters( 'wpex_card_thumbnail_size', $hover, $this );

		return $this->thumbnail_hover;

	}

	/**
	 * Get card thumbnail filter.
	 *
	 * @since 5.0
	 */
	public function get_thumbnail_filter() {

		$filter = '';

		if ( isset( $this->args['thumbnail_filter'] ) ) {
			$filter = $this->args['thumbnail_filter'];
		}

		$this->thumbnail_filter = apply_filters( 'wpex_card_thumbnail_size', $filter, $this );

		return $this->thumbnail_filter;

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Overlay ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card thumbnail overlay.
	 *
	 * @since 5.0
	 */
	public function get_thumbnail_overlay_style() {

		$style = '';

		if ( ! empty( $this->args['thumbnail_overlay_style'] ) ) {
			$style = $this->args['thumbnail_overlay_style'];
		} elseif ( $this->post_id ) {
			$style = $this->get_card_meta( 'thumbnail_overlay_style', true );
		}

		$style = apply_filters( 'wpex_card_thumbnail_overlay_style', $style, $this );

		if ( 'none' === $style ) {
			$style = '';
		}

		$this->thumbnail_overlay_style = $style;

		return $this->thumbnail_overlay_style;

	}

	/**
	 * Get card overlay.
	 *
	 * @since 5.0
	 */
	public function get_overlay( $style = '', $position = 'inside' ) {

		if ( empty( $style ) || ! function_exists( 'wpex_overlay' ) ) {
			return;
		}

		$args = array();

		if ( ! empty( $this->args['thumbnail_overlay_button_text'] ) ) {
			$args['overlay_button_text'] = $this->args['thumbnail_overlay_button_text'];
		}

		$link_type = $this->get_var( 'link_type' );

		switch ( $link_type ) {
			case 'modal':
			case 'lightbox':
				$args['lightbox_class'] = $this->get_var( 'lightbox_class' );
				$args['lightbox_link']  = $this->get_var( 'url' );
				$lightbox_data = $this->get_var( 'lightbox_data' );
				if ( ! empty( $lightbox_data ) && is_array( $lightbox_data ) ) {
					$args['lightbox_data'] = '';
					foreach( $lightbox_data as $data_key => $data_val ) {
						$args['lightbox_data'] .= 'data-' . sanitize_key( $data_key ) . '="' . esc_attr( $data_val ) . '" ';
					}
					$args['lightbox_data'] = trim( $args['lightbox_data'] );
				}
				break;
			default:
				$args['post_permalink'] = $this->get_var( 'url' );
				break;
		}

		ob_start();
			wpex_overlay( $position, $style, $args );
		return ob_get_clean();

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Gallery Slider ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card gallery slider.
	 *
	 * @since 5.0
	 */
	public function get_gallery_slider( $args = array() ) {

		if ( ! $this->post_id || ! function_exists( 'wpex_get_post_media_gallery_slider' ) ) {
			return;
		}

		$default_args = array(
			'before'         => '',
			'after'          => '',
			'thumbnail_args' => array(),
			'slider_args'    => array(
				'lightbox'       => false,
				'captions'       => false,
				'slider_data'    => array(
					'thumbnails' => 'false',
					'buttons'    => 'true',
					'fade'       => 'true',
				),
			),
		);

		$args = wp_parse_args( $args, $default_args );

		extract( $args );

		if ( empty( $thumbnail_args['size'] ) ) {

			$thumb_size = isset( $this->thumbnail_size ) ? $this->thumbnail_size : 'full';

			if ( is_array( $thumb_size ) ) {
				$thumbnail_args['width']  = isset( $thumb_size[0] ) ? $thumb_size[0] : '';
				$thumbnail_args['height'] = isset( $thumb_size[1] ) ? $thumb_size[1] : '';
				$thumbnail_args['crop']   = isset( $thumb_size[2] ) ? $thumb_size[2] : '';
			} else {
				$thumbnail_args['size']   = $thumb_size;
			}

		}

		$slider_args['thumbnail_args'] = $thumbnail_args;

		$slider = wpex_get_post_media_gallery_slider( $this->post_id, $slider_args );

		$slider = apply_filters( 'wpex_card_gallery_slider', $slider, $this, $args );

		if ( $slider ) {
			return $before . $slider . $after;
		}

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Video ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card video.
	 *
	 * @since 5.0
	 */
	public function get_video( $args = array() ) {

		$default_args = array(
			'class' => '',
		);

		$args = wp_parse_args( $args, $default_args );

		if ( $this->post_id ) {
			$video = wpex_get_post_video( $this->post_id );
			if ( $video ) {
				$video = wpex_get_post_video_html();
			}
		}

		$video = apply_filters( 'wpex_card_video', $video, $this, $args );

		if ( empty( $video ) ) {
			return;
		}

		$args['name']             = 'video';
		$args['content']          = $video;
		$args['sanitize_content'] = false;
		$args['link']             = false;

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Audio ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card audio.
	 *
	 * @since 5.0
	 */
	public function get_audio( $args = array() ) {

		$default_args = array(
			'class' => '',
		);

		$args = wp_parse_args( $args, $default_args );

		if ( $this->post_id ) {
			$audio = wpex_get_post_audio( $this->post_id );
			if ( $audio ) {
				$audio = wpex_get_post_audio_html( $audio );
			}
		}

		$audio = apply_filters( 'wpex_card_audio', $audio, $this, $args );

		if ( empty( $audio ) ) {
			return;
		}

		$args['name']             = 'audio';
		$args['content']          = $audio;
		$args['sanitize_content'] = false;
		$args['link']             = false;

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Title ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card title.
	 *
	 * @since 5.0
	 */
	public function get_title( $args = array() ) {

		$default_args = array(
			'content'    => '',
			'link'       => true,
			'class'      => '',
			'show_count' => false,
		);

		$args = apply_filters( 'wpex_card_title_args', wp_parse_args( $args, $default_args ), $this );

		$title = '';

		if ( ! empty( $this->args['title'] ) ) {
			$title = $this->args['title'];
		} elseif ( ! empty( $args['content'] ) ) {
			$title = $args['content'];
		} elseif ( $this->post_id ) {
			$title = get_the_title( $this->post_id );
		}

		$title = apply_filters( 'wpex_card_title', $title, $this, $args );

		if ( empty( $title ) ) {
			return;
		}

		if ( true === $args['show_count'] ) {
			$title = $this->get_var( 'running_count' ) . '. ' . $title;
		}

		$args['name']    = 'title';
		$args['content'] = $title;

		return $this->get_element( $args );

	}

	/**
	 * Get card title tag.
	 *
	 * @since 5.0
	 */
	public function get_title_tag() {

		if ( ! empty( $this->args['title_tag'] ) ) {
			$tag = $this->args['title_tag'];
		} else {
			$tag = ( 'related' === wpex_get_loop_instance() ) ? 'h4' : 'h2';
		}

		$this->title_tag = tag_escape( apply_filters( 'wpex_card_title_tag', $tag, $this ) );

		return $this->title_tag;

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Excerpt ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card excerpt.
	 *
	 * @since 5.0
	 */
	public function get_excerpt( $args = array() ) {

		$default_args = array(
			'link'   => false, // disabled by default but it's possible to add.
			'class'  => '',
			'length' => $this->is_featured() ? 40 : 20,
		);

		$args = apply_filters( 'wpex_card_excerpt_args', wp_parse_args( $args, $default_args ), $this );

		if ( isset( $this->args['excerpt_length'] ) ) {
			$excerpt_length = $this->args['excerpt_length'];
		} else {
			$excerpt_length = $args['length'];
		}

		$excerpt = '';

		if ( $excerpt_length && '0' !== $excerpt_length ) {

			if ( ! empty( $this->args['excerpt'] ) ) {
				$excerpt = $this->args['excerpt'];
			} elseif ( $this->post_id ) {

				$excerpt = get_post_meta( $this->post_id, 'wpex_card_excerpt', true );

				if ( empty( $excerpt ) ) {

					if ( function_exists( 'wpex_get_excerpt' ) ) {

						$excerpt_args = apply_filters( 'wpex_card_excerpt_args', array(
							'post_id'           => $this->post_id,
							'length'            => $excerpt_length,
							'excerpt_more_link' => false,
						), $this );

						$excerpt = wpex_get_excerpt( $excerpt_args );

					} else {
						$excerpt = get_the_excerpt( $this->post_id );
					}

				}

			}

		}

		$excerpt = apply_filters( 'wpex_card_excerpt', $excerpt, $this, $args );

		if ( empty( $excerpt ) ) {
			return;
		}

		$args['name']    = 'excerpt';
		$args['content'] = $excerpt;

		unset( $args['length'] );

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ More Link ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card more link.
	 *
	 * @since 5.0
	 */
	public function get_more_link( $args = array() ) {

		if ( ! $this->has_link() ) {
			return;
		}

		if ( isset( $this->args['excerpt_length'] ) && '-1' == $this->args['excerpt_length'] ) {
			return;
		}

		$default_args = array(
			'class'      => '',
			'text'       => '',
			'link_class' => '',
		);

		$args = apply_filters( 'wpex_card_more_link_args', wp_parse_args( $args, $default_args ), $this );

		if ( isset( $this->args['more_link_text'] ) ) {
			$args['text'] = $this->args['more_link_text'];
		}

		$more_link_text = apply_filters( 'wpex_card_more_link_text', $args['text'], $this, $args );

		if ( ! empty( $more_link_text ) ) {

			$args['name']    = 'more-link';
			$args['link']    = true; // always has a link
			$args['content'] = $more_link_text;

			return $this->get_element( $args );

		}

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Date ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card date.
	 *
	 * @since 5.0
	 */
	public function get_date( $args = array() ) {

		$default_args = array(
			'link'   => false,
			'type'   => 'published',
			'format' => '',
		);

		$args = apply_filters( 'wpex_card_date_args', wp_parse_args( $args, $default_args ), $this );

		$date = '';

		if ( isset( $this->args['date'] ) ) {
			$date = $this->args['date'];
		} elseif ( $this->post_id ) {

			switch ( $args['type'] ) {
				case 'time_ago':
					$date = human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' '. esc_html__( 'ago', 'total' );
					break;
				case 'modified':
					$date = get_the_modified_date( $args['format'], $this->post_id );
					break;
				default:
					$date = get_the_date( $args['format'], $this->post_id );
					break;
			}

			if ( 'tribe_events' == $this->get_post_type() && function_exists( 'tribe_get_start_date' ) ) {
				$display_time = false;
				$date = tribe_get_start_date( $this->post_id, $display_time, $args['format'] );
			}

		}

		$date = apply_filters( 'wpex_card_date', $date, $this, $args );

		if ( empty( $date ) ) {
			return;
		}

		$args['name']    = 'date';
		$args['content'] = $date;

		unset( $args['type'] );
		unset( $args['format'] );

		return $this->get_element( $args );

	}

	/**
	 * Get card time.
	 *
	 * @since 5.0
	 */
	public function get_time( $args = array() ) {

		$default_args = array(
			'link'   => false,
			'type'   => 'published',
			'format' => '',
		);

		$args = apply_filters( 'wpex_card_time_args', wp_parse_args( $args, $default_args ), $this );

		$time = '';

		if ( isset( $this->args['time'] ) ) {
			$time = $this->args['time'];
		} elseif ( $this->post_id ) {

			switch ( $args['type'] ) {
				case 'modified':
					$time = get_the_modified_time( $args['format'], $this->post_id );
					break;
				default:
					$time = get_the_time( $args['format'], $this->post_id );
					break;
			}

			if ( 'tribe_events' == $this->get_post_type() ) {
				$time = '';
			}

		}

		$time = apply_filters( 'wpex_card_time', $time, $this, $args );

		if ( empty( $time ) ) {
			return;
		}

		$args['name']    = 'time';
		$args['content'] = $time;

		unset( $args['type'] );
		unset( $args['format'] );

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Author ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card author.
	 *
	 * @since 5.0
	 */
	public function get_author( $args = array() ) {

		$default_args = array(
			'link'       => true,
			'link_class' => '',
			'class'      => '',
		);

		$args = apply_filters( 'wpex_card_author_args', wp_parse_args( $args, $default_args ), $this );

		$author = '';

		if ( isset( $this->args['author'] ) ) {
			$author = $this->args['author'];
		} elseif ( $this->post_id ) {

			$post = get_post( $this->post_id );

			$authordata = get_userdata( $post->post_author );

			$the_author = apply_filters( 'the_author', is_object( $authordata ) ? $authordata->display_name : null );

			if ( $the_author ) {

				if ( true === $args['link'] ) {
					$author_posts_url = get_author_posts_url( $post->post_author );
				}

				if ( ! empty( $author_posts_url ) ) {

					$link_attrs = array(
						'href'  => esc_url( $author_posts_url ),
						'class' => $args['link_class'],
					);

					$author .= '<a ' . trim( wpex_parse_attrs( $link_attrs ) ) . '>';
				}

				$author .= esc_html( ucwords( $the_author ) );

				if ( ! empty( $author_posts_url ) ) {
					$author .= '</a>';
				}

			}

		}

		$author = apply_filters( 'wpex_card_author', $author, $this, $args );

		if ( empty( $author ) ) {
			return;
		}

		$args['name']             = 'author';
		$args['content']          = $author;
		$args['sanitize_content'] = false;
		$args['link']             = false; // important!!

		unset( $args['link_class'] );

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Terms ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card terms list.
	 *
	 * @since 5.0
	 */
	public function get_terms_list( $args = array() ) {

		$default_args = array(
			'class'      => '',
			'term_class' => '',
			'separator'  => ' ',
			'taxonomy'   => '',
		);

		$args = apply_filters( 'wpex_card_terms_list_args', wp_parse_args( $args, $default_args ), $this );

		$terms = $this->get_terms( $args );

		if ( empty( $terms ) ) {
			return false;
		}

		$links = array();

		foreach ( $terms as $term ) {
			$term_link = get_term_link( $term );
			if ( ! is_wp_error( $term_link ) ) {
				$link = '<a href="' . esc_url( $term_link ) . '"';
					if ( $args['term_class'] ) {
						$link .= ' class="' . esc_attr( $args['term_class'] ) . '"';
					}
				$link .= '>';
				$link .= esc_html( $term->name ) . '</a>';
				$links[] = $link;
			}
		}

		$terms_list = apply_filters( 'wpex_card_terms_list', join( $args['separator'], $links ), $this, $args );

		if ( empty( $terms_list ) ) {
			return;
		}

		$args['name']             = 'terms-list';
		$args['content']          = $terms_list;
		$args['sanitize_content'] = false;
		$args['link']             = false; // important!!!

		unset( $args['term_class'] );
		unset( $args['separator'] );
		unset( $args['taxonomy'] );

		return $this->get_element( $args );


	}

	/**
	 * Get card terms.
	 *
	 * @since 5.0
	 */
	public function get_terms( $args = array() ) {

		$default_args = array(
			'taxonomy' => '',
		);

		$args = wp_parse_args( $args, $default_args );

		$terms = '';

		if ( $this->post_id ) {

			if ( empty( $args['taxonomy'] ) ) {
				if ( function_exists( 'wpex_get_post_primary_taxonomy' ) ) {
					$args['taxonomy'] = wpex_get_post_primary_taxonomy( $this->post_id );
				} else {
					$args['taxonomy'] = 'category';
				}
			}

			$get_terms = get_the_terms( $this->post_id, $args['taxonomy'] );

			if ( ! empty( $get_terms ) && ! is_wp_error( $get_terms ) ) {
				$terms = $get_terms;
			}

		}

		return apply_filters( 'wpex_card_terms', $terms, $this );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Primary Term ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card primary term.
	 *
	 * @since 5.0
	 */
	public function get_primary_term( $args = array() ) {

		$default_args = array(
			'link'       => true,
			'class'      => '',
			'term_class' => '', // @todo add?
		);

		$args = apply_filters( 'wpex_card_primary_term_args', wp_parse_args( $args, $default_args ), $this );

		$primary_term = '';

		if ( isset( $this->args['primary_term'] ) ) {
			$terms = $this->args['primary_term'];
		} elseif ( $this->post_id ) {

			if ( function_exists( 'wpex_get_post_primary_term' ) ) {
				$primary_term = wpex_get_post_primary_term( $this->post_id );
			}

			if ( ! $primary_term ) {
				$get_terms = $this->get_terms( $args );
				if ( $get_terms ) {
					$primary_term = $get_terms[0];
				}
			}

			$primary_term = apply_filters( 'wpex_card_primary_term', $primary_term, $this, $args );

			if ( $primary_term ) {

				if ( $args['link'] ) {

					$link_attrs = array(
						'href' => esc_url( get_term_link( $primary_term ) ),
						'class' => $args['term_class'],
					);

					$primary_term_out = '<a ' . trim( wpex_parse_attrs( $link_attrs) ) . '>' . esc_html( $primary_term->name ) . '</a>';

				} else {

					if ( $args['term_class'] ) {
						$primary_term_out = '<span class="' . esc_attr( $args['term_class'] ) . '">' . esc_html( $primary_term->name ) . '</span>';
					} else {
						$primary_term_out = esc_html( $primary_term->name );
					}

				}

			}

		}

		if ( empty( $primary_term_out ) ) {
			return;
		}

		$args['name']             = 'primary-term';
		$args['content']          = $primary_term_out;
		$args['sanitize_content'] = false;
		$args['link']             = false; // important!!

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Icon ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card icon.
	 *
	 * @since 5.0
	 */
	public function get_icon( $args = array() ) {

		$default_args = array(
			'class' => '',
			'icon'  => '',
			'size'  => '',
		);

		$args = apply_filters( 'wpex_card_icon_args', wp_parse_args( $args, $default_args ), $this );

		$icon = $args['icon'];

		if ( isset( $this->args['icon'] ) ) {
			$icon = $this->args['icon'];
		} elseif ( $this->post_id ) {
			$meta_icon = $this->get_card_meta( 'icon' );
			if ( ! empty( $meta_icon ) ) {
				$icon = $meta_icon;
			}
		}

		if ( ! empty( $icon ) && 'none' !== $icon && 'ticon ticon-none' !== $icon ) {
			$icon = '<span class="' . esc_attr( $icon ) . '" aria-hidden="true"></span>';
		} else {
			$icon = '';
		}

		$icon = apply_filters( 'wpex_card_icon', $icon, $this, $args );

		if ( empty( $icon ) ) {
			return;
		}

		$args['name']             = 'icon';
		$args['content']          = $icon;
		$args['sanitize_content'] = false;

		unset( $args['icon'] ); // !!! important !!!

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Comments ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card comment count.
	 *
	 * @since 5.0
	 */
	public function get_comment_count( $args = array() ) {

		$default_args = array(
			'link'        => true,
			'number_only' => false,
		);

		$args = apply_filters( 'wpex_card_comment_count_args', wp_parse_args( $args, $default_args ), $this );

		$comment_count = '';

		if ( $this->post_id && comments_open( $this->post_id ) ) {

			if ( $args['link'] ) {
				ob_start();
					if ( $args['number_only'] ) {
						comments_popup_link( '0', '1', '%' );
					} else {
						comments_popup_link();
					}
				$comment_count = ob_get_clean();
			} else {
				$comment_count = get_comments_number( $this->post_id );
			}

		}

		$comment_count = apply_filters( 'wpex_card_comment_count', $comment_count, $this, $args );

		if ( empty( $comment_count ) ) {
			return;
		}

		$args['name']             = 'comment-count';
		$args['content']          = $comment_count;
		$args['sanitize_content'] = false;
		$args['link']             = false; // important!!

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Avatar ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card avatar.
	 *
	 * @since 5.0
	 */
	public function get_avatar( $args = array() ) {

		$default_args = array(
			'link'        => true,
			'size'        => '',
			'class'       => '',
			'image_class' => '',
		);

		$args = apply_filters( 'wpex_card_avatar_args', wp_parse_args( $args, $default_args ), $this );

		$avatar = '';


		if ( isset( $this->args['avatar'] ) ) {
			$avatar = $this->args['avatar'];
		} elseif ( $this->post_id ) {

			$post = get_post( $this->post_id );

			if ( true === $args['link'] ) {
				$author_posts_url = get_author_posts_url( $post->post_author );
			}

			if ( ! empty( $author_posts_url ) ) {
				$avatar .= '<a href="' . esc_url( $author_posts_url ) . '">';
			}

			$avatar .= get_avatar( $post->post_author, $args['size'], '', '', array(
				'class' => $args['image_class'],
			) );

			if ( ! empty( $author_posts_url ) ) {
				$avatar .= '</a>';
			}

		}

		$avatar = apply_filters( 'wpex_card_avatar', $avatar, $this, $args );

		if ( empty( $avatar ) ) {
			return;
		}

		$args['name']             = 'avatar';
		$args['content']          = $avatar;
		$args['sanitize_content'] = false;
		$args['link']             = false; // important!!

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Count ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card number.
	 *
	 * @since 5.0
	 */
	public function get_number( $args = array() ) {

		$default_args = array(
			'link'         => false,
			'class'        => '',
			'number'       => '',
			'prepend_zero' => false,
		);

		$args = apply_filters( 'wpex_card_number_args', wp_parse_args( $args, $default_args ), $this );

		$number = $args['number'];

		if ( isset( $this->args['number'] ) ) {
			$number = $this->args['number'];
		} elseif ( ! empty( $this->post_id ) ) {
			$number = $this->get_card_meta( 'number', true );
		}

		if ( empty( $number ) ) {
			$number = $this->get_var( 'running_count' );
		}

		if ( true == $args['prepend_zero'] ) {
			$number = sprintf( '%02d', $number );
		}

		$number = apply_filters( 'wpex_card_number', $number, $this, $args );

		if ( empty( $number ) ) {
			return;
		}

		$args['name']    = 'number';
		$args['content'] = $number;
		unset( $args['number'] );

		return $this->get_element( $args );

	}

	/**
	 * Get card count.
	 *
	 * @since 5.0
	 */
	public function get_running_count() {

		$running_count = get_query_var( 'wpex_loop_running_count' );

		if ( is_search() && is_paged() && in_the_loop() ) {
			$paged = absint( get_query_var( 'paged' ) );
			if ( $paged > 1 ) {
				$posts_per_page = absint( get_query_var( 'posts_per_page' ) );
				if ( $posts_per_page ) {
					$args['number'] = absint( $args['number'] ) + $posts_per_page * ( $paged - 1 );
				}
			}
		}

		$running_count = apply_filters( 'wpex_card_running_count', $running_count, $this );

		$this->running_count = absint( $running_count );

		return $this->running_count;

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Rating ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card rating.
	 *
	 * @since 5.0
	 */
	public function get_rating() {

		$rating = '';

		if ( ! empty( $this->args['rating'] ) ) {
			$rating = $this->args['rating'];
		} elseif ( $this->post_id ) {
			$rating = get_post_meta( $this->post_id, 'wpex_post_rating', true );
			if ( empty( $rating ) ) {
				$rating = $this->get_card_meta( 'rating', true );
			}
		}

		if ( empty( $rating ) && function_exists( 'wc_get_product' ) && 'product' == $this->get_post_type() ) {
			$product = wc_get_product( $this->post_id );
			if ( $product ) {
				$rating = $product->get_average_rating();
			}
		}

		$rating = apply_filters( 'wpex_card_rating', $rating );

		$this->rating = floatval( $rating );

		return $this->rating;

	}

	/**
	 * Get card rating.
	 *
	 * @since 5.0
	 */
	public function get_star_rating( $args = array() ) {

		$default_args = array(
			'link'   => false,
			'class'  => '',
		);

		$args = apply_filters( 'wpex_card_star_rating_args', wp_parse_args( $args, $default_args ), $this );

		$star_rating = '';

		$rating = $this->get_rating();

		if ( $rating ) {
			$star_rating = wpex_get_star_rating( $rating );
		}

		$star_rating = apply_filters( 'wpex_card_star_rating', $star_rating, $this, $args );

		if ( empty( $star_rating ) ) {
			return;
		}

		$args['name']             = 'star-rating';
		$args['content']          = $star_rating;
		$args['sanitize_content'] = false; // already sanitized

		return $this->get_element( $args );

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Products ]
	/*-------------------------------------------------------------------------------*/

	/**
	 * Get card product price.
	 *
	 * @since 5.0
	 */
	public function get_price( $args = array() ) {

		$default_args = array(
			'link'  => false,
			'class' => '',
		);

		$args = apply_filters( 'wpex_card_price_args', wp_parse_args( $args, $default_args ), $this );

		$price = '';

		if ( ! empty( $this->args['price'] ) ) {
			$price = $this->args['price'];
		} elseif ( $this->post_id ) {

			$price = get_card_meta( 'price', true );

			if ( empty( $price ) ) {

				$post_type = get_post_type( $this->post_id );

				switch ( $post_type ) {
					case 'product':

						// Get WooCommerce price
						if ( function_exists( 'wc_get_product' ) ) {
							$product = wc_get_product( $this->post_id );
							if ( $product ) {
								$price = $product->get_price_html();
							}
						}

						break;
				}

			}

		}

		$price = apply_filters( 'wpex_card_price', $price, $this, $args );

		if ( empty( $price ) ) {
			return;
		}

		$args['name']             = 'price';
		$args['content']          = $price;
		$args['sanitize_content'] = false; // already sanitized

		return $this->get_element( $args );

	}

	/**
	 * Get card sale flash.
	 *
	 * @since 5.0
	 */
	public function get_sale_flash( $args = array() ) {

		$default_args = array(
			'link'  => false,
			'class' => '',
			'text'  => esc_html( 'Sale', 'total' ),
		);

		$args = apply_filters( 'wpex_card_sale_flash_args', wp_parse_args( $args, $default_args ), $this );

		$sale_flash = '';

		if ( ! empty( $this->args['sale_flash'] ) ) {
			$sale_flash = $this->args['sale_flash'];
		} elseif ( $this->post_id ) {

			$post_type = get_post_type( $this->post_id );

			switch ( $post_type ) {
				case 'product':

					// Get WooCommerce price
					if ( function_exists( 'wc_get_product' ) ) {
						$product = wc_get_product( $this->post_id );
						if ( $product && $product->is_on_sale() ) {
							$sale_flash = $args['text'];
						}
					}

					break;
			}

		}

		$sale_flash = apply_filters( 'wpex_card_sale_flash', $sale_flash, $this, $args );

		if ( empty( $sale_flash ) ) {
			return;
		}

		$args['name']    = 'sale-flash';
		$args['content'] = $sale_flash;

		unset( $args['text'] );

		return $this->get_element( $args );

	}

}