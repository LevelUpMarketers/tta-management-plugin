# Member History Admin Tab

The **Member History** tab is available under **Members** in the WordPress admin. It lists all members just like the Manage Members tab. Clicking a member row loads a detailed history view showing:

- Total amount spent across all transactions
- Count of events they have purchased
- Count of events checked in
- Count of no‑shows
- Number of refund or cancellation requests
- A complete payment history table including event purchases and membership charges
- Any private notes stored with the member record appear in the expanded detail view rather than as a table column

The summary metrics, member email and notes appear as columns in a single-row `widefat` table above the payment history for quick reference. The “Member Summary” heading also includes a tooltip describing the data shown.

Data is pulled from `tta_memberhistory`, `tta_transactions`, `tta_attendees` (and
`tta_attendees_archive`), `tta_events`, `tta_events_archive`, and
`tta_tickets_archive` to ensure attendance metrics remain accurate even after
events are removed.

Below the summary is a **Manage Subscription** section. The controls are arranged side-by-side for quick access and each heading includes a tooltip describing its purpose. Tooltip icons come before each heading for improved readability. Administrators can:

- Update the stored payment method and billing address for the member's recurring Authorize.Net subscription.
- Cancel or reactivate the subscription without leaving WordPress.
- Change the membership level and specify a custom monthly price. The update attempts to modify the existing subscription via Authorize.Net; on failure a clear error message is returned.
- Each form displays its own response message directly below the submit button for clearer feedback.
- The payment and billing fields only need to be filled out when updating the stored card information, reactivating a cancelled membership or changing levels. Cancelling does not require them.
The payment form uses the same field layout as the public checkout page so administrators see familiar labels and the expiration field auto‑formats as they type.

Each member profile form now includes a **Ban Status** control. Choose "Banned Indefinitely" or a 1‑4 week duration to prevent the member from buying tickets or memberships. A banner on their dashboard shows the ban end date.
