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
    $this->ensure_cart();
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

  protected function ensure_cart() {
    // load or create a cart row
    $row = $this->wpdb->get_row(
      $this->wpdb->prepare(
        "SELECT * FROM {$this->carts_table} WHERE session_key = %s",
        $this->session_key
      ),
      ARRAY_A
    );
    if ( $row ) {
      $this->cart_id = (int)$row['id'];
    } else {
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

  public function add_item( $ticket_id, $qty, $price ) {
    if ( $qty <= 0 ) {
      $this->remove_item( $ticket_id );
      return;
    }

    $exists = $this->wpdb->get_var(
      $this->wpdb->prepare(
        "SELECT COUNT(*) FROM {$this->items_table} WHERE cart_id = %d AND ticket_id = %d",
        $this->cart_id,
        $ticket_id
      )
    );
    if ( $exists ) {
      $this->wpdb->update(
        $this->items_table,
        [ 'quantity' => $qty, 'price' => $price ],
        [ 'cart_id' => $this->cart_id, 'ticket_id' => $ticket_id ],
        ['%d','%f'],['%d','%d']
      );
    } else {
      $this->wpdb->insert(
        $this->items_table,
        [
          'cart_id'   => $this->cart_id,
          'ticket_id' => $ticket_id,
          'quantity'  => $qty,
          'price'     => $price,
        ],
        ['%d','%d','%d','%f']
      );
    }
  }

  public function update_quantity( $ticket_id, $qty ) {
    if ( $qty <= 0 ) {
      $this->remove_item( $ticket_id );
    } else {
      $this->wpdb->update(
        $this->items_table,
        [ 'quantity' => $qty ],
        [ 'cart_id' => $this->cart_id, 'ticket_id' => $ticket_id ],
        ['%d'],['%d','%d']
      );
    }
  }

  public function remove_item( $ticket_id ) {
    $this->wpdb->delete(
      $this->items_table,
      [ 'cart_id' => $this->cart_id, 'ticket_id' => $ticket_id ],
      [ '%d','%d' ]
    );
  }

  public function get_items() {
    return $this->wpdb->get_results(
      $this->wpdb->prepare(
        "SELECT ci.*, t.ticket_name, e.discountcode
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
    foreach ( $this->get_items() as $it ) {
      $sub = $it['quantity'] * $it['price'];
      if ( $discount_code && strtolower( $discount_code ) === strtolower( $it['discountcode'] ) ) {
        $sub *= 0.9; // 10% discount
      }
      $total += $sub;
    }
    return $total;
  }

  public function empty_cart() {
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
  public function finalize_purchase() {
    $this->empty_cart();
    if ( isset( $_SESSION['tta_discount_code'] ) ) {
      unset( $_SESSION['tta_discount_code'] );
    }
    do_action( 'tta_checkout_complete', $this->cart_id );
  }
}
