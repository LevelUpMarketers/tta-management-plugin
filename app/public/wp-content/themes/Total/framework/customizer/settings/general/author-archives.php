<?php
/**
 * Author Aarchives
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_author_archives'] = array(
	'title' => esc_html__( 'Author Archives', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'label_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Label Color', 'total' ),
			),
			'inline_css' => array(
				'target' => 'label,#comments #commentform label',
				'alter' => 'color',
			),
		),
	),
);
