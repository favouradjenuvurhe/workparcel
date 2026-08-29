<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Customer {

	/** Unambiguous charset: no 0/O, 1/I/L, so IDs are easy to read and re-type by hand. */
	const SCAN_ID_CHARS = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
	const SCAN_ID_LENGTH = 11;

	public static function types() {
		return apply_filters( 'workparcel_customer_types', array(
			'driver' => __( 'Driver', 'workparcel' ),
			'customer' => __( 'Customer / Receiver', 'workparcel' ),
		) );
	}

	public static function generate_scan_id() {
		global $wpdb;
		$chars = self::SCAN_ID_CHARS;
		$max = strlen( $chars ) - 1;

		do {
			$id = '';
			for ( $i = 0; $i < self::SCAN_ID_LENGTH; $i++ ) {
				$id .= $chars[ random_int( 0, $max ) ];
			}
			$exists = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}workparcel_customers WHERE scan_id = %s LIMIT 1",
				$id
			) );
		} while ( $exists );

		return $id;
	}

	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}workparcel_customers WHERE id = %d",
			$id
		) );
	}

	public static function find_by_scan_id( $scan_id ) {
		global $wpdb;
		$scan_id = strtoupper( sanitize_text_field( $scan_id ) );
		if ( ! $scan_id ) return null;
		$customer = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}workparcel_customers WHERE scan_id = %s AND status = 'active'",
			$scan_id
		) );
		return $customer;
	}

	public static function all( $args = array() ) {
		global $wpdb;
		$page = max( 1, absint( $args['page'] ?? 1 ) );
		$per_page = min( 100, max( 1, absint( $args['per_page'] ?? 20 ) ) );
		$offset = ( $page - 1 ) * $per_page;
		$where = 'WHERE 1=1';
		$params = array();

		if ( ! empty( $args['type'] ) && isset( self::types()[ $args['type'] ] ) ) {
			$where .= ' AND type = %s';
			$params[] = $args['type'];
		}
		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where .= ' AND (name LIKE %s OR scan_id LIKE %s OR email LIKE %s OR phone LIKE %s)';
			$params[] = $search; $params[] = $search; $params[] = $search; $params[] = $search;
		}

		$count_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}workparcel_customers $where";
		$total = (int) $wpdb->get_var( $params ? $wpdb->prepare( $count_sql, $params ) : $count_sql );

		$sql = "SELECT * FROM {$wpdb->prefix}workparcel_customers $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$list_params = array_merge( $params, array( $per_page, $offset ) );
		$items = $wpdb->get_results( $wpdb->prepare( $sql, $list_params ) );

		return array( 'items' => $items, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
	}

	public static function save( $data, $id = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'workparcel_customers';
		$types = self::types();

		$row = array(
			'name' => sanitize_text_field( $data['name'] ?? '' ),
			'type' => isset( $types[ $data['type'] ?? '' ] ) ? $data['type'] : 'customer',
			'email' => sanitize_email( $data['email'] ?? '' ),
			'phone' => sanitize_text_field( $data['phone'] ?? '' ),
			'notes' => sanitize_textarea_field( $data['notes'] ?? '' ),
			'status' => in_array( $data['status'] ?? 'active', array( 'active', 'inactive' ), true ) ? $data['status'] : 'active',
			'updated_at' => current_time( 'mysql' ),
		);
		$formats = array( '%s','%s','%s','%s','%s','%s','%s' );

		if ( $id ) {
			$result = $wpdb->update( $table, $row, array( 'id' => $id ), $formats, array( '%d' ) );
			if ( false === $result ) return new \WP_Error( 'save_failed', __( 'Could not update customer.', 'workparcel' ) );
			return $id;
		}

		$row['scan_id'] = self::generate_scan_id();
		$row['created_at'] = current_time( 'mysql' );
		$formats[] = '%s';
		$formats[] = '%s';
		$result = $wpdb->insert( $table, $row, $formats );
		if ( false === $result ) return new \WP_Error( 'save_failed', __( 'Could not create customer.', 'workparcel' ) );

		$new_id = (int) $wpdb->insert_id;
		do_action( 'workparcel_customer_created', $new_id, $row );
		return $new_id;
	}

	public static function delete( $id ) {
		global $wpdb;
		return $wpdb->delete( $wpdb->prefix . 'workparcel_customers', array( 'id' => absint( $id ) ), array( '%d' ) );
	}
}
