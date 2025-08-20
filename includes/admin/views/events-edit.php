<?php
/**
 * File: includes/admin/views/events-edit.php
 *
 * This partial is used for inline‐editing a single Event. It’s 
 * fetched via AJAX (wp_ajax_tta_get_event_form) and injected 
 * immediately below the row you clicked.
 */

global $wpdb;
$is_archive = ! empty( $_GET['archive'] );
$table      = $wpdb->prefix . ( $is_archive ? 'tta_events_archive' : 'tta_events' );
$venue_table = $wpdb->prefix . 'tta_venues';
$readonly   = $is_archive;
$editing    = false;
$event      = [];

// Load existing event data (using the event_id we passed via AJAX)
if ( isset( $_GET['event_id'] ) ) {
    $event_id = intval( $_GET['event_id'] );
    $event    = $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $event_id ),
        ARRAY_A
    );
    if ( $event ) {
        $editing = true;
        $disc               = tta_parse_discount_data( $event['discountcode'] );
        $event['discountcode']   = $disc['code'];
        $event['discount_type']  = $disc['type'];
        $event['discount_amount'] = $disc['amount'];
    }
}
?>

<?php
$member_choices = $wpdb->get_col(
    "SELECT CONCAT(first_name,' ',last_name) FROM {$wpdb->prefix}tta_members WHERE member_type IN ('volunteer','admin','super_admin') ORDER BY first_name, last_name"
);
$venue_choices = $wpdb->get_results( "SELECT name, address, venueurl, url2, url3, url4 FROM {$venue_table} ORDER BY name", ARRAY_A );
$hosts      = ! empty( $event['hosts'] ) ? tta_get_member_names_by_ids( explode( ',', $event['hosts'] ) ) : [''];
$volunteers = ! empty( $event['volunteers'] ) ? tta_get_member_names_by_ids( explode( ',', $event['volunteers'] ) ) : [''];
?>

<form method="post" id="tta-event-edit-form">
    <?php if ( ! $readonly ) : ?>
        <?php wp_nonce_field( 'tta_event_save_action', 'tta_event_save_nonce' ); ?>
    <?php endif; ?>
    <?php if ( $editing ) : ?>
        <input type="hidden" name="tta_event_id" value="<?php echo esc_attr( $event['id'] ); ?>">
    <?php endif; ?>

    <!-- Always output waitlist_id for AJAX updates -->
    <input type="hidden" name="waitlist_id" id="waitlist_id"
           value="<?php echo esc_attr( $event['waitlist_id'] ?? 0 ); ?>">

    <?php if ( $readonly ) : ?>
        <p><em><?php esc_html_e( 'This archived event is read-only.', 'tta' ); ?></em></p>
    <?php endif; ?>

    <fieldset <?php echo $readonly ? 'disabled' : ''; ?>>

    <table class="form-table">
        <tbody>
            <!-- Event Name -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EVENT_NAME ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="name">Event Name</label>
                </th>
                <td>
                    <input type="text" name="name" id="name" class="regular-text"
                           value="<?php echo esc_attr( $event['name'] ?? '' ); ?>">
                </td>
            </tr>


            <!-- Date -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EVENT_DATE ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="date">Date</label>
                </th>
                <td>
                    <input type="date" name="date" id="date"
                           value="<?php echo esc_attr( $event['date'] ?? '' ); ?>">
                </td>
            </tr>

            <!-- All-day Event? -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EVENT_ALL_DAY ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="all_day_event">All-day Event?</label>
                </th>
                <td>
                    <select name="all_day_event" id="all_day_event">
                        <option value="0" <?php selected( $event['all_day_event'] ?? '0', '0' ); ?>>No</option>
                        <option value="1" <?php selected( $event['all_day_event'] ?? '0', '1' ); ?>>Yes</option>
                    </select>
                </td>
            </tr>

            <!-- Start Time -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::START_TIME ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="start_time">Start Time</label>
                </th>
                <td>
                    <input type="time" name="start_time" id="start_time" class="regular-text"
                           value="<?php echo esc_attr( explode( '|', $event['time'] ?? '|' )[0] ?? '' ); ?>">
                </td>
            </tr>

            <!-- End Time -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::END_TIME ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="end_time">End Time</label>
                </th>
                <td>
                    <input type="time" name="end_time" id="end_time" class="regular-text"
                           value="<?php echo esc_attr( explode( '|', $event['time'] ?? '|' )[1] ?? '' ); ?>">
                </td>
            </tr>

            <!-- Virtual Event? -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::VIRTUAL_EVENT ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="virtual_event">Virtual Event?</label>
                </th>
                <td>
                    <select name="virtual_event" id="virtual_event">
                        <option value="0" <?php selected( $event['virtual_event'] ?? '0', '0' ); ?>>No</option>
                        <option value="1" <?php selected( $event['virtual_event'] ?? '0', '1' ); ?>>Yes</option>
                    </select>
                </td>
            </tr>

            <?php
            // Split address into pieces
            $parts = isset( $event['address'] ) ? explode( ' - ', $event['address'] ) : [];
            list( $street, $addr2, $city, $state, $zip ) = array_pad( $parts, 5, '' );
            ?>

            <!-- Street Address -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::STREET_ADDRESS ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="street_address">Street Address</label>
                </th>
                <td>
                    <input type="text" name="street_address" id="street_address" class="regular-text"
                           value="<?php echo esc_attr( $street ); ?>">
                </td>
            </tr>

            <!-- Address 2 -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::ADDRESS_2 ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="address_2">Address 2</label>
                </th>
                <td>
                    <input type="text" name="address_2" id="address_2" class="regular-text"
                           value="<?php echo esc_attr( $addr2 ); ?>">
                </td>
            </tr>

            <!-- City -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::CITY ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="city">City</label>
                </th>
                <td>
                    <input type="text" name="city" id="city" class="regular-text"
                           value="<?php echo esc_attr( $city ); ?>">
                </td>
            </tr>

            <!-- State -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::STATE ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="state">State</label>
                </th>
                <td>
                    <?php
                    $all_states = [
                        'Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware',
                        'Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky',
                        'Louisiana','Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi',
                        'Missouri','Montana','Nebraska','Nevada','New Hampshire','New Jersey','New Mexico',
                        'New York','North Carolina','North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania',
                        'Rhode Island','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont',
                        'Virginia','Washington','West Virginia','Wisconsin','Wyoming'
                    ];
                    $states = array_diff( $all_states, [ 'Virginia' ] );
                    sort( $states );
                    array_unshift( $states, 'Virginia' );
                    ?>
                    <select name="state" id="state">
                        <?php foreach ( $states as $st ) : ?>
                            <option value="<?php echo esc_attr( $st ); ?>" <?php selected( $state, $st ); ?>>
                                <?php echo esc_html( $st ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- ZIP -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::ZIP ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="zip">ZIP</label>
                </th>
                <td>
                    <input type="text" name="zip" id="zip" class="regular-text"
                           value="<?php echo esc_attr( $zip ); ?>">
                </td>
            </tr>

            <!-- Venue Name -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::VENUE_NAME ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="venuename">Venue Name</label>
                </th>
                <td>
                    <input type="text" name="venuename" id="venuename" class="regular-text" list="tta-venue-options"
                           value="<?php echo esc_attr( $event['venuename'] ?? '' ); ?>">
                </td>
            </tr>

            <!-- Venue Link -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::VENUE_URL ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="venueurl">Venue Link</label>
                </th>
                <td>
                    <input type="url" name="venueurl" id="venueurl" class="regular-text"
                           value="<?php echo esc_attr( $event['venueurl'] ?? '' ); ?>">
                </td>
            </tr>

            <!-- Event Type -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EVENT_TYPE ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="type">Event Type</label>
                </th>
                <td>
                    <select name="type" id="type">
                        <?php
                        $types = [
                            'free'       => 'Open Event',
                            'paid'       => 'Basic Membership Required',
                            'memberonly' => 'Premium Membership Required',
                        ];
                        foreach ( $types as $val => $lbl ) {
                            printf(
                                '<option value="%1$s"%2$s>%3$s</option>',
                                esc_attr( $val ),
                                selected( $event['type'] ?? '', $val, false ),
                                esc_html( $lbl )
                            );
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <!-- Base Cost -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::BASE_COST ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="baseeventcost">Base Cost</label>
                </th>
                <td>
                    <input type="number" name="baseeventcost" id="baseeventcost" class="regular-text"
                           step="0.01" min="0"
                           value="<?php echo esc_attr( number_format_i18n( $event['baseeventcost'] ?? 0, 2 ) ); ?>">
                </td>
            </tr>

            <!-- Basic Member Cost -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::BASIC_COST ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="discountedmembercost">Basic Member Cost</label>
                </th>
                <td>
                    <input type="number" name="discountedmembercost" id="discountedmembercost" class="regular-text"
                           step="0.01" min="0"
                           value="<?php echo esc_attr( number_format_i18n( $event['discountedmembercost'] ?? 0, 2 ) ); ?>">
                </td>
            </tr>

            <!-- Premium Member Cost -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::PREMIUM_COST ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="premiummembercost">Premium Member Cost</label>
                </th>
                <td>
                    <input type="number" name="premiummembercost" id="premiummembercost" class="regular-text"
                           step="0.01" min="0"
                           value="<?php echo esc_attr( number_format_i18n( $event['premiummembercost'] ?? 0, 2 ) ); ?>">
                </td>
            </tr>

            <!-- Waitlist Available? -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::WAITLIST ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="waitlistavailable">Waitlist Available?</label>
                </th>
                <td>
                    <select name="waitlistavailable" id="waitlistavailable">
                        <option value="0"<?php selected( $event['waitlistavailable'] ?? 0, 0 ); ?>>No</option>
                        <option value="1"<?php selected( $event['waitlistavailable'] ?? 0, 1 ); ?>>Yes</option>
                    </select>
                </td>
            </tr>

            <!-- Refunds Available? -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::REFUNDS ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="refundsavailable">Refunds Available?</label>
                </th>
                <td>
                    <select name="refundsavailable" id="refundsavailable">
                        <option value="0"<?php selected( $event['refundsavailable'] ?? 0, 0 ); ?>>No</option>
                        <option value="1"<?php selected( $event['refundsavailable'] ?? 0, 1 ); ?>>Yes</option>
                    </select>
                </td>
            </tr>

            <!-- Discount Code -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::DISCOUNT_CODE ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="discountcode">Discount Code</label>
                </th>
                <td>
                    <input type="text" name="discountcode" id="discountcode" class="regular-text"
                           value="<?php echo esc_attr( $event['discountcode'] ?? '' ); ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::DISCOUNT_TYPE ) ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="discount_type">Discount Type</label>
                </th>
                <td>
                    <select name="discount_type" id="discount_type">
                        <option value="flat" <?php selected( $event['discount_type'] ?? 'percent', 'flat' ); ?>>Flat $ Amount Off</option>
                        <option value="percent" <?php selected( $event['discount_type'] ?? 'percent', 'percent' ); ?>>Percentage Off</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::DISCOUNT_AMOUNT ) ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="discount_amount">Discount Amount</label>
                </th>
                <td>
                    <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0"
                           value="<?php echo esc_attr( $event['discount_amount'] ?? 0 ); ?>">
                </td>
            </tr>

            <!-- Extra Event Links -->
            <?php for ( $i = 2; $i <= 4; $i++ ) : ?>
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-ttakey="extra_link" style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                    <label for="url<?php echo $i; ?>">Extra Event Link <?php echo $i - 1; ?></label>
                </th>
                <td>
                    <input type="url" name="url<?php echo $i; ?>" id="url<?php echo $i; ?>" class="regular-text"
                           value="<?php echo esc_attr( $event["url{$i}"] ?? '' ); ?>">
                </td>
            </tr>

<?php endfor; ?>

            <!-- Event Hosts -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::HOSTS ) ); ?>" style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="hosts">Event Hosts</label>
                </th>
                <td>
                    <div id="hosts-container">
                        <?php foreach ( $hosts as $i => $h ) : ?>
                            <div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;">
                                <input type="text" name="hosts[]" class="regular-text host-field" list="tta-member-options" placeholder="Host #<?php echo $i+1; ?>" value="<?php echo esc_attr( $h ); ?>" />
                                <button type="button" class="delete-interest" aria-label="Remove" style="background:none;border:none;cursor:pointer;margin-left:8px;">
                                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/bin.svg' ); ?>" alt="×" style="width:16px;height:16px;" />
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button" id="add-host-edit" style="margin-top:8px;">+ Add Another Host</button>
                </td>
            </tr>

            <!-- Event Volunteers -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::VOLUNTEERS ) ); ?>" style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="volunteers">Event Volunteers</label>
                </th>
            <td>
                <div id="volunteers-container">
                        <?php foreach ( $volunteers as $i => $v ) : ?>
                            <div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;">
                                <input type="text" name="volunteers[]" class="regular-text volunteer-field" list="tta-member-options" placeholder="Volunteer #<?php echo $i+1; ?>" value="<?php echo esc_attr( $v ); ?>" />
                                <button type="button" class="delete-interest" aria-label="Remove" style="background:none;border:none;cursor:pointer;margin-left:8px;">
                                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/bin.svg' ); ?>" alt="×" style="width:16px;height:16px;" />
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
            <button type="button" class="button" id="add-volunteer-edit" style="margin-top:8px;">+ Add Another Volunteer</button>
                </td>
            </tr>

            <!-- Host Notes -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr__( 'Notes about hosts or volunteers (not public)', 'tta' ); ?>" style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="host_notes">Host Notes</label>
                </th>
                <td>
                    <textarea name="host_notes" id="host_notes" rows="4" class="large-text" placeholder="Notes…"><?php echo esc_textarea( $event['host_notes'] ?? '' ); ?></textarea>
                </td>
            </tr

        </tbody>

        <datalist id="tta-member-options">
            <?php foreach ( $member_choices as $name ) : ?>
                <option value="<?php echo esc_attr( $name ); ?>"></option>
            <?php endforeach; ?>
        </datalist>

        <datalist id="tta-venue-options">
            <?php foreach ( $venue_choices as $v ) : ?>
                <option value="<?php echo esc_attr( $v['name'] ); ?>"
                        data-address="<?php echo esc_attr( $v['address'] ); ?>"
                        data-url="<?php echo esc_attr( $v['venueurl'] ); ?>"
                        data-url2="<?php echo esc_attr( $v['url2'] ); ?>"
                        data-url3="<?php echo esc_attr( $v['url3'] ); ?>"
                        data-url4="<?php echo esc_attr( $v['url4'] ); ?>"></option>
            <?php endforeach; ?>
        </datalist>

        <!-- Event Description (TinyMCE) -->
        <tr style="width:100%;">
          <th>
            <h2>
              <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::DESCRIPTION ) ); ?>">
                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
              </span>
              <?php esc_html_e( 'Event Description', 'tta' ); ?>
            </h2>
          </th>
          <td style="width:100vw;">
            <?php
            // Fetch existing description from the auto‐generated page (if editing):
            $description = '';
            if ( ! empty( $event['page_id'] ) ) {
                $description = get_post_field( 'post_content', intval( $event['page_id'] ) );
            }
            if ( $readonly ) {
                echo '<div class="tta-archived-description">' . apply_filters( 'the_content', $description ) . '</div>';
            } else {
                // Render the full TinyMCE editor
               wp_editor(
                    $description,
                    'tta_event_description',
                    [
                        'textarea_name' => 'description',
                        'media_buttons' => true,
                        'textarea_rows' => 10,
                        'teeny'         => false,

                        // force TinyMCE to load all the usual buttons (format dropdown, quotes, etc)
                        'tinymce' => [
                            'wpautop'   => true,
                            'toolbar1'  => 'formatselect,bold,italic,underline,strikethrough,blockquote,alignleft,aligncenter,alignright,alignjustify,bullist,numlist,link,unlink,undo,redo,fullscreen',
                            'toolbar2'  => 'pastetext,pasteword,selectall,removeformat,table,hr',
                            'toolbar3'  => '',
                            'toolbar4'  => '',
                            'block_formats' => 'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6',
                        ],
                        'quicktags' => true,  // still allow the “Code" tab
                    ]
                );
            }
            ?>
          </td>
        </tr>
    </table>

    <h2>Event Images</h2>
    <table class="form-table">
        <tbody>
            <!-- Main Image -->
            <tr>
                <th>
                    Main Image
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::MAIN_IMAGE ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                </th>
                <td>
                    <input type="hidden" name="mainimageid" id="mainimageid"
                           value="<?php echo esc_attr( $event['mainimageid'] ?? '' ); ?>">
                    <button type="button" class="button tta-upload-single" data-target="#mainimageid">
                        Select Image
                    </button>
                    <div id="mainimage-preview" style="margin-top:10px;">
                        <?php if ( ! empty( $event['mainimageid'] ) ) {
                            echo tta_admin_preview_image( $event['mainimageid'], [150,150] );
                        } ?>
                    </div>
                </td>
            </tr>

            <!-- Gallery Images -->
            <tr>
                <th>
                    Gallery Images
                    <span class="tta-tooltip-icon"
                          data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::GALLERY ) ); ?>"
                          style="margin-left:4px;">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                             alt="Help">
                    </span>
                </th>
                <td>
                    <input type="hidden" name="otherimageids" id="otherimageids"
                           value="<?php echo esc_attr( $event['otherimageids'] ?? '' ); ?>">
                    <button type="button" class="button tta-upload-multiple" data-target="#otherimageids">
                        Select Images
                    </button>
                    <div id="otherimage-preview" style="margin-top:10px;">
                        <?php
                        if ( ! empty( $event['otherimageids'] ) ) {
                            foreach ( explode( ',', $event['otherimageids'] ) as $aid ) {
                                echo tta_admin_preview_image(
                                    intval( $aid ),
                                    [100,100],
                                    [ 'style' => 'margin-right:5px;' ]
                                );
                            }
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <?php
    // Show or hide based on dropdown
    $show_waitlist = ( $event['waitlistavailable'] ?? '0' ) === '1';
    ?>

    </fieldset>
    <?php if ( ! $readonly ) : ?>
        <p class="submit">
            <button type="submit" name="tta_event_save" class="button button-primary">
                <?php echo $editing ? 'Update Event' : 'Create Event'; ?>
            </button>
            <div class="tta-admin-progress-spinner-div">
                <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>"/>
            </div>
            <div class="tta-admin-progress-reponse-div">
                <p class="tta-admin-progress-response-p"></p>
            </div>
        </p>
    <?php endif; ?>
</form>
