<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Send scheduled SMS reminders alongside email notifications.
 */
class TTA_SMS_Reminders {
    /**
     * Templates that should trigger attendee reminder SMS messages.
     *
     * @var string[]
     */
    protected static $attendee_templates = [ 'reminder_24hr', 'reminder_2hr' ];

    /**
     * Cached member contexts keyed by email address.
     *
     * @var array
     */
    protected static $context_cache = [];

    /**
     * Register hooks for reminder SMS delivery.
     *
     * @return void
     */
    public static function init() {
        add_action( 'tta_attendee_reminder_email', [ __CLASS__, 'send_attendee_reminder_sms' ], 20, 2 );
        add_action( 'tta_post_event_thanks_email', [ __CLASS__, 'send_post_event_thanks_sms' ], 20, 1 );
    }

    /**
     * Send SMS reminders for attendee reminder templates.
     *
     * @param int    $event_id     Event identifier.
     * @param string $template_key Reminder template key.
     * @return void
     */
    public static function send_attendee_reminder_sms( $event_id, $template_key ) {
        $event_id     = intval( $event_id );
        $template_key = sanitize_key( $template_key );

        if ( ! $event_id || ! in_array( $template_key, self::$attendee_templates, true ) ) {
            return;
        }

        $template = self::get_template( $template_key );
        if ( ! is_array( $template ) || empty( $template['sms_text'] ) ) {
            return;
        }

        $ute_id = tta_get_event_ute_id( $event_id );
        if ( ! $ute_id ) {
            return;
        }

        $event = tta_get_event_for_email( $ute_id );
        if ( empty( $event ) ) {
            return;
        }

        $attendees = tta_get_event_attendees_with_status( $ute_id );
        if ( empty( $attendees ) ) {
            return;
        }

        self::deliver_attendee_sms( $attendees, $event, $template );
    }

    /**
     * Send post-event thank you SMS messages to checked-in attendees.
     *
     * @param int $event_id Event identifier.
     * @return void
     */
    public static function send_post_event_thanks_sms( $event_id ) {
        $event_id = intval( $event_id );
        if ( ! $event_id ) {
            return;
        }

        $template = self::get_template( 'post_event_review' );
        if ( ! is_array( $template ) || empty( $template['sms_text'] ) ) {
            return;
        }

        $ute_id = tta_get_event_ute_id( $event_id );
        if ( ! $ute_id ) {
            return;
        }

        $event = tta_get_event_for_email( $ute_id );
        if ( empty( $event ) ) {
            return;
        }

        $attendees = array_filter(
            tta_get_event_attendees_with_status( $ute_id ),
            function ( $attendee ) {
                return ! empty( $attendee['opt_in_sms'] ) && isset( $attendee['status'] ) && 'checked_in' === $attendee['status'];
            }
        );

        if ( empty( $attendees ) ) {
            return;
        }

        self::deliver_attendee_sms( $attendees, $event, $template );
    }

    /**
     * Retrieve a communication template by key.
     *
     * @param string $key Template key.
     * @return array|null
     */
    protected static function get_template( $key ) {
        $templates = tta_get_comm_templates();

        return isset( $templates[ $key ] ) ? $templates[ $key ] : null;
    }

    /**
     * Deliver SMS messages to the provided attendees.
     *
     * @param array $attendees Attendee list.
     * @param array $event     Event details.
     * @param array $template  Template data.
     * @return void
     */
    protected static function deliver_attendee_sms( array $attendees, array $event, array $template ) {
        if ( ! is_array( $template ) || empty( $template['sms_text'] ) ) {
            return;
        }

        $handler = TTA_SMS_Handler::get_instance();
        if ( ! $handler ) {
            return;
        }

        self::$context_cache = [];

        foreach ( $attendees as $attendee ) {
            if ( empty( $attendee['opt_in_sms'] ) ) {
                continue;
            }

            $phone = sanitize_text_field( $attendee['phone'] ?? '' );
            if ( '' === $phone ) {
                continue;
            }

            $attendee_prepared = [
                'first_name' => sanitize_text_field( $attendee['first_name'] ?? '' ),
                'last_name'  => sanitize_text_field( $attendee['last_name'] ?? '' ),
                'email'      => sanitize_email( $attendee['email'] ?? '' ),
                'phone'      => $phone,
                'status'     => sanitize_text_field( $attendee['status'] ?? '' ),
                'opt_in_sms' => intval( $attendee['opt_in_sms'] ?? 0 ),
            ];

            if ( $attendee_prepared['status'] && ! in_array( $attendee_prepared['status'], [ 'pending', 'checked_in' ], true ) ) {
                continue;
            }

            $context = self::get_member_context( $attendee_prepared );
            $message = $handler->compile_message( $template['sms_text'], $event, $context, [ $attendee_prepared ] );
            if ( '' === $message ) {
                continue;
            }

            $handler->send_bulk_sms( [ $phone ], $message );
        }
    }

    /**
     * Build the token context for an attendee.
     *
     * @param array $attendee Attendee details.
     * @return array
     */
    protected static function get_member_context( array $attendee ) {
        $email = sanitize_email( $attendee['email'] ?? '' );
        if ( isset( self::$context_cache[ $email ] ) ) {
            return self::$context_cache[ $email ];
        }

        $context = [
            'wp_user_id'         => 0,
            'user_email'         => $email,
            'first_name'         => sanitize_text_field( $attendee['first_name'] ?? '' ),
            'last_name'          => sanitize_text_field( $attendee['last_name'] ?? '' ),
            'member'             => [
                'phone'               => sanitize_text_field( $attendee['phone'] ?? '' ),
                'member_type'         => '',
                'membership_level'    => '',
                'subscription_id'     => null,
                'subscription_status' => null,
                'banned_until'        => null,
            ],
            'membership_level'   => '',
            'subscription_id'    => null,
            'subscription_status'=> null,
            'banned_until'       => null,
        ];

        if ( $email ) {
            $user = get_user_by( 'email', $email );
            if ( $user ) {
                $context = tta_get_user_context_by_id( intval( $user->ID ) );
                if ( empty( $context['member'] ) || ! is_array( $context['member'] ) ) {
                    $context['member'] = [];
                }
                if ( empty( $context['member']['phone'] ) ) {
                    $context['member']['phone'] = sanitize_text_field( $attendee['phone'] ?? '' );
                }
            }
        }

        if ( empty( $context['member']['phone'] ) ) {
            $context['member']['phone'] = sanitize_text_field( $attendee['phone'] ?? '' );
        }

        return self::$context_cache[ $email ] = $context;
    }
}
