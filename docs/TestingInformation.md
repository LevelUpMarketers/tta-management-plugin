# Testing Information

This document summarizes test accounts and card numbers used for local development of the Trying To Adult Management Plugin. The data below is provided by Authorize.Net for sandbox use only. The plugin talks to the live Authorize.Net environment by default; switch to the sandbox under **TTA Settings → API Settings** by selecting the sandbox environment. Each environment remembers its own API Login ID and Transaction Key and the form auto-fills the saved credentials whenever you toggle between Live and Sandbox.

## Test Members

| Membership Level | Member Type | Name            | Email                    | Password                 |
|------------------|-------------|-----------------|--------------------------|--------------------------|
| Basic            | Member      | Stacy Harper    | tilypoquh@mailinator.com | ##ALNEE#DLI)wZHvOp14A8Tp |
| Premium          | Member      | Tucker Copeland | sicuzymyt@mailinator.com | ^$^^6@TyiDpiL72B3rZ7v*tY |
| Premium          | Super Admin | Sam Lydard      | tryingtoadultrva@gmail.com | bNe#JO#h)uyP30oAdcZkrQfi |
| Premium          | Super Admin | Julie Marsh     | eippih@gmail.com         | a14B%(T*UXk1auRFd)#ZNw)g |
| Premium          | Admin       | Adam Peoples    | foreunner1618@gmail.com  | 3grQTvBOODPRtOOQESmS0TXD |
| Premium          | Admin       | Mariah Payne    | mariah.payne831@gmail.com | 3grQTvBOODPRtOOQESmS0TXD |
| Premium          | Volunteer   | Cassidy Ryan | claineryan13@gmail.com   | ^yDYADcss&kcH29yxhdvnJXO |
| Premium          | Volunteer   | Dana Harrell    | dana.p.harrell@gmail.com | b0niD@oMxf9wax@n8*@DIYGH |

## Authorize.Net Test Credit Card Numbers

The following card numbers are publicly available in the [Authorize.Net testing guide](https://developer.authorize.net/hello_world/testing_guide.html). They will only work in sandbox mode. Use any expiration date after today's date. For the card code, use any three digits (or four digits for American Express).

| Card Brand | Number |
|------------|-------------------|
| American Express | 370000000000002 |
| China UnionPay | 6221499053360818 |
| China UnionPay | 6262320002000067 |
| China UnionPay | 6284480000000008 |
| Discover | 6011000000000012 |
| JCB | 3088000000000017 |
| Diners Club / Carte Blanche | 38000000000006 |
| Visa | 4007000000027 |
| Visa | 4012888818888 |
| Visa | 4111111111111111 |
| Mastercard | 5424000000000015 |
| Mastercard | 2223000010309703 |
| Mastercard | 2223000010309711 |


## PHPUnit Suite Notes

- The `MemberTest::test_update_member_changes_data` test previously caused the suite to hang.
- This was due to the `wp_update_user` stub looping indefinitely when updating the user email.
- The stub now updates the `$GLOBALS['wp_users']` array without modifying it during iteration, allowing the suite to complete.
