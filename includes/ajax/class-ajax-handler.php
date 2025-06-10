<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Handler {

    /**
     * Singleton instance
     *
     * @return TTA_Ajax_Handler
     */
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    /**
     * Constructor: hook our AJAX actions
     */
    private function __construct() {
        // generic placeholder AJAX
        add_action( 'wp_ajax_tta_action',        [ $this, 'handle_ajax' ] );
        add_action( 'wp_ajax_nopriv_tta_action', [ $this, 'handle_ajax' ] );

        // Create‐only event save
        add_action( 'wp_ajax_tta_save_event',     [ $this, 'save_event' ] );

        // Update existing event
        add_action( 'wp_ajax_tta_update_event',   [ $this, 'update_event' ] );

        // Fetch raw event data (for inline JSON)
        add_action( 'wp_ajax_tta_get_event',      [ $this, 'get_event' ] );

        // Fetch full create/edit form HTML
        add_action( 'wp_ajax_tta_get_event_form', [ $this, 'get_event_form' ] );

        // Create new member
        add_action( 'wp_ajax_tta_save_member',    [ $this, 'save_member' ] );

        add_action( 'wp_ajax_tta_get_member',      [ $this, 'get_member' ] );
        add_action( 'wp_ajax_tta_get_member_form', [ $this, 'get_member_form' ] );
        add_action('wp_ajax_tta_update_member', [ $this, 'update_member' ]);

        // Frontend “update my profile” action (logged-in users only)
        add_action( 'wp_ajax_tta_front_update_member', [ $this, 'update_member_front' ] );

        add_action( 'wp_ajax_tta_get_ticket_form', [ $this, 'get_ticket_form' ] );

        // right after your existing add_action for tta_get_ticket_form:
        add_action( 'wp_ajax_tta_update_ticket', [ $this, 'update_ticket' ] );
                


    }

    /**
     * Placeholder for existing AJAX
     */
    public function handle_ajax() {
        wp_send_json_success();
    }

    /**
     * Handle AJAX request to create a new Event (insert only),
     * plus associated ticket & waitlist records, and auto-create a Page.
     */
    public function save_event() {
        // 0) Verify nonce
        check_ajax_referer( 'tta_event_save_action', 'tta_event_save_nonce' );

        global $wpdb;
        $events_table   = $wpdb->prefix . 'tta_events';
        $tickets_table  = $wpdb->prefix . 'tta_tickets';
        $waitlist_table = $wpdb->prefix . 'tta_waitlist';

        // 1) Gather & sanitize the incoming event data
        $ute_id             = uniqid( 'tte_', true );
        // Combine address parts (preserve blank segments)
        $address_parts = [
            sanitize_text_field( $_POST['street_address'] ?? '' ),
            sanitize_text_field( $_POST['address_2']      ?? '' ),
            sanitize_text_field( $_POST['city']           ?? '' ),
            sanitize_text_field( $_POST['state']          ?? '' ),
            sanitize_text_field( $_POST['zip']            ?? '' ),
        ];
        $address = implode( ' - ', $address_parts );
        $start              = sanitize_text_field( $_POST['start_time'] ?? '' );
        $end                = sanitize_text_field( $_POST['end_time']   ?? '' );
        $time               = $start . '|' . $end;
        $waitlist_available = sanitize_text_field( $_POST['waitlistavailable'] ?? '0' );

        $event_data = [
            'ute_id'               => $ute_id,
            'name'                 => sanitize_text_field( $_POST['name']                 ?? '' ),
            'date'                 => sanitize_text_field( $_POST['date']                 ?? '' ),
            'all_day_event'        => sanitize_text_field( $_POST['all_day_event']        ?? '0' ),
            'time'                 => $time,
            'virtual_event'        => sanitize_text_field( $_POST['virtual_event']        ?? '0' ),
            'address'              => $address,
            'venuename'            => sanitize_text_field( $_POST['venuename']            ?? '' ),
            'venueurl'             => esc_url_raw   ( $_POST['venueurl']            ?? '' ),
            'type'                 => sanitize_text_field( $_POST['type']                 ?? '' ),
            'baseeventcost'        => floatval        ( $_POST['baseeventcost']        ?? 0 ),
            'discountedmembercost' => floatval        ( $_POST['discountedmembercost'] ?? 0 ),
            'premiummembercost' => floatval        ( $_POST['premiummembercost'] ?? 0 ),
            'waitlistavailable'    => $waitlist_available,
            'refundsavailable'     => sanitize_text_field( $_POST['refundsavailable']    ?? '0' ),
            'discountcode'         => sanitize_text_field( $_POST['discountcode']        ?? '' ),
            'url2'                 => esc_url_raw   ( $_POST['url2']                ?? '' ),
            'url3'                 => esc_url_raw   ( $_POST['url3']                ?? '' ),
            'url4'                 => esc_url_raw   ( $_POST['url4']                ?? '' ),
            'mainimageid'          => intval          ( $_POST['mainimageid']         ?? 0 ),
            'otherimageids'        => sanitize_text_field( $_POST['otherimageids']       ?? '' ),
        ];

        // 2) Insert the event record
        $wpdb->insert( $events_table, $event_data );
        $event_id = $wpdb->insert_id;
        if ( ! $event_id ) {
            wp_send_json_error( [ 'message' => 'Failed to create event.' ] );
        }

        // 3) Always create a ticket record (with a placeholder waitlist_id)
        $ticket_data = [
            'event_ute_id'         => $ute_id,
            'event_name'           => $event_data['name'],
            'ticket_name'          => 'General Admission',
            'waitlist_id'          => 0,
            'baseeventcost'        => $event_data['baseeventcost'],
            'discountedmembercost' => $event_data['discountedmembercost'],
            'premiummembercost' => $event_data['premiummembercost'],
        ];
        $wpdb->insert( $tickets_table, $ticket_data );
        $ticket_id = $wpdb->insert_id;
        if ( ! $ticket_id ) {
            wp_send_json_error( [ 'message' => 'Failed to create ticket.' ] );
        }

        // 4) If waitlists are enabled, create one now
        $waitlist_id = 0;
        if ( '1' === $waitlist_available ) {
            $waitlist_data = [
                'event_ute_id' => $ute_id,
                'ticket_id'    => $ticket_id,
                'event_name'   => $event_data['name'],
                'userids'      => '',  // start empty
            ];
            $wpdb->insert( $waitlist_table, $waitlist_data );
            $waitlist_id = $wpdb->insert_id;
            if ( ! $waitlist_id ) {
                wp_send_json_error( [ 'message' => 'Failed to create waitlist.' ] );
            }

            // 5) Now update the ticket record with its waitlist_id
            $wpdb->update(
                $tickets_table,
                [ 'waitlist_id' => $waitlist_id ],
                [ 'id'           => $ticket_id ]
            );
        }

        // 6) Finally update the event with its ticket & waitlist IDs
        $wpdb->update(
            $events_table,
            [
                'ticket_id'   => $ticket_id,
                'waitlist_id' => $waitlist_id,
                // (We’ll update 'page_id' in step 7 below.)
            ],
            [ 'id' => $event_id ]
        );

        // 7) Auto-create a dedicated WordPress Page for this Event
        $page_data = [
            'post_title'   => $event_data['name'],
            'post_content' => '',          // description will be set below
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'meta_input'   => [
                '_tta_event_id' => $event_id // back-reference for our page manager if needed
            ],
        ];
        $page_id = wp_insert_post( $page_data );

        if ( $page_id && ! is_wp_error( $page_id ) ) {
            // 7a) Assign our custom template
            update_post_meta( $page_id, '_wp_page_template', 'event-page-template.php' );

            // 7b) Store the page_id back on the event record
            $wpdb->update(
                $events_table,
                [ 'page_id' => $page_id ],
                [ 'id'      => $event_id ]
            );
        }

        // 8) Save the TinyMCE “description” (if provided) into that page’s post_content:
        if ( isset( $_POST['description'] ) && $page_id ) {
            $content = wp_kses_post( $_POST['description'] );
            wp_update_post( [
                'ID'           => $page_id,
                'post_content' => $content,
            ] );
        }

        // 9) Return success, including the front-end URL
        $page_url = $page_id
            ? get_permalink( $page_id )
            : '';

        wp_send_json_success( [
            'message'  => 'Your Event was created successfully! Additionally, an <a href="' . esc_url( $page_url ) . '" target="_blank">individual event page</a>, waitlist, and ticket database entries were successfully created as well.<br/><br/>Have a GREAT day, Trying to Adult administrator!',
            'id'       => $event_id,
            'ticket'   => $ticket_id,
            'waitlist' => $waitlist_id,
            'page_id'  => $page_id ?? 0,
            'page_url' => $page_url,
        ] );
    }

    /**
     * Update an existing event, plus its ticket & waitlist records,
     * and also save any edited description back to the page’s post_content.
     */
    public function update_event() {
        check_ajax_referer( 'tta_event_save_action', 'tta_event_save_nonce' );

        if ( empty( $_POST['tta_event_id'] ) ) {
            wp_send_json_error([ 'message' => 'Missing event ID.' ]);
        }

        global $wpdb;
        $events_table   = $wpdb->prefix . 'tta_events';
        $tickets_table  = $wpdb->prefix . 'tta_tickets';
        $waitlist_table = $wpdb->prefix . 'tta_waitlist';

        $id = intval( $_POST['tta_event_id'] );

         $address_parts = [
            sanitize_text_field( $_POST['street_address'] ?? '' ),
            sanitize_text_field( $_POST['address_2']      ?? '' ),
            sanitize_text_field( $_POST['city']           ?? '' ),
            sanitize_text_field( $_POST['state']          ?? '' ),
            sanitize_text_field( $_POST['zip']            ?? '' ),
        ];
        $address = implode( ' - ', $address_parts );
        $start = sanitize_text_field( $_POST['start_time'] ?? '' );
        $end   = sanitize_text_field( $_POST['end_time']   ?? '' );
        $time  = $start . '|' . $end;

        // 2) Build event data
        $event_data = [
            'name'                 => sanitize_text_field( $_POST['name']                 ?? '' ),
            'date'                 => sanitize_text_field( $_POST['date']                 ?? '' ),
            'all_day_event'        => sanitize_text_field( $_POST['all_day_event']        ?? '0' ),
            'time'                 => $time,
            'virtual_event'        => sanitize_text_field( $_POST['virtual_event']        ?? '0' ),
            'address'              => $address,
            'venuename'            => sanitize_text_field( $_POST['venuename']            ?? '' ),
            'venueurl'             => esc_url_raw   ( $_POST['venueurl']            ?? '' ),
            'type'                 => sanitize_text_field( $_POST['type']                 ?? '' ),
            'baseeventcost'        => floatval        ( $_POST['baseeventcost']        ?? 0 ),
            'discountedmembercost' => floatval        ( $_POST['discountedmembercost'] ?? 0 ),
            'premiummembercost'    => floatval        ( $_POST['premiummembercost'] ?? 0 ),
            'waitlistavailable'    => sanitize_text_field( $_POST['waitlistavailable']   ?? '0' ),
            'refundsavailable'     => sanitize_text_field( $_POST['refundsavailable']    ?? '0' ),
            'discountcode'         => sanitize_text_field( $_POST['discountcode']        ?? '' ),
            'url2'                 => esc_url_raw   ( $_POST['url2']                ?? '' ),
            'url3'                 => esc_url_raw   ( $_POST['url3']                ?? '' ),
            'url4'                 => esc_url_raw   ( $_POST['url4']                ?? '' ),
            'mainimageid'          => intval          ( $_POST['mainimageid']         ?? 0 ),
            'otherimageids'        => sanitize_text_field( $_POST['otherimageids']       ?? '' ),
        ];

        // 3) Update the event row
        $updated = $wpdb->update( $events_table, $event_data, [ 'id' => $id ] );
        if ( false === $updated ) {
            wp_send_json_error([ 'message' => 'Failed to update event.' ]);
        }

        // 4) Fetch fresh event to get ute_id, ticket_id, waitlist_id, page_id
        $event        = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$events_table} WHERE id = %d", $id ),
            ARRAY_A
        );
        $ute_id       = $event['ute_id'];
        $ticket_id    = intval( $event['ticket_id'] );
        $waitlist_id  = intval( $event['waitlist_id'] );
        $page_id      = intval( $event['page_id'] );

        // 5) Update its ticket record
        $ticket_update = [
            'event_name'           => $event_data['name'],
            'baseeventcost'        => $event_data['baseeventcost'],
            'discountedmembercost' => $event_data['discountedmembercost'],
            'premiummembercost' => $event_data['premiummembercost'],
        ];
        $wpdb->update(
            $tickets_table,
            $ticket_update,
            [ 'event_ute_id' => $ute_id ]
        );

        // 6) Handle the waitlist
        $waitlist_available = sanitize_text_field( $_POST['waitlistavailable'] ?? '0' );
        $waitlist_csv       = sanitize_text_field( $_POST['userids']           ?? '' );

        if ( '1' === $waitlist_available ) {
            if ( $waitlist_id ) {
                // just update name + CSV
                $wpdb->update(
                    $waitlist_table,
                    [
                        'event_name' => $event_data['name'],
                        'userids'    => $waitlist_csv,
                    ],
                    [ 'id' => $waitlist_id ]
                );
            } else {
                // create brand-new waitlist
                $wpdb->insert(
                    $waitlist_table,
                    [
                        'event_ute_id' => $ute_id,
                        'ticket_id'    => $ticket_id,
                        'event_name'   => $event_data['name'],
                        'userids'      => $waitlist_csv,
                    ]
                );
                $new_wl = $wpdb->insert_id;
                if ( $new_wl ) {
                    // record it back on the event
                    $wpdb->update(
                        $events_table,
                        [ 'waitlist_id' => $new_wl ],
                        [ 'id'           => $id     ]
                    );
                }
            }
        }
        // if disabling waitlist (0), we leave the old waitlist row untouched

        // 7) If the TinyMCE “description” was edited, save it into the page’s post_content:
        if ( isset( $_POST['description'] ) && $page_id ) {
            $content = wp_kses_post( $_POST['description'] );
            wp_update_post( [
                'ID'           => $page_id,
                'post_content' => $content,
            ] );
        }

        // 8) Return success, including the front-end URL
        $page_url = $page_id
            ? get_permalink( $page_id )
            : '';

        wp_send_json_success([
            'message'  => 'Your Event was edited successfully! Additionally, the <a href="' . esc_url( $page_url ) . '" target="_blank">individual event page</a> and the associated waitlist and ticket database entries were successfully updated as well. Have a GREAT day, Trying to Adult administrator!',
            'id'       => $id,
            'ticket'   => $ticket_id,
            'waitlist' => $waitlist_id,
            'page_id'  => $page_id ?? 0,
            'page_url' => $page_url,
        ]);
    }

    /**
     * Fetch one event by ID and return raw data as JSON.
     */
    public function get_event() {
        check_ajax_referer( 'tta_event_get_action', 'get_event_nonce' );

        if ( empty( $_POST['event_id'] ) ) {
            wp_send_json_error([ 'message' => 'Missing event ID.' ]);
        }

        global $wpdb;
        $id    = intval( $_POST['event_id'] );
        $table = $wpdb->prefix . 'tta_events';
        $event = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
            ARRAY_A
        );

        if ( ! $event ) {
            wp_send_json_error([ 'message' => 'Event not found.' ]);
        }
        wp_send_json_success([ 'event' => $event ]);
    }

    /**
     * Fetch fully rendered create/edit form HTML for a given event.
     *
     * → IMPORTANT: we force-set $_GET['event_id'] = $id so that events-edit.php
     *   can pick up the existing data (instead of relying only on $_GET).
     */
    public function get_event_form() {
        check_ajax_referer( 'tta_event_get_action', 'get_event_nonce' );

        if ( empty( $_POST['event_id'] ) ) {
            wp_send_json_error([ 'message' => 'Missing event ID.' ]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'tta_events';
        $id    = intval( $_POST['event_id'] );

        $event = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
            ARRAY_A
        );

        if ( ! $event ) {
            wp_send_json_error([ 'message' => 'Event not found.' ]);
        }

        // Make sure media uploader is available
        wp_enqueue_media();

        // *** IMPORTANT: ensure that all editor assets (TinyMCE, Quicktags, etc.) are loaded ***
        wp_enqueue_editor();

        // Force-inject $_GET['event_id'] so that events-edit.php sees it
        $_GET['event_id'] = $id;

        // Now capture the HTML for your inline form (which calls wp_editor())
        ob_start();
        include plugin_dir_path( __FILE__ ) . '../admin/views/events-edit.php';
        $html = ob_get_clean();

        wp_send_json_success([ 'html' => $html ]);
    }

    /**
     * Return the “edit ticket” form via AJAX
     */
    public function get_ticket_form() {
        check_ajax_referer( 'tta_ticket_get_action', 'get_ticket_nonce' );

        $ute = sanitize_text_field( $_POST['event_ute_id'] ?? '' );
        if ( ! $ute ) {
            wp_send_json_error([ 'message' => 'Missing event ID.' ]);
        }

        // Make $ticket stub so tickets-edit.php loops properly
        $GLOBALS['ticket'] = ['event_ute_id' => $ute];

        // Capture the tickets-edit.php output
        ob_start();
        include plugin_dir_path(__FILE__) . '../admin/views/tickets-edit.php';
        $html = ob_get_clean();

        wp_send_json_success([ 'html' => $html ]);
    }


    /**
     * Handle AJAX request to create a new Member (insert only),
     * plus associated WordPress user & profile‐image logic.
     */
     public function save_member() {
        // 0) Verify nonce
        check_ajax_referer( 'tta_member_save_action', 'tta_member_save_nonce' );

        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';

        // 1) Gather & sanitize incoming data
        $first_name   = sanitize_text_field( $_POST['first_name']   ?? '' );
        $last_name    = sanitize_text_field( $_POST['last_name']    ?? '' );
        $email        = sanitize_email(       $_POST['email']        ?? '' );
        $email_verify = sanitize_email(       $_POST['email_verify'] ?? '' );

        // Build a “full address” string in a fixed five‐part order, using an en‐dash with spaces " – "
        $address = implode(
            ' – ',
            [
                sanitize_text_field( $_POST['street_address'] ?? '' ),
                sanitize_text_field( $_POST['address_2']      ?? '' ),
                sanitize_text_field( $_POST['city']           ?? '' ),
                sanitize_text_field( $_POST['state']          ?? '' ),
                sanitize_text_field( $_POST['zip']            ?? '' ),
            ]
        );

        // OTHER NEW FIELDS
        $phone                 = sanitize_text_field( $_POST['phone']            ?? '' );
        $dob                   = sanitize_text_field( $_POST['dob']              ?? '' ); // 'YYYY-MM-DD'
        $member_type           = sanitize_text_field( $_POST['member_type']      ?? 'member' );
        $membership_level      = sanitize_text_field( $_POST['membership_level'] ?? 'free' );
        $facebook              = esc_url_raw( $_POST['facebook']  ?? '' );
        $linkedin              = esc_url_raw( $_POST['linkedin']  ?? '' );
        $instagram             = esc_url_raw( $_POST['instagram'] ?? '' );
        $twitter               = esc_url_raw( $_POST['twitter']   ?? '' );
        $biography             = sanitize_textarea_field( $_POST['biography'] ?? '' );
        $notes                 = sanitize_textarea_field( $_POST['notes']     ?? '' );

        // Interests: array of strings ⇒ implode to comma‐separated
        $interests_array = array_filter( array_map( 'sanitize_text_field', $_POST['interests'] ?? [] ) );
        $interests       = ! empty( $interests_array ) ? implode( ',', $interests_array ) : '';

        // Opt‐in checkboxes (will be '1' if checked, otherwise missing from $_POST)
        $opt_in_marketing_email    = ! empty( $_POST['opt_in_marketing_email'] )    ? 1 : 0;
        $opt_in_marketing_sms      = ! empty( $_POST['opt_in_marketing_sms'] )      ? 1 : 0;
        $opt_in_event_update_email = ! empty( $_POST['opt_in_event_update_email'] ) ? 1 : 0;
        $opt_in_event_update_sms   = ! empty( $_POST['opt_in_event_update_sms'] )   ? 1 : 0;

        // 1a) Basic validation
        if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) ) {
            wp_send_json_error( [ 'message' => 'Please fill in all required fields (first name, last name, and email).' ] );
        }

        // 1b) Check that email & email_verify match
        if ( $email !== $email_verify ) {
            wp_send_json_error( [ 'message' => 'Email addresses do not match. Please correct them.' ] );
        }

        // 2) Check if a WP user already exists with that email
        if ( email_exists( $email ) ) {
            $existing_user = get_user_by( 'email', $email );
            $profile_link  = admin_url( 'user-edit.php?user_id=' . intval( $existing_user->ID ) );
            $msg = sprintf(
                'A WordPress user with that email already exists. View their profile <a href="%s" target="_blank">here</a>.',
                esc_url( $profile_link )
            );
            wp_send_json_error( [ 'message' => $msg ] );
        }

        // 3) Create WP user with default password
        $username = sanitize_user( $email, true );
        if ( username_exists( $username ) ) {
            // If a user-login based on email already exists, append random suffix
            $username .= '_' . wp_generate_password( 4, false, false );
        }
        $random_password = 'NewTTAUser198';
        $userdata = [
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => $random_password,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => 'subscriber',
        ];
        $wp_user_id = wp_insert_user( $userdata );
        if ( is_wp_error( $wp_user_id ) ) {
            wp_send_json_error( [ 'message' => 'Failed to create WordPress user: ' . $wp_user_id->get_error_message() ] );
        }

        // 4) Insert into tta_members table
        $inserted = $wpdb->insert(
            $members_table,
            [
                'wpuserid'                   => intval( $wp_user_id ),
                'first_name'                 => $first_name,
                'last_name'                  => $last_name,
                'email'                      => $email,
                'profileimgid'               => intval( $_POST['profileimgid'] ?? 0 ),
                'joined_at'                  => current_time( 'mysql' ),
                'address'                    => $address,

                // NEW COLUMNS (all sanitized above):
                'phone'                      => $phone,
                'dob'                        => $dob,
                'member_type'                => $member_type,
                'membership_level'           => $membership_level,
                'facebook'                   => $facebook,
                'linkedin'                   => $linkedin,
                'instagram'                  => $instagram,
                'twitter'                    => $twitter,
                'biography'                  => $biography,
                'notes'                      => $notes,
                'interests'                  => $interests,
                'opt_in_marketing_email'     => $opt_in_marketing_email,
                'opt_in_marketing_sms'       => $opt_in_marketing_sms,
                'opt_in_event_update_email'  => $opt_in_event_update_email,
                'opt_in_event_update_sms'    => $opt_in_event_update_sms,
            ],
            [
                '%d', // wpuserid
                '%s', // first_name
                '%s', // last_name
                '%s', // email
                '%d', // profileimgid
                '%s', // joined_at
                '%s', // address

                // NEW formats:
                '%s', // phone
                '%s', // dob
                '%s', // member_type
                '%s', // membership_level
                '%s', // facebook
                '%s', // linkedin
                '%s', // instagram
                '%s', // twitter
                '%s', // biography
                '%s', // notes
                '%s', // interests
                '%d', // opt_in_marketing_email
                '%d', // opt_in_marketing_sms
                '%d', // opt_in_event_update_email
                '%d', // opt_in_event_update_sms
            ]
        );
        $member_id = $wpdb->insert_id;

        if ( ! $member_id ) {
            // Roll back WP user if DB insertion fails
            wp_delete_user( $wp_user_id );
            wp_send_json_error( [ 'message' => 'Failed to create member record in the custom table.' ] );
        }

        // 5) If a profile image was chosen, store it as user meta
        $profileimgid = intval( $_POST['profileimgid'] ?? 0 );
        if ( $profileimgid ) {
            update_user_meta( $wp_user_id, 'profileimgid', $profileimgid );
        }

// 2) Handle profile_image upload
        if ( ! empty( $_FILES['profile_image']['name'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            // Upload to Media Library
            $attachment_id = media_handle_upload( 'profile_image', 0 );

            if ( is_wp_error( $attachment_id ) ) {
                wp_send_json_error( 'Image upload failed: ' . $attachment_id->get_error_message() );
            }

            // 3) Save attachment ID into custom members table
            global $wpdb;
            $table   = $this->members_table;
            $updated = $wpdb->update(
                $table,
                [ 'profile_image_id' => $attachment_id ],
                [ 'id'               => $member_id ],
                [ '%d' ],
                [ '%d' ]
            );

            if ( false === $updated ) {
                wp_send_json_error( 'DB update failed.' );
            }
        }

        wp_send_json_success( [
            'message'    => $response_message,
            'member_id'  => $member_id,
            'wp_user_id' => $wp_user_id,
        ] );
    }

    public function get_member_form() {
        check_ajax_referer( 'tta_member_update_action', 'get_member_nonce' );

        if ( empty( $_POST['member_id'] ) ) {
            wp_send_json_error([ 'message' => 'Missing member ID.' ]);
        }

        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';
        $id = intval( $_POST['member_id'] );

        $member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$members_table} WHERE id = %d", $id ), ARRAY_A );
        if ( ! $member ) {
            wp_send_json_error([ 'message' => 'Member not found.' ]);
        }

        // Ensure media uploader is available
        wp_enqueue_media();
        // Ensure editor (if you have a WYSIWYG for bio)
        if ( function_exists( 'wp_enqueue_editor' ) ) {
            wp_enqueue_editor();
        }

        // Force‐inject $_GET so that members-edit.php sees it
        $_GET['member_id'] = $id;

        ob_start();
        include plugin_dir_path( __FILE__ ) . '../admin/views/members-edit.php';
        $html = ob_get_clean();

        wp_send_json_success([ 'html' => $html ]);
    }

    // In includes/ajax/class-ajax-handler.php, update the `update_member` method to handle address parts:
    public function update_member() {
        check_ajax_referer( 'tta_member_update_action', 'tta_member_update_nonce' );

        if ( empty( $_POST['member_id'] ) ) {
            wp_send_json_error([ 'message' => 'Missing member ID.' ]);
        }

        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';
        $id            = intval( $_POST['member_id'] );

        // 1) Gather and sanitize fields
        $first_name  = sanitize_text_field( $_POST['first_name'] ?? '' );
        $last_name   = sanitize_text_field( $_POST['last_name'] ?? '' );
        $email       = sanitize_email( $_POST['email'] ?? '' );

        $phone            = sanitize_text_field( $_POST['phone'] ?? '' );
        $dob              = sanitize_text_field( $_POST['dob'] ?? '' );
        $member_type      = sanitize_text_field( $_POST['member_type'] ?? '' );
        $membership_level = sanitize_text_field( $_POST['membership_level'] ?? '' );

        // 1) Combine the five address components (always include all 5 slots)
        $address = implode(
            ' – ',
            [
                sanitize_text_field( $_POST['street_address'] ?? '' ),
                sanitize_text_field( $_POST['address_2']      ?? '' ),
                sanitize_text_field( $_POST['city']           ?? '' ),
                sanitize_text_field( $_POST['state']          ?? '' ),
                sanitize_text_field( $_POST['zip']            ?? '' ),
            ]
        );

        $facebook  = esc_url_raw( $_POST['facebook'] ?? '' );
        $linkedin  = esc_url_raw( $_POST['linkedin'] ?? '' );
        $instagram = esc_url_raw( $_POST['instagram'] ?? '' );
        $twitter   = esc_url_raw( $_POST['twitter'] ?? '' );

        $biography = sanitize_textarea_field( $_POST['biography'] ?? '' );
        $notes     = sanitize_textarea_field( $_POST['notes'] ?? '' );

        // Interests:
        $interests_arr = array_filter( array_map( 'sanitize_text_field', $_POST['interests'] ?? [] ) );
        $interests_csv = ! empty( $interests_arr ) ? implode( ',', $interests_arr ) : '';

        // Opt-ins:
        $opt_in_marketing_email     = ! empty( $_POST['opt_in_marketing_email'] )       ? 1 : 0;
        $opt_in_marketing_sms       = ! empty( $_POST['opt_in_marketing_sms'] )         ? 1 : 0;
        $opt_in_event_update_email  = ! empty( $_POST['opt_in_event_update_email'] )    ? 1 : 0;
        $opt_in_event_update_sms    = ! empty( $_POST['opt_in_event_update_sms'] )      ? 1 : 0;

        // Profile image:
        $profileimgid = intval( $_POST['profileimgid'] ?? 0 );

        // 2) Build the update array
        $update_data = [
            'first_name'                 => $first_name,
            'last_name'                  => $last_name,
            'email'                      => $email,
            'phone'                      => $phone,
            'dob'                        => $dob,
            'member_type'                => $member_type,
            'membership_level'           => $membership_level,
            'address'                    => $address,
            'facebook'                   => $facebook,
            'linkedin'                   => $linkedin,
            'instagram'                  => $instagram,
            'twitter'                    => $twitter,
            'biography'                  => $biography,
            'notes'                      => $notes,
            'interests'                  => $interests_csv,
            'opt_in_marketing_email'     => $opt_in_marketing_email,
            'opt_in_marketing_sms'       => $opt_in_marketing_sms,
            'opt_in_event_update_email'  => $opt_in_event_update_email,
            'opt_in_event_update_sms'    => $opt_in_event_update_sms,
            'profileimgid'               => $profileimgid,
        ];

        // 3) Run the update (correct formats array now has 15 '%s' then 5 '%d')
        $updated = $wpdb->update(
            $members_table,
            $update_data,
            [ 'id' => $id ],
            [
                '%s', // first_name
                '%s', // last_name
                '%s', // email
                '%s', // phone
                '%s', // dob
                '%s', // member_type
                '%s', // membership_level
                '%s', // address
                '%s', // facebook
                '%s', // linkedin
                '%s', // instagram
                '%s', // twitter
                '%s', // biography
                '%s', // notes
                '%s', // interests
                '%d', // opt_in_marketing_email
                '%d', // opt_in_marketing_sms
                '%d', // opt_in_event_update_email
                '%d', // opt_in_event_update_sms
                '%d', // profileimgid
            ],
            [ '%d' ]
        );

        if ( false === $updated ) {
            wp_send_json_error([ 'message' => 'Failed to update member.' ]);
        }

        // 4) If profileimgid changed, update user meta too
        $wp_user_id = intval( $member['wpuserid'] );
        if ( $wp_user_id && $profileimgid ) {
            update_user_meta( $wp_user_id, 'profileimgid', $profileimgid );
        }

        // 5) If email changed, update WP user’s email as well
        if ( $wp_user_id ) {
            $existing_wp_email = get_userdata( $wp_user_id )->user_email;
            if ( $email !== $existing_wp_email ) {
                wp_update_user([
                    'ID'         => $wp_user_id,
                    'user_email' => $email,
                ]);
            }
        }

        wp_send_json_success([ 'message' => 'Member updated successfully!' ]);
    }


    /**
     * AJAX: Front-end “Update Logged-In Member’s Profile”
     */
        /**
     * AJAX: Front-end “Update Logged-In Member’s Profile”
     */
    public function update_member_front() {
        // 1) Verify nonce
        check_ajax_referer( 'tta_member_front_update', 'tta_member_front_update_nonce' );

        // 2) Must be logged in
        if ( ! is_user_logged_in() ) {
            wp_send_json_error([ 'message' => 'You must be logged in to update your profile.' ]);
        }

        // 3) Get current WP user & matching tta_members row
        $wp_user_id = get_current_user_id();
        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';

        $member_row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$members_table} WHERE wpuserid = %d LIMIT 1", $wp_user_id ),
            ARRAY_A
        );
        if ( ! $member_row ) {
            wp_send_json_error([ 'message' => 'Member record not found.' ]);
        }
        $member_id = intval( $member_row['id'] );

        // 4) Handle profile image upload if provided
        if ( ! empty( $_FILES['profile_image_file']['name'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attachment_id = media_handle_upload( 'profile_image_file', 0 );
            if ( is_wp_error( $attachment_id ) ) {
                wp_send_json_error([ 'message' => 'Image upload failed: ' . $attachment_id->get_error_message() ]);
            }
            $profileimgid = $attachment_id;
        } else {
            $profileimgid = intval( $_POST['profileimgid'] ?? 0 );
        }

        // 5) Gather & sanitize POST data
        $first_name  = sanitize_text_field( $_POST['first_name']      ?? '' );
        $last_name   = sanitize_text_field( $_POST['last_name']       ?? '' );
        $email       = sanitize_email(       $_POST['email']           ?? '' );
        $email_verify= sanitize_email(       $_POST['email_verify']    ?? '' );

        // (Optional) update WP user email if changed & matched
        if ( $email && $email === $email_verify && $email !== $member_row['email'] ) {
            $upd = wp_update_user([
                'ID'         => $wp_user_id,
                'user_email' => $email,
            ]);
            if ( is_wp_error( $upd ) ) {
                wp_send_json_error([ 'message' => 'Failed to update WordPress user email: ' . $upd->get_error_message() ]);
            }
        }

        $phone      = sanitize_text_field( $_POST['phone']      ?? '' );
        $dob        = sanitize_text_field( $_POST['dob']        ?? '' );
        $facebook   = esc_url_raw( $_POST['facebook']  ?? '' );
        $linkedin   = esc_url_raw( $_POST['linkedin']  ?? '' );
        $instagram  = esc_url_raw( $_POST['instagram'] ?? '' );
        $twitter    = esc_url_raw( $_POST['twitter']   ?? '' );

        // Rebuild full address
        $address_full = implode( ' – ', [
            sanitize_text_field( $_POST['street_address'] ?? '' ),
            sanitize_text_field( $_POST['address_2']      ?? '' ),
            sanitize_text_field( $_POST['city']           ?? '' ),
            sanitize_text_field( $_POST['state']          ?? '' ),
            sanitize_text_field( $_POST['zip']            ?? '' ),
        ] );

        $biography = sanitize_textarea_field( $_POST['biography'] ?? '' );

        // Interests array → CSV
        $ints_arr     = array_filter( array_map( 'sanitize_text_field', $_POST['interests'] ?? [] ) );
        $interests_csv= ! empty( $ints_arr ) ? implode( ',', $ints_arr ) : '';

        // Opt-ins
        $opt_email    = ! empty( $_POST['opt_in_marketing_email']    ) ? 1 : 0;
        $opt_sms      = ! empty( $_POST['opt_in_marketing_sms']      ) ? 1 : 0;
        $opt_upd_email= ! empty( $_POST['opt_in_event_update_email'] ) ? 1 : 0;
        $opt_upd_sms  = ! empty( $_POST['opt_in_event_update_sms']   ) ? 1 : 0;

        // 6) Build update array
        $update_data = [
            'first_name'                 => $first_name,
            'last_name'                  => $last_name,
            'email'                      => $email,
            'phone'                      => $phone,
            'dob'                        => $dob,
            'facebook'                   => $facebook,
            'linkedin'                   => $linkedin,
            'instagram'                  => $instagram,
            'twitter'                    => $twitter,
            'address'                    => $address_full,
            'biography'                  => $biography,
            'interests'                  => $interests_csv,
            'opt_in_marketing_email'     => $opt_email,
            'opt_in_marketing_sms'       => $opt_sms,
            'opt_in_event_update_email'  => $opt_upd_email,
            'opt_in_event_update_sms'    => $opt_upd_sms,
            'profileimgid'               => $profileimgid,
        ];

        // 7) Run the update
        $formats = array_merge(
            array_fill(0, 12, '%s'),
            array_fill(0, 5, '%d')
        );
        $updated = $wpdb->update(
            $members_table,
            $update_data,
            [ 'id' => $member_id ],
            $formats,
            [ '%d' ]
        );
        if ( false === $updated ) {
            wp_send_json_error([ 'message' => 'Failed to update your member data.' ]);
        }

        // 8) Update WP user meta if image changed
        if ( $profileimgid ) {
            update_user_meta( $wp_user_id, 'profileimgid', $profileimgid );
        }

        // 9) Return success + new attachment ID
        wp_send_json_success([
            'message'       => 'Profile updated successfully!',
            'profileimgid'  => $profileimgid,
        ]);
    }

    /**
     * AJAX: Save updates to tickets & their waitlists.
     */
    public function update_ticket() {
        // 1) Verify nonce
        check_ajax_referer( 'tta_ticket_save_action', 'tta_ticket_save_nonce' );

        // 2) Must have event_ute_id
        if ( empty( $_POST['event_ute_id'] ) ) {
            wp_send_json_error( [ 'message' => 'Missing event identifier.' ] );
        }

        global $wpdb;
        $tickets_table  = $wpdb->prefix . 'tta_tickets';
        $waitlist_table = $wpdb->prefix . 'tta_waitlist';
        $ute = sanitize_text_field( $_POST['event_ute_id'] );

        // 3) Grab all submitted arrays
        $names            = $_POST['event_name']             ?? [];
        $limits           = $_POST['attendancelimit']        ?? [];
        $base_costs       = $_POST['baseeventcost']          ?? [];
        $member_costs     = $_POST['discountedmembercost']   ?? [];
        $premium_costs    = $_POST['premiummembercost']      ?? [];
        $waitlist_csv_by_tid = $_POST['waitlist_userids']    ?? [];

        error_log(print_r($waitlist_csv_by_tid,true));

        // 4) Loop through each ticket by ID
        foreach ( $names as $tid => $raw_name ) {
            $tid = intval( $tid );
            // 4a) Update ticket row
            $wpdb->update(
                $tickets_table,
                [
                    'ticket_name'           => sanitize_text_field( $raw_name ),
                    'attendancelimit'       => intval( $limits[ $tid ] ?? 0 ),
                    'baseeventcost'         => floatval( $base_costs[ $tid ] ?? 0 ),
                    'discountedmembercost'  => floatval( $member_costs[ $tid ] ?? 0 ),
                    'premiummembercost'     => floatval( $premium_costs[ $tid ] ?? 0 ),
                ],
                [ 'id' => $tid ],
                [ '%s', '%d', '%f', '%f', '%f' ],
                [ '%d' ]
            );

            // 4b) Handle its waitlist
            $csv = sanitize_text_field( $waitlist_csv_by_tid[ $tid ] ?? '' );

            error_log($csv);
            if ( '' === $csv ) {
                // If CSV is blank, clear out the userids column (do not delete row)
                $wpdb->update(
                    $waitlist_table,
                    [ 'userids' => '' ],
                    [ 'ticket_id' => $tid ],
                    [ '%s' ],
                    [ '%d' ]
                );
            } else {
                // Otherwise update existing row or insert new one
                $exists = (bool) $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM {$waitlist_table} WHERE ticket_id = %d",
                        $tid
                    )
                );
                if ( $exists ) {
                    $wpdb->update(
                        $waitlist_table,
                        [ 'userids' => $csv ],
                        [ 'ticket_id' => $tid ],
                        [ '%s' ],
                        [ '%d' ]
                    );
                } else {
                    $wpdb->insert(
                        $waitlist_table,
                        [
                            'event_ute_id' => $ute,
                            'ticket_id'    => $tid,
                            'userids'      => $csv,
                        ],
                        [ '%s', '%d', '%s' ]
                    );
                }
            }
        }

        // 5) All done
        wp_send_json_success( [ 'message' => __( 'Tickets & waitlists saved.', 'tta' ) ] );
    }







} // end class

TTA_Ajax_Handler::get_instance();
