<?php
/**
 * Handles DB creation for the TTA plugin.
 */
class TTA_DB_Setup {
    public static function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $prefix = $wpdb->prefix . 'tta_';
        $tables = [];

        // Events table
        $tables[] = "CREATE TABLE {$prefix}events (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            ute_id VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            baseeventcost DECIMAL(10,2) DEFAULT 0.00,
            discountedmembercost DECIMAL(10,2) DEFAULT 0.00,
            premiummembercost DECIMAL(10,2) DEFAULT 0.00,
            address VARCHAR(500) DEFAULT '',
            type VARCHAR(50) DEFAULT 'free',
            time VARCHAR(50) DEFAULT 'N/A',
            venueurl VARCHAR(255) DEFAULT '',
            venuename VARCHAR(255) DEFAULT '',     /* ← NEW column */
            url2 VARCHAR(255) DEFAULT '',
            url3 VARCHAR(255) DEFAULT '',
            url4 VARCHAR(255) DEFAULT '',
            mainimageid BIGINT UNSIGNED DEFAULT 0,
            otherimageids TEXT,
            attendancelimited TINYINT(1) DEFAULT 0,
            waitlistavailable TINYINT(1) DEFAULT 0,
            attendancelimit INT UNSIGNED DEFAULT 0,
            waitlist_id INT UNSIGNED DEFAULT 0,
            page_id INT UNSIGNED DEFAULT 0,
            ticket_id INT UNSIGNED DEFAULT 0,
            discountcode VARCHAR(255) DEFAULT '',
            all_day_event TINYINT(1) DEFAULT 0,
            virtual_event TINYINT(1) DEFAULT 0,
            refundsavailable TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ute_id (ute_id)
        ) $charset_collate";


        // Members table
        $tables[] = "CREATE TABLE {$prefix}members (
            id                              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            wpuserid                        BIGINT(20) UNSIGNED NOT NULL,
            first_name                      VARCHAR(100)            NOT NULL,
            last_name                       VARCHAR(100)            NOT NULL,
            email                           VARCHAR(191)            NOT NULL,
            profileimgid                    BIGINT(20) UNSIGNED     DEFAULT 0,
            joined_at                       DATETIME                NOT NULL,
            address                         VARCHAR(255)            NOT NULL DEFAULT '',
            phone                           VARCHAR(20)             DEFAULT NULL,
            dob                             DATE                    DEFAULT NULL,
            member_type                     ENUM('member','volunteer','admin','super_admin') DEFAULT 'member',
            membership_level                ENUM('free','basic','premium')            DEFAULT 'free',
            facebook                        VARCHAR(191)            DEFAULT NULL,
            linkedin                        VARCHAR(191)            DEFAULT NULL,
            instagram                       VARCHAR(191)            DEFAULT NULL,
            twitter                         VARCHAR(191)            DEFAULT NULL,
            biography                       TEXT                    DEFAULT NULL,
            notes                           TEXT                    DEFAULT NULL,
            interests                       TEXT                    DEFAULT NULL,
            opt_in_marketing_email          TINYINT(1)              DEFAULT 0,
            opt_in_marketing_sms            TINYINT(1)              DEFAULT 0,
            opt_in_event_update_email       TINYINT(1)              DEFAULT 0,
            opt_in_event_update_sms         TINYINT(1)              DEFAULT 0,
            PRIMARY KEY (id),
            KEY (wpuserid),
            UNIQUE KEY email (email)
        ) $charset_collate";


        // Tickets table
        $tables[] = "CREATE TABLE {$prefix}tickets (
            id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_ute_id           VARCHAR(100)     NOT NULL,
            event_name             VARCHAR(255)     NOT NULL,
            waitlist_id            INT UNSIGNED     NOT NULL,
            attendancelimit        INT UNSIGNED     NOT NULL,
            baseeventcost          DECIMAL(10,2)    NOT NULL,
            discountedmembercost   DECIMAL(10,2)    NOT NULL,
            premiummembercost      DECIMAL(10,2) DEFAULT 0.00,
            PRIMARY KEY  (id)
        ) $charset_collate";

        // Member history table
        $tables[] = "CREATE TABLE {$prefix}memberhistory (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT UNSIGNED NOT NULL,
            action_type VARCHAR(100) NOT NULL,
            action_data TEXT,
            action_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY member_id (member_id)
        ) $charset_collate";

        // Waitlist table
        $tables[] = "CREATE TABLE {$prefix}waitlist (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_ute_id VARCHAR(100)     NOT NULL,
            ticket_id BIGINT UNSIGNED NOT NULL,
            event_name   VARCHAR(255)     NOT NULL,
            userids      TEXT             NOT NULL,
            added_at     DATETIME         DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        foreach ( $tables as $sql ) {
            dbDelta( $sql );
        }
    }

    public static function uninstall() {
        // No automatic drops by default
    }
}
