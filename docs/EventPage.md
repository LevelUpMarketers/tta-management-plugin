# Event Page Context and Message Center

This document summarizes helper functions and template behavior related to user context on the Event Page.

## Current User Context Helper

`tta_get_current_user_context()` returns information about the visiting user and any linked member record. The data is cached for five minutes to reduce queries.

```php
$context = tta_get_current_user_context();
```

Returned array keys:

- `is_logged_in` – `true` when the user is authenticated.
- `wp_user_id` – WordPress user ID or `0` if not logged in.
- `user_email`, `user_login`, `first_name`, `last_name` – basic profile info.
- `member` – row from the `tta_members` table or `null` if none exists.
- `membership_level` – member level (`free`, `basic`, or `premium`).

## Message Center

The Event Page template now includes a **Message Center** block under the “About This Event” section. When the visitor is not logged in the following notice appears:

```
Ticket discounts may be available! Log in here to check. Don't have an account? Create one here.
```

Links direct to the standard WordPress login and registration pages. The block is hidden entirely for logged-in users or when no messages apply.

