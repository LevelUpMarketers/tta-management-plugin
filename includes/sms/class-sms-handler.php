<?php
use Twilio\Rest\Client;

class TTA_SMS_Handler {
    /** @var self */
    protected static $instance;

    /** @var Client|null */
    protected $client = null;

    /** @var string */
    protected $from = '';

    public static function get_instance() {
        return self::$instance ?: ( self::$instance = new self() );
    }

    private function __construct() {
        if (
            defined( 'TTA_TWILIO_SID' ) &&
            defined( 'TTA_TWILIO_TOKEN' ) &&
            defined( 'TTA_TWILIO_FROM' ) &&
            class_exists( Client::class )
        ) {
            $this->client = new Client( TTA_TWILIO_SID, TTA_TWILIO_TOKEN );
            $this->from   = TTA_TWILIO_FROM;
        }
    }

    protected function send_sms( $to, $body ) {
        if ( ! $this->client || ! $to || ! $body ) {
            return;
        }
        try {
            $this->client->messages->create( $to, [ 'from' => $this->from, 'body' => $body ] );
        } catch ( \Exception $e ) {
            error_log( 'TTA SMS Error: ' . $e->getMessage() );
        }
    }

    protected function build_tokens( array $event, array $member, array $attendees, array $refund = [] ) {
        $email = TTA_Email_Handler::get_instance();
        $ref   = new \ReflectionClass( $email );
        $m     = $ref->getMethod( 'build_tokens' );
        $m->setAccessible( true );
        return $m->invoke( $email, $event, $member, $attendees, $refund );
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
                    $attendees[] = $att;
                }
            }

            $tokens  = $this->build_tokens( $event, $context, $attendees );
            $msg_raw = tta_expand_anchor_tokens( $tpl['sms_text'], $tokens );
            $message = tta_strip_bold( strtr( $msg_raw, $tokens ) );

            $numbers = [];
            $member_phone = $context['member']['phone'] ?? '';
            if ( $member_phone ) {
                $numbers[] = $member_phone;
            }
            foreach ( $attendees as $a ) {
                $phone = sanitize_text_field( $a['phone'] ?? '' );
                if ( $phone && ! in_array( $phone, $numbers, true ) ) {
                    $numbers[] = $phone;
                }
            }

            foreach ( $numbers as $num ) {
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
        $attendees = tta_get_transaction_event_attendees( $transaction['transaction_id'], $refund['event_id'] );

        $tokens  = $this->build_tokens( $event, $context, $attendees, $refund );
        $msg_raw = tta_expand_anchor_tokens( $tpl['sms_text'], $tokens );
        $message = tta_strip_bold( strtr( $msg_raw, $tokens ) );

        $numbers = array_unique( array_merge( [ $context['member']['phone'] ?? '' ], array_column( $attendees, 'phone' ) ) );
        foreach ( $numbers as $num ) {
            $num = sanitize_text_field( $num );
            if ( $num ) {
                $this->send_sms( $num, $message );
            }
        }
    }

    public function send_waitlist_text( array $entry, array $event ) {
        $templates = tta_get_comm_templates();
        if ( empty( $templates['waitlist_available']['sms_text'] ) ) {
            return;
        }
        $tpl = $templates['waitlist_available'];

        $tokens = [
            '{event_name}' => $event['name'] ?? '',
            '{event_link}' => get_permalink( intval( $event['page_id'] ) ),
            '{first_name}' => $entry['first_name'] ?? '',
        ];

        $msg_raw = tta_expand_anchor_tokens( $tpl['sms_text'], $tokens );
        $message = tta_strip_bold( strtr( $msg_raw, $tokens ) );
        $phone   = sanitize_text_field( $entry['phone'] ?? '' );
        if ( $phone ) {
            $this->send_sms( $phone, $message );
        }
    }
}
