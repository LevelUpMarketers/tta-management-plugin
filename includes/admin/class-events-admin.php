<?php
class TTA_Events_Admin {
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            'TTA Events',
            'TTA Events',
            'manage_options',
            'tta-events',
            [ $this, 'render_page' ],
            'dashicons-calendar',
            9.3
        );
    }

    public function render_page() {
        // Tabs
        $tabs = [
            'create'  => 'Add New Event',
            'manage'  => 'Manage Events',
            'archive' => 'Archived Events',
        ];
        $current = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs )
            ? $_GET['tab']
            : 'create';

        echo '<h1>Events</h1><h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $slug => $label ) {
            $class = $current === $slug ? ' nav-tab-active' : '';
            $url   = esc_url( add_query_arg( [ 'page' => 'tta-events', 'tab' => $slug ], admin_url( 'admin.php' ) ) );
            printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $class, esc_html( $label ) );
        }
        echo '</h2><div class="wrap">';

        // Include the tab-specific view
        $view = TTA_PLUGIN_DIR . "includes/admin/views/events-{$current}.php";
        if ( file_exists( $view ) ) {
            include $view;
        } else {
            echo '<p>View not found: ' . esc_html( $view ) . '</p>';
        }

        echo '</div>';
    }
}
?>
