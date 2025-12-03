<?php
use Twilio\Rest\Client;

class TTA_SMS_Handler {
    /** @var self */
    protected static $instance;

    /** @var Client|null */
    protected $client = null;

    /** @var string */
    protected $from = '';

    /** @var string */
    protected $messaging_service_sid = '';

    /** @var string */
    protected $recipient_override = '';

    /** @var bool */
    protected $sandbox_mode = false;

    public static function get_instance() {
        return self::$instance ?: ( self::$instance = new self() );
    }

    private function __construct() {
        if ( ! class_exists( Client::class ) ) {
            return;
        }

        if (
            defined( 'TTA_TWILIO_API_SID' ) && TTA_TWILIO_API_SID &&
            defined( 'TTA_TWILIO_API_KEY' ) && TTA_TWILIO_API_KEY
        ) {
            $account_sid   = defined( 'TTA_TWILIO_USER_SID' ) ? TTA_TWILIO_USER_SID : null;
            $this->client  = $account_sid ? new Client( TTA_TWILIO_API_SID, TTA_TWILIO_API_KEY, $account_sid ) : new Client( TTA_TWILIO_API_SID, TTA_TWILIO_API_KEY );
        } elseif (
            defined( 'TTA_TWILIO_SID' ) && TTA_TWILIO_SID &&
            defined( 'TTA_TWILIO_TOKEN' ) && TTA_TWILIO_TOKEN
        ) {
            $this->client = new Client( TTA_TWILIO_SID, TTA_TWILIO_TOKEN );
        }

        if ( ! $this->client ) {
            return;
        }

        if ( defined( 'TTA_TWILIO_MESSAGING_SERVICE_SID' ) && TTA_TWILIO_MESSAGING_SERVICE_SID ) {
            $this->messaging_service_sid = TTA_TWILIO_MESSAGING_SERVICE_SID;
        }

        if ( defined( 'TTA_TWILIO_SENDING_NUMBER' ) && TTA_TWILIO_SENDING_NUMBER ) {
            $this->from = TTA_TWILIO_SENDING_NUMBER;
        } elseif ( defined( 'TTA_TWILIO_FROM' ) && TTA_TWILIO_FROM ) {
            $this->from = TTA_TWILIO_FROM;
        }

        if ( defined( 'TTA_TWILIO_IS_SANDBOX' ) ) {
            $this->sandbox_mode = (bool) TTA_TWILIO_IS_SANDBOX;
        } elseif ( defined( 'TTA_TWILIO_ENVIRONMENT' ) ) {
            $this->sandbox_mode = 'sandbox' === strtolower( TTA_TWILIO_ENVIRONMENT );
        }

        if ( $this->sandbox_mode ) {
            if ( defined( 'TTA_TWILIO_SANDBOX_NUMBER' ) && TTA_TWILIO_SANDBOX_NUMBER ) {
                $this->recipient_override = sanitize_text_field( TTA_TWILIO_SANDBOX_NUMBER );
            } elseif ( getenv( 'TTA_TWILIO_SANDBOX_NUMBER' ) ) {
                $this->recipient_override = sanitize_text_field( getenv( 'TTA_TWILIO_SANDBOX_NUMBER' ) );
            }
        }
    }

    protected function send_sms( $to, $body ) {
        if ( $this->sandbox_mode && ! $this->recipient_override ) {
            return;
        }

        $to = $this->recipient_override ?: $to;

        if ( ! $this->client || ! $to || ! $body ) {
            return;
        }
        try {
            $args = [ 'body' => $body ];
            if ( $this->messaging_service_sid ) {
                $args['messagingServiceSid'] = $this->messaging_service_sid;
            } elseif ( $this->from ) {
                $args['from'] = $this->from;
            } else {
                return;
            }
            $this->client->messages->create( $to, $args );
        } catch ( \Exception $e ) {
            // error_log( 'TTA SMS Error: ' . $e->getMessage() );
        }
    }

    protected function normalize_numbers( array $numbers ) {
        if ( $this->recipient_override ) {
            return [ $this->recipient_override ];
        }

        if ( $this->sandbox_mode ) {
            return [];
        }

        $normalized = [];
        foreach ( $numbers as $number ) {
            $number = sanitize_text_field( $number );
            if ( $number && ! in_array( $number, $normalized, true ) ) {
                $normalized[] = $number;
            }
        }

        return $normalized;
    }

    protected function build_tokens( array $event, array $member, array $attendees, array $refund = [] ) {
        $email = TTA_Email_Handler::get_instance();
        $ref   = new \ReflectionClass( $email );
        $m     = $ref->getMethod( 'build_tokens' );
        $m->setAccessible( true );
        return $m->invoke( $email, $event, $member, $attendees, $refund );
    }

    /**
     * Render a template string into a final SMS body.
     *
     * @param string $template_text Template contents containing tokens.
     * @param array  $event         Event data for token replacement.
     * @param array  $member        Member context for token replacement.
     * @param array  $attendees     Attendee list for token replacement.
     * @param array  $refund        Refund data for token replacement.
     * @return string
     */
    public function compile_message( $template_text, array $event, array $member, array $attendees, array $refund = [] ) {
        $template_text = (string) $template_text;
        if ( '' === trim( $template_text ) ) {
            return '';
        }

        $tokens  = $this->build_tokens( $event, $member, $attendees, $refund );
        $msg_raw = tta_expand_anchor_tokens( $template_text, $tokens );

        return tta_strip_bold( strtr( $msg_raw, $tokens ) );
    }

    /**
     * Send an SMS message to multiple recipients, respecting sandbox overrides.
     *
     * @param array  $numbers Recipient phone numbers.
     * @param string $message Message body.
     * @return void
     */
    public function send_bulk_sms( array $numbers, $message ) {
        $message = trim( (string) $message );
        if ( '' === $message ) {
            return;
        }

        foreach ( $this->normalize_numbers( $numbers ) as $num ) {
            $this->send_sms( $num, $message );
        }
    }

    public function send_purchase_texts( array $items, $user_id ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['purchase']['sms_text'] ) ) {
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
                    $attendees[] = [
                        'first_name' => sanitize_text_field( $att['first_name'] ?? '' ),
                        'last_name'  => sanitize_text_field( $att['last_name'] ?? '' ),
                        'email'      => sanitize_email( $att['email'] ?? '' ),
                        'phone'      => sanitize_text_field( $att['phone'] ?? '' ),
                        'opt_in_sms' => intval( $att['opt_in_sms'] ?? 0 ),
                    ];
                }
            }

            $opted_in_attendees = array_values(
                array_filter(
                    $attendees,
                    function ( $attendee ) {
                        return ! empty( $attendee['opt_in_sms'] );
                    }
                )
            );

            if ( empty( $opted_in_attendees ) ) {
                continue;
            }

            $tokens  = $this->build_tokens( $event, $context, $opted_in_attendees );
            $msg_raw = tta_expand_anchor_tokens( $tpl['sms_text'], $tokens );
            $message = tta_strip_bold( strtr( $msg_raw, $tokens ) );

            $numbers = array_column( $opted_in_attendees, 'phone' );

            foreach ( $this->normalize_numbers( $numbers ) as $num ) {
                $this->send_sms( $num, $message );
            }
        }
    }

    public function send_refund_texts( array $transaction, array $refund ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['refund_processed']['sms_text'] ) ) {
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
        $attendees = array_map(
            function ( $attendee ) {
                return [
                    'first_name' => sanitize_text_field( $attendee['first_name'] ?? '' ),
                    'last_name'  => sanitize_text_field( $attendee['last_name'] ?? '' ),
                    'email'      => sanitize_email( $attendee['email'] ?? '' ),
                    'phone'      => sanitize_text_field( $attendee['phone'] ?? '' ),
                    'opt_in_sms' => intval( $attendee['opt_in_sms'] ?? 0 ),
                    'status'     => sanitize_text_field( $attendee['status'] ?? '' ),
                ];
            },
            tta_get_transaction_event_attendees( $transaction['transaction_id'], $refund['event_id'] )
        );

        $opted_in_attendees = array_values(
            array_filter(
                $attendees,
                function ( $attendee ) {
                    return ! empty( $attendee['opt_in_sms'] );
                }
            )
        );

        if ( empty( $opted_in_attendees ) ) {
            return;
        }

        $tokens  = $this->build_tokens( $event, $context, $opted_in_attendees, $refund );
        $msg_raw = tta_expand_anchor_tokens( $tpl['sms_text'], $tokens );
        $message = tta_strip_bold( strtr( $msg_raw, $tokens ) );

        $numbers = array_column( $opted_in_attendees, 'phone' );
        foreach ( $this->normalize_numbers( $numbers ) as $num ) {
            $this->send_sms( $num, $message );
        }
    }

    public function send_waitlist_text( array $entry, array $event ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['waitlist_available']['sms_text'] ) ) {
            return;
        }
        $tpl = $templates['waitlist_available'];

        $context = tta_build_waitlist_notification_context( $entry, $event );
        $tokens  = $context['tokens'];

        if ( empty( $context['data']['opt_in_sms'] ) ) {
            return;
        }

        $msg_raw = tta_expand_anchor_tokens( $tpl['sms_text'], $tokens );
        $message = tta_strip_bold( strtr( $msg_raw, $tokens ) );
        $phone   = $entry['phone'] ?? '';
        foreach ( $this->normalize_numbers( [ $phone ] ) as $num ) {
            $this->send_sms( $num, $message );
        }
    }
}
