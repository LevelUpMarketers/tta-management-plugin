# Email and SMS Templates

The plugin sends automated notifications to members. Administrators can edit the text for each message from **Email & SMS** in the WordPress admin menu. Templates are listed in a table similar to the Manage Events page. Click a row to expand an inline form containing the fields for that communication. Each form has its own **Save Changes** button and progress spinner.

## Available Templates

| Key | Description |
|-----|-------------|
| `purchase` | Sent after a successful event purchase. Includes event details automatically. |
| `reminder_24hr` | Sent 24 hours before an event starts. |
| `reminder_2hr` | Sent two hours before an event starts. |

Each template stores:

- **Type** – whether the message is sent to members (External) or used internally
- **Category** – grouping such as Event Reminder or Event Confirmation
- **Email Subject** – subject line of the email
- **Email Body** – text shown above the automatically generated event details
- **SMS Text** – short message sent via SMS

Default values are provided on initial install:

- **Purchase Email Subject**: "Thanks for Registering!"
- **Purchase Email Body**: "You're in! Thank for registering for our upcoming Trying To Adult event. The details of the event are below. Please keep this email, as you'll need to present this to the Event Host or Volunteer when arriving at your event."
- **Purchase SMS**: "Thanks for registering! View your upcoming events at "
- **24-Hour Reminder Email Body**: "Heads-up! Your event is just 1 day away! Below are the details."
- **2-Hour Reminder Email Body**: "Your event is only 2 hours away! Below are the details."

The member dashboard link appended to SMS messages uses a short URL when possible.

## Previews and Tokens

Editing a template shows live **Email Preview** and **SMS Preview** boxes. Values are updated whenever an input field loses focus. For reminder and purchase templates, the preview substitutes details from the next upcoming event in the database.

SMS previews show a character count. If the text exceeds 160 characters the count turns red to indicate the message may be split by carriers.

Buttons labelled with tokens (e.g. `{event_name}`) insert placeholders into the last focused field. The sending logic will replace these tokens with real data. Available tokens include:

```
{event_name}
{event_address}
{event_link}
{dashboard_link}
{event_date}
{event_time}
{event_type}
{venue_name}
{venue_url}
{base_cost}
{member_cost}
{premium_cost}
```

Use the **Line Break** button to insert a newline. Email previews render these breaks as HTML `<br>` tags so the saved text remains plain.
