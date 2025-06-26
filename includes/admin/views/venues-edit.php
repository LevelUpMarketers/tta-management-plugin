<?php
/**
 * Inline edit form for venues.
 */

global $wpdb;
$table = $wpdb->prefix . 'tta_venues';
$venue = [];
$editing = false;
if ( isset( $_GET['venue_id'] ) ) {
    $venue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id=%d", intval( $_GET['venue_id'] ) ), ARRAY_A );
    if ( $venue ) {
        $editing = true;
    }
}
?>
<form method="post" id="tta-venue-edit-form">
    <?php wp_nonce_field( 'tta_venue_save_action', 'tta_venue_save_nonce' ); ?>
    <?php if ( $editing ) : ?>
        <input type="hidden" name="venue_id" value="<?php echo esc_attr( $venue['id'] ); ?>">
    <?php endif; ?>
    <table class="form-table"><tbody>
        <tr><th><label for="name">Venue Name</label></th>
            <td><input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr( $venue['name'] ?? '' ); ?>"></td></tr>
        <tr><th><label for="venueurl">Venue URL</label></th>
            <td><input type="url" name="venueurl" id="venueurl" class="regular-text" value="<?php echo esc_attr( $venue['venueurl'] ?? '' ); ?>"></td></tr>
        <tr><th><label for="url2">Extra Event Link 1</label></th>
            <td><input type="url" name="url2" id="url2" class="regular-text" value="<?php echo esc_attr( $venue['url2'] ?? '' ); ?>"></td></tr>
        <tr><th><label for="url3">Extra Event Link 2</label></th>
            <td><input type="url" name="url3" id="url3" class="regular-text" value="<?php echo esc_attr( $venue['url3'] ?? '' ); ?>"></td></tr>
        <tr><th><label for="url4">Extra Event Link 3</label></th>
            <td><input type="url" name="url4" id="url4" class="regular-text" value="<?php echo esc_attr( $venue['url4'] ?? '' ); ?>"></td></tr>
        <tr><th><label for="street_address">Street Address</label></th>
            <td><input type="text" name="street_address" id="street_address" class="regular-text" value="<?php echo esc_attr( explode(' - ', $venue['address'] ?? '')[0] ?? '' ); ?>"></td></tr>
        <tr><th><label for="address_2">Address 2</label></th>
            <td><input type="text" name="address_2" id="address_2" class="regular-text" value="<?php echo esc_attr( explode(' - ', $venue['address'] ?? '')[1] ?? '' ); ?>"></td></tr>
        <tr><th><label for="city">City</label></th>
            <td><input type="text" name="city" id="city" class="regular-text" value="<?php echo esc_attr( explode(' - ', $venue['address'] ?? '')[2] ?? '' ); ?>"></td></tr>
        <tr><th><label for="state">State</label></th>
            <td><input type="text" name="state" id="state" class="regular-text" value="<?php echo esc_attr( explode(' - ', $venue['address'] ?? '')[3] ?? '' ); ?>"></td></tr>
        <tr><th><label for="zip">ZIP</label></th>
            <td><input type="text" name="zip" id="zip" class="regular-text" value="<?php echo esc_attr( explode(' - ', $venue['address'] ?? '')[4] ?? '' ); ?>"></td></tr>
    </tbody></table>
    <p class="submit"><button type="submit" class="button button-primary"><?php echo $editing ? 'Update Venue' : 'Create Venue'; ?></button></p>
</form>
