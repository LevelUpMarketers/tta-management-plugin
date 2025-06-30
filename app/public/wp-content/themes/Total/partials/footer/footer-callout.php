<?php
/**
 * Footer callout
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

// Return if disabled
if ( ! wpex_has_footer_callout() ) {
	return;
}

// Return if there isn't any content or button.
if ( ! wpex_has_footer_callout_content() && ! wpex_has_footer_callout_button() ) {
	return;
}

?>

<div id="footer-callout-wrap" <?php wpex_footer_callout_wrap_class(); ?><?php wpex_aria_label( 'footer_callout' ); ?><?php wpex_aria_landmark( 'footer_callout' ); ?>>

	<div id="footer-callout" <?php wpex_footer_callout_class(); ?>>

		<?php
		// Content & Button Callout
		if ( wpex_has_footer_callout_content() ) { ?>

			<div id="footer-callout-left" <?php wpex_footer_callout_left_class(); ?>><?php wpex_footer_callout_content(); ?></div>

			<?php
			// Display the Button
			if ( wpex_has_footer_callout_button() ) { ?>

				<div id="footer-callout-right" <?php wpex_footer_callout_right_class(); ?>><?php wpex_footer_callout_button(); ?></div>

			<?php } ?>

		<?php
		}

		// Button Only Callout
		elseif ( wpex_has_footer_callout_button() ) { ?>

			<?php wpex_footer_callout_button(); ?>

		<?php } ?>

	</div>

</div>