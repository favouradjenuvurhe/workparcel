<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Database {
	public static function activate() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$shipments = $wpdb->prefix . 'workparcel_shipments';
		$events = $wpdb->prefix . 'workparcel_tracking_events';

		$sql1 = "CREATE TABLE $shipments (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			tracking_number varchar(64) NOT NULL,
			reference varchar(100) NOT NULL DEFAULT '',
			title varchar(190) NOT NULL DEFAULT '',
			sender_name varchar(190) NOT NULL DEFAULT '',
			sender_email varchar(190) NOT NULL DEFAULT '',
			sender_phone varchar(50) NOT NULL DEFAULT '',
			sender_address text NOT NULL,
			receiver_name varchar(190) NOT NULL DEFAULT '',
			receiver_email varchar(190) NOT NULL DEFAULT '',
			receiver_phone varchar(50) NOT NULL DEFAULT '',
			receiver_address text NOT NULL,
			origin varchar(190) NOT NULL DEFAULT '',
			destination varchar(190) NOT NULL DEFAULT '',
			description text NOT NULL,
			parcel_type varchar(100) NOT NULL DEFAULT '',
			weight decimal(10,2) NOT NULL DEFAULT 0,
			quantity int(11) unsigned NOT NULL DEFAULT 1,
			shipping_fee decimal(12,2) NOT NULL DEFAULT 0,
			status varchar(40) NOT NULL DEFAULT 'pending',
			estimated_delivery date NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY tracking_number (tracking_number),
			KEY status (status),
			KEY created_at (created_at)
		) $charset;";

		$sql2 = "CREATE TABLE $events (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			shipment_id bigint(20) unsigned NOT NULL,
			status varchar(40) NOT NULL DEFAULT 'pending',
			location varchar(190) NOT NULL DEFAULT '',
			description text NOT NULL,
			event_date datetime NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY shipment_id (shipment_id),
			KEY event_date (event_date)
		) $charset;";

		dbDelta( $sql1 );
		dbDelta( $sql2 );

		update_option( 'workparcel_db_version', WORKPARCEL_VERSION );
		update_option( 'workparcel_settings', array(
			'tracking_page' => '',
			'default_status' => 'pending',
			'tracking_prefix' => 'WP',
			'company_name' => get_bloginfo( 'name' ),
			'tracking_title' => 'Track Your Parcel',
			'tracking_description' => 'Enter your tracking number to see shipment progress.',
			'delete_data' => 0,
		) );

		Capabilities::add();
	}

	public static function deactivate() {}

	public static function uninstall() {
		$settings = get_option( 'workparcel_settings', array() );
		if ( ! empty( $settings['delete_data'] ) ) {
			global $wpdb;
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}workparcel_tracking_events" );
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}workparcel_shipments" );
			delete_option( 'workparcel_settings' );
			delete_option( 'workparcel_db_version' );
		}
	}
}
