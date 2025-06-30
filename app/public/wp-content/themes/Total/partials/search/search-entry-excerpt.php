<?php
/**
 * Search entry excerpt
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div <?php wpex_search_entry_excerpt_class(); ?>><?php

	// Display excerpt up to the "more" tag if enabled
	if ( apply_filters( 'wpex_check_more_tag', false ) && strpos( get_the_content(), 'more-link' ) ) :

		the_content( '', '&hellip;' );

	// Otherwise display custom excerpt
	else :

		wpex_excerpt( apply_filters( 'wpex_search_entry_excerpt_args', array(
			'length'   => wpex_search_entry_excerpt_length(),
			'readmore' => false,
		) ) );

	endif;

?></div>