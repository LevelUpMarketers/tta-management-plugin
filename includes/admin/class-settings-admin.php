<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Settings_Admin {
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            'TTA Settings',
            'TTA Settings',
            'manage_options',
            'tta-settings',
            [ $this, 'render_page' ],
            'dashicons-admin-generic',
            9.6
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
        echo '<div class="wrap"><h1>TTA Settings</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=tta-settings&tab=general" class="nav-tab ' . ( 'general' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'General Settings', 'tta' ) . '</a>';
        echo '<a href="?page=tta-settings&tab=logging" class="nav-tab ' . ( 'logging' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Logging', 'tta' ) . '</a>';
        echo '<a href="?page=tta-settings&tab=api" class="nav-tab ' . ( 'api' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'API Settings', 'tta' ) . '</a>';
        echo '</h2>';

        if ( 'logging' === $active_tab ) {
            if ( isset( $_POST['tta_clear_log'] ) && check_admin_referer( 'tta_clear_log_action', 'tta_clear_log_nonce' ) ) {
                TTA_Debug_Logger::clear();
                echo '<div class="updated"><p>' . esc_html__( 'Debug log cleared.', 'tta' ) . '</p></div>';
            }

            $log = TTA_Debug_Logger::get_messages();
            if ( $log ) {
                echo '<pre class="tta-debug-log" style="max-height:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:10px;">' . esc_html( implode( "\n", $log ) ) . '</pre>';
                echo '<form method="post" action="?page=tta-settings&tab=logging">';
                wp_nonce_field( 'tta_clear_log_action', 'tta_clear_log_nonce' );
                echo '<p><input type="submit" name="tta_clear_log" class="button" value="' . esc_attr__( 'Clear Log', 'tta' ) . '"></p>';
                echo '</form>';
            } else {
                echo '<p>' . esc_html__( 'No debug messages logged yet.', 'tta' ) . '</p>';
            }
        } elseif ( 'api' === $active_tab ) {
            $import_results = [];
            if ( isset( $_POST['tta_save_api_settings'] ) && check_admin_referer( 'tta_save_api_settings_action', 'tta_save_api_settings_nonce' ) ) {
                $login   = isset( $_POST['tta_authnet_login_id'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_authnet_login_id'] ) ) : '';
                $trans   = isset( $_POST['tta_authnet_transaction_key'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_authnet_transaction_key'] ) ) : '';
                $send    = isset( $_POST['tta_sendgrid_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_sendgrid_api_key'] ) ) : '';
                $sandbox = isset( $_POST['tta_authnet_sandbox'] ) ? (int) $_POST['tta_authnet_sandbox'] : 0;

                if ( $sandbox ) {
                    update_option( 'tta_authnet_login_id_sandbox', $login, false );
                    update_option( 'tta_authnet_transaction_key_sandbox', $trans, false );
                } else {
                    update_option( 'tta_authnet_login_id_live', $login, false );
                    update_option( 'tta_authnet_transaction_key_live', $trans, false );
                }
                update_option( 'tta_sendgrid_api_key', $send, false );
                update_option( 'tta_authnet_sandbox', $sandbox ? 1 : 0, false );
                echo '<div class="updated"><p>' . esc_html__( 'API settings saved.', 'tta' ) . '</p></div>';
            }

            $convert_results = [];
            if ( isset( $_POST['tta_convert_subscription'] ) && check_admin_referer( 'tta_convert_subscription_action', 'tta_convert_subscription_nonce' ) ) {
                $transaction_ids_raw = isset( $_POST['tta_transaction_ids'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tta_transaction_ids'] ) ) : '';
                $transaction_ids     = array_filter( array_map( 'trim', preg_split( '/\s+/', $transaction_ids_raw ) ) );

                if ( empty( $transaction_ids ) ) {
                    echo '<div class="error"><p>' . esc_html__( 'No transaction ID provided.', 'tta' ) . '</p></div>';
                } else {
                    $api = new TTA_AuthorizeNet_API();

                    foreach ( $transaction_ids as $transaction_id ) {
                        $transaction_id = sanitize_text_field( $transaction_id );
                        $details        = $api->get_transaction_details( $transaction_id );

                        if ( empty( $details['success'] ) ) {
                            $error            = isset( $details['error'] ) ? $details['error'] : __( 'Transaction lookup failed', 'tta' );
                            $convert_results[] = sprintf( 'Transaction %s: %s', $transaction_id, $error );
                            echo '<div class="error"><p>' . esc_html( $error . ' (' . $transaction_id . ')' ) . '</p></div>';
                            continue;
                        }

                        $amount = (float) $details['amount'];
                        $email  = sanitize_email( $details['email'] );

                        $tag   = '';
                        $level = '';
                        if ( abs( $amount - 5.0 ) < 0.01 ) {
                            $tag   = 'Trying to Adult Basic Membership';
                            $level = 'basic';
                        } elseif ( abs( $amount - 10.0 ) < 0.01 ) {
                            $tag   = 'Trying to Adult Premium Membership';
                            $level = 'premium';
                        }

                        if ( $email ) {
                            global $wpdb;
                            $members_table = $wpdb->prefix . 'tta_members';
                            $existing_sub  = $wpdb->get_var( $wpdb->prepare( "SELECT subscription_id FROM {$members_table} WHERE email = %s", $email ) );
                            if ( $existing_sub ) {
                                $api->cancel_subscription( $existing_sub );
                            }
                        }

                        $result = $api->create_subscription_from_transaction( $transaction_id, $amount, $tag ?: 'Membership Subscription', $tag );

                        if ( empty( $result['success'] ) ) {
                            $error            = isset( $result['error'] ) ? $result['error'] : __( 'Subscription creation failed', 'tta' );
                            $convert_results[] = sprintf( 'Transaction %s: %s', $transaction_id, $error );
                            echo '<div class="error"><p>' . esc_html( $error . ' (' . $transaction_id . ')' ) . '</p></div>';
                            continue;
                        }

                        $subscription_id = $result['subscription_id'];

                        if ( $email ) {
                            global $wpdb;
                            $members_table = $wpdb->prefix . 'tta_members';
                            $member_id     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$members_table} WHERE email = %s", $email ) );
                            if ( $member_id ) {
                                $data = [
                                    'subscription_id'     => $subscription_id,
                                    'subscription_status' => 'active',
                                ];
                                if ( $level ) {
                                    $data['membership_level'] = $level;
                                }
                                $wpdb->update( $members_table, $data, [ 'id' => $member_id ] );
                            }
                        }

                        $convert_results[] = sprintf( 'Email: %s | Transaction: %s | Subscription: %s', $email ?: '[none]', $transaction_id, $subscription_id );
                        echo '<div class="updated"><p>' . esc_html__( 'Subscription created.', 'tta' ) . '</p></div>';
                    }
                }
            }

            $login_live    = get_option( 'tta_authnet_login_id_live', '' );
            $trans_live    = get_option( 'tta_authnet_transaction_key_live', '' );
            $login_sandbox = get_option( 'tta_authnet_login_id_sandbox', '' );
            $trans_sandbox = get_option( 'tta_authnet_transaction_key_sandbox', '' );
            $send          = get_option( 'tta_sendgrid_api_key', '' );
            $sandbox       = (int) get_option( 'tta_authnet_sandbox', 0 );
            $login         = $sandbox ? $login_sandbox : $login_live;
            $trans         = $sandbox ? $trans_sandbox : $trans_live;

            echo '<form method="post" action="?page=tta-settings&tab=api">';
            wp_nonce_field( 'tta_save_api_settings_action', 'tta_save_api_settings_nonce' );
            echo '<table class="form-table"><tbody>';
            echo '<tr><th scope="row"><label for="tta_authnet_sandbox">' . esc_html__( 'Authorize.Net Environment', 'tta' ) . '</label></th><td><select id="tta_authnet_sandbox" name="tta_authnet_sandbox"><option value="0"' . selected( $sandbox, 0, false ) . '>' . esc_html__( 'Live', 'tta' ) . '</option><option value="1"' . selected( $sandbox, 1, false ) . '>' . esc_html__( 'Sandbox', 'tta' ) . '</option></select></td></tr>';
            echo '<tr><th scope="row"><label for="tta_authnet_login_id">' . esc_html__( 'Authorize.Net Login ID', 'tta' ) . '</label></th><td><input type="password" id="tta_authnet_login_id" name="tta_authnet_login_id" value="' . esc_attr( $login ) . '" /> <button type="button" class="button tta-reveal" data-target="tta_authnet_login_id">' . esc_html__( 'Reveal', 'tta' ) . '</button></td></tr>';
            echo '<tr><th scope="row"><label for="tta_authnet_transaction_key">' . esc_html__( 'Authorize.Net Transaction Key', 'tta' ) . '</label></th><td><input type="password" id="tta_authnet_transaction_key" name="tta_authnet_transaction_key" value="' . esc_attr( $trans ) . '" /> <button type="button" class="button tta-reveal" data-target="tta_authnet_transaction_key">' . esc_html__( 'Reveal', 'tta' ) . '</button></td></tr>';
            echo '<tr><th scope="row"><label for="tta_sendgrid_api_key">' . esc_html__( 'SendGrid API Key', 'tta' ) . '</label></th><td><input type="password" id="tta_sendgrid_api_key" name="tta_sendgrid_api_key" value="' . esc_attr( $send ) . '" /> <button type="button" class="button tta-reveal" data-target="tta_sendgrid_api_key">' . esc_html__( 'Reveal', 'tta' ) . '</button></td></tr>';
            echo '</tbody></table>';
            echo '<p><input type="submit" name="tta_save_api_settings" class="button button-primary" value="' . esc_attr__( 'Save API Settings', 'tta' ) . '"></p>';
            echo '</form>';

            echo '<hr><h2>' . esc_html__( 'Convert Transaction to Subscription', 'tta' ) . '</h2>';
            echo '<form method="post" action="?page=tta-settings&tab=api">';
            wp_nonce_field( 'tta_convert_subscription_action', 'tta_convert_subscription_nonce' );
            echo '<p><label for="tta_transaction_ids">' . esc_html__( 'Transaction IDs', 'tta' ) . '</label><br /><textarea id="tta_transaction_ids" name="tta_transaction_ids" rows="8" cols="40"></textarea><br /><span class="description">' . esc_html__( 'Enter one transaction ID per line.', 'tta' ) . '</span></p>';
            echo '<p><input type="submit" name="tta_convert_subscription" class="button button-secondary" value="' . esc_attr__( 'Convert to Subscription', 'tta' ) . '"></p>';
            echo '</form>';

            if ( ! empty( $convert_results ) ) {
                echo '<h3>' . esc_html__( 'Results', 'tta' ) . '</h3>';
                echo '<textarea readonly style="width:100%;height:200px;">' . esc_html( implode( "\n", $convert_results ) ) . '</textarea>';
            }

            echo '<script>document.querySelectorAll(".tta-reveal").forEach(function(btn){btn.addEventListener("click",function(){var t=document.getElementById(btn.dataset.target);if(t.type==="password"){t.type="text";btn.textContent="' . esc_js( __( 'Hide', 'tta' ) ) . '";}else{t.type="password";btn.textContent="' . esc_js( __( 'Reveal', 'tta' ) ) . '";}});});</script>';
        } else {
            if ( isset( $_POST['tta_flush_cache'] ) && check_admin_referer( 'tta_flush_cache_action', 'tta_flush_cache_nonce' ) ) {
                TTA_Cache::flush();
                echo '<div class="updated"><p>' . esc_html__( 'All caches cleared.', 'tta' ) . '</p></div>';
            }

            if ( isset( $_POST['tta_load_sample_data'] ) && check_admin_referer( 'tta_load_sample_data_action', 'tta_load_sample_data_nonce' ) ) {
                TTA_Sample_Data::load();
                echo '<div class="updated"><p>' . esc_html__( 'Sample data loaded.', 'tta' ) . '</p></div>';
            }

            if ( isset( $_POST['tta_delete_sample_data'] ) && check_admin_referer( 'tta_delete_sample_data_action', 'tta_delete_sample_data_nonce' ) ) {
                TTA_Sample_Data::clear();
                echo '<div class="updated"><p>' . esc_html__( 'Sample data deleted.', 'tta' ) . '</p></div>';
            }

            echo '<form method="post" action="?page=tta-settings&tab=general">';
            wp_nonce_field( 'tta_flush_cache_action', 'tta_flush_cache_nonce' );
            echo '<p><input type="submit" name="tta_flush_cache" class="button button-secondary" value="' . esc_attr__( 'Clear Cache', 'tta' ) . '"></p>';
            echo '</form>';

            echo '<form method="post" action="?page=tta-settings&tab=general">';
            wp_nonce_field( 'tta_load_sample_data_action', 'tta_load_sample_data_nonce' );
            echo '<p><input type="submit" name="tta_load_sample_data" class="button button-secondary" value="' . esc_attr__( 'Load Sample Data', 'tta' ) . '"></p>';
            echo '</form>';

            echo '<form method="post" action="?page=tta-settings&tab=general">';
            wp_nonce_field( 'tta_delete_sample_data_action', 'tta_delete_sample_data_nonce' );
            echo '<p><input type="submit" name="tta_delete_sample_data" class="button button-secondary" value="' . esc_attr__( 'Delete Sample Data', 'tta' ) . '"></p>';
            echo '</form>';

            echo '<div id="tta-authnet-test-wrapper">';
            echo '<p>';
            echo '<button id="tta-authnet-test-button" class="button button-secondary">Authorize.net testing</button>';
            echo '<span class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ) . '" alt="" style="display:none;"></span>';
            echo '</p>';
            echo '<p class="tta-admin-progress-response-p"></p>';
            echo '</div>';
        }

        echo '</div>';
    }
}

TTA_Settings_Admin::get_instance();
