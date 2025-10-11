# Event Check-In Page for Admins

The plugin provides a dedicated check-in screen at `/event-check-in-for-admins/`.
Administrators and volunteers can quickly mark attendees as checked in or as
no-shows. The page loads event details, a list of ticket holders and buttons to
update each record via AJAX. Attendance status writes back to the
`tta_attendees` table and the interface updates instantly without a full refresh.

Visitors who aren't logged in are shown a styled login form with a **Forgot your
password?** link so volunteers can quickly authenticate from any device.

Events remain listed for 24 hours after their scheduled end time, giving hosts
extra time to check people in or mark no-shows before the event drops off the
page.

Every expanded event now includes an **Email All Attendees** panel directly
beneath the bulk no-show controls. Hosts can type a quick update, review the
template-generated greeting/closing in the admin preview, and click **Send
Email** to message every attendee, host, and volunteer tied to the event. The
plugin sanitizes the typed content to remove HTML and emoji, merges it with the
opening and closing text saved on the Communication Templates tab, and sends the
personalized message via AJAX without leaving the check-in screen.

Each broadcast is logged under **Email History** on the Communications page so
administrators can audit which hosts sent updates and who received them. The
textarea requires a substantive message—at least 20 characters once whitespace
is collapsed—before the **Send Email** button becomes active, helping prevent
accidental blank or partial messages.

On screens 1199px wide or narrower, the events table converts into stacked
cards for easier mobile use. Each event row becomes a full-width block with
labels for its fields, and tapping the block reveals the attendee list just like
on desktop.

The desktop view drops the former **Status** column to simplify the layout and
combines the image and name into a single **Event** column. On mobile, the
toggle cell displays a bold “See All Attendees” prompt so it’s clear where to
tap. When expanded on screens 1199px wide or narrower, the attendee list now
appears inside the toggle cell itself and shows labels such as **Name**,
**Email**, and **Status** before each value. These labels are hidden on wider
screens because the table headers remain visible.

Attendees are ordered with all pending members first, followed by checked-in
and then no-shows. Pending attendees who request assistance are listed before
other pending members, and each group remains alphabetized by first name.
Expanding a row on desktop slides the attendee list into view for a smoother
experience.

### Table details

- Attendees who cancelled or requested a refund no longer appear in the list so
  hosts don't accidentally mark them as no-shows.
- Two columns display each attendee's event history and any **Needs Assistance** note. The history column shows `X Events Attended, Y No-Shows`; when no assistance note exists a simple `-` is shown.
- Members submit these assistance notes from the Upcoming Events tab on their dashboard. The note is stored only for the member's own attendee record and emailed to all event hosts automatically.
- A **Mark all Pending as No-Shows** button lets hosts convert every remaining
  pending attendee at once after a confirmation prompt. This triggers the usual
  banning and notification process for members who reach five no-shows.
- Any assistance notes also appear in a list above the attendee table along
  with the member's name, phone, and email. The list text is bold red and rows
  with assistance notes are highlighted in the table to draw attention.
- A new **# of Expected Attendees** column shows how many approved attendees are expected for each event.
- If the event has host notes, they appear beneath the address so volunteers can see any special instructions.
- The **Date & Time** column uses the same human-friendly format as the event header.
- Clicking the **Check In** or **No-Show** buttons now updates the status label with proper capitalization and adjusts the
  attendee's event history totals. If another host updates the record first, the page reloads and automatically opens that event so everyone sees the latest information.
- Adding `?event=<id>` to the URL deep-links directly to an event. On load, the page scrolls to that event and expands it automatically.
- Selecting **No-Show** prompts a confirmation explaining that a fifth no-show automatically bans the member until they purchase a Re-entry Ticket and sends them an email with reinstatement instructions.
- Once an attendee is marked as a no-show or checked in, both action buttons are disabled—and remain disabled on reload—to prevent duplicate submissions.
- The ban and notification email trigger the moment a member's total no-shows reach five (counting the event just marked) and won't resend on additional no-shows.
- Event headers display the date and time in a friendly format like `Saturday July 19, 2025 - 6:00 PM to 8:00 PM`. The venue name links to its website and the address links directly to Google Maps for quick directions. Event details are loaded via `tta_get_event_for_email()` so the venue information always appears.
