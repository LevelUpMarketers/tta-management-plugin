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
          <div class="tta-upcoming-event-indiv-holder">
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
            <?php $first_att = reset( $it['attendees'] ); ?>
            <div class="tta-ticket-details">
              <strong><?php echo esc_html( $it['ticket_name'] ); ?> (<?php echo intval( $it['quantity'] ); ?>)</strong>
              <?php if ( isset( $it['final_price'] ) ) : ?>
                <span class="tta-ticket-price"><?php printf( esc_html__( '$%s', 'tta' ), number_format_i18n( floatval( $it['final_price'] ), 2 ) ); ?></span>
              <?php endif; ?>
              <?php if ( ! empty( $it['refund_pending'] ) ) : ?>
                <p class="tta-refund-pending">
                  <?php
                  $ra = $it['refund_attendee'] ?? [];
                  $name = trim( ( $ra['first_name'] ?? '' ) . ' ' . ( $ra['last_name'] ?? '' ) );
                  $email = $ra['email'] ?? '';
                  if ( $name || $email ) {
                      printf( esc_html__( '%1$s (%2$s) - refund request pending', 'tta' ), esc_html( $name ), esc_html( $email ) );
                  } else {
                      esc_html_e( 'Refund request pending', 'tta' );
                  }
                  ?>
                </p>
              <?php elseif ( ! empty( $it['refund_approved'] ) ) : ?>
                <p class="tta-refund-approved">
                  <?php
                  $ra = $it['refund_attendee'] ?? [];
                  $name = trim( ( $ra['first_name'] ?? '' ) . ' ' . ( $ra['last_name'] ?? '' ) );
                  $email = $ra['email'] ?? '';
                  $amt = $it['refund_amount'] ?? 0;
                  $amount = sprintf( '$%s', number_format_i18n( floatval( $amt ), 2 ) );
                  if ( $name || $email ) {
                      printf( esc_html__( '%1$s (%2$s) - %3$s refund request approved & attendance cancelled', 'tta' ), esc_html( $name ), esc_html( $email ), esc_html( $amount ) );
                  } else {
                      printf( esc_html__( '%s refund request approved & attendance cancelled', 'tta' ), esc_html( $amount ) );
                  }
                  ?>
                </p>
              <?php elseif ( ! empty( $it['refund_keep'] ) ) : ?>
                <p class="tta-refund-keep">
                  <?php
                  $ra     = $it['refund_attendee'] ?? [];
                  $name   = trim( ( $ra['first_name'] ?? '' ) . ' ' . ( $ra['last_name'] ?? '' ) );
                  $email  = $ra['email'] ?? '';
                  $amt    = $it['refund_amount'] ?? 0;
                  $amount = sprintf( '$%s', number_format_i18n( floatval( $amt ), 2 ) );
                  $full   = isset( $it['final_price'] ) && abs( floatval( $it['final_price'] ) - floatval( $amt ) ) < 0.01;
                  if ( $name || $email ) {
                      if ( $full ) {
                          printf( esc_html__( '%1$s (%2$s) - %3$s refund processed and attendance kept', 'tta' ), esc_html( $name ), esc_html( $email ), esc_html( $amount ) );
                      } else {
                          printf( esc_html__( '%1$s (%2$s) - %3$s partial refund processed and attendance kept', 'tta' ), esc_html( $name ), esc_html( $email ), esc_html( $amount ) );
                      }
                  } else {
                      if ( $full ) {
                          printf( esc_html__( '%s refund processed and attendance kept', 'tta' ), esc_html( $amount ) );
                      } else {
                          printf( esc_html__( '%s partial refund processed and attendance kept', 'tta' ), esc_html( $amount ) );
                      }
                  }
                  ?>
                </p>
              <?php else : ?>
                <ul class="tta-attendees">
              <?php foreach ( (array) ( $it['attendees'] ?? [] ) as $att ) : ?>
                <li><?php echo esc_html( trim( $att['first_name'] . ' ' . $att['last_name'] ) . ' (' . $att['email'] . ')' ); ?></li>
              <?php endforeach; ?>
              </ul>
              <?php endif; ?>
              <?php if ( empty( $it['refund_pending'] ) && empty( $it['refund_approved'] ) && empty( $it['refund_keep'] ) && intval( $it['purchaser_id'] ?? 0 ) === get_current_user_id() ) : ?>
              <div class="tta-refund-wrapper">
                <?php if ( $ev['amount'] > 0 ) : ?>
                  <a href="#" class="tta-refund-link" data-tx="<?php echo esc_attr( $ev['transaction_id'] ); ?>" data-event="<?php echo esc_attr( $ev['event_id'] ); ?>" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>" data-attendee="<?php echo esc_attr( $first_att['id'] ?? '' ); ?>">
                    <?php esc_html_e( 'Request a Refund', 'tta' ); ?>
                  </a>
                <?php else : ?>
                  <a href="#" class="tta-cancel-link" data-tx="<?php echo esc_attr( $ev['transaction_id'] ); ?>" data-event="<?php echo esc_attr( $ev['event_id'] ); ?>" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>">
                    <?php esc_html_e( 'Cancel Attendance', 'tta' ); ?>
                  </a>
                <?php endif; ?>
                <form class="tta-refund-form" data-tx="<?php echo esc_attr( $ev['transaction_id'] ); ?>" data-event="<?php echo esc_attr( $ev['event_id'] ); ?>" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>" data-attendee="<?php echo esc_attr( $first_att['id'] ?? '' ); ?>">
                  <span class="description"><?php esc_html_e( 'Tell us why you\'re requesting a refund', 'tta' ); ?></span>
                  <textarea id="refund-<?php echo esc_attr( $ev['transaction_id'] . '-' . $it['ticket_id'] ); ?>" placeholder="<?php esc_attr_e( 'Tell us why you\'re requesting a refund', 'tta' ); ?>"></textarea>
                  <button type="button" class="tta-refund-submit" data-tx="<?php echo esc_attr( $ev['transaction_id'] ); ?>" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>" data-attendee="<?php echo esc_attr( $first_att['id'] ?? '' ); ?>">
                    <?php echo $ev['amount'] > 0 ? esc_html__( 'Request a Refund', 'tta' ) : esc_html__( 'Cancel Attendance', 'tta' ); ?>
                  </button>
                  <span class="tta-progress-spinner">
                    <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
                  </span>
                  <span class="tta-admin-progress-response-p"></span>
                </form>
              </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          <div class="tta-assistance-form">
            <label for="assist-<?php echo esc_attr( $ev['event_id'] ); ?>"><?php esc_html_e( 'Can\'t find the group? Gonna be late? Need something else? Message the event host below.', 'tta' ); ?></label>
            <textarea id="assist-<?php echo esc_attr( $ev['event_id'] ); ?>" rows="3"></textarea>
            <button type="button" class="button tta-assistance-submit" data-ute="<?php echo esc_attr( tta_get_event_ute_id( $ev['event_id'] ) ); ?>">
              <?php esc_html_e( 'Send', 'tta' ); ?>
            </button>
            <span class="tta-progress-spinner">
              <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
            </span>
            <span class="tta-admin-progress-response-p"></span>
          </div>
        </div>
      <?php endforeach; ?>
  <?php else : ?>
      <p><?php esc_html_e( 'No upcoming events found.', 'tta' ); ?></p>
  <?php endif; ?>
</div>

