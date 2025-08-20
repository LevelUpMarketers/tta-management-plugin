<?php
if (!defined('ABSPATH')) { exit; }

class TTA_Comms_Admin {
    public static function get_instance(){ static $inst; return $inst ?: $inst = new self(); }
    private function __construct(){ add_action('admin_menu', [ $this, 'register_menu' ]); }

    public function register_menu(){
        add_menu_page(
            'TTA Email & SMS',
            'TTA Email & SMS',
            'manage_options',
            'tta-comms',
            [ $this, 'render_page' ],
            'dashicons-email-alt',
            9.2
        );
    }

    public static function get_default_templates(){
        return [
            'purchase' => [
                'label'       => __('Successful Event Purchase', 'tta'),
                'type'        => 'External',
                'category'    => 'Event Confirmation',
                'description' => __('Sent after a member buys tickets to an event.', 'tta'),
                'email_subject' => __('Thanks for Registering!', 'tta'),
                'email_body'  => __("You're in! Thank for registering for our upcoming Trying To Adult event. The details of the event are below. Please keep this email, as you'll need to present this to the Event Host or Volunteer when arriving at your event.", 'tta'),
                'sms_text'    => __('Thanks for registering! View your upcoming events at ', 'tta'),
            ],
            'reminder_24hr' => [
                'label'       => __('24-Hour Event Reminder', 'tta'),
                'type'        => 'External',
                'category'    => 'Event Reminder',
                'description' => __('Reminder email to attendees one day before the event.', 'tta'),
                'email_subject' => __('Your event is tomorrow!', 'tta'),
                'email_body'  => __('Heads-up! Your event is just 1 day away! Below are the details.', 'tta'),
                'sms_text'    => __('Heads-Up! your event is tomorrow! View your upcoming events at ', 'tta'),
            ],
            'reminder_2hr' => [
                'label'       => __('2-Hour Event Reminder', 'tta'),
                'type'        => 'External',
                'category'    => 'Event Reminder',
                'description' => __('Reminder email to attendees two hours before the event.', 'tta'),
                'email_subject' => __('Event starting soon!', 'tta'),
                'email_body'  => __('Your event is only 2 hours away! Below are the details.', 'tta'),
                'sms_text'    => __('Only 2 hours to go! View your upcoming events at ', 'tta'),
            ],
            'refund_requested' => [
                'label'       => __('Refund Requested', 'tta'),
                'type'        => 'External',
                'category'    => 'Refund',
                'description' => __('Sent to a member when they request a refund.', 'tta'),
                'email_subject' => __('Refund request received', 'tta'),
                'email_body'  => __('We received your refund request for the event below. Our team will review and follow up soon.', 'tta'),
                'sms_text'    => '',
            ],
            'refund_processed' => [
                'label'       => __('Refund Processed', 'tta'),
                'type'        => 'External',
                'category'    => 'Refund',
                'description' => __('Notifies attendees when a refund request is approved and issued.', 'tta'),
                'email_subject' => __('Your refund has been issued', 'tta'),
                'email_body'  => __('Your refund request was approved and has been processed. We\'re sorry you couldn\'t make it, but we hope to see you at future events!', 'tta'),
                'sms_text'    => '',
            ],
            'banned_reinstatement' => [
                'label'       => __( 'Banned Reinstatement', 'tta' ),
                'type'        => 'External',
                'category'    => 'Ban',
                'description' => __( 'Sent when a member purchases a Re-Entry Ticket to lift a ban.', 'tta' ),
                'email_subject' => __( 'Welcome back!', 'tta' ),
                'email_body'  => __( 'Your account has been reinstated and you may now purchase event tickets.', 'tta' ),
                'sms_text'    => '',
            ],
            'no_show_limit' => [
                'label'       => __( 'No-Show Limit & Banned Status Notification', 'tta' ),
                'type'        => 'External',
                'category'    => 'Ban',
                'description' => __( 'Sent when a member accrues three no-shows and is banned until purchasing a Re-Entry Ticket.', 'tta' ),
                'email_subject' => __( 'No-Show Limit Reached', 'tta' ),
                'email_body'  => __( 'You have reached the no-show limit and are banned until you purchase a [Re-entry Ticket]({reentry_link}).', 'tta' ),
                'sms_text'    => '',
            ],
            'event_sold_out' => [
                'label'       => __('Event Sold Out', 'tta'),
                'type'        => 'Internal',
                'category'    => 'Admin Notice',
                'description' => __('Alert when an event reaches capacity.', 'tta'),
                'email_subject' => __('Event has sold out', 'tta'),
                'email_body'  => __('The following event is now sold out.', 'tta'),
                'sms_text'    => '',
            ],
            'waitlist_available' => [
                'label'       => __('Waitlist Spot Available', 'tta'),
                'type'        => 'External',
                'category'    => 'Waitlist',
                'description' => __('Notifies members when a ticket opens up.', 'tta'),
                'email_subject' => __('A ticket is available!', 'tta'),
                'email_body'  => __('Good news! A spot has opened for the event below. Grab your ticket before it\'s gone.', 'tta'),
                'sms_text'    => __('A ticket is available! Visit {event_link}', 'tta'),
            ],
            'host_reminder_24hr' => [
                'label'       => __('Host Reminder 24hr', 'tta'),
                'type'        => 'Internal',
                'category'    => 'Host Reminder',
                'description' => __('Reminder to event hosts one day before the event.', 'tta'),
                'email_subject' => __('You are hosting tomorrow', 'tta'),
                'email_body'  => __('Friendly reminder that you are hosting an event in 24 hours. Details are below.', 'tta'),
                'sms_text'    => '',
            ],
            'host_reminder_2hr' => [
                'label'       => __('Host Reminder 2hr', 'tta'),
                'type'        => 'Internal',
                'category'    => 'Host Reminder',
                'description' => __('Reminder to event hosts two hours before the event.', 'tta'),
                'email_subject' => __('Hosting duty soon', 'tta'),
                'email_body'  => __('Your hosting duties begin in two hours. See the event details below.', 'tta'),
                'sms_text'    => '',
            ],
            'volunteer_reminder_24hr' => [
                'label'       => __('Volunteer Reminder 24hr', 'tta'),
                'type'        => 'Internal',
                'category'    => 'Volunteer Reminder',
                'description' => __('Reminder to volunteers one day before the event.', 'tta'),
                'email_subject' => __('You are volunteering tomorrow', 'tta'),
                'email_body'  => __('This is a reminder that you volunteered for an event in 24 hours. Details are below.', 'tta'),
                'sms_text'    => '',
            ],
            'volunteer_reminder_2hr' => [
                'label'       => __('Volunteer Reminder 2hr', 'tta'),
                'type'        => 'Internal',
                'category'    => 'Volunteer Reminder',
                'description' => __('Reminder to volunteers two hours before the event.', 'tta'),
                'email_subject' => __('Volunteer duty soon', 'tta'),
                'email_body'  => __('Your volunteer shift begins in two hours. Event details are below.', 'tta'),
                'sms_text'    => '',
            ],
            'post_event_review' => [
                'label'       => __( 'Post-Event Thank You', 'tta' ),
                'type'        => 'External',
                'category'    => 'Post Event',
                'description' => __( 'Sent to attendees who checked in after the event asking for a Google review.', 'tta' ),
                'email_subject' => __( 'Thanks for attending!', 'tta' ),
                'email_body'  => __( 'We hope you enjoyed the event. Please consider leaving a review: https://g.page/r/tryingtoadultrva/review', 'tta' ),
                'sms_text'    => '',
            ],
            'assistance_request' => [
                'label'       => __('Assistance Request', 'tta'),
                'type'        => 'Internal',
                'category'    => 'Event Coordination',
                'description' => __('Notification sent when a member asks for help finding the group.', 'tta'),
                'email_subject' => __('Member needs assistance', 'tta'),
                'email_body'  => __('A member has requested help finding the event group. Their note is below.', 'tta'),
                'sms_text'    => '',
            ],
        ];
    }

    protected function get_templates(){
        $defaults = self::get_default_templates();
        $saved    = tta_unslash( get_option( 'tta_comms_templates', [] ) );
        if ( ! is_array( $saved ) ) {
            return $defaults;
        }

        if ( isset( $saved['refund_requested'] ) ) {
            $needs_update = false;
            if ( 'External' !== ( $saved['refund_requested']['type'] ?? '' ) ) {
                $saved['refund_requested']['type'] = 'External';
                $needs_update = true;
            }
            if ( 'Refund' !== ( $saved['refund_requested']['category'] ?? '' ) ) {
                $saved['refund_requested']['category'] = 'Refund';
                $needs_update = true;
            }
            if ( $needs_update ) {
                update_option( 'tta_comms_templates', $saved, false );
            }
        }

        foreach ( $saved as $key => $vals ) {
            if ( isset( $defaults[ $key ] ) && is_array( $vals ) ) {
                $defaults[ $key ] = array_merge( $defaults[ $key ], $vals );
            }
        }
        return $defaults;
    }

    public function render_page(){
        $tabs = [
            'templates' => __( 'Email Templates', 'tta' ),
            'logs'      => __( 'Email Logs', 'tta' ),
            'history'   => __( 'Email History', 'tta' ),
        ];
        $current = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs ) ? $_GET['tab'] : 'templates';

        echo '<h1>TTA Email & SMS</h1><h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $slug => $label ) {
            $class = $current === $slug ? ' nav-tab-active' : '';
            $url   = esc_url( add_query_arg( [ 'page' => 'tta-comms', 'tab' => $slug ], admin_url( 'admin.php' ) ) );
            printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $class, esc_html( $label ) );
        }
        echo '</h2><div class="wrap">';

        if ( 'logs' === $current ) {
            $this->render_logs_tab();
        } elseif ( 'history' === $current ) {
            $this->render_history_tab();
        } else {
            $this->render_templates_tab();
        }

        echo '</div>';
    }

    protected function render_templates_tab(){
        $templates = $this->get_templates();

        if ( isset( $_POST['template_key'] ) && isset( $_POST['tta_comms_save_nonce'] ) && check_admin_referer( 'tta_comms_save_action', 'tta_comms_save_nonce' ) ) {
            $key       = sanitize_key( $_POST['template_key'] );
            $templates[ $key ]['email_subject'] = tta_sanitize_text_field( $_POST['email_subject'] ?? $templates[ $key ]['email_subject'] );
            $templates[ $key ]['email_body']    = tta_sanitize_textarea_field( $_POST['email_body'] ?? $templates[ $key ]['email_body'] );
            $templates[ $key ]['sms_text']      = tta_sanitize_textarea_field( $_POST['sms_text'] ?? $templates[ $key ]['sms_text'] );
            update_option( 'tta_comms_templates', $templates, false );
            echo '<div class="updated"><p>'.esc_html__( 'Template saved.', 'tta' ).'</p></div>';
        }

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>'.esc_html__( 'Communication Name', 'tta' ).'</th>';
        echo '<th>'.esc_html__( 'Communication Type', 'tta' ).'</th>';
        echo '<th>'.esc_html__( 'Communication Category', 'tta' ).'</th>';
        echo '<th>'.esc_html__( 'Actions', 'tta' ).'</th>';
        echo '<th></th></tr></thead><tbody>';

        foreach ( $templates as $key => $vals ) {
            echo '<tr data-comms-key="'.esc_attr( $key ).'">';
            echo '<td>'.esc_html( $vals['label'] ).'</td>';
            echo '<td>'.esc_html( $vals['type'] ).'</td>';
            echo '<td>'.esc_html( $vals['category'] ).'</td>';
            echo '<td><a href="#" class="tta-edit-link">'.esc_html__( 'Edit', 'tta' ).'</a></td>';
            echo '<td class="tta-toggle-cell"><img src="'.esc_url( TTA_PLUGIN_URL.'assets/images/admin/arrow.svg' ).'" class="tta-toggle-arrow" width="10" height="10" alt="Toggle"></td>';
            echo '</tr>';

            echo '<tr class="tta-inline-row" style="display:none;">';
            echo '<td colspan="5"><div class="tta-inline-container" style="display:none;">';
            echo '<form method="post" class="tta-comms-form">';
            wp_nonce_field( 'tta_comms_save_action', 'tta_comms_save_nonce' );
            echo '<input type="hidden" name="template_key" value="'.esc_attr( $key ).'">';
            if ( ! empty( $vals['description'] ) ) {
                echo '<p class="description">' . esc_html( $vals['description'] ) . '</p>';
            }
            echo '<table class="form-table">';
            echo '<tr><th scope="row">' . esc_html__( 'Email Subject', 'tta' ) . '</th><td><input type="text" name="email_subject" value="' . esc_attr( $vals['email_subject'] ) . '" class="regular-text tta-comm-input"></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'Email Body', 'tta' ) . '</th><td><textarea name="email_body" rows="4" class="large-text tta-comm-input">' . esc_textarea( $vals['email_body'] ) . '</textarea></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'SMS Text', 'tta' ) . '</th><td><textarea name="sms_text" rows="2" class="large-text tta-comm-input">' . esc_textarea( $vals['sms_text'] ) . '</textarea><br><span class="tta-sms-count">0</span>/160</td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'Insert Token', 'tta' ) . '</th><td>';
            echo '<div class="tta-token-section"><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Details about the event.', 'tta' ) . '"><img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ) . '" alt="?"></span><strong>' . esc_html__( 'Event Information', 'tta' ) . '</strong> ';
            echo '<span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Details about the event.', 'tta' ) . '"></span><br>';
            echo '<button type="button" class="button tta-insert-token" data-token="{event_name}">{event_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{event_address}">{event_address}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{event_address_link}">{event_address_link}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{event_link}">{event_link}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{event_date}">{event_date}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{event_time}">{event_time}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{event_type}">{event_type}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{venue_name}">{venue_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{venue_url}">{venue_url}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{base_cost}">{base_cost}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{member_cost}">{member_cost}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{premium_cost}">{premium_cost}</button></div>';

            echo '<div class="tta-token-section"><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Details from the purchasing member profile.', 'tta' ) . '"><img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ) . '" alt="?"></span><strong>' . esc_html__( 'Member Information', 'tta' ) . '</strong><br>';
            echo '<button type="button" class="button tta-insert-token" data-token="{first_name}">{first_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{last_name}">{last_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{email}">{email}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{phone}">{phone}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{membership_level}">{membership_level}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{member_type}">{member_type}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{dashboard_profile_url}">{dashboard_profile_url}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{dashboard_upcoming_url}">{dashboard_upcoming_url}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{dashboard_waitlist_url}">{dashboard_waitlist_url}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{dashboard_past_url}">{dashboard_past_url}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{dashboard_billing_url}">{dashboard_billing_url}</button></div>';
            echo '<div class="tta-token-section"><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Re-entry links for banned members.', 'tta' ) . '"><img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ) . '" alt="?"></span><strong>' . esc_html__( 'Ban & Re-Entry', 'tta' ) . '</strong><br>';
            echo '<button type="button" class="button tta-insert-token" data-token="{reentry_link}">{reentry_link}</button></div>';

            echo '<div class="tta-token-section"><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Per-ticket attendee details.', 'tta' ) . '"><img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ) . '" alt="?"></span><strong>' . esc_html__( 'Event Attendee Information', 'tta' ) . '</strong><br>';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee_first_name}">{attendee_first_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee_last_name}">{attendee_last_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee_email}">{attendee_email}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee_phone}">{attendee_phone}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee2_first_name}">{attendee2_first_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee2_last_name}">{attendee2_last_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee2_email}">{attendee2_email}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee2_phone}">{attendee2_phone}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee3_first_name}">{attendee3_first_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee3_last_name}">{attendee3_last_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee3_email}">{attendee3_email}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee3_phone}">{attendee3_phone}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee4_first_name}">{attendee4_first_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee4_last_name}">{attendee4_last_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee4_email}">{attendee4_email}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee4_phone}">{attendee4_phone}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{assistance_message}">{assistance_message}</button></div>';

            echo '<div class="tta-token-section"><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Event hosts and volunteers.', 'tta' ) . '"><img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ) . '" alt="?"></span><strong>' . esc_html__( 'Event Contacts', 'tta' ) . '</strong><br>';
            echo '<button type="button" class="button tta-insert-token" data-token="{event_host}">{event_host}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{event_volunteer}">{event_volunteer}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{host_notes}">{host_notes}</button></div>';

            echo '<div class="tta-token-section"><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Details about the refunded ticket.', 'tta' ) . '"><img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ) . '" alt="?"></span><strong>' . esc_html__( 'Refund Information', 'tta' ) . '</strong><br>';
            echo '<button type="button" class="button tta-insert-token" data-token="{refund_first_name}">{refund_first_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{refund_last_name}">{refund_last_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{refund_email}">{refund_email}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{refund_amount}">{refund_amount}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{refund_ticket}">{refund_ticket}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{refund_event_name}">{refund_event_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{refund_event_date}">{refund_event_date}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{refund_event_time}">{refund_event_time}</button></div>';

            echo '<div class="tta-token-section"><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Details from an assistance request.', 'tta' ) . '"><img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ) . '" alt="?"></span><strong>' . esc_html__( 'Assistance Message', 'tta' ) . '</strong><br>';
            echo '<button type="button" class="button tta-insert-token" data-token="{assistance_message}">{assistance_message}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{assistance_first_name}">{assistance_first_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{assistance_last_name}">{assistance_last_name}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{assistance_email}">{assistance_email}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{assistance_phone}">{assistance_phone}</button></div>';

            echo '<div class="tta-token-section"><strong>' . esc_html__( 'Formatting & Styling', 'tta' ) . '</strong><br>';
            echo '<button type="button" class="button tta-link-text">' . esc_html__( 'Link This Text', 'tta' ) . '</button> ';
            echo '<button type="button" class="button tta-insert-br">' . esc_html__( 'Line Break', 'tta' ) . '</button> ';
            echo '<button type="button" class="button tta-bold-text">' . esc_html__( 'Bold', 'tta' ) . '</button> ';
            echo '<button type="button" class="button tta-italic-text">' . esc_html__( 'Italic', 'tta' ) . '</button></div>';

            echo '</td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'Email Preview', 'tta' ) . '</th><td><div class="tta-email-preview"><strong class="tta-email-preview-subject"></strong><p class="tta-email-preview-body"></p></div></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'SMS Preview', 'tta' ) . '</th><td><div class="tta-sms-preview"></div></td></tr>';
            echo '</table>';
            echo '<p class="submit">';
            echo '<button type="submit" class="button button-primary">'.esc_html__( 'Save Changes', 'tta' ).'</button>';
            echo '<div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="'.esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ).'" alt="Loading" /></div>';
            echo '<div class="tta-admin-progress-response-div"><p class="tta-admin-progress-response-p"></p></div>';
            echo '</p>';
            echo '</form></div></td></tr>';
        }

        echo '</tbody></table>';
    }

    /** Render scheduled email jobs. */
    protected function render_logs_tab() {
        $scheduled = TTA_Email_Reminders::get_scheduled_emails();
        $per_page  = 20;
        $paged     = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $event_ids = array_keys( $scheduled );
        $total     = count( $event_ids );
        $slice     = array_slice( $event_ids, ( $paged - 1 ) * $per_page, $per_page );

        echo '<div id="tta-email-logs">';
        if ( empty( $slice ) ) {
            echo '<p>' . esc_html__( 'No scheduled emails.', 'tta' ) . '</p>';
        } else {
            echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Event', 'tta' ) . '</th><th></th></tr></thead><tbody>';
            foreach ( $slice as $event_id ) {
                $info = $scheduled[ $event_id ];
                echo '<tr class="tta-email-log-event" data-event="' . esc_attr( $event_id ) . '">';
                echo '<td>' . esc_html( $info['name'] ) . '</td>';
                echo '<td class="tta-toggle-cell"><img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/arrow.svg' ) . '" class="tta-toggle-arrow" width="10" height="10" alt="Toggle"></td>';
                echo '</tr>';
                echo '<tr class="tta-email-log-details tta-inline-row" style="display:none;">';
                echo '<td colspan="2"><div class="tta-inline-container">';
                echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Type', 'tta' ) . '</th><th>' . esc_html__( 'Scheduled Time', 'tta' ) . '</th><th>' . esc_html__( 'Time Until', 'tta' ) . '</th><th>' . esc_html__( 'Actions', 'tta' ) . '</th></tr></thead><tbody>';
                $tz  = wp_timezone();
                $now = TTA_Email_Reminders::current_time();
                foreach ( $info['jobs'] as $job ) {
                    $send_ts = $job['timestamp'];
                    $time    = wp_date( 'm-d-Y g:iA', $send_ts, $tz );
                    $diff    = max( 0, $send_ts - $now );
                    $hours   = floor( $diff / HOUR_IN_SECONDS );
                    $minutes = floor( ( $diff % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
                    $seconds = $diff % MINUTE_IN_SECONDS;
                    $remain  = sprintf( '%02d H, %02d M, %02d S', $hours, $minutes, $seconds );
                    echo '<tr>';
                    echo '<td>' . esc_html( $job['label'] ) . '</td>';
                    echo '<td>' . esc_html( $time ) . '</td>';
                    echo '<td class="tta-countdown" data-remaining="' . esc_attr( $diff ) . '">' . esc_html( $remain ) . '</td>';
                    echo '<td>';
                    echo '<button class="button tta-email-log-list" data-event="' . esc_attr( $event_id ) . '" data-hook="' . esc_attr( $job['hook'] ) . '" data-template="' . esc_attr( $job['template'] ) . '">' . esc_html__( 'See Email List', 'tta' ) . '</button> ';
                    echo '<button class="button tta-email-log-delete" data-event="' . esc_attr( $event_id ) . '" data-hook="' . esc_attr( $job['hook'] ) . '" data-template="' . esc_attr( $job['template'] ) . '">' . esc_html__( 'Delete', 'tta' ) . '</button>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table></div></td></tr>';
            }
            echo '</tbody></table>';

            $base = add_query_arg( [ 'page' => 'tta-comms', 'tab' => 'logs', 'paged' => '%#%' ], admin_url( 'admin.php' ) );
            echo '<div class="tablenav"><div class="tablenav-pages">';
            echo paginate_links( [
                'base'      => $base,
                'format'    => '',
                'current'   => $paged,
                'total'     => ceil( $total / $per_page ),
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'end_size'  => 1,
                'mid_size'  => 2,
            ] );
            echo '</div></div>';
        }
        echo '</div>';
    }

    /** Render email history log. */
    protected function render_history_tab() {
        $log      = TTA_Email_Reminders::get_email_log();
        $per_page = 20;
        $paged    = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $total    = count( $log );
        $slice    = array_slice( array_reverse( $log ), ( $paged - 1 ) * $per_page, $per_page );

        echo '<div id="tta-email-history">';
        echo '<p><button id="tta-email-clear-log" class="button">' . esc_html__( 'Clear Log', 'tta' ) . '</button></p>';
        if ( empty( $slice ) ) {
            echo '<p>' . esc_html__( 'No emails logged.', 'tta' ) . '</p>';
        } else {
            echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Time', 'tta' ) . '</th><th>' . esc_html__( 'Event ID', 'tta' ) . '</th><th>' . esc_html__( 'Template', 'tta' ) . '</th><th>' . esc_html__( 'Recipient', 'tta' ) . '</th><th>' . esc_html__( 'Status', 'tta' ) . '</th></tr></thead><tbody>';
            foreach ( $slice as $entry ) {
                $time = wp_date( 'Y-m-d H:i', $entry['time'], wp_timezone() );
                echo '<tr>'; // escape fields
                echo '<td>' . esc_html( $time ) . '</td>';
                echo '<td>' . esc_html( $entry['event_id'] ) . '</td>';
                echo '<td>' . esc_html( $entry['template'] ) . '</td>';
                echo '<td>' . esc_html( $entry['recipient'] ) . '</td>';
                echo '<td>' . esc_html( $entry['status'] ) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';

            $base = add_query_arg( [ 'page' => 'tta-comms', 'tab' => 'history', 'paged' => '%#%' ], admin_url( 'admin.php' ) );
            echo '<div class="tablenav"><div class="tablenav-pages">';
            echo paginate_links( [
                'base'      => $base,
                'format'    => '',
                'current'   => $paged,
                'total'     => ceil( $total / $per_page ),
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'end_size'  => 1,
                'mid_size'  => 2,
            ] );
            echo '</div></div>';
        }
        echo '</div>';
    }
}

