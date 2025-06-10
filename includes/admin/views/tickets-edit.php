<?php
// includes/admin/views/tickets-edit.php
global $wpdb;

$tickets_table  = $wpdb->prefix . 'tta_tickets';
$waitlist_table = $wpdb->prefix . 'tta_waitlist';

// 1) Fetch all tickets for this event:
$event_ute_id = esc_sql( $ticket['event_ute_id'] );
$tickets = $wpdb->get_results(
    "SELECT * FROM {$tickets_table} WHERE event_ute_id = '{$event_ute_id}' ORDER BY id ASC",
    ARRAY_A
);
?>

<form id="tta-ticket-edit-form">
  <?php wp_nonce_field( 'tta_ticket_save_action', 'tta_ticket_save_nonce' ); ?>

  <?php foreach ( $tickets as $t ) :
    $tid = intval( $t['id'] );
    $waitlist_entries = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM {$waitlist_table} WHERE ticket_id = %d",
        $tid
      ),
      ARRAY_A
    );
  ?>
    <section class="tta-ticket-row" style="border:1px solid #ddd;padding:1em;margin-bottom:1em;">
      <h3>
        <?php esc_html_e( 'Ticket #', 'tta' ); echo esc_html( $tid ); ?>
      </h3>
      <table class="form-table">
        <tr>
          <th>
            <label for="event_name_<?php echo $tid; ?>"><?php esc_html_e( 'Ticket Name', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Name that appears on the ticket.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td>
            <input
              type="text"
              name="event_name[<?php echo $tid; ?>]"
              id="event_name_<?php echo $tid; ?>"
              class="regular-text"
              value="<?php echo esc_attr( $t['event_name'] ); ?>"
            >
          </td>
        </tr>
        <tr>
          <th>
            <label for="attendancelimit_<?php echo $tid; ?>"><?php esc_html_e( 'Attendance Limit', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Maximum number of tickets available.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td>
            <input
              type="number"
              name="attendancelimit[<?php echo $tid; ?>]"
              id="attendancelimit_<?php echo $tid; ?>"
              value="<?php echo esc_attr( $t['attendancelimit'] ); ?>"
            >
          </td>
        </tr>
        <tr>
          <th>
            <label for="baseeventcost_<?php echo $tid; ?>"><?php esc_html_e( 'Base Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Standard ticket price.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td>
            <input
              type="number"
              name="baseeventcost[<?php echo $tid; ?>]"
              id="baseeventcost_<?php echo $tid; ?>"
              step="0.01"
              value="<?php echo esc_attr( $t['baseeventcost'] ); ?>"
            >
          </td>
        </tr>
        <tr>
          <th>
            <label for="discountedmembercost_<?php echo $tid; ?>"><?php esc_html_e( 'Member Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Discounted price for members.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td>
            <input
              type="number"
              name="discountedmembercost[<?php echo $tid; ?>]"
              id="discountedmembercost_<?php echo $tid; ?>"
              step="0.01"
              value="<?php echo esc_attr( $t['discountedmembercost'] ); ?>"
            >
          </td>
        </tr>
        <tr>
          <th>
            <label for="premiummembercost_<?php echo $tid; ?>"><?php esc_html_e( 'Premium Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Special price for premium members.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td>
            <input
              type="number"
              name="premiummembercost[<?php echo $tid; ?>]"
              id="premiummembercost_<?php echo $tid; ?>"
              step="0.01"
              value="<?php echo esc_attr( $t['premiummembercost'] ); ?>"
            >
          </td>
        </tr>
      </table>

      <details class="tta-ticket-waitlist">
        <summary>
          <?php esc_html_e( 'Waitlist Entries', 'tta' ); ?>
          <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'People currently on the waitlist.', 'tta' ); ?>">
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
          </span>
          (<?php echo count( $waitlist_entries ); ?>)
        </summary>

        <?php if ( $waitlist_entries ) : ?>
          <ul style="margin-top:.5em;padding-left:1.5em;">
            <?php foreach ( $waitlist_entries as $w ) :
              $uids = array_filter( array_map( 'intval', explode( ',', $w['userids'] ) ) );
              foreach ( $uids as $uid ) :
                $user = get_userdata( $uid );
                $name = $user ? $user->display_name : sprintf( '#%d', $uid );
            ?>
              <li><?php echo esc_html( $name ); ?></li>
            <?php endforeach; endforeach; ?>
          </ul>
        <?php else : ?>
          <p style="margin-top:.5em;"><?php esc_html_e( 'No waitlist entries.', 'tta' ); ?></p>
        <?php endif; ?>
      </details>

    </section>
  <?php endforeach; ?>

  <!-- blank “new ticket” template -->
  <template id="tta-new-ticket-template">
    <section class="tta-ticket-row" style="border:1px solid #ddd;padding:1em;margin-bottom:1em;">
      <h3><?php esc_html_e( 'New Ticket', 'tta' ); ?></h3>
      <table class="form-table">
        <tr>
          <th>
            <label><?php esc_html_e( 'Ticket Name', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Name that appears on the ticket.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td><input type="text" name="new_event_name[]" class="regular-text" value=""></td>
        </tr>
        <tr>
          <th>
            <label><?php esc_html_e( 'Attendance Limit', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Maximum number of tickets available.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td><input type="number" name="new_attendancelimit[]" value="0"></td>
        </tr>
        <tr>
          <th>
            <label><?php esc_html_e( 'Base Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Standard ticket price.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td><input type="number" name="new_baseeventcost[]" step="0.01" value="0.00"></td>
        </tr>
        <tr>
          <th>
            <label><?php esc_html_e( 'Member Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Discounted price for members.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td><input type="number" name="new_discountedmembercost[]" step="0.01" value="0.00"></td>
        </tr>
        <tr>
          <th>
            <label><?php esc_html_e( 'Premium Cost', 'tta' ); ?></label>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Special price for premium members.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td><input type="number" name="new_premiummembercost[]" step="0.01" value="0.00"></td>
        </tr>
      </table>
      <details class="tta-ticket-waitlist">
        <summary>
          <?php esc_html_e( 'Waitlist Entries', 'tta' ); ?>
          <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'People currently on the waitlist.', 'tta' ); ?>">
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
          </span>
          (0)
        </summary>
        <p style="margin-top:.5em;"><?php esc_html_e( 'No waitlist entries.', 'tta' ); ?></p>
      </details>
    </section>
  </template>

  <p class="submit">
    <button type="submit" class="button button-primary">
      <?php esc_html_e( 'Save All Tickets', 'tta' ); ?>
    </button>
    <button
      type="button"
      id="add-new-ticket"
      class="button"
      data-event-ute-id="<?php echo esc_attr( $event_ute_id ); ?>"
    >
      <?php esc_html_e( 'Add New Ticket', 'tta' ); ?>
    </button>
    <span class="tta-admin-progress-spinner-svg" style="display:none;opacity:0;"></span>
    <p class="tta-admin-progress-response-p"></p>
  </p>
</form>

