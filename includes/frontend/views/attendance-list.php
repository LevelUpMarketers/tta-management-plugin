<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var array $event Event row */
/** @var array[] $attendees List of attendees */
?>
<div class="tta-attendance-details">
  <h4><?php echo esc_html( $event['name'] ); ?></h4>
  <p><?php echo esc_html( tta_format_event_datetime( $event['date'], $event['time'] ) ); ?></p>
  <p>
    <a href="<?php echo esc_url( $event['venue_url'] ); ?>" target="_blank" rel="noopener">
      <?php echo esc_html( $event['venue_name'] ); ?>
    </a>
  </p>
  <p>
    <a href="<?php echo esc_url( tta_get_google_maps_url( $event['address'] ) ); ?>" target="_blank" rel="noopener">
      <?php echo esc_html( tta_format_address( $event['address'] ) ); ?>
    </a>
  </p>
  <?php if ( ! empty( $event['host_notes'] ) ) : ?>
  <p class="tta-host-notes">
    <?php echo nl2br( esc_html( $event['host_notes'] ) ); ?>
  </p>
  <?php endif; ?>
</div>
<table class="widefat striped tta-attendance-table">
  <thead>
    <tr>
      <th><?php esc_html_e( 'Attendee', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Email', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Event Attendance & No-Shows', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Needs Assistance', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Status', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Actions', 'tta' ); ?></th>
    </tr>
  </thead>
  <tbody>
  <?php if ( $attendees ) :
    foreach ( $attendees as $a ) : ?>
    <tr data-attendee-id="<?php echo esc_attr( $a['id'] ); ?>">
      <td><span class="tta-info-title"><?php esc_html_e( 'Name:', 'tta' ); ?></span><?php echo esc_html( $a['first_name'] . ' ' . $a['last_name'] ); ?></td>
      <td><span class="tta-info-title"><?php esc_html_e( 'Email:', 'tta' ); ?></span><?php echo esc_html( $a['email'] ); ?></td>
      <td><span class="tta-info-title"><?php esc_html_e( 'Event Attendance & No-Shows:', 'tta' ); ?></span><?php echo intval( $a['attended_count'] ); ?> <?php esc_html_e( 'Events Attended', 'tta' ); ?>, <?php echo intval( $a['no_show_count'] ); ?> <?php esc_html_e( 'No-Shows', 'tta' ); ?></td>
      <td><span class="tta-info-title"><?php esc_html_e( 'Needs Assistance:', 'tta' ); ?></span><?php echo isset( $a['assistance_note'] ) ? esc_html( $a['assistance_note'] ) : '-'; ?></td>
      <td><span class="tta-info-title"><?php esc_html_e( 'Status:', 'tta' ); ?></span><span class="status-label"><?php echo esc_html( ucwords( str_replace('_',' ', $a['status'] ) ) ); ?></span></td>
      <td>
        <?php $disabled = in_array( $a['status'], [ 'checked_in', 'no_show' ], true ) ? 'disabled' : ''; ?>
        <button class="button tta-mark-attendance<?php echo $disabled ? ' disabled' : ''; ?>" data-attendee-id="<?php echo esc_attr( $a['id'] ); ?>" data-status="checked_in" <?php echo $disabled; ?>><?php esc_html_e( 'Check In', 'tta' ); ?></button>
        <button class="button tta-mark-attendance<?php echo $disabled ? ' disabled' : ''; ?>" data-attendee-id="<?php echo esc_attr( $a['id'] ); ?>" data-status="no_show" <?php echo $disabled; ?>><?php esc_html_e( 'No-Show', 'tta' ); ?></button>
      </td>
    </tr>
  <?php endforeach; else : ?>
    <tr><td colspan="6"><?php esc_html_e( 'No attendees found.', 'tta' ); ?></td></tr>
  <?php endif; ?>
  </tbody>
</table>
