<?php
/**
 * Single blog post title
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<header <?php wpex_blog_single_header_class(); ?>>
	<h1 <?php wpex_blog_single_title_class(); ?><?php wpex_schema_markup( 'headline' ); ?>><?php the_title(); ?></h1>
</header>