<?php
/**
 * Handles DB creation for the TTA plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_DB_Setup {

    /**
     * Install or upgrade the plugin’s database tables.
     */
    public static function install() {
        global $wpdb;

        // Ensure dbDelta() is available
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $prefix          = $wpdb->prefix . 'tta_';

        $sql_statements = [];

        // ─────────────────────────────────────────────────────────────────
        // Venues table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}venues (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            address VARCHAR(500) DEFAULT '',
            venueurl VARCHAR(255) DEFAULT '',
            url2 VARCHAR(255) DEFAULT '',
            url3 VARCHAR(255) DEFAULT '',
            url4 VARCHAR(255) DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Events table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}events (
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
            venuename VARCHAR(255) DEFAULT '',
            venueurl VARCHAR(255) DEFAULT '',
            url2 VARCHAR(255) DEFAULT '',
            url3 VARCHAR(255) DEFAULT '',
            url4 VARCHAR(255) DEFAULT '',
            mainimageid BIGINT UNSIGNED DEFAULT 0,
            otherimageids TEXT,
            waitlistavailable TINYINT(1) DEFAULT 0,
            waitlist_id BIGINT UNSIGNED DEFAULT 0,
            page_id BIGINT UNSIGNED DEFAULT 0,
            ticket_id BIGINT UNSIGNED DEFAULT 0,
            discountcode VARCHAR(255) DEFAULT '',
            all_day_event TINYINT(1) DEFAULT 0,
            virtual_event TINYINT(1) DEFAULT 0,
            refundsavailable TINYINT(1) DEFAULT 0,
            hosts TEXT,
            volunteers TEXT,
            host_notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY ute_id (ute_id),
            KEY page_id_idx (page_id),
            KEY date_idx (date)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Events archive table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}events_archive (
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
            venuename VARCHAR(255) DEFAULT '',
            venueurl VARCHAR(255) DEFAULT '',
            url2 VARCHAR(255) DEFAULT '',
            url3 VARCHAR(255) DEFAULT '',
            url4 VARCHAR(255) DEFAULT '',
            mainimageid BIGINT UNSIGNED DEFAULT 0,
            otherimageids TEXT,
            waitlistavailable TINYINT(1) DEFAULT 0,
            waitlist_id BIGINT UNSIGNED DEFAULT 0,
            page_id BIGINT UNSIGNED DEFAULT 0,
            ticket_id BIGINT UNSIGNED DEFAULT 0,
            discountcode VARCHAR(255) DEFAULT '',
            all_day_event TINYINT(1) DEFAULT 0,
            virtual_event TINYINT(1) DEFAULT 0,
            refundsavailable TINYINT(1) DEFAULT 0,
            hosts TEXT,
            volunteers TEXT,
            host_notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY ute_id (ute_id),
            KEY page_id_idx (page_id),
            KEY date_idx (date)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Members table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}members (
            id                              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wpuserid                        BIGINT UNSIGNED NOT NULL,
            first_name                      VARCHAR(100) NOT NULL,
            last_name                       VARCHAR(100) NOT NULL,
            email                           VARCHAR(191) NOT NULL,
            profileimgid                    BIGINT UNSIGNED DEFAULT 0,
            joined_at                       DATETIME NOT NULL,
            address                         VARCHAR(255) NOT NULL DEFAULT '',
            phone                           VARCHAR(20) DEFAULT NULL,
            dob                             DATE DEFAULT NULL,
            member_type                     ENUM('member','volunteer','admin','super_admin') DEFAULT 'member',
            membership_level                ENUM('free','basic','premium') DEFAULT 'free',
            subscription_id                 VARCHAR(50) DEFAULT NULL,
            subscription_status            ENUM('active','cancelled','paymentproblem') DEFAULT NULL,
            facebook                        VARCHAR(191) DEFAULT NULL,
            linkedin                        VARCHAR(191) DEFAULT NULL,
            instagram                       VARCHAR(191) DEFAULT NULL,
            twitter                         VARCHAR(191) DEFAULT NULL,
            biography                       TEXT DEFAULT NULL,
            notes                           TEXT DEFAULT NULL,
            interests                       TEXT DEFAULT NULL,
            opt_in_marketing_email          TINYINT(1) DEFAULT 0,
            opt_in_marketing_sms            TINYINT(1) DEFAULT 0,
            opt_in_event_update_email       TINYINT(1) DEFAULT 0,
            opt_in_event_update_sms         TINYINT(1) DEFAULT 0,
            hide_event_attendance           TINYINT(1) DEFAULT 0,
            banned_until                   DATETIME DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY wpuserid_idx (wpuserid),
            KEY name_idx (last_name, first_name),
            UNIQUE KEY email (email)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Tickets table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}tickets (
            id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_ute_id           VARCHAR(100) NOT NULL,
            event_name             VARCHAR(255) NOT NULL,
            ticket_name            VARCHAR(255) NOT NULL,
            waitlist_id            BIGINT UNSIGNED NOT NULL,
            ticketlimit            INT UNSIGNED NOT NULL DEFAULT 10000,
            memberlimit            INT UNSIGNED NOT NULL DEFAULT 2,
            baseeventcost          DECIMAL(10,2) NOT NULL,
            discountedmembercost   DECIMAL(10,2) NOT NULL,
            premiummembercost      DECIMAL(10,2) DEFAULT 0.00,
            PRIMARY KEY (id),
            KEY event_ute_id_idx (event_ute_id)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Tickets archive table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}tickets_archive (
            id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_ute_id           VARCHAR(100) NOT NULL,
            event_name             VARCHAR(255) NOT NULL,
            ticket_name            VARCHAR(255) NOT NULL,
            waitlist_id            BIGINT UNSIGNED NOT NULL,
            ticketlimit            INT UNSIGNED NOT NULL DEFAULT 10000,
            memberlimit            INT UNSIGNED NOT NULL DEFAULT 2,
            baseeventcost          DECIMAL(10,2) NOT NULL,
            discountedmembercost   DECIMAL(10,2) NOT NULL,
            premiummembercost      DECIMAL(10,2) DEFAULT 0.00,
            PRIMARY KEY (id),
            KEY event_ute_id_idx (event_ute_id)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Global discount codes table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}discount_codes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            code VARCHAR(50) NOT NULL,
            type ENUM('flat','percent') DEFAULT 'percent',
            amount DECIMAL(10,2) DEFAULT 0.00,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code (code)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Member history table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}memberhistory (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id    BIGINT UNSIGNED NOT NULL,
            wpuserid     BIGINT UNSIGNED NOT NULL,
            event_id     BIGINT UNSIGNED NOT NULL,
            action_type  VARCHAR(100) NOT NULL,
            action_data  TEXT,
            action_date  DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY   (id),
            KEY member_id_idx (member_id),
            KEY wpuserid_idx  (wpuserid),
            KEY event_id_idx  (event_id)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Waitlist table (with ticket_name)
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}waitlist (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_ute_id VARCHAR(100)     NOT NULL,
            ticket_id    BIGINT UNSIGNED NOT NULL,
            ticket_name  VARCHAR(255)     NOT NULL,
            event_name   VARCHAR(255)     NOT NULL,
            wp_user_id   BIGINT UNSIGNED DEFAULT 0,
            first_name   VARCHAR(255)     NOT NULL,
            last_name    VARCHAR(255)     NOT NULL,
            email        VARCHAR(255)     NOT NULL,
            phone        VARCHAR(50)      DEFAULT '',
            opt_in_email TINYINT(1)       DEFAULT 1,
            opt_in_sms   TINYINT(1)       DEFAULT 1,
            added_at     DATETIME         DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY   (id),
            KEY event_ute_id_idx (event_ute_id),
            KEY ticket_id_idx    (ticket_id),
            KEY wp_user_id_idx   (wp_user_id)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Carts table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$wpdb->prefix}tta_carts (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_key  VARCHAR(64)     NOT NULL,
            user_id      BIGINT UNSIGNED NULL,
            created_at   DATETIME        NOT NULL,
            expires_at   DATETIME        NOT NULL,
            locked_until DATETIME        NULL,
            PRIMARY KEY    (id),
            UNIQUE KEY     session_key_idx (session_key),
            KEY expires_at_idx (expires_at)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Cart items table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$wpdb->prefix}tta_cart_items (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            cart_id    BIGINT UNSIGNED NOT NULL,
            ticket_id  BIGINT UNSIGNED NOT NULL,
            quantity   INT UNSIGNED     NOT NULL,
            price      DECIMAL(10,2)    NOT NULL,
            added_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME        NOT NULL,
            PRIMARY KEY   (id),
            KEY cart_idx   (cart_id),
            KEY ticket_idx (ticket_id)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Transactions table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}transactions (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wpuserid        BIGINT UNSIGNED NOT NULL,
            member_id       BIGINT UNSIGNED NULL,
            transaction_id  VARCHAR(50)     NOT NULL,
            amount          DECIMAL(10,2)  NOT NULL,
            refunded        DECIMAL(10,2)  DEFAULT 0.00,
            card_last4      VARCHAR(4)     DEFAULT '',
            discount_code   VARCHAR(255)   DEFAULT '',
            discount_saved  DECIMAL(10,2)  DEFAULT 0.00,
            details         TEXT           NULL,
            created_at      DATETIME       DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY     (id),
            KEY wpuserid_idx       (wpuserid),
            KEY member_id_idx      (member_id),
            KEY transaction_id_idx (transaction_id)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Attendees table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}attendees (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            transaction_id  BIGINT UNSIGNED NOT NULL,
            ticket_id       BIGINT UNSIGNED NOT NULL,
            first_name      VARCHAR(255)   NOT NULL,
            last_name       VARCHAR(255)   NOT NULL,
            email           VARCHAR(255)   NOT NULL,
            phone           VARCHAR(50)    DEFAULT '',
            opt_in_sms      TINYINT(1)     DEFAULT 0,
            opt_in_email    TINYINT(1)     DEFAULT 0,
            is_member       TINYINT(1)     DEFAULT 0,
            assistance_note TEXT,
            status          ENUM('pending','checked_in','no_show') DEFAULT 'pending',
            PRIMARY KEY     (id),
            KEY transaction_idx (transaction_id),
            KEY ticket_idx      (ticket_id),
            KEY email_idx (email)
        ) $charset_collate";

        // ─────────────────────────────────────────────────────────────────
        // Attendees archive table
        // ─────────────────────────────────────────────────────────────────
        $sql_statements[] = "
        CREATE TABLE {$prefix}attendees_archive (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            transaction_id  BIGINT UNSIGNED NOT NULL,
            ticket_id       BIGINT UNSIGNED NOT NULL,
            first_name      VARCHAR(255)   NOT NULL,
            last_name       VARCHAR(255)   NOT NULL,
            email           VARCHAR(255)   NOT NULL,
            phone           VARCHAR(50)    DEFAULT '',
            opt_in_sms      TINYINT(1)     DEFAULT 0,
            opt_in_email    TINYINT(1)     DEFAULT 0,
            is_member       TINYINT(1)     DEFAULT 0,
            assistance_note TEXT,
            status          ENUM('pending','checked_in','no_show') DEFAULT 'pending',
            PRIMARY KEY     (id),
            KEY transaction_idx (transaction_id),
            KEY ticket_idx      (ticket_id),
            KEY email_idx (email)
        ) $charset_collate";

        // Run dbDelta on each statement
        foreach ( $sql_statements as $sql ) {
            dbDelta( $sql );
        }
    }

    /**
     * Run install when the stored DB version differs from the plugin version.
     */
    public static function maybe_upgrade() {
        $current = get_option( 'tta_db_version' );
        if ( $current !== TTA_DB_VERSION ) {
            self::install();
            update_option( 'tta_db_version', TTA_DB_VERSION, false );
        }
    }

    /**
     * Uninstall - nothing destructive by default.
     */
    public static function uninstall() {
        // You can drop tables here if desired.
    }
}

register_activation_hook( __FILE__, [ 'TTA_DB_Setup', 'install' ] );
