# Event Creation and Editing

Administrators manage events from the **TTA Events** pages. The editor collects standard details like the name, date and location along with optional images and links.

After an event is saved, the plugin automatically schedules reminder emails for hosts, volunteers, and attendees. These messages are queued to send 24 hours and 3 hours before the event start time and always use the latest host, volunteer, and attendee lists when they run.

If the event's date or start time changes, any pending reminder emails are rescheduled to match. Deleting or archiving an event clears pending reminder jobs to prevent stray messages.

## Hosts and Volunteers

Host and volunteer fields use an autocomplete list populated from members whose type is Volunteer, Admin or Super Admin. When the form is saved each name is converted to the member's WordPress user ID and stored in the `hosts` and `volunteers` columns. Older records that still contain names are handled automatically.

Administrators can also add optional **Host Notes**. These internal notes are saved in the `host_notes` column and are never displayed publicly.

## Inline Editing

The Manage Events table supports inline editing via AJAX. Fields match the create form and also save host and volunteer IDs.
