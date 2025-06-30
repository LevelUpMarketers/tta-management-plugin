<?php
/**
 * Skip To Content
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get correct content ID
$id = ( $id = esc_attr( get_theme_mod( 'skip_to_content_id' ) ) ) ? $id : 'content';

?>

<a href="#<?php echo str_replace( '#', '', $id ); ?>" class="skip-to-content"<?php wpex_aria_landmark( 'skip_to_content' ); ?>><?php echo esc_html__( 'skip to Main Content', 'total' ); ?></a>