# Event Check-In Page for Admins

The plugin provides a dedicated check-in screen at `/event-check-in-for-admins/`.
Administrators and volunteers can quickly mark attendees as checked in or as no-shows.
The page loads event details, a list of ticket holders and buttons to update each
record via AJAX. Attendance status writes back to the `tta_attendees` table and the
interface updates instantly without a full refresh.
