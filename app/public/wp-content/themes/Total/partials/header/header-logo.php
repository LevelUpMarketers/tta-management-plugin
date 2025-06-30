<?php
/**
 * Header Logo
 *
 * The default elements and hooks for the header logo
 * @see partials/header/header-logo-inner.php for the actual logo output.
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="site-logo" class="<?php echo esc_attr( wpex_header_logo_classes() ); ?>">
	<div id="site-logo-inner" class="wpex-clr"><?php

		/**
		 * Hook: wpex_hook_site_logo_inner.
		 *
		 * @hooked wpex_header_logo_inner - 10
		 */
		wpex_hook_site_logo_inner();

	?></div>

</div>