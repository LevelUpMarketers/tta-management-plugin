<?php
// includes/ajax/class-ajax-handler.php

if ( ! defined( 'ABSPATH' ) ) exit;

// Load each handler
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-events.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-members.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-tickets.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-cart.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-membership.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-checkout.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-comms.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-attendance.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-calendar.php';


// Initialize them
TTA_Ajax_Events::init();
TTA_Ajax_Members::init();
TTA_Ajax_Tickets::init();
TTA_Ajax_Cart::init();
TTA_Ajax_Membership::init();
TTA_Ajax_Checkout::init();
TTA_Ajax_Comms::init();
TTA_Ajax_Attendance::init();
TTA_Ajax_Calendar::init();
