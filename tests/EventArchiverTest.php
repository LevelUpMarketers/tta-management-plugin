<?php
use PHPUnit\Framework\TestCase;
if (!defined('ABSPATH')) { define('ABSPATH', sys_get_temp_dir().'/wp/'); }

class EventArchiverTest extends TestCase {
    protected function setUp(): void {
        if (!function_exists('wp_next_scheduled')) { function wp_next_scheduled($h){ return false; } }
        if (!function_exists('wp_schedule_event')) { function wp_schedule_event($t,$rec,$hook){ $GLOBALS['scheduled'][] = [$t,$rec,$hook]; } }
        if (!function_exists('wp_clear_scheduled_hook')) {
            function wp_clear_scheduled_hook( $hook, $args = [] ) {
                $GLOBALS['cleared'][] = $hook;
                if ( isset( $GLOBALS['scheduled'] ) ) {
                    foreach ( $GLOBALS['scheduled'] as $i => $event ) {
                        if ( $event[1] === $hook && $event[2] === $args ) {
                            unset( $GLOBALS['scheduled'][ $i ] );
                        }
                    }
                    $GLOBALS['scheduled'] = array_values( $GLOBALS['scheduled'] );
                }
            }
        }
        require_once __DIR__ . '/../includes/classes/class-tta-event-archiver.php';
    }

    protected function tearDown(): void {
        $GLOBALS['scheduled'] = [];
        $GLOBALS['cleared'] = [];
    }

    public function test_schedule_and_clear_events() {
        TTA_Event_Archiver::schedule_event();
        $this->assertNotEmpty($GLOBALS['scheduled']);
        TTA_Event_Archiver::clear_event();
        $this->assertContains('tta_event_archive_event', $GLOBALS['cleared']);
    }
}
