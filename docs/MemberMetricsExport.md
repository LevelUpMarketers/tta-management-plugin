# Member Metrics Export

Administrators can download a spreadsheet of member information from either the **Manage Members** or **Member History** tabs. A short form above each table lets you optionally provide a start and end date for the member join date. Leaving the fields blank exports all members.

The export relies on PhpSpreadsheet installed within the plugin directory. Run `composer install` inside `app/public/wp-content/plugins/tta-management-plugin` if the form warns that the library is missing.

Columns omit internal IDs and private data like passwords. Boolean values are shown as **Yes** or **No** and addresses are formatted for readability. After the basic member fields, several business metrics are included:

- `membership_length` – days since the member joined
- `events` – number of events they purchased tickets for
- `attended` – events they checked in to
- `no_show` – events marked as no show
- `refunds` – refund requests
- `cancellations` – membership cancellation requests
- `total_spent` – total amount spent on memberships and tickets

Press **Export Members** and an `.xlsx` file downloads immediately. The form posts to `admin-post.php` so the file is generated before any admin markup is sent and browsers treat the download as safe.
