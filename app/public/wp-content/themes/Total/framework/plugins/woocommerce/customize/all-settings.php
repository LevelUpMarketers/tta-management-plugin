<?php
/**
 * WooCommerce Customizer Settings
 *
 * @package Total WordPress Theme
 * @subpackage WooCommerce
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Strings
$refresh_desc = esc_html__( 'You must save your options and refresh your live site to preview changes to this setting. You may have to also add or remove an item from the cart to clear the WooCommerce cache.', 'total' );
$refresh_desc_2 = esc_html__( 'You must save your options and refresh your live site to preview changes to this setting.', 'total' );

// General
$this->sections['wpex_woocommerce_general'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'panel' => 'wpex_woocommerce',
	'settings' => array(
		array(
			'id' => 'woo_custom_sidebar',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Custom WooCommerce Sidebar?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_header_product_searchform',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Use product searchform for header search?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_show_og_price',
			'default' => true,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Show Original Price on Sale Items?', 'total' ),
				'type' => 'checkbox',
			),
			'inline_css' => array(
				'target' => '.woocommerce ul.products li.product .price del,.woocommerce div.product div.summary del',
				'alter' => 'display',
				'sanitize' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_sale_flash_text',
			'control' => array(
				'label' => esc_html__( 'On Sale Text', 'total' ),
				'type' => 'text',
			),
		),
	)
);

// Header Cart
$this->sections['wpex_woocommerce_menu_cart'] = array(
	'title' => esc_html__( 'Header Menu Cart', 'total' ),
	'panel' => 'wpex_woocommerce',
	'description' =>$refresh_desc,
	'settings' => array(
		array(
			'id' => 'woo_menu_icon_display',
			'default' => 'icon_count',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Menu Cart: Display', 'total' ),
				'type' => 'select',
				'choices' => array(
					'disabled' => esc_html__( 'Disabled', 'total' ),
					'icon' => esc_html__( 'Icon', 'total' ),
					'icon_total' => esc_html__( 'Icon And Cart Total', 'total' ),
					'icon_count' => esc_html__( 'Icon And Cart Count', 'total' ),
				),
			),
		),
		array(
			'id' => 'wpex_woo_menu_icon_bubble',
			'transport' => 'postMessage',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable "bubble" design for cart count', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'woo_menu_icon_display',
				'value' => 'icon_count',
			),
		),
		array(
			'id' => 'woo_menu_icon_class',
			'default' => 'shopping-cart',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Shop Icon', 'total' ),
				'type' => 'select',
				'choices' => array(
					'shopping-cart' => esc_html__( 'Shopping Cart', 'total' ),
					'shopping-bag' => esc_html__( 'Shopping Bag', 'total' ),
					'shopping-basket' => esc_html__( 'Shopping Basket', 'total' ),
				),
			),
		),
		array(
			'id' => 'woo_menu_icon_style',
			'default' => 'drop_down',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Menu Cart: Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'drop_down' => esc_html__( 'Drop-Down', 'total' ),
					'overlay' => esc_html__( 'Open Cart Overlay', 'total' ),
					'store' => esc_html__( 'Go To Store', 'total' ),
					'custom-link' => esc_html__( 'Custom Link', 'total' ),
				),
			),
		),
		array(
			'id' => 'woo_menu_icon_custom_link',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Menu Cart: Custom Link', 'total' ),
				'type' => 'text',
			),
		),
	)
);

// Archives
$this->sections['wpex_woocommerce_archives'] = array(
	'title' => esc_html__( 'Shop & Archives', 'total' ),
	'panel' => 'wpex_woocommerce',
	'settings' => array(
		array(
			'id' => 'woo_shop_title',
			'default' => 'on',
			'control' => array(
				'label' => esc_html__( 'Display Shop Title?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_shop_disable_default_output',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Disable Default Shop Output?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_added_to_cart_notice',
			'transport' => 'postMessage',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Added to Cart Popup on Shop Archives?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_shop_posts_per_page',
			'default' => '12',
			'control' => array(
				'label' => esc_html__( 'Products Per Page', 'total' ),
				'type' => 'text',
				'desc' => esc_html__( 'You must save your options and refresh your live site to preview changes to this setting.', 'total' ),
			),
		),
		array(
			'id' => 'woo_shop_layout',
			'default' => 'full-width',
			'control' => array(
				'label' => esc_html__( 'Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'woocommerce_shop_columns',
			'default' => '4',
			'control' => array(
				'label' => esc_html__( 'Columns', 'total' ),
				'type' => 'wpex-columns',
			),
		),
		array(
			'id' => 'woo_shop_columns_gap',
			'control' => array(
				'label' => esc_html__( 'Gap', 'total' ),
				'type' => 'select',
				'choices' => wpex_column_gaps(),
			),
		),
		array(
			'id' => 'woo_category_description_position',
			'default' => 'under_title',
			'control' => array(
				'label' => esc_html__( 'Category Description Position', 'total' ),
				'type' => 'select',
				'choices' => array(
					''			  => esc_html__( 'Default', 'total' ),
					'under_title' => esc_html__( 'Under Title', 'total' ),
					'above_loop' => esc_html__( 'Before Entries', 'total' ),
				),

			),
		),
		array(
			'id' => 'woo_shop_sort',
			'default' => 'on',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Display Sort Dropdown?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'You must save your options and refresh your live site to preview changes to this setting.', 'total' ),
			),
		),
		array(
			'id' => 'woo_shop_result_count',
			'default' => 'on',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Shop Result Count?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'You must save your options and refresh your live site to preview changes to this setting.', 'total' ),
			),
		),

		// Entry settings
		array(
			'id' => 'woo_entry_settings_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Product Entry', 'total' ),
			),
		),
		array(
			'id' => 'woo_entry_align',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Entry Alignment', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'Default','total' ),
					'left' => esc_html__( 'Left','total' ),
					'right' => esc_html__( 'Right','total' ),
					'center' => esc_html__( 'Center','total' ),
				),
			),
			'inline_css' => array(
				'target' => '.woocommerce .products .product',
				'alter' => 'text-align',
			),
		),
		array(
			'id' => 'woo_product_entry_style',
			'default' => 'image-swap',
			'control' => array(
				'label' => esc_html__( 'Entry Media Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'featured-image' => esc_html__( 'Featured Image', 'total' ),
					'image-swap' => esc_html__( 'Image Swap', 'total' ),
					'gallery-slider' => esc_html__( 'Gallery Slider', 'total' ),
				),
			),
		),
		array(
			'id' => 'woo_entry_equal_height',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Equal Height Entries?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_show_entry_title',
			'default' => true,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Entry Title?', 'total' ),
				'type' => 'checkbox',
			),
			'inline_css' => array(
				'target' => '.woocommerce ul.products li.product .woocommerce-loop-product__title',
				'alter' => 'display',
				'sanitize' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_show_entry_rating',
			'default' => true,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Display Product Rating?', 'total' ),
				'type' => 'checkbox',
			),
			'inline_css' => array(
				'target' => '.woocommerce ul.products li.product .star-rating',
				'alter' => 'display',
				'sanitize' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_show_entry_price',
			'default' => true,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Entry Price?', 'total' ),
				'type' => 'checkbox',
			),
			'inline_css' => array(
				'target' => '.woocommerce ul.products li.product .price',
				'alter' => 'display',
				'sanitize' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_show_entry_add_to_cart',
			'default' => true,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Add to Cart Button?', 'total' ),
				'type' => 'checkbox',
			),
			'inline_css' => array(
				'target' => '.woocommerce ul.products li.product a.button',
				'alter' => 'display',
				'sanitize' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_default_entry_buttons',
			'default' => false,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Always Visible Add to Cart Button?', 'total' ),
				'type' => 'checkbox',
				'desc' => $refresh_desc_2,
			),
		),
	)
);

// Single
$this->sections['wpex_woocommerce_single'] = array(
	'title' => esc_html__( 'Single Product', 'total' ),
	'panel' => 'wpex_woocommerce',
	'settings' => array(
		array(
			'id' => 'woo_shop_single_title',
			'default' => esc_html__( 'Store', 'total' ),
			'control' => array(
				'label' => esc_html__( 'Page Header Title', 'total' ),
				'type' => 'text',
			),
		),
		array(
			'id' => 'woo_product_layout',
			'default' => 'full-width',
			'control' => array(
				'label' => esc_html__( 'Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'woo_product_gallery_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Product Gallery Width', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Default:', 'total' ) .' 52%',
			),
			'inline_css' => array(
				'target' => '.woocommerce div.product div.images, .woocommerce-page div.product div.images',
				'alter' => array( 'width' ),
			),
		),
		array(
			'id' => 'woo_product_summary_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Product Summary Width', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Default:', 'total' ) .' 44%',
			),
			'inline_css' => array(
				'target' => '.woocommerce .product .summary',
				'alter' => array( 'width' ),
			),
		),
		array(
			'id' => 'woo_product_gallery_slider',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Product Gallery Slider', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_product_gallery_slider_animation_speed',
			'default'  => '600',
			'control' => array(
				'label' => esc_html__( 'Product Gallery Slider Animation Speed', 'total' ),
				'type' => 'text',
				'desc' => esc_html__( 'Enter a value in milliseconds.', 'total' )
			),
			'control_display' => array(
				'check' => 'woo_product_gallery_slider',
				'value' => 'true',
			),
		),
		array(
			'id' => 'woo_product_gallery_zoom',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Product Gallery Zoom', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_product_gallery_lightbox',
			'default' => 'total',
			'control' => array(
				'label' => esc_html__( 'Product Gallery Lightbox', 'total' ),
				'type' => 'select',
				'choices' => array(
					'disabled' => esc_html__( 'Disabled', 'total' ),
					'total' => esc_html__( 'Theme Lightbox', 'total' ),
					'woo' => esc_html__( 'WooCommerce Lightbox', 'total' ),
				),
			),
		),
		array(
			'id' => 'woo_product_gallery_lightbox_titles',
			'control' => array(
				'label' => esc_html__( 'Lightbox Titles', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'woo_product_gallery_lightbox',
				'value' => 'total',
			),
		),
		array(
			'id' => 'woocommerce_gallery_thumbnails_count',
			'default' => 5,
			'control' => array(
				'label' => esc_html__( 'Gallery Thumbnails Columns', 'total' ),
				'type' => 'select',
				'choices' => array(
					'1' => 1,
					'2' => 2,
					'3' => 3,
					'4' => 4,
					'5' => 5,
					'6' => 6,
				),
			),
		),
		array(
			'id' => 'woo_product_tabs_position',
			'default' => '',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Product Tabs Position', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'right' => esc_html__( 'Next to Image', 'total' ),
				),
				'description' => $refresh_desc_2,
			),
		),
		array(
			'id' => 'woocommerce_upsells_count',
			'default' => '4',
			'control' => array(
				'label' => esc_html__( 'Up-Sells Count', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Enter 0 to disable.', 'total' ),
			),
		),
		array(
			'id' => 'woocommerce_upsells_columns',
			'default' => '4',
			'control' => array(
				'label' => esc_html__( 'Up-Sells Columns', 'total' ),
				'type' => 'wpex-columns',
			),
		),
		array(
			'id' => 'woocommerce_related_count',
			'default' => '4',
			'control' => array(
				'label' => esc_html__( 'Related Items Count', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Enter 0 to disable.', 'total' ),
			),
		),
		array(
			'id' => 'woocommerce_related_columns',
			'default' => '4',
			'control' => array(
				'label' => esc_html__( 'Related Products Columns', 'total' ),
				'type' => 'wpex-columns',
			),
		),
		array(
			'id' => 'woo_show_post_rating',
			'default' => true,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Display Product Rating?', 'total' ),
				'type' => 'checkbox',
			),
			'inline_css' => array(
				'target' => '.woocommerce div.product .woocommerce-product-rating',
				'alter' => 'display',
				'sanitize' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_product_meta',
			'default' => 'on',
			'control' => array(
				'label' => esc_html__( 'Display Product Meta?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_next_prev',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Next & Previous Links?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_product_responsive_tabs',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Expand Tabs on Mobile?', 'total' ),
				'type' => 'checkbox',
			),
		),
	),
);

// Social Share Buttons
$this->sections['wpex_woocommerce_social_share'] = array(
	'title' => esc_html__( 'Social Share Buttons', 'total' ),
	'panel' => 'wpex_woocommerce',
	'settings' => array(
		array(
			'id' => 'social_share_woo',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Enable?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_product_social_share_location',
			'control' => array(
				'label' => esc_html__( 'Location', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'woocommerce_share' => esc_html__( 'With Main Details', 'total' ),
				),
			),
		),
		array(
			'id' => 'woo_product_social_share_heading',
			'default' => esc_html__( 'Share This', 'total' ),
			'control' => array(
				'label' => esc_html__( 'Heading Text', 'total' ),
				'type'  => 'text',
				'description' => esc_html__( 'Leave blank to disable.', 'total' ),
			),
		),
		array(
			'id' => 'woo_product_social_share_style',
			'default' => 'flat',
			'control' => array(
				'label' => esc_html__( 'Style', 'total' ),
				'type'  => 'select',
				'choices' => array(
					''        => esc_html__( 'Default', 'total' ),
					'flat'    => esc_html__( 'Flat', 'total' ),
					'minimal' => esc_html__( 'Minimal', 'total' ),
					'three-d' => esc_html__( '3D', 'total' ),
					'rounded' => esc_html__( 'Rounded', 'total' ),
					'custom'  => esc_html__( 'Custom', 'total' ),
				),
			),
		),
		array(
			'id' => 'woo_social_share_label',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Labels?', 'total' ),
				'type' => 'checkbox',
			),
		),
	),
);

// Cart
$this->sections['wpex_woocommerce_cart'] = array(
	'title' => esc_html__( 'Cart', 'total' ),
	'panel' => 'wpex_woocommerce',
	'settings' => array(
		array(
			'id' => 'woocommerce_cross_sells_count',
			'default' => '2',
			'control' => array(
				'label' => esc_html__( 'Cross-Sells Count', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Enter 0 to disable.', 'total' ),
			),
		),
		array(
			'id' => 'woocommerce_cross_sells_columns',
			'default' => '2',
			'control' => array(
				'label' => esc_html__( 'Cross-Sells Columns', 'total' ),
				'type' => 'wpex-columns',
			),
		),
	),
);

// Checkout
$this->sections['wpex_woocommerce_checkout'] = array(
	'title' => esc_html__( 'Checkout', 'total' ),
	'panel' => 'wpex_woocommerce',
	'settings' => array(
		array(
			'id' => 'woo_checkout_single_col',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Single Column Checkout', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'woo_checkout_order_review_placement',
			'default' => 'right_col',
			'control' => array(
				'label' => esc_html__( 'Order Review Placement', 'total' ),
				'type' => 'select',
				'choices' => array(
					'right_col' => esc_html__( 'Right Column (below additonal information)', 'total' ),
					'left_col' => esc_html__( 'After Columns (below billing and additional information)', 'total' ),
				),
			),
		),
	),
);

// Styling
$this->sections['wpex_woocommerce_styling'] = array(
	'title' => esc_html__( 'Styling', 'total' ),
	'panel' => 'wpex_woocommerce',
	'settings' => array(
		array(
			'id' => 'onsale_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'On Sale Tag Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '.woocommerce span.onsale',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'onsale_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'On Sale Tag Color', 'total' )
			),
			'inline_css' => array(
				'target' => '.woocommerce span.onsale',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'woo_onsale_border_radius',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'On Sale Tag Border Radius', 'total' )
			),
			'inline_css' => array(
				'target' => '.woocommerce span.onsale, .woocommerce .outofstock-badge',
				'alter' => 'border-radius',
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'woo_onsale_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'On Sale Tag Padding', 'total' ),
				'description' => $padding_desc,
			),
			'inline_css' => array(
				'target' => '.woocommerce span.onsale, .woocommerce .outofstock-badge',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'woo_add_to_cart_popup_button_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Product Entry Hover Add to Cart Button Background', 'total' )
			),
			'inline_css' => array(
				'target' => array(
					'body .wpex-loop-product-images .wpex-loop-product-add-to-cart > .button, body .wpex-loop-product-images .wpex-loop-product-add-to-cart > .added_to_cart ',
				),
				'alter' => 'background',
				'important' => true,
			),
		),
		array(
			'id' => 'woo_product_title_link_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Product Entry Title Color', 'total' )
			),
			'inline_css' => array(
				'target' => array(
					'.woocommerce ul.products li.product .woocommerce-loop-product__title,.woocommerce ul.products li.product .woocommerce-loop-category__title',
				),
				'alter' => 'color',
			),
		),
		array(
			'id' => 'woo_product_title_link_color_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Product Entry Title Color: Hover', 'total' )
			),
			'inline_css' => array(
				'target' => array(
					'.woocommerce ul.products li.product .woocommerce-loop-product__title:hover,.woocommerce ul.products li.product .woocommerce-loop-category__title:hover',
				),
				'alter' => 'color',
			),
		),
		array(
			'id' => 'woo_price_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Global Price Color', 'total' )
			),
			'inline_css' => array(
				'target' => array(
					'.price > .amount',
					'.price ins .amount',
				),
				'alter' => 'color',
			),
		),
		array(
			'id' => 'woo_product_entry_price_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Product Entry Price Color', 'total' )
			),
			'inline_css' => array(
				'target' => array(
					'.woocommerce ul.products li.product .price',
					'.woocommerce ul.products li.product .price .amount',
				),
				'alter' => 'color',
			),
		),
		array(
			'id' => 'woo_single_price_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Single Product Price Color', 'total' )
			),
			'inline_css' => array(
				'target' => array(
					'.product .summary ins .woocommerce-Price-amount, .product .summary .price>.woocommerce-Price-amount',
				),
				'alter' => 'color',
			),
		),
		array(
			'id' => 'woo_stars_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Star Ratings Color', 'total' )
			),
			'inline_css' => array(
				'target' => array(
					'.woocommerce p.stars a',
					'.woocommerce .star-rating',
				),
				'alter' => 'color',
			),
		),
		array(
			'id' => 'woo_single_tabs_active_border_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Product Tabs Active Border Color', 'total' )
			),
			'inline_css' => array(
				'target' => array(
					'.woocommerce div.product .woocommerce-tabs ul.tabs li.active a',
				),
				'alter' => 'border-color',
			),
		),
	),
);