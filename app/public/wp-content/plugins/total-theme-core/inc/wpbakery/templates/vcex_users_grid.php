<?php
/**
 * vcex_users_grid shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_users_grid';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Extract shortcode attributes so they are easier to work with
extract( $atts );

// Declare and sanitize main vars
$output = '';
$avatar_size = ! empty( $avatar_size ) ? absint( $avatar_size ) : '150';

// Get user roles to query
$role__in = array();
if ( ! empty( $role__in ) ) {
	$role__in = preg_split( '/\,[\s]*/', $role__in );
}

// Query arguments
$args = apply_filters( 'vcex_users_grid_query_args', array(
	'order'    => $order,
	'orderby'  => $orderby,
	'role__in' => $role__in,
) );

// Get users
$users = get_users( $args );

// No users, lets bail completely!
if ( ! $users ) {
	return;
}

// Get onclick action (with fallback for old link_to_author_page param)
if ( isset( $link_to_author_page ) ) {
	if ( 'true' == $link_to_author_page ) {
		$onclick = 'author_page';
	} elseif ( 'false' == $link_to_author_page ) {
		$onclick = 'disable';
	}
}

// Display header if enabled
if ( $header ) {

	$output .= vcex_get_module_header( array(
		'style'   => $header_style,
		'content' => $header,
		'classes' => array( 'vcex-module-heading vcex_users_grid-heading' ),
	) );

}

// Wrap classes
$wrap_classes = array(
	'vcex-module',
	'vcex-users-grid',
	'wpex-row',
	'wpex-clr',
);

if ( 'masonry' == $grid_style ) {
	$wrap_classes[] = 'vcex-isotope-grid';
	vcex_enqueue_isotope_scripts();
}

if ( $columns_gap ) {
	$wrap_classes[] = 'gap-' . sanitize_html_class( $columns_gap );
}

if ( $visibility ) {
	$wrap_classes[] = sanitize_html_class( $visibility );
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Begin output
$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_get_unique_id( $unique_id ) . '>';

	// Define loop vars
	$first_run = true;
	$counter = 0;

	// Loop through users
	foreach ( $users as $user ) :

		$counter++;

		$author_link = ''; // Reset after each user

		// Get author description (check early so we can check later if the description should display or not)
		if ( 'true' == $description ) {
			$get_description = get_the_author_meta( 'description', $user->ID );
		}

		// Get entry classes
		if ( $first_run ) {

			$entry_classes = array(
				'vcex-users-grid-entry',
				'wpex-clr',
			);

			if ( 'masonry' == $grid_style ) {
				$entry_classes[] = 'vcex-isotope-entry';
			}

			$entry_classes[] = vcex_get_grid_column_class( $atts );

			if ( 'false' == $columns_responsive ) {
				$entry_classes[] = 'nr-col';
			} else {
				$entry_classes[] = 'col';
			}

			if ( $css_animation && 'none' !== $css_animation ) {
				$entry_classes[] = vcex_get_css_animation( $css_animation );
			}

			if ( $content_alignment ) {
				$entry_classes[] = 'text'. sanitize_html_class( $content_alignment );
			}

			$entry_classes[] = 'wpex-last-mb-0';

		}

		$entry_classes['counter'] = 'col-' . sanitize_html_class( $counter ) . '';

		// Befin entry output
		$output .= '<div class="' . esc_attr( implode( ' ', $entry_classes ) ) . '">';

			if ( $entry_css ) {

				$output .= '<div class="entry-css-wrap wpex-clr ' . esc_attr( vcex_vc_shortcode_custom_css_class( $entry_css ) ) .'">';

			}

			// Avatar
			if ( 'true' == $avatar ) {

				//$atts['media_type'] = 'thumbnail'; // users grid doesn't need to check for hover styles or overlays.

				if ( $first_run ) {

					$media_classes = array(
						'entry-media',
						'wpex-mb-20',
						'wpex-clr',
					);

					if ( $avatar_hover_style ) {
						$media_classes[] .= vcex_image_hover_classes( $avatar_hover_style );
					}

				}

				$output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_media_class( null, $shortcode_tag, $atts ) ) ) . '">';

					if ( 'disable' != $onclick ) {

						if ( 'author_page' == $onclick ) {
							$author_link = get_author_posts_url( $user->ID );
						} else if ( 'user_website' == $onclick ) {
							$author_link = $user->user_url;
						}

						if ( $author_link ) {

							$author_link = '<a href="' . esc_url( $author_link ) . '" title="' . esc_attr( $user->display_name ) . '">';

							$output .= $author_link;

						}

					}

					// Avatar image classes
					$avatar_classes = array(
						'wpex-align-middle',
					);

					if ( $avatar_border_radius ) {
						$avatar_classes[] = 'wpex-' . sanitize_html_class( $avatar_border_radius );
					}

					$avatar_args = apply_filters( 'vcex_users_grid_avatar_args', array(
						'class' => implode( ' ', $avatar_classes ),
					) );

					// Meta based avatar
					if ( ! empty( $avatar_meta_field ) ) {

						if ( $avatar = get_user_meta( $user->ID, $avatar_meta_field, true ) ) {

							if ( is_numeric( $avatar ) ) {

								$get_avatar = wp_get_attachment_image(
									$avatar,
									array( $avatar_size, $avatar_size ),
									false,
									$avatar_args
								);
							}

							if ( ! empty( $get_avatar ) ) {

								$output .= $get_avatar;

							} else {

								$output .= '<img src="' . esc_url( $avatar ) . '" alt="' . esc_attr( $user->display_name ) . '" />';

							}

						}

					}

					// Standard avatar
					else {

						$output .= get_avatar( $user->ID, $avatar_size, '', $user->display_name, $avatar_args );

					}

					if ( $author_link ) {

						$output .= '</a>';

					}

				$output .= '</div>';

			}

			/*--------------------------------*/
			/* [ Entry Content ]
			/*--------------------------------*/
			if ( 'true' == $name || ( 'true' == $description && ! empty( $get_description ) ) || 'true' == $social_links ) {

				// Open details el
				$output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_details_class( array( 'vcex-users-grid-details' ), $shortcode_tag, $atts ) ) ) . '">';

					// Display name
					if ( 'true' == $name ) {

						if ( $first_run ) {

							$name_tag_escaped = $name_heading_tag ? tag_escape( $name_heading_tag ) : 'div';

							$name_classes = 'entry-title wpex-clr';

							if ( $name_color && 'disable' != $onclick ) {
								$name_classes .= ' wpex-child-inherit-color';
							}

							$name_css = vcex_inline_style( array(
								'color'          => $name_color,
								'font_size'      => $name_font_size,
								'font_weight'    => $name_font_weight,
								'font_family'    => $name_font_family,
								'margin_bottom'  => $name_margin_bottom,
								'text_transform' => $name_text_transform,
							) );

						}

						$output .= '<' . $name_tag_escaped . ' class="' . esc_attr( $name_classes ) . '"' . $name_css . '>';

							if ( $author_link ) {

								$output .= $author_link;

							}

							$output .= $user->display_name;

							if ( $author_link ) {

								$output .= '</a>';

							}

						$output .= '</' . $name_tag_escaped . '>';

					}


					// Description
					if ( 'true' == $description && ! empty( $get_description ) ) {

						if ( $first_run ) {

							$description_css = vcex_inline_style( array(
								'color'          => $description_color,
								'font_size'      => $description_font_size,
								'font_weight'    => $description_font_weight,
								'font_family'    => $description_font_family,
							) );

						}

						$output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_excerpt_class( null, $shortcode_tag, $atts ) ) ) . '" '. $description_css .'>';

							$output .= wpautop( wp_kses_post( $get_description ) );

						$output .= '</div>';

					}

					// Display social
					if ( 'true' == $social_links ) {

						if ( $first_run ) {

							$social_links_inline_css = vcex_inline_style( array(
								'padding'   => $social_links_padding,
								'font_size' => $social_links_size,
							) );

							$social_links_style = vcex_get_social_button_class( $social_links_style );

						}

						$output .= '<div class="entry-social-links wpex-mt-15 wpex-last-mr-0 wpex-clr"' . $social_links_inline_css . '>';

							$output .= vcex_get_user_social_links( $user->ID, 'icons', array(
								'class' => array(
									$social_links_style,
									'wpex-mt-5',
									'wpex-mr-5'
								),
							) );

						$output .= '</div>';

					}

				$output .= '</div>'; // end entry-details

				if ( $entry_css ) {
					$output .= '</div>';
				}

			}

		$output .= '</div>'; // end entry

		// Clear counter
		if ( $counter == $columns ) {
			$counter = 0;
		}

		$first_run = false;

	// End loop
	endforeach;

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
