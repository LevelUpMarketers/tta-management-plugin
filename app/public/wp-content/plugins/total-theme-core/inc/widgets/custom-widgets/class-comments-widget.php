<?php
/**
 * Comments with Avatars widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class CommentsWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_recent_comments_avatars_widget',
			'name'    => $this->branding() . esc_html__( 'Comments With Avatars', 'total-theme-core' ),
			'options' => array(
				'customize_selective_refresh' => true,
			),
			'fields'  => array(
				array(
					'id'    => 'title',
					'label' => esc_html__( 'Title', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'      => 'number',
					'label'   => esc_html__( 'Number', 'total-theme-core' ),
					'type'    => 'number',
					'default' => 3,
				),
				array(
					'id'      => 'avatar_size',
					'label'   => esc_html__( 'Avatar Size', 'total-theme-core' ),
					'type'    => 'number',
					'default' => 50,
				),
				array(
					'id'          => 'excerpt_length',
					'label'       => esc_html__( 'Excerpt Length', 'total-theme-core' ),
					'type'        => 'number',
					'default'     => 10,
					'description' => esc_html__( 'Enter -1 to display the full content.', 'total-theme-core' ),
				),
				array(
					'id'      => 'avatar_border_radius',
					'label'   => esc_html__( 'Avatar Border Radius', 'total-theme-core' ),
					'type'    => 'select',
					'default' => 'round',
					'choices' => array(
						'round' => esc_html__( 'Round', 'total-theme-core' ),
						'semi-rounded' => esc_html__( 'Semiround', 'total-theme-core' ),
						'square'  => esc_html__( 'Square', 'total-theme-core' ),
					),
				),
				array(
					'id'    => 'items_center',
					'label' => esc_html__( 'Vertical Align?', 'total-theme-core' ),
					'type'  => 'checkbox',
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

		extract( $this->parse_instance( $instance ) );

		echo wp_kses_post( $args['before_widget'] );

		$this->widget_title( $args, $instance );

		$output = '';

		$output .= '<ul class="wpex-recent-comments-widget wpex-clr">';

		$comments = get_comments( array (
			'number'      => $number,
			'status'      => 'approve',
			'post_status' => 'publish',
			'type'        => 'comment',
		) );

		$avatar_size = ! empty( $avatar_size ) ? $avatar_size : 50;
		$excerpt_length = ! empty( $excerpt_length ) ? $excerpt_length : 10;

		if ( $comments ) {

			$arrow = is_rtl() ? '&larr;' : '&rarr;';

			$count = 0;

			foreach ( $comments as $comment ) {

				$count ++;

				// Get comment ID
				$comment_id   = $comment->comment_ID;
				$comment_link = get_permalink( $comment->comment_post_ID ) . '#comment-' . $comment_id;

				$li_classes = 'wpex-recent-comments-widget-li wpex-border-b wpex-border-solid wpex-border-main wpex-clr';

				if ( 1 === $count ) {
					$li_classes .= ' wpex-border-t';
				}

				$output .= '<li class="' . esc_attr( $li_classes ) . '">';

					$link_classes = 'wpex-inherit-color wpex-py-15 wpex-flex';

					if ( true === wp_validate_boolean( $items_center ) ) {
						$link_classes .= ' wpex-items-center';
					}

					$output .= '<a href="' . esc_url( $comment_link ) . '" title="' . esc_html__( 'view comment', 'total-theme-core' ) . '" class="' . esc_attr( $link_classes ) . '">';

						$output .= '<div class="wpex-recent-comments-widget-avatar wpex-flex-shrink-0 wpex-mr-15">';

							$output .= get_avatar( $comment->comment_author_email, $avatar_size, '', '', array(
								'class' => ( $avatar_border_radius && 'square' !== $avatar_border_radius ) ? 'wpex-' . sanitize_html_class( $avatar_border_radius ) : '',
							) );

						$output .= '</div>';

						$output .= '<div class="wpex-recent-comments-widget-details wpex-flex-grow">';

							$output .= '<strong>' . get_comment_author( $comment_id ) . ': </strong>';

							if ( '-1' === $excerpt_length ) {

								$output .= wp_strip_all_tags( $comment->comment_content );

							} else {

								$output .= wp_trim_words( $comment->comment_content, $excerpt_length, '&hellip;' );

							}

						$output .= '</div>';

					$output .= '</a>';

				$output .= '</li>';

			}

		// Display no comments notice
		} else {

			$output .= '<li>' . esc_html__( 'No comments yet.', 'total-theme-core' ) . '</li>';

		}

		$output .= '</ul>';

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\CommentsWidget' );