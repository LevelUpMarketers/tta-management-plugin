<?php
/**
 * CPT single title
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<header id="post-header" <?php wpex_cpt_single_header_class(); ?>>
	<h1 <?php wpex_cpt_single_title_class(); ?><?php wpex_schema_markup( 'heading' ); ?>><?php the_title(); ?></h1>
</header>