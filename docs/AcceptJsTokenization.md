# Accept.js Tokenization

The checkout page now uses [Authorize.Net Accept.js](https://developer.authorize.net/api/reference/features/acceptjs.html) to tokenize payment details in the browser before sending them to the server.

## Assets
- `Accept.js` is loaded from Authorize.Net's CDN based on the sandbox or live mode.
- Handler script: `assets/js/frontend/tta-accept-checkout.js` intercepts the checkout form and exchanges card data for an opaque token.
- If tokenization is unavailable, the script posts the raw card details as a fallback so checkout can continue.

## AJAX flow
1. The token and billing details are posted to `admin-ajax.php` via the `tta_process_payment` action (the action is passed in the query string to satisfy WordPress's admin-ajax routing).
2. The handler sanitizes the request, builds an invoice and description, and calls the existing `TTA_AuthorizeNet_API::charge()` method.
3. The charge prefers opaque data and falls back to raw PAN only when no token is provided.

Successful responses return a JSON object containing the `transaction_id`; failures return a friendly error message displayed in `#tta-checkout-response`.
