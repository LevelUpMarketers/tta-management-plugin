<?php
global $wpdb;
$table = $wpdb->prefix . 'tta_venues';
$editing = false;
$venue = [];
if ( isset($_GET['venue_id']) ) {
    $venue = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", intval($_GET['venue_id'])), ARRAY_A );
    if($venue){ $editing = true; }
}
if ( isset($_POST['tta_venue_save']) && check_admin_referer('tta_venue_save_action','tta_venue_save_nonce') ) {
    $address = implode(' - ', array_filter([
        tta_sanitize_text_field($_POST['street_address']),
        tta_sanitize_text_field($_POST['address_2']),
        tta_sanitize_text_field($_POST['city']),
        tta_sanitize_text_field($_POST['state']),
        tta_sanitize_text_field($_POST['zip'])
    ]));
    $data = [
        'name'     => tta_sanitize_text_field($_POST['name']),
        'venueurl' => tta_esc_url_raw($_POST['venueurl']),
        'address'  => $address,
    ];
    if($editing){
        $wpdb->update($table,$data,['id'=>intval($venue['id'])]);
        echo '<div class="updated"><p>Venue updated!</p></div>';
    }else{
        $wpdb->insert($table,$data);
        echo '<div class="updated"><p>Venue created!</p></div>';
        $editing = true;
        $venue = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $wpdb->insert_id), ARRAY_A );
    }
}
?>
<form method="post">
<?php wp_nonce_field('tta_venue_save_action','tta_venue_save_nonce'); ?>
<?php if($editing): ?><input type="hidden" name="venue_id" value="<?php echo esc_attr($venue['id']); ?>"><?php endif; ?>
<table class="form-table"><tbody>
<tr><th><label for="name">Venue Name</label></th>
<td><input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr($venue['name'] ?? ''); ?>"></td></tr>
<tr><th><label for="venueurl">Venue URL</label></th>
<td><input type="url" name="venueurl" id="venueurl" class="regular-text" value="<?php echo esc_attr($venue['venueurl'] ?? ''); ?>"></td></tr>
<tr><th><label for="street_address">Street Address</label></th>
<td><input type="text" name="street_address" id="street_address" class="regular-text" value="<?php echo esc_attr(explode(' - ',$venue['address'] ?? '')[0] ?? ''); ?>"></td></tr>
<tr><th><label for="address_2">Address 2</label></th>
<td><input type="text" name="address_2" id="address_2" class="regular-text" value="<?php echo esc_attr(explode(' - ',$venue['address'] ?? '')[1] ?? ''); ?>"></td></tr>
<tr><th><label for="city">City</label></th>
<td><input type="text" name="city" id="city" class="regular-text" value="<?php echo esc_attr(explode(' - ',$venue['address'] ?? '')[2] ?? ''); ?>"></td></tr>
<tr><th><label for="state">State</label></th>
<td><input type="text" name="state" id="state" class="regular-text" value="<?php echo esc_attr(explode(' - ',$venue['address'] ?? '')[3] ?? ''); ?>"></td></tr>
<tr><th><label for="zip">ZIP</label></th>
<td><input type="text" name="zip" id="zip" class="regular-text" value="<?php echo esc_attr(explode(' - ',$venue['address'] ?? '')[4] ?? ''); ?>"></td></tr>
</tbody></table>
<p class="submit"><button type="submit" name="tta_venue_save" class="button button-primary"><?php echo $editing?'Update Venue':'Create Venue'; ?></button></p>
</form>
