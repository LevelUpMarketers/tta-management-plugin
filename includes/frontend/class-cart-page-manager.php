<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TTA_Cart_Page_Manager {

  public static function init() {
    add_filter( 'theme_page_templates', [ __CLASS__, 'register_template' ] );
    add_filter( 'template_include',    [ __CLASS__, 'load_template' ] );
  }

  public static function register_template( $templates ) {
    $templates['cart-page-template.php'] = __( 'Cart Page', 'tta' );
    return $templates;
  }

  public static function load_template( $orig ) {
    if ( is_page() ) {
      global $post;
      $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
      if ( 'cart-page-template.php' === $tpl ) {
        $file = plugin_dir_path( __FILE__ ) . 'templates/cart-page-template.php';
        if ( file_exists( $file ) ) {
          return $file;
        }
      }
    }
    return $orig;
  }
}

TTA_Cart_Page_Manager::init();
