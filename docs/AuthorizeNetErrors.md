# Authorize.Net Error Codes

The plugin surfaces Authorize.Net error codes during checkout and refunds. When a common error is detected, an additional explanation is appended to the message.

| Code   | Meaning | Additional Notes |
| ------ | ------- | ---------------- |
| `E00001` | An unexpected error occurred. | Retry the request. |
| `E00002` | Login invalid or account inactive. | Check the API Login ID and Transaction Key. |
| `E00003` | The referenced record was not found. | Likely an invalid transaction or customer ID. |
| `E00007` | User authentication failed. | Verify your sandbox credentials. |
| `E00027` | The transaction was unsuccessful. | Typically declined by the processor or card issuer. |
| `54` | The referenced transaction does not meet the criteria for issuing a credit. | Usually indicates the transaction has not settled yet. |

Unknown codes continue to display as-is from Authorize.Net.
