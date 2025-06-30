<?php
/**
 * Disable the VC template library
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Admin only functions @todo remove check and load file only as needed
if ( ! is_admin() ) {
	return;
}

// Disable (hide) Template Library
function wpex_disable_vc_template_library( $data ) {
	foreach( $data as $key => $val ) {
		if ( isset( $val['category'] ) && 'shared_templates' == $val['category'] ) {
			unset( $data[$key] );
		}
	}
	return $data;
}
add_filter( 'vc_get_all_templates', 'wpex_disable_vc_template_library', 99 );