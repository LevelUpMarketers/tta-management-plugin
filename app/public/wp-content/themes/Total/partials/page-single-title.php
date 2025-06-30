<?php
/**
 * Page Single Header/Title
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 4.0
 */

defined( 'ABSPATH' ) || exit;

?>

<header <?php wpex_page_single_header_class(); ?>>
	<h1 <?php wpex_page_single_title_class(); ?><?php wpex_schema_markup( 'heading' ); ?>><?php the_title(); ?></h1>
</header>