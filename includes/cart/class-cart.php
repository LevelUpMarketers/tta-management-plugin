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
      $now = current_time('mysql');
      $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
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
    if ( $event_ute_id ) {
      TTA_Cache::delete( 'tickets_' . $event_ute_id );
    }
  }

  public function add_item( $ticket_id, $qty, $price ) {
    $this->ensure_cart( true );
    $ticket_id = intval( $ticket_id );
    $qty       = intval( $qty );
    if ( $qty <= 0 ) {
      $this->remove_item( $ticket_id );
      return;
    }

    $existing_qty = (int) $this->wpdb->get_var(
      $this->wpdb->prepare(
        "SELECT quantity FROM {$this->items_table} WHERE cart_id = %d AND ticket_id = %d",
        $this->cart_id,
        $ticket_id
      )
    );
    $diff = $qty - $existing_qty;

    if ( $diff > 0 ) {
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
        [ 'quantity' => $qty, 'price' => $price ],
        [ 'cart_id' => $this->cart_id, 'ticket_id' => $ticket_id ],
        ['%d','%f'],['%d','%d']
      );
    } else {
      $expire = date( 'Y-m-d H:i:s', time() + 300 );
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

    // Enforce two ticket limit per event in total
    $event_ute = $this->wpdb->get_var(
      $this->wpdb->prepare(
        "SELECT event_ute_id FROM {$this->wpdb->prefix}tta_tickets WHERE id = %d",
        $ticket_id
      )
    );
    if ( $event_ute ) {
      $event_total = 0;
      foreach ( $this->get_items() as $row ) {
        if ( $row['event_ute_id'] === $event_ute && intval( $row['ticket_id'] ) !== $ticket_id ) {
          $event_total += intval( $row['quantity'] );
        }
      }
      $purchased = is_user_logged_in() ? tta_get_purchased_ticket_count( get_current_user_id(), $event_ute ) : 0;
      $allowed   = max( 0, 2 - $purchased - $event_total );
      if ( $qty > $allowed ) {
        $qty = $allowed;
      }
    }

    if ( $qty <= 0 ) {
      $this->remove_item( $ticket_id );
      return 0;
    }

    $diff = $qty - $existing_qty;
    if ( $diff > 0 ) {
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
      $expire = date( 'Y-m-d H:i:s', time() + 300 );
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
    if ( ! empty( $_SESSION['tta_cart_locked'] ) ) {
      return;
    }
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

  public function get_total( $discount_codes = [] ) {
    $total = 0;
    foreach ( $this->get_items_with_discounts( $discount_codes ) as $it ) {
      $total += $it['final_price'] * $it['quantity'];
    }
    return $total;
  }

  /**
   * Extend expiration time for all items in the cart.
   *
   * @param int $seconds Seconds from now to set new expiration.
   */
  public function extend_expiration( $seconds ) {
    $this->ensure_cart( false );
    $expire = date( 'Y-m-d H:i:s', time() + absint( $seconds ) );
    $this->wpdb->update(
      $this->items_table,
      [ 'expires_at' => $expire ],
      [ 'cart_id' => $this->cart_id ],
      [ '%s' ],
      [ '%d' ]
    );
    $this->wpdb->update(
      $this->carts_table,
      [ 'expires_at' => $expire ],
      [ 'id' => $this->cart_id ],
      [ '%s' ],
      [ '%d' ]
    );
  }

  /**
   * Freeze countdowns when the user begins checkout.
   */
  public function lock_items() {
    $this->ensure_cart( false );
    if ( empty( $this->cart_id ) ) {
      return;
    }

    $_SESSION['tta_cart_locked'] = true;
    $_SESSION['tta_lock_remaining'] = [];

    $items = $this->get_raw_items();
    $future = date( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS );
    foreach ( $items as $row ) {
      $remain = max( 0, strtotime( $row['expires_at'] ) - time() );
      $_SESSION['tta_lock_remaining'][ intval( $row['ticket_id'] ) ] = $remain;
    }

    $this->wpdb->update(
      $this->items_table,
      [ 'expires_at' => $future ],
      [ 'cart_id' => $this->cart_id ],
      [ '%s' ],
      [ '%d' ]
    );
    $this->wpdb->update(
      $this->carts_table,
      [ 'expires_at' => $future ],
      [ 'id' => $this->cart_id ],
      [ '%s' ],
      [ '%d' ]
    );
  }

  /**
   * Resume countdowns after a failed checkout.
   */
  public function resume_items() {
    $this->ensure_cart( false );
    if ( empty( $this->cart_id ) ) {
      return;
    }

    $remain = $_SESSION['tta_lock_remaining'] ?? [];
    if ( $remain ) {
      foreach ( $remain as $ticket => $sec ) {
        $expire = date( 'Y-m-d H:i:s', time() + absint( $sec ) );
        $this->wpdb->update(
          $this->items_table,
          [ 'expires_at' => $expire ],
          [ 'cart_id' => $this->cart_id, 'ticket_id' => intval( $ticket ) ],
          [ '%s' ],
          [ '%d','%d' ]
        );
      }
      $max = max( $remain );
      $this->wpdb->update(
        $this->carts_table,
        [ 'expires_at' => date( 'Y-m-d H:i:s', time() + absint( $max ) ) ],
        [ 'id' => $this->cart_id ],
        [ '%s' ],
        [ '%d' ]
      );
    } else {
      $this->extend_expiration( 300 );
    }
    unset( $_SESSION['tta_cart_locked'], $_SESSION['tta_lock_remaining'] );
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
  }

  /**
   * Finalize checkout and trigger completion actions.
   *
   * This basic implementation simply empties the cart.
   * Hooked listeners can handle ticket delivery or payment processing.
   */
  public function finalize_purchase( $transaction_id = '', $amount = 0 ) {
    global $wpdb;

    $discount_codes = $_SESSION['tta_discount_codes'] ?? [];
    $items          = $this->get_items_with_discounts( $discount_codes );
    $discount_total = 0;
    $total_before   = 0;
    $total_after    = 0;

    // Check availability before attempting updates
    foreach ( $items as $chk ) {
      $available = (int) $wpdb->get_var(
        $wpdb->prepare(
          "SELECT ticketlimit FROM {$wpdb->prefix}tta_tickets WHERE id = %d",
          intval( $chk['ticket_id'] )
        )
      );
      if ( $available < intval( $chk['quantity'] ) ) {
        return new WP_Error( 'tta_sold_out', __( 'One or more tickets are no longer available.', 'tta' ) );
      }
    }

    foreach ( $items as &$item ) {
      $before       = $item['quantity'] * $item['price'];
      $after        = $item['quantity'] * $item['final_price'];
      $total_before += $before;
      $total_after  += $after;
      $item['discount_used']  = $item['discount_applied'] ? 1 : 0;
      $item['discount_saved'] = round( $before - $after, 2 );
    }
    unset( $item );

    $discount_total = max( 0, $total_before - $total_after );

    // Log transaction details
    if ( $transaction_id ) {
      TTA_Transaction_Logger::log( $transaction_id, $amount, $items, implode( ',', $discount_codes ), $discount_total );
    }

    $this->empty_cart();
    $wpdb->delete( $this->carts_table, [ 'id' => $this->cart_id ], [ '%d' ] );

    unset( $_SESSION['tta_cart_session'] );
    unset( $_SESSION['tta_discount_codes'] );
    unset( $_SESSION['tta_cart_locked'], $_SESSION['tta_lock_remaining'] );

    do_action( 'tta_checkout_complete', $this->cart_id );
  }
}
