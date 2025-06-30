<?php
/**
 * Author box data
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.5
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns post author data for use with author box.
 *
 * @since 5.0
 */
function wpex_get_author_box_data( $post = null ) {

	if ( ! $post ) {
		global $post;
	}

	if ( ! empty( $post ) ) {

		$authordata = get_userdata( $post->post_author );

		$data = array(
			'post_author' => $post->post_author,
			'avatar_size' => apply_filters( 'wpex_author_bio_avatar_size', get_theme_mod( 'author_box_avatar_size', 70 ) ),
			'author_name' => apply_filters( 'the_author', is_object( $authordata ) ? $authordata->display_name : null ),
			'posts_url'   => get_author_posts_url( $post->post_author ),
			'description' => get_the_author_meta( 'description', $post->post_author ),
		);

		if ( ( isset( $data['avatar_size'] ) && 0 !== $data['avatar_size'] ) ) {

			$avatar_class = 'wpex-align-middle';

			$avatar_border_radius = get_theme_mod( 'author_box_avatar_border_radius' );
			$avatar_border_radius = $avatar_border_radius ? $avatar_border_radius : 'round';

			$avatar_class .= ' wpex-' . sanitize_html_class( $avatar_border_radius );

			$data['avatar'] = get_avatar( $post->post_author, $data['avatar_size'], '', '', array(
				'class' => $avatar_class
			) );

		} else {

			$data['avatar'] = ''; // important

		}

	}

	$data = apply_filters( 'wpex_post_author_bio_data', $data, $post ); // @todo deprecate this filter

	return apply_filters( 'wpex_author_box_data', $data, $post );

}

/**
 * Display author box social links.
 *
 * @since 5.0
 */
function wpex_author_box_social_links( $post_author = '' ) {

	$social_btn_style = get_theme_mod( 'author_box_social_style', 'flat-color-round' );
	$social_btn_classes = wpex_get_social_button_class( $social_btn_style );
	$social_btn_classes .= ' wpex-inline-block wpex-mr-5';
	wpex_user_social_links( array(
		'user_id'         => $post_author,
		'display'         => 'icons',
		'before'          => '<div class="author-bio-social wpex-mb-15">',
		'after'           => '</div>',
		'link_attributes' => array(
			'class' => $social_btn_classes
		),
	) );

}
