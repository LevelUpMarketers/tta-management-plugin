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

// Fetch this page’s rows
$members = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$members_table} {$where_sql} ORDER BY joined_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
), ARRAY_A );

$total_pages = ceil( $total_members / $per_page );

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
        <button class="button" type="submit">Search Members</button>
      </p>
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
                    echo paginate_links( [
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => $total_pages,
                        'current'   => $page,
                    ] );
                ?>
            </div>
        </div>
    <?php endif; ?>

</div>
