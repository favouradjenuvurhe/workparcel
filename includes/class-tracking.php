<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Tracking {

	/**
	 * Turns whatever the admin form / API sent (e.g. "2026-09-20T10:30") into a valid MySQL datetime
	 * in the site's timezone. Falls back to "now" if it can't be parsed, so a bad value never
	 * makes the insert fail or store 0000-00-00.
	 */
	public static function normalize_date( $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) return current_time( 'mysql' );
		$dt = date_create( str_replace( 'T', ' ', $value ), wp_timezone() );
		return $dt ? $dt->format( 'Y-m-d H:i:s' ) : current_time( 'mysql' );
	}

	public static function add_event( $shipment_id, $status, $location = '', $description = '', $event_date = '', $actor = '' ) {
		global $wpdb;
		if ( ! Shipment::get( $shipment_id ) ) return new \WP_Error( 'invalid_shipment', __( 'Shipment not found.', 'workparcel' ) );
		if ( ! isset( Shipment::statuses()[ $status ] ) ) $status = 'pending';

		$event_date = self::normalize_date( $event_date );
		$result = $wpdb->insert(
			$wpdb->prefix . 'workparcel_tracking_events',
			array(
				'shipment_id' => absint( $shipment_id ),
				'status' => sanitize_key( $status ),
				'location' => sanitize_text_field( $location ),
				'description' => sanitize_textarea_field( $description ),
				'actor' => sanitize_text_field( $actor ),
				'event_date' => $event_date,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d','%s','%s','%s','%s','%s','%s' )
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
		 * @param string $event_date  Event datetime (MySQL format, site timezone).
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

	/**
	 * Public-safe view of a shipment's history (used by the public REST lookup).
	 * Deliberately leaves out the internal `actor` (who scanned/updated it) and row IDs.
	 */
	public static function public_events( $shipment_id ) {
		$statuses = Shipment::statuses();
		$out = array();
		foreach ( self::events( $shipment_id ) as $event ) {
			$out[] = array(
				'status' => $event->status,
				'status_label' => $statuses[ $event->status ] ?? $event->status,
				'location' => $event->location,
				'description' => $event->description,
				'event_date' => $event->event_date,
			);
		}
		return $out;
	}
}
