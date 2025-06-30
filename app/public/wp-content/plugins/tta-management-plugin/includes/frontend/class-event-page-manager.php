<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TTA_Event_Page_Manager {

  public static function init() {
    // Add our template to the “Page Template” dropdown
    add_filter( 'theme_page_templates',    [ __CLASS__, 'register_template' ] );
    // When WP is about to include a template file, detect ours
    add_filter( 'template_include',        [ __CLASS__, 'load_template'  ] );
  }

  /**
   * Register our template file in the admin UI
   */
  public static function register_template( $templates ) {
    $templates['event-page-template.php'] = __( 'Event Page', 'tta' );
    return $templates;
  }

  /**
   * If current page uses our template, load it from plugin/
   */
  public static function load_template( $orig ) {
    if ( is_page() ) {
      global $post;
      $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
      if ( 'event-page-template.php' === $tpl ) {
        $file = plugin_dir_path( __FILE__ ) . 'templates/event-page-template.php';
        if ( file_exists( $file ) ) {
          return $file;
        }
      }
    }
    return $orig;
  }
}

TTA_Event_Page_Manager::init();
