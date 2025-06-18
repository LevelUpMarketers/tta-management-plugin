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
      return;
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

  public function get_total( $discount_code = '' ) {
    $total = 0;
    $info  = null;
    foreach ( $this->get_items() as $it ) {
      $sub = $it['quantity'] * $it['price'];
      $total += $sub;
      if ( null === $info && $discount_code ) {
        $d = tta_parse_discount_data( $it['discountcode'] );
        if ( $d['code'] && strtolower( $discount_code ) === strtolower( $d['code'] ) ) {
          $info = $d;
        }
      }
    }

    if ( $discount_code && $info ) {
      if ( 'flat' === $info['type'] ) {
        $total = max( 0, $total - $info['amount'] );
      } else {
        $total *= max( 0, 1 - ( $info['amount'] / 100 ) );
      }
    }

    return $total;
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

    $discount_code = $_SESSION['tta_discount_code'] ?? '';
    $items         = $this->get_items();
    $discount_total = 0;
    $discount_info  = null;
    $total_before   = 0;

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
      $sub  = $item['quantity'] * $item['price'];
      $total_before += $sub;

      if ( null === $discount_info && $discount_code ) {
        $info = tta_parse_discount_data( $item['discountcode'] );
        if ( $info['code'] && strtolower( $discount_code ) === strtolower( $info['code'] ) ) {
          $discount_info = $info;
        }
      }
    }
    unset( $item );

    if ( $discount_code && $discount_info ) {
      if ( 'flat' === $discount_info['type'] ) {
        $discount_total = min( $total_before, $discount_info['amount'] );
      } else {
        $discount_total = $total_before * ( $discount_info['amount'] / 100 );
      }
    }

    // Distribute savings proportionally across items
    foreach ( $items as &$item ) {
      $sub = $item['quantity'] * $item['price'];
      $share = $total_before > 0 ? ( $sub / $total_before ) : 0;
      $item_saved = $discount_total * $share;
      $item['discount_used']  = $discount_total > 0 ? 1 : 0;
      $item['discount_saved'] = round( $item_saved, 2 );
    }
    unset( $item );

    // Log transaction details
    if ( $transaction_id ) {
      TTA_Transaction_Logger::log( $transaction_id, $amount, $items, $discount_code, $discount_total );
    }

    $this->empty_cart();
    $wpdb->delete( $this->carts_table, [ 'id' => $this->cart_id ], [ '%d' ] );

    unset( $_SESSION['tta_cart_session'] );
    unset( $_SESSION['tta_discount_code'] );

    do_action( 'tta_checkout_complete', $this->cart_id );
  }
}
