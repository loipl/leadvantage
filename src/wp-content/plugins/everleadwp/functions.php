<?php
/*
Plugin Name: WP EverLead 
Plugin URI: http://www.getinstantpayments.com/everlead
Description: Grow ever green leads for your business
Version: 1.0
Author: WPEverLead
Author URI: http://www.getinstantpayments.com/catalog
*/

// Activation Here:
register_activation_hook(__FILE__, 'everleadwp_installer');

include("inc/activation.php");

// AJAX Callbacks:
include("inc/callback.php");

// Menu Here:
include("inc/menu.php");

// Dashboard:
include("UI/index.php");

// Link Up LP With WP Page
include("inc/page_link.php");


?>