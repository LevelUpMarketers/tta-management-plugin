# Sample Database Data

The plugin bundles development data for testing. From the **TTA Settings** admin page you can load twenty fake upcoming events scheduled over the next two months along with matching tickets, members and example transactions. Each sample event is created the same way as a normal event including its WordPress page so calendar links work and attendees are generated for each event. When loading the data the loader attempts to assign a random image from your media library as the featured image for each event.

Click **Load Sample Data** under `/wp-admin/admin.php?page=tta-settings` to insert the sample rows into the relevant tables (`tta_events`, `tta_tickets`, `tta_members`, `tta_transactions`, `tta_attendees`) and automatically generate pages. Use **Delete Sample Data** on the same screen to remove everything created by the loader. All caches are flushed after each action.

The raw arrays are stored in `database-testing/sample-events.php`, `database-testing/sample-tickets.php` and `database-testing/sample-members.php`.
