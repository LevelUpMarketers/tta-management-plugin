<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TTA_Events_List_Page {
    public static function init() {
        add_filter( 'theme_page_templates', [ __CLASS__, 'register_template' ] );
        add_filter( 'template_include',    [ __CLASS__, 'load_template' ] );
    }

    public static function register_template( $templates ) {
        $templates['events-list-page-template.php'] = __( 'Events List Page', 'tta' );
        return $templates;
    }

    public static function load_template( $template ) {
        if ( is_page() ) {
            global $post;
            $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
            if ( 'events-list-page-template.php' === $tpl ) {
                $file = plugin_dir_path( __FILE__ ) . 'templates/events-list-page-template.php';
                if ( file_exists( $file ) ) {
                    return $file;
                }
            }
        }
        return $template;
    }
}

TTA_Events_List_Page::init();
?>
