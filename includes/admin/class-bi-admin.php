<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class TTA_BI_Admin {
    public static function get_instance() {
        static $inst; return $inst ?: $inst = new self();
    }
    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }
    public function register_menu() {
        add_menu_page(
            'TTA BI Dashboard',
            'TTA BI Dashboard',
            'manage_options',
            'tta-bi-dashboard',
            [ $this, 'render_page' ],
            'dashicons-chart-bar',
            9.1
        );
    }
    public function render_page() {
        $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'events';
        $tabs = [
            'events'   => 'Event Sales & Tickets',
            'members'  => 'Membership Metrics',
            'predict'  => 'Predictive Analytics',
        ];

        echo '<div class="wrap"><h1>TTA BI Dashboard</h1><h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $slug => $label ) {
            $class = $tab === $slug ? ' nav-tab-active' : '';
            printf( '<a href="%s" class="nav-tab%s">%s</a>',
                esc_url( admin_url( 'admin.php?page=tta-bi-dashboard&tab=' . $slug ) ),
                $class,
                esc_html( $label ) );
        }
        echo '</h2>';

        include plugin_dir_path( __FILE__ ) . 'views/bi-dashboard.php';
        echo '</div>';
    }
}
TTA_BI_Admin::get_instance();
