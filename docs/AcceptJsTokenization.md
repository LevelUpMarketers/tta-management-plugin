# Accept.js Tokenization

The checkout page now uses [Authorize.Net Accept.js](https://developer.authorize.net/api/reference/features/acceptjs.html) to tokenize payment details in the browser before sending them to the server. When the order total is `$0.00`, this tokenization step is skipped and the checkout finalizes immediately without contacting Authorize.Net. For any paid order, tokenization is mandatory—unencrypted card details are never posted to WordPress.

## Assets
- `Accept.js` is loaded from Authorize.Net's CDN based on the sandbox or live mode.
- Handler script: `assets/js/frontend/tta-accept-checkout.js` intercepts the checkout form and exchanges card data for an opaque token.
- The script blocks submission when encryption fails (e.g., Accept.js unavailable) and surfaces the localized "Encryption of your payment information failed…" message so the member can retry or contact support.
- The script requires the API Login ID and a public **client key**. Enter this Client Key under **TTA Settings → API Settings**; the value is stored in the `tta_authnet_public_client_key_*` options or may come from the `TTA_AUTHNET_CLIENT_KEY` environment variable.

## AJAX flow
1. The token and billing details are posted to `admin-ajax.php` via the `tta_checkout` action.
2. The handler sanitizes the request, builds an invoice, and composes an order description from the cart's event names and any membership purchase. The description is stripped of non-ASCII characters and truncated to Authorize.Net's 255 character limit before charging.
3. The charge requires opaque data for any paid amount. Requests without a token are rejected before Authorize.Net is contacted.

Successful responses return a JSON object containing the `transaction_id`; failures return a friendly error message displayed in `#tta-checkout-response`.
