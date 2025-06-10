<?php
/* includes/admin/views/tickets-manage.php */

global $wpdb;
$events_table  = $wpdb->prefix . 'tta_events';
$tickets_table = $wpdb->prefix . 'tta_tickets';

// 1) Search & WHERE
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$where  = $search
    ? $wpdb->prepare( "WHERE e.name LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' )
    : '';

// 2) Pagination vars
$per_page = 20;
$paged    = max( 1, intval( $_GET['paged'] ?? 1 ) );
$offset   = ( $paged - 1 ) * $per_page;

// 3) Total count for pagination
$total = $wpdb->get_var( "
    SELECT COUNT(*)
      FROM {$tickets_table} t
      JOIN {$events_table}  e ON e.ute_id = t.event_ute_id
    {$where}
" );

// 4) Fetch tickets + event data, ordering same as Manage Events
$sql = "
    SELECT
      t.*,
      e.name          AS event_name,
      e.mainimageid   AS event_image_id,
      e.page_id       AS event_page_id,
      e.date          AS event_date,
      t.attendancelimit
    FROM {$tickets_table} t
    JOIN {$events_table}  e ON e.ute_id = t.event_ute_id
    {$where}
 ORDER
    BY
      CASE WHEN e.date >= CURDATE() THEN 0 ELSE 1 END ASC,
      CASE WHEN e.date >= CURDATE() THEN e.date ELSE NULL END ASC,
      CASE WHEN e.date <  CURDATE() THEN e.date ELSE NULL END DESC
    LIMIT %d, %d
";
$tickets = $wpdb->get_results(
    $wpdb->prepare( $sql, $offset, $per_page ),
    ARRAY_A
);
?>

<form method="get" style="margin-bottom:1em;">
  <input type="hidden" name="page" value="tta-tickets">
  <p class="search-box">
    <label class="screen-reader-text" for="ticket-search-input">Search Tickets:</label>
    <input id="ticket-search-input" type="search" name="s" value="<?php echo esc_attr( $search ); ?>">
    <button class="button">Search Tickets</button>
  </p>
</form>

<table class="widefat striped">
  <thead>
    <tr>
      <th><?php esc_html_e( 'Event Image', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Event Name', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Event Page', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Event Date', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Total Attendance Limit', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Waitlist?', 'tta' ); ?></th>
      <th><?php esc_html_e( 'Actions', 'tta' ); ?></th>
      <th></th><!-- toggle arrow -->
    </tr>
  </thead>
  <tbody>
    <?php if ( $tickets ) : ?>
      <?php foreach ( $tickets as $r ) :
        // build event thumbnail
        if ( ! empty( $r['event_image_id'] ) ) {
          $img = wp_get_attachment_image( intval( $r['event_image_id'] ), [50,50] );
        } else {
          $default = esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' );
          $img     = '<img src="' . $default . '" width="50" height="50" alt="">';
        }

        // front-end page link
        $page_url = $r['event_page_id']
          ? get_permalink( intval( $r['event_page_id'] ) )
          : '';

        // human‐readable event date in n-j-Y
        $evt_date = date_i18n( 'n-j-Y', strtotime( $r['event_date'] ) );
      ?>
        <tr data-ticket-id="<?php echo esc_attr( $r['id'] ); ?>">
          <td><?php echo $img; ?></td>
          <td><?php echo esc_html( $r['event_name'] ); ?></td>
          <td>
            <?php if ( $page_url ) : ?>
              <a href="<?php echo esc_url( $page_url ); ?>" target="_blank">
                <?php esc_html_e( 'View Page', 'tta' ); ?>
              </a>
            <?php else : ?>
              &mdash;
            <?php endif; ?>
          </td>
          <td><?php echo esc_html( $evt_date ); ?></td>
          <td><?php echo esc_html( $r['attendancelimit'] ?: __( 'Unlimited', 'tta' ) ); ?></td>
          <td><?php echo $r['waitlist_id'] ? __( 'Yes', 'tta' ) : __( 'No', 'tta' ); ?></td>
          <td>
            <a href="#" class="tta-edit-ticket" data-ticket-id="<?php echo esc_attr( $r['id'] ); ?>">
              <?php esc_html_e( 'Edit', 'tta' ); ?>
            </a>
          </td>
          <td class="tta-toggle-cell">
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/arrow.svg' ); ?>"
                 class="tta-toggle-arrow"
                 width="16" height="16"
                 alt="<?php esc_attr_e( 'Toggle Edit Form', 'tta' ); ?>">
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else : ?>
      <tr>
        <td colspan="8"><?php esc_html_e( 'No tickets found.', 'tta' ); ?></td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<?php
// Pagination (mirrors events-manage.php, but page=tta-tickets)
$base = add_query_arg( [
  'page'  => 'tta-tickets',
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
