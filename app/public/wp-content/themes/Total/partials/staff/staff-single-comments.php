<?php
/**
 * Staff single comments
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! comments_open() ) {
	return;
}

?>

<div id="staff-single-comments" <?php wpex_staff_single_comments_class(); ?>><?php

	comments_template();

?></div>