<?php
/* includes/admin/views/tickets-manage.php */

global $wpdb;
$events_table  = $wpdb->prefix . 'tta_events';
$tickets_table = $wpdb->prefix . 'tta_tickets';

// 1) Search/filter events by name
$search = isset($_GET['s']) ? tta_sanitize_text_field($_GET['s']) : '';
$where  = $search
    ? $wpdb->prepare( "WHERE e.name LIKE %s", '%' . $wpdb->esc_like($search) . '%' )
    : '';

// 2) Pagination
$per_page = 20;
$paged    = max(1, intval($_GET['paged'] ?? 1));
$offset   = ($paged - 1) * $per_page;

// 3) Count distinct events with tickets
$total = $wpdb->get_var("
  SELECT COUNT(DISTINCT e.id)
    FROM {$events_table} e
    JOIN {$tickets_table} t ON t.event_ute_id = e.ute_id
  {$where}
");

// 4) Fetch event rows, ordered exactly as in manage-events.php
$sql = "
  SELECT e.*
    FROM {$events_table} e
    JOIN {$tickets_table} t ON t.event_ute_id = e.ute_id
  {$where}
  GROUP BY e.id
  ORDER
    BY
      CASE WHEN e.date >= CURDATE() THEN 0 ELSE 1 END ASC,
      CASE WHEN e.date >= CURDATE() THEN e.date ELSE NULL END ASC,
      CASE WHEN e.date <  CURDATE() THEN e.date ELSE NULL END DESC
  LIMIT %d, %d
";
$events = $wpdb->get_results( $wpdb->prepare($sql, $offset, $per_page), ARRAY_A );
?>

<form method="get" style="margin-bottom:1em;">
  <input type="hidden" name="page" value="tta-tickets">
  <p class="search-box">
    <label for="ticket-search-input" class="screen-reader-text"><?php esc_html_e('Search Events:', 'tta'); ?></label>
    <input id="ticket-search-input" type="search" name="s" value="<?php echo esc_attr($search); ?>">
    <button class="button"><?php esc_html_e('Search Events', 'tta'); ?></button>
  </p>
</form>

<table class="widefat striped">
  <thead>
    <tr>
      <th><?php esc_html_e('Event Image', 'tta'); ?></th>
      <th><?php esc_html_e('Event Name', 'tta'); ?></th>
      <th><?php esc_html_e('Date', 'tta'); ?></th>
      <th><?php esc_html_e('Tickets', 'tta'); ?></th>
      <th></th><!-- expand arrow -->
    </tr>
  </thead>
  <tbody>
  <?php if ( $events ) : ?>
    <?php foreach ( $events as $e ) :
      // thumbnail/fallback
      if ( $e['mainimageid'] ) {
        $img_html = tta_admin_preview_image( intval($e['mainimageid']), [50,50] );
      } else {
        $default  = esc_url(TTA_PLUGIN_URL.'assets/images/admin/default-event.png');
        $img_html = '<img src="'.$default.'" width="50" height="50" alt="">';
      }
      $date_fmt = date_i18n('n-j-Y', strtotime($e['date']));
      // how many tickets exist for this event?
      $count_tix = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$tickets_table} WHERE event_ute_id = %s",
        $e['ute_id']
      ));
    ?>
      <tr data-event-ute-id="<?php echo esc_attr($e['ute_id']); ?>">
        <td><?php echo $img_html; ?></td>
        <td><?php echo esc_html($e['name']); ?></td>
        <td><?php echo esc_html($date_fmt); ?></td>
        <td><?php echo intval($count_tix); ?></td>
        <td class="tta-toggle-cell">
          <img src="<?php echo esc_url(TTA_PLUGIN_URL.'assets/images/admin/arrow.svg'); ?>"
               class="tta-toggle-arrow" width="10" height="10"
               alt="<?php esc_attr_e('Toggle Tickets', 'tta'); ?>">
        </td>
      </tr>
    <?php endforeach; ?>
  <?php else : ?>
    <tr><td colspan="5"><?php esc_html_e('No events found.', 'tta'); ?></td></tr>
  <?php endif; ?>
  </tbody>
</table>

<?php
// pagination (mirrors events-manage)
$base = add_query_arg([
  'page'  => 'tta-tickets',
  's'     => $search,
  'paged' => '%#%',
], admin_url('admin.php'));

echo '<div class="tablenav"><div class="tablenav-pages">';
echo paginate_links([
  'base'      => $base,
  'format'    => '',
  'current'   => $paged,
  'total'     => ceil($total/$per_page),
  'prev_text' => '&laquo;',
  'next_text' => '&raquo;',
]);
echo '</div></div>';
?>
