<?php
/**
 * vcex_post_meta shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_post_meta';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

$sections = $sections ? (array) vcex_vc_param_group_parse_atts( $sections ) : '';

if ( ! $sections ) {
	return;
}

global $post;

if ( ! $post ) {
	return;
}

$output = '';

// Use some fallbaks when previewing with Templatera
$is_templatera = ( 'templatera' == $post->post_type ) ? true : false;

// Classes
$wrap_classes = array(
	'vcex-post-meta',
	'meta',
);

switch ( $style ) {
	case 'vertical':
		$wrap_classes[] = 'meta-vertical';
		break;
}

if ( $color || 'vertical' == $style ) {
	$wrap_classes[] = 'wpex-child-inherit-color';
}

if ( $align ) {
	$wrap_classes[] = 'text' . sanitize_html_class( $align );
}

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $css ) {
	$wrap_classes[] = vcex_vc_shortcode_custom_css_class( $css );
}

if ( $visibility ) {
	$wrap_classes[] = sanitize_html_class( $visibility );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $el_class ) {
	$wrap_classes[] = vcex_get_extra_class( $el_class );
}

$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Inline CSS
$wrap_style_escaped = vcex_inline_style( array(
	'font_size' => $font_size,
	'color'     => $color,
) );

// Generate output
$output .= '<ul class="' . esc_attr( $wrap_classes ) . '"' . $wrap_style_escaped . '>';

	// Sections
	foreach ( $sections as $section ) {

		// Get section vars
		$type          = isset( $section['type'] ) ? $section['type'] : '';
		$label         = isset( $section['label'] ) ? $section['label'] : '';
		$icon_type     = isset( $section['icon_type'] ) ? $section['icon_type'] : '';
		$icon          = isset( $section['icon'] ) ? $section['icon'] : '';
		$icon_typicons = isset( $section['icon_typicons'] ) ? $section['icon_typicons'] : '';
		$icon_class    = vcex_get_icon_class( $section, 'icon' );
		$icon_out      = '';
		$label_out     = '';

		// Enqueue icon font family
		if ( $icon_class ) {
			vcex_enqueue_icon_font( $icon_type, $icon_class );
			$icon_out = '<span class="meta-icon ' . esc_attr( $icon_class ) . '" aria-hidden="true"></span>';
		}

		// Parse label
		if ( $label ) {

			$label_out = '<span class="meta-label">';

				$label_out .= wp_strip_all_tags( $label );

				if ( vcex_validate_boolean( $label_colon ) ) {
					$label_out .= ':';
				}

			$label_out .= '</span> ';

		}

		// Display sections
		switch ( $type ) {

			// Date
			case 'date':

				$output .= '<li class="meta-date">';

					if ( $icon_out ) {
						$output .= $icon_out;
					}

					if ( $label_out ) {
						$output .= $label_out;
					}

					$date_format = isset( $section['date_format'] ) ? $section['date_format'] : '';

					$output .= '<time datetime="' . esc_attr( get_the_date( 'Y-m-d' ) ) . '"' . vcex_get_schema_markup( 'publish_date' ) . '>' . get_the_date( $date_format, $post->ID ) . '</time>';

				$output .= '</li>';

				break;

			// Author
			case 'author':

				$output .= '<li class="meta-author">';

					if ( $icon_out ) {
						$output .= $icon_out;
					}

					if ( $label_out ) {
						$output .= $label_out;
					}

					$output .= '<span class="vcard author"' . vcex_get_schema_markup( 'author_name' ) . '><span class="fn"><a href="' . esc_url( get_author_posts_url( $post->post_author ) ) . '">' . get_the_author_meta( 'nickname', $post->post_author ) . '</a></span></span>';

				$output .= '</li>';

				break;

			// Comments
			case 'comments':

				$output .= '<li class="meta-comments comment-scroll">';

					if ( $icon_out ) {
						$output .= $icon_out;
					}

					if ( $label_out ) {
						$output .= $label_out;
					}

					$comment_number = get_comments_number();
					if ( $comment_number == 0 ) {
						$output .= esc_html__( '0 Comments', 'total' );
					} elseif ( $comment_number > 1 ) {
						$output .= $comment_number .' '. esc_html__( 'Comments', 'total' );
					} else {
						$output .= esc_html__( '1 Comment',  'total' );
					}

					$output .= '</li>';

				break;

			// Post terms
			case 'post_terms':

				$taxonomy = isset( $section['taxonomy'] ) ? $section['taxonomy'] : '';
				$get_terms    = '';

				if ( $is_templatera ) {

					$output .= '<li class="meta-post-terms wpex-clr">';

						if ( $icon_out ) {
							$output .= $icon_out;
						}

						if ( $label_out ) {
							$output .= $label_out;
						}

						$output .= '<a href="#">' . esc_html__( 'Sample Item', 'total' ) . '</a>';

					$output .= '</li>';

				} elseif ( $taxonomy ) {

					$get_terms = vcex_get_list_post_terms( $taxonomy, true );

					if ( $get_terms ) {

						$output .= '<li class="meta-post-terms wpex-clr">';

							if ( $icon_out ) {
								$output .= $icon_out;
							}

							if ( $label_out ) {
								$output .= $label_out;
							}

							$output .= $get_terms;

						$output .= '</li>';

					}


				}

				break;

			// Last updated
			case 'modified_date':

				$output .= '<li class="meta-modified-date">';

					if ( $icon_out ) {
						$output .= $icon_out;
					}

					if ( $label_out ) {
						$output .= $label_out;
					}

					$output .= '<time datetime="' . esc_attr( get_the_modified_date( 'Y-m-d' ) ) . '"' . vcex_get_schema_markup( 'date_modified' ) . '>' . get_the_modified_date( $date_format, $post->ID ) . '</time>';

				$output .= '</li>';

				break;

				// Callback
				case 'callback':

					$callback_function = isset( $section['callback_function'] ) ? $section['callback_function'] : '';

					if ( $callback_function && function_exists( $callback_function ) ) {

						$output .= '<li class="meta-callback">';

							if ( $icon_out ) {
								$output .= $icon_out;
							}

							if ( $label_out ) {
								$output .= $label_out;
							}

							$output .= wp_kses_post( call_user_func( $callback_function ) );

						$output .= '</li>';

					}

					break;


			// Default - see if the type is a callback function
			default:

				$custom_section_output = apply_filters( 'vcex_post_meta_custom_section_output', $type, $icon_class );

				if ( ! empty( $custom_section_output ) ) {
					$output .= $custom_section_output;
				}

				break;


		} // end switch

	} // end foreach

$output .= '</ul>';

// @codingStandardsIgnoreLine
echo $output;
