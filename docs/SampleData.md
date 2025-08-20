# Sample Database Data

The plugin bundles development data for testing. From the **TTA Settings** → **General Settings** tab you can load twenty-four fake upcoming events scheduled over the next two months along with matching tickets, members and example transactions. Each sample event is created the same way as a normal event including its WordPress page so calendar links work and attendees are generated for each event. When loading the data the loader attempts to assign a random image from your media library as the featured image for each event.

Click **Load Sample Data** under `/wp-admin/admin.php?page=tta-settings&tab=general` to insert the sample rows into every custom table (`tta_events`, `tta_tickets`, `tta_members`, `tta_transactions`, `tta_attendees`, `tta_memberhistory`, `tta_waitlist`, `tta_venues`, etc.) and automatically generate pages. The loader now creates WordPress user accounts for each sample member so you can log in with them during testing and also imports all existing WordPress users into the `tta_members` table so `wpuserid` values match. Sample transactions contain realistic item details, the last four digits of a fake credit card and history entries. Events cycle through free/paid, waitlist, all-day and virtual permutations so all columns are exercised. Use **Delete Sample Data** on the same screen to completely empty every TTA table and delete any event pages. All caches are flushed after each action.
Each sample event also creates a matching record in the `tta_venues` table so venue autocomplete works during editing.

The raw arrays are stored in `database-testing/sample-events.php`, `database-testing/sample-tickets.php` and `database-testing/sample-members.php`.

## Sample Member Accounts

The loader creates a few real WordPress accounts for convenience:

| Membership Level | Member Type  | Name             | Email                        | Password                       |
|------------------|--------------|------------------|------------------------------|--------------------------------|
| Basic            | Member       | Stacy Harper     | tilypoquh@mailinator.com     | `##ALNEE#DLI)wZHvOp14A8Tp`     |
| Premium          | Member       | Tucker Copeland  | sicuzymyt@mailinator.com     | `^$^^6@TyiDpiL72B3rZ7v*tY`      |
| Premium          | Super Admin  | Sam Lydard       | tryingtoadultrva@gmail.com   | `bNe#JO#h)uyP30oAdcZkrQfi`     |
| Premium          | Super Admin  | Julie Marsh      | eippih@gmail.com             | `a14B%(T*UXk1auRFd)#ZNw)g`     |
| Premium          | Admin        | Adam Peoples     | foreunner1618@gmail.com      | `3grQTvBOODPRtOOQESmS0TXD`     |
| Premium          | Admin        | Mariah Payne     | mariah.payne831@gmail.com    | `3grQTvBOODPRtOOQESmS0TXD`     |
| Premium          | Volunteer    | Cassidy Ryan     | claineryan13@gmail.com       | `^yDYADcss&kcH29yxhdvnJXO`     |
| Premium          | Volunteer    | Dana Harrell     | dana.p.harrell@gmail.com     | `b0niD@oMxf9wax@n8*@DIYGH`     |

These accounts are also used to populate the rest of the database. Each
sample event links one of the members above to a transaction containing
their attendees. Every fifth event logs a refund so you can test how the
plugin records refund amounts and history entries.

Some of the sample members only provide names and emails. The loader now handles
missing fields like phone numbers so warnings do not appear during installation.
Every column is filled with placeholder values so member profiles appear complete.
