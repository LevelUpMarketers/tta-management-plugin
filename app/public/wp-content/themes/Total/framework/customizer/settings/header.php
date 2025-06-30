<?php
/**
 * Header Customizer Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$headers_w_aside = wpex_get_header_styles_with_aside_support();

$header_styles = wpex_get_header_styles();

$header_styles_no_dev = $header_styles;
unset( $header_styles_no_dev['dev'] );
$header_styles_no_dev = array_keys( $header_styles_no_dev );

/*-----------------------------------------------------------------------------------*/
/* - Header => General
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_header_general'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'panel' => 'wpex_header',
	'settings' => array(
		array(
			'id' => 'enable_header',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Site Header?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'full_width_header',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Full Width Header?', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'main_layout_style',
				'value' => 'full-width',
			),
		),
		array(
			'id' => 'header_style',
			'default' => 'one',
			'control' => array(
				'label' => esc_html__( 'Header Style', 'total' ),
				'type' => 'select',
				'choices' => $header_styles,
			),
		),
		array(
			'id' => 'vertical_header_style',
			'transport' => 'postMessage',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Vertical Header Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'fixed' => esc_html__( 'Fixed', 'total' ),
				),
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => array( 'six' ),
			),
		),
		array(
			'id' => 'vertical_header_width',
			'transport' => 'refresh',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Vertical Header Width (in pixels)', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '280px',
				),
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => array( 'six' ),
			),
		),
		array(
			'id' => 'header_top_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Top Padding', 'total' ),
				'input_attrs' => array(
					'placeholder' => '30px',
				),
			),
			'inline_css' => array(
				'target' => array(
					'#site-header #site-header-inner',
				),
				'alter' => 'padding-top',
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'header_bottom_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Bottom Padding', 'total' ),
				'input_attrs' => array(
					'placeholder' => '30px',
				),
			),
			'inline_css' => array(
				'target' => array(
					'#site-header #site-header-inner',
				),
				'alter' => 'padding-bottom',
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'header_background',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Background', 'total' ),
				'type' => 'color',
			),
			'inline_css' => array(
				'target' => array(
					'#site-header',
					'#site-header-sticky-wrapper',
					'#site-header-sticky-wrapper.is-sticky #site-header',
					'.footer-has-reveal #site-header',
					'#searchform-header-replace',
					'body.wpex-has-vertical-header #site-header',
				),
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'header_background_image',
			'control' => array(
				'type' => 'media',
				'mime_type' => 'image',
				'label' => esc_html__( 'Background Image', 'total' ),
			),
		),
		array(
			'id' => 'header_background_image_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Background Image Style', 'total' ),
				'type'  => 'select',
				'choices' => $bg_styles,
			),
		),
		/*** Aside ***/
		array(
			'id' => 'header_aside_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Aside', 'total' ),
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $headers_w_aside,
			),
		),
		array(
			'id' => 'header_aside_visibility',
			'transport' => 'postMessage',
			'default' => 'visible-desktop',
			'control' => array(
				'label' => esc_html__( 'Visibility', 'total' ),
				'type' => 'wpex-visibility-select',
			),
			'control_display' => array(
				'check'      => 'header_style',
				'value'      => $headers_w_aside,
			),
		),
		array(
			'id' => 'header_flex_items',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Vertical Align Aside Content', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => 'two',
			),
		),
		array(
			'id' => 'header_aside_search',
			'transport' => 'partialRefresh',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Header Aside Search', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => 'two',
			),
		),
		array(
			'id' => 'header_aside',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Header Aside Content', 'total' ),
				'type' => 'textarea',
				'description' => $post_id_content_desc,
			),
			'control_display' => array(
				'check'      => 'header_style',
				'value'      => $headers_w_aside,
			),
		),
	)
);

/*-----------------------------------------------------------------------------------*/
/* - Header => Logo
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_header_logo'] = array(
	'title' => esc_html__( 'Logo', 'total' ),
	'panel' => 'wpex_header',
	'settings' => array(
		array(
			'id' => 'logo_top_margin',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Top Padding', 'total' ),
				'input_attrs' => array(
					'placeholder' => '0px',
				),
			),
			'inline_css' => array(
				'target' => '#site-logo',
				'alter' => 'padding-top',
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'logo_bottom_margin',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Bottom Padding', 'total' ),
				'input_attrs' => array(
					'placeholder' => '0px',
				),
			),
			'inline_css' => array(
				'target' => '#site-logo',
				'alter' => 'padding-bottom',
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'logo_mobile_side_offset',
			'transport' => 'refresh', // this is an advanced CSS option because of RTL and custom mobile menu breakpoint.
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Mobile Side Offset', 'total' ),
				'description' => esc_html__( 'You can use this option to add an offset to your logo on the right side (or left for RTL layouts) if needed to prevent any content from overlapping your logo such as your mobile menu toggle when using a larger sized logo. Enter a pixel value such as 50px.', 'total' ),
			),
			'active_callback' => 'wpex_cac_has_image_logo',
		),
		array(
			'id' => 'logo_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_custom_logo',
			),
			'inline_css' => array(
				'target' => '#site-logo a.site-logo-text',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'logo_hover_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Hover Color', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_custom_logo',
			),
			'inline_css' => array(
				'target' => '#site-logo a.site-logo-text:hover',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'custom_logo',
			'control' => array(
				'label' => esc_html__( 'Image Logo', 'total' ),
				'type' => 'media',
				'mime_type' => 'image'
			),
		),
		array(
			'id' => 'logo_height',
			'control' => array(
				'label' => esc_html__( 'Height', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Used for retina and image height attribute tag.', 'total' ),
				'active_callback' => 'wpex_cac_has_image_logo',
			),
		),
		array(
			'id' => 'apply_logo_height',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Apply Height', 'total' ),
				'type' => 'checkbox',
				'description' => __( 'Check this box to apply your logo height to the image. Useful for displaying large logos at a smaller size. Note: If you have enabled the shrink sticky header style you need to alter your height value under the Sticky Header settings.', 'total' ),
				'active_callback' => 'wpex_cac_has_image_logo',
			),
		),
		array(
			'id' => 'logo_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Width', 'total' ),
				'description' => esc_html__( 'Used for image width attribute tag.', 'total' ),
				'type' => 'text',
				'active_callback' => 'wpex_cac_has_image_logo',
			),
		),
		array(
			'id' => 'retina_logo',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Retina Image Logo', 'total' ),
				'type' => 'media',
				'mime_type' => 'image',
				'active_callback' => 'wpex_cac_has_image_logo',
			),
		),
		array(
			'id' => 'logo_max_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Logo Max Width: Desktop', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Screens 960px wide and greater.', 'total' ),
				'active_callback' => 'wpex_cac_has_image_logo',
			),
			'inline_css' => array(
				'media_query' => '(min-width: 960px)',
				'target' => '#site-logo img',
				'alter' => 'max-width',
			),
		),
		array(
			'id' => 'logo_max_width_tablet_portrait',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Logo Max Width: Tablet Portrait', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Screens 768px-959px wide.', 'total' ),
				'active_callback' => 'wpex_cac_has_image_logo',
			),
			'inline_css' => array(
				'media_query' => '(min-width: 768px) and (max-width: 959px)',
				'target' => '#site-logo img',
				'alter' => 'max-width',
			),
		),
		array(
			'id' => 'logo_max_width_phone',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Logo Max Width: Phone', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Screens smaller than 767px wide.', 'total' ),
				'active_callback' => 'wpex_cac_has_image_logo',
			),
			'inline_css' => array(
				'media_query' => '(max-width: 767px)',
				'target' => '#site-logo img',
				'alter' => 'max-width',
			),
		),
	)
);

/*-----------------------------------------------------------------------------------*/
/* - Header => Logo Icon
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_header_logo_icon'] = array(
	'title' => esc_html__( 'Logo Icon', 'total' ),
	'panel' => 'wpex_header',
	'settings' => array(
		array(
			'id' => 'logo_icon',
			'transport' => 'postMessage',
			'default' => 'none',
			'control' => array(
				'label' => esc_html__( 'Icon Select', 'total' ),
				'type' => 'wpex-fa-icon-select',
				'active_callback' => 'wpex_cac_hasnt_custom_logo',
			),
		),
		array(
			'id' => 'logo_icon_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Logo Icon Color', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_custom_logo',
			),
			'inline_css' => array(
				'target' => '#site-logo-fa-icon',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'logo_icon_right_margin',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Logo Icon Right Margin', 'total' ),
				'description' => $pixel_desc,
				'active_callback' => 'wpex_cac_hasnt_custom_logo',
			),
			'inline_css' => array(
				'target' => '#site-logo-fa-icon',
				'alter' => 'margin-right',
			),
		),
	)
);

/*-----------------------------------------------------------------------------------*/
/* - Header => Fixed Menu
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_header_overlay'] = array(
	'title' => esc_html__( 'Overlay/Transparent Header', 'total' ),
	'panel' => 'wpex_header',
	'settings' => array(
		array(
			'id' => 'overlay_header',
			'transport' => 'refresh',
			'control' => array(
				'label' => esc_html__( 'Enable?', 'total' ),
				'type' => 'checkbox',
				'description' => esc_html__( 'When enabled your header will be placed over your site content. Note: Only certain header styles support this function, if you are using a non-supported style such as the vertical header the theme will swap to Header Style One.', 'total' ),
			),
		),
		array(
			'id' => 'overlay_header_style',
			'transport' => 'refresh',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Style', 'total' ),
				'type' => 'select',
				'choices' => wpex_header_overlay_styles(),
				'description' => esc_html__( 'By default the overlay header makes your menu items white and excludes certain customizer options to prevent design issues. However all the default header and menu settings will be used when your header becomes fixed/sticky. If you wish to include all customizer modifications made to the header and menu for the Overlay/Transparent header simply select the "Core Styles" option.', 'total' ),
			),
		),
		array(
			'id' => 'overlay_header_template',
			'transport' => 'refresh',
			'control' => array(
				'label' => esc_html__( 'Template', 'total' ),
				'type' => 'wpex-dropdown-templates',
				'description' => esc_html__( 'If you wish to display a template beneath your header you can select it here.', 'total' ),
			),
		),
	),
);

/*-----------------------------------------------------------------------------------*/
/* - Header => Sticky Header
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_header_fixed'] = array(
	'title' => esc_html__( 'Sticky Header', 'total' ),
	'panel' => 'wpex_header',
	'settings' => array(
		array(
			'id' => 'fixed_header_style',
			'transport' => 'refresh',
			'default' => 'standard',
			'sanitize_callback' => 'esc_html',
			'control' => array(
				'label' => esc_html__( 'Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'disabled' => esc_html__( 'Disabled', 'total' ),
					'standard' => esc_html__( 'Standard', 'total' ),
					'shrink' => esc_html__( 'Shrink', 'total' ),
					'shrink_animated' => esc_html__( 'Animated Shrink (Best with Image Logo)', 'total' ),
				),
				'active_callback' => 'wpex_cac_header_supports_fixed_header',
			),
		),
		array(
			'id' => 'fixed_header_mobile',
			'sanitize_callback' => 'esc_html',
			'control' => array(
				'label' => esc_html__( 'Enable Mobile Support?', 'total' ),
				'desc' => esc_html__( 'If disabled the sticky header will only function on desktops.', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_header_supports_fixed_header',
			),
		),
		array(
			'id' => 'fixed_header_start_position',
			'sanitize_callback' => 'esc_html',
			'control' => array(
				'label' => esc_html__( 'Sticky Start Position', 'total' ),
				'type' => 'text',
				'active_callback' => 'wpex_cac_header_supports_fixed_header',
				'description' => esc_html__( 'By default, the header becomes sticky as soon as you reach the header while scrolling. You can use this field to enter a number (in pixels) to offset the point at which the header becomes sticky (based on the top of the page) or the classname or ID of another element so that the header becomes sticky when it reaches that point (example: #my-custom-div).', 'total' ),
			),
		),
		array(
			'id' => 'fixed_header_shrink_start_height',
			'sanitize_callback' => 'absint',
			'default' => 60,
			'control' => array(
				'label' => esc_html__( 'Logo Start Height', 'total' ),
				'type' => 'number',
				'description' => esc_html__( 'In order to properly animate the header with CSS3 it is important to apply a fixed height to the header logo by default.', 'total' ),
				'active_callback' => 'wpex_cac_has_fixed_header_shrink',
			),
		),
		array(
			'id' => 'fixed_header_shrink_end_height',
			'default' => 50,
			'sanitize_callback' => 'absint',
			'control' => array(
				'label' => esc_html__( 'Logo Shrunk Height', 'total' ),
				'type' => 'number',
				'active_callback' => 'wpex_cac_has_fixed_header_shrink',
				'description' => esc_html__( 'Your shrink header height will be set to your Logo Shrunk Height plus 20px for a top and bottom padding of 10px.', 'total' ),
			),
		),
		array(
			'id' => 'fixed_header_opacity',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'number',
				'label' => esc_html__( 'Opacity', 'total' ),
				'active_callback' => 'wpex_cac_has_fixed_header',
				'input_attrs' => array(
					'min'  => 0.1,
					'max'  => 1,
					'step' => 0.1,
				),
			),
			'inline_css' => array(
				'target' => '.wpex-sticky-header-holder.is-sticky #site-header',
				'alter' => 'opacity',
			),
		),
		array(
			'id' => 'fixed_header_logo',
			'sanitize_callback' => 'absint',
			'control' => array(
				'label' => esc_html__( 'Custom Logo', 'total' ),
				'type' => 'media',
				'mime_type' => 'image',
				'active_callback' => 'wpex_cac_supports_fixed_header_logo',
				'description' => esc_html__( 'If this custom logo is a different size, for best results go to the Logo section and apply a custom height to your logo.', 'total' ),
			),
		),
		array(
			'id' => 'fixed_header_logo_retina',
			'sanitize_callback' => 'absint',
			'control' => array(
				'label' => esc_html__( 'Custom Logo Retina', 'total' ) .' '. esc_html__( 'Retina', 'total' ),
				'type' => 'media',
				'mime_type' => 'image',
				'active_callback' => 'wpex_cac_has_fixed_header_logo',
			),
		),
		array(
			'id' => 'fixed_header_logo_retina_height',
			'sanitize_callback' => 'absint',
			'control' => array(
				'label' => esc_html__( 'Custom Logo Retina Height', 'total' ),
				'type' => 'number',
				'active_callback' => 'wpex_supports_fixed_header_logo_retina_height',
			),
			'inline_css' => array(
				'target' => 'body.wpex-is-retina #site-header-sticky-wrapper.is-sticky #site-logo img',
				'alter' => 'height',
			),
		),
	)
);

/*-----------------------------------------------------------------------------------*/
/* - Header => Menu
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_header_menu'] = array(
	'title' => esc_html__( 'Menu', 'total' ),
	'panel' => 'wpex_header',
	'settings' => array(
		array(
			'id' => 'menu_active_underline',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Enable Hover & Active Underline?', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => array( 'one', 'two', 'three', 'four', 'five' ),
			),
		),
		array(
			'id' => 'menu_arrow_down',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Display Top Level Dropdown Icon?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'menu_arrow_side',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Second+ Level Dropdown Icon?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'header_menu_disable_borders',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Disable Menu Inner Borders?', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => array( 'two', 'six' ),
			),
		),
		array(
			'id' => 'header_menu_center',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Center Menu Items?', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => 'two',
			),
		),
		array(
			'id' => 'header_menu_stretch_items',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Stretch Menu Items?', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => array( 'two', 'three', 'four' ),
			),
		),
		array(
			'id' => 'menu_dropdown_top_border',
			//'transport' => 'postMessage', // Can't cause it has dependent options
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Enable Dropdown Colored Top Border?', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => array( 'one', 'two', 'three', 'four', 'five', 'six' ),
			),
		),
		array(
			'id' => 'menu_flush_dropdowns',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Full-Height Menu Items?', 'total' ),
				'type' => 'checkbox',
				'description' => esc_html__( 'When enabled your menu li elements will display at the same height as your header so that your dropdowns line up with the bottom of the header.', 'total' ),
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => 'one',
			),
		),
		array(
			'id' => 'menu_arrow',
			'control' => array(
				'label' => esc_html__( 'Dropdown Icon Type', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'angle' => esc_html__( 'Angle', 'total' ),
					'angle-double' => esc_html__( 'Angle Double', 'total' ),
					'chevron' => esc_html__( 'Chevron', 'total' ),
					'caret' => esc_html__( 'Caret', 'total' ),
					'arrow' => esc_html__( 'Arrow', 'total' ),
					'arrow-circle' => esc_html__( 'Arrow Circle', 'total' ),
				),
			),
		),
		array(
			'id' => 'menu_dropdown_style',
			'transport' => 'postMessage',
			'default' => 'default',
			'control' => array(
				'label' => esc_html__( 'Dropdown Style', 'total' ),
				'type' => 'select',
				'choices' => wpex_get_menu_dropdown_styles(),
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'menu_dropdown_dropshadow',
			'transport' => 'postMessage',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Dropdown Dropshadow Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'None', 'total' ),
					'one' => esc_html__( 'One', 'total' ),
					'two' => esc_html__( 'Two', 'total' ),
					'three' => esc_html__( 'Three', 'total' ),
					'four' => esc_html__( 'Four', 'total' ),
					'five' => esc_html__( 'Five', 'total' ),
				),
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'menu_li_left_margin',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Menu Items Left Margin', 'total' ),
				'description' => esc_html__( 'Can be used to increase the spacing between your items. Value in pixels.', 'total' ),
				'input_attrs' => array(
					'placeholder' => '0px',
				),
			),
			'inline_css' => array(
				'target' => 'body .navbar-style-one .dropdown-menu > li.menu-item',
				'alter' => 'margin-left',
				'sanitize' => 'px',
				'important' => true, // prevents issues with the active/hover underline border
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => 'one',
			),
		),
		array(
			'id' => 'menu_a_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Menu Items Left/Right Padding', 'total' ),
				'description' => esc_html__( 'Can be used to increase the spacing between your items. Value in pixels.', 'total' ),
			),
			'inline_css' => array(
				'target' => array(
					'body .navbar-style-two .dropdown-menu > li.menu-item > a',
					'body .navbar-style-three .dropdown-menu > li.menu-item > a',
					'body .navbar-style-four .dropdown-menu > li.menu-item > a',
					'body .navbar-style-five .dropdown-menu > li.menu-item > a',
				),
				'alter' => array( 'padding-left', 'padding-right' ),
				'sanitize' => 'px',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => array( 'two', 'three', 'four', 'five' ),
			),
		),

		/*** Main Styling ***/
		array(
			'id' => 'menu_main_styling_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Styling: Main', 'total' ),
			),
		),
		array(
			'id' => 'menu_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => array(
					'#site-navigation-wrap',
					'#site-navigation-sticky-wrapper.is-sticky #site-navigation-wrap',
				),
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'menu_borders',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Borders', 'total' ),
				'description' => esc_html__( 'Not all menus have borders, but this setting is for those that do', 'total' ),
			),
			'inline_css' => array(
				'target' => array(
					'#site-navigation > ul li.menu-item',
					'#site-navigation a',
					'#site-navigation ul',
					'#site-navigation-wrap',
					'#site-navigation',
					'.navbar-style-six #site-navigation',
					'#site-navigation-sticky-wrapper.is-sticky #site-navigation-wrap',
				),
				'alter' => 'border-color',
			),
		),
		// Menu Link Colors
		array(
			'id' => 'menu_link_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-navigation .dropdown-menu > li.menu-item > a',
				'alter' => 'color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'menu_link_color_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-navigation .dropdown-menu > li.menu-item > a:hover,
							#site-navigation .dropdown-menu > li.menu-item.dropdown.sfHover > a',
				'alter' => 'color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'menu_link_color_active',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color: Current Menu Item', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-navigation .dropdown-menu > li.menu-item.current-menu-item > a,
							#site-navigation .dropdown-menu > li.menu-item.current-menu-parent > a',
				'alter' => 'color',
				//'important' => true, // removed in 4.4.1 - causes issues with superfish hover settings
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		// Link Background
		array(
			'id' => 'menu_link_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-navigation .dropdown-menu > li.menu-item > a',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'menu_link_hover_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Background: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-navigation .dropdown-menu > li.menu-item > a:hover,
							#site-navigation .dropdown-menu > li.menu-item.dropdown.sfHover > a',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'menu_link_active_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Background: Current Menu Item', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-navigation .dropdown-menu > li.menu-item.current-menu-item > a,
							#site-navigation .dropdown-menu > li.menu-item.current-menu-parent > a',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		// Link Inner
		array(
			'id' => 'menu_link_span_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Inner Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-navigation .dropdown-menu > li.menu-item > a > span.link-inner',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'menu_link_span_hover_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Inner Background: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-navigation .dropdown-menu > li.menu-item > a:hover > span.link-inner,
							#site-navigation .dropdown-menu > li.menu-item.dropdown.sfHover > a > span.link-inner',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'menu_link_span_active_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Inner Background: Current Menu Item', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-navigation .dropdown-menu > li.menu-item.current-menu-item > a > span.link-inner,
							#site-navigation .dropdown-menu > li.menu-item.current-menu-parent > a > span.link-inner',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),

		/**** Dropdown Styling ****/
		array(
			'id' => 'menu_dropdowns_styling_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Styling: Dropdowns', 'total' ),
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),

		// Menu Dropdowns
		array(
			'id' => 'dropdown_menu_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-header #site-navigation .dropdown-menu ul.sub-menu',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		// Pointer
		array(
			'id' => 'dropdown_menu_pointer_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Pointer Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '.wpex-dropdowns-caret .dropdown-menu ul.sub-menu::after',
				'alter' => 'border-bottom-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'dropdown_menu_pointer_border',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Pointer Border', 'total' ),
			),
			'inline_css' => array(
				'target' => '.wpex-dropdowns-caret .dropdown-menu ul.sub-menu::before',
				'alter' => 'border-bottom-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		// Borders
		array(
			'id' => 'dropdown_menu_borders',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Dropdown Borders', 'total' ),
			),
			'inline_css' => array(
				'target' => array(
					'#site-header #site-navigation .dropdown-menu ul.sub-menu',
					'#site-header #site-navigation .dropdown-menu ul.sub-menu li.menu-item',
					'#site-header #site-navigation .dropdown-menu ul.sub-menu li.menu-item a',
				),
				'alter' => 'border-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'menu_dropdown_top_border_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Colored Top Border', 'total' ),
				'active_callback' => 'wpex_cac_has_menu_dropdown_top_border',
			),
			'inline_css' => array(
				'target' => array(
					'.wpex-dropdown-top-border #site-navigation .dropdown-menu li.menu-item ul.sub-menu',
					'.header-drop-widget',
				),
				'alter' => 'border-top-color',
				'important' => true,
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		// Link color
		array(
			'id' => 'dropdown_menu_link_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-header #site-navigation .dropdown-menu ul.sub-menu > li.menu-item > a',
				'alter' => 'color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'dropdown_menu_link_color_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-header #site-navigation .dropdown-menu ul.sub-menu > li.menu-item > a:hover',
				'alter' => 'color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'dropdown_menu_link_hover_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Background: Hover', 'total' ),
			),
			'subtitle' => esc_html__( 'Select your custom hex color.', 'total' ),
			'inline_css' => array(
				'target' => '#site-header #site-navigation .dropdown-menu ul.sub-menu > li.menu-item > a:hover',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		// Current item
		array(
			'id' => 'dropdown_menu_link_color_active',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color: Current Menu Item', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-header #site-navigation .dropdown-menu ul.sub-menu > li.menu-item.current-menu-item > a',
				'alter' => 'color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		array(
			'id' => 'dropdown_menu_link_bg_active',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Background: Current Menu Item', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-header #site-navigation .dropdown-menu ul.sub-menu > li.menu-item.current-menu-item > a',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
		// Mega menu
		array(
			'id' => 'mega_menu_title',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Megamenu Subtitle Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-header #site-navigation .sf-menu > li.megamenu > ul.sub-menu > .menu-item-has-children > a',
				'alter' => 'color',
			),
			'control_display' => array(
				'check' => 'header_style',
				'value' => $header_styles_no_dev,
			),
		),
	)
);

/*-----------------------------------------------------------------------------------*/
/* - Header => Menu Search Form
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_menu_search'] = array(
	'title' => esc_html__( 'Menu Search', 'total' ),
	'panel' => 'wpex_header',
	'settings' => array(
		array(
			'id' => 'menu_search_style',
			'default' => 'drop_down',
			'control' => array(
				'label' => esc_html__( 'Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'disabled' => esc_html__( 'Disabled','total' ),
					'drop_down' => esc_html__( 'Drop Down','total' ),
					'overlay' => esc_html__( 'Site Overlay','total' ),
					'header_replace' => esc_html__( 'Header Replace','total' )
				),
				'description' => esc_html__( 'The vertical header may not support all styles.', 'total' ),
			),
		),
		array(
			'id' => 'search_dropdown_top_border',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Drop Down Top Border', 'total' ),
				'type' => 'color',
			),
			'inline_css' => array(
				'target' => '#searchform-dropdown',
				'alter' => 'border-top-color',
				'important' => true,
			),
		),
	)
);

/*-----------------------------------------------------------------------------------*/
/* - Header => Fixed Menu
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_fixed_menu'] = array(
	'title' => esc_html__( 'Sticky Menu', 'total' ),
	'panel' => 'wpex_header',
	'settings' => array(
		array(
			'id' => 'fixed_header_menu',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Sticky Header Menu', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_header_supports_fixed_menu',
			),
		),
	)
);

/*-----------------------------------------------------------------------------------*/
/* - Header => Mobile Menu
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_header_mobile_menu'] = array(
	'title' => esc_html__( 'Mobile Menu', 'total' ),
	'panel' => 'wpex_header',
	'settings' => array(
		// Breakpoint
		array(
			'id' => 'mobile_menu_breakpoint',
			'control' => array(
				'label' => esc_html__( 'Mobile Menu Breakpoint', 'total' ),
				'description' => esc_html__( 'Enter a custom viewport width in pixels for when the default menu will become the mobile menu. Enter 9999 to display the mobile menu always.', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '959px',
				),
			),
		),
		// Search
		array(
			'id' => 'mobile_menu_search',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Searchform in Mobile Menu?', 'total' ),
				'type' => 'checkbox',
			),
		),
		/*** Mobile Menu > Toggle Style ***/
		array(
			'id' => 'mobile_menu_toggle_style',
			'default' => 'icon_buttons',
			'control' => array(
				'label' => esc_html__( 'Toggle Button Style', 'total' ),
				'type' => 'select',
				'active_callback' => 'wpex_cac_mobile_menu_toggle_style',
				'choices' => array(
					'icon_buttons' => esc_html__( 'Right Aligned Icon Button(s)', 'total' ),
					'icon_buttons_under_logo' => esc_html__( 'Under The Logo Icon Button(s)', 'total' ),
					'navbar' => esc_html__( 'Navbar', 'total' ),
					'fixed_top'  => esc_html__( 'Fixed Site Top', 'total' ),
					'custom'  => esc_html__( 'Custom', 'total' ),
				),
				'desc' => esc_html__( 'If you select "custom" the theme will load the needed code for your mobile menu which you can then open/close by adding any link to the page with the classname "mobile-menu-toggle".', 'total' )
			),
		),
		array(
			'id' => 'mobile_menu_navbar_position',
			'default' => 'wpex_hook_header_bottom',
			'control' => array(
				'label' => esc_html__( 'Menu Position', 'total' ),
				'type' => 'select',
				'active_callback' => 'wpex_cac_is_mobile_navbar',
				'choices' => array(
					'wpex_hook_header_bottom' => esc_html__( 'Header Bottom', 'total' ),
					'outer_wrap_before' => esc_html__( 'Top of site', 'total' ),
				),
			),
		),
		array(
			'id' => 'mobile_menu_toggle_fixed_top_bg',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Toggle Background', 'total' ),
				'type' => 'color',
				'active_callback' => 'wpex_cac_is_mobile_fixed_or_navbar',
			),
			'inline_css' => array(
				'target' => '#wpex-mobile-menu-fixed-top, #wpex-mobile-menu-navbar',
				'alter' => 'background',
			),
		),
		array(
			'id' => 'mobile_menu_toggle_text',
			'transport' => 'postMessage',
			'default' => esc_html__( 'Menu', 'total' ),
			'control' => array(
				'label' => esc_html__( 'Toggle Text', 'total' ),
				'type' => 'text',
				'active_callback' => 'wpex_cac_is_mobile_fixed_or_navbar',
			),
		),
		/*** Mobile Menu > Style */
		array(
			'id' => 'mobile_menu_style',
			'default' => 'sidr',
			'control' => array(
				'label' => esc_html__( 'Mobile Menu Style', 'total' ),
				'type' => 'select',
				'choices' => wpex_get_mobile_menu_styles(),
			),
		),
		array(
			'id' => 'full_screen_mobile_menu_style',
			'default' => 'white',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Style', 'total' ),
				'type' => 'select',
				'active_callback' => 'wpex_cac_mobile_menu_is_full_screen',
				'choices' => array(
					'white'	=> esc_html__( 'White', 'total' ),
					'black'	=> esc_html__( 'Black', 'total' ),
				),
			),
		),
		array(
			'id' => 'mobile_menu_sidr_direction',
			'default' => 'right',
			'control' => array(
				'label' => esc_html__( 'Direction', 'total' ),
				'type' => 'select',
				'active_callback' => 'wpex_cac_mobile_menu_is_sidr',
				'choices' => array(
					'right'	=> esc_html__( 'Right', 'total' ),
					'left'	=> esc_html__( 'Left', 'total' ),
				),
			),
		),
		array(
			'id' => 'mobile_menu_sidr_displace',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Enable Site Displacement?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_mobile_menu_is_sidr',
				'description' => esc_html__( 'Enable to display/push the site content over when opening the sidebar mobile menu.', 'total' ),
			),
		),
		/*** Mobile Menu > Mobile Icons Styling ***/
		array(
			'id' => 'mobile_menu_icons_styling',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Icons Styling', 'total' ),
				'active_callback' => 'wpex_cac_has_mobile_menu_icons',
			),
		),

		/* @deprecated in v4.5.5
		array(
			'id' => 'mobile_menu_icon_size',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Font Size', 'total' ),
				'active_callback' => 'wpex_cac_has_mobile_menu_icons',
				'description' => $pixel_desc,
			),
			'inline_css' => array(
				'target' => '#mobile-menu a',
				'alter' => 'font-size',
				'sanitize' => 'px',
			),
		),*/

		array(
			'id' => 'mobile_menu_icon_color',
			'transport' => 'refresh',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
				'active_callback' => 'wpex_cac_has_mobile_menu_icons',
			),
			'inline_css' => array(
				'target' => '#mobile-menu a',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'mobile_menu_icon_color_hover',
			'transport' => 'refresh',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color: Hover', 'total' ),
				'active_callback' => 'wpex_cac_has_mobile_menu_icons',
			),
			'inline_css' => array(
				'target' => '#mobile-menu a:hover',
				'alter' => 'color',
			),
		),

		/* @deprecated in v4.5.5
		array(
			'id' => 'mobile_menu_icon_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
				'active_callback' => 'wpex_cac_has_mobile_menu_icons',
			),
			'inline_css' => array(
				'target' => '#mobile-menu a',
				'alter' => 'background',
			),
		),
		array(
			'id' => 'mobile_menu_icon_background_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background: Hover', 'total' ),
				'active_callback' => 'wpex_cac_has_mobile_menu_icons',
			),
			'inline_css' => array(
				'target' => '#mobile-menu a:hover',
				'alter' => 'background',
			),
		),
		array(
			'id' => 'mobile_menu_icon_border',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Border', 'total' ),
				'active_callback' => 'wpex_cac_has_mobile_menu_icons',
			),
			'inline_css' => array(
				'target' => '#mobile-menu a',
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'mobile_menu_icon_border_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Border: Hover', 'total' ),
				'active_callback' => 'wpex_cac_has_mobile_menu_icons',
			),
			'inline_css' => array(
				'target' => '#mobile-menu a:hover',
				'alter' => 'border-color',
			),
		), */

		/*** Mobile Menu > Sidr ***/
		array(
			'id' => 'mobile_menu_sidr_styling',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Sidebar Menu Styling', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_sidr',
			),
		),
		array(
			'id' => 'mobile_menu_sidr_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_sidr',
			),
			'inline_css' => array(
				'target' => '#sidr-main',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'mobile_menu_sidr_borders',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Borders', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_sidr',
			),
			'inline_css' => array(
				'target' => '#sidr-main li, #sidr-main ul, .sidr-class-mobile-menu-searchform input, .sidr-class-mobile-menu-searchform',
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'mobile_menu_links',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_sidr',
			),
			'inline_css' => array(
				'target' => '#sidr-main',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'mobile_menu_links_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Links: Hover', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_sidr',
			),
			'inline_css' => array(
				'target' => '.sidr a:hover,.sidr-class-menu-item-has-children.active > a',
				'alter' => 'color',
			),
		),

		/*** Mobile Menu > Toggle Menu ***/
		array(
			'id' => 'mobile_menu_toggle_styling',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Toggle Menu Styling', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_toggle',
			),
		),
		array(
			'id' => 'toggle_mobile_menu_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_toggle',
			),
			'inline_css' => array(
				'target' => array(
					'.mobile-toggle-nav',
					'.wpex-mobile-toggle-menu-fixed_top .mobile-toggle-nav',
				),
				'alter' => 'background',
			),
		),
		array(
			'id' => 'toggle_mobile_menu_borders',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Borders', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_toggle',
			),
			'inline_css' => array(
				'target' => array(
					'.mobile-toggle-nav a',
					'.wpex-mobile-toggle-menu-fixed_top .mobile-toggle-nav a',
				),
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'toggle_mobile_menu_links',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Links', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_toggle',
			),
			'inline_css' => array(
				'target' => array(
					'.mobile-toggle-nav a',
					'.wpex-mobile-toggle-menu-fixed_top .mobile-toggle-nav a',
				),
				'alter' => 'color',
			),
		),
		array(
			'id' => 'toggle_mobile_menu_links_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Links: Hover', 'total' ),
				'active_callback' => 'wpex_cac_mobile_menu_is_toggle',
			),
			'inline_css' => array(
				'target' => array(
					'.mobile-toggle-nav a:hover',
					'.wpex-mobile-toggle-menu-fixed_top .mobile-toggle-nav a:hover',
				),
				'alter' => 'color',
			),
		),
	),
);