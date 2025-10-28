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
<div class="tta-no-show-actions">
  <button type="button" class="button tta-mark-all-no-show" data-event-ute-id="<?php echo esc_attr( $event['ute_id'] ); ?>"><?php esc_html_e( 'Mark all Pending as No-Shows', 'tta' ); ?></button>
  <span class="tta-progress-spinner"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" /></span>
  <span class="tta-admin-progress-response-p"></span>
</div>
<?php
$textarea_id   = 'tta-email-message-' . sanitize_key( $event['ute_id'] );
$instructions  = __( 'Need to send Attendees an update? Type your message below. <span style="font-weight:bold;color:red;">DO NOT INCLUDE</span> any opening or closing statements, such as <strong>"Hi there,"</strong> or <strong>"See you soon!"</strong> or <strong>"Thanks!"</strong>, as these will be included automatically. Simply type the message you want sent below. When you click the "Send Email" button, an email will be sent to ALL Attendees - regardless of attendance status - as well as Event Hosts & Volunteers.', 'tta' );
?>
<div class="tta-email-attendees">
  <h4><?php esc_html_e( 'Email All Attendees', 'tta' ); ?></h4>
  <p><?php echo wp_kses( $instructions, array( 'span' => array( 'style' => array() ), 'strong' => array() ) ); ?></p>
  <textarea id="<?php echo esc_attr( $textarea_id ); ?>" class="widefat tta-email-attendees__message" rows="6" placeholder="<?php esc_attr_e( 'Type your message here…', 'tta' ); ?>"></textarea>
  <div class="tta-email-attendees__actions">
    <button type="button" class="button button-primary tta-email-attendees__send disabled" data-event-ute-id="<?php echo esc_attr( $event['ute_id'] ); ?>" disabled="disabled"><?php esc_html_e( 'Send Email', 'tta' ); ?></button>
    <span class="tta-progress-spinner"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" /></span>
    <span class="tta-admin-progress-response-p"></span>
  </div>
</div>
<?php
$assist_notes = array_filter(
    $attendees,
    function ( $a ) {
        return '-' !== $a['assistance_note'];
    }
);
if ( $assist_notes ) : ?>
<div class="tta-assistance-list">
  <ul>
    <?php foreach ( $assist_notes as $note ) : ?>
    <li><?php echo esc_html( $note['assistance_note'] ); ?> - <?php echo esc_html( $note['first_name'] . ' ' . $note['last_name'] ); ?> - <?php echo esc_html( $note['phone'] ); ?> - <?php echo esc_html( $note['email'] ); ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>
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
    foreach ( $attendees as $a ) :
        $row_class = '-' !== $a['assistance_note'] ? ' class="tta-assistance-row"' : '';
    ?>
    <tr<?php echo $row_class; ?> data-attendee-id="<?php echo esc_attr( $a['id'] ); ?>">
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
