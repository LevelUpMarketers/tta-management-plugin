<?php
use PHPUnit\Framework\TestCase;

class DbSetupTest extends TestCase {
    private $captured = [];

    protected function setUp(): void {
        // Minimal WP constants and globals
        if (!defined('ABSPATH')) {
            $tmp = sys_get_temp_dir() . '/wp/';
            define('ABSPATH', $tmp);
            @mkdir(ABSPATH . 'wp-admin/includes', 0777, true);
            file_put_contents(ABSPATH . 'wp-admin/includes/upgrade.php', "<?php\n" );
        }
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public function get_charset_collate() {
                return 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            }
        };
        $GLOBALS['captured_sql'] = [];
        if (!function_exists('dbDelta')) {
            function dbDelta($sql) { $GLOBALS['captured_sql'][] = $sql; }
        }
        if (!function_exists('register_activation_hook')) {
            function register_activation_hook($file, $callback) {}
        }
        require_once __DIR__ . '/../includes/class-db-setup.php';
        \TTA_DB_Setup::install();
        $this->captured = $GLOBALS['captured_sql'];
    }

    public function test_indexes_added() {
        $sql = implode("\n", $this->captured);
        $this->assertStringContainsString('KEY page_id_idx (page_id)', $sql);
        $this->assertStringContainsString('KEY date_idx (date)', $sql);
        $this->assertStringContainsString('KEY expires_at_idx (expires_at)', $sql);
        $this->assertStringContainsString('KEY name_idx (last_name, first_name)', $sql);
    }
}
