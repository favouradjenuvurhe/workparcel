<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class RestApi {

	const NAMESPACE_V1 = 'workparcel/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	private static function enabled() {
		$settings = Settings::get();
		return ! empty( $settings['enable_rest_api'] );
	}

	private static function disabled_error() {
		return new \WP_Error( 'workparcel_rest_disabled', __( 'The Workparcel REST API is disabled. Enable it under Workparcel → Settings → Scan & API.', 'workparcel' ), array( 'status' => 403 ) );
	}

	public static function register_routes() {
		register_rest_route( self::NAMESPACE_V1, '/shipments', array(
			array(
				'methods' => 'GET',
				'callback' => array( __CLASS__, 'get_shipments' ),
				'permission_callback' => function () { return self::check_cap( 'workparcel_view_shipments' ); },
			),
			array(
				'methods' => 'POST',
				'callback' => array( __CLASS__, 'create_shipment' ),
				'permission_callback' => function () { return self::check_cap( 'workparcel_create_shipments' ); },
			),
		) );

		register_rest_route( self::NAMESPACE_V1, '/shipments/(?P<id>\d+)', array(
			array(
				'methods' => 'GET',
				'callback' => array( __CLASS__, 'get_shipment' ),
				'permission_callback' => function () { return self::check_cap( 'workparcel_view_shipments' ); },
			),
			array(
				'methods' => array( 'POST', 'PUT', 'PATCH' ),
				'callback' => array( __CLASS__, 'update_shipment' ),
				'permission_callback' => function () { return self::check_cap( 'workparcel_edit_shipments' ); },
			),
			array(
				'methods' => 'DELETE',
				'callback' => array( __CLASS__, 'delete_shipment' ),
				'permission_callback' => function () { return self::check_cap( 'workparcel_delete_shipments' ); },
			),
		) );

		register_rest_route( self::NAMESPACE_V1, '/shipments/(?P<id>\d+)/status', array(
			'methods' => 'POST',
			'callback' => array( __CLASS__, 'update_status' ),
			'permission_callback' => function () { return self::check_cap( 'workparcel_edit_shipments' ); },
		) );

		register_rest_route( self::NAMESPACE_V1, '/shipments/(?P<id>\d+)/events', array(
			'methods' => 'GET',
			'callback' => array( __CLASS__, 'get_events' ),
			'permission_callback' => function () { return self::check_cap( 'workparcel_view_shipments' ); },
		) );

		// Public, read-only — mirrors what the [workparcel_tracking] shortcode already exposes.
		register_rest_route( self::NAMESPACE_V1, '/track/(?P<tracking_number>[A-Za-z0-9\-]+)', array(
			'methods' => 'GET',
			'callback' => array( __CLASS__, 'track' ),
			'permission_callback' => function () { return self::enabled() ? true : self::disabled_error(); },
		) );

		register_rest_route( self::NAMESPACE_V1, '/customers', array(
			array(
				'methods' => 'GET',
				'callback' => array( __CLASS__, 'get_customers' ),
				'permission_callback' => function () { return self::check_cap( 'workparcel_manage_customers' ); },
			),
			array(
				'methods' => 'POST',
				'callback' => array( __CLASS__, 'create_customer' ),
				'permission_callback' => function () { return self::check_cap( 'workparcel_manage_customers' ); },
			),
		) );
	}

	private static function check_cap( $cap ) {
		if ( ! self::enabled() ) return self::disabled_error();
		if ( ! current_user_can( $cap ) ) {
			return new \WP_Error( 'workparcel_rest_forbidden', __( 'You do not have permission to do this.', 'workparcel' ), array( 'status' => 403 ) );
		}
		return true;
	}

	private static function shipment_response( $shipment ) {
		$data = (array) $shipment;
		$data['status_label'] = Shipment::statuses()[ $shipment->status ] ?? $shipment->status;
		return $data;
	}

	public static function get_shipments( \WP_REST_Request $req ) {
		$result = Shipment::all( array(
			'page' => max( 1, (int) $req->get_param( 'page' ) ),
			'per_page' => min( 100, max( 1, (int) ( $req->get_param( 'per_page' ) ?: 20 ) ) ),
			'search' => (string) $req->get_param( 'search' ),
			'status' => (string) $req->get_param( 'status' ),
		) );
		$result['items'] = array_map( array( __CLASS__, 'shipment_response' ), $result['items'] );
		return rest_ensure_response( $result );
	}

	public static function get_shipment( \WP_REST_Request $req ) {
		$shipment = Shipment::get( (int) $req->get_param( 'id' ) );
		if ( ! $shipment ) return new \WP_Error( 'not_found', __( 'Shipment not found.', 'workparcel' ), array( 'status' => 404 ) );
		return rest_ensure_response( self::shipment_response( $shipment ) );
	}

	public static function create_shipment( \WP_REST_Request $req ) {
		$data = self::sanitize_shipment_params( $req );
		$id = Shipment::save( $data, 0 );
		if ( is_wp_error( $id ) ) return $id;
		return rest_ensure_response( self::shipment_response( Shipment::get( $id ) ) );
	}

	public static function update_shipment( \WP_REST_Request $req ) {
		$id = (int) $req->get_param( 'id' );
		if ( ! Shipment::get( $id ) ) return new \WP_Error( 'not_found', __( 'Shipment not found.', 'workparcel' ), array( 'status' => 404 ) );
		$data = self::sanitize_shipment_params( $req );
		$result = Shipment::save( $data, $id );
		if ( is_wp_error( $result ) ) return $result;
		return rest_ensure_response( self::shipment_response( Shipment::get( $id ) ) );
	}

	public static function delete_shipment( \WP_REST_Request $req ) {
		$id = (int) $req->get_param( 'id' );
		if ( ! Shipment::get( $id ) ) return new \WP_Error( 'not_found', __( 'Shipment not found.', 'workparcel' ), array( 'status' => 404 ) );
		Shipment::delete( $id );
		return rest_ensure_response( array( 'deleted' => true ) );
	}

	public static function update_status( \WP_REST_Request $req ) {
		$id = (int) $req->get_param( 'id' );
		$status = sanitize_key( (string) $req->get_param( 'status' ) );
		$location = (string) $req->get_param( 'location' );
		$note = (string) $req->get_param( 'note' );
		$result = Shipment::update_status( $id, $status, $location, $note );
		if ( is_wp_error( $result ) ) return $result;
		return rest_ensure_response( self::shipment_response( Shipment::get( $id ) ) );
	}

	public static function get_events( \WP_REST_Request $req ) {
		$id = (int) $req->get_param( 'id' );
		if ( ! Shipment::get( $id ) ) return new \WP_Error( 'not_found', __( 'Shipment not found.', 'workparcel' ), array( 'status' => 404 ) );
		return rest_ensure_response( Tracking::events( $id ) );
	}

	public static function track( \WP_REST_Request $req ) {
		if ( ! self::enabled() ) return self::disabled_error();
		$tracking_number = strtoupper( sanitize_text_field( (string) $req->get_param( 'tracking_number' ) ) );
		$shipment = Shipment::find_by_tracking( $tracking_number );
		if ( ! $shipment ) return new \WP_Error( 'not_found', __( 'Shipment not found.', 'workparcel' ), array( 'status' => 404 ) );

		// Public-safe subset only — no sender/receiver contact details, matching the tracking shortcode.
		return rest_ensure_response( array(
			'tracking_number' => $shipment->tracking_number,
			'status' => $shipment->status,
			'status_label' => Shipment::statuses()[ $shipment->status ] ?? $shipment->status,
			'origin' => $shipment->origin,
			'destination' => $shipment->destination,
			'estimated_delivery' => $shipment->estimated_delivery,
			'events' => Tracking::events( $shipment->id ),
		) );
	}

	public static function get_customers( \WP_REST_Request $req ) {
		$result = Customer::all( array(
			'page' => max( 1, (int) $req->get_param( 'page' ) ),
			'per_page' => min( 100, max( 1, (int) ( $req->get_param( 'per_page' ) ?: 20 ) ) ),
			'search' => (string) $req->get_param( 'search' ),
			'type' => (string) $req->get_param( 'type' ),
		) );
		return rest_ensure_response( $result );
	}

	public static function create_customer( \WP_REST_Request $req ) {
		$data = array(
			'name' => sanitize_text_field( (string) $req->get_param( 'name' ) ),
			'type' => sanitize_key( (string) $req->get_param( 'type' ) ),
			'email' => sanitize_email( (string) $req->get_param( 'email' ) ),
			'phone' => sanitize_text_field( (string) $req->get_param( 'phone' ) ),
			'notes' => sanitize_textarea_field( (string) $req->get_param( 'notes' ) ),
		);
		$id = Customer::save( $data, 0 );
		if ( is_wp_error( $id ) ) return $id;
		return rest_ensure_response( Customer::get( $id ) );
	}

	private static function sanitize_shipment_params( \WP_REST_Request $req ) {
		$fields = array(
			'tracking_number', 'reference', 'title', 'sender_name', 'sender_phone', 'origin',
			'destination', 'description', 'parcel_type', 'status', 'estimated_delivery', 'container_no',
		);
		$data = array();
		foreach ( $fields as $field ) {
			if ( null !== $req->get_param( $field ) ) $data[ $field ] = sanitize_text_field( (string) $req->get_param( $field ) );
		}
		foreach ( array( 'sender_email', 'receiver_email' ) as $field ) {
			if ( null !== $req->get_param( $field ) ) $data[ $field ] = sanitize_email( (string) $req->get_param( $field ) );
		}
		foreach ( array( 'sender_address', 'receiver_address' ) as $field ) {
			if ( null !== $req->get_param( $field ) ) $data[ $field ] = sanitize_textarea_field( (string) $req->get_param( $field ) );
		}
		if ( null !== $req->get_param( 'receiver_name' ) ) $data['receiver_name'] = sanitize_text_field( (string) $req->get_param( 'receiver_name' ) );
		if ( null !== $req->get_param( 'receiver_phone' ) ) $data['receiver_phone'] = sanitize_text_field( (string) $req->get_param( 'receiver_phone' ) );
		if ( null !== $req->get_param( 'weight' ) ) $data['weight'] = (float) $req->get_param( 'weight' );
		if ( null !== $req->get_param( 'quantity' ) ) $data['quantity'] = (int) $req->get_param( 'quantity' );
		if ( null !== $req->get_param( 'shipping_fee' ) ) $data['shipping_fee'] = (float) $req->get_param( 'shipping_fee' );
		if ( null !== $req->get_param( 'customer_id' ) ) $data['customer_id'] = (int) $req->get_param( 'customer_id' );
		return $data;
	}
}
