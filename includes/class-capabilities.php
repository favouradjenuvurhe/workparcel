<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Capabilities {
	public static function add() {
		$role = get_role( 'administrator' );
		if ( ! $role ) return;

		foreach ( array(
			'workparcel_manage_shipments',
			'workparcel_view_shipments',
			'workparcel_create_shipments',
			'workparcel_edit_shipments',
			'workparcel_delete_shipments',
			'workparcel_manage_settings',
		) as $cap ) {
			$role->add_cap( $cap );
		}
	}

	public static function remove() {
		$role = get_role( 'administrator' );
		if ( ! $role ) return;
		foreach ( array(
			'workparcel_manage_shipments',
			'workparcel_view_shipments',
			'workparcel_create_shipments',
			'workparcel_edit_shipments',
			'workparcel_delete_shipments',
			'workparcel_manage_settings',
		) as $cap ) {
			$role->remove_cap( $cap );
		}
	}
}
