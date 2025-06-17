# Trying To Adult Management Plugin

This plugin integrates with Authorize.Net for payment processing. For security, the API credentials are **not** stored in the database. Instead, define the following constants in your `wp-config.php` file or set them as environment variables before WordPress loads:

```
define('TTA_AUTHNET_LOGIN_ID', 'your_login_id');
define('TTA_AUTHNET_TRANSACTION_KEY', 'your_transaction_key');
# Optional: set to 'false' for production
define('TTA_AUTHNET_SANDBOX', true);
```

Without these values, checkout will fail and an admin notice will be displayed.

## Documentation

- [Cart and Checkout Flow](docs/CartFlow.md)
- [Object Caching](docs/ObjectCaching.md)
- [Project TODOs](TODO.md)
