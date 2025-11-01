# Cart and Checkout Flow

This document summarizes the current logic around the cart and checkout process in the Trying To Adult Management Plugin. Future documentation will cover other areas such as creating or editing events and members.

## Overview

1. **Adding Tickets**
   - Visitors interact with the **Event Page** template. When the page loads a `TTA_Cart` instance is created so session data exists early.
   - Ticket details are fetched from the database. Prices vary depending on membership level (`free`, `basic`, or `premium`).
   - Expired cart items are cleared before ticket data loads so availability displays correctly.
   - Whenever a new item is added or a quantity increases, expired reservations are purged to free any leftover stock.
   - Expiration timestamps use WordPress local time so cleanup works consistently across servers.
  - Quantity selectors on the event page enforce each ticket's per‑member limit. Past purchases reduce the remaining allowance so members immediately see a notice if they already bought the maximum. The notice now clarifies when the limit is reached because of a previous transaction. Sold out ticket rows have their quantity controls disabled and the **Get Tickets** button is disabled if no tickets remain.
   - When a user adds tickets, the browser issues an AJAX request to `tta_add_to_cart`. The handler calculates the price, reserves inventory, and calls `TTA_Cart::add_item()`. If the request fails (for example, the member is banned), a modal overlay displays the error message and any HTML links remain clickable.
   - The AJAX handler now explicitly creates a cart row first via `ensure_cart_exists()` so empty databases start tracking sessions immediately.
   - Cart data is stored in the `tta_carts` and `tta_cart_items` tables keyed by a session ID. Ticket availability is decreased immediately on add and the related event cache is cleared.

2. **Viewing the Cart**
   - The **Cart Page** template renders the current cart contents using `tta_render_cart_contents()`.
   - The cart table is wrapped in a `.tta-cart-table-wrapper` element so it can scroll horizontally on narrower screens, and CSS now stacks table cells for easier reading on phones.
   - A WPBakery hero banner appears above the cart using `do_shortcode()`.
   - A **Browse More Events** button sits beside the Checkout action. It links back to the most recent events list page the visitor viewed so they can continue exploring without relying on the browser back button.
   - If a visitor selected a membership on the Become a Member page, that membership appears as its own line item labeled **Standard Membership** or **Premium Membership**.
   - Premium members cannot add another membership at all. Attempts to add Standard or Premium memberships are rejected.
   - Membership rows never display the **Ticket Reserved for…** column. When tickets are also present, the membership's **Event or Item** cell spans both columns so the layout remains aligned. Pricing shows "Per Month" in the price and subtotal columns, and the total row also displays "Per Month".
   - Membership line items include `data-label` attributes so mobile views show field names like **Event or Item**, **Quantity**, **Price**, and **Subtotal**. On phones, the remove button sits centered beneath each item without a surrounding border, matching ticket rows.
   - If both a membership and tickets are present, the total row displays the first charge (e.g. `$15.00 today, $10 Per Month`) so customers understand future recurring payments.
  - When a membership is present, a note below the total summarizes the immediate charge and the monthly renewal date and now reminds members they can manage billing on their profile and view the refund policy (e.g. "You will be billed $10.00 today to begin your membership, and a recurring $10.00 on the 15th of every month. If you wish to cancel your membership, you can do so at any time on your member profile. For questions about refunds, visit our Rules & Policies page.").
   - A dedicated **Ticket Reserved for…** column displays a live ten minute countdown for ticket rows.
  - The Quantity column enforces the per‑member limit configured for each ticket.
   - Discount codes are applied via an **Apply Discount** button. Multiple codes can be active and are split across matching event tickets. Active codes list the related event name in parentheses and appear beneath the cart total for easy removal.
   - The Price column always shows the base cost (e.g. `$20 x 2` when quantity is two). Subtotals strike through the original amount when discounts are applied.
  - Quantity updates that exceed a ticket's limit display an inline notice beside the input.
   - Countdown timers remove items immediately when they expire.
    - Expired items are batched into a single AJAX request so multiple events expiring at once do not overload the database.
    - Timers calculate remaining time from the expiration timestamp so they stay accurate when the tab is hidden.
    - Timers restart after any AJAX update or when the page regains focus.
   - Quantities and discount codes are updated via the `tta_update_cart` AJAX endpoint. This calls `TTA_Cart::update_quantity()` and stores applied codes in the session.
   - When all tickets for an event are removed, any related discount codes are automatically cleared from the session.
   - Removing an item now also purges expired cart rows and frees its reserved stock immediately so other visitors can purchase the ticket without refreshing the event page.
   - If stock sells out before reaching the cart, the table is replaced with a notice explaining that the last ticket was just reserved. The notice includes a **Join The Waitlist** button and the Checkout button is disabled until another item is added.
   - The ticket quantity buttons on the event page now verify current stock via AJAX. When the last ticket becomes unavailable in another member's cart, clicking the **+** button shows the same notice inline and the quantity does not increase or redirect to the cart.

3. **Checkout**
  - The **Checkout Page** template performs checkout via an AJAX request (`tta_do_checkout`). The page fades while a spinner remains visible until the final response and always waits at least five seconds before displaying the result under the **Place Order** button.
   - A matching WPBakery hero banner is displayed at the top of the page.
  - Inventory is reserved when items are added to the cart. Checkout no longer revalidates stock so users can complete a purchase with their held tickets as long as the reservation has not expired.
  - Checkout displays a read-only summary table that mirrors the cart layout with tooltips, countdown timers, and a list of active discount codes below the total.
  - A **Browse More Events** button sits directly beneath the summary so visitors can jump back to the last events listing they viewed without relying on the browser history.
  - Membership items use the same **Standard Membership** and **Premium Membership** labels and include `data-label` attributes for mobile views. The **Ticket Reserved for…** column is never shown on membership rows.
  - The checkout summary table now uses the same responsive styling as the cart so it scrolls horizontally and stacks cells with labels on small screens. Attendee and billing inputs also stack vertically on phones for easier entry when many tickets are present.
  - Attendee fields collect a first name, last name, email, and phone for each ticket. A "text me" and "email me" checkbox is included and checked by default. The first ticket for every event autofills with the logged‑in member's details. Those first name, last name, and email fields are displayed but locked from editing. Phone numbers are automatically formatted as the user types.
   - Countdown timers run just like on the cart page. If a timer reaches zero the item is removed and totals update automatically.
   - The `tta_update_cart` AJAX endpoint returns updated markup for both the cart table and checkout summary so timers can refresh either view.
  - Visitors must be logged in to complete checkout. When not authenticated a **Log in or Register** accordion appears above the form, matching the one used on event and dashboard pages. If a membership is in the cart, the accordion changes to **Register Below to Complete Your Membership Purchase** and displays the registration form by default with a link to switch to the login form. In all cases the login portion submits through WordPress while the registration form uses AJAX. All attendee and billing inputs stay disabled until authentication succeeds and the page reloads.
  - On screens under 782px, the **Already have an Account? Log in here!** link displays on its own line for easier tapping on phones.
  - After login or account creation, if the cart's membership matches the member's current plan or represents a downgrade from Premium to Standard, the membership item is automatically removed and a notice links to the Member Dashboard for managing their subscription.
  - Billing now requires **Street Address**, **Address Line 2**, **City**, **State** and **ZIP** fields. The front-end validates these before the purchase can proceed.
  - When the final total is `$0.00`, only the card number, expiration, and CVC inputs are disabled. A notice instructs members that no payment is required and the **Place Order** button remains active to complete the checkout.
  - Zero‑dollar orders bypass Authorize.Net tokenization entirely. Clicking **Place Order** immediately finalizes the purchase without loading Accept.js.
  - The server finalizes the cart whenever tickets are present—even when discounts reduce the total to `$0.00`. Membership‑only carts skip finalization and are logged separately to prevent duplicate transaction rows.
  - For paid orders, the payment request's description lists the event names in the cart and any membership purchase. The text is cleaned of non-ASCII characters and clipped to Authorize.Net's 255 character ceiling.
  - A total is calculated with any discount code applied. Card data is tokenized in the browser via Authorize.Net Accept.js and the token is sent to the `tta_process_payment` AJAX handler. The server charges the token through `TTA_AuthorizeNet_API::charge()` and returns a transaction ID. The charge request now asks Authorize.Net to create a customer profile when none exists so the gateway response includes the `customerProfileId`/`customerPaymentProfileId`. If Accept.js omits those IDs, checkout immediately calls `create_profile_from_transaction()` to create or fetch them before building the subscription. Responses follow WordPress's `{success:bool, data:{...}}` JSON convention and the Accept.js script unwraps the `data` object to retrieve the transaction ID and gateway diagnostics. If a membership is in the cart we first attempt to create the recurring plan with `TTA_AuthorizeNet_API::create_subscription_from_profile()` using the freshly issued profile IDs, falling back to `create_subscription_from_transaction()` only when both the live response and the follow-up lookup fail to provide usable profile data. When tickets and a membership are checked out together, attendee details are still required for the tickets and the subscription is created in the same request. Checkout works even when no tickets are present.
  - On success, `TTA_Cart::finalize_purchase()` logs the transaction, stores each ticket's attendee info in the `tta_attendees` table, clears the cart tables, removes all discount codes, and triggers the `tta_checkout_complete` action. Inventory has already been reserved when items were added. The checkout page then displays a custom message summarizing any membership purchased and lists the logged‑in member's email along with every unique attendee address. Duplicate addresses are removed case‑insensitively while preserving the original casing, and the receipt list only appears when tickets were part of the order. The AJAX response normalizes the email array so the front‑end receives a proper list when multiple events are purchased in one transaction. The message expands automatically so all email addresses remain visible regardless of list length.
  - After a successful purchase, anyone associated with the transaction is removed from the waitlist for the tickets they bought.

  - Each cart now includes a unique **checkout key**. This key is sent with the payment request, stored on the `tta_transactions` table and used as the Authorize.Net `invoiceNumber`. Before charging, the server checks for an existing row with that key to avoid duplicate transactions and provide atomic, idempotent checkout.
  - The **Place Order** button is disabled while a request is in progress and this state persists across reloads via `sessionStorage`. If the browser is refreshed mid‑checkout the button remains disabled and the page polls a `tta_checkout_status` endpoint until the original request completes.

4. **Cleanup**
   - `TTA_Cart_Cleanup` schedules an hourly task and also runs on checkout completion to remove expired cart rows.
   - A second cron task runs every ten minutes to purge expired cart items and free their ticket inventory.
   - Expired items are deleted from carts automatically and their reserved stock is released back to the ticket pool. When this occurs the event ticket cache is also cleared so front‑end counts stay in sync.

## Branching Logic Highlights

- Pricing logic branches on membership level when adding items to the cart.
- Each member may purchase up to the limit specified for each ticket. Quantities in the cart plus past purchases are checked during the `tta_add_to_cart` AJAX request, and the summed limits for all tickets cap a member's total for the event.
- Checkout can branch if inventory changes mid-process, redirecting back to the cart with a notice.
 - Payment failure stops checkout and displays the returned error. The plugin now surfaces Authorize.Net error codes and descriptions (for example `11: A duplicate transaction has been submitted`). If the code is recognized, an extra sentence explains what it means.
- Successful completion empties the cart and fires hooks for additional actions (e.g., ticket emails).

This flow will evolve as more features are added. Additional documentation for creating events, editing events, managing members, and other future functionality will live alongside this document in the `docs/` directory.
