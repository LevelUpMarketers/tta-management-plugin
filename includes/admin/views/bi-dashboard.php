<?php
$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'events';
$tab_labels = [
    'events'  => 'Events',
    'members' => 'Members',
    'predict' => 'Predictive Analytics',
];
$tab_title = isset( $tab_labels[ $tab ] ) ? $tab_labels[ $tab ] : $tab_labels['events'];
?>
<div id="tta-bi-dashboard" class="wrap">
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
</div>
