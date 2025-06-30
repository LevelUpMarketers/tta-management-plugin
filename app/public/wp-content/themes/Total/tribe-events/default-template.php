<?php
/**
 * Default Page Template for "The Events Calendar Plugin"
 * Must keep file so all hooks are properly included
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 * @todo does this need Elementor theme builder checks?
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

	<div id="content-wrap" class="container wpex-clr">

		<?php wpex_hook_primary_before(); ?>

		<div id="primary" class="content-area wpex-clr">

			<?php wpex_hook_content_before(); ?>

			<div id="content" class="site-content wpex-clr">

				<?php wpex_hook_content_top(); ?>

				<div id="tribe-events-pg-template">
					<?php tribe_events_before_html(); ?>
					<?php tribe_get_view(); ?>
					<?php tribe_events_after_html(); ?>
				</div>

				<?php wpex_hook_content_bottom(); ?>

			</div>

			<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>