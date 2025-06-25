<!-- BILLING & MEMBERSHIP INFO -->
<div id="tab-billing" class="tta-dashboard-section">
  <h3><?php esc_html_e( 'Billing & Membership Info', 'tta' ); ?></h3>
  <?php
  $level  = strtolower( $member['membership_level'] ?? 'free' );
  $status = strtolower( $member['subscription_status'] ?? 'active' );
  $sub_id = $member['subscription_id'] ?? '';
  $last4  = $sub_id ? tta_get_subscription_card_last4( $sub_id ) : '';
  if ( 'free' === $level ) :
  ?>
    <p><?php esc_html_e( 'You do not currently have a paid membership.', 'tta' ); ?></p>
  <?php else :
    $price = tta_get_membership_price( $level );
    ?>
    <p>
      <strong id="tta-membership-level"><?php echo esc_html( tta_get_membership_label( $level ) ); ?></strong>
      – $<?php echo number_format( $price, 2 ); ?> <?php esc_html_e( 'Per Month', 'tta' ); ?>
    </p>
    <p><?php esc_html_e( 'Status:', 'tta' ); ?> <span id="tta-membership-status"><?php echo esc_html( ucfirst( $status ) ); ?></span></p>
    <?php if ( $last4 ) : ?>
      <p><?php esc_html_e( 'Current Card:', 'tta' ); ?> <span id="tta-card-last4">**** <?php echo esc_html( $last4 ); ?></span></p>
    <?php endif; ?>
    <?php if ( 'cancelled' !== $status ) : ?>
      <form id="tta-cancel-membership-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
        <?php wp_nonce_field( 'tta_member_front_update', 'nonce' ); ?>
        <input type="hidden" name="action" value="tta_cancel_membership" />
        <p class="tta-submit-wrap">
          <button type="submit" class="button">
            <?php esc_html_e( 'Cancel Membership', 'tta' ); ?>
          </button>
          <span class="tta-progress-spinner">
            <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
          </span>
          <span class="tta-admin-progress-response"><p class="tta-admin-progress-response-p"></p></span>
        </p>
      </form>

      <form id="tta-update-card-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" class="tta-update-card-form">
        <?php wp_nonce_field( 'tta_member_front_update', 'nonce' ); ?>
        <input type="hidden" name="action" value="tta_update_payment" />
        <p>
          <label>
            <?php esc_html_e( 'Card Number', 'tta' ); ?>
            <input type="text" name="card_number" required />
          </label>
        </p>
        <p>
          <label><?php esc_html_e( 'Expiry (YYYY-MM)', 'tta' ); ?> <input type="text" name="exp_date" required /></label>
          <label><?php esc_html_e( 'CVC', 'tta' ); ?> <input type="text" name="card_cvc" size="4" /></label>
        </p>
        <p class="tta-submit-wrap">
          <button type="submit" class="button"><?php esc_html_e( 'Update Card', 'tta' ); ?></button>
          <span class="tta-progress-spinner">
            <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
          </span>
          <span class="tta-admin-progress-response"><p class="tta-admin-progress-response-p"></p></span>
        </p>
      </form>
    <?php endif; ?>
  <?php endif; ?>

  <?php
  $history = tta_get_member_billing_history( get_current_user_id() );
  if ( $history ) : ?>
    <h4><?php esc_html_e( 'Payment History', 'tta' ); ?></h4>
    <table class="widefat striped tta-billing-history">
      <thead>
        <tr>
          <th><?php esc_html_e( 'Date', 'tta' ); ?></th>
          <th><?php esc_html_e( 'Item', 'tta' ); ?></th>
          <th><?php esc_html_e( 'Amount', 'tta' ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $history as $row ) : ?>
          <tr>
            <td><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $row['date'] ) ) ); ?></td>
            <td><?php echo esc_html( $row['description'] ); ?></td>
            <td>$<?php echo esc_html( number_format( $row['amount'], 2 ) ); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else : ?>
    <p><?php esc_html_e( 'No transactions found.', 'tta' ); ?></p>
  <?php endif; ?>
</div>
