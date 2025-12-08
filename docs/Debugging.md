# Debugging Tools

The **Logging** tab under TTA Settings displays a debugging console. All PHP warnings, notices, and errors encountered while the plugin runs are captured by the `TTA_Debug_Logger` class and stored in a WordPress option.

The log output is displayed in a scrollable `<pre>` block. A **Clear Log** button removes all entries. Messages are preserved across requests until cleared and include a timestamp along with the error type and location.

This feature is intended for development only. Before deploying to production, consider disabling or removing the logger to avoid collecting sensitive information.

To protect customer privacy, verbose payment debugging is disabled by default. Payment and transaction events no longer write their details to PHP error logs or the Logging tab, and any context provided to payment log helpers is redacted before storage.

The General Settings tab also provides an **Authorize.net testing** button. Clicking it triggers a series of automated purchases through the plugin's AJAX endpoints using your sandbox credentials. Each scenario (single ticket, multiple tickets, membership only, and membership plus tickets) runs with short delays to avoid API throttling. Progress and results for every step are written to the debug log while testing is active, but payment payloads and responses remain redacted.

Clear the log regularly in production environments to avoid retaining outdated debugging information.
