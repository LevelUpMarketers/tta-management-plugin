# Tickets Admin

The **Tickets** screen in WordPress Admin lists each event that has at least one ticket type. For every event the table displays the thumbnail, name, date and a **Tickets Left** column indicating remaining inventory across all ticket types.

The count is computed using the `tta_get_remaining_ticket_count()` helper so sold tickets are subtracted from each type's `ticketlimit`. For example:

- An event with one ticket type, limit 10, and 3 sold will show **7 Tickets Left**.
- An event with two ticket types each limited to 15, where 4 and 5 have sold, will show **18 Tickets Left**.

Use the arrow at the end of each row to expand and edit individual ticket types. The **Manage Tickets** panel includes **Add New Ticket** and **Export All Attendees** actions so staff can append ticket types or download attendee data for the selected event. The export downloads a CSV with **First Name**, **Last Name**, **Email**, and **Status** for every attendee across all ticket types, including Verified, Waitlist, Refunded, and Pending refund Request entries.
