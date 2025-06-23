<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TTA_Become_Member_Page {
    public static function init() {
        add_filter( 'theme_page_templates', [ __CLASS__, 'register_template' ] );
        add_filter( 'template_include',      [ __CLASS__, 'load_template' ] );
    }

    public static function register_template( $templates ) {
        $templates['become-member-page-template.php'] = __( 'Become a Member', 'tta' );
        return $templates;
    }

    public static function load_template( $template ) {
        if ( is_page() ) {
            global $post;
            $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
            if ( 'become-member-page-template.php' === $tpl ) {
                $file = plugin_dir_path( __FILE__ ) . 'templates/become-member-page-template.php';
                if ( file_exists( $file ) ) {
                    return $file;
                }
            }
        }
        return $template;
    }
}

TTA_Become_Member_Page::init();
