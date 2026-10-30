<?php
/**
 * Plugin Name: Workparcel
 * Description: Manage shipments and track parcels from WordPress with branded tracking pages, WooCommerce integration, barcode scanning, and a REST API.
 * Version: 1.1.2
 * Author: faav11_
 * License: GPL-2.0-or-later
 * Text Domain: workparcel
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WORKPARCEL_VERSION', '1.1.2' );
define( 'WORKPARCEL_FILE', __FILE__ );
define( 'WORKPARCEL_DIR', plugin_dir_path( __FILE__ ) );
define( 'WORKPARCEL_URL', plugin_dir_url( __FILE__ ) );

require_once WORKPARCEL_DIR . 'includes/class-database.php';
require_once WORKPARCEL_DIR . 'includes/class-capabilities.php';
require_once WORKPARCEL_DIR . 'includes/class-barcode.php';
require_once WORKPARCEL_DIR . 'includes/class-customer.php';
require_once WORKPARCEL_DIR . 'includes/class-shipment.php';
require_once WORKPARCEL_DIR . 'includes/class-tracking.php';
require_once WORKPARCEL_DIR . 'includes/class-settings.php';
require_once WORKPARCEL_DIR . 'includes/class-shortcodes.php';
require_once WORKPARCEL_DIR . 'includes/class-scan.php';
require_once WORKPARCEL_DIR . 'includes/class-rest-api.php';
require_once WORKPARCEL_DIR . 'includes/class-plugin.php';
require_once WORKPARCEL_DIR . 'includes/class-woocommerce.php';
require_once WORKPARCEL_DIR . 'includes/class-mailer.php';

register_activation_hook( __FILE__, array( 'Workparcel\\Database', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Workparcel\\Database', 'deactivate' ) );

\Workparcel\WooCommerceIntegration::init();
\Workparcel\Mailer::init();
\Workparcel\Scan::init();
\Workparcel\RestApi::init();

add_action( 'plugins_loaded', function () {
	\Workparcel\Plugin::instance()->init();
} );
