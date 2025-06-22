# Member Dashboard

The Member Dashboard is accessible at `/member-dashboard/` via the shortcode `[tta_member_dashboard]`.
It presents four tabs: **Profile Info**, **Your Upcoming Events**, **Your Past Events**, and **Billing & Membership Info**.

## Upcoming Events Tab

The **Your Upcoming Events** tab lists future events you have tickets for. Each event
shows:

- The event thumbnail image
- Event name linking to the event page
- Event date and time
- The total amount paid for the transaction
- Each ticket purchased with the attendee names and emails

Events are loaded chronologically and the layout supports any number of events.
Attendee details are pulled from the transaction history and stored in the
`tta_attendees` table.
