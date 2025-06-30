<?php
/**
 * Helper functions that return arrays for custom module mapping.
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return form styles.
 */
function vcex_get_theme_heading_styles() {
	if ( function_exists( 'wpex_get_theme_heading_styles' ) ) {
		return array_flip( wpex_get_theme_heading_styles() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Returns list of post types.
 */
function vcex_get_post_types() {
	$post_types_list = array();

	// Lets not do heavy lifting unless we are actually editing things
	if ( 'vc_edit_form' === vc_post_param( 'action' ) ) {
		$post_types = get_post_types( array(
			'public' => true
		) );
		if ( $post_types ) {
			foreach ( $post_types as $post_type ) {
				if ( 'revision' != $post_type && 'nav_menu_item' != $post_type && 'attachment' != $post_type ) {
					$post_types_list[$post_type] = $post_type;
				}
			}
		}
	}

	return $post_types_list;

}

/**
 * Return form styles.
 */
function vcex_get_form_styles() {
	if ( function_exists( 'wpex_get_form_styles' ) ) {
		return wpex_get_form_styles();
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Returns array of contact form 7 forms.
 */
function vcex_select_cf7_form( $settings = array() ) {
	if ( ! defined( 'WPCF7_VERSION' ) ) {
		return;
	}
	$defaults = array(
		'type' => 'vcex_cf7_select',
	);
	return wp_parse_args( $settings, $defaults );
}


/**
 * Returns array of image sizes for use in the Customizer.
 */
function vcex_image_sizes() {
	$sizes = array(
		__( 'Custom Size', 'total-theme-core' ) => 'wpex_custom',
	);
	$get_sizes = get_intermediate_image_sizes();
	array_unshift( $get_sizes, 'full' );
	$get_sizes = array_combine( $get_sizes, $get_sizes );
	$sizes     = array_merge( $sizes, $get_sizes );
	return $sizes;
}

/**
 * Array of Icon box styles.
 */
function vcex_icon_box_styles() {

	// Define array
	$array  = array(
		'one'   => esc_html__( 'Left Icon', 'total-theme-core' ),
		'seven' => esc_html__( 'Right Icon', 'total-theme-core' ),
		'two'   => esc_html__( 'Top Icon', 'total-theme-core' ),
		'four'  => esc_html__( 'Top Icon with Outline', 'total-theme-core' ),
		'five'  => esc_html__( 'Top Icon with Gray Background', 'total-theme-core' ),
		'six'   => esc_html__( 'Top Icon with Black Background', 'total-theme-core' ),
		'three' => esc_html__( 'Top Icon (legacy)', 'total-theme-core' ),
	);

	// Apply filters
	$array = apply_filters( 'vcex_icon_box_styles', $array );

	// Flip array around for use with VC
	$array = array_flip( $array );

	// Return array
	return $array;

}

/**
 * Array of orderby options.
 */
function vcex_orderby_array( $type = 'post' ) {
	$array = array(
		esc_html__( 'Default', 'total-theme-core' )            => '',
		esc_html__( 'Date', 'total-theme-core' )               => 'date',
		esc_html__( 'Title', 'total-theme-core' )              => 'title',
		esc_html__( 'Name', 'total-theme-core' )               => 'name',
		esc_html__( 'Modified', 'total-theme-core' )           => 'modified',
		esc_html__( 'Author', 'total-theme-core' )             => 'author',
		esc_html__( 'Random', 'total-theme-core' )             => 'rand',
		esc_html__( 'Parent', 'total-theme-core' )             => 'parent',
		esc_html__( 'Type', 'total-theme-core' )               => 'type',
		esc_html__( 'ID', 'total-theme-core' )                 => 'ID',
		esc_html__( 'Comment Count', 'total-theme-core' )      => 'comment_count',
		esc_html__( 'Menu Order', 'total-theme-core' )         => 'menu_order',
		esc_html__( 'Meta Key Value', 'total-theme-core' )     => 'meta_value',
		esc_html__( 'Meta Key Value Num', 'total-theme-core' ) => 'meta_value_num',
	);
	if ( 'woo_product' == $type ) {
		$array[ esc_html__( 'Best Selling', 'total-theme-core' ) ] = 'woo_best_selling';
		$array[ esc_html__( 'Top Rated', 'total-theme-core' ) ]    = 'woo_top_rated';
	}
	return apply_filters( 'vcex_orderby', $array );
}

/**
 * Array of social links profiles to loop through.
 *
 * @todo rename to vcex_social_share_sites
 */
function vcex_get_social_items() {
	if ( function_exists( 'wpex_social_share_items' ) ) {
		return wpex_social_share_items();
	}
	return array(
        'twitter' => array(
            'li_class'   => 'wpex-twitter',
            'icon_class' => 'ticon ticon-twitter',
            'label'      => esc_html__( 'Tweet', 'total-theme-core' ),
            'site'       => 'Twitter',
        ),
        'facebook' => array(
            'li_class'   => 'wpex-facebook',
            'icon_class' => 'ticon ticon-facebook',
            'label'      => esc_html__( 'Share', 'total-theme-core' ),
            'site'       => 'Facebook',
        ),
        'pinterest' => array(
            'li_class'   => 'wpex-pinterest',
            'icon_class' => 'ticon ticon-pinterest',
            'label'      => esc_html__( 'Pin It', 'total-theme-core' ),
            'site'       => 'Pinterest',
        ),
        'linkedin' => array(
            'li_class'   => 'wpex-linkedin',
            'icon_class' => 'ticon ticon-linkedin',
            'label'      => esc_html__( 'Share', 'total-theme-core' ),
            'site'       => 'LinkedIn',
        ),
        'email' => array(
            'li_class'   => 'wpex-email',
            'icon_class' => 'ticon ticon-envelope',
            'label'      => esc_html__( 'Email', 'total-theme-core' ),
            'site'       => 'Email',
        ),
    );
}

/**
 * Array of social links profiles to loop through.
 */
function vcex_social_links_profiles() {
	if ( function_exists( 'wpex_social_profile_options_list' ) ) {
		$profiles = wpex_social_profile_options_list();
	} else {
		$profiles = array(
			'twitter' => array(
				'label' => 'Twitter',
				'icon_class' => 'ticon ticon-twitter',
			),
			'facebook' => array(
				'label' => 'Facebook',
				'icon_class' => 'ticon ticon-facebook',
			),
			'pinterest'  => array(
				'label' => 'Pinterest',
				'icon_class' => 'ticon ticon-pinterest',
			),
			'dribbble' => array(
				'label' => 'Dribbble',
				'icon_class' => 'ticon ticon-dribbble',
			),
			'etsy'  => array(
				'label' => 'Etsy',
				'icon_class' => 'ticon ticon-etsy',
			),
			'vk' => array(
				'label' => 'VK',
				'icon_class' => 'ticon ticon-vk',
			),
			'instagram'  => array(
				'label' => 'Instagram',
				'icon_class' => 'ticon ticon-instagram',
			),
			'linkedin' => array(
				'label' => 'LinkedIn',
				'icon_class' => 'ticon ticon-linkedin',
			),
			'flickr' => array(
				'label' => 'Flickr',
				'icon_class' => 'ticon ticon-flickr',
			),
			'quora' => array(
				'label' => 'Quora',
				'icon_class' => 'ticon ticon-quora',
			),
			'skype' => array(
				'label' => 'Skype',
				'icon_class' => 'ticon ticon-skype',
			),
			'whatsapp' => array(
				'label' => 'Whatsapp',
				'icon_class' => 'ticon ticon-whatsapp',
			),
			'youtube' => array(
				'label' => 'Youtube',
				'icon_class' => 'ticon ticon-youtube',
			),
			'vimeo' => array(
				'label' => 'Vimeo',
				'icon_class' => 'ticon ticon-vimeo',
			),
			'vine' => array(
				'label' => 'Vine',
				'icon_class' => 'ticon ticon-vine',
			),
			'spotify' => array(
				'label' => 'Spotify',
				'icon_class' => 'ticon ticon-spotify',
			),
			'xing' => array(
				'label' => 'Xing',
				'icon_class' => 'ticon ticon-xing',
			),
			'yelp' => array(
				'label' => 'Yelp',
				'icon_class' => 'ticon ticon-yelp',
			),
			'tripadvisor' => array(
				'label' => 'Tripadvisor',
				'icon_class' => 'ticon ticon-tripadvisor',
			),
			'houzz' => array(
				'label' => 'Houzz',
				'icon_class' => 'ticon ticon-houzz',
			),
			'twitch' => array(
				'label' => 'Twitch',
				'icon_class' => 'ticon ticon-twitch',
			),
			'tumblr' => array(
				'label' => 'Tumblr',
				'icon_class' => 'ticon ticon-tumblr',
			),
			'github' => array(
				'label' => 'Github',
				'icon_class' => 'ticon ticon-github',
			),
			'rss'  => array(
				'label' => esc_html__( 'RSS', 'total-theme-core' ),
				'icon_class' => 'ticon ticon-rss',
			),
			'email' => array(
				'label' => esc_html__( 'Email', 'total-theme-core' ),
				'icon_class' => 'ticon ticon-envelope',
			),
			'phone' => array(
				'label' => esc_html__( 'Phone', 'total-theme-core' ),
				'icon_class' => 'ticon ticon-phone',
			),
		);
	}
	return apply_filters( 'vcex_social_links_profiles', $profiles );
}

/**
 * Array of pixel icons.
 */
if ( ! function_exists( 'vcex_pixel_icons' ) ) {
	function vcex_pixel_icons() {
		return array(
			array( 'vc_pixel_icon vc_pixel_icon-alert' => esc_html__( 'Alert', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-info' => esc_html__( 'Info', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-tick' => esc_html__( 'Tick', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-explanation' => esc_html__( 'Explanation', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-address_book' => esc_html__( 'Address book', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-alarm_clock' => esc_html__( 'Alarm clock', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-anchor' => esc_html__( 'Anchor', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-application_image' => esc_html__( 'Application Image', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-arrow' => esc_html__( 'Arrow', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-asterisk' => esc_html__( 'Asterisk', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-hammer' => esc_html__( 'Hammer', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-balloon' => esc_html__( 'Balloon', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-balloon_buzz' => esc_html__( 'Balloon Buzz', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-balloon_facebook' => esc_html__( 'Balloon Facebook', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-balloon_twitter' => esc_html__( 'Balloon Twitter', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-battery' => esc_html__( 'Battery', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-binocular' => esc_html__( 'Binocular', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-document_excel' => esc_html__( 'Document Excel', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-document_image' => esc_html__( 'Document Image', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-document_music' => esc_html__( 'Document Music', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-document_office' => esc_html__( 'Document Office', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-document_pdf' => esc_html__( 'Document PDF', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-document_powerpoint' => esc_html__( 'Document Powerpoint', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-document_word' => esc_html__( 'Document Word', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-bookmark' => esc_html__( 'Bookmark', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-camcorder' => esc_html__( 'Camcorder', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-camera' => esc_html__( 'Camera', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-chart' => esc_html__( 'Chart', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-chart_pie' => esc_html__( 'Chart pie', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-clock' => esc_html__( 'Clock', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-fire' => esc_html__( 'Fire', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-heart' => esc_html__( 'Heart', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-mail' => esc_html__( 'Mail', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-play' => esc_html__( 'Play', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-shield' => esc_html__( 'Shield', 'total-theme-core' ) ),
			array( 'vc_pixel_icon vc_pixel_icon-video' => esc_html__( 'Video', 'total-theme-core' ) ),
		);
	}
}

/**
 * Returns font icon options.
 */
function vcex_get_icon_font_families( $module = '' ) {
	return apply_filters( 'vcex_vc_map_icon_font_families', array(
		'fontawesome' => array(
			'label' => esc_html__( 'Font Awesome', 'total-theme-core' ),
			'default' => 'fa fa-info-circle',
		),
		'openiconic' => array(
			'label' => esc_html__( 'Open Iconic', 'total-theme-core' ),
		),
		'typicons' => array(
			'label' =>__( 'Typicons', 'total-theme-core' ),
		),
		'entypo' => array(
			'label' =>__( 'Entypo', 'total-theme-core' ),
		),
		'linecons' => array(
			'label' =>__( 'Linecons', 'total-theme-core' ),
		),
		'pixelicons' => array(
			'label' =>__( 'Pixel', 'total-theme-core' ),
			'source' => vcex_pixel_icons()
		),
		'monosocial' => array(
			'label' =>__( 'Mono Social', 'total-theme-core' ),
		),
	), $module );
}

/**
 * Array of Google Font options.
 */
function vcex_fonts_array() {

	// Default array
	$array = array(
		__( 'Default', 'total-theme-core' ) => '',
	);

	// Add custom fonts
	if ( $custom_fonts = wpex_add_custom_fonts() ) {
		$array = array_merge( $array, wpex_add_custom_fonts() );
	}

	// Add standard fonts
	$std_fonts = wpex_standard_fonts();
	$array = array_merge( $array, $std_fonts );

	// Add Google Fonts
	if ( $google_fonts = wpex_google_fonts_array() ) {
		$array = array_merge( $array, $google_fonts );
	}

	// Return fonts
	return apply_filters( 'vcex_google_fonts_array', $array );

}

/**
 * Text decoration choices.
 */
function vcex_text_decorations() {
	return apply_filters( 'wpex_text_decorations', array(
		''             => esc_html__( 'Default', 'total-theme-core' ),
		'underline'    => esc_html__( 'Underline', 'total-theme-core' ),
		'overline'     => esc_html__( 'Overline','total-theme-core' ),
		'line-through' => esc_html__( 'Line Through', 'total-theme-core' ),
	) );
}

/**
 * Font style choices.
 */
function vcex_font_styles() {
	return apply_filters( 'wpex_font_styles', array(
		''        => esc_html__( 'Default', 'total-theme-core' ),
		'normal'  => esc_html__( 'Normal', 'total-theme-core' ),
		'italic'  => esc_html__( 'Italic', 'total-theme-core' ),
		'oblique' => esc_html__( 'Oblique', 'total-theme-core' ),
	) );
}

/**
 * Entry content styles.
 */
function vcex_entry_content_styles() {
	return (array) apply_filters( 'vcex_entry_content_styles', array(
		'none'     => esc_html__( 'None', 'total-theme-core' ), // none needs a value or you can never save it.
		'boxed'    => esc_html__( 'Boxed', 'total-theme-core' ),
		'bordered' => esc_html__( 'Bordered', 'total-theme-core' ),
	) );
}

/**
 * Font sizes choices.
 */
function vcex_font_size_choices() {
	if ( function_exists( 'wpex_utl_font_sizes' ) ) {
		return array_flip( wpex_utl_font_sizes() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Border radius choices.
 */
function vcex_border_radius_choices() {
	if ( function_exists( 'wpex_utl_border_radius' ) ) {
		return array_flip( wpex_utl_border_radius() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Break point choices.
 */
function vcex_breakpoint_choices() {
	if ( function_exists( 'wpex_utl_breakpoints' ) ) {
		return array_flip( wpex_utl_breakpoints() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Shadow choices.
 */
function vcex_shadow_choices() {
	if ( function_exists( 'wpex_utl_shadows' ) ) {
		return array_flip( wpex_utl_shadows() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Border width choices.
 */
function vcex_border_width_choices() {
	if ( function_exists( 'wpex_utl_border_widths' ) ) {
		return array_flip( wpex_utl_border_widths() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Border style choices.
 */
function vcex_border_style_choices() {
	return array(
		esc_html__( 'Default', 'total-theme-core' ) => '',
		esc_html__( 'Solid', 'total-theme-core' )   => 'solid',
		esc_html__( 'Dashed', 'total-theme-core' )  => 'dashed',
		esc_html__( 'Dotted', 'total-theme-core' )  => 'dotted',
	);
}

/**
 * Padding choices.
 */
function vcex_padding_choices() {
	if ( function_exists( 'wpex_utl_paddings' ) ) {
		return array_flip( wpex_utl_paddings() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Margin choices.
 */
function vcex_margin_choices() {
	if ( function_exists( 'wpex_utl_margins' ) ) {
		return array_flip( wpex_utl_margins() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Divider Style choices.
 */
function vcex_divider_style_choices() {
	if ( function_exists( 'wpex_utl_divider_styles' ) ) {
		return array_flip( wpex_utl_divider_styles() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}

/**
 * Opacity choices.
 */
function vcex_opacity_choices() {
	if ( function_exists( 'wpex_utl_opacities' ) ) {
		return array_flip( wpex_utl_opacities() );
	}
	return array( 'Exclusive Total theme setting' => '' );
}
