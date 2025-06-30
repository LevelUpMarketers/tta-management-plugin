<?php
/**
 * Custom Shortcodes
 *
 * @package Total Theme Core
 * @subpackage inc
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

// Allow for the use of shortcodes in the WordPress excerpt
add_filter( 'the_excerpt', 'shortcode_unautop' );
add_filter( 'the_excerpt', 'do_shortcode' );

// Allow shortcodes in menus
add_filter( 'wp_nav_menu_items', 'do_shortcode' );

// Allow shortcodes in widgets
add_filter( 'widget_text', 'do_shortcode' );

/**
 * Fixes spacing issues with shortcodes.
 *
 * IMPORTANT FOR WPBAKERY!!!
 */
if ( ! function_exists( 'wpex_fix_shortcodes' ) ) {
	function wpex_fix_shortcodes( $content ){
		$array = array(
			'<p>['    => '[',
			']</p>'   => ']',
			']<br />' => ']'
		);
		$content = strtr( $content, $array) ;
		return $content;
	}
}
add_filter( 'the_content', 'wpex_fix_shortcodes' );

/**
 * Site URL.
 */
if ( ! shortcode_exists( 'site_url' ) ) {
	function wpex_site_url_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'path'   => '',
			'scheme' => null,
		), $atts, 'site_url' );
		return site_url( $atts['path'], $atts['scheme'] );
	}
	add_shortcode( 'site_url', 'wpex_site_url_shortcode' );
}

/**
 * Relative URL.
 */
if ( ! shortcode_exists( 'menu_site_url' ) ) {
	function wpex_menu_site_url_shortcode( $atts ) {
		$url = get_site_url( null, '', 'http' );
		$url = str_replace( 'http://', '', $url );
		return $url;
	}
	add_shortcode( 'menu_site_url', 'wpex_menu_site_url_shortcode' );
}

/**
 * Text highlight.
 */
if ( ! shortcode_exists( 'highlight' ) ) {
	function wpex_text_highlight( $atts, $content = '' ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return; // !important check because shortcode functions are only loaded on the front-end
		}
		$atts = shortcode_atts( array(
			'color'  => '',
			'height' => '',
		), $atts, 'highlight' );
		$inline_style = '';
		$inline_style = vcex_inline_style( array(
			'background' => $atts['color'],
			'height'     => $atts['height'],
		), true );
		return '<span class="wpex-highlight">' . wp_kses_post( $content ) . '<span class="wpex-after wpex-bg-accent"' . $inline_style . '></span></span>';
	}
	add_shortcode( 'highlight', 'wpex_text_highlight' );
}

/**
 * Line break shortcode.
 */
if ( ! shortcode_exists( 'br' ) ) {
	function wpex_br_shortcode() {
		return '<br />';
	}
	add_shortcode( 'br', 'wpex_br_shortcode' );
}

/**
 * Username shortcode.
 */
if ( ! shortcode_exists( 'username' ) ) {
	function wpex_username_shortcode() {
		$current_user = wp_get_current_user();
		if ( ! ( $current_user instanceof WP_User ) ) {
			return;
		}
		return esc_html( $current_user->display_name );
	}
	add_shortcode( 'username', 'wpex_username_shortcode' );
}

/**
 * Select dropdown menu.
 */
if ( ! shortcode_exists( 'select_menu' ) ) {
	function wpex_select_menu_shortcode( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'menu'          => null,
			'custom_select' => true
		), $atts );

		if ( empty( $atts['menu'] ) ) {
			return;
		}

		$menu = wp_get_nav_menu_object( $atts['menu'] );

		if ( ! $menu ) {
			return;
		}

		$atts['custom_select'] = wp_validate_boolean( $atts['custom_select'] ); // sanitize custom_select field

		ob_start();

		$menu_items = wp_get_nav_menu_items( $menu->term_id );

		$escaped_menu_id = esc_attr( 'select-menu-' . esc_attr( $menu->term_id ) ); ?>

		<?php if ( ! empty( $atts['custom_select'] ) ) echo '<div class="wpex-select-wrap">'; ?>

		<select id="<?php echo $escaped_menu_id; ?>" class="wpex-select-menu-shortcode" onchange="if (this.value) window.location.href=this.value">

			<?php
			// Make sure we have menu items
			if ( $menu_items && is_array( $menu_items ) ) {

				foreach ( $menu_items as $menu_item ) : ?>

					<option value="<?php echo esc_url( $menu_item->url ); ?>"><?php echo esc_attr( $menu_item->title ); ?></option>

				<?php endforeach;
			} ?>

		</select>

		<?php if ( ! empty( $atts['custom_select'] ) ) echo '</div>'; ?>

		<?php return ob_get_clean();


	}
	add_shortcode( 'select_menu', 'wpex_select_menu_shortcode' );
}

/**
 * Custom date shortcode.
 */
if ( ! shortcode_exists( 'date' ) ) {
	function wpex_date_format( $atts ) {
		$atts = shortcode_atts( array(
			'id'     => null,
			'format' => 'F j, Y',
		), $atts );
		$id = ! empty( $atts['id'] ) ? $atts['id'] : get_the_ID();
		$format = ! empty( $atts['format'] ) ? $atts['format'] : get_option( 'date_format' );
		return esc_html( get_the_date( $format, $id ) );
	}
	add_shortcode( 'date', 'wpex_date_format' );
}

/**
 * Staff social shortcode.
 */
if ( ! function_exists( 'wpex_staff_social_shortcode' ) ) {
	function wpex_staff_social_shortcode( $atts = NULL ) {
		if ( function_exists( 'wpex_get_staff_social' ) ) {
			return wpex_get_staff_social( $atts );
		}
	}
}
add_shortcode( 'staff_social', 'wpex_staff_social_shortcode' );

/**
 * Registers the Polylang Language Switcher function as a shortcode.
 */
if ( ! function_exists( 'wpex_polylang_switcher_shortcode' ) ) {
	function wpex_polylang_switcher_shortcode( $atts, $content = null ) {

		// Make sure pll_the_languages() is defined
		if ( function_exists( 'pll_the_languages' ) ) {

			// Extract attributes
			extract( shortcode_atts( array(
				'dropdown'               => false,
				'show_flags'             => true,
				'show_names'             => false,
				'classes'                => '',
				'hide_if_empty'          => true,
				'force_home'             => false,
				'hide_if_no_translation' => false,
				'hide_current'           => false,
				'post_id'                => null,
				'raw'                    => false,
				'echo'                   => 0
			), $atts ) );

			// Define output
			$output = '';

			// Args
			$dropdown   = 'true' == $dropdown ? true : false;
			$show_flags = 'true' == $show_flags ? true : false;
			$show_names = 'true' == $show_names ? true : false;

			// Dropdown args
			if ( $dropdown ) {
				$show_flags = $show_names = false;
			}

			// Classes
			$classes = 'polylang-switcher-shortcode wpex-clr';
			if ( $show_names && ! $dropdown ) {
				$classes .= ' flags-and-names';
			}

			// Display Switcher
			if ( ! $dropdown ) {
				$output .= '<ul class="' . esc_attr( $classes ) . '">';
			}

			// Display the switcher
			$output .= pll_the_languages( array(
				'dropdown'               => $dropdown,
				'show_flags'             => $show_flags,
				'show_names'             => $show_names,
				'hide_if_empty'          => $hide_if_empty,
				'force_home'             => $force_home,
				'hide_if_no_translation' => $hide_if_no_translation,
				'hide_current'           => $hide_current,
				'post_id'                => $post_id,
				'raw'                    => $raw,
				'echo'                   => $echo,
			) );

			if ( ! $dropdown ) {
				$output .= '</ul>';
			}

			// Return output
			return $output;

		}

	}
}
add_shortcode( 'polylang_switcher', 'wpex_polylang_switcher_shortcode' );

/**
 * Registers the enqueue_imagesloaded shortcode.
 */
if ( ! function_exists( 'wpex_enqueue_imagesloaded' ) ) {
	function wpex_enqueue_imagesloaded() {
		if ( function_exists( 'wpex_enqueue_lightbox_scripts' ) ) {
			wp_enqueue_script( 'imagesloaded' );
		}
	}
}
add_shortcode( 'enqueue_imagesloaded', 'wpex_enqueue_imagesloaded' );

/**
 * Registers the wpex_lightbox_scripts shortcode.
 */
if ( ! function_exists( 'wpex_lightbox_scripts_shortcode' ) ) {
	function wpex_lightbox_scripts_shortcode() {
		if ( function_exists( 'wpex_enqueue_lightbox_scripts' ) ) {
			wpex_enqueue_lightbox_scripts();
		}
	}
}
add_shortcode( 'wpex_lightbox_scripts', 'wpex_lightbox_scripts_shortcode' );

/**
 * Registers the wpml translation shortcode.
 */
if ( ! function_exists( 'wpex_wpml_translate_shortcode' ) ) {
	function wpex_wpml_translate_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'lang'	=> '',
		), $atts ) );
		$lang_active = ICL_LANGUAGE_CODE;
		if ( $lang == $lang_active ) {
			return do_shortcode( $content );
		}
	}
}
add_shortcode( 'wpml_translate', 'wpex_wpml_translate_shortcode' );

/**
 * Registers the wpml switcher shortcode.
 */
if ( ! function_exists( 'wpex_wpml_lang_selector_shortcode' ) ) {
	function wpex_wpml_lang_selector_shortcode( $atts, $content = null ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		ob_start();
			do_action( 'icl_language_selector' );
		return ob_get_clean();
	}
}
add_shortcode( 'wpml_lang_selector', array( $this, 'wpex_wpml_lang_selector_shortcode' ) );

/**
 * Searchform shortcode.
 */
if ( ! function_exists( 'wpex_searchform_shortcode' ) && ! shortcode_exists( 'searchform' ) ) {
	function wpex_searchform_shortcode() {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return; // !important check because shortcode functions are only loaded on the front-end
		}
		ob_start();
		get_search_form();
		return ob_get_clean();
	}
	add_shortcode( 'searchform', 'wpex_searchform_shortcode' );
}

/**
 * Post Title.
 */
if ( ! shortcode_exists( 'post_title' ) ) {
	function wpex_post_title() {
		return get_the_title();
	}
	add_shortcode( 'post_title', 'wpex_post_title' );
}

/**
 * Post Permalink.
 */
if ( ! shortcode_exists( 'post_permalink' ) ) {
	function wpex_post_permalink() {
		return get_permalink();
	}
	add_shortcode( 'post_permalink', 'wpex_post_permalink' );
}

/**
 * Post Publish Date.
 */
if ( ! shortcode_exists( 'post_publish_date' ) ) {
	function wpex_post_publish_date() {
		return get_the_date();
	}
	add_shortcode( 'post_publish_date', 'wpex_post_publish_date' );
}

/**
 * Post Modified Date.
 */
if ( ! shortcode_exists( 'post_modified_date' ) ) {
	function wpex_post_modified_date() {
		return get_the_modified_date();
	}
	add_shortcode( 'post_modified_date', 'wpex_post_modified_date' );
}

/**
 * Post Author.
 */
if ( ! shortcode_exists( 'post_author' ) ) {
	function wpex_post_author() {
		global $post;
		return $post ? get_the_author_meta( 'nicename', $post->post_author ) : '';
	}
	add_shortcode( 'post_author', 'wpex_post_author' );
}

/**
 * Year shortcode.
 */
if ( ! shortcode_exists( 'current_year' ) ) {
	function wpex_year_shortcode() {
		return date( 'Y' );
	}
	add_shortcode( 'current_year', 'wpex_year_shortcode' );
}

/**
 * Custom field shortcode.
 */
if ( ! shortcode_exists( 'cf_value' ) ) {
	function wpex_cf_value_shortcode( $atts ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return; // !important check because shortcode functions are only loaded on the front-end
		}
		extract( shortcode_atts( array(
			'name' => '',
		), $atts ) );
		if ( $name ) {
			return get_post_meta( get_the_ID(), $name, true );
		}
	}
	add_shortcode( 'cf_value', 'wpex_cf_value_shortcode' );
}

/**
 * Font Awesome Shortcode.
 */
if ( ! function_exists( 'wpex_font_awesome_shortcode' ) ) {

	function wpex_font_awesome_shortcode( $atts ) {

		if ( is_admin() && ! wp_doing_ajax() ) {
			return; // !important check because shortcode functions are only loaded on the front-end
		}

		extract( shortcode_atts( array(
			'icon'          => '',
			'link'          => '',
			'link_title'    => '',
			'link_target'   => '',
			'link_rel'      => '',
			'margin_right'  => '',
			'margin_left'   => '',
			'margin_top'    => '',
			'margin_bottom' => '',
			'color'         => '',
			'size'          => '',
			'link'          => '',
			'class'         => '',
		), $atts ) );

		if ( empty( $icon ) ) {
			return;
		}

		// Enqueue font icon stylesheet.
		wp_enqueue_style( 'ticons' );

		// Sanitize vars
		$link       = esc_url( $link );
		$icon       = esc_attr( $icon );
		$link_title = $link_title ? esc_attr( $link_title ) : '';

		// Sanitize $icon
		// @Todo check if part of Ticons array if not enqueue fontawesome and use that.
		if ( apply_filters( 'wpex_font_awesome_shortcode_parse_fa', false ) ) {
			$icon = str_replace( 'fa ', 'ticon ', $icon );
			$icon = str_replace( 'fa-', 'ticon-', $icon );
		}

		// Generate inline styles
		$style = array();
		$style_escaped = '';
		if ( $color ) {
			$style[] = 'color:' . esc_attr( $color ) . ';';
		}
		if ( $margin_left ) {
			$style[] = 'margin-left:' . intval( $margin_left ) . 'px;';
		}
		if ( $margin_right ) {
			$style[] = 'margin-right:' . intval( $margin_right ) . 'px;';
		}
		if ( $margin_top ) {
			$style[] = 'margin-top:' . intval( $margin_top ) . 'px;';
		}
		if ( $margin_bottom ) {
			$style[] = 'margin-bottom:' . intval( $margin_bottom ) . 'px;';
		}
		if ( $size ) {
			$style[] = 'font-size:' . intval( $size ) . 'px;';
		}
		$style = implode( '', $style );

		if ( $style ) {
			$style = wp_kses( $style, array() ); // @todo Do we need this?
			$style_escaped = ' style="' . esc_attr( $style ) . '"';
		}

		// Display icon with link
		if ( $link ) {

			$a_attrs = array(
				'href'   => $link,
				'title'  => $link_title,
				'target' => $link_target,
				'rel'    => $link_rel,
			);

			$output = '<a';

				foreach ( $a_attrs as $a_attrs_k => $a_attrs_v ) {
					$output .= ' ' . $a_attrs_k . '=' . '"' . esc_attr( $a_attrs_v ) . '"';
				}

			$output .= '>';

				if ( defined( 'TOTAL_THEME_ACTIVE' ) ) {

					$output .= '<span class="ticon ticon-' . esc_attr( $icon ) . '"' . $style_escaped . '></span>';

				} else {

					wp_enqueue_style( 'font-awesome' );

					$output .= '<span class="fa fa-' . esc_attr( $icon ) . '"' . $style_escaped . '></span>';

				}

			$output .= '</a>';



		}

		// Display icon without link
		else {

			if ( defined( 'TOTAL_THEME_ACTIVE' ) ) {

				$output = '<span class="ticon ticon-' . esc_attr( $icon ) . '"' . $style_escaped . '></span>';

			} else {

				wp_enqueue_style( 'font-awesome' );

				$output = '<span class="fa fa-' . esc_attr( $icon ) . '"' . $style_escaped . '></span>';

			}


		}

		// Return shortcode output
		return $output;

	}

}
add_shortcode( 'font_awesome', 'wpex_font_awesome_shortcode' );
add_shortcode( 'ticon', 'wpex_font_awesome_shortcode' );

/**
 * Login Link.
 */
if ( ! function_exists( 'wpex_wp_login_url_shortcode' ) ) {

	function wpex_wp_login_url_shortcode( $atts ) {

		if ( is_admin() && ! wp_doing_ajax() ) {
			return; // !important check because shortcode functions are only loaded on the front-end
		}

		extract( shortcode_atts( array(
			'login_url'       => '',
			'url'             => '',
			'text'            => esc_html__( 'Login', 'total-theme-core' ),
			'logout_text'     => esc_html__( 'Log Out', 'total-theme-core' ),
			'target'          => '',
			'logout_redirect' => '',
			'icon'            => '',
		), $atts, 'wp_login_url' ) );

		// Target
		if ( 'blank' == $target ) {
			$target = 'target="_blank"';
		} else {
			$target = '';
		}

		// Define login url
		if ( $url ) {
			$login_url = $url;
		} elseif ( $login_url ) {
			$login_url = $login_url;
		} else {
			$login_url = wp_login_url();
		}

		// Logout redirect
		if ( ! $logout_redirect ) {
			$permalink = get_permalink();
			if ( $permalink ) {
				$logout_redirect = $permalink;
			} else {
				$logout_redirect = home_url( '/' );
			}
		}

		// Logged in link
		if ( is_user_logged_in() ) {
			$href    = wp_logout_url( $logout_redirect );
			$class   = 'wpex_logout';
			$content = wp_strip_all_tags( $logout_text );
		}

		// Non-logged in link
		else {
			$href    = esc_url( $login_url );
			$class   = 'login';
			$content = wp_strip_all_tags( $text );
		}

		$attrs['target'] = $target;

		$output = '';

		$output .= '<a href="' . esc_url( $href ) . '" class="' . esc_attr( $class ) . '">';

			if ( $icon ) {

				$output .= '<span class="' . esc_attr( $icon ) . '" aria-hidden="true"></span>';

			}

			$output .= $content;

		$output .= '</a>';

		return $output;

	}

}
add_shortcode( 'wp_login_url', 'wpex_wp_login_url_shortcode' );

/**
 * Add shortcode buttons to the MCE Editor.
 */
if ( get_theme_mod( 'editor_shortcodes_enable', true ) ) {

	// Adds filters to admin_head
	function wpex_shortcodes_add_mce_button() {
		if ( ! current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', 'wpex_shortcodes_add_tinymce_plugin' );
			add_filter( 'mce_buttons', 'wpex_shortcodes_register_mce_button' );
		}
	}
	add_action( 'admin_head', 'wpex_shortcodes_add_mce_button' );

	// Loads js for the Button
	function wpex_shortcodes_add_tinymce_plugin( $plugin_array ) {
		$plugin_array['wpex_shortcodes_mce_button'] = TTC_PLUGIN_DIR_URL . 'assets/js/shortcodes-tinymce.js';
		return $plugin_array;
	}

	// Registers new button
	function wpex_shortcodes_register_mce_button( $buttons ) {
		array_push( $buttons, 'wpex_shortcodes_mce_button' );
		return $buttons;
	}

	// Localize js
	function wpex_shortcodes_tinymce_json() {

		// TinyMCE data array
		$data = array();
		$data['btnLabel']   = esc_html__( 'Shortcodes', 'total-theme-core' );
		$data['shortcodes'] = array(
			'br' => array(
				'text' => esc_html__( 'Line Break', 'total-theme-core' ),
				'insert' => '[br]',
			),
			'ticon' => array(
				'text' => esc_html__( 'Icon', 'total-theme-core' ),
				'insert' => '[ticon icon="bolt" color="000" size="16px" margin_right="" margin_left="" margin_top="" margin_bottom="" link=""]',
			),
			'current_year' => array(
				'text' => esc_html__( 'Current Year', 'total-theme-core' ),
				'insert' => '[current_year]',
			),
			'searchform' => array(
				'text' => esc_html__( 'WP Searchform', 'total-theme-core' ),
				'insert' => '[searchform]',
			),
		);

		// Apply filters for child theming
		$data = apply_filters( 'wpex_shortcodes_tinymce_json', $data ); ?>

		<!-- Total TinyMCE Shortcodes -->
		<script>var wpexTinymce = <?php echo wp_json_encode( $data ); ?> ;</script>
		<!-- Total TinyMCE Shortcodes -->

	<?php }
	add_action( 'admin_footer', 'wpex_shortcodes_tinymce_json' );

}