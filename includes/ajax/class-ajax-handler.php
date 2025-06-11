<?php
// includes/ajax/class-ajax-handler.php

if ( ! defined( 'ABSPATH' ) ) exit;

// Load each handler
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-events.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-members.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-tickets.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/handlers/class-ajax-cart.php';


// Initialize them
TTA_Ajax_Events::init();
TTA_Ajax_Members::init();
TTA_Ajax_Tickets::init();
TTA_Ajax_Cart::init();
