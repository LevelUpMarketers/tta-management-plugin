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
- [Input Sanitization Helpers](docs/InputSanitization.md)
- [Event Page Context](docs/EventPage.md)
- [Event Hosts & Volunteers](docs/EventPage.md#event-hosts-and-volunteers)
- [Event Type Options](docs/EventTypes.md)
- [Testing Information](docs/TestingInformation.md) (includes sandbox credit card numbers)
- [Member Privacy Options](docs/MemberPrivacy.md)
- [Development SQL Assets](docs/DevelopmentSQL.md)
- [Project TODOs](TODO.md)

## Running Tests

After installing PHP and Composer, execute `composer install` followed by
`vendor/bin/phpunit` to run the plugin's unit tests.
