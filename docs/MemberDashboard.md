# Member Dashboard

The Member Dashboard is accessible at `/member-dashboard/` via the shortcode `[tta_member_dashboard]`.
When a visitor is not logged in, the dashboard still displays all tabs but each one contains a login form with an option to register or become a member.
It presents five tabs: **Profile Info**, **Your Upcoming Events**, **Your Waitlist Events**, **Your Past Events**, and **Billing & Membership Info**. Tooltip icons now appear before each field label for quicker context. The tooltip text is displayed instantly on hover using visibility toggles instead of opacity fades. The dashboard's JavaScript and CSS are enqueued whenever the page is viewed so tab switching works even when not logged in.

On screens narrower than 1200px the dashboard switches to a mobile-friendly layout. The sidebar tabs become full-width accordion headers; tapping a header expands its section while collapsing the others. Each tab’s form and fields remain intact inside its accordion panel.

When rendered the shortcode inserts a WPBakery hero banner above the dashboard using `do_shortcode()`. The banner displays **Member Dashboard** across a full-width background image.

If a member is banned the dashboard displays a prominent notice at the top. Indefinite bans simply state that purchases are blocked and direct members to the contact page, re-entry bans note that a Re-Entry Ticket is required, and timed bans show the reinstatement date or remaining weeks.
Non-admin users never see the WordPress dashboard. On login the page simply reloads, and any attempt to access `/wp-admin/` redirects back to the front end.


## Upcoming Events Tab

The **Your Upcoming Events** tab lists future events you have tickets for. Each event
shows:

- The event thumbnail image
- Event name linking to the event page
- Event date and time
- Event location
- The total amount paid for the transaction
- Each ticket purchased with the attendee names and emails and its individual price
- A separate link labeled **Request a Refund** for each ticket (paid events) or **Cancel Attendance** for free events which reveals a small form. This link only appears for tickets you purchased yourself. When multiple of the same ticket type are purchased they appear as individual entries so refunds can be requested per attendee
- Each refund link now carries the attendee ID so the correct person is removed when multiple identical tickets exist in one transaction
- If a refund is pending for one attendee the entry remains with a pending note while any remaining attendees still appear separately with their own refund links
        - Submitting the form shows a spinner and records a `refund_request` entry in `tta_memberhistory`. The attendee is removed immediately. The refund is processed automatically once another member buys that ticket, otherwise the request expires two hours before the event. If the transaction has not settled the refund is scheduled for the next Authorize.Net settlement window (around 3:15 AM) and reattempted until it succeeds. Administrators can review pending requests on the **TTA Refund Requests** admin page where they remain listed until processed.
- Once a refund is approved the entry stays visible until the event date with a note showing the refunded amount and that the attendee has been cancelled. The refund link and form are removed so no further requests can be made.
- If an administrator issues a refund but keeps attendance, the entry remains with a note reflecting the status. Pending refunds display "refund request pending" until processed. Once processed, it shows either "refund processed and attendance kept" for full refunds or "partial refund processed and attendance kept" for partial refunds. In all cases, the refund link is removed.
- Successful submission displays the message "Your refund request has been submitted! Per our Refund Policy, once all remaining tickets are sold, your ticket will be available for purchase by other members. Once it's sold, you'll automatically receive a refund. There's nothing else for you to do! Check back here periodically to see the status of your refund request."
- After submitting, the page no longer reloads automatically; the refund button and link are disabled so the confirmation message stays visible.
- Each event includes a small form to send a message to the hosts and volunteers. Submitted notes are stored with your tickets and emailed to the hosts so they can assist you on event day.
- When a member purchases tickets on behalf of another member the additional attendee's upcoming events cache is cleared so the new event appears immediately on their dashboard.

Events are loaded chronologically and the layout supports any number of events.
Attendee details are pulled from the transaction history and stored in the
`tta_attendees` table.
When a single checkout includes tickets for multiple events each event now
receives its own history record so it appears individually in this list.
Event thumbnails use the medium image size and are scaled to a consistent width so nothing is cropped.
Attendee lists now reflect the database in real time. When a member requests a refund the attendee is removed from the list but the ticket entry remains with a "refund request pending" note until another member buys the ticket or an admin processes the refund manually from the ticket editor. Events only disappear once no attendees or pending requests remain.

## Past Events Tab

Past events show the same details as upcoming events. To keep the database small, events more than three days past are moved to an `tta_events_archive` table by a daily cron job. The dashboard transparently queries both the current events table and this archive so members can always view their history.

- Each attendee entry also lists their final attendance status (Attended, No-Show, or Pending) along with any refund notes.
- A summary box at the top displays how many events you've attended and no‑showed along with your total savings. The savings amount is wrapped in a `<span class="tta-savings-wow-span">` element so it can be styled prominently. The message varies by membership level—Basic members are prompted to upgrade, Premium members see a referral link, and Free members get an invitation to join.
- Ticket details remain enclosed within each past event's container so stray markup doesn't spill outside the dashboard layout.

## Billing & Membership Info

The billing tab now displays the member's current plan and subscription status. When a Basic or Premium plan is active, a **Cancel Membership** button appears. Submitting the form calls an AJAX endpoint that shows a loading spinner and returns a success or error message. On success the membership level reverts to **Free**, the status changes to *Cancelled*, and the button disappears.
If the subscription remains active, the last four digits of the stored payment method are retrieved directly from Authorize.Net and displayed. Members can update the card by submitting a second form which calls `TTA_AuthorizeNet_API::update_subscription_payment()` via AJAX. The update form now requires the cardholder's billing address (first name, last name, street, city, state and ZIP) in addition to the card details and those fields are pre‑filled with the address from the member profile. The plugin never stores any full payment data.
Subscription metadata is stored in two columns on `tta_members`:

- `subscription_id` – Authorize.Net identifier for the recurring payment
- `subscription_status` – `active`, `cancelled`, or `paymentproblem`

When a membership is cancelled the action is recorded in `tta_memberhistory`.
If the dashboard detects a cancelled status it shows the date of cancellation,
who initiated it and the last four digits of the card used along with a button
to reactivate the previous level. If the subscription status is *paymentproblem*
the tab displays the gateway's reported status and prompts the member to update
their card information directly on the dashboard.
If no membership is active and there is no payment issue, a simple message
"You do not currently have a paid membership." appears instead of the controls.

Below the membership controls is a **Payment History** table. On screens narrower than 1200px the table is replaced with a stacked mobile layout for readability. It lists all
transactions in chronological order including event purchases logged in the
`tta_transactions` table, any refunds processed, and monthly membership charges
retrieved from the Authorize.Net API. Refund transactions now appear alongside
all other charges so members can see every adjustment to their account.
Charges related to a membership, including the first month billed at checkout
and all recurring payments, use the transaction type **Membership Subscription**
in the history table.
Event names link to their event pages even after the events move into the
archive, and each row displays the date, item name, amount charged, the gateway
transaction ID, transaction type, and the payment method used. Refunds appear as
negative amounts in the table.
