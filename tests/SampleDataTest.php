<?php
use PHPUnit\Framework\TestCase;

class DummyWpdbSample {
    public $prefix = 'wp_';
    public $options = 'wp_options';
    public $events = [];
    public $tickets = [];
    public $members = [];
    public $transactions = [];
    public $attendees = [];
    public $insert_id = 0;
    public $last_query = '';
    public $queries = [];

    public function esc_like( $str ) { return $str; }
    public function get_col( $query ) { return []; }
    public function get_var( $query ) { return null; }
    public function prepare( $query, ...$args ) {
        foreach ( $args as $a ) {
            $query = preg_replace('/%s/', $a, $query, 1);
            $query = preg_replace('/%d/', intval($a), $query, 1);
        }
        return $query;
    }

    public function insert( $table, $data ) {
        if ( false !== strpos( $table, 'tta_events' ) ) {
            $this->events[] = $data;
        } elseif ( false !== strpos( $table, 'tta_tickets' ) ) {
            $this->tickets[] = $data;
        } elseif ( false !== strpos( $table, 'tta_members' ) ) {
            $this->members[] = $data;
        } elseif ( false !== strpos( $table, 'tta_transactions' ) ) {
            $this->transactions[] = $data;
        } elseif ( false !== strpos( $table, 'tta_attendees' ) ) {
            $this->attendees[] = $data;
        }
        $this->insert_id++;
        return true;
    }

    public function update( $table, $data, $where ) {
        if ( false !== strpos( $table, 'tta_events' ) && ! empty( $this->events ) ) {
            $this->events[count($this->events)-1] = array_merge($this->events[count($this->events)-1], $data);
        }
        return 1;
    }

    public function query( $sql ) {
        $this->last_query = $sql;
        $this->queries[] = $sql;
        if ( stripos( $sql, 'DELETE' ) !== false ) {
            if ( strpos( $sql, 'tta_events' ) !== false ) {
                $this->events = [];
            } elseif ( strpos( $sql, 'tta_tickets' ) !== false ) {
                $this->tickets = [];
            } elseif ( strpos( $sql, 'tta_members' ) !== false ) {
                $this->members = [];
            } elseif ( strpos( $sql, 'tta_transactions' ) !== false ) {
                $this->transactions = [];
            } elseif ( strpos( $sql, 'tta_attendees' ) !== false ) {
                $this->attendees = [];
            }
        }
        return 1;
    }
    public function delete( $table, $where ) { return 1; }
}

class SampleDataTest extends TestCase {
    protected function setUp(): void {
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', sys_get_temp_dir() . '/wp/' );
        }
        if ( ! defined( 'TTA_PLUGIN_DIR' ) ) {
            define( 'TTA_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
        }
        if ( ! function_exists( 'sanitize_text_field' ) ) { function sanitize_text_field( $v ) { return $v; } }
        if ( ! function_exists( 'sanitize_email' ) ) { function sanitize_email( $v ) { return $v; } }
        if ( ! function_exists( 'tta_waitlist_uses_csv' ) ) { function tta_waitlist_uses_csv() { return false; } }
        if ( ! function_exists( 'wp_json_encode' ) ) { function wp_json_encode($d,$o=0,$depth=512){ return json_encode($d,$o,$depth); } }
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        require_once __DIR__ . '/../includes/database-testing/class-tta-sample-data.php';
    }

    public function test_load_inserts_rows() {
        global $wpdb;
        $wpdb = new DummyWpdbSample();
        TTA_Sample_Data::load();
        $this->assertGreaterThanOrEqual( 20, count( $wpdb->events ) );
        $this->assertGreaterThanOrEqual( 20, count( $wpdb->tickets ) );
        $this->assertGreaterThanOrEqual( 10, count( $wpdb->members ) );
        $this->assertGreaterThanOrEqual( 20, count( $wpdb->transactions ) );
        $this->assertNotEmpty( $wpdb->attendees );
        $this->assertIsInt( $wpdb->events[0]['mainimageid'] );
    }

    public function test_clear_removes_rows() {
        global $wpdb;
        $wpdb = new DummyWpdbSample();
        TTA_Sample_Data::load();
        TTA_Sample_Data::clear();
        $this->assertCount( 0, $wpdb->events );
        $this->assertCount( 0, $wpdb->tickets );
        $this->assertCount( 0, $wpdb->members );
        $this->assertCount( 0, $wpdb->transactions );
    }
}
