<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Members_Admin {
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            'Members',
            'Members',
            'manage_options',
            'tta-members',
            [ $this, 'render_list' ],
            'dashicons-groups',
            6
        );
    }

    public function render_list() {
        // Determine which “tab” is active: create vs. manage
        $tab = isset( $_GET['tab'] ) && $_GET['tab'] === 'create'
             ? 'create'
             : 'manage';

        // Render horizontal tabs, matching the style of Events
        echo '<div class="wrap">';
          echo '<h1 class="wp-heading-inline">Members</h1>';
          echo '<a href="' . esc_url( admin_url( 'admin.php?page=tta-members&tab=create' ) ) . '" class="page-title-action">Add New</a>';
          echo '<hr class="wp-header-end">';

          // Tabs
          echo '<h2 class="nav-tab-wrapper">';
            printf(
                '<a href="%1$s" class="nav-tab %2$s">Add New Member</a>',
                esc_url( admin_url( 'admin.php?page=tta-members&tab=create' ) ),
                $tab === 'create' ? 'nav-tab-active' : ''
            );
            printf(
                '<a href="%1$s" class="nav-tab %2$s">Manage Members</a>',
                esc_url( admin_url( 'admin.php?page=tta-members&tab=manage' ) ),
                $tab === 'manage' ? 'nav-tab-active' : ''
            );
          echo '</h2>';

          // Load the appropriate view
          if ( $tab === 'create' ) {
              include plugin_dir_path( __FILE__ ) . '../admin/views/members-create.php';
          } else {
              include plugin_dir_path( __FILE__ ) . '../admin/views/members-manage.php';
          }
        echo '</div>';
    }
}

TTA_Members_Admin::get_instance();
