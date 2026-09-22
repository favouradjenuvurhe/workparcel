<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;
require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-capabilities.php';
\Workparcel\Database::uninstall();
