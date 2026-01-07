<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="tta-partners-create">
    <form id="tta-partner-form" method="post">
        <?php wp_nonce_field( 'tta_partner_save_action', 'tta_partner_save_nonce' ); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="company_name"><?php esc_html_e( 'Company Name', 'tta' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="company_name" id="company_name" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="contact_first_name"><?php esc_html_e( 'Contact First Name', 'tta' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="contact_first_name" id="contact_first_name" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="contact_last_name"><?php esc_html_e( 'Contact Last Name', 'tta' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="contact_last_name" id="contact_last_name" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="contact_phone"><?php esc_html_e( 'Contact Phone', 'tta' ); ?></label>
                    </th>
                    <td>
                        <input type="tel" name="contact_phone" id="contact_phone" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. (555) 123-4567', 'tta' ); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="contact_email"><?php esc_html_e( 'Contact Email', 'tta' ); ?></label>
                    </th>
                    <td>
                        <input type="email" name="contact_email" id="contact_email" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="licenses"><?php esc_html_e( 'Licenses', 'tta' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="licenses" id="licenses" class="small-text" min="0" max="9999" step="1">
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Partner', 'tta' ); ?></button>
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
</div>
