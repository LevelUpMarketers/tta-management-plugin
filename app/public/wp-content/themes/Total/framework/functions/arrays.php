<?php
/**
 * Functions that return lists of items, primarily for use with select fields.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Site Layouts.
 *
 * @since 4.9
 * @todo rename filter to "wpex_site_layouts"
 */
function wpex_get_site_layouts() {
	return apply_filters( 'wpex_get_site_layouts', array(
		''           => esc_html__( 'Default', 'total' ),
		'full-width' => esc_html__( 'Full-Width', 'total' ),
		'boxed'      => esc_html__( 'Boxed', 'total' ),
	) );
}

/**
 * Accent Colors.
 *
 * @since 4.4.1
 * @todo rename filter to "wpex_accent_colors"
 */
function wpex_get_accent_colors() {
	return apply_filters( 'wpex_get_accent_colors', array(
		'default'  => array(
			'label' => esc_html__( 'Default', 'total' ),
			'hex'   => '', // Consider using wpex_get_custom_accent_color() for future updates?
		),
		'black'  => array(
			'label' => esc_html__( 'Black', 'total' ),
			'hex'   => '#333',
		),
		'blue'   => array(
			'label' => esc_html__( 'Blue', 'total' ),
			'hex'   => '#4a97c2',
		),
		'brown'  => array(
			'label' => esc_html__( 'Brown', 'total' ),
			'hex'   => '#804b35',
		),
		'grey'   => array(
			'label' => esc_html__( 'Grey', 'total' ),
			'hex'   => '#bbb',
		),
		'green'  => array(
			'label' => esc_html__( 'Green', 'total' ),
			'hex'   => '#87bf17',
		),
		'gold'   => array(
			'label' => esc_html__( 'Gold', 'total' ),
			'hex'   => '#ddba00',
		),
		'orange' => array(
			'label' => esc_html__( 'Orange', 'total' ),
			'hex'   => '#ee7836',
		),
		'pink'   => array(
			'label' => esc_html__( 'Pink', 'total' ),
			'hex'   => '#f261c2',
		),
		'purple' => array(
			'label' => esc_html__( 'Purple', 'total' ),
			'hex'   => '#9a5e9a',
		),
		'red'    => array(
			'label' => esc_html__( 'Red', 'total' ),
			'hex'   => '#f73936',
		),
		'rosy'   => array(
			'label' => esc_html__( 'Rosy', 'total' ),
			'hex'   => '#ea2487',
		),
		'teal'   => array(
			'label' => esc_html__( 'Teal', 'total' ),
			'hex'   => '#00b3b3',
		),
		'white'  => array(
			'label' => esc_html__( 'White', 'total' ),
			'hex'   => '#fff',
		),
	) );
}

/**
 * Returns array of header styles.
 *
 * @since 4.3
 */
function wpex_get_header_styles() {
	return apply_filters( 'wpex_header_styles', array(
		'one'   => '1. ' . esc_html__( 'Left Logo & Right Menu','total' ),
		'two'   => '2. ' . esc_html__( 'Bottom Menu','total' ),
		'three' => '3. ' . esc_html__( 'Bottom Menu Centered','total' ),
		'four'  => '4. ' . esc_html__( 'Top Centered Menu','total' ),
		'five'  => '5. ' . esc_html__( 'Centered Inline Logo','total' ),
		'six'   => '6. ' . esc_html__( 'Vertical','total' ),
		'dev'   => '7. ' . esc_html__( 'Dev (No Styling)','total' ),
	) );
}

/**
 * Returns array of header styles that allow sticky.
 *
 * @since 4.9.4
 * @todo rename filter to "wpex_header_styles_with_sticky_support"
 */
function wpex_get_header_styles_with_sticky_support() {
	return apply_filters( 'wpex_get_header_styles_with_sticky_support', array( 'one', 'five', 'dev' ) );
}

/**
 * Returns array of header styles that allow aside content.
 *
 * @since 4.9.4
 * @todo rename filter to "wpex_header_styles_with_aside_support"
 */
function wpex_get_header_styles_with_aside_support() {
	return apply_filters( 'wpex_get_header_styles_with_aside_support', array( 'two', 'three', 'four', 'dev' ) );
}

/**
 * Return array of page header styles.
 *
 * @since 4.0
 */
function wpex_get_page_header_styles() {
	return apply_filters( 'wpex_page_header_styles', array(
		''                 => esc_html__( 'Default','total' ),
		'centered'         => esc_html__( 'Centered', 'total' ),
		'centered-minimal' => esc_html__( 'Centered Minimal', 'total' ),
		'background-image' => esc_html__( 'Background Image', 'total' ),
		'hidden'           => esc_html__( 'Hidden (Disabled)', 'total' ),
	) );
}

/**
 * Returns array of heading styles.
 *
 * @since 4.9.5
 * @todo rename filter to "wpex_theme_heading_styles"
 */
function wpex_get_theme_heading_styles() {
	return apply_filters( 'wpex_get_theme_heading_styles', array(
		''               => esc_html__( 'Default', 'total' ) ,
		'plain'          => esc_html__( 'Plain (no styling)', 'total' ),
		'border-side'    => esc_html__( 'Side Border', 'total' ),
		'border-bottom'  => esc_html__( 'Bottom Border', 'total' ),
		'border-w-color' => esc_html__( 'Bottom Border With Color', 'total' ),
	) );
}

/**
 * Returns array of image background styles.
 *
 * @since 3.5.0
 */
function wpex_get_bg_img_styles() {
	return array(
		''             => esc_html__( 'Default', 'total' ),
		'cover'        => esc_html__( 'Cover', 'total' ),
		'stretched'    => esc_html__( 'Stretched', 'total' ),
		'repeat'       => esc_html__( 'Repeat', 'total' ),
		'fixed-top'    => esc_html__( 'Fixed Top', 'total' ),
		'fixed'        => esc_html__( 'Fixed Center', 'total' ),
		'fixed-bottom' => esc_html__( 'Fixed Bottom', 'total' ),
		'repeat-x'     => esc_html__( 'Repeat-x', 'total' ),
		'repeat-y'     => esc_html__( 'Repeat-y', 'total' ),
		'inherit'      => esc_html__( 'Inherit', 'total' ),
	);
}

/**
 * Returns array of dropdown styles.
 *
 * @since 3.4.0
 * @todo rename filter to "wpex_header_menu_dropdown_styles"
 */
function wpex_get_menu_dropdown_styles() {
	return apply_filters( 'wpex_get_header_menu_dropdown_styles', array(
		'default'    => esc_html__( 'Default', 'total' ),
		'minimal-sq' => esc_html__( 'Minimal', 'total' ),
		'minimal'    => esc_html__( 'Minimal - Rounded', 'total' ),
		'black'      => esc_html__( 'Black', 'total' ),
	) );
}

/**
 * Returns array of form styles.
 *
 * @since 3.6.0
 * @todo rename filter to "wpex_form_styles"
 */
function wpex_get_form_styles() {
	return apply_filters( 'wpex_get_form_styles', array(
		''        => esc_html__( 'Default', 'total' ),
		'min'     => esc_html__( 'Minimal', 'total' ),
		'modern'  => esc_html__( 'Modern', 'total' ),
		'white'   => esc_html__( 'White', 'total' ),
		'black'   => esc_html__( 'Black', 'total' ),
		'white-o' => esc_html__( 'White Outline', 'total' ),
		'black-o' => esc_html__( 'Black Outline', 'total' ),
	) );
}

/**
 * Array of carousel arrow positions.
 *
 * @since 3.5.3
 */
function wpex_carousel_arrow_positions() {
	return apply_filters( 'wpex_carousel_arrow_positions', array(
		'default' => esc_html__( 'Default', 'total' ),
		'left'    => esc_html__( 'Left', 'total' ) ,
	 	'center'  => esc_html__( 'Center', 'total' ),
		'right'   => esc_html__( 'Right', 'total' ),
		'abs'     => esc_html__( 'Absolute', 'total' ),
	) );
}

/**
 * Array of carousel arrow styles.
 *
 * @since 3.5.3
 */
function wpex_carousel_arrow_styles() {
	return apply_filters( 'wpex_carousel_arrow_styles', array(
		''       => esc_html__( 'Default', 'total' ),
		'slim'   => esc_html__( 'Slim', 'total' ),
		'min'    => esc_html__( 'Minimal', 'total' ),
		'border' => esc_html__( 'Border', 'total' ),
		'circle' => esc_html__( 'Circle', 'total' ),
	) );
}

/**
 * Returns array of page layouts.
 *
 * @since 3.3.3
 * @todo rename filter to "wpex_post_layouts"
 */
function wpex_get_post_layouts() {
	return apply_filters( 'wpex_get_post_layouts', array(
		''              => esc_html__( 'Default', 'total' ),
		'right-sidebar' => esc_html__( 'Right Sidebar', 'total' ),
		'left-sidebar'  => esc_html__( 'Left Sidebar', 'total' ),
		'full-width'    => esc_html__( 'No Sidebar', 'total' ),
		'full-screen'   => esc_html__( 'Full Screen', 'total' ),
	) );
}

/**
 * Returns array of Header Overlay Styles.
 *
 * @since 3.3.0
 */
function wpex_header_overlay_styles() {
	return apply_filters( 'wpex_header_overlay_styles', array(
		''      => esc_html__( 'Default', 'total' ),
		'white' => esc_html__( 'White Text', 'total' ),
		'light' => esc_html__( 'Light Text', 'total' ),
		'dark'  => esc_html__( 'Black Text', 'total' ),
		'core'  => esc_html__( 'Core Styles', 'total' ),
	) );
}


/**
 * Returns array of Header Overlay Styles.
 *
 * @since 4.5.5.1
 * @todo rename filter to "wpex_mobile_menu_styles"
 */
function wpex_get_mobile_menu_styles() {
	return apply_filters( 'wpex_get_mobile_menu_styles', array(
		'sidr'        => esc_html__( 'Sidebar', 'total' ),
		'toggle'      => esc_html__( 'Toggle', 'total' ),
		'full_screen' => esc_html__( 'Full Screen Overlay', 'total' ),
		'disabled'    => esc_html__( 'Disabled', 'total' ),
	) );
}

/**
 * Returns array of available post types.
 *
 * @since 3.3.0
 */
function wpex_get_post_types( $instance = '', $exclude = array() ) {
	$types = array();
	$get_types = get_post_types( array(
		'public'   => true,
	), 'objects', 'and' );
	foreach ( $get_types as $key => $val ) {
		if ( ! in_array( $key, $exclude ) ) {
			$types[$key] = $val->labels->name;
		}
	}
	return apply_filters( 'wpex_get_post_types', $types, $instance );
}

/**
 * User social options.
 *
 * @since 4.0
 */
function wpex_get_user_social_profile_settings_array() {
	return apply_filters( 'wpex_get_user_social_profile_settings_array', array(
		'twitter'        => array(
			'label'      => 'Twitter',
			'icon_class' => 'ticon ticon-twitter',
		),
		'facebook'        => array(
			'label'       => 'Facebook',
			'icon_class'  => 'ticon ticon-facebook',
		),
		'linkedin'        => array(
			'label'       => 'LinkedIn',
			'icon_class'  => 'ticon ticon-linkedin',
		),
		'pinterest'       => array(
			'label'       => 'Pinterest',
			'icon_class'  => 'ticon ticon-pinterest',
		),
		'instagram'       => array(
			'label'       => 'Instagram',
			'icon_class'  => 'ticon ticon-instagram',
		),
	) );
}

/**
 * Global List Social Link Options.
 *
 * @since 4.3
 */
function wpex_social_profile_options_list() {
	return apply_filters ( 'wpex_social_profile_options_list', array(
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
			'label' => esc_html__( 'RSS', 'total' ),
			'icon_class' => 'ticon ticon-rss',
		),
		'email' => array(
			'label' => esc_html__( 'Email', 'total' ),
			'icon_class' => 'ticon ticon-envelope',
		),
		'phone' => array(
			'label' => esc_html__( 'Phone', 'total' ),
			'icon_class' => 'ticon ticon-phone',
		),
	) );
}

/**
 * Returns array of Social Options for the Top Bar.
 *
 * @since 1.6.0
 */
function wpex_topbar_social_options() {
	return apply_filters ( 'wpex_topbar_social_options', wpex_social_profile_options_list() );
}

/**
 * Returns array of WP dashicons.
 *
 * @since 3.3.0
 */
function wpex_get_dashicons_array() {
	$dashicons = array(
		'admin-appearance' => 'f100',
		'admin-collapse' => 'f148',
		'admin-comments' => 'f117',
		'admin-generic' => 'f111',
		'admin-home' => 'f102',
		'admin-media' => 'f104',
		'admin-network' => 'f112',
		'admin-page' => 'f133',
		'admin-plugins' => 'f106',
		'admin-settings' => 'f108',
		'admin-site' => 'f319',
		'admin-tools' => 'f107',
		'admin-users' => 'f110',
		'align-center' => 'f134',
		'align-full-width' => 'f114',
		'align-pull-left' => 'f10a',
		'align-pull-right' => 'f10b',
		'align-wide' => 'f11b',
		'align-left' => 'f135',
		'align-none' => 'f138',
		'align-right' => 'f136',
		'analytics' => 'f183',
		'arrow-down' => 'f140',
		'arrow-down-alt' => 'f346',
		'arrow-down-alt2' => 'f347',
		'arrow-left' => 'f141',
		'arrow-left-alt' => 'f340',
		'arrow-left-alt2' => 'f341',
		'arrow-right' => 'f139',
		'arrow-right-alt' => 'f344',
		'arrow-right-alt2' => 'f345',
		'arrow-up' => 'f142',
		'arrow-up-alt' => 'f342',
		'arrow-up-alt2' => 'f343',
		'art' => 'f309',
		'awards' => 'f313',
		'backup' => 'f321',
		'block-default' => 'f12b',
		'button' => 'f11a',
		'book' => 'f330',
		'book-alt' => 'f331',
		'businessman' => 'f338',
		'calendar' => 'f145',
		'camera' => 'f306',
		'cart' => 'f174',
		'category' => 'f318',
		'chart-area' => 'f239',
		'chart-bar' => 'f185',
		'chart-line' => 'f238',
		'chart-pie' => 'f184',
		'clock' => 'f469',
		'cloud' => 'f176',
		'cloud-saved' => 'f137',
		'cloud-upload' => 'f13b',
		'cover-image' => 'f13d',
		'columns' => 'f13c',
		'dashboard' => 'f226',
		'desktop' => 'f472',
		'dismiss' => 'f153',
		'download' => 'f316',
		'edit' => 'f464',
		'editor-aligncenter' => 'f207',
		'editor-alignleft' => 'f206',
		'editor-alignright' => 'f208',
		'editor-bold' => 'f200',
		'editor-customchar' => 'f220',
		'editor-distractionfree' => 'f211',
		'editor-help' => 'f223',
		'editor-indent' => 'f222',
		'editor-insertmore' => 'f209',
		'editor-italic' => 'f201',
		'editor-justify' => 'f214',
		'editor-kitchensink' => 'f212',
		'editor-ol' => 'f204',
		'editor-outdent' => 'f221',
		'editor-paste-text' => 'f217',
		'editor-paste-word' => 'f216',
		'editor-quote' => 'f205',
		'editor-removeformatting' => 'f218',
		'editor-rtl' => 'f320',
		'editor-spellcheck' => 'f210',
		'editor-strikethrough' => 'f224',
		'editor-textcolor' => 'f215',
		'editor-ul' => 'f203',
		'editor-underline' => 'f213',
		'editor-unlink' => 'f225',
		'editor-video' => 'f219',
		'exit' => 'f14a',
		'heading' => 'f10e',
		'html' => 'f14b',
		'info-outline' => 'f14c',
		'insert-after' => 'f14d',
		'insert-before' => 'f14e',
		'insert' => 'f10f',
		'remove' => 'f14f',
		'shortcode' => 'f150',
		'email' => 'f465',
		'email-alt' => 'f466',
		'embed-audio' => 'f13e',
		'embed-photo' => 'f144',
		'embed-post' => 'f146',
		'embed-video' => 'f149',
		'exerpt-view' => 'f164',
		'facebook' => 'f304',
		'facebook-alt' => 'f305',
		'feedback' => 'f175',
		'flag' => 'f227',
		'format-aside' => 'f123',
		'format-audio' => 'f127',
		'format-chat' => 'f125',
		'format-gallery' => 'f161',
		'format-image' => 'f128',
		'format-links' => 'f103',
		'format-quote' => 'f122',
		'format-standard' => 'f109',
		'format-status' => 'f130',
		'format-video' => 'f126',
		'forms' => 'f314',
		'googleplus' => 'f462',
		'groups' => 'f307',
		'hammer' => 'f308',
		'id' => 'f336',
		'id-alt' => 'f337',
		'image-crop' => 'f165',
		'image-flip-horizontal' => 'f169',
		'image-flip-vertical' => 'f168',
		'image-rotate-left' => 'f166',
		'image-rotate-right' => 'f167',
		'images-alt' => 'f232',
		'images-alt2' => 'f233',
		'info' => 'f348',
		'leftright' => 'f229',
		'lightbulb' => 'f339',
		'list-view' => 'f163',
		'location' => 'f230',
		'location-alt' => 'f231',
		'lock' => 'f160',
		'marker' => 'f159',
		'menu' => 'f333',
		'migrate' => 'f310',
		'minus' => 'f460',
		'networking' => 'f325',
		'no' => 'f158',
		'no-alt' => 'f335',
		'performance' => 'f311',
		'plus' => 'f132',
		'portfolio' => 'f322',
		'post-status' => 'f173',
		'pressthis' => 'f157',
		'products' => 'f312',
		'redo' => 'f172',
		'rss' => 'f303',
		'screenoptions' => 'f180',
		'search' => 'f179',
		'share' => 'f237',
		'share-alt' => 'f240',
		'share-alt2' => 'f242',
		'shield' => 'f332',
		'shield-alt' => 'f334',
		'slides' => 'f181',
		'smartphone' => 'f470',
		'smiley' => 'f328',
		'sort' => 'f156',
		'sos' => 'f468',
		'star-empty' => 'f154',
		'star-filled' => 'f155',
		'star-half' => 'f459',
		'tablet' => 'f471',
		'tag' => 'f323',
		'testimonial' => 'f473',
		'translation' => 'f326',
		'trash' => 'f182',
		'twitter' => 'f301',
		'undo' => 'f171',
		'update' => 'f463',
		'upload' => 'f317',
		'vault' => 'f178',
		'video-alt' => 'f234',
		'video-alt2' => 'f235',
		'video-alt3' => 'f236',
		'visibility' => 'f177',
		'welcome-add-page' => 'f133',
		'welcome-comments' => 'f117',
		'welcome-edit-page' => 'f119',
		'welcome-learn-more' => 'f118',
		'welcome-view-site' => 'f115',
		'welcome-widgets-menus' => 'f116',
		'wordpress' => 'f120',
		'wordpress-alt' => 'f324',
		'yes' => 'f147',
		'table-col-after' => 'f151',
		'table-col-before' => 'f152',
		'table-col-delete' => 'f15a',
		'table-row-after' => 'f15b',
		'table-row-before' => 'f15c',
		'table-row-delete' => 'f15d',
		'saved' => 'f15e',
		'database-add' => 'f170',
		'database-export' => 'f17a',
		'database-import' => 'f17b',
		'database-remove' => 'f17c',
		'database-view' => 'f17d',
		'database' => 'f17e',
		'airplane' => 'f15f',
		'car' => 'f16b',
		'calculator' => 'f16e',
		'games' => 'f18a',
		'printer' => 'f193',
		'beer' => 'f16c',
		'coffee' => 'f16f',
		'drumstick' => 'f17f',
		'food' => 'f187',
		'bank' => 'f16a',
		'hourglass' => 'f18c',
		'money-alt' => 'f18e',
		'open-folder' => 'f18f',
		'pdf' => 'f190',
		'pets' => 'f191',
		'privacy' => 'f194',
		'superhero' => 'f198',
		'superhero-alt' => 'f197',
		'edit-page' => 'f186',
		'fullscreen-alt' => 'f188',
		'fullscreen-exit-alt' => 'f189',
	);
	ksort( $dashicons );
	return apply_filters( 'wpex_dashicons_array', $dashicons );
}

/**
 * Array of social profiles for staff members.
 *
 * @since 1.5.4
 */
function wpex_staff_social_array() {
	return apply_filters( 'wpex_staff_social_array', array(
		'twitter'        => array(
			'key'        => 'twitter',
			'meta'       => 'wpex_staff_twitter',
			'icon_class' => 'ticon ticon-twitter',
			'label'      => 'Twitter',
		),
		'facebook'        => array(
			'key'        => 'facebook',
			'meta'       => 'wpex_staff_facebook',
			'icon_class' => 'ticon ticon-facebook',
			'label'      => 'Facebook',
		),
		'instagram'      => array(
			'key'        => 'instagram',
			'meta'       => 'wpex_staff_instagram',
			'icon_class' => 'ticon ticon-instagram',
			'label'      => 'Instagram',
		),
		'linkedin'       => array(
			'key'        => 'linkedin',
			'meta'       => 'wpex_staff_linkedin',
			'icon_class' => 'ticon ticon-linkedin',
			'label'      => 'Linkedin',
		),
		'dribbble'       => array(
			'key'        => 'dribbble',
			'meta'       => 'wpex_staff_dribbble',
			'icon_class' => 'ticon ticon-dribbble',
			'label'      => 'Dribbble',
		),
		'vk'             => array(
			'key'        => 'vk',
			'meta'       => 'wpex_staff_vk',
			'icon_class' => 'ticon ticon-vk',
			'label'      => 'VK',
		),
		'skype'          => array(
			'key'        => 'skype',
			'meta'       => 'wpex_staff_skype',
			'icon_class' => 'ticon ticon-skype',
			'label'      => 'Skype',
		),
		'phone_number'   => array(
			'key'        => 'phone_number',
			'meta'       => 'wpex_staff_phone_number',
			'icon_class' => 'ticon ticon-phone',
			'label'      => esc_html__( 'Phone Number', 'total' ),
		),
		'email'          => array(
			'key'        => 'email',
			'meta'       => 'wpex_staff_email',
			'icon_class' => 'ticon ticon-envelope',
			'label'      => esc_html__( 'Email', 'total' ),
		),
		'website'        => array(
			'key'        => 'website',
			'meta'       => 'wpex_staff_website',
			'icon_class' => 'ticon ticon-external-link-square',
			'label'      => esc_html__( 'Website', 'total' ),
		),
	) );
}

/**
 * Creates an array for adding the staff social options to the metaboxes.
 *
 * @since 1.5.4
 */
function wpex_staff_social_meta_array() {
	$profiles = wpex_staff_social_array();
	$array = array();
	foreach ( $profiles as $profile ) {
		$array[] = array(
			'title' => '<span class="' . esc_attr( $profile['icon_class'] ) . '" aria-hidden="true"></span>' . esc_html( $profile['label'] ),
			'id'    => wp_strip_all_tags( $profile['meta'] ),
			'type'  => 'text',
			'std'   => '',
		);
	}
	return $array;
}

/**
 * Grid Columns.
 *
 * @since 2.0.0
 */
function wpex_grid_columns() {
	return (array) apply_filters( 'wpex_grid_columns', array(
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
		'7' => '7',
	) );
}

/**
 * Grid Column Gaps.
 *
 * @since 2.0.0
 */
function wpex_column_gaps() {
	return apply_filters( 'wpex_column_gaps', array(
		''     => esc_html__( 'Default', 'total' ),
		'none' => '0px',
		'1'    => '1px',
		'5'    => '5px',
		'10'   => '10px',
		'15'   => '15px',
		'20'   => '20px',
		'25'   => '25px',
		'30'   => '30px',
		'35'   => '35px',
		'40'   => '40px',
		'50'   => '50px',
		'60'   => '60px',
	) );
}

/**
 * Typography Styles.
 *
 * @since 2.0.0
 * @todo deprecate these styles...
 */
function wpex_typography_styles() {
	return apply_filters( 'wpex_typography_styles', array(
		''             => esc_html__( 'Default', 'total' ),
		'light'        => esc_html__( 'Light', 'total' ),
		'white'        => esc_html__( 'White', 'total' ),
		'white-shadow' => esc_html__( 'White with Shadow', 'total' ),
		'black'        => esc_html__( 'Black', 'total' ),
		'none'         => esc_html__( 'None', 'total' ),
	) );
}

/**
 * Button styles.
 *
 * @since 1.6.2
 */
function wpex_button_styles() {
	return apply_filters( 'wpex_button_styles', array(
		''               => esc_html__( 'Default', 'total' ),
		'flat'           => esc_html__( 'Flat', 'total' ),
		'graphical'      => esc_html__( 'Graphical', 'total' ),
		'clean'          => esc_html__( 'Clean', 'total' ),
		'three-d'        => esc_html__( '3D', 'total' ),
		'outline'        => esc_html__( 'Outline', 'total' ),
		'minimal-border' => esc_html__( 'Minimal Border', 'total' ),
		'plain-text'     => esc_html__( 'Plain Text', 'total' ),
	) );
}

/**
 * Button colors.
 *
 * @since 1.6.2
 * @deprecated since 4.4.1 - theme now uses new wpex_get_accent_colors function.
 */
function wpex_button_colors() {
	$button_colors = array();
	$accents = ( array ) wpex_get_accent_colors();
	if ( $accents ) {
		foreach ( $accents as $k => $v ) {
			if ( 'default' == $k ) {
				$button_colors[''] = $v['label'];
			} else {
				$button_colors[$k] = $v['label'];
			}
		}
	}
	return apply_filters( 'wpex_button_colors', $button_colors );
}

/**
 * Array of image crop locations.
 *
 * @link 2.0.0
 */
function wpex_image_crop_locations() {
	return array(
		''              => esc_html__( 'Default', 'total' ),
		'left-top'      => esc_html__( 'Top Left', 'total' ),
		'right-top'     => esc_html__( 'Top Right', 'total' ),
		'center-top'    => esc_html__( 'Top Center', 'total' ),
		'left-center'   => esc_html__( 'Center Left', 'total' ),
		'right-center'  => esc_html__( 'Center Right', 'total' ),
		'center-center' => esc_html__( 'Center Center', 'total' ),
		'left-bottom'   => esc_html__( 'Bottom Left', 'total' ),
		'right-bottom'  => esc_html__( 'Bottom Right', 'total' ),
		'center-bottom' => esc_html__( 'Bottom Center', 'total' ),
		'soft-crop'     => esc_html__( 'Soft Crop', 'total' ),
	);
}

/**
 * Image Hovers.
 *
 * @since 1.6.2
 */
function wpex_image_hovers() {
	return apply_filters( 'wpex_image_hovers', array(
		''                 => esc_html__( 'Default', 'total' ),
		'opacity'          => esc_html__( 'Opacity', 'total' ),
		'opacity-invert'   => esc_html__( 'Opacity Invert', 'total' ),
		'shrink'           => esc_html__( 'Shrink', 'total' ),
		'grow'             => esc_html__( 'Grow', 'total' ),
		'side-pan'         => esc_html__( 'Side Pan', 'total' ),
		'vertical-pan'     => esc_html__( 'Vertical Pan', 'total' ),
		'tilt'             => esc_html__( 'Tilt', 'total' ),
		'blurr'            => esc_html__( 'Blurr', 'total' ),
		'blurr-invert'     => esc_html__( 'Blurr Invert', 'total' ),
		'sepia'            => esc_html__( 'Sepia', 'total' ),
		'fade-out'         => esc_html__( 'Fade Out', 'total' ),
		'fade-in'          => esc_html__( 'Fade In', 'total' ),
		'grayscale'        => esc_html__( 'Grayscale', 'total' ),
		'grayscale-invert' => esc_html__( 'Grayscale Invert', 'total' ),
	) );
}

/**
 * Text decorations.
 *
 * @since 1.6.2
 */
function wpex_text_decorations() {
	return apply_filters( 'wpex_text_decorations', array(
		''             => esc_html__( 'Default', 'total' ),
		'underline'    => esc_html__( 'Underline', 'total' ),
		'overline'     => esc_html__( 'Overline','total' ),
		'line-through' => esc_html__( 'Line Through', 'total' ),
	) );
}

/**
 * Font Weights.
 *
 * @since 1.6.2
 */
function wpex_font_weights() {
	return apply_filters( 'wpex_font_weights', array(
		''         => esc_html__( 'Default', 'total' ),
		'normal'   => esc_html__( 'Normal', 'total' ),
		'semibold' => esc_html__( 'Semibold','total' ),
		'bold'     => esc_html__( 'Bold', 'total' ),
		'bolder'   => esc_html__( 'Bolder', 'total' ),
		'100'      => '100',
		'200'      => '200',
		'300'      => '300',
		'400'      => '400',
		'500'      => '500',
		'600'      => '600',
		'700'      => '700',
		'800'      => '800',
		'900'      => '900',
	) );
}

/**
 * Font Style.
 *
 * @since 1.6.2
 */
function wpex_font_styles() {
	return apply_filters( 'wpex_font_styles', array(
		''        => esc_html__( 'Default', 'total' ),
		'normal'  => esc_html__( 'Normal', 'total' ),
		'italic'  => esc_html__( 'Italic', 'total' ),
		'oblique' => esc_html__( 'Oblique', 'total' ),
	) );
}

/**
 * Text Transform.
 *
 * @since 1.6.2
 */
function wpex_text_transforms() {
	return array(
		''           => esc_html__( 'Default', 'total' ),
		'none'       => esc_html__( 'None', 'total' ) ,
		'capitalize' => esc_html__( 'Capitalize', 'total' ),
		'uppercase'  => esc_html__( 'Uppercase', 'total' ),
		'lowercase'  => esc_html__( 'Lowercase', 'total' ),
	);
}

/**
 * Border Styles.
 *
 * @since 1.6.0
 */
function wpex_border_styles() {
	return array(
		''       => esc_html__( 'Default', 'total' ),
		'solid'  => esc_html__( 'Solid', 'total' ),
		'dotted' => esc_html__( 'Dotted', 'total' ),
		'dashed' => esc_html__( 'Dashed', 'total' ),
	);
}

/**
 * Alignments.
 *
 * @since 1.6.0
 */
function wpex_alignments() {
	return array(
		''       => esc_html__( 'Default', 'total' ),
		'left'   => esc_html__( 'Left', 'total' ),
		'right'  => esc_html__( 'Right', 'total' ),
		'center' => esc_html__( 'Center', 'total' ),
	);
}

/**
 * Visibility.
 *
 * @since 1.6.0
 */
function wpex_visibility() {
	return (array) apply_filters( 'wpex_visibility', array(
		''                         => esc_html__( 'Always Visible', 'total' ),
		'hidden'                   => esc_html__( 'Always Hidden', 'total' ),

		'show-at-mm-breakpoint'    => esc_html__( 'Visible At Mobile Menu Breakpoint', 'total' ),
		'hide-at-mm-breakpoint'    => esc_html__( 'Hidden At Mobile Menu Breakpoint', 'total' ),

		'visible-desktop-large'    => esc_html__( 'Visible on Large Desktops (1280px or greater)', 'total' ),
		'hidden-desktop-large'     => esc_html__( 'Hidden on Large Desktops (1280px or greater)', 'total' ),

		'visible-desktop'          => esc_html__( 'Visible on Desktop (1024px or greater)', 'total' ),
		'hidden-desktop'           => esc_html__( 'Hidden on Desktop (1024px or greater)', 'total' ),

		'visible-tablet'           => esc_html__( 'Visible on Tablets (768px to 1023px)', 'total' ),
		'hidden-tablet'            => esc_html__( 'Hidden on Tablets (768px to 1023px)', 'total' ),

		'visible-phone'            => esc_html__( 'Visible on Phones (767px or smaller)', 'total' ),
		'hidden-phone'             => esc_html__( 'Hidden on Phones (767px or smaller)', 'total' ),

	) );
}

/**
 * CSS Animations.
 *
 * @since 1.6.0
 */
function wpex_css_animations() {
	return apply_filters( 'wpex_css_animations', array(
		''              => esc_html__( 'None', 'total') ,
		'top-to-bottom' => esc_html__( 'Top to bottom', 'total' ),
		'bottom-to-top' => esc_html__( 'Bottom to top', 'total' ),
		'left-to-right' => esc_html__( 'Left to right', 'total' ),
		'right-to-left' => esc_html__( 'Right to left', 'total' ),
		'appear'        => esc_html__( 'Appear from center', 'total' ),
	) );
}

/**
 * Array of Hover CSS animations.
 *
 * @since 2.0.0
 */
function wpex_hover_css_animations() {
	return apply_filters( 'wpex_hover_css_animations', array(
		''                       => esc_html__( 'Default', 'total' ),
		'shadow'                 => esc_html__( 'Shadow', 'total' ),
		'grow-shadow'            => esc_html__( 'Grow Shadow', 'total' ),
		'float-shadow'           => esc_html__( 'Float Shadow', 'total' ),
		'grow'                   => esc_html__( 'Grow', 'total' ),
		'shrink'                 => esc_html__( 'Shrink', 'total' ),
		'pulse'                  => esc_html__( 'Pulse', 'total' ),
		'pulse-grow'             => esc_html__( 'Pulse Grow', 'total' ),
		'pulse-shrink'           => esc_html__( 'Pulse Shrink', 'total' ),
		'push'                   => esc_html__( 'Push', 'total' ),
		'pop'                    => esc_html__( 'Pop', 'total' ),
		'bounce-in'              => esc_html__( 'Bounce In', 'total' ),
		'bounce-out'             => esc_html__( 'Bounce Out', 'total' ),
		'rotate'                 => esc_html__( 'Rotate', 'total' ),
		'grow-rotate'            => esc_html__( 'Grow Rotate', 'total' ),
		'float'                  => esc_html__( 'Float', 'total' ),
		'sink'                   => esc_html__( 'Sink', 'total' ),
		'bob'                    => esc_html__( 'Bob', 'total' ),
		'hang'                   => esc_html__( 'Hang', 'total' ),
		'skew'                   => esc_html__( 'Skew', 'total' ),
		'skew-backward'          => esc_html__( 'Skew Backward', 'total' ),
		'wobble-horizontal'      => esc_html__( 'Wobble Horizontal', 'total' ),
		'wobble-vertical'        => esc_html__( 'Wobble Vertical', 'total' ),
		'wobble-to-bottom-right' => esc_html__( 'Wobble To Bottom Right', 'total' ),
		'wobble-to-top-right'    => esc_html__( 'Wobble To Top Right', 'total' ),
		'wobble-top'             => esc_html__( 'Wobble Top', 'total' ),
		'wobble-bottom'          => esc_html__( 'Wobble Bottom', 'total' ),
		'wobble-skew'            => esc_html__( 'Wobble Skew', 'total' ),
		'buzz'                   => esc_html__( 'Buzz', 'total' ),
		'buzz-out'               => esc_html__( 'Buzz Out', 'total' ),
		'glow'                   => esc_html__( 'Glow', 'total' ),
		'shadow-radial'          => esc_html__( 'Shadow Radial', 'total' ),
		'box-shadow-outset'      => esc_html__( 'Box Shadow Outset', 'total' ),
		'box-shadow-inset'       => esc_html__( 'Box Shadow Inset', 'total' ),
	) );
}

/**
 * Image filter styles.
 *
 * @since 1.4.0
 */
function wpex_image_filters() {
	return apply_filters( 'wpex_image_filters', array(
		''             => esc_html__( 'None', 'total' ),
		'grayscale'    => esc_html__( 'Grayscale', 'total' ),
		'sepia'        => esc_html__( 'Sepia', 'total' ),
		'contrast-150' => esc_html__( 'High Contrast', 'total' ),
		'saturate-2'   => esc_html__( 'Saturate', 'total' ),
	) );
}

/**
 * Social Link styles.
 *
 * @since 3.0.0
 */
function wpex_social_button_styles() {
	return apply_filters( 'wpex_social_button_styles', array(
		'default'            => esc_html__( 'Default', 'total' ),
		'none'               => esc_html__( 'None', 'total' ),
		'minimal'            => esc_html__( 'Minimal', 'total' ),
		'minimal-rounded'    => esc_html__( 'Minimal Rounded', 'total' ),
		'minimal-round'      => esc_html__( 'Minimal Round', 'total' ),
		'flat'               => esc_html__( 'Flat', 'total' ),
		'flat-rounded'       => esc_html__( 'Flat Rounded', 'total' ),
		'flat-round'         => esc_html__( 'Flat Round', 'total' ),
		'flat-color'         => esc_html__( 'Flat Color', 'total' ),
		'flat-color-rounded' => esc_html__( 'Flat Color Rounded', 'total' ),
		'flat-color-round'   => esc_html__( 'Flat Color Round', 'total' ),
		'3d'                 => esc_html__( '3D', 'total' ),
		'3d-color'           => esc_html__( '3D Color', 'total' ),
		'black'              => esc_html__( 'Black', 'total' ),
		'black-rounded'      => esc_html__( 'Black Rounded', 'total' ),
		'black-round'        => esc_html__( 'Black Round', 'total' ),
		'black-ch'           => esc_html__( 'Black with Color Hover', 'total' ),
		'black-ch-rounded'   => esc_html__( 'Black with Color Hover Rounded', 'total' ),
		'black-ch-round'     => esc_html__( 'Black with Color Hover Round', 'total' ),
		'graphical'          => esc_html__( 'Graphical', 'total' ),
		'graphical-rounded'  => esc_html__( 'Graphical Rounded', 'total' ),
		'graphical-round'    => esc_html__( 'Graphical Round', 'total' ),
		'bordered'           => esc_html__( 'Bordered', 'total' ),
		'bordered-rounded'   => esc_html__( 'Bordered Rounded', 'total' ),
		'bordered-round'     => esc_html__( 'Bordered Round', 'total' ),
	) );
}

/**
 * Array of background patterns.
 *
 * @since 4.0
 * @todo rename filter to "wpex_background_patterns" (possible remove this function completely?)
 */
function wpex_get_background_patterns() {
	$url = wpex_asset_url( 'images/patterns/' );
	return apply_filters( 'wpex_get_background_patterns', array(
		'dark_wood' => array(
			'label' => esc_html__( 'Dark Wood', 'total' ),
			'url'   => $url . 'dark_wood.png',
		),
		'diagmonds' => array(
			'label' => esc_html__( 'Diamonds', 'total' ),
			'url'   => $url . 'diagmonds.png',
		),
		'grilled' => array(
			'label' => esc_html__( 'Grilled', 'total' ),
			'url'   => $url . 'grilled.png',
		),
		'lined_paper' => array(
			'label' => esc_html__( 'Lined Paper', 'total' ),
			'url'   => $url . 'lined_paper.png',
		),
		'old_wall' => array(
			'label' => esc_html__( 'Old Wall', 'total' ),
			'url'   => $url . 'old_wall.png',
		),
		'ricepaper' => array(
			'label' => esc_html__( 'Rice Paper', 'total' ),
			'url'   => $url . 'ricepaper.png',
		),
		'tree_bark' => array(
			'label' => esc_html__( 'Tree Bark', 'total' ),
			'url'   => $url . 'tree_bark.png',
		),
		'triangular' => array(
			'label' => esc_html__( 'Triangular', 'total' ),
			'url'   => $url . 'triangular.png',
		),
		'white_plaster' => array(
			'label' => esc_html__( 'White Plaster', 'total' ),
			'url'   => $url . 'white_plaster.png',
		),
		'wild_flowers' => array(
			'label' => esc_html__( 'Wild Flowers', 'total' ),
			'url'   => $url . 'wild_flowers.png',
		),
		'wood_pattern' => array(
			'label' => esc_html__( 'Wood Pattern', 'total' ),
			'url'   => $url . 'wood_pattern.png',
		),
	) );
}

/**
 * Get social share items array used for Customizer setting and front-end output.
 *
 * @since 5.0
 */
function wpex_social_share_items() {
	$items = array(
        'twitter' => array(
            'li_class'   => 'wpex-twitter',
            'icon_class' => 'ticon ticon-twitter',
            'label'      => esc_html__( 'Tweet', 'total' ),
            'site'       => 'Twitter',
        ),
        'facebook' => array(
            'li_class'   => 'wpex-facebook',
            'icon_class' => 'ticon ticon-facebook',
            'label'      => esc_html__( 'Share', 'total' ),
            'site'       => 'Facebook',
        ),
        'pinterest' => array(
            'li_class'   => 'wpex-pinterest',
            'icon_class' => 'ticon ticon-pinterest',
            'label'      => esc_html__( 'Pin It', 'total' ),
            'site'       => 'Pinterest',
        ),
        'linkedin' => array(
            'li_class'   => 'wpex-linkedin',
            'icon_class' => 'ticon ticon-linkedin',
            'label'      => esc_html__( 'Share', 'total' ),
            'site'       => 'LinkedIn',
        ),
        'email' => array(
            'li_class'   => 'wpex-email',
            'icon_class' => 'ticon ticon-envelope',
            'label'      => esc_html__( 'Email', 'total' ),
            'site'       => 'Email',
        ),
    );
    $items = apply_filters( 'wpex_get_social_items', $items );
    return (array) apply_filters( 'wpex_social_share_items', $items );
}

/**
 * Return an array of template choices.
 *
 * @since 5.0
 */
function wpex_choices_dynamic_templates() {

	$choices = array(
		'' => '&#8211; ' . esc_html__( 'Select', 'total' ) . ' &#8211;',
	);

	$templates = new WP_Query( array(
		'posts_per_page' => -1,
		'post_type'      => apply_filters( 'wpex_singular_template_supported_post_types', array( 'templatera', 'elementor_library' ) ),
		'fields'         => 'ids',
	) );

	$templates = $templates->posts;

	if ( $templates ) {

		foreach ( $templates as $template ) {

			$label = get_the_title( $template ) . ' (' . get_post_type( $template ) . ')';

			$choices[sanitize_key( $template )] = wp_strip_all_tags( $label );

		}

	}

	return $choices;

}

/**
 * Return an array of theme defined widget areas.
 *
 * @since 5.0
 */
function wpex_choices_widget_areas() {

	$widget_areas = array(
		'' => esc_html__( 'Default', 'total' )
	);

	if ( function_exists( 'wpex_get_widget_areas' ) ) {
		$widget_areas = array_merge( $widget_areas, wpex_get_widget_areas() );
	}

	return $widget_areas;

}
