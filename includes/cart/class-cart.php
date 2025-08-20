<?php
if ( ! defined('ABSPATH') ) exit;

class TTA_Cart {
  protected $wpdb;
  protected $carts_table;
  protected $items_table;
  protected $session_key;
  protected $cart_id;

  public function __construct() {
    global $wpdb;
    $this->wpdb        = $wpdb;
    $this->carts_table = $wpdb->prefix . 'tta_carts';
    $this->items_table = $wpdb->prefix . 'tta_cart_items';
    $this->init_session();
    // Only load an existing cart on construct; don't create one until needed.
    $this->ensure_cart( false );
  }

  protected function init_session() {
    if ( ! session_id() ) {
      session_start();
    }
    if ( empty($_SESSION['tta_cart_session']) ) {
      $_SESSION['tta_cart_session'] = wp_generate_uuid4();
    }
    $this->session_key = $_SESSION['tta_cart_session'];
  }

  protected function ensure_cart( $create = true ) {
    // load or optionally create a cart row
    $row = $this->wpdb->get_row(
      $this->wpdb->prepare(
        "SELECT * FROM {$this->carts_table} WHERE session_key = %s",
        $this->session_key
      ),
      ARRAY_A
    );
    if ( $row ) {
      $this->cart_id = (int)$row['id'];
    } elseif ( $create ) {
      $now_ts = current_time( 'timestamp', true );
      $now    = gmdate( 'Y-m-d H:i:s', $now_ts );
      $expires = gmdate( 'Y-m-d H:i:s', $now_ts + 1800 );
      $this->wpdb->insert(
        $this->carts_table,
        [
          'session_key' => $this->session_key,
          'user_id'     => get_current_user_id() ?: null,
          'created_at'  => $now,
          'expires_at'  => $expires,
        ],
        ['%s','%d','%s','%s']
      );
      $this->cart_id = (int)$this->wpdb->insert_id;
    }
  }

  /**
   * Ensure a cart row exists for the current session.
   *
   * Public wrapper used by AJAX handlers to guarantee the cart
   * table has an entry before manipulating items.
   */
  public function ensure_cart_exists() {
    $this->ensure_cart( true );
  }

  /**
   * Adjust ticket inventory when reserving or releasing items.
   *
   * @param int $ticket_id Ticket ID.
   * @param int $qty_diff  Positive number to release, negative to reserve.
   */
  protected function adjust_inventory( $ticket_id, $qty_diff ) {
    $ticket_id = intval( $ticket_id );
    $qty_diff  = intval( $qty_diff );
    if ( 0 === $qty_diff ) {
      return;
    }

    $current = (int) $this->wpdb->get_var( $this->wpdb->prepare(
        "SELECT ticketlimit FROM {$this->wpdb->prefix}tta_tickets WHERE id = %d",
        $ticket_id
    ) );
    $after   = $current + $qty_diff;
    $should_notify = ( $current <= 0 && $after > 0 );

    if ( $qty_diff < 0 ) {
      // Reserve stock only if enough tickets remain.
      $this->wpdb->query(
        $this->wpdb->prepare(
          "UPDATE {$this->wpdb->prefix}tta_tickets SET ticketlimit = ticketlimit + %d WHERE id = %d AND ticketlimit >= %d",
          $qty_diff,
          $ticket_id,
          -$qty_diff
        )
      );
    } else {
      // Release previously reserved stock.
      $this->wpdb->query(
        $this->wpdb->prepare(
          "UPDATE {$this->wpdb->prefix}tta_tickets SET ticketlimit = ticketlimit + %d WHERE id = %d",
          $qty_diff,
          $ticket_id
        )
      );
    }

    // Invalidate cached ticket data for this event.
    $event_ute_id = $this->wpdb->get_var(
      $this->wpdb->prepare(
        "SELECT event_ute_id FROM {$this->wpdb->prefix}tta_tickets WHERE id = %d",
        $ticket_id
      )
    );
    tta_clear_ticket_cache( $event_ute_id, $ticket_id );

    if ( $should_notify ) {
      tta_notify_waitlist_ticket_available( $ticket_id );
    }
  }

  public function add_item( $ticket_id, $qty, $price ) {
    $this->ensure_cart( true );
    $ticket_id = intval( $ticket_id );
    $qty       = intval( $qty );
    $event_ute = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT event_ute_id FROM {$this->wpdb->prefix}tta_tickets WHERE id = %d", $ticket_id ) );
    if ( $event_ute ) {
      tta_release_refund_tickets( $event_ute );
    }
    $existing_qty = (int) $this->wpdb->get_var(
      $this->wpdb->prepare(
        "SELECT quantity FROM {$this->items_table} WHERE cart_id = %d AND ticket_id = %d",
        $this->cart_id,
        $ticket_id
      )
    );

    if ( $qty <= 0 ) {
      if ( $existing_qty ) {
        $this->remove_item( $ticket_id );
      }
      return 0;
    }
    $diff = $qty - $existing_qty;

    if ( $diff > 0 ) {
      // Release expired reservations so stock reflects reality
      if ( class_exists( 'TTA_Cart_Cleanup' ) ) {
        TTA_Cart_Cleanup::clean_expired_items();
      }
      $available = (int) $this->wpdb->get_var(
        $this->wpdb->prepare(
          "SELECT ticketlimit FROM {$this->wpdb->prefix}tta_tickets WHERE id = %d",
          $ticket_id
        )
      );
      if ( $available < $diff ) {
        $qty  = $existing_qty + $available;
        $diff = $available;
      }
    }

    if ( $qty <= 0 ) {
      if ( $existing_qty ) {
        $this->remove_item( $ticket_id );
      }
      return 0;
    }

    if ( $diff !== 0 ) {
      $this->adjust_inventory( $ticket_id, -$diff );
    }

    if ( $existing_qty ) {
      $this->wpdb->update(
        $this->items_table,
        [ 'quantity' => $qty, 'price' => $price ],
        [ 'cart_id' => $this->cart_id, 'ticket_id' => $ticket_id ],
        ['%d','%f'],['%d','%d']
      );
    } else {
      $expire = gmdate( 'Y-m-d H:i:s', (int) current_time( 'timestamp', true ) + 600 );
      $this->wpdb->insert(
        $this->items_table,
        [
          'cart_id'   => $this->cart_id,
          'ticket_id' => $ticket_id,
          'quantity'  => $qty,
          'price'     => $price,
          'expires_at' => $expire,
        ],
        ['%d','%d','%d','%f','%s']
      );
    }

    return $qty;
  }

  public function update_quantity( $ticket_id, $qty ) {
    $this->ensure_cart( true );
    $ticket_id   = intval( $ticket_id );
    $qty         = intval( $qty );
    $existing_qty = (int) $this->wpdb->get_var(
      $this->wpdb->prepare(
        "SELECT quantity FROM {$this->items_table} WHERE cart_id = %d AND ticket_id = %d",
        $this->cart_id,
        $ticket_id
      )
    );

    // Enforce per-ticket and per-event purchase limits
    $row = $this->wpdb->get_row(
      $this->wpdb->prepare(
        "SELECT memberlimit, event_ute_id FROM {$this->wpdb->prefix}tta_tickets WHERE id = %d",
        $ticket_id
      ),
      ARRAY_A
    );
    $limit     = $row ? intval( $row['memberlimit'] ) : 2;
    if ( $limit < 1 ) { $limit = 2; }
    $event_ute = $row['event_ute_id'] ?? '';

    $event_limit = $event_ute ? (int) $this->wpdb->get_var(
      $this->wpdb->prepare(
        "SELECT SUM(memberlimit) FROM {$this->wpdb->prefix}tta_tickets WHERE event_ute_id = %s",
        $event_ute
      )
    ) : 0;
    if ( $event_limit < 1 ) { $event_limit = $limit; }

    $purchased_ticket = is_user_logged_in() ? tta_get_purchased_ticket_count_for_ticket( get_current_user_id(), $ticket_id ) : 0;
    $purchased_event  = is_user_logged_in() && $event_ute ? tta_get_purchased_ticket_count( get_current_user_id(), $event_ute ) : 0;

    $existing_event_total = 0;
    foreach ( $this->get_raw_items() as $row_it ) {
      if ( $row_it['event_ute_id'] === $event_ute ) {
        $existing_event_total += intval( $row_it['quantity'] );
      }
    }
    $existing_event_total -= $existing_qty;

    $ticket_allowed = max( 0, $limit - $purchased_ticket );
    $event_allowed  = max( 0, $event_limit - $purchased_event - $existing_event_total );
    $allowed        = min( $ticket_allowed, $event_allowed );
    if ( $qty > $allowed ) {
      $qty = $allowed;
    }

    if ( $qty <= 0 ) {
      $this->remove_item( $ticket_id );
      return 0;
    }

    $diff = $qty - $existing_qty;
    if ( $diff > 0 ) {
      // Expired items may hold inventory that should be freed
      if ( class_exists( 'TTA_Cart_Cleanup' ) ) {
        TTA_Cart_Cleanup::clean_expired_items();
      }
      $available = (int) $this->wpdb->get_var(
        $this->wpdb->prepare(
          "SELECT ticketlimit FROM {$this->wpdb->prefix}tta_tickets WHERE id = %d",
          $ticket_id
        )
      );
      if ( $available < $diff ) {
        $qty  = $existing_qty + $available;
        $diff = $available;
      }
    }

    if ( $diff !== 0 ) {
      $this->adjust_inventory( $ticket_id, -$diff );
    }

    if ( $existing_qty ) {
      $this->wpdb->update(
        $this->items_table,
        [ 'quantity' => $qty ],
        [ 'cart_id' => $this->cart_id, 'ticket_id' => $ticket_id ],
        ['%d'],['%d','%d']
      );
    } else {
      $expire = gmdate( 'Y-m-d H:i:s', (int) current_time( 'timestamp', true ) + 600 );
      $this->wpdb->insert(
        $this->items_table,
        [
          'cart_id'   => $this->cart_id,
          'ticket_id' => $ticket_id,
          'quantity'  => $qty,
          'price'     => 0,
          'expires_at' => $expire,
        ],
        ['%d','%d','%d','%f','%s']
      );
    }

    return $qty;
  }

  public function remove_item( $ticket_id ) {
    $this->ensure_cart( false );
    $ticket_id = intval( $ticket_id );
    if ( class_exists( 'TTA_Cart_Cleanup' ) ) {
      // Purge any expired rows so availability reflects the current database.
      TTA_Cart_Cleanup::clean_expired_items();
    }
    $qty = (int) $this->wpdb->get_var(
      $this->wpdb->prepare(
        "SELECT quantity FROM {$this->items_table} WHERE cart_id = %d AND ticket_id = %d",
        $this->cart_id,
        $ticket_id
      )
    );
    if ( $qty ) {
      $this->adjust_inventory( $ticket_id, $qty );
    }
    $this->wpdb->delete(
      $this->items_table,
      [ 'cart_id' => $this->cart_id, 'ticket_id' => $ticket_id ],
      [ '%d','%d' ]
    );

    $this->prune_discount_codes();
  }

  /**
   * Fetch cart items without expiring them.
   *
   * @return array
   */
  protected function get_raw_items() {
    $this->ensure_cart( false );
    return $this->wpdb->get_results(
      $this->wpdb->prepare(
        "SELECT ci.*, t.ticket_name, t.event_ute_id, t.baseeventcost, e.discountcode, e.name AS event_name, e.page_id
         FROM {$this->items_table} ci
         JOIN {$this->wpdb->prefix}tta_tickets t ON ci.ticket_id = t.id
         LEFT JOIN {$this->wpdb->prefix}tta_events e ON t.event_ute_id = e.ute_id
         WHERE ci.cart_id = %d",
        $this->cart_id
      ),
      ARRAY_A
    );
  }

  protected function expire_items() {
    $this->ensure_cart( false );
    $expired = $this->wpdb->get_results(
      $this->wpdb->prepare(
        "SELECT ticket_id FROM {$this->items_table} WHERE cart_id = %d AND expires_at <= %s",
        $this->cart_id,
        current_time('mysql')
      ),
      ARRAY_A
    );
    foreach ( $expired as $row ) {
      $this->remove_item( intval( $row['ticket_id'] ) );
    }
  }

  protected function prune_discount_codes() {
    if ( empty( $_SESSION['tta_discount_codes'] ) ) {
      return;
    }
    $codes = array_map( 'strtolower', (array) $_SESSION['tta_discount_codes'] );
    $active = [];
    foreach ( $this->get_raw_items() as $row ) {
      $info = tta_parse_discount_data( $row['discountcode'] );
      if ( $info['code'] ) {
        $active[] = strtolower( $info['code'] );
      }
    }
    $active = array_unique( $active );
    $_SESSION['tta_discount_codes'] = array_values( array_intersect( $codes, $active ) );
    if ( empty( $_SESSION['tta_discount_codes'] ) ) {
      unset( $_SESSION['tta_discount_codes'] );
    }
  }

  public function get_items() {
    $this->ensure_cart( false );
    $this->expire_items();
    return $this->wpdb->get_results(
      $this->wpdb->prepare(
          "SELECT ci.*, t.ticket_name, t.event_ute_id, t.baseeventcost, e.discountcode, e.name AS event_name, e.page_id
         FROM {$this->items_table} ci
         JOIN {$this->wpdb->prefix}tta_tickets t ON ci.ticket_id = t.id
         LEFT JOIN {$this->wpdb->prefix}tta_events e ON t.event_ute_id = e.ute_id
         WHERE ci.cart_id = %d",
        $this->cart_id
      ),
      ARRAY_A
    );
  }

  public function get_items_with_discounts( $discount_codes = [] ) {
    $codes = array_map( 'strtolower', (array) $discount_codes );
    $items = $this->get_items();
    $groups = [];
    foreach ( $items as &$it ) {
      $id               = $it['event_ute_id'];
      $groups[ $id ][]  =& $it;
    }
    unset( $it );

    foreach ( $groups as $event_items ) {
      $first  = $event_items[0];
      $info   = tta_parse_discount_data( $first['discountcode'] );
      $match  = $info['code'] && in_array( strtolower( $info['code'] ), $codes, true );
      $qtytot = 0;
      foreach ( $event_items as $it ) {
        $qtytot += intval( $it['quantity'] );
      }
      foreach ( $event_items as &$it ) {
        $price = floatval( $it['price'] );
        $final = $price;
        if ( $match ) {
          if ( 'percent' === $info['type'] ) {
            $final = $price * max( 0, 1 - ( $info['amount'] / 100 ) );
          } elseif ( $qtytot > 0 ) {
            $share = $info['amount'] / $qtytot;
            $final = max( 0, $price - $share );
          }
        }
        $it['final_price']     = round( $final, 2 );
        $it['discount_applied'] = $match;
      }
      unset( $it );
    }

    return $items;
  }

  public function get_total( $discount_codes = [], $include_membership = true ) {
    $total = 0;
    foreach ( $this->get_items_with_discounts( $discount_codes ) as $it ) {
      $total += $it['final_price'] * $it['quantity'];
    }
    if ( $include_membership && ! empty( $_SESSION['tta_membership_purchase'] ) ) {
      $total += tta_get_membership_price( $_SESSION['tta_membership_purchase'] );
    }

    $codes  = array_map( 'strtolower', (array) $discount_codes );
    $globals = array_filter( tta_get_global_discount_codes(), function ( $row ) use ( $codes ) {
      return $row['code'] && in_array( strtolower( $row['code'] ), $codes, true );
    } );
    foreach ( $globals as $g ) {
      if ( 'percent' === $g['type'] ) {
        $total *= max( 0, 1 - ( floatval( $g['amount'] ) / 100 ) );
      } else {
        $total = max( 0, $total - floatval( $g['amount'] ) );
      }
    }

    return round( $total, 2 );
  }


  /**
   * Ensure cart quantities reflect remaining ticket inventory.
   *
   * Items with no stock are removed and quantities are reduced when
   * fewer tickets remain than requested. Returns true if the cart was
   * modified in any way.
   */
  public function sync_with_inventory() {
    global $wpdb;
    $modified = false;
    foreach ( $this->get_items() as $item ) {
      $available = (int) $wpdb->get_var(
        $wpdb->prepare(
          "SELECT ticketlimit FROM {$wpdb->prefix}tta_tickets WHERE id = %d",
          intval( $item['ticket_id'] )
        )
      );
      if ( $available <= 0 ) {
        $this->remove_item( $item['ticket_id'] );
        $modified = true;
      } elseif ( $available < intval( $item['quantity'] ) ) {
        $this->update_quantity( $item['ticket_id'], $available );
        $modified = true;
      }
    }
    return $modified;
  }

  public function empty_cart() {
    $this->ensure_cart( false );
    $this->wpdb->delete(
      $this->items_table,
      [ 'cart_id' => $this->cart_id ],
      [ '%d' ]
    );
    $this->prune_discount_codes();
  }

  /**
   * Finalize checkout and trigger completion actions.
   *
   * This basic implementation simply empties the cart.
   * Hooked listeners can handle ticket delivery or payment processing.
   *
   * @param string $transaction_id Authorize.Net transaction ID.
   * @param float  $amount         Charged amount.
   * @param array  $attendees      Optional attendee info keyed by ticket ID.
   */
  public function finalize_purchase( $transaction_id = '', $amount = 0, array $attendees = [], $card_last4 = '' ) {
    global $wpdb;

    $discount_codes = $_SESSION['tta_discount_codes'] ?? [];
    $items          = $this->get_items_with_discounts( $discount_codes );

    $att_map        = [];
    foreach ( $attendees as $tid => $rows ) {
      $tid = intval( $tid );
      foreach ( (array) $rows as $row ) {
        $att_map[ $tid ][] = [
          'first_name' => tta_sanitize_text_field( $row['first_name'] ?? '' ),
          'last_name'  => tta_sanitize_text_field( $row['last_name'] ?? '' ),
          'email'      => tta_sanitize_email( $row['email'] ?? '' ),
          'phone'      => tta_sanitize_text_field( $row['phone'] ?? '' ),
          'opt_in_sms' => empty( $row['opt_in_sms'] ) ? 0 : 1,
          'opt_in_email' => empty( $row['opt_in_email'] ) ? 0 : 1,
        ];
      }
    }

    $discount_total = 0;
    $total_before   = 0;
    $total_after    = 0;


    foreach ( $items as &$item ) {
      $before       = $item['quantity'] * $item['price'];
      $after        = $item['quantity'] * $item['final_price'];
      $total_before += $before;
      $total_after  += $after;
      $item['discount_used']  = $item['discount_applied'] ? 1 : 0;
      $item['discount_saved'] = round( $before - $after, 2 );
      $item['attendees']      = $att_map[ intval( $item['ticket_id'] ) ] ?? [];
    }
    unset( $item );

    $discount_total = max( 0, $total_before - $total_after );

    // Log transaction details and send notifications
    if ( $transaction_id ) {
      $user_id = get_current_user_id();
      TTA_Transaction_Logger::log( $transaction_id, $amount, $items, implode( ',', $discount_codes ), $discount_total, $user_id, $card_last4 );
      TTA_Email_Handler::get_instance()->send_purchase_emails( $items, $user_id );
      TTA_SMS_Handler::get_instance()->send_purchase_texts( $items, $user_id );
      tta_remove_purchased_from_waitlists( $items, $user_id );
    }

    $this->empty_cart();
    $wpdb->delete( $this->carts_table, [ 'id' => $this->cart_id ], [ '%d' ] );

    unset( $_SESSION['tta_cart_session'] );
    unset( $_SESSION['tta_discount_codes'] );

    do_action( 'tta_checkout_complete', $this->cart_id );
  }
}
