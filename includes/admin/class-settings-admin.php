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
        echo '<a href="?page=tta-settings&tab=slider" class="nav-tab ' . ( 'slider' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Slider Images', 'tta' ) . '</a>';
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
                $login           = isset( $_POST['tta_authnet_login_id'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_authnet_login_id'] ) ) : '';
                $trans           = isset( $_POST['tta_authnet_transaction_key'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_authnet_transaction_key'] ) ) : '';
                $client          = isset( $_POST['tta_authnet_client_key'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_authnet_client_key'] ) ) : '';
                $twilio_user_sid   = isset( $_POST['tta_twilio_user_sid'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_twilio_user_sid'] ) ) : '';
                $twilio_api_sid    = isset( $_POST['tta_twilio_api_sid'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_twilio_api_sid'] ) ) : '';
                $twilio_api_key    = isset( $_POST['tta_twilio_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_twilio_api_key'] ) ) : '';
                $twilio_service    = isset( $_POST['tta_twilio_messaging_service_sid'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_twilio_messaging_service_sid'] ) ) : '';
                $twilio_number     = isset( $_POST['tta_twilio_sending_number'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_twilio_sending_number'] ) ) : '';
                $twilio_env        = isset( $_POST['tta_twilio_environment'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_twilio_environment'] ) ) : 'live';
                $twilio_sandbox_to = isset( $_POST['tta_twilio_sandbox_number'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_twilio_sandbox_number'] ) ) : '';
                $sandbox           = isset( $_POST['tta_authnet_sandbox'] ) ? (int) $_POST['tta_authnet_sandbox'] : 0;

                if ( ! in_array( $twilio_env, [ 'live', 'sandbox' ], true ) ) {
                    $twilio_env = 'live';
                }

                if ( $sandbox ) {
                    update_option( 'tta_authnet_login_id_sandbox', $login, false );
                    update_option( 'tta_authnet_transaction_key_sandbox', $trans, false );
                    update_option( 'tta_authnet_public_client_key_sandbox', $client, false );
                } else {
                    update_option( 'tta_authnet_login_id_live', $login, false );
                    update_option( 'tta_authnet_transaction_key_live', $trans, false );
                    update_option( 'tta_authnet_public_client_key_live', $client, false );
                }
                update_option( 'tta_twilio_user_sid', $twilio_user_sid, false );
                update_option( 'tta_twilio_api_sid', $twilio_api_sid, false );
                update_option( 'tta_twilio_api_key', $twilio_api_key, false );
                update_option( 'tta_twilio_messaging_service_sid', $twilio_service, false );
                update_option( 'tta_twilio_sending_number', $twilio_number, false );
                update_option( 'tta_twilio_environment', $twilio_env, false );
                update_option( 'tta_twilio_sandbox_number', $twilio_sandbox_to, false );
                delete_option( 'tta_sendgrid_api_key' );
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
                            $tag   = 'Trying to Adult Standard Membership';
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

            $login_live     = get_option( 'tta_authnet_login_id_live', '' );
            $trans_live     = get_option( 'tta_authnet_transaction_key_live', '' );
            $client_live    = get_option( 'tta_authnet_public_client_key_live', '' );
            $login_sandbox  = get_option( 'tta_authnet_login_id_sandbox', '' );
            $trans_sandbox  = get_option( 'tta_authnet_transaction_key_sandbox', '' );
            $client_sandbox = get_option( 'tta_authnet_public_client_key_sandbox', '' );
            $twilio_user_sid    = get_option( 'tta_twilio_user_sid', '' );
            $twilio_api_sid     = get_option( 'tta_twilio_api_sid', '' );
            $twilio_api_key     = get_option( 'tta_twilio_api_key', '' );
            $twilio_service     = get_option( 'tta_twilio_messaging_service_sid', '' );
            $twilio_number      = get_option( 'tta_twilio_sending_number', '' );
            $twilio_env         = get_option( 'tta_twilio_environment', 'live' );
            $twilio_sandbox_to  = get_option( 'tta_twilio_sandbox_number', '' );
            $sandbox            = (int) get_option( 'tta_authnet_sandbox', 0 );
            $login              = $sandbox ? $login_sandbox : $login_live;
            $trans              = $sandbox ? $trans_sandbox : $trans_live;
            $client             = $sandbox ? $client_sandbox : $client_live;

            if ( ! in_array( $twilio_env, [ 'live', 'sandbox' ], true ) ) {
                $twilio_env = 'live';
            }

            echo '<form method="post" action="?page=tta-settings&tab=api">';
            wp_nonce_field( 'tta_save_api_settings_action', 'tta_save_api_settings_nonce' );
            echo '<table class="form-table"><tbody>';
            echo '<tr><th scope="row"><label for="tta_authnet_sandbox">' . esc_html__( 'Authorize.Net Environment', 'tta' ) . '</label></th><td><select id="tta_authnet_sandbox" name="tta_authnet_sandbox"><option value="0"' . selected( $sandbox, 0, false ) . '>' . esc_html__( 'Live', 'tta' ) . '</option><option value="1"' . selected( $sandbox, 1, false ) . '>' . esc_html__( 'Sandbox', 'tta' ) . '</option></select></td></tr>';
            echo '<tr><th scope="row"><label for="tta_authnet_login_id">' . esc_html__( 'Authorize.Net Login ID', 'tta' ) . '</label></th><td><input type="password" id="tta_authnet_login_id" name="tta_authnet_login_id" value="' . esc_attr( $login ) . '" /> <button type="button" class="button tta-reveal" data-target="tta_authnet_login_id">' . esc_html__( 'Reveal', 'tta' ) . '</button></td></tr>';
            echo '<tr><th scope="row"><label for="tta_authnet_transaction_key">' . esc_html__( 'Authorize.Net Transaction Key', 'tta' ) . '</label></th><td><input type="password" id="tta_authnet_transaction_key" name="tta_authnet_transaction_key" value="' . esc_attr( $trans ) . '" /> <button type="button" class="button tta-reveal" data-target="tta_authnet_transaction_key">' . esc_html__( 'Reveal', 'tta' ) . '</button></td></tr>';
            echo '<tr><th scope="row"><label for="tta_authnet_client_key">' . esc_html__( 'Authorize.Net Client Key', 'tta' ) . '</label></th><td><input type="password" id="tta_authnet_client_key" name="tta_authnet_client_key" value="' . esc_attr( $client ) . '" /> <button type="button" class="button tta-reveal" data-target="tta_authnet_client_key">' . esc_html__( 'Reveal', 'tta' ) . '</button></td></tr>';
            echo '<tr><th scope="row"><label for="tta_twilio_user_sid">' . esc_html__( 'Twilio User SID', 'tta' ) . '</label></th><td><input type="text" id="tta_twilio_user_sid" name="tta_twilio_user_sid" value="' . esc_attr( $twilio_user_sid ) . '" /></td></tr>';
            echo '<tr><th scope="row"><label for="tta_twilio_api_sid">' . esc_html__( 'Twilio API SID', 'tta' ) . '</label></th><td><input type="text" id="tta_twilio_api_sid" name="tta_twilio_api_sid" value="' . esc_attr( $twilio_api_sid ) . '" /></td></tr>';
            echo '<tr><th scope="row"><label for="tta_twilio_api_key">' . esc_html__( 'Twilio API Key', 'tta' ) . '</label></th><td><input type="password" id="tta_twilio_api_key" name="tta_twilio_api_key" value="' . esc_attr( $twilio_api_key ) . '" /> <button type="button" class="button tta-reveal" data-target="tta_twilio_api_key">' . esc_html__( 'Reveal', 'tta' ) . '</button></td></tr>';
            echo '<tr><th scope="row"><label for="tta_twilio_messaging_service_sid">' . esc_html__( 'Messaging Service SID', 'tta' ) . '</label></th><td><input type="text" id="tta_twilio_messaging_service_sid" name="tta_twilio_messaging_service_sid" value="' . esc_attr( $twilio_service ) . '" /></td></tr>';
            echo '<tr><th scope="row"><label for="tta_twilio_sending_number">' . esc_html__( 'Twilio Sending Number', 'tta' ) . '</label></th><td><input type="text" id="tta_twilio_sending_number" name="tta_twilio_sending_number" value="' . esc_attr( $twilio_number ) . '" /></td></tr>';
            echo '<tr><th scope="row"><label for="tta_twilio_environment">' . esc_html__( 'Twilio Environment', 'tta' ) . '</label></th><td><select id="tta_twilio_environment" name="tta_twilio_environment"><option value="live"' . selected( $twilio_env, 'live', false ) . '>' . esc_html__( 'Live', 'tta' ) . '</option><option value="sandbox"' . selected( $twilio_env, 'sandbox', false ) . '>' . esc_html__( 'Sandbox', 'tta' ) . '</option></select></td></tr>';
            echo '<tr><th scope="row"><label for="tta_twilio_sandbox_number">' . esc_html__( 'Twilio Sandbox Number', 'tta' ) . '</label></th><td><input type="text" id="tta_twilio_sandbox_number" name="tta_twilio_sandbox_number" value="' . esc_attr( $twilio_sandbox_to ) . '" /></td></tr>';
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

            echo '<hr><h2>' . esc_html__( 'Test Twilio Sandbox SMS', 'tta' ) . '</h2>';
            echo '<form method="post" action="?page=tta-settings&tab=api" class="tta-twilio-sandbox-test">';
            wp_nonce_field( 'tta_test_twilio_sms_action', 'tta_test_twilio_sms_nonce' );
            echo '<p><label for="tta_twilio_test_message">' . esc_html__( 'Message Content', 'tta' ) . '</label><br />';
            echo '<textarea id="tta_twilio_test_message" name="tta_twilio_test_message" rows="6" cols="60" placeholder="' . esc_attr__( 'Type a message to send to the sandbox number.', 'tta' ) . '"></textarea></p>';
            echo '<p class="description">' . esc_html__( 'Messages from this form will always be delivered to the configured Twilio sandbox number.', 'tta' );
            if ( ! empty( $twilio_sandbox_to ) ) {
                echo ' ' . esc_html__( 'Current sandbox recipient:', 'tta' ) . ' <code>' . esc_html( $twilio_sandbox_to ) . '</code>';
            } else {
                echo ' ' . esc_html__( 'No sandbox number is currently configured.', 'tta' );
            }
            echo '</p>';
            echo '<p><input type="submit" name="tta_send_test_twilio_sms" class="button button-secondary" value="' . esc_attr__( 'Send Test SMS', 'tta' ) . '"></p>';
            echo '</form>';

            echo '<div class="tta-twilio-test-feedback">';
            echo '<h3>' . esc_html__( 'Debug Output', 'tta' ) . '</h3>';
            echo '<p class="description">' . esc_html__( 'When implemented, this area will display endpoint details, variables, payloads, and API responses for troubleshooting.', 'tta' ) . '</p>';
            echo '<table class="widefat striped" style="max-width:800px;">';
            echo '<tbody>';
            echo '<tr><th scope="row">' . esc_html__( 'API Endpoint', 'tta' ) . '</th><td><code id="tta_twilio_test_endpoint">' . esc_html__( 'Pending implementation', 'tta' ) . '</code></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'Request Variables', 'tta' ) . '</th><td><pre id="tta_twilio_test_variables" style="white-space:pre-wrap;">' . esc_html__( 'Pending implementation', 'tta' ) . '</pre></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'Payload', 'tta' ) . '</th><td><pre id="tta_twilio_test_payload" style="white-space:pre-wrap;">' . esc_html__( 'Pending implementation', 'tta' ) . '</pre></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'API Response', 'tta' ) . '</th><td><pre id="tta_twilio_test_response" style="white-space:pre-wrap;">' . esc_html__( 'Pending implementation', 'tta' ) . '</pre></td></tr>';
            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            echo '<script>document.querySelectorAll(".tta-reveal").forEach(function(btn){btn.addEventListener("click",function(){var t=document.getElementById(btn.dataset.target);if(t.type==="password"){t.type="text";btn.textContent="' . esc_js( __( 'Hide', 'tta' ) ) . '";}else{t.type="password";btn.textContent="' . esc_js( __( 'Reveal', 'tta' ) ) . '";}});});</script>';
        } elseif ( 'slider' === $active_tab ) {
            if ( isset( $_POST['tta_save_slider_images'] ) && check_admin_referer( 'tta_save_slider_images_action', 'tta_save_slider_images_nonce' ) ) {
                $ids_raw = isset( $_POST['tta_slider_image_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['tta_slider_image_ids'] ) ) : '';
                $ids     = array_filter( array_map( 'absint', array_filter( array_map( 'trim', explode( ',', $ids_raw ) ) ) ) );
                update_option( 'tta_slider_images', $ids, false );
                echo '<div class="updated"><p>' . esc_html__( 'Slider images saved.', 'tta' ) . '</p></div>';
            }

            $ids = get_option( 'tta_slider_images', [] );
            echo '<form method="post" action="?page=tta-settings&tab=slider">';
            wp_nonce_field( 'tta_save_slider_images_action', 'tta_save_slider_images_nonce' );
            echo '<p><button type="button" class="button tta-upload-multiple" data-target="#tta_slider_image_ids" data-preview="#tta_slider_images_preview">' . esc_html__( 'Select Images', 'tta' ) . '</button></p>';
            echo '<div id="tta_slider_images_preview">';
            foreach ( $ids as $id ) {
                $thumb = wp_get_attachment_image_url( intval( $id ), 'thumbnail' );
                if ( $thumb ) {
                    echo '<img src="' . esc_url( $thumb ) . '" style="max-width:100px;margin-right:5px;" />';
                }
            }
            echo '</div>';
            echo '<input type="hidden" id="tta_slider_image_ids" name="tta_slider_image_ids" value="' . esc_attr( implode( ',', $ids ) ) . '">';
            echo '<p><input type="submit" name="tta_save_slider_images" class="button button-primary" value="' . esc_attr__( 'Save Slider Images', 'tta' ) . '"></p>';
            echo '</form>';
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
