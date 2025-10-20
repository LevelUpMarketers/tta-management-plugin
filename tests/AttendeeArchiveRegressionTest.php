<?php
use PHPUnit\Framework\TestCase;

if ( ! defined( 'ARRAY_A' ) ) { define( 'ARRAY_A', 'ARRAY_A' ); }
if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/../' ); }
if ( ! function_exists( 'sanitize_text_field' ) ) { function sanitize_text_field( $value ) { return is_string( $value ) ? trim( $value ) : $value; } }
if ( ! function_exists( 'sanitize_email' ) ) { function sanitize_email( $value ) { return is_string( $value ) ? strtolower( trim( $value ) ) : $value; } }
if ( ! function_exists( 'add_action' ) ) { function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {} }
if ( ! function_exists( 'add_filter' ) ) { function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {} }
if ( ! function_exists( 'apply_filters' ) ) { function apply_filters( $hook, $value ) { return $value; } }
if ( ! function_exists( '__' ) ) { function __( $text ) { return $text; } }
if ( ! function_exists( 'current_time' ) ) { function current_time( $type ) { return '2024-01-10'; } }
if ( ! function_exists( 'get_userdata' ) ) { function get_userdata( $id ) { return (object) [ 'user_email' => 'buyer@example.com' ]; } }
if ( ! function_exists( 'get_permalink' ) ) { function get_permalink( $id ) { return 'https://example.com/page/' . intval( $id ); } }
if ( ! function_exists( 'wp_json_encode' ) ) { function wp_json_encode( $data ) { return json_encode( $data ); } }
if ( ! class_exists( 'TTA_Cache' ) ) {
    class TTA_Cache {
        public static function get( $key ) { return false; }
        public static function set( $key, $value, $ttl = 0 ) { return true; }
        public static function delete( $key ) { return true; }
        public static function flush() { return true; }
    }
}

class AttendeeArchiveRegressionTest extends TestCase {
    protected function setUp(): void {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $attendees;
            public $attendees_archive;
            public $transactions;
            public $history_rows;
            public $member_id = 77;
            public function __construct() {
                $this->attendees = [
                    [
                        'id'              => 501,
                        'ticket_id'       => 101,
                        'first_name'      => 'Alex',
                        'last_name'       => 'Doe',
                        'email'           => 'alex@example.com',
                        'phone'           => '555-0000',
                        'status'          => 'no_show',
                        'transaction_id'  => 99,
                        'assistance_note' => '',
                        'opt_in_sms'      => 0,
                        'created_at'      => '2023-01-01 12:00:00',
                    ],
                ];
                $this->attendees_archive = [
                    [
                        'id'              => 501,
                        'ticket_id'       => 101,
                        'first_name'      => 'Alex',
                        'last_name'       => 'Doe',
                        'email'           => 'alex@example.com',
                        'phone'           => '555-0000',
                        'status'          => 'no_show',
                        'transaction_id'  => 99,
                        'assistance_note' => '',
                        'opt_in_sms'      => 0,
                        'created_at'      => '2023-01-01 12:00:00',
                    ],
                ];
                $this->transactions = [
                    [
                        'id'             => 99,
                        'transaction_id' => 'TX123',
                        'created_at'     => '2023-01-01 09:00:00',
                        'wpuserid'       => 123,
                        'details'        => json_encode([
                            [
                                'ticket_id'    => 101,
                                'event_ute_id' => 'event-ute',
                                'final_price'  => 25,
                                'quantity'     => 1,
                            ],
                        ]),
                    ],
                ];
                $this->history_rows = [
                    [
                        'action_data' => json_encode([
                            'transaction_id' => 'TX123',
                            'amount'         => 25,
                            'items'          => [
                                [
                                    'ticket_id' => 101,
                                    'quantity'  => 1,
                                    'name'      => 'Ticket',
                                ],
                            ],
                        ]),
                        'event_id'   => 10,
                        'name'       => 'Sample Event',
                        'page_id'    => 44,
                        'mainimageid'=> 55,
                        'date'       => '2023-01-01',
                        'time'       => '10:00',
                        'address'    => '123 Main St',
                        'type'       => 'standard',
                        'refunds'    => 1,
                    ],
                ];
            }
            public function prepare( $query, ...$args ) {
                foreach ( $args as $arg ) {
                    if ( is_array( $arg ) ) {
                        foreach ( $arg as $sub ) {
                            $query = preg_replace( '/%s/', $sub, $query, 1 );
                            $query = preg_replace( '/%d/', (string) (int) $sub, $query, 1 );
                        }
                        continue;
                    }
                    $query = preg_replace( '/%s/', $arg, $query, 1 );
                    $query = preg_replace( '/%d/', (string) (int) $arg, $query, 1 );
                }
                return $query;
            }
            public function get_results( $query, $output = ARRAY_A ) {
                if ( false !== strpos( $query, 'FROM wp_tta_memberhistory' ) && false !== strpos( $query, "action_type = 'purchase'" ) ) {
                    return $this->history_rows;
                }
                if ( false !== strpos( $query, 'SELECT id, transaction_id FROM wp_tta_transactions WHERE transaction_id IN' ) ) {
                    return [ [ 'id' => 99, 'transaction_id' => 'TX123' ] ];
                }
                if ( false !== strpos( $query, 'SELECT transaction_id, COUNT(*) AS cnt FROM wp_tta_attendees WHERE transaction_id IN' ) ) {
                    return $this->count_by_transaction( $this->attendees );
                }
                if ( false !== strpos( $query, 'SELECT transaction_id, COUNT(*) AS cnt FROM wp_tta_attendees_archive WHERE transaction_id IN' ) ) {
                    return $this->count_by_transaction( $this->attendees_archive );
                }
                if ( false !== strpos( $query, 'SELECT * FROM wp_tta_attendees WHERE ticket_id' ) && false !== strpos( $query, 'wp_tta_attendees_archive' ) ) {
                    return array_merge( $this->attendees, $this->attendees_archive );
                }
                if ( false !== strpos( $query, 'SELECT id, transaction_id, created_at, wpuserid, details FROM wp_tta_transactions WHERE id IN' ) ) {
                    return $this->transactions;
                }
                if ( false !== strpos( $query, 'FROM wp_tta_memberhistory' ) && false !== strpos( $query, "action_type = 'refund'" ) ) {
                    return [];
                }
                if ( false !== strpos( $query, 'FROM wp_tta_attendees_archive WHERE ticket_id' ) ) {
                    return $this->attendees_archive;
                }
                if ( false !== strpos( $query, 'FROM wp_tta_attendees WHERE ticket_id' ) ) {
                    return $this->attendees;
                }
                return [];
            }
            public function get_var( $query ) {
                if ( false !== strpos( $query, 'FROM wp_tta_members WHERE wpuserid' ) ) {
                    return $this->member_id;
                }
                if ( false !== strpos( $query, 'COUNT(*) FROM (' ) && false !== strpos( $query, 'wp_tta_attendees WHERE' ) ) {
                    $unique = [];
                    foreach ( $this->attendees as $row ) {
                        if ( 'no_show' === $row['status'] ) {
                            $unique[ $row['id'] ] = true;
                        }
                    }
                    foreach ( $this->attendees_archive as $row ) {
                        if ( 'no_show' === $row['status'] ) {
                            $unique[ $row['id'] ] = true;
                        }
                    }
                    return count( $unique );
                }
                if ( false !== strpos( $query, 'SELECT no_show_offset' ) ) {
                    return 0;
                }
                return 0;
            }
            public function count_by_transaction( array $rows ) {
                $map = [];
                foreach ( $rows as $row ) {
                    $tid = (int) ( $row['transaction_id'] ?? 0 );
                    if ( ! $tid ) {
                        continue;
                    }
                    if ( ! isset( $map[ $tid ] ) ) {
                        $map[ $tid ] = 0;
                    }
                    $map[ $tid ]++;
                }
                $out = [];
                foreach ( $map as $tid => $count ) {
                    $out[] = [ 'transaction_id' => $tid, 'cnt' => $count ];
                }
                return $out;
            }
        };
        require_once __DIR__ . '/../includes/helpers.php';
    }

    public function test_member_history_ignores_duplicate_attendees(): void {
        $events = tta_get_member_past_events( 123 );
        $this->assertCount( 1, $events );
        $this->assertNotEmpty( $events[0]['items'] );
        $this->assertSame( 1, count( $events[0]['items'][0]['attendees'] ) );

        $count = tta_get_no_show_event_count_by_email( 'alex@example.com' );
        $this->assertSame( 1, $count );
    }

    public function test_member_past_events_include_archive_counts(): void {
        global $wpdb;
        $wpdb->attendees = [];

        $events = tta_get_member_past_events( 123 );

        $this->assertCount( 1, $events );
        $this->assertSame( 'Sample Event', $events[0]['name'] );
        $this->assertNotEmpty( $events[0]['items'] );
    }
}
