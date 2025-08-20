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
  <h4>
    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Summary of spending, attendance and notes for this member.', 'tta' ); ?>">
      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
    </span>
    <?php esc_html_e( 'Member Summary', 'tta' ); ?>
  </h4>
  <table class="widefat striped">
    <thead>
      <tr>
        <th><?php esc_html_e( 'Total Spent', 'tta' ); ?></th>
        <th><?php esc_html_e( 'Events Attended', 'tta' ); ?></th>
        <th><?php esc_html_e( 'No-Shows', 'tta' ); ?></th>
        <th><?php esc_html_e( 'Refund/Cancellation Requests', 'tta' ); ?></th>
        <th><?php esc_html_e( 'Total Events Purchased', 'tta' ); ?></th>
        <th><?php esc_html_e( 'Email', 'tta' ); ?></th>
        <?php if ( ! empty( $member['notes'] ) ) : ?>
          <th><?php esc_html_e( 'Notes', 'tta' ); ?></th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>$<?php echo esc_html( number_format( $summary['total_spent'], 2 ) ); ?></td>
        <td><?php echo esc_html( $summary['attended'] ); ?></td>
        <td><?php echo esc_html( $summary['no_show'] ); ?></td>
        <td><?php echo esc_html( $summary['refunds'] + $summary['cancellations'] ); ?></td>
        <td><?php echo esc_html( $summary['events'] ); ?></td>
        <td><?php echo esc_html( $member['email'] ); ?></td>
        <?php if ( ! empty( $member['notes'] ) ) : ?>
          <td><?php echo esc_html( $member['notes'] ); ?></td>
        <?php endif; ?>
      </tr>
    </tbody>
  </table>
  <?php if ( $billing_history ) : ?>
  <h4>
    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Full record of all event purchases and membership payments.', 'tta' ); ?>">
      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
    </span>
    <?php esc_html_e( 'Payment History', 'tta' ); ?>
  </h4>
    <table class="widefat striped tta-billing-history">
      <thead>
        <tr>
          <th><?php esc_html_e( 'Date', 'tta' ); ?></th>
          <th><?php esc_html_e( 'Item', 'tta' ); ?></th>
          <th><?php esc_html_e( 'Amount', 'tta' ); ?></th>
          <th><?php esc_html_e( 'Transaction ID', 'tta' ); ?></th>
          <th><?php esc_html_e( 'Type', 'tta' ); ?></th>
          <th><?php esc_html_e( 'Payment Method', 'tta' ); ?></th>
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
            <td><?php echo esc_html( $row['transaction_id'] ?? '' ); ?></td>
            <td><?php echo esc_html( ucwords( $row['type'] ) ); ?></td>
            <td><?php echo esc_html( $row['method'] ); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else : ?>
    <p><?php esc_html_e( 'No transactions found.', 'tta' ); ?></p>
  <?php endif; ?>

  <h4>
    <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Update payment methods, cancel, reactivate or change membership level.', 'tta' ); ?>">
      <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
    </span>
    <?php esc_html_e( 'Manage Subscription', 'tta' ); ?>
  </h4>

  <div class="tta-subscription-info">
    <?php
      $level    = strtolower( $member['membership_level'] ?? 'free' );
      $status   = strtolower( $member['subscription_status'] ?? '' );
      $sub_id   = $member['subscription_id'] ?? '';
      $sub_info   = $sub_id ? tta_get_subscription_status_info( $sub_id ) : [];
      $last4      = $sub_info['last4'] ?? '';
      $cancel   = tta_get_last_membership_cancellation( $member['wpuserid'] );
      $prev_level = get_user_meta( $member['wpuserid'], 'tta_prev_level', true );
      if ( ! $prev_level && $cancel ) {
          $prev_level = $cancel['level'] ?? '';
      }
    $react_amount = $sub_info['amount'] ?? 0;
    if ( ! $react_amount && $prev_level ) {
        $react_amount = tta_get_membership_price( $prev_level );
    }
    if ( ! $react_amount ) {
        $react_amount = tta_get_membership_price( $level );
    }
    $react_level = $prev_level ? strtolower( $prev_level ) : $level;
    if ( ! in_array( $react_level, array( 'basic', 'premium' ), true ) ) {
        $react_level = 'basic';
    }
    $billing_prefill = $sub_info['billing'] ?? [];
    $exp_prefill     = '';
    if ( ! empty( $sub_info['exp_date'] ) && preg_match( '/^(\d{4})-(\d{2})$/', $sub_info['exp_date'], $m ) ) {
        $exp_prefill = $m[2] . '/' . substr( $m[1], -2 );
    }
      $had_mem  = tta_user_had_membership( $member['wpuserid'] );

    if ( ! $sub_id ) {
        if ( $cancel ) {
            echo '<p>' . sprintf(
                /* translators: 1: membership level, 2: date, 3: actor */
                esc_html__( 'Previous membership: %1$s. Cancelled on %2$s by %3$s.', 'tta' ),
                esc_html( tta_get_membership_label( $cancel['level'] ) ),
                esc_html( date_i18n( 'F j, Y', strtotime( $cancel['date'] ) ) ),
                ( 'admin' === ( $cancel['by'] ?? 'member' ) ) ? esc_html__( 'an administrator', 'tta' ) : esc_html__( 'the member', 'tta' )
            ) . '</p>';
            if ( ! empty( $cancel['card_last4'] ) ) {
                echo '<p>' . esc_html__( 'Last Card Used:', 'tta' ) . ' **** ' . esc_html( $cancel['card_last4'] ) . '</p>';
            }
        } else {
            echo '<p>' . esc_html__( 'This member has never purchased a membership.', 'tta' ) . '</p>';
        }
    } else {
        $price = $react_amount;
        echo '<p><strong>' . esc_html( tta_get_membership_label( $level ) ) . '</strong> - $' . number_format( $price, 2 ) . ' ' . esc_html__( 'Per Month', 'tta' ) . '</p>';
        $display_status = 'paymentproblem' === $status ? __( 'Payment problem', 'tta' ) : ucfirst( $status );
        echo '<p>' . esc_html__( 'Status:', 'tta' ) . ' <span>' . esc_html( $display_status ) . '</span></p>';
        if ( $last4 ) {
            echo '<p>' . esc_html__( 'Current Card:', 'tta' ) . ' <span>**** ' . esc_html( $last4 ) . '</span></p>';
        }
        $prev = get_user_meta( $member['wpuserid'], 'tta_prev_level', true );
        if ( 'paymentproblem' === $status && $prev ) {
            $prev_price = tta_get_membership_price( $prev );
            echo '<p>' . esc_html__( 'Previous Membership:', 'tta' ) . ' <span>' . esc_html( tta_get_membership_label( $prev ) ) . ' - $' . number_format( $prev_price, 2 ) . ' ' . esc_html__( 'per month', 'tta' ) . '</span></p>';
        } elseif ( 'cancelled' === $status && $cancel ) {
            $prev_level = $cancel['level'] ?? 'basic';
            $prev_price = tta_get_membership_price( $prev_level );
            echo '<p>' . esc_html__( 'Previous Membership:', 'tta' ) . ' <span>' . esc_html( tta_get_membership_label( $prev_level ) ) . ' - $' . number_format( $prev_price, 2 ) . ' ' . esc_html__( 'per month', 'tta' ) . '</span></p>';
            echo '<p>' . sprintf(
                /* translators: 1: cancellation date, 2: actor */
                esc_html__( 'Cancelled on %1$s by %2$s.', 'tta' ),
                esc_html( date_i18n( 'F j, Y', strtotime( $cancel['date'] ) ) ),
                ( 'admin' === ( $cancel['by'] ?? 'member' ) ) ? esc_html__( 'an administrator', 'tta' ) : esc_html__( 'the member', 'tta' )
            ) . '</p>';
            if ( ! empty( $cancel['card_last4'] ) ) {
                echo '<p>' . esc_html__( 'Last Card Used:', 'tta' ) . ' **** ' . esc_html( $cancel['card_last4'] ) . '</p>';
            }
        }
        if ( 'paymentproblem' === $status ) {
            echo '<p>' . esc_html__( 'There is a payment problem with this subscription.', 'tta' ) . '</p>';
        }
    }
    ?>
  </div>

  <div class="tta-subscription-forms">
<?php if ( $had_mem ) : ?>

<?php if ( in_array( $status, array( 'cancelled', 'paymentproblem' ), true ) ) : ?>
  <form id="tta-admin-reactivate-subscription-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Update the billing details and restart charges for this member.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
      <?php
        if ( 'cancelled' === $status ) {
            echo esc_html__( 'Create a New Subscription for This Member', 'tta' );
        } elseif ( 'paymentproblem' === $status ) {
            echo esc_html__( 'Attempt Payment Again or Cancel Current Subscription and Create a New One', 'tta' );
        } else {
            echo esc_html__( 'Update & Reactivate Membership', 'tta' );
        }
      ?>
    </h5>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">
    <input type="hidden" name="create_new" value="0" />
    <p>
      <label>
        <?php esc_html_e( 'Membership Level', 'tta' ); ?><br />
        <select name="level">
          <option value="basic" <?php selected( $react_level, 'basic' ); ?>><?php esc_html_e( 'Basic', 'tta' ); ?></option>
          <option value="premium" <?php selected( $react_level, 'premium' ); ?>><?php esc_html_e( 'Premium', 'tta' ); ?></option>
        </select>
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Monthly Amount', 'tta' ); ?><br />
        <input type="number" step="0.01" name="amount" value="<?php echo esc_attr( $react_amount ); ?>" />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Card Number', 'tta' ); ?><br />
        <?php $ph = $last4 ? '**** **** **** ' . esc_attr( $last4 ) : '&#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226;'; ?>
        <input type="text" name="card_number" placeholder="<?php echo $ph; ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Expiration', 'tta' ); ?><br />
        <input type="text" class="tta-card-exp" name="exp_date" placeholder="MM/YY" value="<?php echo esc_attr( $exp_prefill ); ?>" required maxlength="5" pattern="\d{2}/\d{2}" inputmode="numeric" />
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
        <input type="text" name="bill_first" value="<?php echo esc_attr( $billing_prefill['first_name'] ?? '' ); ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Billing Last Name', 'tta' ); ?><br />
        <input type="text" name="bill_last" value="<?php echo esc_attr( $billing_prefill['last_name'] ?? '' ); ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Street Address', 'tta' ); ?><br />
        <input type="text" name="bill_address" value="<?php echo esc_attr( $billing_prefill['address'] ?? '' ); ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Address Line 2', 'tta' ); ?><br />
        <input type="text" name="bill_address2" value="<?php echo esc_attr( $billing_prefill['address2'] ?? '' ); ?>" />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'City', 'tta' ); ?><br />
        <input type="text" name="bill_city" value="<?php echo esc_attr( $billing_prefill['city'] ?? '' ); ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'State', 'tta' ); ?><br />
        <select name="bill_state">
          <?php foreach ( tta_get_us_states() as $abbr => $name ) : ?>
            <option value="<?php echo esc_attr( $abbr ); ?>" <?php selected( $billing_prefill['state'] ?? '', $abbr ); ?>><?php echo esc_html( $name ); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'ZIP', 'tta' ); ?><br />
        <input type="text" name="bill_zip" value="<?php echo esc_attr( $billing_prefill['zip'] ?? '' ); ?>" required />
      </label>
    </p>
    <input type="hidden" name="use_current" value="0" />
    <p class="submit">
      <div class="tta-submit-history-div">
        <button type="submit" id="tta-create-sub-btn" class="button" data-tooltip="<?php esc_attr_e( 'Use the details entered above to restart billing.', 'tta' ); ?>"><?php esc_html_e( 'Create New Subscription', 'tta' ); ?></button>
        <?php if ( 'cancelled' !== $status ) : ?>
        <button type="button" id="tta-reactivate-current-btn" class="button" data-tooltip="<?php esc_attr_e( 'Retry billing using payment info already on file in Authorize.Net.', 'tta' ); ?>"><?php esc_html_e( 'Attempt billing again using current Authorize.net payment & billing info', 'tta' ); ?></button>
        <?php endif; ?>
        <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
      </div>
      <div id="tta-subscription-response" class="tta-admin-progress-response-div"><p class="tta-admin-progress-response-p"></p></div>
    </p>
  </form>

<?php else : ?>

  <form id="tta-admin-update-payment-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Change the card and billing address used for the member\'s recurring payments.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
      <?php esc_html_e( 'Update Payment Method', 'tta' ); ?>
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
        <?php esc_html_e( 'Address Line 2', 'tta' ); ?><br />
        <input type="text" name="bill_address2" />
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
      <div class="tta-submit-history-div">
        <button type="submit" class="button"><?php esc_html_e( 'Update Payment Method', 'tta' ); ?></button>
        <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
      </div>
      <div id="tta-subscription-response" class="tta-admin-progress-response-div"><p class="tta-admin-progress-response-p"></p></div>
    </p>
  </form>


  <?php if ( 'active' !== $status ) : ?>
  <form id="tta-admin-reactivate-subscription-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Restart billing for a cancelled member. A new subscription may be created if needed.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
      <?php esc_html_e( 'Update & Reactivate Membership', 'tta' ); ?>
    </h5>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">
    <input type="hidden" name="use_current" value="0" />
    <p>
      <label>
        <?php esc_html_e( 'Monthly Amount', 'tta' ); ?><br />
        <input type="number" step="0.01" name="amount" value="<?php echo esc_attr( $react_amount ); ?>" />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Card Number', 'tta' ); ?><br />
        <?php $ph = $last4 ? '**** **** **** ' . esc_attr( $last4 ) : '&#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226;'; ?>
        <input type="text" name="card_number" placeholder="<?php echo $ph; ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Expiration', 'tta' ); ?><br />
        <input type="text" class="tta-card-exp" name="exp_date" placeholder="MM/YY" value="<?php echo esc_attr( $exp_prefill ); ?>" required maxlength="5" pattern="\d{2}/\d{2}" inputmode="numeric" />
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
        <input type="text" name="bill_first" value="<?php echo esc_attr( $billing_prefill['first_name'] ?? '' ); ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Billing Last Name', 'tta' ); ?><br />
        <input type="text" name="bill_last" value="<?php echo esc_attr( $billing_prefill['last_name'] ?? '' ); ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Street Address', 'tta' ); ?><br />
        <input type="text" name="bill_address" value="<?php echo esc_attr( $billing_prefill['address'] ?? '' ); ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Address Line 2', 'tta' ); ?><br />
        <input type="text" name="bill_address2" value="<?php echo esc_attr( $billing_prefill['address2'] ?? '' ); ?>" />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'City', 'tta' ); ?><br />
        <input type="text" name="bill_city" value="<?php echo esc_attr( $billing_prefill['city'] ?? '' ); ?>" required />
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'State', 'tta' ); ?><br />
        <select name="bill_state">
          <?php foreach ( tta_get_us_states() as $abbr => $name ) : ?>
            <option value="<?php echo esc_attr( $abbr ); ?>" <?php selected( $billing_prefill['state'] ?? '', $abbr ); ?>><?php echo esc_html( $name ); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'ZIP', 'tta' ); ?><br />
        <input type="text" name="bill_zip" value="<?php echo esc_attr( $billing_prefill['zip'] ?? '' ); ?>" required />
      </label>
    </p>
    <p class="submit">
      <div class="tta-submit-history-div">
        <button type="submit" class="button" data-tooltip="<?php esc_attr_e( 'Use the details entered above to restart billing.', 'tta' ); ?>"><?php esc_html_e( 'Reactivate & Update Using Info Above', 'tta' ); ?></button>
        <?php if ( 'cancelled' !== $status ) : ?>
        <button type="button" id="tta-reactivate-current-btn" class="button" data-tooltip="<?php esc_attr_e( 'Retry billing using payment info already on file in Authorize.Net.', 'tta' ); ?>"><?php esc_html_e( 'Attempt Reactivation using Current Authorize.net Subscription Info', 'tta' ); ?></button>
        <?php endif; ?>
        <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
      </div>
      <div id="tta-subscription-response" class="tta-admin-progress-response-div"><p class="tta-admin-progress-response-p"></p></div>
    </p>
  </form>
  <?php endif; ?>

<?php endif; ?>

<?php if ( ! in_array( $status, array( 'cancelled', 'paymentproblem' ), true ) ) : ?>
  <form id="tta-admin-change-level-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Switch the member between Basic and Premium tiers starting on their next bill.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
      <?php esc_html_e( 'Change Membership Level', 'tta' ); ?>
    </h5>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">
    <p>
      <label>
        <?php esc_html_e( 'New Level', 'tta' ); ?><br />
        <select name="level">
          <option value="basic" <?php selected( $level, 'basic' ); ?>><?php esc_html_e( 'Basic', 'tta' ); ?></option>
          <option value="premium" <?php selected( $level, 'premium' ); ?>><?php esc_html_e( 'Premium', 'tta' ); ?></option>
        </select>
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Price Per Month', 'tta' ); ?><br />
        <input type="number" step="0.01" name="price" value="<?php echo esc_attr( $react_amount ); ?>" required />
      </label>
    </p>
    <p class="submit">
      <div class="tta-submit-history-div">
        <button type="submit" class="button"><?php esc_html_e( 'Update Membership Level', 'tta' ); ?></button>
        <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
      </div>
      <div id="tta-subscription-response" class="tta-admin-progress-response-div"><p class="tta-admin-progress-response-p"></p></div>
    </p>
  </form>
  <form id="tta-admin-cancel-subscription-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Immediately cancel the member\'s active subscription.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
      <?php esc_html_e( 'Cancel Subscription', 'tta' ); ?>
    </h5>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">
    <p class="submit">
      <div class="tta-submit-history-div">
        <button type="submit" class="button"><?php esc_html_e( 'Cancel This Member\'s Subscription', 'tta' ); ?></button>
        <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
      </div>
      <div id="tta-subscription-response" class="tta-admin-progress-response-div"><p class="tta-admin-progress-response-p"></p></div>
    </p>
  </form>
<?php endif; ?>
</div>
<?php else : ?>
  <form id="tta-admin-assign-membership-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <h5>
      <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Sign this user up for a new membership and charge the first month immediately.', 'tta' ); ?>">
        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help">
      </span>
      <?php esc_html_e( 'Assign This Member a Membership', 'tta' ); ?>
    </h5>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">
    <p>
      <label>
        <?php esc_html_e( 'Level', 'tta' ); ?><br />
        <select name="level">
          <option value="basic">Basic</option>
          <option value="premium">Premium</option>
        </select>
      </label>
    </p>
    <p>
      <label>
        <?php esc_html_e( 'Monthly Amount', 'tta' ); ?><br />
        <input type="number" step="0.01" name="amount" required />
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
        <?php esc_html_e( 'Address Line 2', 'tta' ); ?><br />
        <input type="text" name="bill_address2" />
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
      <div class="tta-submit-history-div">
        <button type="submit" class="button"><?php esc_html_e( 'Assign Membership', 'tta' ); ?></button>
        <div class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="" style="display:none;opacity:0"></div>
      </div>
      <div id="tta-subscription-response" class="tta-admin-progress-response-div"><p class="tta-admin-progress-response-p"></p></div>
    </p>
  </form>
<?php endif; ?>
</div>
</div>
