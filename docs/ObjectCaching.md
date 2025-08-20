# Object Caching

This document outlines the object caching layer used by the Trying To Adult Management Plugin.

## Overview

1. **Purpose**
   - Reduce repeated database queries on the Event Page and other templates.
   - Provide a simple API that can be expanded as the plugin grows.
2. **Implementation**
   - The `TTA_Cache` class wraps WordPress transients. All cache entries use the `tta_cache_` prefix.
   - Cached values are retrieved with `TTA_Cache::remember( $key, $callback, $ttl )`. When a key is missing the callback is executed and its return value cached for `$ttl` seconds.
   - Common lookups (event details, ticket lists, related events) are cached for ten minutes on the front‑end.

## Automatic Invalidations

- Whenever an event or its tickets are created or updated (via the admin pages or AJAX), `TTA_Cache::flush()` runs to clear all plugin caches.
- Member records created or edited by admins also trigger a flush so attendee lists update immediately.
- This helps prevent confusing situations where an admin edits content but the front‑end still shows old data.
- Ticket availability changes from cart activity or cleanup delete the affected event's ticket cache so numbers stay current.
- The checkout process triggers `tta_checkout_complete`, which now calls `TTA_Cache::flush()` so membership upgrades and new purchases appear immediately.
- `TTA_Cache::flush()` locates all plugin transients and removes them using `delete_transient()` so persistent object caches clear properly.
- The cache layer is bypassed entirely when viewing the plugin's admin pages so the dashboard always shows the most current data.

## Clearing the Cache Manually

- A new **TTA Settings** menu item appears in the WordPress dashboard.
- The page contains a single "Clear Cache" button that calls `TTA_Cache::flush()` when clicked.
- Use this if changes are not visible right away or for troubleshooting.

## API Summary

```php
TTA_Cache::get( $key );       // Returns a cached value or false
TTA_Cache::set( $key, $value, $ttl = 0 );
TTA_Cache::delete( $key );    // Remove a single entry
TTA_Cache::remember( $key, function() { ... }, $ttl = 0 );
TTA_Cache::flush();           // Remove all plugin caches
```

The caching layer is intentionally lightweight. Additional cache keys can be added with the same prefix and cleared automatically using the existing button or hooks.
