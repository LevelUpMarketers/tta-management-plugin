# Development SQL Assets

This document collects helpful SQL snippets for testing and development. They are designed for a local sandbox WordPress environment.

## Import WordPress users into `tta_members`

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

-- Populate tta_tickets
INSERT INTO `wp_j9bzlz98u3_tta_tickets`
  (`id`, `event_ute_id`, `event_name`, `ticket_name`, `waitlist_id`, `ticketlimit`, `baseeventcost`, `discountedmembercost`, `premiummembercost`)
VALUES
  (8, 'tte_6852c9ecec0713.67263231', 'New Member Dinner at Tres Machos', 'General Admission', 8, 15, 20.00, 15.00, 10.00),
  (9, 'tte_68554e50b4e3b2.57528002', 'Buffet & Besties at King\u2019s Korner', 'General Admission', 9, 12,  0.00,  0.00,  0.00),
  (10,'tte_68554f6943c920.35289009', 'Roller Skating',               'General Admission',10, 30, 12.00, 10.00,  8.00);

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
