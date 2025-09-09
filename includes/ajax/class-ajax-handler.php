<?php
// includes/ajax/class-ajax-handler.php

if ( ! defined( 'ABSPATH' ) ) exit;

// Load each handler
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-events.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-members.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-tickets.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-cart.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-membership.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-membership-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-checkout.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-auth.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-comms.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-attendance.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-calendar.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-venues.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-ads.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-authnet-test.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-bi.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-waitlist.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-refund.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-assistance.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-email-logs.php';


// Initialize them
TTA_Ajax_Events::init();
TTA_Ajax_Members::init();
TTA_Ajax_Tickets::init();
TTA_Ajax_Cart::init();
TTA_Ajax_Membership::init();
TTA_Ajax_Membership_Admin::init();
TTA_Ajax_Checkout::init();
TTA_Ajax_Comms::init();
TTA_Ajax_Attendance::init();
TTA_Ajax_Calendar::init();
TTA_Ajax_Venues::init();
TTA_Ajax_Ads::init();
TTA_Ajax_Authnet_Test::init();
TTA_Ajax_Auth::init();
TTA_Ajax_BI::init();
TTA_Ajax_Waitlist::init();
TTA_Ajax_Refund::init();
TTA_Ajax_Assistance::init();
TTA_Ajax_Email_Logs::init();
