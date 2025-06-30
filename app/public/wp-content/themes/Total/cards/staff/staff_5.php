<?php
/**
 * Card: Staff 5
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Inner
$output .= '<div class="wpex-card-inner wpex-bg-white wpex-p-40 wpex-text-center wpex-rounded wpex-shadow">';

	// Thumbnail
	$output .= $this->get_thumbnail( array(
		'class' => 'wpex-mb-15 wpex-rounded-full',
		'image_class' => 'wpex-rounded-full'
	) );

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-text-lg',
	) );

	// Email
	if ( ! empty( $this->post_id ) ) {
		$email = sanitize_email( get_post_meta( $this->post_id, 'wpex_staff_email', true ) );
		if ( $email ) {
			$output .= $this->get_element( array(
				'content' => $email,
				'link'    => 'mailto:' . $email,
				'class'   => 'wpex-card-staff-member-email wpex-text-gray-500 wpex-mt-15',
			) );
		}
	}

$output .= '</div>';

return $output;