<?php
/**
 * Staff single content
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<article id="staff-single-content" <?php wpex_staff_single_content_class(); ?><?php wpex_schema_markup( 'entry_content' ); ?>><?php

	the_content();

?></article>

<?php
// Page links (for the <!-nextpage-> tag)
wpex_get_template_part( 'link_pages' ); ?>