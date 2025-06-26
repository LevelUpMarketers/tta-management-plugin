<?php
global $wpdb;
$table = $wpdb->prefix . 'tta_venues';
if( isset($_GET['action'], $_GET['venue_id']) && $_GET['action']=='delete' && check_admin_referer('tta_venue_delete_nonce') ){
    $wpdb->delete($table, ['id'=>intval($_GET['venue_id'])]);
    echo '<div class="updated"><p>Venue deleted.</p></div>';
}
$venues = $wpdb->get_results("SELECT * FROM {$table} ORDER BY name", ARRAY_A);
?>
<table class="widefat">
<thead><tr><th>Name</th><th>Address</th><th>Link</th><th>Actions</th></tr></thead>
<tbody>
<?php if($venues): foreach($venues as $v): ?>
<tr>
<td><?php echo esc_html($v['name']); ?></td>
<td><?php echo esc_html($v['address']); ?></td>
<td><?php echo esc_html($v['venueurl']); ?></td>
<td><a href="<?php echo esc_url(admin_url('admin.php?page=tta-venues&tab=create&venue_id='.$v['id'])); ?>">Edit</a> |
<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=tta-venues&tab=manage&action=delete&venue_id='.$v['id']),'tta_venue_delete_nonce')); ?>" onclick="return confirm('Delete this venue?');">Delete</a></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4">No venues found.</td></tr>
<?php endif; ?>
</tbody>
</table>
