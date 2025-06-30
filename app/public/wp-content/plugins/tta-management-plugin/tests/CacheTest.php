<?php
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase {
    private $calls;

    protected function setUp(): void {
        // Minimal WP constant and globals
        if (!defined('ABSPATH')) {
            $tmp = sys_get_temp_dir() . '/wp/';
            define('ABSPATH', $tmp);
        }
        @mkdir(ABSPATH . 'wp-admin/includes', 0777, true);
        if (!file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            file_put_contents(ABSPATH . 'wp-admin/includes/upgrade.php', "<?php\n");
        }

        // Set up stub transients array
        $GLOBALS['transients'] = [];
        $this->calls = 0;

        if (!function_exists('get_transient')) {
            function get_transient($key) {
                return $GLOBALS['transients'][$key] ?? false;
            }
        }
        if (!function_exists('set_transient')) {
            function set_transient($key, $value, $ttl = 0) {
                $GLOBALS['transients'][$key] = $value;
                return true;
            }
        }
        if (!function_exists('delete_transient')) {
            function delete_transient($key) {
                unset($GLOBALS['transients'][$key]);
                return true;
            }
        }
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
    }

    public function test_set_get_delete() {
        TTA_Cache::set('foo', 'bar');
        $this->assertSame('bar', TTA_Cache::get('foo'));
        TTA_Cache::delete('foo');
        $this->assertFalse(TTA_Cache::get('foo'));
    }

    public function test_remember_calls_once() {
        $value = TTA_Cache::remember('key', function() {
            $GLOBALS['callback_calls'] = ($GLOBALS['callback_calls'] ?? 0) + 1;
            return 'val';
        }, 0);
        $this->assertSame('val', $value);
        $value2 = TTA_Cache::remember('key', function() { return 'new'; }, 0);
        $this->assertSame('val', $value2);
        $this->assertSame(1, $GLOBALS['callback_calls']);
    }

    public function test_flush_deletes_cached_transients() {
        global $wpdb;
        $wpdb = new class {
            public $options = 'wp_options';
            public $prepared = [];
            public function esc_like($str) { return $str; }
            public function prepare($query, $param) {
                $this->prepared[] = sprintf($query, $param);
                return end($this->prepared);
            }
            public function get_col($query) {
                return ['_transient_tta_cache_a', '_transient_tta_cache_b'];
            }
        };

        $GLOBALS['transients']['tta_cache_a'] = 'x';
        $GLOBALS['transients']['tta_cache_b'] = 'y';

        TTA_Cache::flush();

        $this->assertFalse( isset( $GLOBALS['transients']['tta_cache_a'] ) );
        $this->assertFalse( isset( $GLOBALS['transients']['tta_cache_b'] ) );
        $this->assertNotEmpty( $wpdb->prepared );
    }
}
