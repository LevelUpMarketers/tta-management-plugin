# Event Page Context and Message Center

This document summarizes helper functions and template behavior related to user context on the Event Page.

## Current User Context Helper

`tta_get_current_user_context()` returns information about the visiting user and any linked member record. The data is cached for five minutes to reduce queries.

```php
$context = tta_get_current_user_context();
```

Returned array keys:

- `is_logged_in` – `true` when the user is authenticated.
- `wp_user_id` – WordPress user ID or `0` if not logged in.
- `user_email`, `user_login`, `first_name`, `last_name` – basic profile info.
- `member` – row from the `tta_members` table or `null` if none exists.
- `membership_level` – member level (`free`, `basic`, or `premium`).

## Message Center

The Event Page template includes a **Message Center** block under the “About This Event” section. When a visitor is not logged in a small callout invites them to authenticate. Clicking **Log in here** expands an embedded login form with the same accordion animation used elsewhere on the page. The form submits via `wp_login_form()` and redirects back to the event page on success. A link to the standard registration page is also provided.

## Event Type and Ticket Context

The **Event Details** sidebar now lists the event type (Open Event, Basic Membership Required, or Premium Membership Required). A short message under the “Get Your Tickets Now” heading communicates the membership requirement and offers login or upgrade links depending on the visitor’s status.

## Your Events Sidebar Section

Between the Venue Links and Refund Policy sections a new **Your Events** block appears. When not logged in it shows a single link prompting visitors to log in. Clicking the link scrolls to the Message Center and automatically expands the login form.
Login expansion happens directly by adding the `expanded` class so the form is open immediately.
The toggle button text remains **Log in here** even after the form expands so the call to action stays consistent.
Scrolling accounts for any fixed header on the site. Adjust the selector in `event-page.js` if your theme uses a different header structure.

Logged-in members instead see links to profile info, upcoming events, past events, and membership/billing details. Each link loads the Member Dashboard with the matching tab active. A log out link is also provided which returns the user to the same event page after signing out.

## Attendee Gallery

Below the image gallery, a second accordion displays profile pictures of confirmed attendees. The list is built from the `tta_memberhistory` table using records where `action_type` is `purchase` for the current event. Each attendee's profile image ID is pulled from the `tta_members` table. Results are cached for ten minutes via `TTA_Cache`.


