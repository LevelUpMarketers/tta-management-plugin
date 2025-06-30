<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var array $event Event row */
/** @var array[] $attendees List of attendees */
?>
<div class="tta-attendance-details">
  <h4><?php echo esc_html( $event['name'] ); ?></h4>
  <p><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $event['date'] ) ) ); ?>
     <?php echo esc_html( $event['time'] ); ?></p>
  <p><?php echo esc_html( tta_format_address( $event['address'] ) ); ?></p>
</div>
<table class="widefat striped tta-attendance-table">
  <thead>
    <tr>
      <th><?php esc_html_e( 'Attendee', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Email', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Status', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Actions', 'tta' ); ?></th>
    </tr>
  </thead>
  <tbody>
  <?php if ( $attendees ) :
    foreach ( $attendees as $a ) : ?>
    <tr data-attendee-id="<?php echo esc_attr( $a['id'] ); ?>">
      <td><?php echo esc_html( $a['first_name'] . ' ' . $a['last_name'] ); ?></td>
      <td><?php echo esc_html( $a['email'] ); ?></td>
      <td class="status-label"><?php echo esc_html( ucwords( str_replace('_',' ', $a['status'] ) ) ); ?></td>
      <td>
        <button class="button tta-mark-attendance" data-attendee-id="<?php echo esc_attr( $a['id'] ); ?>" data-status="checked_in"><?php esc_html_e( 'Check In', 'tta' ); ?></button>
        <button class="button tta-mark-attendance" data-attendee-id="<?php echo esc_attr( $a['id'] ); ?>" data-status="no_show"><?php esc_html_e( 'No-Show', 'tta' ); ?></button>
      </td>
    </tr>
  <?php endforeach; else : ?>
    <tr><td colspan="4"><?php esc_html_e( 'No attendees found.', 'tta' ); ?></td></tr>
  <?php endif; ?>
  </tbody>
</table>
