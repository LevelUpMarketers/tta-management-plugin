<?php
if (!defined('ABSPATH')) { exit; }

class TTA_Comms_Admin {
    public static function get_instance(){ static $inst; return $inst ?: $inst = new self(); }
    private function __construct(){ add_action('admin_menu', [ $this, 'register_menu' ]); }

    public function register_menu(){
        add_menu_page(
            'Email & SMS',
            'Email & SMS',
            'manage_options',
            'tta-comms',
            [ $this, 'render_page' ],
            'dashicons-email-alt',
            9
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
            'new_event' => [
                'label'       => __('New Event Created', 'tta'),
                'type'        => 'Internal',
                'category'    => 'Admin Notice',
                'description' => __('Notifies administrators when a new event is created.', 'tta'),
                'email_subject' => __('New event created', 'tta'),
                'email_body'  => __('A new event has been added to the calendar. Details are below.', 'tta'),
                'sms_text'    => '',
            ],
            'refund_requested' => [
                'label'       => __('Refund Requested', 'tta'),
                'type'        => 'Internal',
                'category'    => 'Admin Notice',
                'description' => __('Alert when a member requests a refund.', 'tta'),
                'email_subject' => __('Refund request received', 'tta'),
                'email_body'  => __('A member has requested a refund for the event below.', 'tta'),
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
        ];
    }

    protected function get_templates(){
        $defaults = self::get_default_templates();
        $saved    = tta_unslash( get_option( 'tta_comms_templates', [] ) );
        if ( ! is_array( $saved ) ) {
            return $defaults;
        }

        foreach ( $saved as $key => $vals ) {
            if ( isset( $defaults[ $key ] ) && is_array( $vals ) ) {
                $defaults[ $key ] = array_merge( $defaults[ $key ], $vals );
            }
        }
        return $defaults;
    }

    public function render_page(){
        $templates = $this->get_templates();

        if ( isset( $_POST['template_key'] ) && isset( $_POST['tta_comms_save_nonce'] ) && check_admin_referer( 'tta_comms_save_action', 'tta_comms_save_nonce' ) ) {
            $key       = sanitize_key( $_POST['template_key'] );
            $templates[ $key ]['email_subject'] = tta_sanitize_text_field( $_POST['email_subject'] ?? $templates[ $key ]['email_subject'] );
            $templates[ $key ]['email_body']    = tta_sanitize_textarea_field( $_POST['email_body'] ?? $templates[ $key ]['email_body'] );
            $templates[ $key ]['sms_text']      = tta_sanitize_textarea_field( $_POST['sms_text'] ?? $templates[ $key ]['sms_text'] );
            update_option( 'tta_comms_templates', $templates, false );
            echo '<div class="updated"><p>'.esc_html__( 'Template saved.', 'tta' ).'</p></div>';
        }

        echo '<div class="wrap"><h1>'.esc_html__( 'Email & SMS', 'tta' ).'</h1>';
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
            echo '<td class="tta-toggle-cell"><img src="'.esc_url( TTA_PLUGIN_URL.'assets/images/admin/arrow.svg' ).'" class="tta-toggle-arrow" width="16" height="16" alt="Toggle"></td>';
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
            echo '<button type="button" class="button tta-insert-token" data-token="{event_link}">{event_link}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{dashboard_profile_url}">{dashboard_profile_url}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{dashboard_upcoming_url}">{dashboard_upcoming_url}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{dashboard_past_url}">{dashboard_past_url}</button> ';
            echo '<button type="button" class="button tta-insert-token" data-token="{dashboard_billing_url}">{dashboard_billing_url}</button> ';
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
            echo '<button type="button" class="button tta-insert-token" data-token="{member_type}">{member_type}</button></div>';

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
            echo '<button type="button" class="button tta-insert-token" data-token="{attendee4_phone}">{attendee4_phone}</button></div>';

            echo '<button type="button" class="button tta-insert-br">Line Break</button>';
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

        echo '</tbody></table></div>';
    }
}

