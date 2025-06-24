# Sample Database Data

The plugin bundles development data for testing. From the **TTA Settings** admin page you can load twenty fake upcoming events scheduled over the next two months along with matching tickets. Each sample event is created the same way as a normal event including its WordPress page so calendar links work.

Click **Load Sample Data** under `/wp-admin/admin.php?page=tta-settings` to insert the sample rows into the `tta_events` and `tta_tickets` tables and automatically generate pages. Use **Delete Sample Data** on the same screen to remove all sample events and their pages. All caches are flushed after each action.

The raw arrays are stored in `database-testing/sample-events.php` and `database-testing/sample-tickets.php`.
