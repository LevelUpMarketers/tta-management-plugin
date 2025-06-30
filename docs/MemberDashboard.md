# Member Dashboard

The Member Dashboard is accessible at `/member-dashboard/` via the shortcode `[tta_member_dashboard]`.
It presents four tabs: **Profile Info**, **Your Upcoming Events**, **Your Past Events**, and **Billing & Membership Info**. Tooltip icons now appear before each field label for quicker context. The tooltip text is displayed instantly on hover using visibility toggles instead of opacity fades. The dashboard's JavaScript and CSS are enqueued whenever the page is viewed so tab switching works even when not logged in.

If a member is banned the dashboard displays a prominent notice at the top explaining the ban duration and purchases are blocked until it expires.

## Upcoming Events Tab

The **Your Upcoming Events** tab lists future events you have tickets for. Each event
shows:

- The event thumbnail image
- Event name linking to the event page
- Event date and time
- Event location
- The total amount paid for the transaction
- Each ticket purchased with the attendee names and emails
- A link to request a refund (paid events) or cancel attendance (free events) which reveals a small form

Events are loaded chronologically and the layout supports any number of events.
Attendee details are pulled from the transaction history and stored in the
`tta_attendees` table.
Event thumbnails use the medium image size and are scaled to a consistent width so nothing is cropped.

## Past Events Tab

Past events show the same details as upcoming events. To keep the database small, events more than three days past are moved to an `tta_events_archive` table by a daily cron job. The dashboard transparently queries both the current events table and this archive so members can always view their history.

## Billing & Membership Info

The billing tab now displays the member's current plan and subscription status. When a Basic or Premium plan is active, a **Cancel Membership** button appears. Submitting the form calls an AJAX endpoint that shows a loading spinner and returns a success or error message. On success the membership level reverts to **Free**, the status changes to *Cancelled*, and the button disappears.
If the subscription remains active, the last four digits of the stored payment method are retrieved directly from Authorize.Net and displayed. Members can update the card by submitting a second form which calls `TTA_AuthorizeNet_API::update_subscription_payment()` via AJAX. The update form now requires the cardholder's billing address (first name, last name, street, city, state and ZIP) in addition to the card details. The plugin never stores any full payment data.
Subscription metadata is stored in two columns on `tta_members`:

- `subscription_id` – Authorize.Net identifier for the recurring payment
- `subscription_status` – either `active` or `cancelled`

Below the membership controls is a **Payment History** table. It lists all
transactions in chronological order including event purchases logged in the
`tta_transactions` table and monthly membership charges retrieved from the
Authorize.Net API. Event names link to their event pages even after the
events move into the archive, and each row displays the date, item name, and
amount charged.
