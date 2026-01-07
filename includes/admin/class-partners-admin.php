<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Partners_Admin {
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            'TTA Partners',
            'TTA Partners',
            'manage_options',
            'tta-partners',
            [ $this, 'render_page' ],
            'dashicons-handshake',
            9.45
        );
    }

    public function render_page() {
        $tabs = [
            'create' => __( 'Add New Partner', 'tta' ),
            'manage' => __( 'Manage Partners', 'tta' ),
        ];

        $current = isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'create';
        if ( ! isset( $tabs[ $current ] ) ) {
            $current = 'create';
        }

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html__( 'TTA Partners', 'tta' ) . '</h1>';
        echo '<a href="' . esc_url( admin_url( 'admin.php?page=tta-partners&tab=create' ) ) . '" class="page-title-action">' . esc_html__( 'Add New', 'tta' ) . '</a>';
        echo '<hr class="wp-header-end">';

        echo '<h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $slug => $label ) {
            $class = $current === $slug ? ' nav-tab-active' : '';
            $url   = esc_url( add_query_arg( [ 'page' => 'tta-partners', 'tab' => $slug ], admin_url( 'admin.php' ) ) );
            printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, esc_attr( $class ), esc_html( $label ) );
        }
        echo '</h2>';

        $view = TTA_PLUGIN_DIR . "includes/admin/views/partners-{$current}.php";

        if ( file_exists( $view ) ) {
            include $view;
        } else {
            echo '<p>' . esc_html__( 'View not found.', 'tta' ) . '</p>';
        }

        echo '</div>';
    }
}

TTA_Partners_Admin::get_instance();
