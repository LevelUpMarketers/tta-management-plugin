<?php
/**
 * The next and previous post links.
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

// Get current post type
$post_type = get_post_type();

// Check if post has terms if so then show next/prev from the same_cat
if ( get_theme_mod( 'next_prev_in_same_term', true  ) ) {
	$has_terms = wpex_post_has_terms( get_the_ID() );
	$same_cat  = $has_terms;
} else {
	$same_cat = false;
}
$same_cat = apply_filters( 'wpex_next_prev_in_same_term', $same_cat, $post_type );

$has_terms = $same_cat ? $has_terms : false; // Added check for filter

// Get taxonomy for same_term filter
if ( $same_cat ) {
	$taxonomy = wpex_get_post_type_cat_tax();
	$taxonomy = apply_filters( 'wpex_next_prev_same_cat_taxonomy', $taxonomy, $post_type );
} else {
	$taxonomy = 'category';
}

// Exclude terms
$excluded_terms = apply_filters( 'wpex_next_prev_excluded_terms', null, $post_type );

// Check if order is set to reverse
$reverse_order = apply_filters( 'wpex_nex_prev_reverse', get_theme_mod( 'next_prev_reverse_order', false ), $post_type );

// Texts
$prev_text = ( $prev_text = get_theme_mod( 'next_prev_prev_text' ) ) ? esc_html( $prev_text ) : '%title';
$next_text = ( $next_text = get_theme_mod( 'next_prev_next_text' ) ) ? esc_html( $next_text ) : '%title';

// Previous post link title
$left_dir = is_rtl() ? 'right' : 'left';
$prev_post_link_title = '<span class="ticon ticon-angle-double-'. $left_dir .' wpex-mr-10" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'previous post', 'total' ) . ': </span>' . $prev_text;
$prev_post_link_title = apply_filters( 'wpex_prev_post_link_title', $prev_post_link_title, $post_type );

// Next post link title
$right_dir = is_rtl() ? 'left' : 'right';
$next_post_link_title = '<span class="screen-reader-text">' . esc_html__( 'next post', 'total' ) . ': </span>' . $next_text . '<span class="ticon ticon-angle-double-'. $right_dir . ' wpex-ml-10" aria-hidden="true"></span>';
$next_post_link_title = apply_filters( 'wpex_next_post_link_title', $next_post_link_title, $post_type );

// Reverse titles
if ( $reverse_order ) {
	$prev_post_link_title_tmp = $prev_post_link_title;
	$next_post_link_title_tmp = $next_post_link_title;
	$prev_post_link_title     = $next_post_link_title_tmp;
	$next_post_link_title     = $prev_post_link_title_tmp;
}

// Get post links
if ( $has_terms || wpex_is_post_in_series() ) {
	$prev_link = get_previous_post_link( '%link', $prev_post_link_title, $same_cat, $excluded_terms, $taxonomy );
	$next_link = get_next_post_link( '%link', $next_post_link_title, $same_cat, $excluded_terms, $taxonomy );
} else {
	$prev_link = get_previous_post_link( '%link', $prev_post_link_title, false );
	$next_link = get_next_post_link( '%link', $next_post_link_title, false );
}

// Display next and previous links
if ( $prev_link || $next_link ) : ?>

	<div class="post-pagination-wrap wpex-py-20 wpex-border-solid wpex-border-t wpex-border-main">

		<ul class="post-pagination container wpex-flex wpex-justify-between wpex-list-none"><?php

			if ( $reverse_order ) {
				echo '<li class="post-prev wpex-flex-grow wpex-ml-10">' . $next_link . '</li>';
				echo '<li class="post-next wpex-flex-grow wpex-mr-10 wpex-text-right">' . $prev_link . '</li>';
			} else {
				echo '<li class="post-prev wpex-flex-grow wpex-mr-10">' . $prev_link . '</li>';
				echo '<li class="post-next wpex-flex-grow wpex-ml-10 wpex-text-right">' . $next_link . '</li>';
			}

		?></ul>

	</div>

<?php endif; ?>