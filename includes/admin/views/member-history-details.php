<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$member_id = intval( $_GET['member_id'] ?? 0 );
if ( ! $member_id ) { echo '<p>Missing member.</p>'; return; }

global $wpdb;
$members_table = $wpdb->prefix . 'tta_members';
$member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$members_table} WHERE id=%d", $member_id ), ARRAY_A );
if ( ! $member ) { echo '<p>Member not found.</p>'; return; }

$summary = tta_get_member_history_summary( $member_id );
?>
<div class="tta-member-history-details">
  <h3><?php echo esc_html( $member['first_name'] . ' ' . $member['last_name'] ); ?></h3>
  <p><?php echo esc_html( $member['email'] ); ?></p>
  <h4><?php esc_html_e( 'Member Summary', 'tta' ); ?></h4>
  <ul>
    <li><?php printf( esc_html__( 'Total Spent: $%s', 'tta' ), number_format( $summary['total_spent'], 2 ) ); ?></li>
    <li><?php printf( esc_html__( 'Events Attended: %d', 'tta' ), $summary['attended'] ); ?></li>
    <li><?php printf( esc_html__( 'No-Shows: %d', 'tta' ), $summary['no_show'] ); ?></li>
    <li><?php printf( esc_html__( 'Refund/Cancellation Requests: %d', 'tta' ), $summary['refunds'] + $summary['cancellations'] ); ?></li>
    <li><?php printf( esc_html__( 'Total Events Purchased: %d', 'tta' ), $summary['events'] ); ?></li>
  </ul>
  <h4><?php esc_html_e( 'Event History', 'tta' ); ?></h4>
  <table class="widefat striped">
    <thead>
      <tr><th><?php esc_html_e( 'Event', 'tta' ); ?></th><th><?php esc_html_e( 'Date', 'tta' ); ?></th><th><?php esc_html_e( 'Amount', 'tta' ); ?></th></tr>
    </thead>
    <tbody>
      <?php if ( $summary['transactions'] ) : foreach ( $summary['transactions'] as $tx ) : ?>
      <tr>
        <td><?php echo esc_html( $tx['name'] ); ?></td>
        <td><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $tx['date'] ) ) ); ?></td>
        <td>$<?php echo esc_html( number_format( $tx['amount'], 2 ) ); ?></td>
      </tr>
      <?php endforeach; else : ?>
      <tr><td colspan="3"><?php esc_html_e( 'No transactions found.', 'tta' ); ?></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
