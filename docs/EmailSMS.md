# Email and SMS Templates

The plugin sends automated notifications to members. Administrators can manage these messages from **TTA Email & SMS** in the WordPress admin. The page contains three tabs:

1. **Email Templates** – existing template editor described below.
2. **Email Logs** – lists scheduled reminder and thank‑you emails grouped by event. Reminder jobs are automatically queued whenever an event is created or its start time changes and are scheduled using the site's timezone setting. Each entry shows the send time in `MM-DD-YYYY HH:MMAM/PM` format along with a live `HH H, MM M, SS S` countdown using the site's timezone, exposes its current recipient list via AJAX, and can be deleted before it runs.
3. **Email History** – a running log of all attempted emails including recipient address and delivery result. A **Clear Log** button removes all entries.

Templates are listed in a table similar to the Manage Events page. Click a row to expand an inline form containing the fields for that communication. Each form has its own **Save Changes** button and progress spinner.

## Available Templates

| Key | Description |
|-----|-------------|
| `purchase` | Sent after a successful event purchase. Includes event details automatically. |
| `reminder_24hr` | Sent 24 hours before an event starts. |
| `reminder_2hr` | Sent two hours before an event starts. |
| `refund_requested` | Sent to a member when they request a refund. |
| `refund_processed` | Sent to attendees when a refund request is approved and issued. |
| `event_sold_out` | Internal alert when an event reaches capacity. |
| `waitlist_available` | Sent when a ticket becomes available for someone on the waitlist. |
| `host_reminder_24hr` | Reminder to event hosts 24 hours before their event. |
| `host_reminder_2hr` | Reminder to event hosts two hours before their event. |
| `volunteer_reminder_24hr` | Reminder to volunteers 24 hours before their event. |
| `volunteer_reminder_2hr` | Reminder to volunteers two hours before their event. |
| `assistance_request` | Sent to event hosts when a member asks for help finding the group. |
| `post_event_review` | Sent 24 hours after an event is archived to attendees marked as checked in. |

Each template stores:

- **Type** – whether the message is sent to members (External) or used internally
- **Category** – grouping such as Event Reminder or Event Confirmation
- **Email Subject** – subject line of the email
- **Email Body** – text shown above the automatically generated event details
- **SMS Text** – short message sent via SMS

All fields are sanitized with the helper functions from `InputSanitization.md`. This strips WordPress slashes so apostrophes display correctly in the admin preview and in the actual emails.

Default values are provided on initial install:

- **Purchase Email Subject**: "Thanks for Registering!"
- **Purchase Email Body**: "You're in! Thank for registering for our upcoming Trying To Adult event. The details of the event are below. Please keep this email, as you'll need to present this to the Event Host or Volunteer when arriving at your event."
- **Purchase SMS**: "Thanks for registering! View your upcoming events at "
- **24-Hour Reminder Email Body**: "Heads-up! Your event is just 1 day away! Below are the details."
- **2-Hour Reminder Email Body**: "Your event is only 2 hours away! Below are the details."
- **Admin Notifications**: internal alerts are sent when events sell out.
- **Host and Volunteer Reminders**: internal messages mirror attendee reminders at 24 and 2 hours before the event.

Links to the member dashboard now output the full site URL and include direct links to each dashboard tab, including the waitlist view.

## Previews and Tokens

Editing a template shows live **Email Preview** and **SMS Preview** boxes. Values are updated whenever an input field loses focus. For reminder and purchase templates, the preview substitutes details from the soonest upcoming event in the database, choosing the earliest date and start time.

SMS previews show a character count. If the text exceeds 160 characters the count turns red to indicate the message may be split by carriers.

Buttons labelled with tokens (e.g. `{event_name}`) insert placeholders into the last focused field. The sending logic will replace these tokens with real data. Available tokens include:

### Event Information

```
{event_name}
{event_address}
{event_address_link}
{event_link}
{dashboard_profile_url}
{dashboard_upcoming_url}
{dashboard_waitlist_url}
{dashboard_past_url}
{dashboard_billing_url}
{event_date}
{event_time}
{event_type}
{venue_name}
{venue_url}
{base_cost}
{member_cost}
{premium_cost}
```

`{event_address_link}` outputs a Google Maps URL for the event address.

Dashboard URL tokens accept an optional `anchor` attribute. For example:

```
{dashboard_upcoming_url anchor="see your upcoming events"}
```

This outputs a clickable link using the provided anchor text. When the anchor is
empty or omitted the full URL is printed.

`{event_date}` outputs the event date formatted like "June 28th, 2025". `{event_time}` shows the start and end times in 12‑hour format with am/pm, e.g. "6:00 pm - 8:00 pm".

### Member Information

```
{first_name}
{last_name}
{email}
{phone}
{membership_level}
{member_type}
```

### Ban & Re-Entry

```
{reentry_link}
```

Insert `{reentry_link}` to output `/checkout?auto=reentry`. Wrap it in Markdown—`[Re-entry Ticket]({reentry_link})`—to create a clickable link that, when the member is logged in, clears any existing items and automatically adds the Re-entry Ticket to their cart. Unauthenticated visitors simply land on the checkout page.

### Event Attendee Information

```
{attendee_first_name}
{attendee_last_name}
{attendee_email}
{attendee_phone}
{attendee2_first_name}
{attendee2_last_name}
{attendee2_email}
{attendee2_phone}
{attendee3_first_name}
{attendee3_last_name}
{attendee3_email}
{attendee3_phone}
{attendee4_first_name}
{attendee4_last_name}
{attendee4_email}
{attendee4_phone}
{assistance_message}
```

Each attendee receives a personalized email where these tokens reflect their own details. `{assistance_message}` contains any note submitted through the assistance form on the member dashboard.

### Host & Volunteer Information

```
{event_host}
{event_volunteer}
{host_notes}
```

If no hosts or volunteers are assigned to an event, `{event_host}` and
`{event_volunteer}` default to `TBD`.

### Refund Information

```
{refund_first_name}
{refund_last_name}
{refund_email}
{refund_amount}
{refund_ticket}
{refund_event_name}
{refund_event_date}
{refund_event_time}
```

### Assistance Message

These tokens insert details from a member's assistance request. Missing values default to `N/A`.

```
{assistance_message}
{assistance_first_name}
{assistance_last_name}
{assistance_email}
{assistance_phone}
```

### Formatting & Styling

The editor provides helper buttons beneath the token sections:

- **Link This Text** – prompts for a URL or token and wraps the selected text
  in `[text](url)` Markdown. Links are converted to clickable `<a>` tags when
  the email is sent.
- **Line Break** – inserts a newline. Email previews render these as HTML
  `<br>` tags so the saved text remains plain.
- **Bold** – wraps the highlighted text with `**` markers. The enclosed text
  appears in bold in the final email.
 - **Italic** – wraps the highlighted text with `*` markers. Text appears in italics in the final email.

Styling markers can be combined, so `***bold & italic***` renders as bold and italic.

Template text can include Markdown-style links directly, so
`[{event_name}]({event_link})` resolves to a link with the event name and URL.

### Formatting Helpers

The helpers `tta_format_event_date()` and `tta_format_event_time()` convert raw
database values into the human-friendly strings shown by `{event_date}` and
`{event_time}`. `tta_format_event_datetime()` is a convenience wrapper that
returns both values combined in a single string.

## Email Delivery

All outgoing messages are dispatched by the `TTA_Email_Handler` class. The handler is loaded on plugin init and is responsible for reading the templates saved on the **Email & SMS** page. After a transaction is recorded, `send_purchase_emails()` groups the purchased items by event and emails the **Successful Event Purchase** template. The purchasing member receives one email and each attendee gets a personalized copy where tokens like `{attendee_first_name}` reflect their own information. Duplicate addresses are skipped so each email address only receives one message.

## SMS Delivery

SMS notifications are sent through Twilio. The `TTA_SMS_Handler` class mirrors
the email handler and reads the SMS text from the same template set. When the
Twilio credentials are configured, members and attendees receive text messages
for purchases, refunds and waitlist openings. If credentials are missing no SMS
is dispatched and a notice appears in the WordPress admin.
