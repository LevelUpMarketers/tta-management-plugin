<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$partner_id = isset( $_GET['partner_id'] ) ? intval( $_GET['partner_id'] ) : 0;

if ( ! $partner_id ) {
    echo '<p>' . esc_html__( 'Partner not found.', 'tta' ) . '</p>';
    return;
}

global $wpdb;
$partners_table = $wpdb->prefix . 'tta_partners';
$partner        = $wpdb->get_row(
    $wpdb->prepare( "SELECT * FROM {$partners_table} WHERE id = %d", $partner_id ),
    ARRAY_A
);

if ( ! $partner ) {
    echo '<p>' . esc_html__( 'Partner not found.', 'tta' ) . '</p>';
    return;
}
?>

<form id="tta-partner-edit-form" method="post">
    <?php wp_nonce_field( 'tta_partner_manage_action', 'tta_partner_update_nonce' ); ?>
    <input type="hidden" name="partner_id" value="<?php echo esc_attr( $partner_id ); ?>">

    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="company_name_edit"><?php esc_html_e( 'Company Name', 'tta' ); ?></label>
                </th>
                <td>
                    <input type="text" name="company_name" id="company_name_edit" class="regular-text" value="<?php echo esc_attr( $partner['company_name'] ); ?>" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="contact_first_name_edit"><?php esc_html_e( 'Contact First Name', 'tta' ); ?></label>
                </th>
                <td>
                    <input type="text" name="contact_first_name" id="contact_first_name_edit" class="regular-text" value="<?php echo esc_attr( $partner['contact_first_name'] ); ?>" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="contact_last_name_edit"><?php esc_html_e( 'Contact Last Name', 'tta' ); ?></label>
                </th>
                <td>
                    <input type="text" name="contact_last_name" id="contact_last_name_edit" class="regular-text" value="<?php echo esc_attr( $partner['contact_last_name'] ); ?>" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="contact_phone_edit"><?php esc_html_e( 'Contact Phone', 'tta' ); ?></label>
                </th>
                <td>
                    <input type="tel" name="contact_phone" id="contact_phone_edit" class="regular-text" value="<?php echo esc_attr( $partner['contact_phone'] ); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="contact_email_edit"><?php esc_html_e( 'Contact Email', 'tta' ); ?></label>
                </th>
                <td>
                    <input type="email" name="contact_email" id="contact_email_edit" class="regular-text" value="<?php echo esc_attr( $partner['contact_email'] ); ?>" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="licenses_edit"><?php esc_html_e( 'Licenses', 'tta' ); ?></label>
                </th>
                <td>
                    <input type="number" name="licenses" id="licenses_edit" class="small-text" min="0" max="9999" step="1" value="<?php echo esc_attr( intval( $partner['licenses'] ) ); ?>">
                </td>
            </tr>
            <tr>
        </tbody>
    </table>

    <p class="submit">
        <button type="submit" class="button button-primary"><?php esc_html_e( 'Update Partner', 'tta' ); ?></button>
        <div class="tta-admin-progress-spinner-div">
            <img
                class="tta-admin-progress-spinner-svg"
                src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>"
                alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>"
            />
        </div>
        <div class="tta-admin-progress-response-div">
            <p class="tta-admin-progress-response-p"></p>
        </div>
    </p>
</form>
