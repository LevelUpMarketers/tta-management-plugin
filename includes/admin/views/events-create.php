/* includes/admin/views/events-create.php */

<?php
global $wpdb;
$table   = $wpdb->prefix . 'tta_events';
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
    }
}

// Handle save
if ( isset( $_POST['tta_event_save'] ) && check_admin_referer(
    'tta_event_save_action', 'tta_event_save_nonce'
) ) {
    // Combine address parts
    $address = implode(
        ' - ',
        array_filter( [
            sanitize_text_field( $_POST['street_address'] ),
            sanitize_text_field( $_POST['address_2'] ),
            sanitize_text_field( $_POST['city'] ),
            sanitize_text_field( $_POST['state'] ),
            sanitize_text_field( $_POST['zip'] ),
        ] )
    );

    $data = [
        'name'                  => sanitize_text_field( $_POST['name'] ),
        'date'                  => sanitize_text_field( $_POST['date'] ),
        'all_day_event'         => sanitize_text_field( $_POST['all_day_event'] ),
        'start_time'            => sanitize_text_field( $_POST['start_time'] ),
        'end_time'              => sanitize_text_field( $_POST['end_time'] ),
        'virtual_event'         => sanitize_text_field( $_POST['virtual_event'] ),
        'address'               => $address,
        'venueurl'              => esc_url_raw( $_POST['venueurl'] ),
        'type'                  => sanitize_text_field( $_POST['type'] ),
        'baseeventcost'         => floatval( $_POST['baseeventcost'] ),
        'discountedmembercost'  => floatval( $_POST['discountedmembercost'] ),
        'attendancelimit'       => intval( $_POST['attendancelimit'] ),
        'waitlistavailable'     => sanitize_text_field( $_POST['waitlistavailable'] ),
        'refundsavailable'      => sanitize_text_field( $_POST['refundsavailable'] ),
        'discountcode'          => sanitize_text_field( $_POST['discountcode'] ),
        'url2'                  => esc_url_raw( $_POST['url2'] ),
        'url3'                  => esc_url_raw( $_POST['url3'] ),
        'url4'                  => esc_url_raw( $_POST['url4'] ),
        'mainimageid'           => intval( $_POST['mainimageid'] ),
        'otherimageids'         => sanitize_text_field( $_POST['otherimageids'] ),
    ];

    if ( $editing ) {
        $wpdb->update( $table, $data, [ 'id' => intval( $_POST['tta_event_id'] ) ] );
        echo '<div class="updated"><p>Event updated!</p></div>';
    } else {
        $wpdb->insert( $table, $data );
        echo '<div class="updated"><p>Event created!</p></div>';
        $editing = true;
    }

    // Reload for further editing
    if ( $editing ) {
        $id    = isset( $_POST['tta_event_id'] )
                   ? intval( $_POST['tta_event_id'] )
                   : $wpdb->insert_id;
        $event = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ), ARRAY_A );
    }
}
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
                <label for="name">Event Name</label>
                <span class="tta-tooltip-icon" data-tooltip="Enter the title of the event as it will appear everywhere.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="text" name="name" id="name" class="regular-text"
                       value="<?php echo esc_attr( $event['name'] ?? '' ); ?>">
            </td>
        </tr>

        <!-- Date -->
        <tr>
            <th>
                <label for="date">Date</label>
                <span class="tta-tooltip-icon" data-tooltip="Choose the calendar date for this event.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="date" name="date" id="date"
                       value="<?php echo esc_attr( $event['date'] ?? '' ); ?>">
            </td>
        </tr>

        <!-- All-day Event? -->
        <tr>
            <th>
                <label for="all_day_event">All-day Event?</label>
                <span class="tta-tooltip-icon" data-tooltip="Check ‘Yes’ if the event spans the entire day.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
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
                <label for="start_time">Start Time</label>
                <span class="tta-tooltip-icon" data-tooltip="Use the time picker to select the event start time.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="time" name="start_time" id="start_time" class="regular-text"
                       value="<?php echo esc_attr( $event['start_time'] ?? '' ); ?>">
            </td>
        </tr>

        <!-- End Time -->
        <tr>
            <th>
                <label for="end_time">End Time</label>
                <span class="tta-tooltip-icon" data-tooltip="Use the time picker to select the event end time.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="time" name="end_time" id="end_time" class="regular-text"
                       value="<?php echo esc_attr( $event['end_time'] ?? '' ); ?>">
            </td>
        </tr>

        <!-- Virtual Event? -->
        <tr>
            <th>
                <label for="virtual_event">Virtual Event?</label>
                <span class="tta-tooltip-icon" data-tooltip="Check ‘Yes’ if this is an online-only event.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
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
                <label for="street_address">Street Address</label>
                <span class="tta-tooltip-icon" data-tooltip="Enter the primary street address.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="text" name="street_address" id="street_address" class="regular-text"
                       value="<?php echo esc_attr( explode(' - ', $event['address'] ?? '')[0] ?? '' ); ?>">
            </td>
        </tr>

        <!-- Address 2 -->
        <tr>
            <th>
                <label for="address_2">Address 2</label>
                <span class="tta-tooltip-icon" data-tooltip="Apartment, suite, unit, etc.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="text" name="address_2" id="address_2" class="regular-text"
                       value="<?php echo esc_attr( explode(' - ', $event['address'] ?? '')[1] ?? '' ); ?>">
            </td>
        </tr>

        <!-- City -->
        <tr>
            <th>
                <label for="city">City</label>
                <span class="tta-tooltip-icon" data-tooltip="City name.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="text" name="city" id="city" class="regular-text"
                       value="<?php echo esc_attr( explode(' - ', $event['address'] ?? '')[2] ?? '' ); ?>">
            </td>
        </tr>

        <!-- State -->
        <tr>
            <th>
                <label for="state">State</label>
                <span class="tta-tooltip-icon" data-tooltip="Select the state for this event location.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
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
                <label for="zip">ZIP</label>
                <span class="tta-tooltip-icon" data-tooltip="Postal code.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="text" name="zip" id="zip" class="regular-text"
                       value="<?php echo esc_attr( explode(' - ', $event['address'] ?? '')[4] ?? '' ); ?>">
            </td>
        </tr>

        <!-- Venue Link -->
        <tr>
            <th>
                <label for="venueurl">Venue Link</label>
                <span class="tta-tooltip-icon" data-tooltip="Link to the venue or event page.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="url" name="venueurl" id="venueurl" class="regular-text"
                       value="<?php echo esc_attr($event['venueurl'] ?? ''); ?>">
            </td>
        </tr>

        <!-- Event Type -->
        <tr>
            <th>
                <label for="type">Event Type</label>
                <span class="tta-tooltip-icon" data-tooltip="Select whether this event is Free, Paid, or Member Only.">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                </span>
            </th>
            <td>
                <select name="type" id="type">
                    <?php
                    $types = ['free'=>'Free','paid'=>'Paid','memberonly'=>'Member Only'];
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
                <label for="baseeventcost">Base Cost</label>
                <span class="tta-tooltip-icon" data-tooltip="Enter the standard ticket price in USD, with cents.">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="number" name="baseeventcost" id="baseeventcost" class="regular-text" step="0.01" min="0"
                       value="<?php echo esc_attr(number_format_i18n($event['baseeventcost']??0,2)); ?>">
            </td>
        </tr>

        <!-- Discounted Cost -->
        <tr>
            <th>
                <label for="discountedmembercost">Discounted Cost</label>
                <span class="tta-tooltip-icon" data-tooltip="Enter the member discounted price in USD, with cents.">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="number" name="discountedmembercost" id="discountedmembercost" class="regular-text" step="0.01" min="0"
                       value="<?php echo esc_attr(number_format_i18n($event['discountedmembercost']??0,2)); ?>">
            </td>
        </tr>

        <!-- Attendance Limit -->
        <tr>
            <th>
                <label for="attendancelimit">Attendance Limit</label>
                <span class="tta-tooltip-icon" data-tooltip="Set max attendees; use 0 for unlimited.">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="number" name="attendancelimit" id="attendancelimit" min="0" step="1"
                       value="<?php echo esc_attr($event['attendancelimit']??0); ?>">
            </td>
        </tr>

        <!-- Waitlist Available? -->
        <tr>
            <th>
                <label for="waitlistavailable">Waitlist Available?</label>
                <span class="tta-tooltip-icon" data-tooltip="Allow users to join a waitlist when the event is full.">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
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
                <label for="refundsavailable">Refunds Available?</label>
                <span class="tta-tooltip-icon" data-tooltip="Allow users to request a refund for this event.">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
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
                <label for="discountcode">Discount Code</label>
                <span class="tta-tooltip-icon" data-tooltip="Apply a promo code and its discount details.">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="text" name="discountcode" id="discountcode" class="regular-text"
                       value="<?php echo esc_attr($event['discountcode']??''); ?>">
            </td>
        </tr>

        <!-- Extra Event Link 1 -->
        <tr>
            <th>
                <label for="url2">Extra Event Link 1</label>
                <span class="tta-tooltip-icon" data-tooltip="Additional resource link (e.g., registration page).">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="url" name="url2" id="url2" class="regular-text"
                       value="<?php echo esc_attr($event['url2']??''); ?>">
            </td>
        </tr>

        <!-- Extra Event Link 2 -->
        <tr>
            <th>
                <label for="url3">Extra Event Link 2</label>
                <span class="tta-tooltip-icon" data-tooltip="Additional resource link (e.g., seating chart).">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="url" name="url3" id="url3" class="regular-text"
                       value="<?php echo esc_attr($event['url3']??''); ?>">
            </td>
        </tr>

        <!-- Extra Event Link 3 -->
        <tr>
            <th>
                <label for="url4">Extra Event Link 3</label>
                <span class="tta-tooltip-icon" data-tooltip="Additional resource link (e.g., sponsor page).">
                    <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/question.svg');?>" alt="Help">
                </span>
            </th>
            <td>
                <input type="url" name="url4" id="url4" class="regular-text"
                       value="<?php echo esc_attr($event['url4']??''); ?>">
            </td>
        </tr>
         <!-- Event Description -->
        <tr style="width: 100%;">
          <th>
            <h2>Event Description</h2>
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

    <h2 style="margin-top: 40px;">Event Images</h2>
    <table class="form-table">
        <tbody>
        <!-- Main Image -->
        <tr>
            <th>
                Main Image
                <span class="tta-tooltip-icon" data-tooltip="Select a primary image for this event.">
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
                    <?php if(!empty($event['mainimageid'])) echo wp_get_attachment_image($event['mainimageid'],[150,150]); ?>
                </div>
            </td>
        </tr>

        <!-- Gallery Images -->
        <tr>
            <th>
                Gallery Images
                <span class="tta-tooltip-icon" data-tooltip="Choose multiple images for the event gallery.">
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
                            echo wp_get_attachment_image(intval($aid),[100,100],false,['style'=>'margin-right:5px;']);
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