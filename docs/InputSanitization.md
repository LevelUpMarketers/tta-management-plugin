# Input Sanitization Helpers

Several admin and front‑end forms accept freeform text where users may enter apostrophes. WordPress automatically adds slashes to `$_POST` values which can lead to stray backslashes being stored in the database. The plugin now exposes helper functions that unslash values before sanitizing them.

## Helper API

```php
$clean = tta_sanitize_text_field( $_POST['name'] );
$bio   = tta_sanitize_textarea_field( $_POST['biography'] );
$email = tta_sanitize_email( $_POST['email'] );
$url   = tta_esc_url_raw( $_POST['facebook'] );
```

All helpers use `wp_unslash()` recursively so both strings and arrays are handled correctly. Existing logic that previously called `sanitize_text_field()` or related functions has been updated to use these wrappers.

Using these functions prevents escaped apostrophes from appearing on the front‑end and ensures data is safely stored without stray slashes.

The Email & SMS template editor leverages these helpers when saving changes. Administrators can include apostrophes or quotes in message text without backslashes appearing in the WordPress admin or outgoing notifications.
