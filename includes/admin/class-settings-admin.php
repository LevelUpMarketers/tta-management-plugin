<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Twilio\Rest\Client;

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

                        $result = $api->create_subscription_from_transaction(
                            $transaction_id,
                            $amount,
                            $tag ?: 'Membership Subscription',
                            $tag,
                            null,
                            [
                                'allow_deferred'   => false,
                                'retry_origin'     => 'admin',
                                'membership_level' => $level,
                            ]
                        );

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

            $twilio_test_debug   = [
                'endpoint'  => '',
                'variables' => null,
                'payload'   => null,
                'response'  => null,
            ];
            $twilio_test_message = '';
            $twilio_render_tokens = false;

            $sandbox_display_value = defined( 'TTA_TWILIO_SANDBOX_NUMBER' ) && TTA_TWILIO_SANDBOX_NUMBER ? TTA_TWILIO_SANDBOX_NUMBER : $twilio_sandbox_to;

            if ( isset( $_POST['tta_send_test_twilio_sms'] ) && check_admin_referer( 'tta_test_twilio_sms_action', 'tta_test_twilio_sms_nonce' ) ) {
                $twilio_test_message = isset( $_POST['tta_twilio_test_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tta_twilio_test_message'] ) ) : '';
                $twilio_render_tokens = isset( $_POST['tta_twilio_render_tokens'] ) ? (bool) $_POST['tta_twilio_render_tokens'] : $this->contains_template_tokens( $twilio_test_message );
                $has_template_tokens  = $this->contains_template_tokens( $twilio_test_message );
                $should_render_tokens = $twilio_render_tokens && $has_template_tokens;

                $display_env           = defined( 'TTA_TWILIO_ENVIRONMENT' ) ? sanitize_key( TTA_TWILIO_ENVIRONMENT ) : $twilio_env;
                $sandbox_destination   = $sandbox_display_value;
                $messaging_service_sid = defined( 'TTA_TWILIO_MESSAGING_SERVICE_SID' ) && TTA_TWILIO_MESSAGING_SERVICE_SID ? TTA_TWILIO_MESSAGING_SERVICE_SID : $twilio_service;
                $from_number           = defined( 'TTA_TWILIO_SENDING_NUMBER' ) && TTA_TWILIO_SENDING_NUMBER ? TTA_TWILIO_SENDING_NUMBER : $twilio_number;

                $twilio_test_debug['variables'] = [
                    'environment'        => $display_env,
                    'sandbox_recipient'  => $sandbox_destination,
                    'using_service_sid'  => (bool) $messaging_service_sid,
                    'using_from_number'  => (bool) $from_number,
                    'message_length'     => strlen( $twilio_test_message ),
                    'rendering_tokens'   => $should_render_tokens,
                ];

                $error_message = '';

                if ( '' === $twilio_test_message ) {
                    $error_message = esc_html__( 'Please enter a test message before sending.', 'tta' );
                } elseif ( empty( $sandbox_destination ) ) {
                    $error_message = esc_html__( 'Unable to send test SMS because no Twilio sandbox number is configured.', 'tta' );
                } elseif ( ! class_exists( Client::class ) ) {
                    $error_message = esc_html__( 'The Twilio PHP SDK is not available. Please ensure the library is installed.', 'tta' );
                }

                $auth_sid    = '';
                $auth_token  = '';
                $account_sid = '';
                $mode        = '';

                if ( ! $error_message ) {
                    if ( defined( 'TTA_TWILIO_API_SID' ) && TTA_TWILIO_API_SID && defined( 'TTA_TWILIO_API_KEY' ) && TTA_TWILIO_API_KEY ) {
                        $auth_sid    = TTA_TWILIO_API_SID;
                        $auth_token  = TTA_TWILIO_API_KEY;
                        $account_sid = defined( 'TTA_TWILIO_USER_SID' ) && TTA_TWILIO_USER_SID ? TTA_TWILIO_USER_SID : ( defined( 'TTA_TWILIO_SID' ) && TTA_TWILIO_SID ? TTA_TWILIO_SID : '' );
                        $mode        = 'api_key';
                    } elseif ( defined( 'TTA_TWILIO_SID' ) && TTA_TWILIO_SID && defined( 'TTA_TWILIO_TOKEN' ) && TTA_TWILIO_TOKEN ) {
                        $auth_sid    = TTA_TWILIO_SID;
                        $auth_token  = TTA_TWILIO_TOKEN;
                        $account_sid = TTA_TWILIO_SID;
                        $mode        = 'auth_token';
                    }

                    if ( ! $auth_sid || ! $auth_token ) {
                        $error_message = esc_html__( 'Twilio credentials are incomplete. Provide an API SID and Key or Account SID and Token before sending a test SMS.', 'tta' );
                    } elseif ( 'api_key' === $mode && ! $account_sid ) {
                        $error_message = esc_html__( 'A Twilio Account SID is required when using API Keys. Update the Twilio settings and try again.', 'tta' );
                    }
                }

                if ( ! $error_message ) {
                    $twilio_test_debug['variables']['credential_mode'] = $mode;
                    $twilio_test_debug['variables']['account_sid']     = $this->mask_sensitive_value( $account_sid );
                    $twilio_test_debug['variables']['auth_sid']        = $this->mask_sensitive_value( $auth_sid );

                    $message_body    = $this->maybe_compile_sms_template( $twilio_test_message, $should_render_tokens );
                    $message_args    = [ 'body' => $message_body ];
                    $payload_display = [
                        'to'   => $sandbox_destination,
                        'body' => $message_body,
                    ];

                    if ( $messaging_service_sid ) {
                        $message_args['messagingServiceSid'] = $messaging_service_sid;
                        $payload_display['messagingServiceSid'] = $messaging_service_sid;
                    } elseif ( $from_number ) {
                        $message_args['from'] = $from_number;
                        $payload_display['from'] = $from_number;
                    } else {
                        $error_message = esc_html__( 'Configure a Twilio Messaging Service SID or Sending Number before sending a test SMS.', 'tta' );
                    }

                    $twilio_test_debug['payload'] = $payload_display;
                }

                if ( $error_message ) {
                    $twilio_test_debug['response'] = [ 'error' => $error_message ];
                    echo '<div class="error"><p>' . esc_html( $error_message ) . '</p></div>';
                } else {
                    try {
                        $client = 'api_key' === $mode ? new Client( $auth_sid, $auth_token, $account_sid ) : new Client( $auth_sid, $auth_token );

                        $account_for_endpoint           = $account_sid ? $account_sid : $auth_sid;
                        $twilio_test_debug['endpoint'] = sprintf( 'https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', rawurlencode( $account_for_endpoint ) );

                        $response = $client->messages->create( $sandbox_destination, $message_args );

                        $twilio_test_debug['response'] = $this->format_twilio_response( $response );

                        echo '<div class="updated"><p>' . esc_html__( 'Test SMS sent successfully. Check the sandbox number for delivery.', 'tta' ) . '</p></div>';
                        TTA_Debug_Logger::log( sprintf( 'Twilio sandbox test SMS sent to %s (SID: %s)', $sandbox_destination, $response->sid ) );
                        $twilio_test_message = '';
                    } catch ( \Exception $e ) {
                        $error = sprintf( __( 'Twilio returned an error while sending the test SMS: %s', 'tta' ), $e->getMessage() );
                        $twilio_test_debug['response'] = [ 'error' => $e->getMessage() ];
                        echo '<div class="error"><p>' . esc_html( $error ) . '</p></div>';
                        TTA_Debug_Logger::log( 'Twilio sandbox test SMS failed: ' . $e->getMessage() );
                    }
                }
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

            $twilio_endpoint_display = $twilio_test_debug['endpoint'] ? $twilio_test_debug['endpoint'] : __( 'Pending implementation', 'tta' );

            $twilio_variables_display = __( 'Pending implementation', 'tta' );
            if ( null !== $twilio_test_debug['variables'] ) {
                $encoded = wp_json_encode( $twilio_test_debug['variables'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
                if ( false !== $encoded ) {
                    $twilio_variables_display = $encoded;
                } else {
                    $twilio_variables_display = print_r( $twilio_test_debug['variables'], true );
                }
            }

            $twilio_payload_display = __( 'Pending implementation', 'tta' );
            if ( null !== $twilio_test_debug['payload'] ) {
                $encoded = wp_json_encode( $twilio_test_debug['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
                if ( false !== $encoded ) {
                    $twilio_payload_display = $encoded;
                } else {
                    $twilio_payload_display = print_r( $twilio_test_debug['payload'], true );
                }
            }

            $twilio_response_display = __( 'Pending implementation', 'tta' );
            if ( null !== $twilio_test_debug['response'] ) {
                $encoded = wp_json_encode( $twilio_test_debug['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
                if ( false !== $encoded ) {
                    $twilio_response_display = $encoded;
                } else {
                    $twilio_response_display = print_r( $twilio_test_debug['response'], true );
                }
            }

            echo '<hr><h2>' . esc_html__( 'Test Twilio Sandbox SMS', 'tta' ) . '</h2>';
            echo '<form method="post" action="?page=tta-settings&tab=api" class="tta-twilio-sandbox-test">';
            wp_nonce_field( 'tta_test_twilio_sms_action', 'tta_test_twilio_sms_nonce' );
            echo '<p><label for="tta_twilio_test_message">' . esc_html__( 'Message Content', 'tta' ) . '</label><br />';
            echo '<textarea id="tta_twilio_test_message" name="tta_twilio_test_message" rows="6" cols="60" placeholder="' . esc_attr__( 'Type a message to send to the sandbox number.', 'tta' ) . '">' . esc_textarea( $twilio_test_message ) . '</textarea></p>';
            $twilio_render_tokens = $twilio_render_tokens || $this->contains_template_tokens( $twilio_test_message );
            echo '<p><label><input type="checkbox" name="tta_twilio_render_tokens" value="1"' . checked( $twilio_render_tokens, true, false ) . '> ' . esc_html__( 'Render template tokens using sample preview data before sending', 'tta' ) . '</label><br />';
            echo '<span class="description">' . esc_html__( 'Leave unchecked to send the exact text, or enable to replace tokens like {event_name} with sample values.', 'tta' ) . '</span></p>';
            echo '<p class="description">' . esc_html__( 'Messages from this form will always be delivered to the configured Twilio sandbox number.', 'tta' );
            if ( ! empty( $sandbox_display_value ) ) {
                echo ' ' . esc_html__( 'Current sandbox recipient:', 'tta' ) . ' <code>' . esc_html( $sandbox_display_value ) . '</code>';
            } else {
                echo ' ' . esc_html__( 'No sandbox number is currently configured.', 'tta' );
            }
            echo '</p>';
            echo '<p><input type="submit" name="tta_send_test_twilio_sms" class="button button-secondary" value="' . esc_attr__( 'Send Test SMS', 'tta' ) . '"></p>';
            echo '</form>';

            echo '<div class="tta-twilio-test-feedback">';
            echo '<h3>' . esc_html__( 'Debug Output', 'tta' ) . '</h3>';
            echo '<p class="description">' . esc_html__( 'Use this information to troubleshoot sandbox deliveries. Values are masked where appropriate.', 'tta' ) . '</p>';
            echo '<table class="widefat striped" style="max-width:800px;">';
            echo '<tbody>';
            echo '<tr><th scope="row">' . esc_html__( 'API Endpoint', 'tta' ) . '</th><td><code id="tta_twilio_test_endpoint">' . esc_html( $twilio_endpoint_display ) . '</code></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'Request Variables', 'tta' ) . '</th><td><pre id="tta_twilio_test_variables" style="white-space:pre-wrap;">' . esc_html( $twilio_variables_display ) . '</pre></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'Payload', 'tta' ) . '</th><td><pre id="tta_twilio_test_payload" style="white-space:pre-wrap;">' . esc_html( $twilio_payload_display ) . '</pre></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'API Response', 'tta' ) . '</th><td><pre id="tta_twilio_test_response" style="white-space:pre-wrap;">' . esc_html( $twilio_response_display ) . '</pre></td></tr>';
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

    private function contains_template_tokens( $message ) {
        return (bool) preg_match( '/\{[a-z0-9_]+\}/i', (string) $message );
    }

    private function maybe_compile_sms_template( $template, $render_tokens ) {
        $template = (string) $template;

        if ( ! $render_tokens || '' === trim( $template ) || ! $this->contains_template_tokens( $template ) ) {
            return $template;
        }

        if ( ! class_exists( 'TTA_SMS_Handler' ) ) {
            return $template;
        }

        $handler = TTA_SMS_Handler::get_instance();
        if ( ! $handler || ! method_exists( $handler, 'compile_message' ) ) {
            return $template;
        }

        $context  = $this->get_sample_sms_context();
        $compiled = $handler->compile_message( $template, $context['event'], $context['member'], $context['attendees'], $context['refund'] );

        return '' !== $compiled ? $compiled : $template;
    }

    private function get_sample_sms_context() {
        $event = tta_get_next_event();

        if ( $event ) {
            $event['page_url']        = isset( $event['page_url'] ) ? $event['page_url'] : ( ! empty( $event['page_id'] ) ? get_permalink( intval( $event['page_id'] ) ) : home_url( '/events/' ) );
            $event['date_formatted']  = $event['date_formatted'] ?? ( isset( $event['date'] ) ? tta_format_event_date( $event['date'] ) : '' );
            $event['time_formatted']  = $event['time_formatted'] ?? ( isset( $event['time'] ) ? tta_format_event_time( $event['time'] ) : '' );
            $event['base_cost']       = isset( $event['base_cost'] ) ? (float) $event['base_cost'] : ( isset( $event['baseeventcost'] ) ? (float) $event['baseeventcost'] : 0 );
            $event['member_cost']     = isset( $event['member_cost'] ) ? (float) $event['member_cost'] : ( isset( $event['discountedmembercost'] ) ? (float) $event['discountedmembercost'] : 0 );
            $event['premium_cost']    = isset( $event['premium_cost'] ) ? (float) $event['premium_cost'] : ( isset( $event['premiummembercost'] ) ? (float) $event['premiummembercost'] : 0 );
            $event['host_notes']      = isset( $event['host_notes'] ) ? $event['host_notes'] : '';
        } else {
            $event = [
                'id'           => 0,
                'name'         => __( 'Sample Event', 'tta' ),
                'date'         => gmdate( 'Y-m-d', strtotime( '+7 days' ) ),
                'time'         => '18:00:00',
                'address'      => __( '123 Example St, Richmond, VA', 'tta' ),
                'page_id'      => 0,
                'page_url'     => home_url( '/events/' ),
                'type'         => __( 'Social', 'tta' ),
                'venue_name'   => __( 'Sample Venue', 'tta' ),
                'venue_url'    => home_url( '/venues/sample' ),
                'base_cost'    => 25.00,
                'member_cost'  => 15.00,
                'premium_cost' => 10.00,
                'host_notes'   => '',
            ];
            $event['date_formatted'] = tta_format_event_date( $event['date'] );
            $event['time_formatted'] = tta_format_event_time( $event['time'] );
        }

        $sample_member = tta_get_sample_member();
        $primary_phone = sanitize_text_field( $sample_member['phone'] ?? '555-0100' );
        $primary_email = sanitize_email( $sample_member['email'] ?? 'member@example.com' );
        $primary_first = sanitize_text_field( $sample_member['first_name'] ?? __( 'Sample', 'tta' ) );
        $primary_last  = sanitize_text_field( $sample_member['last_name'] ?? __( 'Member', 'tta' ) );

        $attendees = [
            [
                'first_name' => $primary_first,
                'last_name'  => $primary_last,
                'email'      => $primary_email,
                'phone'      => $primary_phone,
            ],
            [
                'first_name' => __( 'Guest', 'tta' ),
                'last_name'  => __( 'Two', 'tta' ),
                'email'      => 'guest@example.com',
                'phone'      => '555-0102',
            ],
        ];

        $member = [
            'first_name'        => $primary_first,
            'last_name'         => $primary_last,
            'user_email'        => $primary_email,
            'member'            => [
                'phone'       => $primary_phone,
                'member_type' => sanitize_text_field( $sample_member['member_type'] ?? 'member' ),
            ],
            'membership_level'  => sanitize_text_field( $sample_member['membership_level'] ?? 'free' ),
            'subscription_id'   => 'SUB12345',
            'subscription_status' => 'active',
        ];

        return [
            'event'     => $event,
            'member'    => $member,
            'attendees' => $attendees,
            'refund'    => [],
        ];
    }

    private function mask_sensitive_value( $value ) {
        $value = (string) $value;

        if ( '' === $value ) {
            return '';
        }

        $length = strlen( $value );

        if ( $length <= 4 ) {
            return str_repeat( '*', $length );
        }

        return str_repeat( '*', $length - 4 ) . substr( $value, -4 );
    }

    private function format_twilio_response( $response ) {
        if ( ! is_object( $response ) ) {
            return $response;
        }

        $date_created = null;
        if ( isset( $response->dateCreated ) ) {
            if ( $response->dateCreated instanceof \DateTimeInterface ) {
                $date_created = $response->dateCreated->format( DATE_ATOM );
            } else {
                $date_created = (string) $response->dateCreated;
            }
        }

        $data = [
            'sid'                   => isset( $response->sid ) ? $response->sid : '',
            'status'                => isset( $response->status ) ? $response->status : '',
            'to'                    => isset( $response->to ) ? $response->to : '',
            'from'                  => isset( $response->from ) ? $response->from : '',
            'messaging_service_sid' => isset( $response->messagingServiceSid ) ? $response->messagingServiceSid : '',
            'date_created'          => $date_created,
            'error_code'            => isset( $response->errorCode ) ? $response->errorCode : null,
            'error_message'         => isset( $response->errorMessage ) ? $response->errorMessage : null,
        ];

        return array_filter(
            $data,
            function ( $value ) {
                return null !== $value && '' !== $value;
            }
        );
    }
}

TTA_Settings_Admin::get_instance();
