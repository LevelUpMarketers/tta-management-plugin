<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Handles outbound email notifications.
 */
class TTA_Email_Handler {
    /** @var self */
    protected static $instance;

    /**
     * Singleton accessor.
     *
     * @return self
     */
    public static function get_instance() {
        return self::$instance ?: ( self::$instance = new self() );
    }

    /** Initialize hooks. */
    private function __construct() {
        // Placeholder for future scheduling hooks.
    }

    /**
     * Send purchase confirmation emails for each event in the cart.
     *
     * @param array $items   Items array from TTA_Cart::get_items_with_discounts().
     * @param int   $user_id WordPress user ID who completed checkout.
     */
    public function send_purchase_emails( array $items, $user_id ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['purchase'] ) ) {
            return;
        }
        $tpl = $templates['purchase'];

        $context = tta_get_current_user_context();
        if ( $context['wp_user_id'] !== intval( $user_id ) ) {
            $user = get_user_by( 'ID', $user_id );
            if ( $user ) {
                $context['wp_user_id'] = $user_id;
                $context['user_email'] = sanitize_email( $user->user_email );
                $context['first_name'] = sanitize_text_field( $user->first_name );
                $context['last_name']  = sanitize_text_field( $user->last_name );
            }
        }

        $by_event = [];
        foreach ( $items as $it ) {
            $by_event[ $it['event_ute_id'] ][] = $it;
        }

        foreach ( $by_event as $ute_id => $ev_items ) {
            $event = tta_get_event_for_email( $ute_id );
            if ( empty( $event ) ) {
                continue;
            }

            $attendees = [];
            foreach ( $ev_items as $it ) {
                foreach ( (array) ( $it['attendees'] ?? [] ) as $att ) {
                    $attendees[] = $att;
                }
            }

            $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
            $sent    = [];

            // Send email to purchasing member using default attendee order.
            $base_tokens = $this->build_tokens( $event, $context, $attendees );
            $subject_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $base_tokens );
            $subject     = tta_strip_bold( strtr( $subject_raw, $base_tokens ) );
            $body_raw    = tta_expand_anchor_tokens( $tpl['email_body'], $base_tokens );
            $body_txt    = tta_convert_bold( tta_convert_links( strtr( $body_raw, $base_tokens ) ) );
            $body        = nl2br( $body_txt );
            $to          = sanitize_email( $context['user_email'] );
            if ( $to && ! in_array( $to, $sent, true ) ) {
                wp_mail( $to, $subject, $body, $headers );
                $sent[] = $to;
            }

            // Send personalized email to each attendee.
            foreach ( $attendees as $index => $att ) {
                $recipient = sanitize_email( $att['email'] ?? '' );
                if ( ! $recipient || in_array( $recipient, $sent, true ) ) {
                    continue;
                }

                // Place this attendee first in the array for token generation.
                $ordered = $attendees;
                if ( $index !== 0 ) {
                    $ordered[ $index ] = $attendees[0];
                    $ordered[0]       = $att;
                }

                $tokens      = $this->build_tokens( $event, $context, $ordered );
                $subject_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
                $subject     = tta_strip_bold( strtr( $subject_raw, $tokens ) );
                $body_raw    = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
                $body_txt    = tta_convert_bold( tta_convert_links( strtr( $body_raw, $tokens ) ) );
                $body        = nl2br( $body_txt );
                wp_mail( $recipient, $subject, $body, $headers );
                $sent[] = $recipient;
            }
        }
    }

    /**
     * Build a token replacement map for an event email.
     *
     * @param array $event     Event details from tta_get_event_for_email().
     * @param array $member    Current user context from tta_get_current_user_context().
     * @param array $attendees List of attendee arrays.
     * @return array
     */
    protected function build_tokens( array $event, array $member, array $attendees, array $refund = [] ) {
        $tokens = [
            '{event_name}'           => $event['name'] ?? '',
            '{event_address}'        => $event['address'] ?? '',
            '{event_address_link}'   => isset( $event['address'] ) && $event['address'] !== ''
                ? esc_url( 'https://maps.google.com/?q=' . rawurlencode( $event['address'] ) )
                : '',
            '{event_link}'           => $event['page_url'] ?? '',
            '{dashboard_profile_url}'  => home_url( '/member-dashboard/?tab=profile' ),
            '{dashboard_upcoming_url}' => home_url( '/member-dashboard/?tab=upcoming' ),
            '{dashboard_past_url}'       => home_url( '/member-dashboard/?tab=past' ),
            '{dashboard_billing_url}'    => home_url( '/member-dashboard/?tab=billing' ),
            '{dashboard_waitlist_url}'   => home_url( '/member-dashboard/?tab=waitlist' ),
            '{event_date}'           => isset( $event['date'] ) ? tta_format_event_date( $event['date'] ) : '',
            '{event_time}'           => isset( $event['time'] ) ? tta_format_event_time( $event['time'] ) : '',
            '{event_type}'           => $event['type'] ?? '',
            '{venue_name}'           => $event['venue_name'] ?? '',
            '{venue_url}'            => $event['venue_url'] ?? '',
            '{base_cost}'            => isset( $event['base_cost'] ) ? number_format( (float) $event['base_cost'], 2 ) : '',
            '{member_cost}'          => isset( $event['member_cost'] ) ? number_format( (float) $event['member_cost'], 2 ) : '',
            '{premium_cost}'         => isset( $event['premium_cost'] ) ? number_format( (float) $event['premium_cost'], 2 ) : '',
            '{first_name}'           => $member['first_name'] ?? '',
            '{last_name}'            => $member['last_name'] ?? '',
            '{email}'                => $member['user_email'] ?? '',
            '{phone}'                => $member['member']['phone'] ?? '',
            '{membership_level}'     => '',
            '{membership_price}'     => '',
            '{subscription_id}'      => sanitize_text_field( $member['subscription_id'] ?? '' ),
            '{member_type}'          => $member['member']['member_type'] ?? '',
        ];

        $level = strtolower( $member['membership_level'] ?? '' );
        switch ( $level ) {
            case 'basic':
                $tokens['{membership_level}'] = __( 'Standard', 'tta' );
                break;
            case 'premium':
                $tokens['{membership_level}'] = __( 'Premium', 'tta' );
                break;
            case 'reentry':
                $tokens['{membership_level}'] = __( 'Re-Entry', 'tta' );
                break;
            default:
                $tokens['{membership_level}'] = __( 'Free', 'tta' );
        }

        $price = tta_get_membership_price( $level );
        $tokens['{membership_price}'] = '$' . number_format( (float) $price, 2 );

        $names = tta_get_event_host_volunteer_names( $event['id'] ?? 0 );
        $tokens['{event_host}']       = $names['hosts'] ? implode( ', ', $names['hosts'] ) : 'TBD';
        $tokens['{event_hosts}']      = $tokens['{event_host}'];
        $tokens['{event_volunteer}']  = $names['volunteers'] ? implode( ', ', $names['volunteers'] ) : 'TBD';
        $tokens['{event_volunteers}'] = $tokens['{event_volunteer}'];
        $tokens['{host_notes}']       = tta_unslash( $event['host_notes'] ?? '' );

        for ( $i = 0; $i < 4; $i++ ) {
            $a = $attendees[ $i ] ?? [];
            $index = $i === 0 ? '' : ( $i + 1 );
            $tokens[ '{attendee' . $index . '_first_name}' ] = sanitize_text_field( $a['first_name'] ?? '' );
            $tokens[ '{attendee' . $index . '_last_name}' ]  = sanitize_text_field( $a['last_name'] ?? '' );
            $tokens[ '{attendee' . $index . '_email}' ]      = sanitize_email( $a['email'] ?? '' );
            $tokens[ '{attendee' . $index . '_phone}' ]      = sanitize_text_field( $a['phone'] ?? '' );
        }

        $att_ref = $refund['attendee'] ?? [];
        $tokens['{refund_first_name}'] = sanitize_text_field( $refund['first_name'] ?? $att_ref['first_name'] ?? '' );
        $tokens['{refund_last_name}']  = sanitize_text_field( $refund['last_name'] ?? $att_ref['last_name'] ?? '' );
        $tokens['{refund_email}']      = sanitize_email( $refund['email'] ?? $att_ref['email'] ?? '' );
        $tokens['{refund_amount}']     = isset( $refund['amount'] ) ? number_format( (float) $refund['amount'], 2 ) : '';
        $tokens['{refund_ticket}']     = sanitize_text_field( $refund['ticket_name'] ?? '' );
        $tokens['{refund_event_name}'] = $event['name'] ?? '';
        $tokens['{refund_event_date}'] = isset( $event['date'] ) ? tta_format_event_date( $event['date'] ) : '';
        $tokens['{refund_event_time}'] = isset( $event['time'] ) ? tta_format_event_time( $event['time'] ) : '';

        $now  = new \DateTime( 'now', new \DateTimeZone( 'America/New_York' ) );
        $tokens['{current_time}']         = $now->format( 'g:i A' );
        $tokens['{current_date}']         = $now->format( 'n/j/Y' );
        $tokens['{current_weekday}']      = $now->format( 'l' );
        $tokens['{current_month}']        = $now->format( 'F' );
        $tokens['{current_day_of_month}'] = $now->format( 'jS' );

        return $tokens;
    }

    /**
     * Send a refund processed email to all attendees on the transaction.
     *
     * @param array $transaction Transaction row.
     * @param array $refund      Refund info (event_id, ticket_id, attendee, amount, ticket_name).
     */
    public function send_refund_emails( array $transaction, array $refund ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['refund_processed'] ) ) {
            return;
        }
        $tpl = $templates['refund_processed'];

        $event_ute = tta_get_event_ute_id( intval( $refund['event_id'] ) );
        if ( ! $event_ute ) {
            return;
        }

        $event = tta_get_event_for_email( $event_ute );
        if ( empty( $event ) ) {
            return;
        }

        $context   = tta_get_user_context_by_id( intval( $transaction['wpuserid'] ) );
        $attendees = tta_get_transaction_event_attendees( $transaction['transaction_id'], $refund['event_id'] );

        $tokens      = $this->build_tokens( $event, $context, $attendees, $refund );
        $subject_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
        $subject     = tta_strip_bold( strtr( $subject_raw, $tokens ) );
        $body_raw    = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
        $body_txt    = tta_convert_bold( tta_convert_links( strtr( $body_raw, $tokens ) ) );
        $body        = nl2br( $body_txt );

        $recipients = array_unique( array_merge( [ $context['user_email'] ], array_column( $attendees, 'email' ) ) );
        $headers    = [ 'Content-Type: text/html; charset=UTF-8' ];
        foreach ( $recipients as $to ) {
            $to = sanitize_email( $to );
            if ( $to ) {
                wp_mail( $to, $subject, $body, $headers );
            }
        }
    }

    /**
     * Send a cancellation confirmation email when a free ticket is released.
     *
     * @param array $attendee Attendee details.
     * @param int   $event_id Event ID.
     */
    public function send_cancellation_email( array $attendee, $event_id ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['cancellation_requested'] ) ) {
            return;
        }
        $tpl = $templates['cancellation_requested'];

        $ute = tta_get_event_ute_id( intval( $event_id ) );
        if ( ! $ute ) {
            return;
        }

        $event = tta_get_event_for_email( $ute );
        if ( empty( $event ) ) {
            return;
        }

        $context = tta_get_current_user_context();
        $context['first_name'] = sanitize_text_field( $attendee['first_name'] ?? $context['first_name'] );
        $context['last_name']  = sanitize_text_field( $attendee['last_name'] ?? $context['last_name'] );
        $context['user_email'] = sanitize_email( $attendee['email'] ?? $context['user_email'] );

        $attendees = [ $attendee ];
        $tokens      = $this->build_tokens( $event, $context, $attendees );
        $subject_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
        $subject     = tta_strip_bold( strtr( $subject_raw, $tokens ) );
        $body_raw    = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
        $body_txt    = tta_convert_bold( tta_convert_links( strtr( $body_raw, $tokens ) ) );
        $body        = nl2br( $body_txt );

        $to      = sanitize_email( $attendee['email'] ?? '' );
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        if ( $to ) {
            wp_mail( $to, $subject, $body, $headers );
        }
    }

    /**
     * Send a membership purchase confirmation email.
     *
     * @param int    $user_id WordPress user ID.
     * @param string $level   Membership level slug.
     */
    public function send_membership_purchase_email( $user_id, $level ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['membership_purchase'] ) ) {
            return;
        }
        $tpl     = $templates['membership_purchase'];
        $context = tta_get_user_context_by_id( intval( $user_id ) );
        $context['membership_level'] = $level;

        $tokens      = $this->build_tokens( [], $context, [] );
        $subject_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
        $subject     = tta_strip_bold( strtr( $subject_raw, $tokens ) );
        $body_raw    = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
        $body_txt    = tta_convert_bold( tta_convert_links( strtr( $body_raw, $tokens ) ) );
        $body        = nl2br( $body_txt );
        $to          = sanitize_email( $context['user_email'] );
        $headers     = [ 'Content-Type: text/html; charset=UTF-8' ];
        if ( $to ) {
            wp_mail( $to, $subject, $body, $headers );
        }
    }

    /**
     * Send a membership cancellation confirmation email.
     *
     * @param int    $user_id WordPress user ID.
     * @param string $level   Membership level slug that was cancelled.
     */
    public function send_membership_cancellation_email( $user_id, $level ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['membership_cancellation'] ) ) {
            return;
        }
        $tpl     = $templates['membership_cancellation'];
        $context = tta_get_user_context_by_id( intval( $user_id ) );
        $context['membership_level'] = $level;

        $tokens      = $this->build_tokens( [], $context, [] );
        $subject_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
        $subject     = tta_strip_bold( strtr( $subject_raw, $tokens ) );
        $body_raw    = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
        $body_txt    = tta_convert_bold( tta_convert_links( strtr( $body_raw, $tokens ) ) );
        $body        = nl2br( $body_txt );
        $to          = sanitize_email( $context['user_email'] );
        $headers     = [ 'Content-Type: text/html; charset=UTF-8' ];
        if ( $to ) {
            wp_mail( $to, $subject, $body, $headers );
        }
    }

    /**
     * Send a membership change confirmation email.
     *
     * @param int    $user_id WordPress user ID.
     * @param string $level   New membership level slug.
     */
    public function send_membership_change_email( $user_id, $level ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['membership_change'] ) ) {
            return;
        }
        $tpl     = $templates['membership_change'];
        $context = tta_get_user_context_by_id( intval( $user_id ) );
        $context['membership_level'] = $level;

        $tokens      = $this->build_tokens( [], $context, [] );
        $subject_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
        $subject     = tta_strip_bold( strtr( $subject_raw, $tokens ) );
        $body_raw    = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
        $body_txt    = tta_convert_bold( tta_convert_links( strtr( $body_raw, $tokens ) ) );
        $body        = nl2br( $body_txt );
        $to          = sanitize_email( $context['user_email'] );
        $headers     = [ 'Content-Type: text/html; charset=UTF-8' ];
        if ( $to ) {
            wp_mail( $to, $subject, $body, $headers );
        }
    }

    public function send_mass_email( $event_ute_id, array $emails, $subject_raw, $body_raw ) {
        $event = tta_get_event_for_email( $event_ute_id );
        if ( empty( $event ) ) {
            return 0;
        }
        $attendees = tta_get_event_attendees( $event_ute_id );
        $by_email  = [];
        foreach ( (array) $attendees as $att ) {
            $e = sanitize_email( $att['email'] ?? '' );
            if ( $e ) {
                $by_email[ strtolower( $e ) ] = $att;
            }
        }
        $count   = 0;
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        foreach ( $emails as $addr ) {
            $email = sanitize_email( $addr );
            if ( ! $email ) {
                continue;
            }
            $att  = $by_email[ strtolower( $email ) ] ?? [ 'first_name' => '', 'last_name' => '', 'email' => $email ];
            $row  = tta_get_member_row_by_email( $email );
            $ctx  = $row ? tta_get_user_context_by_id( intval( $row['wpuserid'] ) ) : [ 'wp_user_id' => 0, 'user_email' => $email, 'first_name' => $att['first_name'], 'last_name' => $att['last_name'], 'member' => [ 'phone' => '', 'member_type' => '' ], 'membership_level' => tta_get_membership_level_by_email( $email ), 'subscription_id' => null, 'subscription_status' => null, 'banned_until' => null ];
            $tokens      = $this->build_tokens( $event, $ctx, [ $att ] );
            $subject_txt = tta_expand_anchor_tokens( $subject_raw, $tokens );
            $subject     = tta_strip_bold( strtr( $subject_txt, $tokens ) );
            $body_exp    = tta_expand_anchor_tokens( $body_raw, $tokens );
            $body_txt    = tta_convert_bold( tta_convert_links( strtr( $body_exp, $tokens ) ) );
            $body        = nl2br( $body_txt );
            wp_mail( $email, $subject, $body, $headers );
            $count++;
        }
        return $count;
    }
}
