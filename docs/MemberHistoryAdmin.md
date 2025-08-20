# Member History Admin Tab

The **Member History** tab is available under **Members** in the WordPress admin. It lists all members just like the Manage Members tab. Clicking a member row loads a detailed history view showing:

Members can now be sorted by membership length, events attended, total amount spent or alphabetically by first or last name. The dropdown begins with a disabled **Sort By…** option so the default order is obvious. A **Clear Sorting** button resets the view back to this default.

An **Export Members** form lets administrators download a spreadsheet with the same metrics shown in the table. Optionally specify a start and end join date to limit the export.
- Total amount spent across all transactions, including membership purchases and recurring charges
- Count of events they have purchased
- Count of events checked in
- Count of no‑shows
- Number of refunds or cancellation requests
- A complete payment history table including event purchases and membership charges
- Membership cancellation entries noting who performed the action and the last four digits of the card
- Any private notes stored with the member record appear in the expanded detail view rather than as a table column

The summary metrics, member email and notes appear as columns in a single-row `widefat` table above the payment history for quick reference. The “Member Summary” heading also includes a tooltip describing the data shown.

Data is pulled from `tta_memberhistory`, `tta_transactions`, `tta_attendees` (and
`tta_attendees_archive`), `tta_events`, `tta_events_archive`, and
`tta_tickets_archive` to ensure attendance metrics remain accurate even after
events are removed.

Below the summary is a **Manage Subscription** section. The controls are arranged side-by-side for quick access and each heading includes a tooltip describing its purpose. Tooltip icons come before each heading for improved readability. Administrators can:

 - Update the stored payment method and billing address for the member's recurring Authorize.Net subscription. Billing fields now include **Address Line 2** just like the public checkout form.
 - Cancel or reactivate the subscription without leaving WordPress. When a plan is
    cancelled or has a payment problem a single form is shown. If the status is
   *cancelled* the heading reads **Create a New Subscription for This Member**.
   When the status is *paymentproblem* the heading changes to
   **Attempt Payment Again or Cancel Current Subscription and Create a New One** and two buttons appear:
   **Create New Subscription** uses the fields provided and
   **Retry billing using payment info already on file in Authorize.Net.**
   retries the stored subscription. The Change Level and Cancel forms are hidden
   when the subscription is cancelled or has a payment problem.
- Change the membership level and specify a custom monthly price. The update attempts to modify the existing subscription via Authorize.Net; the subscription name is adjusted to match the new level, and the dropdown defaults to the member's current tier. On failure a clear error message is returned.
- Each form displays its own response message directly below the submit button for clearer feedback.
- Assign a brand new membership to a user who has never subscribed before. The form matches the front-end checkout and charges the first month immediately. When reactivating a cancelled or payment-problem plan the form pre-fills the last monthly amount and billing address from Authorize.Net so usually only a new card number is required.
- Changing the **Level** dropdown automatically fills the price field with the default ($5 for Basic, $10 for Premium). When the form loads for an existing subscription, the field is pre-filled with the member's current monthly charge pulled from Authorize.Net and this amount is preserved; defaults only apply when no price exists.
The section now shows the member's current or most recent membership details. When a subscription is active it lists the level, monthly price, status and the last four digits of the stored card. In this state the reactivation form is hidden since no action is required. If the member previously cancelled a plan those details, including the prior level and price, are displayed so administrators know exactly what was cancelled. When a payment problem downgrades the user to Free, the block likewise shows the former level and price. A short note appears if they have never subscribed. The payment and billing fields still only need to be filled out when updating the stored card, reactivating a cancelled membership or changing levels. Cancelling does not require them.
When expanding a member row the actions cell briefly shows a small spinner while data is fetched from Authorize.Net to populate these details.
When no prior membership exists the update, reactivate and cancel forms are hidden and only the **Assign Membership** form is displayed.
The payment form uses the same field layout as the public checkout page so administrators see familiar labels and the expiration field auto‑formats as they type. The masking script listens for input events so even forms loaded via AJAX gain the same behavior.

Each member profile form now includes a **Ban Status** control. Choose "Banned Indefinitely" or a 1‑4 week duration to prevent the member from buying tickets or memberships. A banner on their dashboard shows the ban end date.
