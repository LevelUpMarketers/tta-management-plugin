<?php
/**
 * Social Share Buttons Output
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Return if social share is disabled
if ( ! wpex_has_social_share() ) {
	return;
}

if ( ! wpex_theme_do_location( 'social_share' ) ) :

	// Custom social share shortcode
	if ( $custom_share = wpex_custom_social_share() ) : ?>

		<div <?php wpex_social_share_class(); ?>>
			<?php echo do_shortcode( wp_kses_post( $custom_share ) ); ?>
		</div>

	<?php
	// Theme Social Share
	elseif ( wpex_has_social_share_sites() ) : ?>

		<div <?php wpex_social_share_class(); ?> <?php wpex_social_share_data(); ?>>

			<?php
			// Display heading for horizontal style social share if enabled
			wpex_social_share_heading(); ?>

			<?php
			// Display social share items
			wpex_social_share_list(); ?>

		</div>

	<?php endif; ?>

<?php endif; ?>