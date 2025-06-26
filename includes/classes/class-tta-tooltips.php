<?php
/**
 * Centralized tooltip text manager.
 *
 * Provides tooltip copy across the plugin via a single map of key => text.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Tooltips {

    /**
     * Return associative array of tooltip texts keyed by identifier.
     *
     * @return array
     */
    public static function get_texts() {
        return [
            'extra_link' => __( "Extra event link such as links to this venue's Facebook, Instagram, Yelp, Tripadvisor, etc.", 'tta' ),
        ];
    }

    /**
     * Get tooltip text by key.
     *
     * @param string $key Identifier for the tooltip.
     * @return string Tooltip text or empty string.
     */
    public static function get( $key ) {
        $texts = self::get_texts();
        return isset( $texts[ $key ] ) ? $texts[ $key ] : '';
    }
}

