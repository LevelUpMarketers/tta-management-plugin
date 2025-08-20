<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * We will query the `tta_members` table to get the first page (10 per page),
 * along with any search filter (by first_name, last_name, or email).
 *
 * Then we output a `<table class="widefat">` with a toggle arrow column
 * (just like Manage Events), then columns: First Name, Last Name, Email, Member Type, Joined At, Actions.
 *
 * Each row has: data‐member‐id="{ID from tta_members}" so that our JS can hook into it.
 */

// Basic pagination and search parameters:
global $wpdb;
$members_table = $wpdb->prefix . 'tta_members';

$per_page = 10;
$page      = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$offset    = ( $page - 1 ) * $per_page;
$orderby_param = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : '';
$orderby       = $orderby_param ? $orderby_param : 'joined';

// Build the WHERE clause for searching:
$where_sql   = '';
$search_term = isset( $_GET['s'] ) ? tta_sanitize_text_field( $_GET['s'] ) : '';
if ( $search_term ) {
    $like = '%' . $wpdb->esc_like( $search_term ) . '%';
    $where_sql = $wpdb->prepare(
        "WHERE first_name LIKE %s OR last_name LIKE %s OR email LIKE %s",
        $like,
        $like,
        $like
    );
}

// Fetch total count (for pagination)
$total_members = $wpdb->get_var( "SELECT COUNT(*) FROM {$members_table} {$where_sql}" );

// Fetch all rows so we can sort by metrics
$members = $wpdb->get_results( "SELECT * FROM {$members_table} {$where_sql}", ARRAY_A );

// Attach metrics for sorting
foreach ( $members as &$m ) {
    $summary                  = tta_get_member_history_summary( $m['id'] );
    $m['__total_spent']       = $summary['total_spent'];
    $m['__attended']          = $summary['attended'];
    $m['__membership_length'] = time() - strtotime( $m['joined_at'] );
}
unset( $m );

usort( $members, function ( $a, $b ) use ( $orderby ) {
    switch ( $orderby ) {
        case 'length':
            return $b['__membership_length'] <=> $a['__membership_length'];
        case 'attended':
            return $b['__attended'] <=> $a['__attended'];
        case 'spent':
            return $b['__total_spent'] <=> $a['__total_spent'];
        case 'first':
            return strcasecmp( $a['first_name'], $b['first_name'] );
        case 'last':
            return strcasecmp( $a['last_name'], $b['last_name'] );
        case 'joined':
        default:
            return strtotime( $b['joined_at'] ) - strtotime( $a['joined_at'] );
    }
} );

$total_members = count( $members );
$members       = array_slice( $members, $offset, $per_page );
$total_pages   = ceil( $total_members / $per_page );

?>

<div id="tta-members-history">

    <!-- Search Form (already present above in class‐members‐admin) -->
    <!-- The form resides in class‐members‐admin; this file focuses on the table -->

    <?php if ( $search_term ): ?>
        <p class="subtitle">Search results for "<strong><?php echo esc_html( $search_term ); ?></strong>"</p>
    <?php endif; ?>
    <form method="get" style="margin-bottom: 20px;">
      <input type="hidden" name="page" value="tta-members">
      <input type="hidden" name="tab" value="history">
  <p class="search-box">
        <label class="screen-reader-text" for="member-search-input">Search Members:</label>
        <input
          type="search"
          id="member-search-input"
          name="s"
          value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>"
          placeholder="Search by first name, last name, or email…"
        >
        <button class="button" type="submit"><?php esc_html_e( 'Search Members', 'tta' ); ?></button>
        <select name="orderby" id="tta-member-orderby" onchange="this.form.submit()">
          <option value="" disabled <?php selected( $orderby_param, '' ); ?>><?php esc_html_e( 'Sort By…', 'tta' ); ?></option>
          <option value="joined" <?php selected( $orderby_param, 'joined' ); ?>><?php esc_html_e( 'Newest Joined', 'tta' ); ?></option>
          <option value="length" <?php selected( $orderby_param, 'length' ); ?>><?php esc_html_e( 'Membership Length', 'tta' ); ?></option>
          <option value="attended" <?php selected( $orderby_param, 'attended' ); ?>><?php esc_html_e( 'Events Attended', 'tta' ); ?></option>
          <option value="spent" <?php selected( $orderby_param, 'spent' ); ?>><?php esc_html_e( 'Total Spent', 'tta' ); ?></option>
          <option value="first" <?php selected( $orderby_param, 'first' ); ?>><?php esc_html_e( 'First Name', 'tta' ); ?></option>
          <option value="last" <?php selected( $orderby_param, 'last' ); ?>><?php esc_html_e( 'Last Name', 'tta' ); ?></option>
        </select>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=tta-members&tab=history' ) ); ?>" class="button"><?php esc_html_e( 'Clear Sorting', 'tta' ); ?></a>
      </p>
    </form>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:20px;">
        <?php wp_nonce_field( 'tta_export_members_nonce' ); ?>
        <input type="hidden" name="action" value="tta_export_member_metrics">
        <label><?php esc_html_e( 'Joined Start', 'tta' ); ?>
            <input type="date" name="start_date">
        </label>
        <label><?php esc_html_e( 'Joined End', 'tta' ); ?>
            <input type="date" name="end_date">
        </label>
        <button class="button" type="submit"><?php esc_html_e( 'Export Members', 'tta' ); ?></button>
    </form>

    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th class="manage-column column-image">Profile Image</th>
                <th class="manage-column column-firstname">First Name</th>
                <th class="manage-column column-lastname">Last Name</th>
                <th class="manage-column column-email">Email</th>
                <th class="manage-column column-membertype">Type</th>
                <th class="manage-column column-joined">Joined</th>
                <th class="manage-column column-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ( ! empty( $members ) ): ?>
            <?php foreach ( $members as $member ): ?>

                <?php
                    $attachment_id = $member['profileimgid']; // e.g. 123 or retrieved dynamically via get_post_thumbnail_id(), etc.

                    // Define your default‐image URL (absolute or relative to your theme/plugin).
                    $default_image_url = esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' );;

                    if ( ! empty( $attachment_id ) ) {
                        // Try to get the “full” size URL (you can swap 'full' for 'thumbnail', 'medium', 'large', etc.).
                        $image_url = wp_get_attachment_image_url( $attachment_id, 'full' );

                        // If for some reason wp_get_attachment_image_url() returned false, revert to default.
                        if ( ! $image_url ) {
                            $image_url = $default_image_url;
                        }
                    } else {
                        // No attachment ID was provided—use default immediately.
                        $image_url = $default_image_url;
                    }
                ?>

                <?php
                    // convert to a UNIX timestamp
                    $ts = strtotime( $member['joined_at'] );

                    // human‐readable absolute date
                    $readable_date = date_i18n( 'F j, Y \a\t g:i A', $ts );
                    // e.g. “June 12, 2025 at 11:30 AM”

                ?>

                <tr data-member-id="<?php echo esc_attr( $member['id'] ); ?>">
                    <td class="tta-member-center-profile-img"><?php echo '<img class="tta-member-edit-unexpanded-profile-img" src="' . $image_url . '"/>' ?></td>
                    <td><?php echo esc_html( $member['first_name'] ); ?></td>
                    <td><?php echo esc_html( $member['last_name'] ); ?></td>
                    <td><?php echo esc_html( $member['email'] ); ?></td>
                    <td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $member['member_type'] ) ) ); ?></td>
                    <td><?php echo esc_html( $readable_date ); ?></td>
                    <td>
                        <a href="#" class="tta-edit-link">View History</a>
                        <img class="tta-row-spinner" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" style="display:none;width:16px;height:16px;margin-left:4px;vertical-align:middle;opacity:0" />
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No members found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination Links -->
    <?php if ( $total_pages > 1 ): ?>
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php
                    $pagination_args = [
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => $total_pages,
                        'current'   => $page,
                        'end_size'  => 1,
                        'mid_size'  => $page === 1 ? 19 : 2,
                    ];

                    echo paginate_links( $pagination_args );
                ?>
            </div>
        </div>
    <?php endif; ?>

</div>
