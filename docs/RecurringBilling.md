# Recurring Billing via Authorize.Net

The plugin creates monthly subscriptions using `TTA_AuthorizeNet_API::create_subscription()`. The function builds an `ARBCreateSubscriptionRequest` with a one month interval. When a membership is purchased the first month is charged immediately and the subscription start date is set one month in the future. In the sandbox environment that start date is automatically shifted one day earlier so the first recurring charge processes during nightly batch jobs. Subscriptions are created during the JavaScript-based checkout flow, which now requires Accept.js tokenization; if encryption fails the checkout is blocked until the member can retry with a valid token. When a description is provided, it is stored via the request's `Order` element because the `ARBSubscriptionType` class does not include a direct description field.

According to Authorize.Net's documentation, the first transaction processed for a new subscription is handled just like any other payment and is **not** flagged as recurring. Subsequent payments are marked as recurring by the gateway. Individual charges are generated automatically after 2 a.m. PST based on the schedule.

Make sure the **Automated Recurring Billing** module is active on your merchant account or no subscriptions will be created.

To verify in the sandbox:

1. Complete checkout with a Standard or Premium membership in the cart.
2. In the Merchant Interface, open **Recurring Billing > Search** to view the new subscription by ID.
3. Transaction history will show the initial payment (if the start date is the same day it may post the next business day) followed by monthly charges.

Use the subscription ID stored in the `tta_members` table to manage the plan or cancel it via the admin tools. New member records begin with `subscription_status` set to `NULL` and are updated to `active` only when a Standard or Premium membership is purchased. When a member buys another membership, any prior subscription ID on file is cancelled through Authorize.Net before the new subscription is created so the member only ever has one active plan.
Members may also upgrade or downgrade their plan from the Billing & Membership Info tab. The change uses `update_subscription_amount()` to adjust the monthly charge, subscription name, and description in Authorize.Net before updating the `tta_members` record.

After a successful membership checkout the confirmation page now displays the returned
`subscriptionId` along with the API result code. If the ID is missing, the profile
was not created and the message will contain the error details reported by
Authorize.Net.

Each time a user logs in the plugin checks the status of any stored subscription.
The status is also verified once per day for active sessions and again whenever a
member views the **Billing & Membership Info** tab. If the gateway reports a
problem, the member's `membership_level` is temporarily set to `free` and
`subscription_status` is set to `paymentproblem`. The most recent recurring
transaction returned by Authorize.Net is also inspected; if its status is
`declined`, the member is likewise downgraded to `free/paymentproblem` even when
the subscription itself still shows as active. The dashboard then displays a
subscription issue notice with full billing and address fields plus a link to purchase a new membership.
If the newest transaction entry comes back with a `NULL` ID (for example, after a
gateway "general error"), the plugin skips any further transaction-status calls
and immediately flags the account as `free/paymentproblem` so the member does not
retain paid access while the issue is unresolved. Members in a `paymentproblem`
state also see a sitewide alert bar prompting them to visit the Billing tab or
start a new membership; the banner can be dismissed for the current session and
automatically hides whenever a cart countdown alert needs to be shown.
When new payment information is submitted the plugin attempts to retry the failed charge immediately—on success the stored
membership level and `subscription_status` return to `active`.

Payment updates now pull the `customerProfileId` and `customerPaymentProfileId` from `ARBGetSubscription` and send the Accept.js
opaque token to an `updateCustomerPaymentProfile` request. Because subscriptions reference that payment profile, updating it
refreshes the billing method without sending raw card data to Authorize.Net.

## Converting Past Transactions

Existing one‑time transactions can be turned into recurring subscriptions
directly from the admin area. Under **TTA Settings → API Settings** enter one or
more Authorize.Net transaction IDs (one per line) and click **Convert to
Subscription**. The plugin retrieves the transaction details for each ID,
creates an Automated Recurring Billing subscription for the same amount and
stores the returned subscription ID in the matching `tta_members` record based
on the billing email. The member's `subscription_status` is set to `active` and
the `membership_level` updated to `basic` or `premium` depending on the charge
amount. To ensure Authorize.Net associates the correct billing method, the
subscription request references the payment profile via the
`customerPaymentProfileId` field.
Transactions for $10 are tagged as **Trying to Adult Standard Membership** while $17
charges become **Trying to Adult Premium Membership** so the subscription is
clearly labeled in Authorize.Net. The results of each conversion are displayed
on the settings page and written to the debug log.

