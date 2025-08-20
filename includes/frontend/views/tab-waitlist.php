<!-- WAITLIST EVENTS -->
<div id="tab-waitlist" class="tta-dashboard-section" style="display:none;">
  <h3><?php esc_html_e( 'Your Waitlist Events', 'tta' ); ?></h3>
  <?php
  $events = tta_get_member_waitlist_events( get_current_user_id() );
  if ( $events ) :
      foreach ( $events as $ev ) :
          $thumb = '';
          if ( ! empty( $ev['image_id'] ) ) {
              $thumb = wp_get_attachment_image( $ev['image_id'], 'medium' );
          } elseif ( ! empty( $ev['page_id'] ) ) {
              $thumb = get_the_post_thumbnail( $ev['page_id'], 'medium' );
          }

          $date_str = date_i18n( get_option( 'date_format' ), strtotime( $ev['date'] ) );
          $time_str = '';
          if ( ! empty( $ev['time'] ) ) {
              $parts     = array_pad( explode( '|', $ev['time'] ), 2, '' );
              $start_fmt = $parts[0] ? date_i18n( get_option( 'time_format' ), strtotime( $parts[0] ) ) : '';
              $end_fmt   = $parts[1] ? date_i18n( get_option( 'time_format' ), strtotime( $parts[1] ) ) : '';
              $time_str  = trim( $start_fmt . ( $end_fmt ? ' – ' . $end_fmt : '' ) );
          }
          ?>
          <div class="tta-upcoming-event-indiv-holder">
          <div class="tta-upcoming-event">
            <?php if ( $thumb ) : ?>
              <div class="tta-upcoming-thumb"><?php echo $thumb; ?></div>
            <?php endif; ?>
            <div class="tta-upcoming-info">
              <h4><a href="<?php echo esc_url( get_permalink( $ev['page_id'] ) ); ?>"><?php echo esc_html( $ev['name'] ); ?></a></h4>
              <p class="tta-upcoming-date"><?php echo esc_html( $date_str . ( $time_str ? ' – ' . $time_str : '' ) ); ?></p>
              <p class="tta-upcoming-address"><?php echo esc_html( tta_format_address( $ev['address'] ) ); ?></p>
              <p><strong><?php echo esc_html( $ev['ticket_name'] ); ?></strong></p>
              <button type="button" class="tta-leave-waitlist" data-event="<?php echo esc_attr( $ev['event_ute_id'] ); ?>" data-ticket="<?php echo esc_attr( $ev['ticket_id'] ); ?>">
                <?php esc_html_e( 'Leave the Waitlist', 'tta' ); ?>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
  <?php else : ?>
      <p><?php esc_html_e( 'You are not currently on any waitlists.', 'tta' ); ?></p>
  <?php endif; ?>
</div>
