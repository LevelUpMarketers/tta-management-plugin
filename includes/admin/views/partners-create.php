<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="tta-partners-create">
    <p><?php esc_html_e( 'Add a new Trying to Adult partner by providing company and contact details. Saving will be implemented in a future update.', 'tta' ); ?></p>
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
                        <p class="description"><?php esc_html_e( 'Full legal or brand name of the partner company.', 'tta' ); ?></p>
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
                        <p class="description"><?php esc_html_e( 'Number of licenses purchased by the partner (0–9,999).', 'tta' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary" disabled><?php esc_html_e( 'Save Partner (coming soon)', 'tta' ); ?></button>
        </p>
    </form>
</div>
