# Event Metrics Export

Administrators can export a spreadsheet of event metrics from the **Manage Events** tab. A short form above the events table lets you optionally specify a start and end date. Leaving both fields blank exports all events.

This feature relies on the PhpSpreadsheet library installed via Composer inside the plugin directory. Run `composer install` within `app/public/wp-content/plugins/tta-management-plugin` after cloning the repository. If the library has not been installed, the export form displays an admin notice explaining how to install it.

The spreadsheet excludes internal IDs like `id`, `page_id`, `ticket_id` and other implementation columns. Boolean values become **Yes** or **No** and discount codes are shown in plain English. The **Time** column appears directly before **Date** and event type values are capitalized. The featured image column links directly to the full image. Additional metrics appear at the end of each row:


- `expected_attendees` – number of purchased tickets (after refunds)
- `checked_in` – count of attendees marked as checked in
- `no_show` – count of attendees marked as no show
- `refund_requests` – number of pending refund requests
- `refunded_amount` – total amount refunded
- `revenue` – total revenue from ticket sales
- `revenue_minus_refunds` – total revenue after subtracting refunds
- `sold_out` – **Yes** if the event sold out, **No** otherwise

Press **Export Metrics** and an `.xlsx` file downloads immediately. The form
submits to WordPress's `admin-post.php` endpoint so the file is generated
before any admin page markup is sent, preventing the "Cannot modify header"
warnings that appear if the export is triggered after output has started.
The export response includes caching and MIME headers so modern browsers
recognize the download as safe.
Columns automatically resize to fit the header text for readability.
