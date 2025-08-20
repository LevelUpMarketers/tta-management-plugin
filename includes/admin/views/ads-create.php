<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ads     = get_option( 'tta_ads', [] );
$editing = false;
$ad      = [];
$index   = null;

if ( isset( $_GET['ad_id'] ) && isset( $ads[ intval( $_GET['ad_id'] ) ] ) ) {
    $index   = intval( $_GET['ad_id'] );
    $ad      = $ads[ $index ];
    $editing = true;
}

if ( isset( $_POST['tta_ad_save'] ) && check_admin_referer( 'tta_ad_save_action', 'tta_ad_save_nonce' ) ) {
    $url = sanitize_text_field( $_POST['url'] ?? '' );
    if ( $url && ! preg_match( '#^https?://#i', $url ) ) {
        $url = 'https://' . $url;
    }
    $host = parse_url( $url, PHP_URL_HOST );
    if ( ! $host || ! preg_match( '/\.[a-z]{2,}$/i', $host ) ) {
        echo '<div class="error"><p>' . esc_html__( 'Please enter a valid Link URL.', 'tta' ) . '</p></div>';
    } else {
        $new_ad = [
            'image_id'        => intval( $_POST['image_id'] ?? 0 ),
            'url'             => esc_url_raw( $url ),
            'business_name'   => sanitize_text_field( $_POST['business_name'] ?? '' ),
            'business_phone'  => sanitize_text_field( $_POST['business_phone'] ?? '' ),
            'business_address'=> sanitize_text_field( $_POST['business_address'] ?? '' ),
        ];

        if ( $editing && null !== $index ) {
            $ads[ $index ] = $new_ad;
            echo '<div class="updated"><p>' . esc_html__( 'Ad updated.', 'tta' ) . '</p></div>';
        } else {
            $ads[] = $new_ad;
            $index   = array_key_last( $ads );
            $editing = true;
            echo '<div class="updated"><p>' . esc_html__( 'Ad created.', 'tta' ) . '</p></div>';
        }

        update_option( 'tta_ads', array_values( $ads ), false );
        TTA_Cache::delete( 'tta_ads_all' );
        $ad = $new_ad;
    }
}
?>
<div class="wrap">
<form method="post">
    <?php wp_nonce_field( 'tta_ad_save_action', 'tta_ad_save_nonce' ); ?>
    <?php if ( $editing && null !== $index ) : ?>
        <input type="hidden" name="ad_id" value="<?php echo esc_attr( $index ); ?>">
    <?php endif; ?>
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
                    <button class="button tta-upload-single" data-target="#ad_imageid"><?php esc_html_e( 'Select Image', 'tta' ); ?></button>
                    <input type="hidden" id="ad_imageid" name="image_id" value="<?php echo esc_attr( $ad['image_id'] ?? 0 ); ?>">
                    <div id="ad_image-preview"><?php echo ! empty( $ad['image_id'] ) ? wp_get_attachment_image( intval( $ad['image_id'] ), 'thumbnail' ) : ''; ?></div>
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
                <td><input type="text" name="business_phone" id="business_phone" class="regular-text" value="<?php echo esc_attr( $ad['business_phone'] ?? '' ); ?>"></td>
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
        <button type="submit" name="tta_ad_save" class="button button-primary">
            <?php echo $editing ? esc_html__( 'Update Ad', 'tta' ) : esc_html__( 'Create Ad', 'tta' ); ?>
        </button>
    </p>
</form>
</div>
