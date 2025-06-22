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
                'label' => __('Successful Event Purchase', 'tta'),
                'email_subject' => __('Thanks for Registering!', 'tta'),
                'email_body' => __("You're in! Thank for registering for our upcoming Trying To Adult event. The details of the event are below. Please keep this email, as you'll need to present this to the Event Host or Volunteer when arriving at your event.", 'tta'),
                'sms_text' => __('Thanks for registering! View your upcoming events at ', 'tta'),
            ],
            'reminder_24hr' => [
                'label' => __('24-Hour Event Reminder', 'tta'),
                'email_subject' => __('Your event is tomorrow!', 'tta'),
                'email_body' => __('Heads-up! Your event is just 1 day away! Below are the details.', 'tta'),
                'sms_text' => __('Heads-Up! your event is tomorrow! View your upcoming events at ', 'tta'),
            ],
            'reminder_2hr' => [
                'label' => __('2-Hour Event Reminder', 'tta'),
                'email_subject' => __('Event starting soon!', 'tta'),
                'email_body' => __('Your event is only 2 hours away! Below are the details.', 'tta'),
                'sms_text' => __('Only 2 hours to go! View your upcoming events at ', 'tta'),
            ],
        ];
    }

    protected function get_templates(){
        $defaults = self::get_default_templates();
        $saved = get_option('tta_comms_templates', []);
        return array_merge($defaults, is_array($saved) ? $saved : []);
    }

    public function render_page(){
        $templates = $this->get_templates();
        if (isset($_POST['tta_save_comms']) && check_admin_referer('tta_save_comms','tta_comms_nonce')) {
            foreach ($templates as $key => $vals) {
                $templates[$key]['email_subject'] = sanitize_text_field($_POST[$key.'_email_subject'] ?? $vals['email_subject']);
                $templates[$key]['email_body']    = sanitize_textarea_field($_POST[$key.'_email_body'] ?? $vals['email_body']);
                $templates[$key]['sms_text']      = sanitize_textarea_field($_POST[$key.'_sms_text'] ?? $vals['sms_text']);
            }
            update_option('tta_comms_templates', $templates, false);
            echo '<div class="updated"><p>'.esc_html__('Settings saved.', 'tta').'</p></div>';
        }
        echo '<div class="wrap"><h1>'.esc_html__('Email & SMS', 'tta').'</h1>';
        echo '<form method="post">';
        wp_nonce_field('tta_save_comms','tta_comms_nonce');
        foreach ( $templates as $key => $vals ) {
            echo '<div class="tta-admin-accordion">';
            echo '<div class="tta-accordion">';
            echo '<button type="button" class="button tta-accordion-toggle" data-open-text="' . esc_attr__( 'Edit', 'tta' ) . '" data-close-text="' . esc_attr__( 'Hide', 'tta' ) . '">' . esc_html( $vals['label'] ) . '</button>';
            echo '<div class="tta-accordion-content">';
            echo '<table class="form-table">';
            echo '<tr><th scope="row">' . esc_html__( 'Email Subject', 'tta' ) . '</th><td><input type="text" name="' . $key . '_email_subject" value="' . esc_attr( $vals['email_subject'] ) . '" class="regular-text"></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'Email Body', 'tta' ) . '</th><td><textarea name="' . $key . '_email_body" rows="4" class="large-text">' . esc_textarea( $vals['email_body'] ) . '</textarea></td></tr>';
            echo '<tr><th scope="row">' . esc_html__( 'SMS Text', 'tta' ) . '</th><td><textarea name="' . $key . '_sms_text" rows="2" class="large-text">' . esc_textarea( $vals['sms_text'] ) . '</textarea></td></tr>';
            echo '</table>';
            echo '</div>'; // content
            echo '</div>'; // accordion
            echo '</div>'; // wrapper
        }
        echo '<p><input type="submit" name="tta_save_comms" class="button button-primary" value="'.esc_attr__('Save Changes', 'tta').'"></p>';
        echo '</form></div>';
    }
}

