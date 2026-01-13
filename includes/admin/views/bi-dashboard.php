<?php
$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'events';
$tab_labels = [
    'events'  => 'Event Revenue Info',
    'members' => 'Members',
    'predict' => 'Predictive Analytics',
];
$tab_title = isset( $tab_labels[ $tab ] ) ? $tab_labels[ $tab ] : $tab_labels['events'];
?>
<div id="tta-bi-dashboard" class="wrap">
    <?php if ( 'events' === $tab ) : ?>
        <?php
        global $wpdb;
        $table  = $wpdb->prefix . 'tta_events_archive';
        $search = isset( $_GET['s'] ) ? tta_sanitize_text_field( $_GET['s'] ) : '';
        $where  = '';
        if ( $search ) {
            $like  = '%' . $wpdb->esc_like( $search ) . '%';
            $where = $wpdb->prepare( "WHERE name LIKE %s OR ute_id LIKE %s", $like, $like );
        }

        $per_page      = 20;
        $paged         = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $offset        = ( $paged - 1 ) * $per_page;
        $orderby_param = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : '';
        $orderby       = $orderby_param ? $orderby_param : 'date_desc';

        $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );

        switch ( $orderby ) {
            case 'date_asc':
                $order_sql = 'ORDER BY `date` ASC';
                break;
            case 'date_desc':
                $order_sql = 'ORDER BY `date` DESC';
                break;
            case 'name':
                $order_sql = 'ORDER BY name ASC';
                break;
            default:
                $order_sql = 'ORDER BY `date` DESC';
                break;
        }

        $sql    = "SELECT * FROM {$table} {$where} {$order_sql} LIMIT %d, %d";
        $events = $wpdb->get_results(
            $wpdb->prepare( $sql, $offset, $per_page ),
            ARRAY_A
        );
        ?>
        <form method="get" class="tta-bi-search-sort">
            <input type="hidden" name="page" value="tta-bi-dashboard">
            <input type="hidden" name="tab" value="events">
            <p class="search-box">
                <label for="tta-bi-event-search-input" class="screen-reader-text"><?php esc_html_e( 'Search Archived Events', 'tta' ); ?></label>
                <input type="search" id="tta-bi-event-search-input" name="s" value="<?php echo esc_attr( $search ); ?>">
                <button class="button" type="submit"><?php esc_html_e( 'Search', 'tta' ); ?></button>
                <select name="orderby" onchange="this.form.submit()">
                    <option value="" disabled <?php selected( $orderby_param, '' ); ?>><?php esc_html_e( 'Sort By…', 'tta' ); ?></option>
                    <option value="date_desc" <?php selected( $orderby_param, 'date_desc' ); ?>><?php esc_html_e( 'Newest First', 'tta' ); ?></option>
                    <option value="date_asc" <?php selected( $orderby_param, 'date_asc' ); ?>><?php esc_html_e( 'Oldest First', 'tta' ); ?></option>
                    <option value="name" <?php selected( $orderby_param, 'name' ); ?>><?php esc_html_e( 'Event Name', 'tta' ); ?></option>
                </select>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=tta-bi-dashboard&tab=events' ) ); ?>" class="button"><?php esc_html_e( 'Clear Sorting', 'tta' ); ?></a>
            </p>
        </form>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Event Image', 'tta' ); ?></th>
                    <th><?php esc_html_e( 'Event Name', 'tta' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'tta' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'tta' ); ?></th>
                    <th><?php esc_html_e( 'Event Page', 'tta' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'tta' ); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $events ) : ?>
                    <?php
                    $today = date( 'Y-m-d' );
                    foreach ( $events as $event ) :
                        if ( $event['date'] > $today ) {
                            $status = 'Upcoming';
                        } elseif ( $event['date'] === $today ) {
                            $status = 'Today';
                        } else {
                            $status = 'Past';
                        }

                        if ( ! empty( $event['mainimageid'] ) ) {
                            $img_html = tta_admin_preview_image( intval( $event['mainimageid'] ), [ 50, 50 ] );
                        } else {
                            $default  = esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' );
                            $img_html = '<img src="' . $default . '" width="50" height="50" alt="Default Event">';
                        }

                        $page_id        = intval( $event['page_id'] );
                        $event_page_url = $page_id ? get_permalink( $page_id ) : '#';
                        $event_ute_id   = sanitize_text_field( $event['ute_id'] ?? '' );
                        $metrics        = TTA_Cache::remember(
                            'tta_bi_event_metrics_' . $event_ute_id,
                            function () use ( $wpdb, $event_ute_id ) {
                                if ( '' === $event_ute_id ) {
                                    return [
                                        'total_signups'  => 0,
                                        'total_attended' => 0,
                                        'revenue'        => 0,
                                        'refunds'        => 0,
                                        'net_profit'     => 0,
                                    ];
                                }
                                $att_table       = $wpdb->prefix . 'tta_attendees';
                                $att_archive     = $wpdb->prefix . 'tta_attendees_archive';
                                $tickets_table   = $wpdb->prefix . 'tta_tickets';
                                $tickets_archive = $wpdb->prefix . 'tta_tickets_archive';

                                $total_signups = (int) $wpdb->get_var(
                                    $wpdb->prepare(
                                        "SELECT COUNT(DISTINCT email) FROM (
                                            SELECT LOWER(a.email) AS email
                                            FROM {$att_table} a
                                            JOIN {$tickets_table} t ON a.ticket_id = t.id
                                            WHERE t.event_ute_id = %s
                                            UNION ALL
                                            SELECT LOWER(a.email) AS email
                                            FROM {$att_archive} a
                                            JOIN {$tickets_archive} t ON a.ticket_id = t.id
                                            WHERE t.event_ute_id = %s
                                        ) AS attendee_emails",
                                        $event_ute_id,
                                        $event_ute_id
                                    )
                                );

                                $total_attended = (int) $wpdb->get_var(
                                    $wpdb->prepare(
                                        "SELECT COUNT(DISTINCT email) FROM (
                                            SELECT LOWER(a.email) AS email
                                            FROM {$att_table} a
                                            JOIN {$tickets_table} t ON a.ticket_id = t.id
                                            WHERE t.event_ute_id = %s AND a.status = 'checked_in'
                                            UNION ALL
                                            SELECT LOWER(a.email) AS email
                                            FROM {$att_archive} a
                                            JOIN {$tickets_archive} t ON a.ticket_id = t.id
                                            WHERE t.event_ute_id = %s AND a.status = 'checked_in'
                                        ) AS attendee_emails",
                                        $event_ute_id,
                                        $event_ute_id
                                    )
                                );

                                $event_metrics = tta_get_event_metrics( $event_ute_id );
                                $revenue       = isset( $event_metrics['revenue'] ) ? (float) $event_metrics['revenue'] : 0;
                                $refunds       = isset( $event_metrics['refunded_amount'] ) ? (float) $event_metrics['refunded_amount'] : 0;

                                return [
                                    'total_signups'  => $total_signups,
                                    'total_attended' => $total_attended,
                                    'revenue'        => $revenue,
                                    'refunds'        => $refunds,
                                    'net_profit'     => $revenue - $refunds,
                                ];
                            },
                            300
                        );
                        ?>
                        <tr class="tta-bi-event-row" data-bi-event-id="<?php echo esc_attr( $event['id'] ); ?>">
                            <td><?php echo $img_html; ?></td>
                            <td><?php echo esc_html( $event['name'] ); ?></td>
                            <td><?php echo esc_html( date_i18n( 'n-j-Y', strtotime( $event['date'] ) ) ); ?></td>
                            <td><?php echo esc_html( $status ); ?></td>
                            <td>
                                <?php if ( $page_id ) : ?>
                                    <a href="<?php echo esc_url( $event_page_url ); ?>" target="_blank" rel="noopener">
                                        <?php esc_html_e( 'View Page', 'tta' ); ?>
                                    </a>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" class="tta-bi-event-view"><?php esc_html_e( 'View', 'tta' ); ?></a>
                            </td>
                            <td class="tta-toggle-cell">
                                <img
                                    src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/arrow.svg' ); ?>"
                                    class="tta-toggle-arrow"
                                    width="16"
                                    height="16"
                                    alt="<?php esc_attr_e( 'Toggle Details', 'tta' ); ?>">
                            </td>
                        </tr>
                        <script type="text/template" id="tta-bi-event-template-<?php echo esc_attr( $event['id'] ); ?>">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th>
                                            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Total unique people who claimed any ticket for this event, including refunded attendees.', 'tta' ); ?>" style="margin-left:4px;">
                                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="<?php esc_attr_e( 'Help', 'tta' ); ?>">
                                            </span>
                                            <label><?php esc_html_e( 'Total Signups', 'tta' ); ?></label>
                                        </th>
                                        <td><?php echo esc_html( number_format_i18n( $metrics['total_signups'] ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Total unique attendees marked as checked in for this event.', 'tta' ); ?>" style="margin-left:4px;">
                                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="<?php esc_attr_e( 'Help', 'tta' ); ?>">
                                            </span>
                                            <label><?php esc_html_e( 'Total Attended', 'tta' ); ?></label>
                                        </th>
                                        <td><?php echo esc_html( number_format_i18n( $metrics['total_attended'] ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Gross ticket sales for this event before refunds are applied.', 'tta' ); ?>" style="margin-left:4px;">
                                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="<?php esc_attr_e( 'Help', 'tta' ); ?>">
                                            </span>
                                            <label><?php esc_html_e( 'Total Ticket Sales', 'tta' ); ?></label>
                                        </th>
                                        <td><?php echo esc_html( '$' . number_format_i18n( $metrics['revenue'], 2 ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Total refunded amount issued for tickets associated with this event.', 'tta' ); ?>" style="margin-left:4px;">
                                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="<?php esc_attr_e( 'Help', 'tta' ); ?>">
                                            </span>
                                            <label><?php esc_html_e( 'Total Refunds Issued', 'tta' ); ?></label>
                                        </th>
                                        <td><?php echo esc_html( '$' . number_format_i18n( $metrics['refunds'], 2 ) ); ?></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Net profit after subtracting refunds from total ticket sales.', 'tta' ); ?>" style="margin-left:4px;">
                                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="<?php esc_attr_e( 'Help', 'tta' ); ?>">
                                            </span>
                                            <label><?php esc_html_e( 'Net Profit', 'tta' ); ?></label>
                                        </th>
                                        <td><?php echo esc_html( '$' . number_format_i18n( $metrics['net_profit'], 2 ) ); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </script>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="7"><?php esc_html_e( 'No archived events found.', 'tta' ); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $base = add_query_arg(
            [
                'page'  => 'tta-bi-dashboard',
                'tab'   => 'events',
                's'     => $search,
                'paged' => '%#%',
            ],
            admin_url( 'admin.php' )
        );

        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo paginate_links(
            [
                'base'      => $base,
                'format'    => '',
                'current'   => $paged,
                'total'     => ceil( $total / $per_page ),
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'end_size'  => 1,
                'mid_size'  => $paged === 1 ? 19 : 2,
            ]
        );
        echo '</div></div>';
        ?>
    <?php else : ?>
        <div class="notice notice-info">
            <p>
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: %s is the BI dashboard tab name. */
                        __( 'We are rebuilding the %s tab. New dashboard content will be added soon.', 'tta-management-plugin' ),
                        $tab_title
                    )
                );
                ?>
            </p>
        </div>
    <?php endif; ?>
</div>
