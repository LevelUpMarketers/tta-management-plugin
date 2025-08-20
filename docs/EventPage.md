# Event Page Context and Message Center

This document summarizes helper functions and template behavior related to user context on the Event Page.

## Layout Overview

The template uses a three‑column layout—sidebar, main content, and an ad
column—all wrapped in a single `.tta-event-columns` container. The narrow
**right** column opens with a **Meet Our Local Partners** heading and a short
message thanking supporting businesses before showing a random ad image.
When provided in the admin, the business name, phone, and address appear below
the image with icons. The name links to the ad URL, the phone triggers a call,
and the address opens a Google Maps search.
On screens wider than 768px this ad column sticks in view while scrolling but stays within its parent container. The StickySidebar library keeps the panel anchored until the bottom of its parent is reached. An extra 148px top offset keeps the ad clear of the site menu.
The event details sidebar remains on the left and the main content occupies the
center.

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

The Event Page template includes a **Message Center** block under the “About This Event” section. When a visitor is not logged in a small callout invites them to authenticate. The block begins with a **Log in or Register Here** heading followed by a login form that is visible by default. Clicking **Log in here** toggles the embedded form with the same accordion animation used elsewhere on the page.

Guests can now create a free account directly within this block. Selecting **Create Account** fades out the login form and reveals a registration form requesting first name, last name, email (entered twice for verification), and password (also entered twice). The original **Create Account** button is disabled while this form is visible. A **Cancel Account Creation** link beside the submit button returns visitors to the login form and re‑enables the button. A progress spinner and response area provide feedback. The form checks that the email and password fields match and ensures no existing WordPress user or member already uses the email address. On success, a confirmation message displays a five‑second countdown before the page reloads and automatically logs the new member in.

The response element reserves space so feedback messages do not shift nearby controls. Passwords must be at least eight characters and include uppercase and lowercase letters plus a number; violations display inline guidance before the form submits.

## Event Type and Ticket Context

The **Event Details** sidebar now lists the event type (Open Event, Basic Membership Required, or Premium Membership Required). A short message under the “Get Your Tickets Now” heading communicates the membership requirement and offers login or upgrade links depending on the visitor’s status. When the **Get Tickets** or **Join The Waitlist** buttons are disabled due to membership requirements, an adjacent **Upgrade** button appears so visitors can quickly navigate to the membership signup page. This link now shows beside each ticket’s waitlist control **and** next to the main “Get Tickets” button. The button label changes to “Upgrade to Basic” or “Upgrade to Premium” based on the event.

## Waitlist Popup

When all tickets are sold out but a waitlist is available, the **Join The Waitlist** button appears in the hero area and above the ticket section. The control only shows once the final regular ticket has been purchased and no active cart reservations remain, so members aren’t prompted to join the waitlist while a ticket is merely being held in someone’s cart. Clicking it opens a modal form with first name, last name, email and phone fields plus two consent checkboxes that are pre-selected. Logged‑in members see their info pre-filled. A spinner shows while the form submits and the confirmation message is delayed so the entire process takes at least three seconds. The × close button is black by default and turns white on hover. The modal can be closed by that button or by clicking outside the popup.

Logged-in visitors who are already on a ticket's waitlist see the Join button for that ticket disabled with a tooltip and a new **Leave the Waitlist** button beside it. Clicking that second button removes their waitlist entry via AJAX and reloads the page. Waitlists are tracked separately for each ticket so attendees can join or leave specific ticket types.
If the Join button is disabled because the visitor doesn't meet the membership requirement, an **Upgrade** link appears beside it so they can easily upgrade before joining.

Visitors who do not meet the event's membership requirement—including logged-out guests—still see the Join The Waitlist buttons, but they are disabled with a tooltip explaining why they cannot join.

The standalone **Get Tickets** button below the ticket table only shows when at least one ticket is available. When all tickets are sold out, this call-to-action is omitted and waitlist controls appear on each ticket row instead.

## Social Sharing

Below the event meta information the hero section shows small Facebook and Instagram icons. Clicking either icon opens a share window preloaded with the event title and URL so visitors can quickly post about the event on social media. The behaviour is handled by `event-share.js` which is only loaded on individual Event Pages.

## Your Events Sidebar Section

Between the Venue Links and Refund Policy sections a new **Your Events** block appears. When not logged in it shows a single link prompting visitors to log in. Clicking the link scrolls to the Message Center and automatically expands the login form.
Login expansion happens directly by adding the `expanded` class so the form is open immediately.
The toggle button text remains **Log in here** even after the form expands so the call to action stays consistent.
Scrolling accounts for any fixed header on the site. Adjust the selector in `event-page.js` if your theme uses a different header structure.

Logged-in members instead see links to profile info, upcoming events, past events, and membership/billing details. Each link loads the Member Dashboard with the matching tab active. A log out link is also provided which returns the user to the same event page after signing out.

## Image Gallery

Events can include additional photos displayed in an expandable gallery. The gallery uses a masonry-style layout so images of varying dimensions fit nicely together with minimal gaps.
Each thumbnail uses the same popup script as attendee photos—clicking an image opens a larger version in an overlay.

## Archived Events

When a visitor lands on an event page that no longer exists in the primary
`tta_events` table, the template automatically checks the
`tta_events_archive` table. Archived events display all of the standard details
and retain the attendee gallery. Ticket types come from `tta_tickets_archive`
and attendee profiles load from `tta_attendees_archive`. The **Get Your Tickets
Now** section remains in place but all controls are disabled and a tooltip
explains that ticket sales are closed. Disabled buttons are dimmed with a light
overlay so the tooltip displays at full opacity. The login prompt is suppressed. A small
notice appears above the “About This Event” section letting the visitor know the
event has passed and linking to `/events/` to browse upcoming events.

## Attendee Gallery

Below the image gallery, a second accordion displays profile pictures of confirmed attendees. The list is built from the `tta_attendees` table joined to `tta_members` via email so member profile images can be shown. Results are cached via `TTA_Cache` for ten minutes (one minute when empty). Each attendee's full name appears beneath their photo along with their membership level (Free, Basic, or Premium). If a profile image is missing or attendance is hidden, a placeholder image is shown with a label like "Attendee #1." Hosts and volunteers are highlighted with a small badge over their photo. Hosts are listed first, followed by volunteers, and then all other attendees in alphabetical order.
Membership levels are retrieved by checking each attendee's email against the `tta_members` table, ensuring changes to a member's account are reflected immediately.

Members may opt to hide their attendance in their profile's *Privacy Options*. Hidden attendees always show the placeholder image and use the numbered "Attendee" label.

Attendee thumbnails use the same popup script as the Events List page. Clicking a profile photo opens a larger version in an overlay, which is disabled on very small screens.

## Event Hosts and Volunteers

Admins can assign one or more hosts and volunteers when creating or editing an event. The autocomplete fields pull from members whose type is Volunteer, Admin, or Super Admin. Selected user IDs are stored in the `hosts` and `volunteers` columns of `tta_events` (legacy name entries are still recognized). On the front end, hosts appear first in the attendee gallery followed by volunteers. Each badge is labelled “Host” or “Volunteer.”

## SEO and Schema Markup

Each event page outputs JSON‑LD Event schema. The markup includes the event name, description, dates, location, main image, and pricing when available. This helps search engines display the event in rich results.

## Related Events

A grid of other upcoming events appears below the main content. Thumbnails are rendered with background images so they remain the same size even when the source photos vary. Each card links to its Event Page and shows the date beneath the title.


