# Banned Members Admin

The **Banned Members** tab appears under **TTA Members** in the WordPress admin. It lists every member currently banned from purchasing tickets. Each row expands to show details including:

- The date the ban began.
- Number of recorded no-shows for the member.
- The ban type (Indefinite, Until Re-Entry, or a timed ban).
- A live countdown to automatic reinstatement when the ban has an end date, otherwise *Banned Indefinitely*.
- The scheduled reinstatement date if an automatic cron job is queued.
- A **Reinstate** button allowing administrators to manually clear the ban.

Members automatically appear here when their total no-shows reach three or more, at which point they are banned until purchasing a Re-entry Ticket and receive an email with reinstatement instructions.
That email includes a direct link to checkout that adds the Re-entry Ticket to their cart when they're already logged in.

When a timed ban is set, a cron job schedules automatic reinstatement. Updating a member's ban clears any existing cron job and queues a new one matching the latest ban length.
Purchasing a Re-Entry Ticket immediately lifts the ban and removes any queued reinstatement job.
