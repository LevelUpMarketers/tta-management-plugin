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

    /*
     * Tooltip identifiers
     */
    const EXTRA_LINK      = 'extra_link';
    const EVENT_NAME      = 'event_name';
    const EVENT_DATE      = 'event_date';
    const EVENT_ALL_DAY   = 'event_all_day';
    const START_TIME      = 'start_time';
    const END_TIME        = 'end_time';
    const VIRTUAL_EVENT   = 'virtual_event';
    const STREET_ADDRESS  = 'street_address';
    const ADDRESS_2       = 'address_2';
    const CITY            = 'city';
    const STATE           = 'state';
    const ZIP             = 'zip';
    const VENUE_NAME      = 'venue_name';
    const VENUE_URL       = 'venue_url';
    const EVENT_TYPE      = 'event_type';
    const BASE_COST       = 'base_cost';
    const BASIC_COST      = 'basic_cost';
    const PREMIUM_COST    = 'premium_cost';
    const WAITLIST        = 'waitlist';
    const REFUNDS         = 'refunds';
    const DISCOUNT_CODE   = 'discount_code';
    const DISCOUNT_TYPE   = 'discount_type';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const HOSTS           = 'hosts';
    const VOLUNTEERS      = 'volunteers';
    const DESCRIPTION     = 'description';
    const MAIN_IMAGE      = 'main_image';
    const GALLERY         = 'gallery';
    const AD_IMAGE        = 'ad_image';
    const AD_URL          = 'ad_url';
    const AD_BUSINESS_NAME = 'ad_business_name';
    const AD_BUSINESS_PHONE = 'ad_business_phone';
    const AD_BUSINESS_ADDRESS = 'ad_business_address';

    /**
     * Return associative array of tooltip texts keyed by identifier.
     *
     * @return array
     */
    public static function get_texts() {
        return [
            self::EXTRA_LINK      => __( "Extra event link such as links to this venue's Facebook, Instagram, Yelp, Tripadvisor, etc.", 'tta' ),
            self::EVENT_NAME      => __( 'Enter the title of the event as it will appear everywhere.', 'tta' ),
            self::EVENT_DATE      => __( 'Choose the calendar date for this event.', 'tta' ),
            self::EVENT_ALL_DAY   => __( 'Check ‘Yes’ if the event spans the entire day.', 'tta' ),
            self::START_TIME      => __( 'Use the time picker to select the event start time.', 'tta' ),
            self::END_TIME        => __( 'Use the time picker to select the event end time.', 'tta' ),
            self::VIRTUAL_EVENT   => __( 'Check ‘Yes’ if this is an online-only event.', 'tta' ),
            self::STREET_ADDRESS  => __( 'Enter the primary street address.', 'tta' ),
            self::ADDRESS_2       => __( 'Apartment, suite, unit, etc.', 'tta' ),
            self::CITY            => __( 'City name.', 'tta' ),
            self::STATE           => __( 'Select the state for this event location.', 'tta' ),
            self::ZIP             => __( 'Postal code.', 'tta' ),
            self::VENUE_NAME      => __( 'The name of the Venue', 'tta' ),
            self::VENUE_URL       => __( 'Link to the venue or event page.', 'tta' ),
            self::EVENT_TYPE      => __( 'Select the membership requirement for this event. Open Events are public. Basic Membership Required means attendees must be logged in with at least a Basic membership. Premium Membership Required limits access to Premium members only.', 'tta' ),
            self::BASE_COST       => __( 'Enter the standard ticket price in USD, with cents.', 'tta' ),
            self::BASIC_COST      => __( 'Enter the basic member discounted price in USD, with cents.', 'tta' ),
            self::PREMIUM_COST    => __( 'Enter the premium member discounted price in USD, with cents.', 'tta' ),
            self::WAITLIST        => __( 'Allow users to join a waitlist when the event is full.', 'tta' ),
            self::REFUNDS         => __( 'Allow users to request a refund for this event.', 'tta' ),
            self::DISCOUNT_CODE   => __( 'Apply a promo code and its discount details.', 'tta' ),
            self::DISCOUNT_TYPE   => __( 'Select whether the discount is a flat amount or percentage.', 'tta' ),
            self::DISCOUNT_AMOUNT => __( 'Numeric amount of the discount. Percentage will use this value as percent.', 'tta' ),
            self::HOSTS           => __( 'Add one or more hosts.', 'tta' ),
            self::VOLUNTEERS      => __( 'Add volunteers assisting with this event.', 'tta' ),
            self::DESCRIPTION     => __( 'Describe the event details shown on the public page.', 'tta' ),
            self::MAIN_IMAGE      => __( 'Select a primary image for this event.', 'tta' ),
            self::GALLERY         => __( 'Choose multiple images for the event gallery.', 'tta' ),
            self::AD_IMAGE        => __( 'Select the image displayed for this advertisement.', 'tta' ),
            self::AD_URL          => __( 'Destination URL when clicking the advertisement.', 'tta' ),
            self::AD_BUSINESS_NAME => __( 'Name of the business being promoted.', 'tta' ),
            self::AD_BUSINESS_PHONE => __( 'Contact phone number for the business.', 'tta' ),
            self::AD_BUSINESS_ADDRESS => __( 'Street or mailing address of the business.', 'tta' ),
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

