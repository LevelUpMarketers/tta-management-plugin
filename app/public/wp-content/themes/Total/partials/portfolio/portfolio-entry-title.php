<?php
/**
 * Portfolio entry title
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<h2 <?php wpex_portfolio_entry_title_class(); ?>><a href="<?php wpex_permalink(); ?>"><?php the_title(); ?></a></h2>