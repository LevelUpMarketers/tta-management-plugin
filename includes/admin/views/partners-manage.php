<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="tta-partners-manage">
    <?php
    global $wpdb;
    $partners_table = $wpdb->prefix . 'tta_partners';
    $partners       = $wpdb->get_results( "SELECT * FROM {$partners_table} ORDER BY created_at DESC", ARRAY_A );
    ?>

    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th class="column-primary">&nbsp;</th>
                <th><?php esc_html_e( 'Company Name', 'tta' ); ?></th>
                <th><?php esc_html_e( 'Contact', 'tta' ); ?></th>
                <th><?php esc_html_e( 'Email', 'tta' ); ?></th>
                <th><?php esc_html_e( 'Licenses', 'tta' ); ?></th>
                <th><?php esc_html_e( 'Created', 'tta' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'tta' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $partners ) ) : ?>
                <?php foreach ( $partners as $partner ) :
                    $created = $partner['created_at'] ? date_i18n( 'F j, Y \a\t g:i A', strtotime( $partner['created_at'] ) ) : '';
                    ?>
                    <tr data-partner-id="<?php echo esc_attr( $partner['id'] ); ?>">
                        <td class="tta-toggle-arrow" aria-hidden="true"></td>
                        <td><?php echo esc_html( $partner['company_name'] ); ?></td>
                        <td><?php echo esc_html( trim( $partner['contact_first_name'] . ' ' . $partner['contact_last_name'] ) ); ?></td>
                        <td><?php echo esc_html( $partner['contact_email'] ); ?></td>
                        <td><?php echo esc_html( intval( $partner['licenses'] ) ); ?></td>
                        <td><?php echo esc_html( $created ); ?></td>
                        <td><a href="#" class="tta-edit-link"><?php esc_html_e( 'Edit', 'tta' ); ?></a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7"><?php esc_html_e( 'No partners found.', 'tta' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
