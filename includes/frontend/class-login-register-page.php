<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Login_Register_Page {
    public static function init() {
        add_filter( 'theme_page_templates', [ __CLASS__, 'register_template' ] );
        add_filter( 'template_include', [ __CLASS__, 'load_template' ] );
    }

    public static function register_template( $templates ) {
        $templates['login-register-page-template.php'] = __( 'Login or Create Account Page', 'tta' );
        return $templates;
    }

    public static function load_template( $template ) {
        if ( is_page() ) {
            global $post;
            $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
            if ( 'login-register-page-template.php' === $tpl ) {
                $file = plugin_dir_path( __FILE__ ) . 'templates/login-register-page-template.php';
                if ( file_exists( $file ) ) {
                    return $file;
                }
            }
        }

        return $template;
    }
}

TTA_Login_Register_Page::init();
