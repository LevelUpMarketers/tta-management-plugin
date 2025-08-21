# Debugging Tools

The **Logging** tab under TTA Settings displays a debugging console. All PHP warnings, notices, and errors encountered while the plugin runs are captured by the `TTA_Debug_Logger` class and stored in a WordPress option.

The log output is displayed in a scrollable `<pre>` block. A **Clear Log** button removes all entries. Messages are preserved across requests until cleared and include a timestamp along with the error type and location.

This feature is intended for development only. Before deploying to production, consider disabling or removing the logger to avoid collecting sensitive information.

The General Settings tab also provides an **Authorize.net testing** button. Clicking it triggers a series of automated purchases through the plugin's AJAX endpoints using your sandbox credentials. Each scenario (single ticket, multiple tickets, membership only, and membership plus tickets) runs with short delays to avoid API throttling. Progress and results for every step are written to the debug log.

All Authorize.Net API responses are logged to the PHP `error_log`, making it easy to inspect the full payload returned by the gateway after any checkout attempt. The debug log also records key `TransactionResponse` fields—response codes, transaction IDs, auth codes, AVS/CVV results, masked account numbers, and any error messages—so declines clearly show their specific reason in both sandbox and live modes.

## Authorize.Net request/response logging

Every transaction attempt records the exact JSON payload sent to Authorize.Net along with the response payload. Sensitive values such as API Login IDs, Transaction Keys, and card numbers are partially masked (only the last four digits of a card are shown) and CVV codes are never logged. Billing details include the full address and default country (`USA`), and amounts are formatted as two-decimal strings. Example entry:

```
charge request (https://api.authorize.net): {"createTransactionRequest":{"merchantAuthentication":{"name":"LOGI****3456","transactionKey":"TRAN****5678"},"transactionRequest":{"transactionType":"authCaptureTransaction","amount":"10.00","payment":{"creditCard":{"cardNumber":"************1111","expirationDate":"2025-12","cardCode":"[omitted]"}},"billTo":{"firstName":"John","lastName":"Doe","address":"123 St","city":"Richmond","state":"VA","zip":"23220","country":"USA"}}}}
charge transactionResponse: {"responseCode":"2","transId":"123456","authCode":"ABC123","avsResultCode":"N","cvvResultCode":"P","accountNumber":"************1111","errors":{"errorCode":"54","errorText":"Card expired"}}
```

Clear the log regularly in production environments to avoid retaining outdated debugging information.
