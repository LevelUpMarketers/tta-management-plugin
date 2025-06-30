<?php
/**
 * Togglebar output
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="toggle-bar-wrap" <?php wpex_togglebar_class(); ?>>

	<?php if ( ! wpex_theme_do_location( 'togglebar' ) ) : ?>

		<div id="toggle-bar" class="container wpex-clr"><?php

			wpex_get_template_part( 'togglebar_content' );

		?></div>

	<?php endif;?>

</div>