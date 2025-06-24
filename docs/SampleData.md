# Sample Database Data

The plugin bundles development data for testing. From the **TTA Settings** admin page you can load twenty fake upcoming events and matching tickets. This is useful for local development and demo environments.

Click **Load Sample Data** under `/wp-admin/admin.php?page=tta-settings` to insert the sample rows into the `tta_events` and `tta_tickets` tables. Existing data remains untouched. All caches are flushed after the import.

The raw arrays are stored in `database-testing/sample-events.php` and `database-testing/sample-tickets.php`.
