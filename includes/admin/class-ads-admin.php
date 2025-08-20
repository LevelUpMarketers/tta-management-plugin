<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ads_Admin {
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            'TTA Ads',
            'TTA Ads',
            'manage_options',
            'tta-ads',
            [ $this, 'render_page' ],
            'dashicons-megaphone',
            9
        );
    }

    public function render_page() {
        $tabs    = [ 'create' => 'Create Ad', 'manage' => 'Manage Ads' ];
        $current = isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ? $_GET['tab'] : 'create';

        echo '<h1>TTA Ads</h1><h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $slug => $label ) {
            $class = $current === $slug ? ' nav-tab-active' : '';
            $url   = esc_url( add_query_arg( [ 'page' => 'tta-ads', 'tab' => $slug ], admin_url( 'admin.php' ) ) );
            printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $class, esc_html( $label ) );
        }
        echo '</h2>';

        $view = TTA_PLUGIN_DIR . "includes/admin/views/ads-{$current}.php";
        if ( file_exists( $view ) ) {
            include $view;
        } else {
            echo '<div class="wrap"><p>' . esc_html__( 'View not found.', 'tta' ) . '</p></div>';
        }
    }
}

TTA_Ads_Admin::get_instance();
