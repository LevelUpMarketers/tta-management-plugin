<?php
/**
 * Blog entry quote
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<article id="post-<?php the_ID(); ?>" <?php wpex_blog_entry_class(); ?>>

	<?php if ( ! wpex_blog_entry_card() ) { ?>

		<div <?php wpex_blog_entry_quote_class(); ?>>
			<span <?php wpex_blog_entry_quote_icon_class(); ?> aria-hidden="true"></span>
			<div class="quote-entry-content wpex-last-mb-0 wpex-clr"><?php the_content(); ?></div>
			<div class="quote-entry-author wpex-text-sm wpex-not-italic wpex-mt-20 wpex-style-none"><span>-</span> <?php the_title(); ?></div>
		</div>

		<?php if ( ! is_singular( 'post' ) ) { ?>
			<?php wpex_blog_entry_divider();  ?>
		<?php } ?>

	<?php } ?>

</article>