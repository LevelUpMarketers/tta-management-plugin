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
    $header_shortcode = '[vc_row full_width="stretch_row_content_no_spaces" css=".vc_custom_1670382516702{background-image: url(https://trying-to-adult-rva-2025.local/wp-content/uploads/2022/12/IMG-4418.png?id=70) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}"][vc_column][vc_empty_space height="300px" el_id="jre-header-title-empty"][vc_column_text css_animation="slideInLeft" el_id="jre-homepage-id-1" css=".vc_custom_1671885403487{margin-left: 50px !important;padding-left: 50px !important;}"]<p id="jre-homepage-id-3">EVENT CHECK-IN</p>[/vc_column_text][/vc_column][/vc_row]';
    echo do_shortcode( $header_shortcode );
    wp_login_form( [ 'redirect' => get_permalink() ] );
    get_footer();
    return;
}

$member   = $context['member'];
$allowed  = [ 'volunteer', 'admin', 'super_admin' ];
$is_admin = current_user_can( 'manage_options' );
if ( ! $is_admin && ( ! $member || ! in_array( $member['member_type'], $allowed, true ) ) ) {
    echo '<p>' . esc_html__( 'You do not have permission to view this page.', 'tta' ) . '</p>';
    return;
}

global $wpdb;
$events_table = $wpdb->prefix . 'tta_events';
$events = $wpdb->get_results( "SELECT * FROM {$events_table} WHERE date >= CURDATE() ORDER BY date ASC", ARRAY_A );
$today  = current_time( 'Y-m-d' );

get_header();
$header_shortcode = '[vc_row full_width="stretch_row_content_no_spaces" css=".vc_custom_1670382516702{background-image: url(https://trying-to-adult-rva-2025.local/wp-content/uploads/2022/12/IMG-4418.png?id=70) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}"][vc_column][vc_empty_space height="300px" el_id="jre-header-title-empty"][vc_column_text css_animation="slideInLeft" el_id="jre-homepage-id-1" css=".vc_custom_1671885403487{margin-left: 50px !important;padding-left: 50px !important;}"]<p id="jre-homepage-id-3">EVENT CHECK-IN</p>[/vc_column_text][/vc_column][/vc_row]';
echo do_shortcode( $header_shortcode );
?>
<div class="tta-checkin-wrap">
<table class="widefat striped">
  <thead>
    <tr>
      <th><?php esc_html_e( 'Event', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Date & Time', 'tta' ); ?></th>
      <th><?php esc_html_e( '# of Expected Attendees', 'tta' ); ?></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php if ( $events ) :
    foreach ( $events as $e ) :
        if ( ! empty( $e['mainimageid'] ) ) {
            $img = wp_get_attachment_image( intval( $e['mainimageid'] ), [50,50] );
        } else {
            $img = '<img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' ) . '" width="50" height="50" alt="">';
        }
  ?>
    <tr class="tta-event-row" data-event-ute-id="<?php echo esc_attr( $e['ute_id'] ); ?>">
      <td class="tta-event-cell" data-label="<?php echo esc_attr__( 'Event', 'tta' ); ?>">
        <?php echo $img; ?>
        <div class="tta-event-name"><?php echo esc_html( $e['name'] ); ?></div>
      </td>
      <td data-label="<?php echo esc_attr__( 'Date & Time', 'tta' ); ?>"><?php echo esc_html( tta_format_event_datetime( $e['date'], $e['time'] ) ); ?></td>
      <td data-label="<?php echo esc_attr__( '# of Expected Attendees', 'tta' ); ?>"><?php echo intval( tta_get_expected_attendee_count( $e['ute_id'] ) ); ?></td>
      <td class="tta-toggle-cell" data-label=""><span class="tta-toggle-text"><strong><?php esc_html_e( 'See All Attendees', 'tta' ); ?></strong></span><img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/arrow.svg' ); ?>" class="tta-toggle-arrow" width="10" height="10" alt="Toggle"><div class="tta-inline-container"></div></td>
    </tr>
  <?php endforeach; else : ?>
    <tr><td colspan="4"><?php esc_html_e( 'No upcoming events found.', 'tta' ); ?></td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
<?php
get_footer();

