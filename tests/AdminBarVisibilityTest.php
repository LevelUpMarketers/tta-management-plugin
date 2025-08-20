<?php
use PHPUnit\Framework\TestCase;

class AdminBarVisibilityTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['filters'] = [];
        $GLOBALS['actions'] = [];
        $GLOBALS['is_logged_in'] = false;
        $GLOBALS['can_manage_options'] = false;
        if ( ! function_exists( 'is_user_logged_in' ) ) {
            function is_user_logged_in() {
                return $GLOBALS['is_logged_in'];
            }
        }
        if ( ! function_exists( 'current_user_can' ) ) {
            function current_user_can( $cap ) {
                return $GLOBALS['can_manage_options'];
            }
        }
        if ( ! function_exists( 'add_filter' ) ) {
            function add_filter( $tag, $func, $priority = 10, $accepted_args = 1 ) {
                $GLOBALS['filters'][ $tag ] = $func;
            }
        }
        if ( ! function_exists( 'apply_filters' ) ) {
            function apply_filters( $tag, $value ) {
                if ( isset( $GLOBALS['filters'][ $tag ] ) ) {
                    $fn = $GLOBALS['filters'][ $tag ];
                    return $fn( $value );
                }
                return $value;
            }
        }
        if ( ! function_exists( 'add_action' ) ) {
            function add_action( $tag, $func, $priority = 10, $accepted_args = 1 ) {
                $GLOBALS['actions'][ $tag ][] = $func;
            }
        }
        if ( ! function_exists( '__return_false' ) ) {
            function __return_false() {
                return false;
            }
        }
        require_once __DIR__ . '/../includes/admin-bar.php';
    }

    public function test_hides_admin_bar_for_non_admins() {
        $GLOBALS['is_logged_in'] = true;
        $GLOBALS['can_manage_options'] = false;
        tta_maybe_hide_admin_bar();
        $this->assertFalse( apply_filters( 'show_admin_bar', true ) );
        $this->assertArrayHasKey( 'wp_print_styles', $GLOBALS['actions'] );
    }

    public function test_shows_admin_bar_for_admins() {
        $GLOBALS['is_logged_in'] = true;
        $GLOBALS['can_manage_options'] = true;
        tta_maybe_hide_admin_bar();
        $this->assertTrue( apply_filters( 'show_admin_bar', true ) );
        $this->assertArrayNotHasKey( 'wp_print_styles', $GLOBALS['actions'] );
    }
}
?>
