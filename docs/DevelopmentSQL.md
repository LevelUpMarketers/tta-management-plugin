# Development SQL Assets

This document collects helpful SQL snippets for testing and development. They are designed for a local sandbox WordPress environment.

## Automatic Upgrades

The plugin automatically runs `dbDelta()` when its database version changes. Any new columns introduced in updates are created without manual intervention. The current version is stored in the `tta_db_version` option.

WordPress's `dbDelta()` does not reliably manage `FOREIGN KEY` constraints. The plugin no longer defines them in table schemas to avoid upgrade errors. Relationships are maintained in application logic instead.

## Import WordPress users into `tta_members` & assign hosts & volunteers

Use the following SQL to copy existing WordPress users into the `tta_members`
table. It also sets specific member levels and types so you have ready-made test
accounts for different scenarios. Run this in a local development environment.

```sql
INSERT INTO `wp_j9bzlz98u3_tta_members` (
  wpuserid,
  first_name,
  last_name,
  email,
  joined_at
)
SELECT
  ID AS wpuserid,
  -- take the first word of display_name as first_name
  CASE
    WHEN display_name LIKE '% %'
      THEN SUBSTRING_INDEX(display_name, ' ', 1)
    ELSE display_name
  END AS first_name,
  -- everything after the first space as last_name (or blank)
  CASE
    WHEN display_name LIKE '% %'
      THEN SUBSTRING(display_name, LOCATE(' ', display_name) + 1)
    ELSE ''
  END AS last_name,
  user_email AS email,
  user_registered AS joined_at
FROM
  `wp_j9bzlz98u3_users`;

UPDATE `wp_j9bzlz98u3_tta_members`
SET
  membership_level = CASE email
    WHEN 'tilypoquh@mailinator.com'       THEN 'basic'
    WHEN 'sicuzymyt@mailinator.com'        THEN 'premium'
    WHEN 'tryingtoadultrva@gmail.com'      THEN 'premium'
    WHEN 'eippih@gmail.com'                THEN 'premium'
    WHEN 'foreunner1618@gmail.com'         THEN 'premium'
    WHEN 'mariah.payne831@gmail.com'       THEN 'premium'
    WHEN 'claineryan13@gmail.com'          THEN 'premium'
    WHEN 'dana.p.harrell@gmail.com'        THEN 'premium'
    ELSE membership_level
  END,
  member_type = CASE email
    WHEN 'tilypoquh@mailinator.com'       THEN 'member'
    WHEN 'sicuzymyt@mailinator.com'        THEN 'member'
    WHEN 'tryingtoadultrva@gmail.com'      THEN 'super_admin'
    WHEN 'eippih@gmail.com'                THEN 'super_admin'
    WHEN 'foreunner1618@gmail.com'         THEN 'admin'
    WHEN 'mariah.payne831@gmail.com'       THEN 'admin'
    WHEN 'claineryan13@gmail.com'          THEN 'volunteer'
    WHEN 'dana.p.harrell@gmail.com'        THEN 'volunteer'
    ELSE member_type
  END
WHERE email IN (
  'tilypoquh@mailinator.com',
  'sicuzymyt@mailinator.com',
  'tryingtoadultrva@gmail.com',
  'eippih@gmail.com',
  'foreunner1618@gmail.com',
  'mariah.payne831@gmail.com',
  'claineryan13@gmail.com',
  'dana.p.harrell@gmail.com'
);
```

## Re-create and populate `tta_events` and `tta_tickets`

```sql
-- Disable foreign key checks to allow dropping
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables
DROP TABLE IF EXISTS `wp_j9bzlz98u3_tta_tickets`;
DROP TABLE IF EXISTS `wp_j9bzlz98u3_tta_events`;

-- Re-create tta_tickets
CREATE TABLE `wp_j9bzlz98u3_tta_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_ute_id` VARCHAR(255) NOT NULL,
  `event_name` VARCHAR(255) NOT NULL,
  `ticket_name` VARCHAR(255) NOT NULL,
  `waitlist_id` BIGINT UNSIGNED NOT NULL,
  `ticketlimit` INT UNSIGNED NOT NULL,
  `memberlimit` INT UNSIGNED NOT NULL DEFAULT 2,
  `baseeventcost` DECIMAL(10,2) NOT NULL,
  `discountedmembercost` DECIMAL(10,2) NOT NULL,
  `premiummembercost` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Re-create tta_events
CREATE TABLE `wp_j9bzlz98u3_tta_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ute_id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `date` DATE NOT NULL,
  `baseeventcost` DECIMAL(10,2) NOT NULL,
  `discountedmembercost` DECIMAL(10,2) NOT NULL,
  `premiummembercost` DECIMAL(10,2) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `time` VARCHAR(50) NOT NULL,
  `venuename` VARCHAR(255) NOT NULL,
  `venueurl` VARCHAR(2083) DEFAULT NULL,
  `url2` VARCHAR(2083) DEFAULT NULL,
  `url3` VARCHAR(2083) DEFAULT NULL,
  `url4` VARCHAR(2083) DEFAULT NULL,
  `mainimageid` BIGINT UNSIGNED DEFAULT NULL,
  `otherimageids` VARCHAR(255) DEFAULT NULL,
  `waitlistavailable` TINYINT(1) NOT NULL DEFAULT 0,
  `waitlist_id` BIGINT UNSIGNED NOT NULL,
  `page_id` BIGINT UNSIGNED NOT NULL,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `discountcode` TEXT,
  `all_day_event` TINYINT(1) NOT NULL DEFAULT 0,
  `virtual_event` TINYINT(1) NOT NULL DEFAULT 0,
  `refundsavailable` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Re-create tta_events_archive (same structure as tta_events)
CREATE TABLE `wp_j9bzlz98u3_tta_events_archive` LIKE `wp_j9bzlz98u3_tta_events`;

-- Re-create tta_tickets_archive (same structure as tta_tickets)
CREATE TABLE `wp_j9bzlz98u3_tta_tickets_archive` LIKE `wp_j9bzlz98u3_tta_tickets`;

-- Re-create tta_attendees_archive (same structure as tta_attendees but referencing archive tickets)
CREATE TABLE `wp_j9bzlz98u3_tta_attendees_archive` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` BIGINT UNSIGNED NOT NULL,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `first_name` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT '',
  `opt_in_sms` TINYINT(1) DEFAULT 0,
  `opt_in_email` TINYINT(1) DEFAULT 0,
  `is_member` TINYINT(1) DEFAULT 0,
  `status` ENUM('pending','checked_in','no_show') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `transaction_idx` (`transaction_id`),
  KEY `ticket_idx` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Populate tta_tickets
INSERT INTO `wp_j9bzlz98u3_tta_tickets`
  (`id`, `event_ute_id`, `event_name`, `ticket_name`, `waitlist_id`, `ticketlimit`, `memberlimit`, `baseeventcost`, `discountedmembercost`, `premiummembercost`)
VALUES
  (8, 'tte_6852c9ecec0713.67263231', 'New Member Dinner at Tres Machos', 'General Admission', 8, 15, 2, 20.00, 15.00, 10.00),
  (9, 'tte_68554e50b4e3b2.57528002', 'Buffet & Besties at King\u2019s Korner', 'General Admission', 9, 12, 2,  0.00,  0.00,  0.00),
  (10,'tte_68554f6943c920.35289009', 'Roller Skating',               'General Admission',10, 30, 2, 12.00, 10.00,  8.00);

-- Populate tta_events
INSERT INTO `wp_j9bzlz98u3_tta_events`
  (`id`, `ute_id`, `name`, `date`,       `baseeventcost`, `discountedmembercost`, `premiummembercost`,
   `address`,                                      `type`,    `time`,        `venuename`, `venueurl`,                                                   `url2`,                                                        `url3`,                                                                 `url4`,                                                                `mainimageid`, `otherimageids`,        `waitlistavailable`, `waitlist_id`, `page_id`, `ticket_id`, `discountcode`,                                            `all_day_event`, `virtual_event`, `refundsavailable`, `created_at`,           `updated_at`)
VALUES
  (3,  'tte_6852c9ecec0713.67263231', 'New Member Dinner at Tres Machos','2025-06-30', 20.00,            15.00,                10.00,
       '2313 Westwood Ave -  - Richmond - Virginia - 23230',       'free',  '18:00|20:00','Tres Machos', 'http://www.tresmachos.com/',                                 'https://www.yelp.com/biz/tres-machos-richmond-3',           'https://www.tripadvisor.com/Restaurant_Review-g60893-d15708234-Reviews-Tres_Machos-Richmond_Virginia.html', 'https://www.instagram.com/tresmachos.glenallen/?hl=en', 19338,          '18705,18714,18857', 1,                   8,           22018,        8,            '{"code":"Discount10Percent","type":"percent","amount":10}', 0,               0,               1,             '2025-06-18 10:15:08', '2025-06-20 07:59:04'),
  (4,  'tte_68554e50b4e3b2.57528002', 'Buffet & Besties at King\u2019s Korner','2025-07-11',  0.00,             0.00,                 0.00,
       '7511 Airfield Dr -  - North Chesterfield - Virginia - 23237','paid',  '18:30|20:30','King\'s Korner Catering and Restaurant','https://www.kingskornercatering.com/?utm_source=google&utm_medium=organic&utm_campaign=gmb_profile','https://www.facebook.com/kingskornercatering/','https://www.yelp.com/biz/kings-korner-catering-and-restaurant-north-chesterfield','https://www.tripadvisor.com/Restaurant_Review-g60893-d395520-Reviews-King_s_Korner_Restaurant-Richmond_Virginia.html',22021,'22022,22023,22024,22025',1,9,22026,9,'',0,0,0,'2025-06-20 08:04:32','2025-06-20 08:04:32'),
  (5,  'tte_68554f6943c920.35289009', 'Roller Skating',                    '2025-07-09', 12.00,            10.00,                 8.00,
       '4902 Williamsburg Rd -  - Richmond - Virginia - 23231',   'paid',  '19:30|21:30','Rollerdome',  'http://www.rollerdomeskating.com/',                      'https://www.facebook.com/RollerdomeSkating/','https://www.yelp.com/biz/roller-dome-richmond',                'https://www.instagram.com/explore/locations/9273866/rollerdome-skating/?hl=en',22028,'22029,22030,22031,22032,22033,22034',1,10,22035,10,'{"code":"2OffSpecial","type":"flat","amount":2}',0,0,1,'2025-06-20 08:09:13','2025-06-20 08:09:13');

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
```

## Add `hide_event_attendance` Column

Run this SQL after updating the plugin to add the new privacy option on existing installs:

```sql
ALTER TABLE `wp_j9bzlz98u3_tta_members`
  ADD COLUMN `hide_event_attendance` TINYINT(1) DEFAULT 0
  AFTER `opt_in_event_update_sms`;
```

## Add `hosts` and `volunteers` Columns

Run this after updating the plugin to store event host and volunteer names:

```sql
ALTER TABLE `wp_j9bzlz98u3_tta_events`
  ADD COLUMN `hosts` TEXT AFTER `refundsavailable`,
  ADD COLUMN `volunteers` TEXT AFTER `hosts`;
```

## Add `host_notes` Column

Version 1.8.0 stores internal notes for event hosts. If upgrading manually run:

```sql
ALTER TABLE `wp_j9bzlz98u3_tta_events`
  ADD COLUMN `host_notes` TEXT AFTER `volunteers`;
ALTER TABLE `wp_j9bzlz98u3_tta_events_archive`
  ADD COLUMN `host_notes` TEXT AFTER `volunteers`;
```

## Add phone and opt-in columns to `tta_attendees`

If you updated from a version prior to 1.0.0, these columns will be added automatically when the plugin loads. If you prefer to run the SQL manually, use:

```sql
ALTER TABLE `wp_j9bzlz98u3_tta_attendees`
  ADD COLUMN `phone` VARCHAR(50) DEFAULT '',
  ADD COLUMN `opt_in_sms` TINYINT(1) DEFAULT 0,
  ADD COLUMN `opt_in_email` TINYINT(1) DEFAULT 0;
```

## Track whether attendees are members

Version 1.1.0 adds an `is_member` column to `tta_attendees`. Existing installs will update automatically, but the raw SQL is:

```sql
ALTER TABLE `wp_j9bzlz98u3_tta_attendees`
  ADD COLUMN `is_member` TINYINT(1) DEFAULT 0;
```

## Track attendance check-in status

Version 1.2.0 introduces an `status` column on `tta_attendees` to record whether each attendee was checked in or marked a no-show. Existing installs will add the column automatically, or you can run:

```sql
ALTER TABLE `wp_j9bzlz98u3_tta_attendees`
  ADD COLUMN `status` ENUM('pending','checked_in','no_show') DEFAULT 'pending';
```

## Store subscription IDs

Version 1.3.0 adds a `subscription_id` column to `tta_members` so we can track recurring billing subscriptions. Existing installs update automatically but you can run the SQL manually:

```sql
ALTER TABLE `wp_j9bzlz98u3_tta_members`
  ADD COLUMN `subscription_id` VARCHAR(50) DEFAULT NULL AFTER `membership_level`;
```

## Track subscription status

Version 1.4.0 adds a `subscription_status` column to `tta_members` which stores `active`, `cancelled`, or `paymentproblem` for each subscription. The column defaults to `NULL` for new members.

```sql
ALTER TABLE `wp_j9bzlz98u3_tta_members`
  ADD COLUMN `subscription_status` ENUM('active','cancelled','paymentproblem') DEFAULT NULL AFTER `subscription_id`;
```

## Add attendee email indexes

Version 1.10.0 adds an index on the `email` column of both `tta_attendees` and `tta_attendees_archive` tables.
This improves lookup performance. Existing installs update automatically, or you can run:

```sql
ALTER TABLE `wp_j9bzlz98u3_tta_attendees`
  ADD KEY `email_idx` (`email`);
ALTER TABLE `wp_j9bzlz98u3_tta_attendees_archive`
  ADD KEY `email_idx` (`email`);
```
