<?php
class TTA_Tickets_Admin {
    public static function get_instance() { static $inst; return $inst ?: $inst = new self(); }
    private function __construct() {
        add_action('admin_menu', array($this, 'register_menu'));
    }

    public function register_menu() {
        add_menu_page(
            'TTA Tickets',
            'TTA Tickets',
            'manage_options',
            'tta-tickets',
            [ $this, 'render_page' ],
            'dashicons-tickets',
            9.7
        );
    }

    public function render_page() {
        // Tabs
        $tabs = [
            'manage' => 'Manage Tickets',
        ];
        $current = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs )
            ? $_GET['tab']
            : 'manage';

        echo '<h1>Tickets</h1><h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $slug => $label ) {
            $class = $current === $slug ? ' nav-tab-active' : '';
            $url   = esc_url( add_query_arg( [ 'page' => 'tta-tickets', 'tab' => $slug ], admin_url( 'admin.php' ) ) );
            printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $class, esc_html( $label ) );
        }
        echo '</h2><div class="wrap">';

        // Include the tab-specific view
        $view = TTA_PLUGIN_DIR . "includes/admin/views/tickets-{$current}.php";
        if ( file_exists( $view ) ) {
            include $view;
        } else {
            echo '<p>View not found: ' . esc_html( $view ) . '</p>';
        }

        echo '</div>';
    }
}
?>