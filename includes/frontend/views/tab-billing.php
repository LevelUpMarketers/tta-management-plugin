<!-- BILLING & MEMBERSHIP INFO -->
<div id="tab-billing" class="tta-dashboard-section">
  <h3><?php esc_html_e( 'Billing & Membership Info', 'tta' ); ?></h3>
  <?php
  $level  = strtolower( $member['membership_level'] ?? 'free' );
  $status = strtolower( $member['subscription_status'] ?? 'active' );
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
    <?php endif; ?>
  <?php endif; ?>
</div>