<!-- UPCOMING EVENTS -->
<div id="tab-upcoming" class="tta-dashboard-section">
  <h3><?php esc_html_e( 'Your Upcoming Events', 'tta' ); ?></h3>
  <?php
  $events = tta_get_member_upcoming_events( get_current_user_id() );
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
          <div class="tta-upcoming-event">
            <?php if ( $thumb ) : ?>
              <div class="tta-upcoming-thumb"><?php echo $thumb; ?></div>
            <?php endif; ?>
            <div class="tta-upcoming-info">
              <h4><a href="<?php echo esc_url( get_permalink( $ev['page_id'] ) ); ?>"><?php echo esc_html( $ev['name'] ); ?></a></h4>
              <p class="tta-upcoming-date"><?php echo esc_html( $date_str . ( $time_str ? ' – ' . $time_str : '' ) ); ?></p>
              <p class="tta-upcoming-address"><?php echo esc_html( tta_format_address( $ev['address'] ) ); ?></p>
              <p class="tta-upcoming-total"><?php printf( esc_html__( 'Total Paid: $%s', 'tta' ), number_format( $ev['amount'], 2 ) ); ?></p>
            </div>
          </div>
          <?php foreach ( $ev['items'] as $it ) : ?>
            <div class="tta-ticket-details">
              <strong><?php echo esc_html( $it['ticket_name'] ); ?> (<?php echo intval( $it['quantity'] ); ?>)</strong>
              <ul class="tta-attendees">
              <?php foreach ( (array) ( $it['attendees'] ?? [] ) as $att ) : ?>
                <li><?php echo esc_html( trim( $att['first_name'] . ' ' . $att['last_name'] ) . ' (' . $att['email'] . ')' ); ?></li>
              <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
          <div class="tta-refund-wrapper">
            <?php if ( $ev['amount'] > 0 ) : ?>
              <a href="#" class="tta-refund-link" data-tx="<?php echo esc_attr( $ev['transaction_id'] ); ?>">
                <?php esc_html_e( 'Request a Refund', 'tta' ); ?>
              </a>
            <?php else : ?>
              <a href="#" class="tta-cancel-link" data-tx="<?php echo esc_attr( $ev['transaction_id'] ); ?>">
                <?php esc_html_e( 'Cancel Attendance', 'tta' ); ?>
              </a>
            <?php endif; ?>
            <form class="tta-refund-form" data-tx="<?php echo esc_attr( $ev['transaction_id'] ); ?>">
              <label for="refund-<?php echo esc_attr( $ev['transaction_id'] ); ?>">
                <?php esc_html_e( 'Refund Request Details', 'tta' ); ?>
              </label>
              <span class="description"><?php esc_html_e( 'tell us why you\'re requesting a refund', 'tta' ); ?></span>
              <textarea id="refund-<?php echo esc_attr( $ev['transaction_id'] ); ?>" placeholder="<?php esc_attr_e( 'tell us why you\'re requesting a refund', 'tta' ); ?>"></textarea>
              <button type="button" class="tta-refund-submit" data-tx="<?php echo esc_attr( $ev['transaction_id'] ); ?>">
                <?php esc_html_e( 'Request Refund', 'tta' ); ?>
              </button>
            </form>
          </div>
      <?php endforeach; ?>
  <?php else : ?>
      <p><?php esc_html_e( 'No upcoming events found.', 'tta' ); ?></p>
  <?php endif; ?>
</div>

