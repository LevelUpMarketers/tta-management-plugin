<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TTA_Checkin_Page_Manager {
    public static function init() {
        add_filter( 'theme_page_templates', [ __CLASS__, 'register_template' ] );
        add_filter( 'template_include', [ __CLASS__, 'load_template' ] );
    }

    public static function register_template( $templates ) {
        $templates['host-checkin-template.php'] = __( 'Host Check-In', 'tta' );
        return $templates;
    }

    public static function load_template( $template ) {
        if ( is_page() ) {
            global $post;
            $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
            if ( 'host-checkin-template.php' === $tpl ) {
                $file = plugin_dir_path( __FILE__ ) . 'templates/host-checkin-template.php';
                if ( file_exists( $file ) ) {
                    return $file;
                }
            }
        }
        return $template;
    }
}

TTA_Checkin_Page_Manager::init();
