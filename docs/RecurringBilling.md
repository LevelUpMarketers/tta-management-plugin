# Recurring Billing via Authorize.Net

The plugin creates monthly subscriptions using `TTA_AuthorizeNet_API::create_subscription()`. The function builds an `ARBCreateSubscriptionRequest` with a one month interval. When running in the sandbox environment the start date is automatically set to **yesterday** so the first charge processes in the next batch. In production the start date is today. Subscriptions are now created during the JavaScript-based checkout flow as well as the non‑JavaScript fallback so the behavior is consistent for all users. When a description is provided, it is stored via the request's `Order` element because the `ARBSubscriptionType` class does not include a direct description field.

According to Authorize.Net's documentation, the first transaction processed for a new subscription is handled just like any other payment and is **not** flagged as recurring. Subsequent payments are marked as recurring by the gateway. Individual charges are generated automatically after 2 a.m. PST based on the schedule.

Make sure the **Automated Recurring Billing** module is active on your merchant account or no subscriptions will be created.

To verify in the sandbox:

1. Complete checkout with a Basic or Premium membership in the cart.
2. In the Merchant Interface, open **Recurring Billing > Search** to view the new subscription by ID.
3. Transaction history will show the initial payment (if the start date is the same day it may post the next business day) followed by monthly charges.

Use the subscription ID stored in the `tta_members` table to manage the plan or cancel it via the admin tools. The member row is updated when the profile is created so `membership_level` records the purchased plan and `subscription_status` becomes `active`.

After a successful membership checkout the confirmation page now displays the returned
`subscriptionId` along with the API result code. If the ID is missing, the profile
was not created and the message will contain the error details reported by
Authorize.Net.
