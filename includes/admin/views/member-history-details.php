<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$member_id = intval( $_GET['member_id'] ?? 0 );
if ( ! $member_id ) { echo '<p>Missing member.</p>'; return; }

global $wpdb;
$members_table = $wpdb->prefix . 'tta_members';
$member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$members_table} WHERE id=%d", $member_id ), ARRAY_A );
if ( ! $member ) { echo '<p>Member not found.</p>'; return; }

$summary = tta_get_member_history_summary( $member_id );
$billing_history = tta_get_member_billing_history( $member['wpuserid'] );
?>
<div class="tta-member-history-details">
  <h3><?php echo esc_html( $member['first_name'] . ' ' . $member['last_name'] ); ?></h3>
  <p><?php echo esc_html( $member['email'] ); ?></p>
  <?php if ( ! empty( $member['notes'] ) ) : ?>
    <p class="tta-member-notes"><strong><?php esc_html_e( 'Notes:', 'tta' ); ?></strong> <?php echo esc_html( $member['notes'] ); ?></p>
  <?php endif; ?>
  <h4><?php esc_html_e( 'Member Summary', 'tta' ); ?></h4>
  <ul>
    <li><?php printf( esc_html__( 'Total Spent: $%s', 'tta' ), number_format( $summary['total_spent'], 2 ) ); ?></li>
    <li><?php printf( esc_html__( 'Events Attended: %d', 'tta' ), $summary['attended'] ); ?></li>
    <li><?php printf( esc_html__( 'No-Shows: %d', 'tta' ), $summary['no_show'] ); ?></li>
    <li><?php printf( esc_html__( 'Refund/Cancellation Requests: %d', 'tta' ), $summary['refunds'] + $summary['cancellations'] ); ?></li>
    <li><?php printf( esc_html__( 'Total Events Purchased: %d', 'tta' ), $summary['events'] ); ?></li>
  </ul>
  <?php if ( $billing_history ) : ?>
  <h4>
    <?php esc_html_e( 'Payment History', 'tta' ); ?>
    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Full record of all event purchases and membership payments.', 'tta' ); ?>">
      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
    </span>
  </h4>
    <table class="widefat striped tta-billing-history">
      <thead>
        <tr>
          <th><?php esc_html_e( 'Date', 'tta' ); ?></th>
          <th><?php esc_html_e( 'Item', 'tta' ); ?></th>
          <th><?php esc_html_e( 'Amount', 'tta' ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $billing_history as $row ) : ?>
          <tr>
            <td><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $row['date'] ) ) ); ?></td>
            <td>
              <?php if ( ! empty( $row['url'] ) ) : ?>
                <a href="<?php echo esc_url( $row['url'] ); ?>"><?php echo esc_html( $row['description'] ); ?></a>
              <?php else : ?>
                <?php echo esc_html( $row['description'] ); ?>
              <?php endif; ?>
            </td>
            <td>$<?php echo esc_html( number_format( $row['amount'], 2 ) ); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else : ?>
    <p><?php esc_html_e( 'No transactions found.', 'tta' ); ?></p>
  <?php endif; ?>

  <h4>
    <?php esc_html_e( 'Manage Subscription', 'tta' ); ?>
    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Update payment methods, cancel, reactivate or change membership level.', 'tta' ); ?>">
      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
    </span>
  </h4>

  <p class="description">
    <?php esc_html_e( 'Fill out the payment and billing fields when updating payment details, reactivating a subscription or changing levels. They are not required for cancellation.', 'tta' ); ?>
  </p>

  <div class="tta-subscription-forms">

  <form id="tta-admin-update-payment-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <?php esc_html_e( 'Update Payment Method', 'tta' ); ?>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Change the card and billing address used for the member\'s recurring payments.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
    </h5>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">
    <p>
      <label>
        <?php esc_html_e( 'Card Number', 'tta' ); ?><br />
        <input type="text" name="card_number" placeholder="&#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226;" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Expiration', 'tta' ); ?><br />
        <input type="text" class="tta-card-exp" name="exp_date" placeholder="MM/YY" required maxlength="5" pattern="\d{2}/\d{2}" inputmode="numeric" />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'CVC', 'tta' ); ?><br />
        <input type="text" name="card_cvc" placeholder="123" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Billing First Name', 'tta' ); ?><br />
        <input type="text" name="bill_first" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Billing Last Name', 'tta' ); ?><br />
        <input type="text" name="bill_last" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Street Address', 'tta' ); ?><br />
        <input type="text" name="bill_address" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'City', 'tta' ); ?><br />
        <input type="text" name="bill_city" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'State', 'tta' ); ?><br />
        <select name="bill_state">
          <?php foreach ( tta_get_us_states() as $abbr => $name ) : ?>
            <option value="<?php echo esc_attr( $abbr ); ?>"><?php echo esc_html( $name ); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'ZIP', 'tta' ); ?><br />
        <input type="text" name="bill_zip" required />
      </label>
    </p>
    <p class="submit">
      <button type="submit" class="button"><?php esc_html_e( 'Update Payment Method', 'tta' ); ?></button>
      <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
    </p>
  </form>

  <form id="tta-admin-cancel-subscription-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <?php esc_html_e( 'Cancel Subscription', 'tta' ); ?>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Immediately cancel the member\'s active subscription.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
    </h5>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">
    <p class="submit">
      <button type="submit" class="button"><?php esc_html_e( 'Cancel This Member\'s Subscription', 'tta' ); ?></button>
      <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
    </p>
  </form>

  <form id="tta-admin-reactivate-subscription-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <?php esc_html_e( 'Reactivate Subscription', 'tta' ); ?>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Restart billing for a cancelled member. A new subscription may be created if needed.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
    </h5>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">
    <p>
      <label>
        <?php esc_html_e( 'Monthly Amount', 'tta' ); ?><br />
        <input type="number" step="0.01" name="amount" value="<?php echo esc_attr( tta_get_membership_price( $member['membership_level'] ) ); ?>" />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Card Number', 'tta' ); ?><br />
        <input type="text" name="card_number" placeholder="&#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226;" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Expiration', 'tta' ); ?><br />
        <input type="text" class="tta-card-exp" name="exp_date" placeholder="MM/YY" required maxlength="5" pattern="\d{2}/\d{2}" inputmode="numeric" />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'CVC', 'tta' ); ?><br />
        <input type="text" name="card_cvc" placeholder="123" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Billing First Name', 'tta' ); ?><br />
        <input type="text" name="bill_first" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Billing Last Name', 'tta' ); ?><br />
        <input type="text" name="bill_last" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Street Address', 'tta' ); ?><br />
        <input type="text" name="bill_address" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'City', 'tta' ); ?><br />
        <input type="text" name="bill_city" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'State', 'tta' ); ?><br />
        <select name="bill_state">
          <?php foreach ( tta_get_us_states() as $abbr => $name ) : ?>
            <option value="<?php echo esc_attr( $abbr ); ?>"><?php echo esc_html( $name ); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'ZIP', 'tta' ); ?><br />
        <input type="text" name="bill_zip" required />
      </label>
    </p>
    <p class="submit">
      <button type="submit" class="button"><?php esc_html_e( 'Reactivate this Member\'s Subscription', 'tta' ); ?></button>
      <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
    </p>
  </form>

  <form id="tta-admin-change-level-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <?php esc_html_e( 'Change Membership Level', 'tta' ); ?>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Switch the member between Basic and Premium tiers starting on their next bill.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
    </h5>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">
    <p>
      <label>
        <?php esc_html_e( 'New Level', 'tta' ); ?><br />
        <select name="level">
          <option value="basic">Basic</option>
          <option value="premium">Premium</option>
        </select>
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Price Per Month', 'tta' ); ?><br />
        <input type="number" step="0.01" name="price" required />
      </label>
    </p>
    <p class="submit">
      <button type="submit" class="button"><?php esc_html_e( 'Update Membership Level', 'tta' ); ?></button>
      <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
    </p>
  </form>
  </div>

  <div id="tta-subscription-response" class="tta-admin-progress-response-div"><p class="tta-admin-progress-response-p"></p></div>
</div>
