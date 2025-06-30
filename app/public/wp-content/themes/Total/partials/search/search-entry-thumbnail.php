<?php
/**
 * Search entry thumbnail
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="search-entry-thumb wpex-flex-shrink-0 wpex-mr-20">
	<a href="<?php wpex_permalink(); ?>" title="<?php wpex_esc_title(); ?>" class="search-entry-img-link"><?php

		// Display thumbnail
		wpex_post_thumbnail( apply_filters( 'wpex_search_thumbnail_args', array(
			'size'  => 'search_results',
			'alt'   => wpex_get_esc_title(),
			'class' => 'wpex-align-middle',
		) ) );

	?></a>
</div>