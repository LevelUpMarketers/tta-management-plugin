<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Member_Dashboard {

    /**
     * Singleton instance
     *
     * @return TTA_Member_Dashboard
     */
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        // Register shortcode [tta_member_dashboard]
        add_shortcode( 'tta_member_dashboard', [ $this, 'render_dashboard_shortcode' ] );

        // Enqueue front-end scripts & styles when shortcode is present
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Enqueue CSS/JS for the dashboard (only if shortcode is in use).
     */
    public function enqueue_assets() {
        global $post;
        $has_shortcode = $post && has_shortcode( $post->post_content, 'tta_member_dashboard' );
        if ( ! $has_shortcode && ! is_page( 'member-dashboard' ) ) {
            return;
        }

        if ( $has_shortcode || is_page( 'member-dashboard' ) ) {
            // CSS
            $css_file = TTA_PLUGIN_DIR . 'assets/css/frontend/member-dashboard.css';
            $css_url  = TTA_PLUGIN_URL . 'assets/css/frontend/member-dashboard.css';
            $css_ver  = file_exists( $css_file ) ? filemtime( $css_file ) : TTA_PLUGIN_VERSION;
            wp_enqueue_style(
                'tta-member-dashboard',
                $css_url,
                [],
                $css_ver
            );

            // JavaScript
            wp_enqueue_script( 'jquery' );
            $js_file = TTA_PLUGIN_DIR . 'assets/js/frontend/member-dashboard.js';
            $js_url  = TTA_PLUGIN_URL . 'assets/js/frontend/member-dashboard.js';
            $js_ver  = file_exists( $js_file ) ? filemtime( $js_file ) : TTA_PLUGIN_VERSION;
            wp_enqueue_script(
                'tta-member-dashboard-js',
                $js_url,
                [ 'jquery' ],
                $js_ver,
                true
            );

            $mask_file = TTA_PLUGIN_DIR . 'assets/js/frontend/checkout-expiration-mask.js';
            $mask_url  = TTA_PLUGIN_URL . 'assets/js/frontend/checkout-expiration-mask.js';
            $mask_ver  = file_exists( $mask_file ) ? filemtime( $mask_file ) : TTA_PLUGIN_VERSION;
            wp_enqueue_script(
                'tta-checkout-js',
                $mask_url,
                [ 'jquery' ],
                $mask_ver,
                true
            );
            wp_localize_script(
                'tta-member-dashboard-js',
                'TTA_MemberDashboard',
                [
                    'ajax_url'                 => admin_url( 'admin-ajax.php' ),
                    'update_nonce'             => wp_create_nonce( 'tta_member_front_update' ),
                    'front_nonce'              => wp_create_nonce( 'tta_frontend_nonce' ),
                    'plugin_url'               => TTA_PLUGIN_URL,
                    'email_mismatch_msg'       => __( 'Email addresses do not match.', 'tta' ),
                    'password_mismatch_msg'    => __( 'Passwords do not match.', 'tta' ),
                    'password_requirements_msg'=> __( 'Password must be at least 8 characters and include upper and lower case letters and a number.', 'tta' ),
                    'request_failed_msg'       => __( 'Request failed.', 'tta' ),
                    'account_created_msg'      => __( 'Account created! Reloading in %d…', 'tta' ),
                ]
            );
        }
    }

    /**
     * Shortcode callback: renders the member dashboard.
     */
    public function render_dashboard_shortcode( $atts ) {
        $is_logged_in = is_user_logged_in();
        $member       = null;

        if ( $is_logged_in ) {
            // Fetch member record
            $wp_user_id    = get_current_user_id();
            global $wpdb;
            $members_table = $wpdb->prefix . 'tta_members';

            $member = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM {$members_table} WHERE wpuserid = %d LIMIT 1", $wp_user_id ),
                ARRAY_A
            );
            if ( ! $member ) {
                return '<p>' . esc_html__( 'No member record found.', 'tta' ) . '</p>';
            }

            // Split address into parts
            $street_address = '';
            $address_2      = '';
            $city           = '';
            $state          = '';
            $zip            = '';
            if ( ! empty( $member['address'] ) ) {
                $addr            = tta_parse_address( $member['address'] );
                $street_address  = $addr['street'];
                $address_2       = $addr['address2'];
                $city            = $addr['city'];
                $state           = $addr['state'];
                $zip             = $addr['zip'];
            }
        }

        ob_start();
        echo do_shortcode('[vc_row full_width="stretch_row_content_no_spaces" css=".vc_custom_1670382516702{background-image: url(https://trying-to-adult-rva-2025.local/wp-content/uploads/2022/12/IMG-4418.png?id=70) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}"][vc_column][vc_empty_space height="300px" el_id="jre-header-title-empty"][vc_column_text css_animation="slideInLeft" el_id="jre-homepage-id-1" css=".vc_custom_1671885403487{margin-left: 50px !important;padding-left: 50px !important;}"]<p id="jre-homepage-id-3">MEMBER DASHBOARD</p>[/vc_column_text][/vc_column][/vc_row]');
        ?>
        <div class="tta-member-dashboard-wrap">
          <?php if ( $is_logged_in ) : ?>
            <h2><?php echo esc_html( 'Welcome, ' . $member['first_name'] . '!' ); ?></h2>
            <p><?php echo esc_html( 'A Member since ' . date_i18n( 'F j, Y', strtotime( $member['joined_at'] ) ) ); ?></p>
            <?php if ( ! empty( $member['banned_until'] ) && strtotime( $member['banned_until'] ) > time() ) : ?>
              <div class="tta-banned-notice">
                <?php
                $ban = tta_get_ban_message( intval( $member['wpuserid'] ) );
                echo wp_kses_post( $ban['message'] );
                if ( ! empty( $ban['button'] ) ) {
                    $url = add_query_arg( 'auto', 'reentry', home_url( '/checkout' ) );
                    echo ' <a class="tta-alert-button" href="' . esc_url( $url ) . '">' . esc_html__( 'Purchase Re-entry Ticket', 'tta' ) . '</a>';
                }
                ?>
              </div>
            <?php endif; ?>
          <?php else : ?>
            <h2><?php esc_html_e( 'Welcome!', 'tta' ); ?></h2>
            <p><?php esc_html_e( 'Log in to view your member information.', 'tta' ); ?></p>
          <?php endif; ?>

          <div class="tta-member-dashboard">
            <div class="tta-dashboard-sidebar">
              <ul class="tta-dashboard-tabs">
                <li data-tab="profile" class="active"><?php esc_html_e( 'Profile Info', 'tta' ); ?></li>
                <li data-tab="upcoming"><?php esc_html_e( 'Your Upcoming Events', 'tta' ); ?></li>
                <li data-tab="waitlist"><?php esc_html_e( 'Your Waitlist Events', 'tta' ); ?></li>
                <li data-tab="past"><?php esc_html_e( 'Your Past Events', 'tta' ); ?></li>
                <li data-tab="billing"><?php esc_html_e( 'Billing & Membership Info', 'tta' ); ?></li>
              </ul>
            </div>

            <div class="tta-dashboard-content">

              <?php if ( $is_logged_in ) : ?>
                <?php include plugin_dir_path( __FILE__ ) . 'views/tab-profile.php'; ?>

                <?php include plugin_dir_path( __FILE__ ) . 'views/tab-upcoming.php'; ?>

                <?php include plugin_dir_path( __FILE__ ) . 'views/tab-waitlist.php'; ?>

                <?php include plugin_dir_path( __FILE__ ) . 'views/tab-past-events.php'; ?>

                <?php include plugin_dir_path( __FILE__ ) . 'views/tab-billing.php'; ?>
              <?php else : ?>
                <?php $tab_slug = 'profile';  include plugin_dir_path( __FILE__ ) . 'views/tab-login.php'; ?>
                <?php $tab_slug = 'upcoming'; include plugin_dir_path( __FILE__ ) . 'views/tab-login.php'; ?>
                <?php $tab_slug = 'waitlist'; include plugin_dir_path( __FILE__ ) . 'views/tab-login.php'; ?>
                <?php $tab_slug = 'past';     include plugin_dir_path( __FILE__ ) . 'views/tab-login.php'; ?>
                <?php $tab_slug = 'billing';  include plugin_dir_path( __FILE__ ) . 'views/tab-login.php'; ?>
              <?php endif; ?>

            </div>
          </div>
        </div>
        <?php
        return ob_get_clean();
    }

}

TTA_Member_Dashboard::get_instance();
