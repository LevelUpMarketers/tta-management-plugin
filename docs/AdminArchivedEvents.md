# Archived Events Admin Tab

The **Archived Events** tab in the Events admin screen lists records from the
`tta_events_archive` table. Rows can be expanded just like on the Manage Events
tab but the form fields are disabled. This allows administrators to review all
details of past events without modifying them. Associated ticket rows live in
`tta_tickets_archive` and attendee data is stored in `tta_attendees_archive` so
historical metrics remain available. Because editing is disabled, the event
description is shown as plain HTML and TinyMCE is not loaded when viewing
archived events.
