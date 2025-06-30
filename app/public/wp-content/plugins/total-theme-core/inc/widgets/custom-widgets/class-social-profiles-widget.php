<?php
/**
 * Social Profiles Widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

use WP_Widget;

defined( 'ABSPATH' ) || exit;

class SocialProfilesWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( function_exists( 'wpex_get_theme_branding' ) ) {
			$branding = wpex_get_theme_branding();
			$branding = $branding ? $branding . ' - ' : '';
		} else {
			$branding = 'Total - ';
		}

		parent::__construct(
			'wpex_fontawesome_social_widget',
			$branding . esc_html__( 'Social Links', 'total-theme-core' ),
			array(
				'description' => esc_html__( 'Displays your social profile links via retina ready font icons with many different styles to choose from (recommended). ', 'total-theme-core' ),
				'customize_selective_refresh' => true,
			)
		);

	}

	/**
	 * Returns social options.
	 *
	 * @since 1.0.0
	 */
	public function get_social_options() {
		return apply_filters( 'wpex_social_widget_profiles', array(
			'twitter' => array(
				'name' => 'Twitter',
				'url'  => '',
			),
			'facebook' => array(
				'name' => 'Facebook',
				'url'  => '',
			),
			'instagram' => array(
				'name' => 'Instagram',
				'url'  => '',
			),
			'linkedin' => array(
				'name' => 'LinkedIn',
				'url'  => '',
			),
			'pinterest' => array(
				'name' => 'Pinterest',
				'url'  => '',
			),
			'etsy' => array(
				'name' => 'Etsy',
				'url'  => '',
			),
			'yelp' => array(
				'name' => 'Yelp',
				'url'  => '',
			),
			'tripadvisor' => array(
				'name' => 'Tripadvisor',
				'url'  => '',
			),
			'dribbble' => array(
				'name' => 'Dribbble',
				'url'  => '',
			),
			'flickr' => array(
				'name' => 'Flickr',
				'url'  => '',
			),
			'vk' => array(
				'name' => 'VK',
				'url'  => '',
			),
			'github' => array(
				'name' => 'GitHub',
				'url'  => '',
			),
			'tumblr' => array(
				'name' => 'Tumblr',
				'url'  => '',
			),
			'skype' => array(
				'name' => 'Skype',
				'url'  => '',
			),
			'whatsapp' => array(
				'name' => 'Whatsapp',
				'url' => '',
			),
			'trello' => array(
				'name' => 'Trello',
				'url'  => '',
			),
			'foursquare' => array(
				'name' => 'Foursquare',
				'url'  => '',
			),
			'renren' => array(
				'name' => 'RenRen',
				'url'  => '',
			),
			'xing' => array(
				'name' => 'Xing',
				'url'  => '',
			),
			'vimeo-square' => array(
				'name' => 'Vimeo',
				'url'  => '',
			),
			'vine' => array(
				'name' => 'Vine',
				'url'  => '',
			),
			'youtube' => array(
				'name' => 'Youtube',
				'url'  => '',
			),
			'twitch' => array(
				'name' => 'Twitch',
				'url'  => '',
			),
			'houzz' => array(
				'name' => 'Houzz',
				'url' => '',
			),
			'spotify' => array(
				'name' => 'Spotify',
				'url' => '',
			),
			'rss' => array(
				'name' => 'RSS',
				'url'  => '',
			),
		) );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @since 1.0.0
	 *
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// Get social services and
		$social_services = isset( $instance['social_services'] ) ? $instance['social_services'] : '';

		// Return if no services defined
		if ( ! $social_services ) {
			return;
		}

		// Define vars
		$output        = '';
		$title         = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$description   = isset( $instance['description'] ) ? $instance['description'] : '';
		$style         = isset( $instance['style'] ) ? $instance['style'] : '';
		$type          = isset( $instance['type'] ) ? $instance['type'] : '';
		$target        = isset( $instance['target'] ) ? $instance['target'] : '';
		$size          = isset( $instance['size'] ) ? intval( $instance['size'] ) : '';
		$font_size     = isset( $instance['font_size'] ) ? $instance['font_size'] : '';
		$border_radius = isset( $instance['border_radius'] ) ? $instance['border_radius'] : '';
		$align         = isset( $instance['align'] ) ? $instance['align'] : '';
		$nofollow      = isset( $instance['nofollow'] ) ? $instance['nofollow'] : false;
		$expand       = isset( $instance['expand'] ) ? $instance['expand'] : false;

		// Parse style
		$style = $this->parse_style( $style, $type ); // Fallback for OLD styles pre-3.0.0

		// Sanitize vars
		$size          = $size ? sanitize_data( $size, 'px' ) : '';
		$font_size     = $font_size ? sanitize_data( $font_size, 'font_size' ) : '';
		$border_radius = $border_radius ? sanitize_data( $border_radius, 'border_radius' ) : '';

		// Wrapper style
		$ul_style = '';
		if ( $font_size ) {
			$ul_style .= ' style="font-size:' . esc_attr( $font_size ) . ';"';
		}

		// Inline style
		$add_style = '';
		if ( $size ) {
			$add_style .= 'height:' . esc_attr( $size ) . ';';
			if ( ! $expand ) {
				$add_style .= 'width:' . esc_attr( $size ) . ';';
			}
			$add_style .= 'line-height:' . esc_attr( $size ) . ';';
		}
		if ( $border_radius ) {
			$add_style .= 'border-radius:' . esc_attr( $border_radius ) . ';';
		}
		if ( $add_style ) {
			$add_style = $add_style;
		}

		// Before widget hook
		$output .= $args['before_widget'];

		// Display title
		if ( $title ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}

		// Get align class
		$align = ( $align && in_array( $align, array( 'left', 'center', 'right' ) ) ) ? ' text' . $align : '';

		// Begin widget output
		$output .= '<div class="wpex-fa-social-widget' . esc_attr( $align ) . '">';

			// Description
			if ( $description ) :

				$output .= '<div class="desc wpex-last-mb-0 wpex-mb-20 wpex-clr">';

					$output .= wp_kses_post( $description );

				$output .= '</div>';

			endif;

			$ul_class = 'wpex-list-none wpex-m-0 wpex-last-mr-0 wpex-text-md';

			if ( $expand ) {
				$ul_class .= ' wpex-flex';
			}

			$output .= '<ul class="' . esc_attr( $ul_class ) . '" ' . $ul_style . '>';

				// Original Array
				$get_social_options = $this->get_social_options();

				// Loop through each item in the array
				foreach( $social_services as $key => $val ) :

					$link = ! empty( $social_services[$key]['url'] ) ? do_shortcode( $social_services[$key]['url'] ) : null;

					if ( $link ) {

						if ( empty( $get_social_options[$key] ) ) {
							continue;
						}

						$name     = $get_social_options[$key]['name'];
						$nofollow = ( $nofollow || isset( $get_social_options[$key]['nofollow'] ) ) ? 'nofollow' : '';

						$a_attrs = array(
							'href'   => esc_url( $link ),
							'title'  => esc_attr( $name ),
							'class'  => 'wpex-' . sanitize_html_class( $key ),
							'rel'    => $nofollow,
							'target' => $target,
							'style'  => $add_style,
						);

						if ( function_exists( 'wpex_get_social_button_class' ) ) {
							$a_attrs['class'] .= ' ' . esc_attr( wpex_get_social_button_class( $style ) );
						}

						$key  = 'vimeo-square' == $key ? 'vimeo' : $key;
						$icon = 'youtube'      == $key ? 'youtube-play' : $key;
						$icon = 'bloglovin'    == $key ? 'heart' : $icon;
						$icon = 'vimeo-square' == $key ? 'vimeo' : $icon;

						$li_class = 'wpex-inline-block wpex-mb-5 wpex-mr-5';

						if ( $expand ) {
							$li_class .= ' wpex-flex-grow';
							$a_attrs['class'] .= ' wpex-w-100';
						}

						$output .= '<li class="' . esc_attr ( $li_class ) . '">';

							$output .= '<a';

								if ( function_exists( 'wpex_parse_attrs' ) ) {
									$output .= ' ' . wpex_parse_attrs( $a_attrs );
								} else {

									foreach ( $a_attrs as $attr_k => $attr_v ) {
										$output .= ' ' . $attr_k . '=' . '"' . esc_attr( $attr_v ) . '"';
									}

								}

							$output .= '>';

								$output .= '<span class="ticon ticon-' . esc_attr( $icon ) . '" aria-hidden="true"></span>';

								if ( ! defined( 'TOTAL_THEME_ACTIVE' ) ) {
									$output .= '<span class="ttc-social-widget-label">' . esc_html( $name ) . '</span>';
								} else {
									$output .= '<span class="screen-reader-text">' . esc_html( $name ) . '</span>';
								}

							$output .= '</a>';

						$output .= '</li>';

					}

				endforeach;

			$output .= '</ul>';

		$output .= '</div>';

		// After widget hook
		$output .= $args['after_widget'];

		// Echo output
		echo $output;

	}

	/**
	 * Parses style attribute for fallback styles
	 *
	 * @since 3.0.0
	 */
	public function parse_style( $style = '', $type = '' ) {
		if ( 'color' == $style && 'flat' == $type ) {
			return 'flat-color';
		}
		if ( 'color' == $style && 'graphical' == $type ) {
			return 'graphical-rounded';
		}
		if ( 'black' == $style && 'flat' == $type ) {
			return 'black-rounded';
		}
		if ( 'black' == $style && 'graphical' == $type ) {
			return 'black-rounded';
		}
		if ( 'black-color-hover' == $style && 'flat' == $type ) {
			return 'black-ch-rounded';
		}
		if ( 'black-color-hover' == $style && 'graphical' == $type ) {
			return 'black-ch-rounded';
		}
		return $style;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 * @since 1.0.0
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new, $old ) {

		// Sanitize data
		$instance = $old;
		$instance['title']           = ! empty( $new['title'] ) ? strip_tags( $new['title'] ) : null;
		$instance['description']     = ! empty( $new['description'] ) ? $new['description'] : null;
		$instance['style']           = ! empty( $new['style'] ) ? strip_tags( $new['style'] ) : 'flat-color';
		$instance['target']          = ! empty( $new['target'] ) ? strip_tags( $new['target'] ) : 'blank';
		$instance['size']            = ! empty( $new['size'] ) ? strip_tags( $new['size'] ) : '';
		$instance['align']           = ! empty( $new['align'] ) ? strip_tags( $new['align'] ) : '';
		$instance['border_radius']   = ! empty( $new['border_radius'] ) ? strip_tags( $new['border_radius'] ) : '';
		$instance['font_size']       = ! empty( $new['font_size'] ) ? strip_tags( $new['font_size'] ) : '';
		$instance['nofollow']        = ! empty( $new['nofollow'] ) ? 'on' : '';
		$instance['expand']          = ! empty( $new['expand'] ) ? 'on' : '';
		$instance['social_services'] = $new['social_services'];

		// Remove deprecated param
		$instance['type'] = null;

		// Return instance
		return $instance;

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @since 1.0.0
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$instance = wp_parse_args( ( array ) $instance, array(
			'title'           => '',
			'description'     => '',
			'style'           => 'flat-color',
			'type'            => '',
			'font_size'       => '',
			'border_radius'   => '',
			'target'          => 'blank',
			'size'            => '',
			'social_services' => $this->get_social_options(),
			'align'           => 'left',
			'nofollow'        => '',
			'expand'          => '',
		) ); ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'total-theme-core' ); ?>:</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description','total-theme-core' ); ?>:</label>
			<textarea class="widefat" rows="5" cols="20" id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>"><?php echo sanitize_data( $instance['description'], 'html' ); ?></textarea>
		</p>

		<?php
		// Styles
		$social_styles = function_exists( 'wpex_social_button_styles' ) ? wpex_social_button_styles() : array();

		if ( $social_styles ) {

			// Parse style
			$style = $this->parse_style( $instance['style'], $instance['type'] ); ?>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>"><?php esc_html_e( 'Style', 'total-theme-core' ); ?>:</label>
				<br />
				<select class="wpex-widget-select" name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>">
					<?php foreach ( $social_styles as $key => $val ) { ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $style, $key ) ?>><?php echo strip_tags( $val ); ?></option>
					<?php } ?>
				</select>
			</p>

		<?php } ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>"><?php esc_html_e( 'Link Target', 'total-theme-core' ); ?>:</label>
			<br />
			<select class="wpex-widget-select" name="<?php echo esc_attr( $this->get_field_name( 'target' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>">
				<option value="blank" <?php selected( $instance['target'], 'blank' ) ?>><?php esc_html_e( 'Blank', 'total-theme-core' ); ?></option>
				<option value="self" <?php selected( $instance['target'], 'self' ) ?>><?php esc_html_e( 'Self', 'total-theme-core' ); ?></option>
			</select>
		</p>

		<p>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'nofollow' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'nofollow' ) ); ?>" type="checkbox" <?php checked( 'on', $instance['nofollow'], true ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'nofollow' ) ); ?>"><?php esc_html_e( 'Add nofollow attribute to links. ','total-theme-core' ); ?></label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'align' ) ); ?>"><?php esc_attr_e( 'Align', 'total-theme-core' ); ?>:</label>
			<br />
			<select class='wpex-widget-select' name="<?php echo esc_attr( $this->get_field_name( 'align' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'align' ) ); ?>">
				<option value="" <?php selected( $instance['align'], '' ); ?>><?php esc_attr_e( 'Default', 'total-theme-core' ); ?></option>
				<option value="left" <?php selected( $instance['align'], 'left' ); ?>><?php esc_attr_e( 'Left', 'total-theme-core' ); ?></option>
				<option value="center" <?php selected( $instance['align'], 'center' ); ?>><?php esc_attr_e( 'Center', 'total-theme-core' ); ?></option>
				<option value="right" <?php selected( $instance['align'], 'right' ); ?>><?php esc_attr_e( 'Right', 'total-theme-core' ); ?></option>
			</select>
		</p>

		<p>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'expand' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'expand' ) ); ?>" type="checkbox" <?php checked( 'on', $instance['expand'], true ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'expand' ) ); ?>"><?php esc_html_e( 'Expand items to fit the widget area?','total-theme-core' ); ?></label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>"><?php esc_html_e( 'Dimensions', 'total-theme-core' ); ?>:</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['size'] ); ?>" placeholder="40px" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>"><?php esc_html_e( 'Size', 'total-theme-core' ); ?>:</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'font_size' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['font_size'] ); ?>" placeholder="13px" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>"><?php esc_html_e( 'Border Radius', 'total-theme-core' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_radius' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['border_radius'] ); ?>" placeholder="4px" />
		</p>

		<?php
		$field_id_services   = $this->get_field_id( 'social_services' );
		$field_name_services = $this->get_field_name( 'social_services' ); ?>

		<label for="<?php echo esc_attr( $this->get_field_id( 'social_services' ) ); ?>"><?php esc_attr_e( 'Social Links', 'total-theme-core' ); ?>:</label>

		<small style="display:block;padding-top:5px;"><?php esc_html_e( 'You can click and drag & drop your items to re-order them. ', 'total-theme-core' ); ?></small>

		<ul id="<?php echo esc_attr( $field_id_services ); ?>" class="wpex-social-widget-services-list">
			<input type="hidden" id="<?php echo esc_attr( $field_name_services ); ?>" value="<?php echo esc_attr( $field_name_services ); ?>" class="wpex-social-widget-services-hidden-field" />
			<?php
			// Social array
			$get_social_options = $this->get_social_options();

			// Get current services display
			$display_services = isset ( $instance['social_services'] ) ? $instance['social_services'] : '';

			// Loop through social services to display inputs
			foreach( $display_services as $key => $val ) {

				if ( empty( $get_social_options[$key] ) ) {
					continue;
				}

				$url  = ! empty( $display_services[$key]['url'] ) ? $display_services[$key]['url'] : null;
				$name = $get_social_options[$key]['name'];

				// Set icon
				$icon = 'vimeo-square' == $key ? 'vimeo' : $key;
				$icon = 'youtube'      == $key ? 'youtube-play' : $icon;
				$icon = 'vimeo-square' == $key ? 'vimeo' : $icon; ?>

				<li id="<?php echo esc_attr( $field_id_services ); ?>_0<?php echo esc_attr( $key ); ?>">
					<p>
						<label for="<?php echo esc_attr( $field_id_services ); ?>-<?php echo esc_attr( $key ); ?>-name"><span class="ticon ticon-<?php echo esc_attr( $icon ); ?>"></span><?php echo strip_tags( $name ); ?>:</label>
						<input type="hidden" id="<?php echo esc_attr( $field_id_services ); ?>-<?php echo esc_attr( $key ); ?>-name" name="<?php echo esc_attr( $field_name_services . '[' .$key. '][name]' ); ?>" value="<?php echo esc_attr( $name ); ?>">
						<input type="text" id="<?php echo esc_attr( $field_id_services ); ?>-<?php echo esc_attr( $key ); ?>-url" name="<?php echo esc_attr( $field_name_services . '[' .$key. '][url]' ); ?>" value="<?php echo esc_attr( $url ); ?>" class="widefat" />
					</p>
				</li>

			<?php } ?>

		</ul>

	<?php
	}

}

register_widget( 'TotalThemeCore\SocialProfilesWidget' );