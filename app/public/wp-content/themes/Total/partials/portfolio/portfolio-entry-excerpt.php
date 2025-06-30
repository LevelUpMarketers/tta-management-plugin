<?php
/**
 * Portfolio entry excerpt
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$excerpt_length = wpex_portfolio_entry_excerpt_length();

if ( '0' == $excerpt_length ) {
	return;
}

?>

<div <?php wpex_portfolio_entry_excerpt_class(); ?>><?php

	wpex_excerpt( array(
		'length'   => $excerpt_length,
		'readmore' => false,
	) );

?></div>