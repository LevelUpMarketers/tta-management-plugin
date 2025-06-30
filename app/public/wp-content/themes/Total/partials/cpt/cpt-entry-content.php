<?php
/**
 * CTP entry content
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0.4
 */

defined( 'ABSPATH' ) || exit;

?>

<div <?php wpex_cpt_entry_excerpt_class(); ?>><?php

	// Display entry content up to the more tag
	if ( true === apply_filters( 'wpex_check_more_tag', true ) && strpos( get_the_content(), 'more-link' ) ) :

		the_content( '', '&hellip;' );

	// Generate custom excerpt
	else :

		wpex_excerpt( array(
			'length' => wpex_cpt_entry_excerpt_length(),
		) );

	endif;

?></div>