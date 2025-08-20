<!-- PAST EVENTS -->
<div id="tab-past" class="tta-dashboard-section">
  <h3><?php esc_html_e( 'Your Past Events', 'tta' ); ?></h3>
  <?php
  $user_id = get_current_user_id();
  $summary = tta_get_member_attendance_summary( $user_id );
  $level   = tta_get_user_membership_level( $user_id );
  ?>
  <div class="tta-attendance-summary-div">
    <?php if ( 'premium' === $level ) : ?>
      <p class="tta-attendance-summary">
        <?php
        echo wp_kses(
            sprintf(
                /* translators: %s: amount saved */
                __( 'Your membership and discount codes have saved you a total of <span class="tta-savings-wow-span">$%s!</span> Did you know you can receive a referral bonus, to include free events? <a href="/referral-program">Click here to learn more now!</a>', 'tta' ),
                number_format( $summary['savings'], 2 )
            ),
            array(
                'a'    => array( 'href' => array() ),
                'span' => array( 'class' => array() ),
            )
        );
        ?>
      </p>
    <?php elseif ( 'basic' === $level ) : ?>
      <p class="tta-attendance-summary">
        <?php
        echo wp_kses(
            sprintf(
                /* translators: %s: amount saved */
                __( 'Your membership and discount codes have saved you a total of <span class="tta-savings-wow-span">$%s!</span> <a href="/become-a-member">Upgrade to a Premium Membership now</a> to save even more!', 'tta' ),
                number_format( $summary['savings'], 2 )
            ),
            array(
                'a'    => array( 'href' => array() ),
                'span' => array( 'class' => array() ),
            )
        );
        ?>
      </p>
    <?php else : ?>
      <p class="tta-attendance-summary">
        <?php
        echo wp_kses(
            __( 'Did you know? Members receive discounts and extra perks. <a href="/become-a-member">Become a member now and start saving!</a>', 'tta' ),
            array( 'a' => array( 'href' => array() ) )
        );
        ?>
      </p>
    <?php endif; ?>
    <p class="tta-attendance-summary tta-attendance-summary-totals"><span class="tta-bmi-bold"><?php printf( esc_html__( 'Total Events Attended:', 'tta' ) ); ?></span> <?php echo intval( $summary['attended'] ); ?></p>
    <p class="tta-attendance-summary tta-attendance-summary-totals"><span class="tta-bmi-bold"><?php printf( esc_html__( 'Total Event No-Shows:', 'tta' ) ); ?></span> <?php echo intval( $summary['no_show'] ); ?></p>
  </div>
  <?php

  $events = tta_get_member_past_events( $user_id );
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
          <span class="tta-for-margin-and-nothing-else-ha-ha"></span>
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
            <div class="tta-ticket-details">
              <strong><?php echo esc_html( $it['ticket_name'] ); ?> (<?php echo intval( $it['quantity'] ); ?>)</strong>
              <ul class="tta-attendees">
              <?php foreach ( (array) ( $it['attendees'] ?? [] ) as $att ) : ?>
                <li>
                  <?php echo esc_html( trim( $att['first_name'] . ' ' . $att['last_name'] ) . ' (' . $att['email'] . ')' ); ?>
                  <span class="tta-attendance-status">
                    <?php
                    $st = $att['status'] ?? 'pending';
                    $map = [
                      'checked_in' => __( 'Attended', 'tta' ),
                      'no_show'    => __( 'No-Show', 'tta' ),
                      'pending'    => __( 'Pending', 'tta' ),
                    ];
                    echo esc_html( $map[ $st ] ?? ucwords( str_replace( '_', ' ', $st ) ) );
                    ?>
                  </span>
                </li>
              <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
  <?php else : ?>
      <p><?php esc_html_e( 'No past events found.', 'tta' ); ?></p>
  <?php endif; ?>
</div>