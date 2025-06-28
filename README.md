# Trying To Adult Management Plugin

This plugin integrates with Authorize.Net for payment processing. For development you may place the API credentials in an `authnet-config.php` file at the plugin root or set them as environment variables before WordPress loads:

```
define('TTA_AUTHNET_LOGIN_ID', 'your_login_id');
define('TTA_AUTHNET_TRANSACTION_KEY', 'your_transaction_key');
# Optional: set to 'false' for production
define('TTA_AUTHNET_SANDBOX', true);
```
If these constants are not defined, checkout will fail and an admin notice will be displayed. When deploying to production, move the credentials out of the plugin directory.

## Documentation

- [Cart and Checkout Flow](docs/CartFlow.md)
- [Object Caching](docs/ObjectCaching.md)
  - Plugin caches can now be cleared reliably even on hosts with persistent object caching.
- [Input Sanitization Helpers](docs/InputSanitization.md)
- [Authorize.Net Error Codes](docs/AuthorizeNetErrors.md)
- [Address Helper Functions](docs/AddressHelpers.md)
- [Event Page Context](docs/EventPage.md)
- [Event Hosts & Volunteers](docs/EventPage.md#event-hosts-and-volunteers)
- [Event Type Options](docs/EventTypes.md)
- [Testing Information](docs/TestingInformation.md) (includes sandbox credit card numbers)
- [Member Privacy Options](docs/MemberPrivacy.md)
- [Membership Benefits](docs/MembershipBenefits.md)
- [Member Dashboard](docs/MemberDashboard.md)
- [Member History Admin](docs/MemberHistoryAdmin.md)
- [Event Check-In Page](docs/EventCheckInAdmin.md)
- [Venue Administration](docs/VenuesAdmin.md)
- [Ticket Attendees](docs/TicketAttendees.md)
- [Events List Page](docs/EventsListPage.md)
- [Profile Image Popup](docs/ProfilePopup.md)
- [Event Sharing](docs/EventShare.md)
- [Tooltip Text Management](docs/TooltipText.md)
- [Events List Page CSS](assets/css/frontend/events-list.css)
- [Become a Member Page](docs/BecomeMemberPage.md)
- [Email and SMS Templates](docs/EmailSMS.md) – manage message text with live previews and token insertion
- [Recurring Billing](docs/RecurringBilling.md)
- [Debugging Tools](docs/Debugging.md)
- [Database Upgrades](docs/DevelopmentSQL.md#automatic-upgrades)
- [Development SQL Assets](docs/DevelopmentSQL.md)
- [Sample Database Data](docs/SampleData.md)
- [Operator Testing Guide](docs/OperatorTestingGuide.md)
- [Project TODOs](TODO.md)

Old events are automatically moved to an `tta_events_archive` table by a daily cron. The process is transparent to admins and members.
Whenever the structure of `tta_events` changes, mirror those updates to `tta_events_archive` as well.

## Running Tests

After installing PHP and Composer, execute `composer install` followed by
`vendor/bin/phpunit` to run the plugin's unit tests.
