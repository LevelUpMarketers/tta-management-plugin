<?php
/**
 * Template Name: Host Check-In
 *
 * @package TTA
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$context = tta_get_current_user_context();

if ( ! $context['is_logged_in'] ) {
    get_header();
    wp_login_form( [ 'redirect' => get_permalink() ] );
    get_footer();
    return;
}

$member = $context['member'];
$allowed = [ 'volunteer', 'admin', 'super_admin' ];
if ( ! $member || ! in_array( $member['member_type'], $allowed, true ) ) {
    echo '<p>' . esc_html__( 'You do not have permission to view this page.', 'tta' ) . '</p>';
    return;
}

global $wpdb;
$events_table = $wpdb->prefix . 'tta_events';
$events = $wpdb->get_results( "SELECT * FROM {$events_table} WHERE date >= CURDATE() ORDER BY date ASC", ARRAY_A );
$today  = current_time( 'Y-m-d' );

get_header();
?>
<div class="tta-checkin-wrap">
<table class="widefat striped">
  <thead>
    <tr>
      <th><?php esc_html_e( 'Event Image', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Event Name', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Date', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Status', 'tta' ); ?></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php if ( $events ) :
    foreach ( $events as $e ) :
        $status = ( $e['date'] > $today ) ? 'Upcoming' : ( $e['date'] === $today ? 'Today' : 'Past' );
        if ( ! empty( $e['mainimageid'] ) ) {
            $img = wp_get_attachment_image( intval( $e['mainimageid'] ), [50,50] );
        } else {
            $img = '<img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' ) . '" width="50" height="50" alt="">';
        }
  ?>
    <tr class="tta-event-row" data-event-ute-id="<?php echo esc_attr( $e['ute_id'] ); ?>">
      <td><?php echo $img; ?></td>
      <td><?php echo esc_html( $e['name'] ); ?></td>
      <td><?php echo esc_html( date_i18n( 'n-j-Y', strtotime( $e['date'] ) ) ); ?></td>
      <td><?php echo esc_html( $status ); ?></td>
      <td class="tta-toggle-cell"><img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/arrow.svg' ); ?>" class="tta-toggle-arrow" width="16" height="16" alt="Toggle"></td>
    </tr>
  <?php endforeach; else : ?>
    <tr><td colspan="5"><?php esc_html_e( 'No upcoming events found.', 'tta' ); ?></td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
<?php
get_footer();

