<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shipment {
	public static function statuses() {
		/**
		 * Filter the list of shipment statuses (key => label).
		 * Lets other plugins (e.g. a WooCommerce bridge, a custom carrier integration) add or relabel statuses.
		 */
		return apply_filters( 'workparcel_statuses', array(
			'pending'          => __( 'Pending', 'workparcel' ),
			'processing'       => __( 'Processing', 'workparcel' ),
			'picked_up'        => __( 'Picked Up', 'workparcel' ),
			'in_transit'       => __( 'In Transit', 'workparcel' ),
			'at_facility'      => __( 'At Facility', 'workparcel' ),
			'out_for_delivery' => __( 'Out for Delivery', 'workparcel' ),
			'delivered'        => __( 'Delivered', 'workparcel' ),
			'failed_delivery'  => __( 'Failed Delivery', 'workparcel' ),
			'cancelled'        => __( 'Cancelled', 'workparcel' ),
		) );
	}

	public static function generate_tracking_number() {
		global $wpdb;
		$settings = Settings::get();
		$prefix = preg_replace( '/[^A-Z0-9-]/i', '', (string) $settings['tracking_prefix'] );
		$prefix = $prefix ?: 'WP';

		do {
			$number = $prefix . '-' . strtoupper( wp_generate_password( 8, false, false ) );
			$exists = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}workparcel_shipments WHERE tracking_number = %s LIMIT 1",
				$number
			) );
		} while ( $exists );

		return $number;
	}

	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}workparcel_shipments WHERE id = %d",
			$id
		) );
	}

	public static function find_by_tracking( $tracking ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}workparcel_shipments WHERE tracking_number = %s",
			strtoupper( sanitize_text_field( $tracking ) )
		) );
	}

	public static function all( $args = array() ) {
		global $wpdb;
		$page = max( 1, absint( $args['page'] ?? 1 ) );
		$per_page = min( 100, max( 1, absint( $args['per_page'] ?? 20 ) ) );
		$offset = ( $page - 1 ) * $per_page;
		$where = 'WHERE 1=1';
		$params = array();

		if ( ! empty( $args['status'] ) && isset( self::statuses()[ $args['status'] ] ) ) {
			$where .= ' AND status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where .= ' AND (tracking_number LIKE %s OR title LIKE %s OR receiver_name LIKE %s)';
			$params[] = $search; $params[] = $search; $params[] = $search;
		}

		$count_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}workparcel_shipments $where";
		$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );

		$sql = "SELECT * FROM {$wpdb->prefix}workparcel_shipments $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$params[] = $per_page; $params[] = $offset;
		$items = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		return array( 'items' => $items, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
	}

	public static function save( $data, $id = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'workparcel_shipments';
		$statuses = self::statuses();
		$status = isset( $statuses[ $data['status'] ] ) ? $data['status'] : 'pending';

		$customer_id = absint( $data['customer_id'] ?? 0 );
		$driver_name = sanitize_text_field( $data['driver_name'] ?? '' );
		if ( $customer_id ) {
			$customer = Customer::get( $customer_id );
			$driver_name = $customer ? $customer->name : '';
			if ( ! $customer ) $customer_id = 0;
		}

		$row = array(
			'tracking_number' => $data['tracking_number'] ?: self::generate_tracking_number(),
			'reference' => sanitize_text_field( $data['reference'] ?? '' ),
			'title' => sanitize_text_field( $data['title'] ?? '' ),
			'sender_name' => sanitize_text_field( $data['sender_name'] ?? '' ),
			'sender_email' => sanitize_email( $data['sender_email'] ?? '' ),
			'sender_phone' => sanitize_text_field( $data['sender_phone'] ?? '' ),
			'sender_address' => sanitize_textarea_field( $data['sender_address'] ?? '' ),
			'receiver_name' => sanitize_text_field( $data['receiver_name'] ?? '' ),
			'receiver_email' => sanitize_email( $data['receiver_email'] ?? '' ),
			'receiver_phone' => sanitize_text_field( $data['receiver_phone'] ?? '' ),
			'receiver_address' => sanitize_textarea_field( $data['receiver_address'] ?? '' ),
			'origin' => sanitize_text_field( $data['origin'] ?? '' ),
			'destination' => sanitize_text_field( $data['destination'] ?? '' ),
			'description' => sanitize_textarea_field( $data['description'] ?? '' ),
			'parcel_type' => sanitize_text_field( $data['parcel_type'] ?? '' ),
			'weight' => max( 0, (float) ( $data['weight'] ?? 0 ) ),
			'quantity' => max( 1, absint( $data['quantity'] ?? 1 ) ),
			'shipping_fee' => max( 0, (float) ( $data['shipping_fee'] ?? 0 ) ),
			'status' => $status,
			'estimated_delivery' => ! empty( $data['estimated_delivery'] ) ? sanitize_text_field( $data['estimated_delivery'] ) : null,
			'container_no' => sanitize_text_field( $data['container_no'] ?? '' ),
			'driver_name' => $driver_name,
			'customer_id' => $customer_id,
			'photo' => ! empty( $data['photo'] ) ? esc_url_raw( $data['photo'] ) : '',
			'pod_signature' => ! empty( $data['pod_signature'] ) ? esc_url_raw( $data['pod_signature'] ) : '',
			'pod_photo' => ! empty( $data['pod_photo'] ) ? esc_url_raw( $data['pod_photo'] ) : '',
			'updated_at' => current_time( 'mysql' ),
		);

		$formats = array( '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%f','%d','%f','%s','%s','%s','%s','%d','%s','%s','%s' );

		if ( $id ) {
			$old = self::get( $id );
			$result = $wpdb->update( $table, $row, array( 'id' => $id ), $formats, array( '%d' ) );
			if ( false === $result ) return new \WP_Error( 'save_failed', __( 'Could not update shipment.', 'workparcel' ) );
			if ( $old && $old->status !== $status ) {
				Tracking::add_event( $id, $status, '', sprintf( __( 'Shipment status changed to %s.', 'workparcel' ), $statuses[ $status ] ) );
				/**
				 * Fires when a shipment's status changes.
				 *
				 * @param int    $id         Shipment ID.
				 * @param string $old_status Previous status key.
				 * @param string $new_status New status key.
				 */
				do_action( 'workparcel_status_changed', $id, $old->status, $status );
			}
			/**
			 * Fires after a shipment is updated.
			 *
			 * @param int   $id  Shipment ID.
			 * @param array $row Saved shipment data.
			 */
			do_action( 'workparcel_shipment_updated', $id, $row );
			return $id;
		}

		$row['created_at'] = current_time( 'mysql' );
		$formats[] = '%s';
		$result = $wpdb->insert( $table, $row, $formats );
		if ( false === $result ) return new \WP_Error( 'save_failed', __( 'Could not create shipment.', 'workparcel' ) );

		$new_id = (int) $wpdb->insert_id;
		Tracking::add_event( $new_id, $status, '', __( 'Shipment created.', 'workparcel' ) );
		/**
		 * Fires after a new shipment is created.
		 *
		 * @param int   $new_id Shipment ID.
		 * @param array $row    Saved shipment data.
		 */
		do_action( 'workparcel_shipment_created', $new_id, $row );
		return $new_id;
	}

	public static function delete( $id ) {
		global $wpdb;
		$id = absint( $id );
		$wpdb->delete( $wpdb->prefix . 'workparcel_tracking_events', array( 'shipment_id' => $id ), array( '%d' ) );
		return $wpdb->delete( $wpdb->prefix . 'workparcel_shipments', array( 'id' => $id ), array( '%d' ) );
	}

	/**
	 * Change a shipment's status and log it as a tracking event in one step.
	 * Shared by the manual "Add Tracking Event" form and the barcode scan tool.
	 */
	public static function update_status( $id, $status, $location = '', $note = '', $event_date = '' ) {
		global $wpdb;
		$id = absint( $id );
		$statuses = self::statuses();
		if ( ! isset( $statuses[ $status ] ) ) return new \WP_Error( 'invalid_status', __( 'Invalid status.', 'workparcel' ) );

		$existing = self::get( $id );
		if ( ! $existing ) return new \WP_Error( 'not_found', __( 'Shipment not found.', 'workparcel' ) );

		$old_status = $existing->status;
		$description = $note ?: sprintf( __( 'Shipment status changed to %s.', 'workparcel' ), $statuses[ $status ] );
		Tracking::add_event( $id, $status, $location, $description, $event_date );

		$wpdb->update( $wpdb->prefix . 'workparcel_shipments', array(
			'status' => $status,
			'updated_at' => current_time( 'mysql' ),
		), array( 'id' => $id ), array( '%s','%s' ), array( '%d' ) );

		if ( $old_status !== $status ) {
			do_action( 'workparcel_status_changed', $id, $old_status, $status );
		}
		return $id;
	}

	/** Assigns a shipment to a registered driver/customer (by Customer record ID) for delivery/pickup. */
	public static function assign_driver( $id, $customer_id ) {
		global $wpdb;
		$id = absint( $id );
		$customer_id = absint( $customer_id );

		$existing = self::get( $id );
		if ( ! $existing ) return new \WP_Error( 'not_found', __( 'Shipment not found.', 'workparcel' ) );

		$customer = Customer::get( $customer_id );
		if ( ! $customer ) return new \WP_Error( 'customer_not_found', __( 'That driver/customer is not registered. Register them under Workparcel → Customers first.', 'workparcel' ) );

		$wpdb->update( $wpdb->prefix . 'workparcel_shipments', array(
			'driver_name' => $customer->name,
			'customer_id' => $customer_id,
			'updated_at' => current_time( 'mysql' ),
		), array( 'id' => $id ), array( '%s','%d','%s' ), array( '%d' ) );

		Tracking::add_event( $id, $existing->status, '', sprintf( __( 'Assigned to %s.', 'workparcel' ), $customer->name ) );

		/**
		 * Fires after a shipment is assigned to a registered driver/customer.
		 *
		 * @param int $id          Shipment ID.
		 * @param int $customer_id ID of the Customer record it was assigned to.
		 */
		do_action( 'workparcel_shipment_assigned', $id, $customer_id );
		return $id;
	}
}
