<?php
/**
 * CPT single content
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<article <?php wpex_cpt_single_content_class(); ?><?php wpex_schema_markup( 'entry_content' ); ?>><?php the_content(); ?></article>