<?php
global $wpdb;
$table = $wpdb->prefix . 'tta_venues';
if( isset($_GET['action'], $_GET['venue_id']) && $_GET['action']=='delete' && check_admin_referer('tta_venue_delete_nonce') ){
    $wpdb->delete($table, ['id'=>intval($_GET['venue_id'])]);
    echo '<div class="updated"><p>Venue deleted.</p></div>';
}
$venues = $wpdb->get_results("SELECT * FROM {$table} ORDER BY name", ARRAY_A);
?>
<div id="tta-venues-manage">
<table class="widefat striped">
<thead><tr><th>Name</th><th>Address</th><th>Main Link</th><th>Actions</th><th></th></tr></thead>
<tbody>
<?php if($venues): foreach($venues as $v): ?>
<tr data-venue-id="<?php echo esc_attr($v['id']); ?>">
<td><?php echo esc_html($v['name']); ?></td>
<td><?php echo esc_html($v['address']); ?></td>
<td><?php echo esc_html($v['venueurl']); ?></td>
<td><a href="#" class="tta-edit-link" data-venue-id="<?php echo esc_attr($v['id']); ?>">Edit</a> |
<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=tta-venues&tab=manage&action=delete&venue_id='.$v['id']),'tta_venue_delete_nonce')); ?>" onclick="return confirm('Delete this venue?');">Delete</a></td>
<td class="tta-toggle-cell"><img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/arrow.svg' ); ?>" class="tta-toggle-arrow" width="10" height="10" alt="Toggle Edit"></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5">No venues found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
