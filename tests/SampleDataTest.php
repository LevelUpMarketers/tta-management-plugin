<?php
use PHPUnit\Framework\TestCase;

class DummyWpdbSample {
    public $prefix = 'wp_';
    public $events = [];
    public $tickets = [];

    public function esc_like( $str ) { return $str; }
    public function get_col( $query ) { return []; }
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
        } else {
            $this->tickets[] = $data;
        }
        return true;
    }
}

class SampleDataTest extends TestCase {
    protected function setUp(): void {
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', sys_get_temp_dir() . '/wp/' );
        }
        if ( ! defined( 'TTA_PLUGIN_DIR' ) ) {
            define( 'TTA_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
        }
        if ( ! function_exists( 'sanitize_text_field' ) ) {
            function sanitize_text_field( $v ) { return $v; }
        }
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        require_once __DIR__ . '/../includes/database-testing/class-tta-sample-data.php';
    }

    public function test_load_inserts_rows() {
        global $wpdb;
        $wpdb = new DummyWpdbSample();
        TTA_Sample_Data::load();
        $this->assertCount( 20, $wpdb->events );
        $this->assertCount( 20, $wpdb->tickets );
    }
}
