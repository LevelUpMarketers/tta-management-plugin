<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="wrap">
<h1><?php esc_html_e( 'Manage Discount Codes', 'tta' ); ?></h1>
<form method="post">
<table class="widefat" id="tta-discount-codes-table">
<thead><tr><th><?php esc_html_e( 'Code', 'tta' ); ?></th><th><?php esc_html_e( 'Type', 'tta' ); ?></th><th><?php esc_html_e( 'Amount', 'tta' ); ?></th><th><?php esc_html_e( 'One-Time Use?', 'tta' ); ?></th><th><?php esc_html_e( 'Date of Use', 'tta' ); ?></th><th></th></tr></thead>
<tbody>
<?php if ( ! empty( $codes ) ) : foreach ( $codes as $i => $row ) : ?>
<?php
$onetime = ! empty( $row['onetime'] ) ? 1 : 0;
$used_raw = $row['used'] ?? '';
$used_display = 'N/A';
if ( $onetime && ! empty( $used_raw ) ) {
    $timestamp = strtotime( $used_raw );
    if ( false !== $timestamp ) {
        $used_display = date_i18n(
            get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
            $timestamp
        );
    }
}
?>
<tr>
<td><input type="text" name="codes[<?php echo esc_attr( $i ); ?>][code]" value="<?php echo esc_attr( $row['code'] ); ?>" class="regular-text"></td>
<td>
<select name="codes[<?php echo esc_attr( $i ); ?>][type]">
<option value="flat" <?php selected( $row['type'], 'flat' ); ?>><?php esc_html_e( 'Flat $ Amount Off', 'tta' ); ?></option>
<option value="percent" <?php selected( $row['type'], 'percent' ); ?>><?php esc_html_e( 'Percentage Off', 'tta' ); ?></option>
</select>
</td>
<td><input type="number" name="codes[<?php echo esc_attr( $i ); ?>][amount]" step="0.01" min="0" value="<?php echo esc_attr( $row['amount'] ); ?>"></td>
<td>
<select name="codes[<?php echo esc_attr( $i ); ?>][onetime]">
<option value="0" <?php selected( $onetime, 0 ); ?>><?php esc_html_e( 'No', 'tta' ); ?></option>
<option value="1" <?php selected( $onetime, 1 ); ?>><?php esc_html_e( 'Yes', 'tta' ); ?></option>
</select>
</td>
<td>
<input type="text" class="regular-text" value="<?php echo esc_attr( $used_display ); ?>" readonly>
<input type="hidden" name="codes[<?php echo esc_attr( $i ); ?>][used]" value="<?php echo esc_attr( $used_raw ); ?>">
</td>
<td><button class="button tta-remove-code">&times;</button></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
<p><button type="button" class="button" id="tta-add-code"><?php esc_html_e( 'Add Discount Code', 'tta' ); ?></button></p>
<?php wp_nonce_field( 'tta_discount_codes_save', 'tta_discount_codes_nonce' ); ?>
<p class="submit"><input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Discount Codes', 'tta' ); ?>"></p>
</form>
</div>
<script>
jQuery(function($){
    var index = <?php echo isset($i) ? intval($i + 1) : 0; ?>;
    $('#tta-add-code').on('click', function(e){
        e.preventDefault();
        var row = '<tr>'+
            '<td><input type="text" name="codes['+index+'][code]" class="regular-text"></td>'+
            '<td><select name="codes['+index+'][type]">'+
            '<option value="flat"><?php echo esc_js( __( 'Flat $ Amount Off', 'tta' ) ); ?></option>'+
            '<option value="percent" selected><?php echo esc_js( __( 'Percentage Off', 'tta' ) ); ?></option>'+
            '</select></td>'+
            '<td><input type="number" name="codes['+index+'][amount]" step="0.01" min="0" value="0"></td>'+
            '<td><select name="codes['+index+'][onetime]">'+
            '<option value="0" selected><?php echo esc_js( __( 'No', 'tta' ) ); ?></option>'+
            '<option value="1"><?php echo esc_js( __( 'Yes', 'tta' ) ); ?></option>'+
            '</select></td>'+
            '<td><input type="text" class="regular-text" value="<?php echo esc_js( __( 'N/A', 'tta' ) ); ?>" readonly>'+
            '<input type="hidden" name="codes['+index+'][used]" value=""></td>'+
            '<td><button class="button tta-remove-code">&times;</button></td>'+
            '</tr>';
        $('#tta-discount-codes-table tbody').append(row);
        index++;
    });
    $('#tta-discount-codes-table').on('click','.tta-remove-code',function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
    });
});
</script>
