<?php
/**
 * Edit post link
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

edit_post_link(
    null, // use wp default
    '<div class="post-edit wpex-my-40">', ' <a href="#" class="hide-post-edit" title="'. esc_html__( 'Hide Post Edit Links', 'total' ) .'" aria-hidden="true"><span class="ticon ticon-times"></span></a></div>'
);