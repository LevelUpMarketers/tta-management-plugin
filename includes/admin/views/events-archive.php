<?php
/* Restored original code */
?><?php
global $wpdb;
$table = $wpdb->prefix . 'tta_events_archive';

// Handle deletion
if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['event_id'] ) ) {
    if ( check_admin_referer( 'tta_event_delete_nonce' ) ) {
        $event_id = intval( $_GET['event_id'] );

        // 1) Fetch the full event row
        $event_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d",
                $event_id
            ),
            ARRAY_A
        );
        $ute_id = $event_row['ute_id'] ?? null;

        if ( $ute_id ) {
            $tickets_table    = $wpdb->prefix . 'tta_tickets_archive';
            $att_archive      = $wpdb->prefix . 'tta_attendees_archive';

            // 2) Delete any archived tickets and attendees
            $tickets = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$tickets_table} WHERE event_ute_id = %s", $ute_id ), ARRAY_A );
            foreach ( $tickets as $t ) {
                $wpdb->delete( $att_archive, [ 'ticket_id' => $t['id'] ], [ '%d' ] );
            }
            $wpdb->delete( $tickets_table, [ 'event_ute_id' => $ute_id ], [ '%s' ] );

            // Remove any active waitlist rows just in case
            $waitlist_table = $wpdb->prefix . 'tta_waitlist';
            $wpdb->delete( $waitlist_table, [ 'event_ute_id' => $ute_id ], [ '%s' ] );
        }

        // 4) Finally delete the event itself
        $wpdb->delete(
            $table,
            [ 'id' => $event_id ],
            [ '%d' ]
        );

        echo '<div class="updated"><p>Event, its tickets, and waitlists have been deleted.</p></div>';
    }
}

// Search
$search = isset( $_GET['s'] ) ? tta_sanitize_text_field( $_GET['s'] ) : '';
$where  = '';
if ( $search ) {
    $like  = '%' . $wpdb->esc_like( $search ) . '%';
    $where = $wpdb->prepare( "WHERE name LIKE %s OR ute_id LIKE %s", $like, $like );
}

// Pagination setup
$per_page = 20;
$paged    = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$offset   = ( $paged - 1 ) * $per_page;

// Total count
$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );

// Fetch events in desired order
$sql = "
    SELECT *
      FROM {$table}
      {$where}
 ORDER
    BY
      CASE WHEN `date` >= CURDATE() THEN 0 ELSE 1 END ASC,
      CASE WHEN `date` >= CURDATE() THEN `date` ELSE NULL END ASC,
      CASE WHEN `date` <  CURDATE() THEN `date` ELSE NULL END DESC
    LIMIT %d, %d
";
$events = $wpdb->get_results(
    $wpdb->prepare( $sql, $offset, $per_page ),
    ARRAY_A
);
?>

<form method="get" style="margin-bottom: 1em;">
    <input type="hidden" name="page" value="tta-events">
    <input type="hidden" name="tab"  value="archive">
    <p class="search-box">
        <label for="event-search-input" class="screen-reader-text">Search Events:</label>
        <input type="search" id="event-search-input" name="s" value="<?php echo esc_attr( $search ); ?>">
        <button class="button" type="submit">Search Events</button>
    </p>
</form>

<table class="widefat striped">
    <thead>
        <tr>
            <th>Event Image</th>
            <th>Event Name</th>
            <th>Date</th>
            <th>Status</th>
            <th>Event Page</th>
            <th>Actions</th>
            <th></th> <!-- toggle arrow column -->
        </tr>
    </thead>
    <tbody>
    <?php if ( $events ) :
        $today = date( 'Y-m-d' );
        foreach ( $events as $e ) :
            // Determine status
            if ( $e['date'] > $today ) {
                $status = 'Upcoming';
            } elseif ( $e['date'] === $today ) {
                $status = 'Today';
            } else {
                $status = 'Past';
            }

            // Check for main image or fallback
            if ( ! empty( $e['mainimageid'] ) ) {
                $img_html = tta_admin_preview_image( intval( $e['mainimageid'] ), [50,50] );
            } else {
                $default   = esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' );
                $img_html  = '<img src="' . $default . '" width="50" height="50" alt="Default Event">';
            }

            // Build the front-end event page URL from stored page_id
            $page_id        = intval( $e['page_id'] );
            $event_page_url = $page_id ? get_permalink( $page_id ) : '#';
    ?>
        <tr data-event-id="<?php echo esc_attr( $e['id'] ); ?>" data-source="archive">
            <td><?php echo $img_html; ?></td>
            <td><?php echo esc_html( $e['name'] ); ?></td>
            <td><?php echo esc_html( date_i18n( 'n-j-Y', strtotime( $e['date'] ) ) ); ?></td>
            <td><?php echo esc_html( $status ); ?></td>
            <td>
              <?php if ( $page_id ) : ?>
                <a href="<?php echo esc_url( $event_page_url ); ?>" target="_blank" rel="noopener">
                  View Page
                </a>
              <?php else : ?>
                —
              <?php endif; ?>
            </td>
            <td>
                <a href="#"
                   class="tta-edit-link"
                   data-event-id="<?php echo esc_attr( $e['id'] ); ?>">
                   View
                </a>
            </td>
            <td class="tta-toggle-cell">
                <img
                  src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/arrow.svg' ); ?>"
                  class="tta-toggle-arrow"
                  width="16" height="16"
                  alt="Toggle Edit Form">
            </td>
        </tr>
    <?php endforeach; else : ?>
        <tr><td colspan="7">No events found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php
// Pagination links
$base = add_query_arg( [
    'page'  => 'tta-events',
    'tab'   => 'manage',
    's'     => $search,
    'paged' => '%#%',
], admin_url( 'admin.php' ) );

echo '<div class="tablenav"><div class="tablenav-pages">';
echo paginate_links( [
    'base'      => $base,
    'format'    => '',
    'current'   => $paged,
    'total'     => ceil( $total / $per_page ),
    'prev_text' => '&laquo;',
    'next_text' => '&raquo;',
] );
echo '</div></div>';
?>
