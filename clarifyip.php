<?php

/**
 * Plugin Name:     ClarifyIP Geo Blocking
 * Description:     Experience unrivaled speed and precision with our industry-leading IP geolocation service, setting new standards for efficiency and accuracy on the web. Bid farewell to delays as our platform swiftly pinpoints the exact locations of IP addresses.
 * Version:         1.0.0
 * Author:          ClarifyIP
 * Author URI:      https://clarifyip.com/
 * License:         GPLv3
 * License URI:     http://www.gnu.org/licenses/gpl.html
 * Text Domain:     clarifyip-geo-block
 */

// No direct access allowed
if (!defined('ABSPATH')) exit;

// Define
define('CLARIFYIP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CLARIFYIP_PLUGIN_DIR', dirname(__FILE__));
define('CLARIFYIP_PLUGIN_BASE_FILE', plugin_basename(__FILE__));

// Require
require_once CLARIFYIP_PLUGIN_DIR . '/includes/ClarifyIP_Countries.php';
require_once CLARIFYIP_PLUGIN_DIR . '/includes/ClarifyIP_Admin.php';
require_once CLARIFYIP_PLUGIN_DIR . '/includes/ClarifyIP_Api.php';
require_once CLARIFYIP_PLUGIN_DIR . '/includes/ClarifyIP.php';

// Init
new ClarifyIP();
