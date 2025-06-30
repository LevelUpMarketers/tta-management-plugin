<?php
/**
 * Blog single post link format media.
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<a href="<?php wpex_permalink(); ?>" title="<?php wpex_esc_title(); ?>"><?php echo wpex_get_blog_post_thumbnail(); ?></a>

<?php wpex_blog_single_thumbnail_caption(); ?>