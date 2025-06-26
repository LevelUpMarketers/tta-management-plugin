<?php
// includes/admin/views/tickets-edit.php
global $wpdb, $ticket;

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

  <!-- ensure event ID is sent along -->
  <input type="hidden" name="event_ute_id" value="<?php echo esc_attr( $event_ute_id ); ?>">

  <?php foreach ( $tickets as $key => $t ) :
    $tid = intval( $t['id'] );

    // Pull waitlist entries for this ticket
    $waitlist_entries = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM {$waitlist_table} WHERE ticket_id = %d",
        $tid
      ),
      ARRAY_A
    );

    // Grab the raw CSV string (or empty)
    $existing_userids = $waitlist_entries[0]['userids'] ?? '';


    $existing_userids_for_count = trim( $existing_userids );

    // Fetch confirmed attendees for this ticket
    $attendees     = tta_get_ticket_attendees( $tid );
    $att_count     = count( $attendees );

    // 1) Is it empty?
    $is_empty = ( $existing_userids_for_count === '' );

    // 2) Does it contain a comma?
    $has_commas = ! $is_empty && strpos( $existing_userids_for_count, ',' ) !== false;

    // 3) Explode, filter, and count
    if ( $is_empty ) {
        $waitlist_count = 0;
    } else {
        // explode on commas
        $parts = explode( ',', $existing_userids_for_count );
        // trim each part and discard any empty strings
        $ids = array_filter( array_map( 'trim', $parts ), 'strlen' );
        $waitlist_count = count( $ids );
    }

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
          <?php
          $members_table = $wpdb->prefix . 'tta_members';
          foreach ( $waitlist_entries as $w ) :
            $uids = array_filter( array_map( 'intval', explode( ',', $w['userids'] ) ) );
            foreach ( $uids as $uid ) :
              $m = $wpdb->get_row(
                $wpdb->prepare(
                  "SELECT first_name, last_name, email, membership_level, member_type, phone, profileimgid
                   FROM {$members_table}
                   WHERE wpuserid = %d
                   LIMIT 1",
                  $uid
                ),
                ARRAY_A
              );
              $name  = $m
                ? ucwords( strtolower( trim( $m['first_name'] . ' ' . $m['last_name'] ) ) )
                : sprintf( '#%d', $uid );
              $email = $m['email'] ?? '';
              $level = $m['membership_level']
                ? ucfirst( strtolower( $m['membership_level'] ) )
                : '';
              $type  = $m['member_type']
                ? ucfirst( strtolower( $m['member_type'] ) )
                : '';
              $phone = $m['phone'] ?? '';
              $thumb = ! empty( $m['profileimgid'] )
                ? tta_admin_preview_image(
                    intval( $m['profileimgid'] ),
                    [50,50],
                    [ 'class' => 'tta-wl-thumb-img', 'alt' => esc_attr( $name ) ]
                  )
                : '<img src="'
                    . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' )
                    . '" class="tta-wl-thumb-img" alt="">';
          ?>
            <div class="tta-wl-entry" data-userid="<?php echo esc_attr( $uid ); ?>">
              <div class="tta-wl-thumb"><?php echo $thumb; ?></div>
              <div class="tta-wl-info-wrapper">
                <table class="tta-wl-info-table">
                  <thead>
                    <tr>
                      <th><?php esc_html_e( 'Name', 'tta' ); ?></th>
                      <th><?php esc_html_e( 'Email', 'tta' ); ?></th>
                      <th><?php esc_html_e( 'Phone', 'tta' ); ?></th>
                      <th><?php esc_html_e( 'Membership Type', 'tta' ); ?></th>
                      <th><?php esc_html_e( 'Membership Level', 'tta' ); ?></th>
                      <th><?php esc_html_e( 'Remove', 'tta' ); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><?php echo esc_html( $name ); ?></td>
                      <td><?php echo esc_html( $email ); ?></td>
                      <td><?php echo esc_html( $phone ); ?></td>
                      <td><?php echo esc_html( $type ); ?></td>
                      <td><?php echo esc_html( $level ); ?></td?>
                      <td>
                        <button type="button"
                                class="tta-remove-waitlist-entry"
                                data-userid="<?php echo esc_attr( $uid ); ?>">
                          <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/bin.svg' ); ?>"
                               alt="<?php esc_attr_e( 'Remove', 'tta' ); ?>">
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endforeach; endforeach; ?>
        <?php else : ?>
          <p class="no-waitlist"><?php esc_html_e( 'No waitlist entries.', 'tta' ); ?></p>
        <?php endif; ?>
      </details>

      <details class="tta-ticket-attendees">
        <summary>
          <?php esc_html_e( 'Attendees', 'tta' ); ?>
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
                <?php foreach ( $attendees as $a ) : ?>
                  <?php
                  $name  = trim( $a['first_name'] . ' ' . $a['last_name'] );
                  $email = $a['email'];
                  $phone = $a['phone'];
                  $paid  = floatval( $a['amount_paid'] );
                  ?>
                  <tr data-attendee-id="<?php echo esc_attr( $a['id'] ); ?>">
                    <td><?php echo esc_html( $name ); ?></td>
                    <td><?php echo esc_html( $email ); ?></td>
                    <td><?php echo esc_html( $phone ); ?></td>
                    <td><?php echo $paid ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $paid, 2 ) ) : '&ndash;'; ?></td>
                    <td>
                      <input type="number" class="tta-refund-amount" step="0.01" style="width:70px" placeholder="<?php esc_attr_e( 'Full', 'tta' ); ?>">
                    </td>
                    <td>
                      <button type="button" class="tta-refund-cancel-attendee" data-attendee="<?php echo esc_attr( $a['id'] ); ?>">
                        <?php esc_html_e( 'Refund & Cancel Attendance', 'tta' ); ?>
                      </button>
                      <button type="button" class="tta-refund-keep-attendee" data-attendee="<?php echo esc_attr( $a['id'] ); ?>">
                        <?php esc_html_e( 'Refund & Keep Attendance', 'tta' ); ?>
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

      <!-- preserve the CSV so update_ticket() sees it -->
      <input type="hidden"
            class="tta-hidden-waitlist"
            name="waitlist_userids[<?php echo $tid; ?>]"
            value="<?php echo esc_attr( $existing_userids ); ?>">

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
