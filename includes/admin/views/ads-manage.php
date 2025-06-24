<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
<h1><?php esc_html_e( 'Manage Ads', 'tta' ); ?></h1>
<form method="post">
<table class="form-table" id="tta-ads-table">
<tbody>
<?php
if ( ! empty( $ads ) ) {
    foreach ( $ads as $index => $ad ) {
        $img_id = intval( $ad['image_id'] );
        $url    = esc_url( $ad['url'] );
        $preview = $img_id ? wp_get_attachment_image( $img_id, 'thumbnail' ) : '';
        ?>
        <tr class="tta-ad-row">
            <th scope="row"><?php esc_html_e( 'Ad Image', 'tta' ); ?></th>
            <td>
                <button class="button tta-upload-single" data-target="#ad_image_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Select Image', 'tta' ); ?></button>
                <input type="hidden" id="ad_image_<?php echo esc_attr( $index ); ?>" name="ads[<?php echo esc_attr( $index ); ?>][image_id]" value="<?php echo esc_attr( $img_id ); ?>">
                <div id="ad_image_preview_<?php echo esc_attr( $index ); ?>"><?php echo $preview; ?></div>
            </td>
        </tr>
        <tr class="tta-ad-row">
            <th scope="row"><?php esc_html_e( 'Link URL', 'tta' ); ?></th>
            <td>
                <input type="text" name="ads[<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" class="regular-text" />
                <button class="button tta-remove-ad">&times;</button>
            </td>
        </tr>
        <?php
    }
}
?>
</tbody>
</table>
<p>
    <button type="button" class="button" id="tta-add-ad"><?php esc_html_e( 'Add Ad', 'tta' ); ?></button>
</p>
<?php wp_nonce_field( 'tta_ads_save', 'tta_ads_nonce' ); ?>
<p class="submit"><input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Ads', 'tta' ); ?>"></p>
</form>
</div>
<script>
jQuery(function($){
    var index = <?php echo isset( $index ) ? intval( $index + 1 ) : 0; ?>;
    $('#tta-add-ad').on('click', function(e){
        e.preventDefault();
        var rowImg = '<tr class="tta-ad-row"><th scope="row"><?php esc_html_e( 'Ad Image', 'tta' ); ?></th><td><button class="button tta-upload-single" data-target="#ad_image_'+index+'">Select Image</button><input type="hidden" id="ad_image_'+index+'" name="ads['+index+'][image_id]" value=""><div id="ad_image_preview_'+index+'"></div></td></tr>';
        var rowUrl = '<tr class="tta-ad-row"><th scope="row"><?php esc_html_e( 'Link URL', 'tta' ); ?></th><td><input type="text" name="ads['+index+'][url]" value="" class="regular-text" /> <button class="button tta-remove-ad">&times;</button></td></tr>';
        $('#tta-ads-table tbody').append(rowImg + rowUrl);
        index++;
    });
    $('#tta-ads-table').on('click','.tta-remove-ad',function(e){
        e.preventDefault();
        $(this).closest('tr').prev('tr').remove();
        $(this).closest('tr').remove();
    });
});
</script>
