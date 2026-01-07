<?php
/**
 * Register and load the Partner Login Page template.
 *
 * @package TTA
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Partner_Login_Page {
    /**
     * Boot hooks.
     *
     * @return void
     */
    public static function init() {
        add_filter( 'theme_page_templates', [ __CLASS__, 'register_template' ] );
        add_filter( 'template_include', [ __CLASS__, 'load_template' ] );
    }

    /**
     * Register the template with WordPress so it appears in the editor dropdown.
     *
     * @param array $templates Existing templates.
     * @return array
     */
    public static function register_template( $templates ) {
        $templates['partner-login-page-template.php'] = __( 'Partner Login Page', 'tta' );
        return $templates;
    }

    /**
     * Swap in the plugin template when selected on a page.
     *
     * @param string $template The template file that would be used.
     * @return string
     */
    public static function load_template( $template ) {
        if ( is_page() ) {
            global $post;
            $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
            if ( 'partner-login-page-template.php' === $tpl ) {
                $file = plugin_dir_path( __FILE__ ) . 'templates/partner-login-page-template.php';
                if ( file_exists( $file ) ) {
                    return $file;
                }
            }
        }

        return $template;
    }
}

TTA_Partner_Login_Page::init();
