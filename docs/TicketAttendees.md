## Ticket Attendees

The ticket editor now shows three attendee tables for each ticket. **Verified Attendees** lists everyone who has successfully purchased the ticket. **Attendees With Pending Refund Requests** appears below it and shows members who cancelled and are waiting for another purchase before their refund is issued. The new **Refunded Attendees** table lists those whose refunds have already been processed. Admins can process or review these entries using the action buttons provided. Any notes submitted with the original request are stored and displayed here so admins know why the attendee cancelled. Notes also persist when refunds are issued automatically once another member purchases the ticket. The **Verified Attendees** table lists **Name**, **Email**, **Phone**, **Paid**, **Purchaser**, **Refund $** and **Actions**. The pending table adds a **Pending Reason** column so staff know why the refund hasn't yet been issued. If an admin triggers a refund without supplying a note, a default message records that the request was created manually and is waiting for settlement. Transactions are grouped by their numeric ID with the gateway transaction ID and purchase date displayed in the group heading.

If a pending entry's **Note** shows that an admin manually issued a "Refund & Cancel Attendance" request and the transaction is still settling, all action buttons for that row are disabled to prevent duplicate requests.

Once an event sells out, refund requests place their tickets back on sale and the pending entry displays **Up for sale - waiting to be purchased** as the reason. If a member requests a refund before the ticket sells out, the pending entry shows **Ticket has yet to sell out** instead. When a ticket has ever sold out and people are already on the waitlist, new refund requests display **Ticket has yet to sell out. Those on the waitlist have been notified that a ticket is available** and notify everyone in the waitlist pool. When another member buys that ticket and the original transaction has not yet settled, the pending refund automatically switches to a **Pending Reason** of "Waiting for transaction to settle". That ticket is immediately removed from the available pool so the event no longer shows extra stock.

The **Paid** column shows the amount charged for that attendee's ticket. A new **Purchaser** column indicates which attendee completed checkout. The **Refund $** field lets admins specify a partial refund before clicking either **Refund & Cancel Attendance** or **Refund & Keep Attendance**. The first option both issues the refund and removes the attendee from the event while increasing the available ticket count. The second option refunds the amount but leaves the attendee registered. For cases where no refund is needed, a **Cancel Attendance (No Refund)** button simply frees the ticket and removes the attendee. Any refund or cancellation also reduces the member's purchase tally so they can buy additional tickets up to the limit. If the transaction has not yet settled, the refund is scheduled for the next Authorize.Net settlement window (around 3:15&nbsp;AM) and reattempted until the settlement completes rather than voiding the entire transaction. All action buttons become disabled with a tooltip to indicate a pending request. Leaving the **Refund $** field blank refunds the full amount paid for that attendee only, not the entire transaction.

## Waitlist Entries

Each ticket also shows a **Waitlist Entries** table when people have joined the
waitlist. Every heading now includes a tooltip icon like the Attendees table. The
columns show **Name**, **Email**, **Phone**, **Membership Level**, and **Date & Time
Joined**, followed by an **Actions** column with a Remove button. Entries are
ordered from oldest to newest so admins can quickly see who has been waiting the
longest. Removing an entry deletes it immediately via AJAX so another person can
take the open spot.

When a refund or cancellation increases a sold‑out ticket's remaining count from
zero to one, the waitlist email sequence is triggered automatically. Premium
members are notified immediately, Basic members after ten minutes and Free
members after fifteen. If a higher tier has no entries, lower tiers move up so
available tickets are offered fairly.
