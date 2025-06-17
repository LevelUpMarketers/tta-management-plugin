# Cart and Checkout Flow

This document summarizes the current logic around the cart and checkout process in the Trying To Adult Management Plugin. Future documentation will cover other areas such as creating or editing events and members.

## Overview

1. **Adding Tickets**
   - Visitors interact with the **Event Page** template. When the page loads a `TTA_Cart` instance is created so session data exists early.
   - Ticket details are fetched from the database. Prices vary depending on membership level (`free`, `basic`, or `premium`).
   - When a user adds tickets, the browser issues an AJAX request to `tta_add_to_cart`. The handler calculates the appropriate price and calls `TTA_Cart::add_item()`.
   - Cart data is stored in the `tta_carts` and `tta_cart_items` tables keyed by a session ID.

2. **Viewing the Cart**
   - The **Cart Page** template renders the current cart contents using `tta_render_cart_contents()`.
   - Each cart row now shows the linked event name above the ticket type along with a live five minute countdown.
   - Countdown timers remove items immediately when they expire.
   - Quantities and discount codes are updated via the `tta_update_cart` AJAX endpoint. This calls `TTA_Cart::update_quantity()` and stores a discount code in the session.

3. **Checkout**
   - The **Checkout Page** template performs checkout when the form is submitted (`tta_do_checkout`).
   - `TTA_Cart::sync_with_inventory()` ensures requested quantities are still available. If inventory changed, a notice is stored and the user is redirected back to the cart.
   - A total is calculated with any discount code applied. Payment details are sent to `TTA_AuthorizeNet_API::charge()`.
   - On success, `TTA_Cart::finalize_purchase()` reduces ticket inventory atomically, logs the transaction, clears the cart tables, and triggers the `tta_checkout_complete` action. On failure, an error message is shown.

4. **Cleanup**
   - `TTA_Cart_Cleanup` schedules an hourly task and also runs on checkout completion to remove expired cart rows.

## Branching Logic Highlights

- Pricing logic branches on membership level when adding items to the cart.
- Each member may purchase a maximum of two tickets per event. Quantities in the cart plus past purchases are checked during the `tta_add_to_cart` AJAX request.
- Checkout can branch if inventory changes mid-process, redirecting back to the cart with a notice.
- Payment failure stops checkout and displays the returned error.
- Successful completion empties the cart and fires hooks for additional actions (e.g., ticket emails).

This flow will evolve as more features are added. Additional documentation for creating events, editing events, managing members, and other future functionality will live alongside this document in the `docs/` directory.
