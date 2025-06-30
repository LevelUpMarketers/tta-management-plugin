<?php
/**
 * Staff single social profile links
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

echo wpex_get_staff_social( apply_filters( 'wpex_staff_single_social_settings', array(
	'before' => '<div id="staff-single-social" class="wpex-mb-40">',
	'after'  => '</div>',
) ) );