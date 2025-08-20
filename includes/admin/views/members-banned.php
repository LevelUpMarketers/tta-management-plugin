<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$members = tta_get_banned_members();

echo '<div id="tta-banned-members">';
if ( empty( $members ) ) {
    echo '<p>' . esc_html__( 'No banned members.', 'tta' ) . '</p>';
} else {
    echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Member', 'tta' ) . '</th><th></th></tr></thead><tbody>';
    foreach ( $members as $m ) {
        $uid  = intval( $m['wpuserid'] );
        $name = trim( $m['first_name'] . ' ' . $m['last_name'] );
        echo '<tr class="tta-banned-member" data-user="' . esc_attr( $uid ) . '">';
        echo '<td>' . esc_html( $name ) . '</td>';
        echo '<td class="tta-toggle-cell"><img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/arrow.svg' ) . '" class="tta-toggle-arrow" width="10" height="10" alt="Toggle"></td>';
        echo '</tr>';

        $status  = tta_get_user_ban_status( $uid );
        $summary = tta_get_member_attendance_summary( $uid );
        $no_show = intval( $summary['no_show'] );
        $next    = wp_next_scheduled( 'tta_reinstate_member', [ $uid ] );
        $tz      = wp_timezone();

        if ( 'timed' === $status['type'] && $next ) {
            $diff    = max( 0, $next - time() );
            $hours   = floor( $diff / HOUR_IN_SECONDS );
            $minutes = floor( ( $diff % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
            $seconds = $diff % MINUTE_IN_SECONDS;
            $remain  = sprintf( '%02d H, %02d M, %02d S', $hours, $minutes, $seconds );
            $count   = '<span class="tta-countdown" data-remaining="' . esc_attr( $diff ) . '">' . esc_html( $remain ) . '</span>';
            $cron    = wp_date( 'm-d-Y g:iA', $next, $tz );
        } else {
            $count = esc_html__( 'Banned Indefinitely', 'tta' );
            $cron  = esc_html__( 'None', 'tta' );
        }

        $banned_on = '';
        if ( 'timed' === $status['type'] && $status['weeks'] > 0 && ! empty( $m['banned_until'] ) ) {
            $banned_on_ts = strtotime( $m['banned_until'] ) - ( $status['weeks'] * WEEK_IN_SECONDS );
            $banned_on    = wp_date( 'm-d-Y g:iA', $banned_on_ts, $tz );
        }
        $banned_on = $banned_on ?: esc_html__( 'Unknown', 'tta' );

        switch ( $status['type'] ) {
            case 'indefinite':
                $ban_label = esc_html__( 'Indefinite', 'tta' );
                break;
            case 'reentry':
                $ban_label = esc_html__( 'Until Re-Entry', 'tta' );
                break;
            case 'timed':
                $ban_label = sprintf( _n( '%d Week', '%d Weeks', $status['weeks'], 'tta' ), $status['weeks'] );
                break;
            default:
                $ban_label = esc_html__( 'None', 'tta' );
        }

        echo '<tr class="tta-banned-details tta-inline-row" style="display:none;"><td colspan="2"><div class="tta-inline-container">';
        echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Banned On', 'tta' ) . '</th><th>' . esc_html__( 'No-Shows', 'tta' ) . '</th><th>' . esc_html__( 'Ban Type', 'tta' ) . '</th><th>' . esc_html__( 'Countdown', 'tta' ) . '</th><th>' . esc_html__( 'Reinstatement Scheduled For...', 'tta' ) . '</th><th>' . esc_html__( 'Actions', 'tta' ) . '</th></tr></thead><tbody>';
        echo '<tr>';
        echo '<td>' . esc_html( $banned_on ) . '</td>';
        echo '<td>' . esc_html( $no_show ) . '</td>';
        echo '<td>' . esc_html( $ban_label ) . '</td>';
        echo '<td>' . $count . '</td>';
        echo '<td>' . esc_html( $cron ) . '</td>';
        echo '<td><button class="button tta-banned-reinstate" data-user="' . esc_attr( $uid ) . '">' . esc_html__( 'Reinstate', 'tta' ) . '</button></td>';
        echo '</tr>';
        echo '</tbody></table></div></td></tr>';
    }
    echo '</tbody></table>';
}
echo '</div>';
