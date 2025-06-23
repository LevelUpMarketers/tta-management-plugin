# Member History Admin Tab

The **Member History** tab is available under **Members** in the WordPress admin. It lists all members just like the Manage Members tab. Clicking a member row loads a detailed history view showing:

- Total amount spent across all transactions
- Count of events they have purchased
- Count of events checked in
- Count of no‑shows
- Number of refund or cancellation requests
- A table of all past event transactions
- Any private notes stored with the member record appear in the expanded detail view rather than as a table column

Data is pulled from `tta_memberhistory`, `tta_transactions`, `tta_attendees` (and
`tta_attendees_archive`), `tta_events`, `tta_events_archive`, and
`tta_tickets_archive` to ensure attendance metrics remain accurate even after
events are removed.
