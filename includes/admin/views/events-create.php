<?php
global $wpdb;
$table   = $wpdb->prefix . 'tta_events';
$venue_table = $wpdb->prefix . 'tta_venues';
$editing = false;
$event   = [];

// Load existing for editing
if ( isset( $_GET['event_id'] ) ) {
    $event_id = intval( $_GET['event_id'] );
    $event    = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$table} WHERE id = %d",
        $event_id
    ), ARRAY_A );
    if ( $event ) {
        $editing = true;
        $disc               = tta_parse_discount_data( $event['discountcode'] );
        $event['discountcode']   = $disc['code'];
        $event['discount_type']  = $disc['type'];
        $event['discount_amount'] = $disc['amount'];
    }
}

// Handle save
if ( isset( $_POST['tta_event_save'] ) && check_admin_referer(
    'tta_event_save_action', 'tta_event_save_nonce'
) ) {
    $required_labels = [
        'name'       => __( 'Event Name', 'tta' ),
        'date'       => __( 'Date', 'tta' ),
        'start_time' => __( 'Start Time', 'tta' ),
        'venuename'  => __( 'Venue Name', 'tta' ),
    ];
    $missing = [];
    foreach ( $required_labels as $field => $label ) {
        if ( empty( $_POST[ $field ] ) ) {
            $missing[] = $label;
        }
    }

    if ( $missing ) {
        printf(
            '<div class="error"><p>%s %s</p></div>',
            esc_html__( 'Please fill in the required fields:', 'tta' ),
            esc_html( implode( ', ', $missing ) )
        );
    } else {
    // Combine address parts
    $address = implode(
        ' - ',
        array_filter( [
            tta_sanitize_text_field( $_POST['street_address'] ),
            tta_sanitize_text_field( $_POST['address_2'] ),
            tta_sanitize_text_field( $_POST['city'] ),
            tta_sanitize_text_field( $_POST['state'] ),
            tta_sanitize_text_field( $_POST['zip'] ),
        ] )
    );

    $data = [
        'name'                  => tta_sanitize_text_field( $_POST['name'] ),
        'date'                  => tta_sanitize_text_field( $_POST['date'] ),
        'all_day_event'         => tta_sanitize_text_field( $_POST['all_day_event'] ),
        'start_time'            => tta_sanitize_text_field( $_POST['start_time'] ),
        'end_time'              => tta_sanitize_text_field( $_POST['end_time'] ),
        'virtual_event'         => tta_sanitize_text_field( $_POST['virtual_event'] ),
        'address'               => $address,
        'venueurl'              => tta_esc_url_raw( $_POST['venueurl'] ),
        'type'                  => tta_sanitize_text_field( $_POST['type'] ),
        'baseeventcost'         => floatval( $_POST['baseeventcost'] ),
        'discountedmembercost'  => floatval( $_POST['discountedmembercost'] ),
        'attendancelimit'       => intval( $_POST['attendancelimit'] ),
        'waitlistavailable'     => tta_sanitize_text_field( $_POST['waitlistavailable'] ),
        'refundsavailable'      => tta_sanitize_text_field( $_POST['refundsavailable'] ),
        'discountcode'          => tta_build_discount_data(
            $_POST['discountcode'] ?? '',
            $_POST['discount_type'] ?? 'percent',
            $_POST['discount_amount'] ?? 0
        ),
        'url2'                  => tta_esc_url_raw( $_POST['url2'] ),
        'url3'                  => tta_esc_url_raw( $_POST['url3'] ),
        'url4'                  => tta_esc_url_raw( $_POST['url4'] ),
        'mainimageid'           => intval( $_POST['mainimageid'] ),
        'otherimageids'         => tta_sanitize_text_field( $_POST['otherimageids'] ),
        'hosts'                 => implode( ',', tta_get_member_ids_by_names( $_POST['hosts'] ?? [] ) ),
        'volunteers'            => implode( ',', tta_get_member_ids_by_names( $_POST['volunteers'] ?? [] ) ),
        'host_notes'            => sanitize_textarea_field( $_POST['host_notes'] ?? '' ),
    ];

    // Store venue if new
    $venue_name = tta_sanitize_text_field( $_POST['venuename'] );
    $venue_url  = tta_esc_url_raw( $_POST['venueurl'] );
    $venue_data = [
        'name'     => $venue_name,
        'address'  => $address,
        'venueurl' => $venue_url,
        'url2'     => tta_esc_url_raw( $_POST['url2'] ),
        'url3'     => tta_esc_url_raw( $_POST['url3'] ),
        'url4'     => tta_esc_url_raw( $_POST['url4'] ),
    ];
    $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$venue_table} WHERE name = %s", $venue_name ) );
    if ( ! $exists ) {
        $wpdb->insert( $venue_table, $venue_data );
    }

    if ( $editing ) {
        $wpdb->update( $table, $data, [ 'id' => intval( $_POST['tta_event_id'] ) ] );
        echo '<div class="updated"><p>Event updated!</p></div>';
    } else {
        $wpdb->insert( $table, $data );
        echo '<div class="updated"><p>Event created!</p></div>';
        $editing = true;
    }
    TTA_Cache::flush();

    // Reload for further editing
    if ( $editing ) {
        $id    = isset( $_POST['tta_event_id'] )
                   ? intval( $_POST['tta_event_id'] )
                   : $wpdb->insert_id;
        $event = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ), ARRAY_A );
        if ( $event ) {
            $disc               = tta_parse_discount_data( $event['discountcode'] );
            $event['discountcode']   = $disc['code'];
            $event['discount_type']  = $disc['type'];
            $event['discount_amount'] = $disc['amount'];
        }
    }
    }
}
?>

<?php
// Build member choices for host/volunteer autocomplete
$member_choices = $wpdb->get_col(
    "SELECT CONCAT(first_name,' ',last_name) FROM {$wpdb->prefix}tta_members WHERE member_type IN ('volunteer','admin','super_admin') ORDER BY first_name, last_name"
);
$venue_choices = $wpdb->get_results( "SELECT name, address, venueurl, url2, url3, url4 FROM {$venue_table} ORDER BY name", ARRAY_A );
$hosts      = ! empty( $event['hosts'] ) ? tta_get_member_names_by_ids( explode( ',', $event['hosts'] ) ) : [''];
$volunteers = ! empty( $event['volunteers'] ) ? tta_get_member_names_by_ids( explode( ',', $event['volunteers'] ) ) : [''];
?>

<form id="tta-event-form" method="post">
    <?php wp_nonce_field( 'tta_event_save_action', 'tta_event_save_nonce' ); ?>
    <?php if ( $editing ) : ?>
        <input type="hidden" name="tta_event_id" value="<?php echo esc_attr( $event['id'] ); ?>">
    <?php endif; ?>

    <table class="form-table">
        <tbody>

        <!-- Event Name -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EVENT_NAME ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="name">Event Name</label>
            </th>
            <td>
                <input type="text" name="name" id="name" class="regular-text" required
                       value="<?php echo esc_attr( $event['name'] ?? '' ); ?>">
            </td>
        </tr>


        <!-- Date -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EVENT_DATE ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="date">Date</label>
            </th>
            <td>
                <input type="date" name="date" id="date" required
                       value="<?php echo esc_attr( $event['date'] ?? '' ); ?>">
            </td>
        </tr>

        <!-- All-day Event? -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EVENT_ALL_DAY ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
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
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::START_TIME ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="start_time">Start Time</label>
            </th>
            <td>
                <input type="time" name="start_time" id="start_time" class="regular-text" required
                       value="<?php echo esc_attr( $event['start_time'] ?? '' ); ?>">
            </td>
        </tr>

        <!-- End Time -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::END_TIME ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="end_time">End Time</label>
            </th>
            <td>
                <input type="time" name="end_time" id="end_time" class="regular-text"
                       value="<?php echo esc_attr( $event['end_time'] ?? '' ); ?>">
            </td>
        </tr>

        <!-- Virtual Event? -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::VIRTUAL_EVENT ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
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

        <!-- Street Address -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::STREET_ADDRESS ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="street_address">Street Address</label>
            </th>
            <td>
                <input type="text" name="street_address" id="street_address" class="regular-text"
                       value="<?php echo esc_attr( explode(' - ', $event['address'] ?? '')[0] ?? '' ); ?>">
            </td>
        </tr>

        <!-- Address 2 -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::ADDRESS_2 ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="address_2">Address 2</label>
            </th>
            <td>
                <input type="text" name="address_2" id="address_2" class="regular-text"
                       value="<?php echo esc_attr( explode(' - ', $event['address'] ?? '')[1] ?? '' ); ?>">
            </td>
        </tr>

        <!-- City -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::CITY ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="city">City</label>
            </th>
            <td>
                <input type="text" name="city" id="city" class="regular-text"
                       value="<?php echo esc_attr( explode(' - ', $event['address'] ?? '')[2] ?? '' ); ?>">
            </td>
        </tr>

        <!-- State -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::STATE ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
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
                $states = array_diff($all_states, ['Virginia']);
                sort($states);
                array_unshift($states, 'Virginia');
                ?>
                <select name="state" id="state">
                    <?php foreach($states as $st): ?>
                        <option value="<?php echo esc_attr($st); ?>" <?php selected( (explode(' - ', $event['address'] ?? '')[3] ?? ''), $st ); ?>>
                            <?php echo esc_html($st); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <!-- ZIP -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::ZIP ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="zip">ZIP</label>
            </th>
            <td>
                <input type="text" name="zip" id="zip" class="regular-text"
                       value="<?php echo esc_attr( explode(' - ', $event['address'] ?? '')[4] ?? '' ); ?>">
            </td>
        </tr>

        <!-- Venue Name -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::VENUE_NAME ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="venuename">Venue Name</label>
            </th>
            <td>
                <input type="text" name="venuename" id="venuename" class="regular-text" list="tta-venue-options" required
                       value="<?php echo esc_attr($event['venuename'] ?? ''); ?>">
            </td>
        </tr>

        <!-- Venue Link -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::VENUE_URL ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="venueurl">Venue Link</label>
            </th>
            <td>
                <input type="url" name="venueurl" id="venueurl" class="regular-text"
                       value="<?php echo esc_attr($event['venueurl'] ?? ''); ?>">
            </td>
        </tr>

        <!-- Event Type -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EVENT_TYPE ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
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
                    foreach($types as $val=>$lbl){
                        printf('<option value="%s"%s>%s</option>',
                            esc_attr($val),
                            selected($event['type']??'',$val,false),
                            esc_html($lbl)
                        );
                    }
                    ?>
                </select>
            </td>
        </tr>

        <!-- Base Cost -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::BASE_COST ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="baseeventcost">Base Cost</label>
            </th>
            <td>
                <input type="number" name="baseeventcost" id="baseeventcost" class="regular-text" step="0.01" min="0"
                       value="<?php echo esc_attr(number_format_i18n($event['baseeventcost']??0,2)); ?>">
            </td>
        </tr>

        <!-- Basic Member Cost -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::BASIC_COST ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="discountedmembercost">Basic Member Cost</label>
            </th>
            <td>
                <input type="number" name="discountedmembercost" id="discountedmembercost" class="regular-text" step="0.01" min="0"
                       value="<?php echo esc_attr(number_format_i18n($event['discountedmembercost']??0,2)); ?>">
            </td>
        </tr>

         <!-- Premium Member Cost -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::PREMIUM_COST ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="premiummembercost">Premium Member Cost</label>
            </th>
            <td>
                <input type="number" name="premiummembercost" id="premiummembercost" class="regular-text" step="0.01" min="0"
                       value="<?php echo esc_attr(number_format_i18n($event['premiummembercost']??0,2)); ?>">
            </td>
        </tr>

        <!-- Waitlist Available? -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::WAITLIST ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="waitlistavailable">Waitlist Available?</label>
            </th>
            <td>
                <select name="waitlistavailable" id="waitlistavailable">
                    <option value="0"<?php selected($event['waitlistavailable']??0,0);?>>No</option>
                    <option value="1"<?php selected($event['waitlistavailable']??0,1);?>>Yes</option>
                </select>
            </td>
        </tr>

        <!-- Refunds Available? -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::REFUNDS ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="refundsavailable">Refunds Available?</label>
            </th>
            <td>
                <select name="refundsavailable" id="refundsavailable">
                    <option value="0"<?php selected($event['refundsavailable']??0,0);?>>No</option>
                    <option value="1"<?php selected($event['refundsavailable']??0,1);?>>Yes</option>
                </select>
            </td>
        </tr>

        <!-- Discount Code -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::DISCOUNT_CODE ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="discountcode">Discount Code</label>
            </th>
            <td>
                <input type="text" name="discountcode" id="discountcode" class="regular-text"
                       value="<?php echo esc_attr($event['discountcode']??''); ?>">
            </td>
        </tr>
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::DISCOUNT_TYPE ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="discount_type">Discount Type</label>
            </th>
            <td>
                <select name="discount_type" id="discount_type">
                    <option value="flat" <?php selected($event['discount_type']??'percent','flat'); ?>>Flat $ Amount Off</option>
                    <option value="percent" <?php selected($event['discount_type']??'percent','percent'); ?>>Percentage Off</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::DISCOUNT_AMOUNT ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="discount_amount">Discount Amount</label>
            </th>
            <td>
                <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0"
                       value="<?php echo esc_attr($event['discount_amount']??0); ?>">
            </td>
        </tr>

        <!-- Extra Event Link 1 -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EXTRA_LINK ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="url2">Extra Event Link 1</label>
            </th>
            <td>
                <input type="url" name="url2" id="url2" class="regular-text"
                       value="<?php echo esc_attr($event['url2']??''); ?>">
            </td>
        </tr>

        <!-- Extra Event Link 2 -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EXTRA_LINK ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="url3">Extra Event Link 2</label>
            </th>
            <td>
                <input type="url" name="url3" id="url3" class="regular-text"
                       value="<?php echo esc_attr($event['url3']??''); ?>">
            </td>
        </tr>

        <!-- Extra Event Link 3 -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EXTRA_LINK ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
                <label for="url4">Extra Event Link 3</label>
            </th>
            <td>
                <input type="url" name="url4" id="url4" class="regular-text"
                       value="<?php echo esc_attr($event['url4']??''); ?>">
            </td>
        </tr>

        <!-- Event Hosts -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::HOSTS ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="hosts">Event Hosts</label>
            </th>
            <td>
                <div id="hosts-container">
                    <?php foreach ( $hosts as $i => $h ) : ?>
                        <div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;">
                            <input type="text" name="hosts[]" class="regular-text host-field" list="tta-member-options" placeholder="Host #<?php echo $i+1; ?>" value="<?php echo esc_attr( $h ); ?>">
                            <button type="button" class="delete-interest" aria-label="Remove" style="background:none;border:none;cursor:pointer;margin-left:8px;">
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/bin.svg' ); ?>" alt="×" style="width:16px;height:16px;">
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button" id="add-host" style="margin-top:8px;">+ Add Another Host</button>
            </td>
        </tr>

        <!-- Event Volunteers -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::VOLUNTEERS ) ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="volunteers">Event Volunteers</label>
            </th>
            <td>
                <div id="volunteers-container">
                    <?php foreach ( $volunteers as $i => $v ) : ?>
                        <div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;">
                            <input type="text" name="volunteers[]" class="regular-text volunteer-field" list="tta-member-options" placeholder="Volunteer #<?php echo $i+1; ?>" value="<?php echo esc_attr( $v ); ?>">
                            <button type="button" class="delete-interest" aria-label="Remove" style="background:none;border:none;cursor:pointer;margin-left:8px;">
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/bin.svg' ); ?>" alt="×" style="width:16px;height:16px;">
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
        <button type="button" class="button" id="add-volunteer" style="margin-top:8px;">+ Add Another Volunteer</button>
            </td>
        </tr>

        <!-- Host Notes -->
        <tr>
            <th>
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr__( 'Notes about hosts or volunteers (not public)', 'tta' ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
                <label for="host_notes">Host Notes</label>
            </th>
            <td>
                <textarea name="host_notes" id="host_notes" rows="4" class="large-text"><?php echo esc_textarea( $event['host_notes'] ?? '' ); ?></textarea>
            </td>
        </tr>

        <!-- Host Notes -->
        <tr>
            <th>
                <label for="host_notes">Event Host Notes</label>
            </th>
            <td>
                <textarea name="host_notes" id="host_notes" rows="4" class="large-text" placeholder="Notes for hosts and volunteers"><?php echo esc_textarea( $event['host_notes'] ?? '' ); ?></textarea>
            </td>
        </tr>

         <!-- Event Description -->
        <tr style="width: 100%;">
          <th>
            <h2>
              <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::DESCRIPTION ) ); ?>">
                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
              </span>
              <?php esc_html_e( 'Event Description', 'tta' ); ?>
            </h2>
          </th>
          <td style="width: 100vw;">
            <?php
                // Fetch existing description from the auto-generated page (if editing):
                $description = '';
                if ( ! empty( $event['page_id'] ) ) {
                    $description = get_post_field( 'post_content', intval( $event['page_id'] ) );
                }
                // Render TinyMCE
                wp_editor(
                    $description,
                    'tta_event_description',               // <textarea id>
                    [
                        'textarea_name' => 'description',   // name=description in $_POST
                        'media_buttons' => true,
                        'textarea_rows' => 10,
                        'teeny'         => false,
                    ]
                );
            ?>
          </td>
        </tr>

        </tbody>
</table>

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

    <h2 style="margin-top: 40px;">Event Images</h2>
    <table class="form-table">
        <tbody>
        <!-- Main Image -->
        <tr>
            <th>
                Main Image
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::MAIN_IMAGE ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="hidden" name="mainimageid" id="mainimageid"
                       value="<?php echo esc_attr($event['mainimageid']??''); ?>">
                <button type="button" class="button tta-upload-single" data-target="#mainimageid">
                    Select Image
                </button>
                <div id="mainimage-preview" style="margin-top:10px;">
                    <?php if(!empty($event['mainimageid'])) echo tta_admin_preview_image($event['mainimageid'], [150,150]); ?>
                </div>
            </td>
        </tr>

        <!-- Gallery Images -->
        <tr>
            <th>
                Gallery Images
                <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::GALLERY ) ); ?>">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="hidden" name="otherimageids" id="otherimageids"
                       value="<?php echo esc_attr($event['otherimageids']??''); ?>">
                <button type="button" class="button tta-upload-multiple" data-target="#otherimageids">
                    Select Images
                </button>
                <div id="otherimage-preview" style="margin-top:10px;">
                    <?php
                    if(!empty($event['otherimageids'])) {
                        foreach(explode(',',$event['otherimageids']) as $aid){
                            echo tta_admin_preview_image(intval($aid), [100,100], ['style' => 'margin-right:5px;']);
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

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
</form>