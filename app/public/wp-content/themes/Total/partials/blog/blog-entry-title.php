<?php
/**
 * Blog entry title
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<header <?php wpex_blog_entry_header_class(); ?>>
	<?php if ( wpex_has_blog_entry_avatar() ) : ?>
		<?php get_template_part( 'partials/blog/blog-entry-avatar' ); ?>
	<?php endif; ?>
	<h2 <?php wpex_blog_entry_title_class(); ?>><a href="<?php wpex_permalink(); ?>"><?php the_title(); ?></a></h2>
</header>