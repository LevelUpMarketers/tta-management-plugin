<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="wrap">
<h1><?php esc_html_e( 'Manage Discount Codes', 'tta' ); ?></h1>
<form method="post">
<table class="widefat" id="tta-discount-codes-table">
<thead><tr><th><?php esc_html_e( 'Code', 'tta' ); ?></th><th><?php esc_html_e( 'Type', 'tta' ); ?></th><th><?php esc_html_e( 'Amount', 'tta' ); ?></th><th></th></tr></thead>
<tbody>
<?php if ( ! empty( $codes ) ) : foreach ( $codes as $i => $row ) : ?>
<tr>
<td><input type="text" name="codes[<?php echo esc_attr( $i ); ?>][code]" value="<?php echo esc_attr( $row['code'] ); ?>" class="regular-text"></td>
<td>
<select name="codes[<?php echo esc_attr( $i ); ?>][type]">
<option value="flat" <?php selected( $row['type'], 'flat' ); ?>><?php esc_html_e( 'Flat $ Amount Off', 'tta' ); ?></option>
<option value="percent" <?php selected( $row['type'], 'percent' ); ?>><?php esc_html_e( 'Percentage Off', 'tta' ); ?></option>
</select>
</td>
<td><input type="number" name="codes[<?php echo esc_attr( $i ); ?>][amount]" step="0.01" min="0" value="<?php echo esc_attr( $row['amount'] ); ?>"></td>
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
