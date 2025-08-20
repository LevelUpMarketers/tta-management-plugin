<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ads   = get_option( 'tta_ads', [] );
$ad    = [];
$index = isset( $_GET['ad_id'] ) ? intval( $_GET['ad_id'] ) : null;
if ( null !== $index && isset( $ads[ $index ] ) ) {
    $ad = $ads[ $index ];
}
?>
<form method="post" id="tta-ad-edit-form">
    <?php wp_nonce_field( 'tta_ad_save_action', 'tta_ad_save_nonce' ); ?>
    <input type="hidden" name="ad_id" value="<?php echo esc_attr( $index ); ?>">
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::AD_IMAGE ) ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                    </span>
                    <?php esc_html_e( 'Ad Image', 'tta' ); ?>
                </th>
                <td>
                    <button class="button tta-upload-single" data-target="#ad_imageid_edit"><?php esc_html_e( 'Select Image', 'tta' ); ?></button>
                    <input type="hidden" id="ad_imageid_edit" name="image_id" value="<?php echo esc_attr( $ad['image_id'] ?? 0 ); ?>">
                    <div id="ad_image-preview_edit"><?php echo ! empty( $ad['image_id'] ) ? wp_get_attachment_image( intval( $ad['image_id'] ), 'thumbnail' ) : ''; ?></div>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::AD_URL ) ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                    </span>
                    <label for="url"><?php esc_html_e( 'Link URL', 'tta' ); ?></label>
                </th>
                <td><input type="text" name="url" id="url" class="regular-text" value="<?php echo esc_attr( $ad['url'] ?? '' ); ?>" placeholder="example.com"></td>
            </tr>
            <tr>
                <th scope="row">
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::AD_BUSINESS_NAME ) ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                    </span>
                    <label for="business_name"><?php esc_html_e( 'Business Name', 'tta' ); ?></label>
                </th>
                <td><input type="text" name="business_name" id="business_name" class="regular-text" value="<?php echo esc_attr( $ad['business_name'] ?? '' ); ?>"></td>
            </tr>
            <tr>
                <th scope="row">
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::AD_BUSINESS_PHONE ) ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                    </span>
                    <label for="business_phone"><?php esc_html_e( 'Business Telephone', 'tta' ); ?></label>
                </th>
                <td><input type="text" name="business_phone" id="business_phone_edit" class="regular-text" value="<?php echo esc_attr( $ad['business_phone'] ?? '' ); ?>"></td>
            </tr>
            <tr>
                <th scope="row">
                    <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::AD_BUSINESS_ADDRESS ) ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
                    </span>
                    <label for="business_address"><?php esc_html_e( 'Business Address', 'tta' ); ?></label>
                </th>
                <td><input type="text" name="business_address" id="business_address" class="regular-text" value="<?php echo esc_attr( $ad['business_address'] ?? '' ); ?>"></td>
            </tr>
        </tbody>
    </table>
    <p class="submit">
        <button type="submit" class="button button-primary"><?php esc_html_e( 'Update Ad', 'tta' ); ?></button>
        <div class="tta-admin-progress-spinner-div">
            <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="">
        </div>
        <div class="tta-admin-progress-response-div">
            <p class="tta-admin-progress-response-p"></p>
        </div>
    </p>
</form>

