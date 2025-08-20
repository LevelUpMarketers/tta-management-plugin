<?php
// includes/admin/views/tickets-edit.php
global $wpdb, $ticket;

$tickets_table  = $wpdb->prefix . 'tta_tickets';
$waitlist_table = $wpdb->prefix . 'tta_waitlist';

// 1) Fetch all tickets for this event:
$event_ute_id = esc_sql( $ticket['event_ute_id'] );
$event_id     = (int) $wpdb->get_var(
    $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s UNION SELECT id FROM {$wpdb->prefix}tta_events_archive WHERE ute_id = %s LIMIT 1",
        $event_ute_id,
        $event_ute_id
    )
);
$tickets = $wpdb->get_results(
    "SELECT * FROM {$tickets_table} WHERE event_ute_id = '{$event_ute_id}' ORDER BY id ASC",
    ARRAY_A
);
?>

<form id="tta-ticket-edit-form">
  <?php wp_nonce_field( 'tta_ticket_save_action', 'tta_ticket_save_nonce' ); ?>

  <!-- ensure event ID is sent along -->
  <input type="hidden" name="event_ute_id" value="<?php echo esc_attr( $event_ute_id ); ?>">

  <?php foreach ( $tickets as $key => $t ) :
    $tid = intval( $t['id'] );

    // Pull waitlist entries for this ticket
    $waitlist_entries = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM {$waitlist_table} WHERE ticket_id = %d ORDER BY added_at ASC",
        $tid
      ),
      ARRAY_A
    );

    // Fetch confirmed attendees for this ticket
    $attendees      = tta_get_ticket_attendees( $tid );
    usort( $attendees, static function ( $a, $b ) {
        return strtotime( $b['created_at'] ) - strtotime( $a['created_at'] );
    } );
    $att_count      = count( $attendees );

    $pending_refs   = tta_get_ticket_pending_refund_attendees( $tid, $event_id );
    usort( $pending_refs, static function ( $a, $b ) {
        return strtotime( $b['created_at'] ) - strtotime( $a['created_at'] );
    } );
    $pending_count  = count( $pending_refs );

    $refunded_attendees = tta_get_ticket_refunded_attendees( $tid, $event_id );
    usort( $refunded_attendees, static function ( $a, $b ) {
        return strtotime( $b['created_at'] ) - strtotime( $a['created_at'] );
    } );
    $refunded_count  = count( $refunded_attendees );

    $waitlist_count = count( $waitlist_entries );

  ?>
    <section
      class="tta-ticket-row"
      data-ticket-id="<?php echo esc_attr( $tid ); ?>"
      style="position:relative;border:1px solid #ddd;padding:1em;margin-bottom:1em;"
    >
      <h3 style="margin:0;">
        <?php echo esc_html_e( ' Ticket: ', 'tta' ); echo esc_html( $t['ticket_name'] ); ?>


       <?php if ( 0 !== $key ) : ?>
        <button type="button"
                class="tta-delete-ticket"
                data-ticket-id="<?php echo esc_attr( $tid ); ?>"
                style="position:absolute; right:0.5em; background:none; border:none; cursor:pointer;">
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/bin.svg' ); ?>"
                 alt="<?php esc_attr_e( 'Delete ticket', 'tta' ); ?>">
        </button>
      <?php endif; ?>


      </h3>

      <table class="form-table">
        <tr>
          <th>
            <label for="event_name_<?php echo $tid; ?>"><?php esc_html_e( 'Ticket Name', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Name that appears on the ticket.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td>
            <input type="text"
                   name="event_name[<?php echo $tid; ?>]"
                   id="event_name_<?php echo $tid; ?>"
                   class="regular-text"
                   value="<?php echo esc_attr( $t['ticket_name'] ); ?>">
          </td>
        </tr>
        <tr>
          <th>
            <label for="ticketlimit_<?php echo $tid; ?>"><?php esc_html_e( 'Ticket Limit', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Maximum number of tickets available.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td>
            <input type="number"
                   name="ticketlimit[<?php echo $tid; ?>]"
                   id="ticketlimit_<?php echo $tid; ?>"
                   value="<?php echo esc_attr( $t['ticketlimit'] ); ?>">
          </td>
        </tr>
        <tr>
          <th>
            <label for="memberlimit_<?php echo $tid; ?>"><?php esc_html_e( 'Per Member Limit', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Maximum quantity each member can purchase.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td>
            <input type="number"
                   name="memberlimit[<?php echo $tid; ?>]"
                   id="memberlimit_<?php echo $tid; ?>"
                   value="<?php echo esc_attr( $t['memberlimit'] ?? 2 ); ?>">
          </td>
        </tr>
        <tr>
          <th>
            <label for="baseeventcost_<?php echo $tid; ?>"><?php esc_html_e( 'Base Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Standard ticket price.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td>
            <input type="number"
                   name="baseeventcost[<?php echo $tid; ?>]"
                   id="baseeventcost_<?php echo $tid; ?>"
                   step="0.01"
                   value="<?php echo esc_attr( $t['baseeventcost'] ); ?>">
          </td>
        </tr>
        <tr>
          <th>
            <label for="discountedmembercost_<?php echo $tid; ?>"><?php esc_html_e( 'Member Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Discounted price for members.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td>
            <input type="number"
                   name="discountedmembercost[<?php echo $tid; ?>]"
                   id="discountedmembercost_<?php echo $tid; ?>"
                   step="0.01"
                   value="<?php echo esc_attr( $t['discountedmembercost'] ); ?>">
          </td>
        </tr>
        <tr>
          <th>
            <label for="premiummembercost_<?php echo $tid; ?>"><?php esc_html_e( 'Premium Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Special price for premium members.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td>
            <input type="number"
                   name="premiummembercost[<?php echo $tid; ?>]"
                   id="premiummembercost_<?php echo $tid; ?>"
                   step="0.01"
                   value="<?php echo esc_attr( $t['premiummembercost'] ); ?>">
          </td>
        </tr>
      </table>

      <details class="tta-ticket-waitlist">
        <summary>
          <?php esc_html_e( 'Waitlist Entries', 'tta' ); ?>
          <span class="tta-tooltip-icon"
                data-tooltip="<?php esc_attr_e( 'People currently on the waitlist.', 'tta' ); ?>">
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                 alt="Help">
          </span>
          (<?php echo $waitlist_count; ?>)
        </summary>

        <?php if ( $waitlist_entries ) : ?>
            <div class="tta-wl-info-wrapper">
              <table class="tta-wl-info-table">
                <thead>
                  <tr>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Waitlist member name.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Name', 'tta' ); ?>
                    </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Waitlist member email address.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Email', 'tta' ); ?>
                    </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Phone number provided on the waitlist form.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Phone', 'tta' ); ?>
                    </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Current membership level.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Membership Level', 'tta' ); ?>
                    </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'When they joined the waitlist.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Date & Time Joined', 'tta' ); ?>
                    </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Available actions for the attendee.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Actions', 'tta' ); ?>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ( $waitlist_entries as $entry ) :
                    $uid   = intval( $entry['wp_user_id'] );
                    $name  = trim( $entry['first_name'] . ' ' . $entry['last_name'] );
                    $email = $entry['email'];
                    $phone = $entry['phone'];
                    $level_slug  = tta_get_user_membership_level( $uid );
                    $level_label = tta_get_membership_label( $level_slug );
                    $joined      = $entry['added_at'] ? mysql2date( 'n/j/Y g:i a', $entry['added_at'] ) : '';
                  ?>
                  <tr data-waitlist-id="<?php echo esc_attr( $entry['id'] ); ?>">
                    <td><?php echo esc_html( $name ); ?></td>
                    <td><?php echo esc_html( $email ); ?></td>
                    <td><?php echo esc_html( $phone ); ?></td>
                    <td><?php echo esc_html( $level_label ); ?></td>
                    <td><?php echo esc_html( $joined ); ?></td>
                    <td>
                      <button type="button" class="tta-remove-waitlist-entry" data-waitlist-id="<?php echo esc_attr( $entry['id'] ); ?>">
                        <?php esc_html_e( 'Remove', 'tta' ); ?>
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else : ?>
          <p class="no-waitlist"><?php esc_html_e( 'No waitlist entries.', 'tta' ); ?></p>
        <?php endif; ?>
      </details>

      <details class="tta-ticket-attendees">
        <summary>
          <?php esc_html_e( 'Refunded Attendees', 'tta' ); ?>
          <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Members who were refunded for this ticket.', 'tta' ); ?>">
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
          </span>
          (<?php echo $refunded_count; ?>)
        </summary>
        <?php if ( $refunded_attendees ) : ?>
          <div class="tta-wl-info-wrapper">
            <table class="tta-wl-info-table">
              <thead>
                <tr>
                  <th><span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Attendee first and last name.', 'tta' ); ?>"><img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?"></span><?php esc_html_e( 'Name', 'tta' ); ?></th>
                  <th><span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Attendee contact info.', 'tta' ); ?>"><img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?"></span><?php esc_html_e( 'Email & Phone', 'tta' ); ?></th>
                  <th><span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Reason provided with the refund request.', 'tta' ); ?>"><img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?"></span><?php esc_html_e( 'Note', 'tta' ); ?></th>
                  <th><span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Amount refunded for this ticket.', 'tta' ); ?>"><img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?"></span><?php esc_html_e( 'Refunded', 'tta' ); ?></th>
                  <th><span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Gateway transaction ID.', 'tta' ); ?>"><img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?"></span><?php esc_html_e( 'Transaction ID', 'tta' ); ?></th>
                  <th><span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Whether this attendee made the purchase.', 'tta' ); ?>"><img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?"></span><?php esc_html_e( 'Purchaser', 'tta' ); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php $last_gate = ''; foreach ( $refunded_attendees as $a ) : ?>
                  <?php
                  if ( $a['gateway_id'] !== $last_gate ) :
                    $last_gate = $a['gateway_id'];
                    $txid   = $a['gateway_id'] ? ' - ' . __( 'Transaction ID', 'tta' ) . ' ' . $a['gateway_id'] : '';
                    $txdate = $a['created_at'] ? ' - ' . mysql2date( 'n/j/Y g:i a', $a['created_at'] ) : '';
                  ?>
                    <tr class="tta-transaction-group">
                      <td colspan="6" style="background:#f9f9f9;font-weight:bold;">
                        <?php echo esc_html( sprintf( __( 'Refund%s%s', 'tta' ), $txid, $txdate ) ); ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                  <tr>
                    <td><?php echo esc_html( trim( $a['first_name'] . ' ' . $a['last_name'] ) ); ?></td>
                    <td><?php echo esc_html( $a['email'] ); ?><?php echo $a['phone'] ? '<br>' . esc_html( $a['phone'] ) : ''; ?></td>
                    <td><?php echo esc_html( $a['reason'] ); ?></td>
                    <td><?php echo sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $a['amount_paid'], 2 ) ); ?></td>
                    <td><?php echo esc_html( $a['gateway_id'] ); ?></td>
                    <td><?php echo ! empty( $a['is_purchaser'] ) ? esc_html__( 'Yes', 'tta' ) : esc_html__( 'No', 'tta' ); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else : ?>
          <p class="no-waitlist"><?php esc_html_e( 'No refunded attendees.', 'tta' ); ?></p>
        <?php endif; ?>
      </details>

      <details class="tta-ticket-attendees">
        <summary>
          <?php esc_html_e( 'Verified Attendees', 'tta' ); ?>
          <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'People who purchased this ticket.', 'tta' ); ?>">
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
          </span>
          (<?php echo $att_count; ?>)
        </summary>
        <?php if ( $attendees ) : ?>
          <div class="tta-wl-info-wrapper">
            <table class="tta-wl-info-table">
              <thead>
                <tr>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Attendee first and last name.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Name', 'tta' ); ?>
                  </th>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Attendee email address.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Email', 'tta' ); ?>
                  </th>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Phone number provided at checkout.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Phone', 'tta' ); ?>
                  </th>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Amount charged for this ticket.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Paid', 'tta' ); ?>
                  </th>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Whether this attendee made the purchase.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Purchaser', 'tta' ); ?>
                  </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Specify a partial refund amount.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Refund $', 'tta' ); ?>
                    </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Available actions for the attendee.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Actions', 'tta' ); ?>
                    </th>
                </tr>
              </thead>
              <tbody>
                <?php $last_tx = 0; foreach ( $attendees as $a ) : ?>
                  <?php
                  $txn_id = intval( $a['transaction_id'] );
                  if ( $txn_id !== $last_tx ) :
                    $last_tx = $txn_id;
                    $txid    = $a['gateway_id'] ? ' - ' . __( 'Transaction ID', 'tta' ) . ' ' . $a['gateway_id'] : '';
                    $txdate  = $a['created_at'] ? ' - ' . mysql2date( 'n/j/Y g:i a', $a['created_at'] ) : '';
                  ?>
                    <tr class="tta-transaction-group">
                      <td colspan="8" style="background:#f9f9f9;font-weight:bold;">
                        <?php echo esc_html( sprintf( __( 'Transaction #%d%s%s', 'tta' ), $txn_id, $txid, $txdate ) ); ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                  <?php
                  $name  = trim( $a['first_name'] . ' ' . $a['last_name'] );
                  $email = $a['email'];
                  $phone = $a['phone'];
                  $paid  = floatval( $a['amount_paid'] );
                  ?>
                  <?php
                  $pending_ref   = tta_get_refund_request( $a['gateway_id'], intval( $a['ticket_id'] ), intval( $a['id'] ) );
                  $cancel_classes = 'tta-refund-cancel-attendee';
                  $cancel_extra   = '';
                  $keep_classes   = 'tta-refund-keep-attendee';
                  $keep_extra     = '';
                  if ( $pending_ref ) {
                      $cancel_classes .= ' tta-disabled tta-tooltip-trigger';
                      $cancel_extra    = ' disabled="disabled" data-tooltip="' . esc_attr__( 'Refund scheduled after settlement.', 'tta' ) . '"';
                      $keep_classes   .= ' tta-disabled tta-tooltip-trigger';
                      $keep_extra      = ' disabled="disabled" data-tooltip="' . esc_attr__( 'Refund scheduled after settlement.', 'tta' ) . '"';
                  }
                  ?>
                  <tr data-attendee-id="<?php echo esc_attr( $a['id'] ); ?>">
                    <td><?php echo esc_html( $name ); ?></td>
                    <td><?php echo esc_html( $email ); ?></td>
                    <td><?php echo esc_html( $phone ); ?></td>
                    <td><?php echo $paid ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $paid, 2 ) ) : '&ndash;'; ?></td>
                    <td><?php echo ! empty( $a['is_purchaser'] ) ? esc_html__( 'Yes', 'tta' ) : esc_html__( 'No', 'tta' ); ?></td>
                    <td>
                      <input type="number" class="tta-refund-amount" step="0.01" style="width:70px" placeholder="<?php esc_attr_e( 'Full', 'tta' ); ?>">
                    </td>
                    <td>
                      <button type="button" class="<?php echo esc_attr( $cancel_classes ); ?>" data-attendee="<?php echo esc_attr( $a['id'] ); ?>"<?php echo $cancel_extra; ?>>
                        <?php esc_html_e( 'Refund & Cancel Attendance', 'tta' ); ?>
                      </button>
                      <button type="button" class="<?php echo esc_attr( $keep_classes ); ?>" data-attendee="<?php echo esc_attr( $a['id'] ); ?>"<?php echo $keep_extra; ?>>
                        <?php esc_html_e( 'Refund & Keep Attendance', 'tta' ); ?>
                      </button>
                      <button type="button" class="tta-cancel-attendee" data-attendee="<?php echo esc_attr( $a['id'] ); ?>">
                        <?php esc_html_e( 'Cancel Attendance (No Refund)', 'tta' ); ?>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else : ?>
          <p class="no-waitlist"><?php esc_html_e( 'No attendees.', 'tta' ); ?></p>
        <?php endif; ?>
      </details>

      <details class="tta-ticket-attendees">
        <summary>
          <?php esc_html_e( 'Attendees With Pending Refund Requests', 'tta' ); ?>
          <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Members who cancelled and await refund.', 'tta' ); ?>">
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
          </span>
          (<?php echo $pending_count; ?>)
        </summary>
        <?php if ( $pending_refs ) : ?>
          <div class="tta-wl-info-wrapper">
            <table class="tta-wl-info-table">
              <thead>
                <tr>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Attendee first and last name.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Name', 'tta' ); ?>
                  </th>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Attendee contact info.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Email & Phone', 'tta' ); ?>
                  </th>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Reason provided with the refund request.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Note', 'tta' ); ?>
                  </th>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Amount charged for this ticket.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Paid', 'tta' ); ?>
                  </th>
                  <th>
                    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Whether this attendee made the purchase.', 'tta' ); ?>">
                      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                    </span>
                    <?php esc_html_e( 'Purchaser', 'tta' ); ?>
                  </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Specify a partial refund amount.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Refund $', 'tta' ); ?>
                    </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Reason this refund has not yet been issued.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Pending Reason', 'tta' ); ?>
                    </th>
                    <th>
                      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Available actions for the attendee.', 'tta' ); ?>">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="?">
                      </span>
                      <?php esc_html_e( 'Actions', 'tta' ); ?>
                    </th>
                </tr>
              </thead>
              <tbody>
                <?php $last_gate = ''; foreach ( $pending_refs as $a ) : ?>
                  <?php
                  if ( $a['gateway_id'] !== $last_gate ) :
                    $last_gate = $a['gateway_id'];
                    $txid   = $a['gateway_id'] ? ' - ' . __( 'Transaction ID', 'tta' ) . ' ' . $a['gateway_id'] : '';
                    $txdate = $a['created_at'] ? ' - ' . mysql2date( 'n/j/Y g:i a', $a['created_at'] ) : '';
                  ?>
                      <tr class="tta-transaction-group">
                        <td colspan="8" style="background:#f9f9f9;font-weight:bold;">
                        <?php echo esc_html( sprintf( __( 'Pending Refund%s%s', 'tta' ), $txid, $txdate ) ); ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                  <?php
                    $name  = trim( $a['first_name'] . ' ' . $a['last_name'] );
                    $email  = $a['email'];
                    $phone  = $a['phone'];
                    $reason = $a['reason'];
                    if ( '' === $reason && ! empty( $a['mode'] ) ) {
                        if ( 'cancel' === $a['mode'] ) {
                            $reason = __( 'Admin manually issued a "Refund & Cancel Attendance" request. Waiting for transaction to settle.', 'tta' );
                        } else {
                            $reason = __( 'Admin manually issued a "Refund & Keep Attendance" request. Waiting for transaction to settle.', 'tta' );
                        }
                    }
                    $paid     = floatval( $a['amount_paid'] );
                    $stock    = tta_get_ticket_stock( $tid );
                    $released = tta_get_released_refund_count( $tid );
                    $waitlist = tta_ticket_has_waitlist_entries( $tid );
                    if ( 'settlement' === ( $a['pending_reason'] ?? '' ) ) {
                        $pending_reason = __( 'Waiting for transaction to settle', 'tta' );
                    } elseif ( $released > 0 && $stock > 0 ) {
                        $pending_reason = __( 'Up for sale - waiting to be purchased', 'tta' );
                    } elseif ( $waitlist ) {
                        $pending_reason = __( 'Ticket has yet to sell out. Those on the waitlist have been notified that a ticket is available', 'tta' );
                    } else {
                        $pending_reason = __( 'Ticket has yet to sell out', 'tta' );
                    }

                    $is_settlement       = ( 'settlement' === ( $a['pending_reason'] ?? '' ) );
                    $cancel_btn_classes  = 'tta-refund-request-process';
                    $cancel_btn_extra    = '';
                    $keep_btn_classes    = 'tta-refund-request-process';
                    $keep_btn_extra      = '';
                    $delete_btn_classes  = 'tta-refund-request-delete';
                    $delete_btn_extra    = '';
                    if ( $is_settlement ) {
                        $tooltip            = esc_attr__( 'Refund scheduled after settlement', 'tta' );
                        $cancel_btn_classes .= ' tta-disabled tta-tooltip-trigger';
                        $cancel_btn_extra    = ' disabled="disabled" data-tooltip="' . $tooltip . '"';
                        $keep_btn_classes   .= ' tta-disabled tta-tooltip-trigger';
                        $keep_btn_extra      = ' disabled="disabled" data-tooltip="' . $tooltip . '"';
                        $delete_btn_classes .= ' tta-disabled tta-tooltip-trigger';
                        $delete_btn_extra    = ' disabled="disabled" data-tooltip="' . $tooltip . '"';
                    }
                    ?>
                    <tr data-request data-tx="<?php echo esc_attr( $a['gateway_id'] ); ?>" data-ticket="<?php echo esc_attr( $tid ); ?>" data-event="<?php echo esc_attr( $event_id ); ?>">
                      <td><?php echo esc_html( $name ); ?></td>
                      <td><?php echo esc_html( $email ); ?><?php echo $phone ? '<br>' . esc_html( $phone ) : ''; ?></td>
                      <td><?php echo esc_html( $reason ); ?></td>
                      <td><?php echo $paid ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $paid, 2 ) ) : '&ndash;'; ?></td>
                      <td><?php echo ! empty( $a['is_purchaser'] ) ? esc_html__( 'Yes', 'tta' ) : esc_html__( 'No', 'tta' ); ?></td>
                      <td>
                        <input type="number" class="tta-refund-amount" step="0.01" style="width:70px" placeholder="<?php esc_attr_e( 'Full', 'tta' ); ?>">
                      </td>
                      <td><?php echo esc_html( $pending_reason ); ?></td>
                      <td>
                        <button type="button" class="<?php echo esc_attr( $cancel_btn_classes ); ?>" data-mode="cancel" data-tx="<?php echo esc_attr( $a['gateway_id'] ); ?>" data-ticket="<?php echo esc_attr( $tid ); ?>"<?php echo $cancel_btn_extra; ?>>
                          <?php esc_html_e( 'Refund & Cancel Attendance', 'tta' ); ?>
                        </button>
                      <button type="button" class="<?php echo esc_attr( $keep_btn_classes ); ?>" data-mode="keep" data-tx="<?php echo esc_attr( $a['gateway_id'] ); ?>" data-ticket="<?php echo esc_attr( $tid ); ?>"<?php echo $keep_btn_extra; ?>>
                        <?php esc_html_e( 'Refund & Keep Attendance', 'tta' ); ?>
                      </button>
                      <button type="button" class="<?php echo esc_attr( $delete_btn_classes ); ?>" data-tx="<?php echo esc_attr( $a['gateway_id'] ); ?>" data-ticket="<?php echo esc_attr( $tid ); ?>"<?php echo $delete_btn_extra; ?>>
                        <?php esc_html_e( 'Cancel Attendance (No Refund)', 'tta' ); ?>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else : ?>
          <p class="no-waitlist"><?php esc_html_e( 'No pending refund requests.', 'tta' ); ?></p>
        <?php endif; ?>
      </details>



    </section>
  <?php endforeach; ?>

  <!-- blank “new ticket” template -->
  <template id="tta-new-ticket-template">
    <section class="tta-ticket-row"
             style="position:relative;border:1px solid #ddd;padding:1em;margin-bottom:1em;"
             data-ticket-id="">
      <h3 style="margin:0;">
        <?php esc_html_e( 'New Ticket', 'tta' ); ?>
        <button type="button" class="tta-delete-new-ticket">
          <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/bin.svg' ); ?>"
               alt="<?php esc_attr_e( 'Remove ticket', 'tta' ); ?>">
        </button>
      </h3>

      <table class="form-table">
        <tr>
          <th>
            <label><?php esc_html_e( 'Ticket Name', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Name that appears on the ticket.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td>
            <input type="text" name="new_event_name[]" class="regular-text" value="">
          </td>
        </tr>
        <tr>
          <th>
            <label><?php esc_html_e( 'Ticket Limit', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Maximum number of tickets available.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td><input type="number" name="new_ticketlimit[]" value="10000"></td>
        </tr>
        <tr>
          <th>
            <label><?php esc_html_e( 'Per Member Limit', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Maximum quantity each member can purchase.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td><input type="number" name="new_memberlimit[]" value="2"></td>
        </tr>
        <tr>
          <th>
            <label><?php esc_html_e( 'Base Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Standard ticket price.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td><input type="number" name="new_baseeventcost[]" step="0.01" value="0.00"></td>
        </tr>
        <tr>
          <th>
            <label><?php esc_html_e( 'Member Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Discounted price for members.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td><input type="number" name="new_discountedmembercost[]" step="0.01" value="0.00"></td>
        </tr>
        <tr>
          <th>
            <label><?php esc_html_e( 'Premium Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon"
                  data-tooltip="<?php esc_attr_e( 'Special price for premium members.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                   alt="Help">
            </span>
          </th>
          <td><input type="number" name="new_premiummembercost[]" step="0.01" value="0.00"></td>
        </tr>
      </table>

      <details class="tta-ticket-waitlist">
        <summary>
          <?php esc_html_e( 'Waitlist Entries', 'tta' ); ?>
          <span class="tta-tooltip-icon"
                data-tooltip="<?php esc_attr_e( 'People currently on the waitlist.', 'tta' ); ?>">
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                 alt="Help">
          </span>
          (0)
        </summary>
        <p class="no-waitlist"><?php esc_html_e( 'No waitlist entries.', 'tta' ); ?></p>
      </details>
    </section>
  </template>

  <p class="submit">
    <button type="submit" class="button button-primary">
      <?php esc_html_e( 'Save All Tickets', 'tta' ); ?>
    </button>
    <button type="button"
            id="add-new-ticket"
            class="button"
            data-event-ute-id="<?php echo esc_attr( $event_ute_id ); ?>">
      <?php esc_html_e( 'Add New Ticket', 'tta' ); ?>
    </button>
    <div class="tta-admin-progress-spinner-div">
        <img class="tta-admin-progress-spinner-svg" src="http://trying-to-adult-rva-2025.local/wp-content/plugins/tta-management-plugin/assets/images/admin/loading.svg" alt="Loading…" style="display:none; opacity:0;">
    </div>
    <div class="tta-admin-progress-response-div">
        <p class="tta-admin-progress-response-p"></p>
    </div>
  </p>
</form>
