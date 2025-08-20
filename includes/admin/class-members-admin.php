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
            'TTA Members',
            'TTA Members',
            'manage_options',
            'tta-members',
            [ $this, 'render_list' ],
            'dashicons-groups',
            9.4
        );
    }

    public function render_list() {
        // Determine which “tab” is active: create vs. manage
        $allowed = [ 'create', 'manage', 'history', 'banned' ];
        $tab = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $allowed, true ) ? $_GET['tab'] : 'create';

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
            printf(
                '<a href="%1$s" class="nav-tab %2$s">Member History</a>',
                esc_url( admin_url( 'admin.php?page=tta-members&tab=history' ) ),
                $tab === 'history' ? 'nav-tab-active' : ''
            );
            printf(
                '<a href="%1$s" class="nav-tab %2$s">Banned Members</a>',
                esc_url( admin_url( 'admin.php?page=tta-members&tab=banned' ) ),
                $tab === 'banned' ? 'nav-tab-active' : ''
            );
          echo '</h2>';

          // Load the appropriate view
          if ( $tab === 'create' ) {
              include plugin_dir_path( __FILE__ ) . '../admin/views/members-create.php';
          } elseif ( $tab === 'history' ) {
              include plugin_dir_path( __FILE__ ) . '../admin/views/members-history.php';
          } elseif ( $tab === 'banned' ) {
              include plugin_dir_path( __FILE__ ) . '../admin/views/members-banned.php';
          } else {
              include plugin_dir_path( __FILE__ ) . '../admin/views/members-manage.php';
          }
        echo '</div>';
    }
}

TTA_Members_Admin::get_instance();
