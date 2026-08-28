<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Tracking {
	public static function add_event( $shipment_id, $status, $location = '', $description = '', $event_date = '' ) {
		global $wpdb;
		if ( ! Shipment::get( $shipment_id ) ) return new \WP_Error( 'invalid_shipment', __( 'Shipment not found.', 'workparcel' ) );
		if ( ! isset( Shipment::statuses()[ $status ] ) ) $status = 'pending';

		$event_date = $event_date ? sanitize_text_field( $event_date ) : current_time( 'mysql' );
		$result = $wpdb->insert(
			$wpdb->prefix . 'workparcel_tracking_events',
			array(
				'shipment_id' => absint( $shipment_id ),
				'status' => sanitize_key( $status ),
				'location' => sanitize_text_field( $location ),
				'description' => sanitize_textarea_field( $description ),
				'event_date' => $event_date,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d','%s','%s','%s','%s','%s' )
		);
		if ( false === $result ) return new \WP_Error( 'event_failed', __( 'Could not add tracking event.', 'workparcel' ) );

		$event_id = (int) $wpdb->insert_id;
		/**
		 * Fires after a tracking event is recorded.
		 * Useful for sending custom notifications or syncing with other plugins (e.g. WooCommerce).
		 *
		 * @param int    $shipment_id Shipment ID.
		 * @param string $status      Event status key.
		 * @param string $location    Event location.
		 * @param string $description Event description.
		 * @param string $event_date  Event datetime (MySQL format).
		 */
		do_action( 'workparcel_tracking_event_added', $shipment_id, $status, $location, $description, $event_date );
		return $event_id;
	}

	public static function events( $shipment_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}workparcel_tracking_events WHERE shipment_id = %d ORDER BY event_date DESC, id DESC",
			absint( $shipment_id )
		) );
	}
}
