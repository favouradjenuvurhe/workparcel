<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Plugin {
	private static $instance;

	public static function instance() {
		if ( ! self::$instance ) self::$instance = new self();
		return self::$instance;
	}

	public function init() {
		add_action( 'admin_init', array( 'Workparcel\\Database', 'maybe_upgrade' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( 'Workparcel\\Settings', 'register' ) );
		add_action( 'admin_post_workparcel_save_shipment', array( $this, 'save_shipment' ) );
		add_action( 'admin_post_workparcel_delete_shipment', array( $this, 'delete_shipment' ) );
		add_action( 'admin_post_workparcel_add_event', array( $this, 'add_event' ) );
		add_action( 'wp_ajax_workparcel_scan', array( $this, 'ajax_scan' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'public_assets' ) );
		add_shortcode( 'workparcel_tracking', array( 'Workparcel\\Shortcodes', 'tracking' ) );
	}

	public function admin_menu() {
		add_menu_page( 'Workparcel', 'Workparcel', 'workparcel_view_shipments', 'workparcel', array( $this, 'dashboard' ), $this->menu_icon(), 26 );
		add_submenu_page( 'workparcel', 'Dashboard', 'Dashboard', 'workparcel_view_shipments', 'workparcel', array( $this, 'dashboard' ) );
		add_submenu_page( 'workparcel', 'Shipments', 'Shipments', 'workparcel_view_shipments', 'workparcel-shipments', array( $this, 'shipments' ) );
		add_submenu_page( 'workparcel', 'Add Shipment', 'Add Shipment', 'workparcel_create_shipments', 'workparcel-add', array( $this, 'edit_shipment' ) );
		add_submenu_page( 'workparcel', 'Scan', 'Scan', 'workparcel_edit_shipments', 'workparcel-scan', array( $this, 'scan_page' ) );
		add_submenu_page( 'workparcel', 'Settings', 'Settings', 'workparcel_manage_settings', 'workparcel-settings', array( 'Workparcel\\Settings', 'page' ) );
		// Hidden (not shown in the menu): reached only via a "View Invoice" link.
		add_submenu_page( null, 'Invoice', 'Invoice', 'workparcel_view_shipments', 'workparcel-invoice', array( $this, 'invoice_page' ) );
	}

	public function admin_assets( $hook ) {
		if ( strpos( $hook, 'workparcel' ) === false ) return;
		wp_enqueue_style( 'workparcel-admin', WORKPARCEL_URL . 'admin/css/admin.css', array(), WORKPARCEL_VERSION );
		$accent = sanitize_hex_color( Settings::get()['accent_color'] ) ?: '#2563eb';
		wp_add_inline_style( 'workparcel-admin', ':root{--wp-workparcel-accent: ' . $accent . ';}' );
		wp_enqueue_script( 'workparcel-admin', WORKPARCEL_URL . 'admin/js/admin.js', array(), WORKPARCEL_VERSION, true );

		if ( strpos( $hook, 'workparcel-settings' ) !== false ) {
			wp_enqueue_media();
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'workparcel-media-field', WORKPARCEL_URL . 'admin/js/media-field.js', array( 'jquery' ), WORKPARCEL_VERSION, true );
			wp_enqueue_script( 'workparcel-settings', WORKPARCEL_URL . 'admin/js/settings.js', array( 'jquery', 'wp-color-picker', 'workparcel-media-field' ), WORKPARCEL_VERSION, true );
		}

		if ( strpos( $hook, 'workparcel-add' ) !== false ) {
			wp_enqueue_media();
			wp_enqueue_script( 'workparcel-media-field', WORKPARCEL_URL . 'admin/js/media-field.js', array( 'jquery' ), WORKPARCEL_VERSION, true );
		}

		if ( strpos( $hook, 'workparcel-scan' ) !== false ) {
			wp_enqueue_script( 'workparcel-scan', WORKPARCEL_URL . 'admin/js/scan.js', array( 'jquery' ), WORKPARCEL_VERSION, true );
			wp_localize_script( 'workparcel-scan', 'workparcelScan', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'workparcel_scan' ),
				'i18n' => array(
					'currentStatus' => __( 'currently', 'workparcel' ),
					'newStatus' => __( 'New status', 'workparcel' ),
					'location' => __( 'Location', 'workparcel' ),
					'locationPlaceholder' => __( 'Optional', 'workparcel' ),
					'updateStatus' => __( 'Update Status', 'workparcel' ),
					'driverName' => __( 'Driver / customer', 'workparcel' ),
					'driverPlaceholder' => __( 'Name', 'workparcel' ),
					'assign' => __( 'Assign', 'workparcel' ),
					'editShipment' => __( 'Edit shipment', 'workparcel' ),
					'created' => __( 'created', 'workparcel' ),
				),
			) );
		}

		if ( strpos( $hook, 'workparcel-invoice' ) !== false ) {
			wp_enqueue_style( 'workparcel-invoice', WORKPARCEL_URL . 'admin/css/invoice.css', array(), WORKPARCEL_VERSION );
			wp_add_inline_style( 'workparcel-invoice', '.wp-workparcel-invoice{--wp-workparcel-accent: ' . $accent . ';}' );
		}
	}

	/**
	 * Base64-encoded SVG data URI for the WordPress admin menu icon.
	 * Core recolors black-filled SVG icons to match the active admin color scheme.
	 */
	private function menu_icon() {
		$svg = file_get_contents( WORKPARCEL_DIR . 'admin/images/menu-icon.svg' );
		if ( false === $svg ) return 'dashicons-location-alt';
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	public function public_assets() {
		// The stylesheet is also enqueued by the shortcode to avoid loading it site-wide.
	}

	private function guard( $cap ) {
		if ( ! current_user_can( $cap ) ) wp_die( esc_html__( 'You do not have permission to perform this action.', 'workparcel' ) );
	}

	public function dashboard() {
		$this->guard( 'workparcel_view_shipments' );
		global $wpdb;
		$table = $wpdb->prefix . 'workparcel_shipments';
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		$stats = array();
		foreach ( Shipment::statuses() as $key => $label ) {
			$stats[ $key ] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", $key ) );
		}
		$recent = Shipment::all( array( 'page' => 1, 'per_page' => 5 ) );
		include WORKPARCEL_DIR . 'admin/views/dashboard.php';
	}

	public function shipments() {
		$this->guard( 'workparcel_view_shipments' );
		$result = Shipment::all( array(
			'page' => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1,
			'per_page' => 20,
			'search' => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
			'status' => isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '',
		) );
		include WORKPARCEL_DIR . 'admin/views/shipments.php';
	}

	public function edit_shipment() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$this->guard( $id ? 'workparcel_edit_shipments' : 'workparcel_create_shipments' );
		$shipment = $id ? Shipment::get( $id ) : null;
		$events = $id ? Tracking::events( $id ) : array();
		include WORKPARCEL_DIR . 'admin/views/shipment-edit.php';
	}

	public function save_shipment() {
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$this->guard( $id ? 'workparcel_edit_shipments' : 'workparcel_create_shipments' );
		check_admin_referer( 'workparcel_save_shipment' );
		$data = array(
			'tracking_number' => isset( $_POST['tracking_number'] ) ? sanitize_text_field( wp_unslash( $_POST['tracking_number'] ) ) : '',
			'reference' => isset( $_POST['reference'] ) ? sanitize_text_field( wp_unslash( $_POST['reference'] ) ) : '',
			'title' => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
			'sender_name' => isset( $_POST['sender_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sender_name'] ) ) : '',
			'sender_email' => isset( $_POST['sender_email'] ) ? sanitize_email( wp_unslash( $_POST['sender_email'] ) ) : '',
			'sender_phone' => isset( $_POST['sender_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['sender_phone'] ) ) : '',
			'sender_address' => isset( $_POST['sender_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sender_address'] ) ) : '',
			'receiver_name' => isset( $_POST['receiver_name'] ) ? sanitize_text_field( wp_unslash( $_POST['receiver_name'] ) ) : '',
			'receiver_email' => isset( $_POST['receiver_email'] ) ? sanitize_email( wp_unslash( $_POST['receiver_email'] ) ) : '',
			'receiver_phone' => isset( $_POST['receiver_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['receiver_phone'] ) ) : '',
			'receiver_address' => isset( $_POST['receiver_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['receiver_address'] ) ) : '',
			'origin' => isset( $_POST['origin'] ) ? sanitize_text_field( wp_unslash( $_POST['origin'] ) ) : '',
			'destination' => isset( $_POST['destination'] ) ? sanitize_text_field( wp_unslash( $_POST['destination'] ) ) : '',
			'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'parcel_type' => isset( $_POST['parcel_type'] ) ? sanitize_text_field( wp_unslash( $_POST['parcel_type'] ) ) : '',
			'weight' => isset( $_POST['weight'] ) ? (float) wp_unslash( $_POST['weight'] ) : 0,
			'quantity' => isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1,
			'shipping_fee' => isset( $_POST['shipping_fee'] ) ? (float) wp_unslash( $_POST['shipping_fee'] ) : 0,
			'status' => isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'pending',
			'estimated_delivery' => isset( $_POST['estimated_delivery'] ) ? sanitize_text_field( wp_unslash( $_POST['estimated_delivery'] ) ) : '',
			'container_no' => isset( $_POST['container_no'] ) ? sanitize_text_field( wp_unslash( $_POST['container_no'] ) ) : '',
			'driver_name' => isset( $_POST['driver_name'] ) ? sanitize_text_field( wp_unslash( $_POST['driver_name'] ) ) : '',
			'photo' => isset( $_POST['photo'] ) ? esc_url_raw( wp_unslash( $_POST['photo'] ) ) : '',
			'pod_signature' => isset( $_POST['pod_signature'] ) ? esc_url_raw( wp_unslash( $_POST['pod_signature'] ) ) : '',
			'pod_photo' => isset( $_POST['pod_photo'] ) ? esc_url_raw( wp_unslash( $_POST['pod_photo'] ) ) : '',
		);
		$result = Shipment::save( $data, $id );
		if ( is_wp_error( $result ) ) wp_die( esc_html( $result->get_error_message() ) );
		wp_safe_redirect( admin_url( 'admin.php?page=workparcel-shipments&message=saved' ) );
		exit;
	}

	public function delete_shipment() {
		$this->guard( 'workparcel_delete_shipments' );
		$id = absint( $_POST['id'] ?? 0 );
		check_admin_referer( 'workparcel_delete_shipment_' . $id );
		Shipment::delete( $id );
		wp_safe_redirect( admin_url( 'admin.php?page=workparcel-shipments&message=deleted' ) );
		exit;
	}

	public function add_event() {
		$this->guard( 'workparcel_edit_shipments' );
		$id = absint( $_POST['shipment_id'] ?? 0 );
		check_admin_referer( 'workparcel_add_event_' . $id );
		$status = sanitize_key( $_POST['status'] ?? 'pending' );
		$location = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$event_date = isset( $_POST['event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) : '';
		Shipment::update_status( $id, $status, $location, $description, $event_date );
		wp_safe_redirect( admin_url( 'admin.php?page=workparcel-add&id=' . $id . '&message=event_added' ) );
		exit;
	}

	public function scan_page() {
		$this->guard( 'workparcel_edit_shipments' );
		include WORKPARCEL_DIR . 'admin/views/scan.php';
	}

	public function invoice_page() {
		$this->guard( 'workparcel_view_shipments' );
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$shipment = Shipment::get( $id );
		if ( ! $shipment ) wp_die( esc_html__( 'Shipment not found.', 'workparcel' ) );
		$events = Tracking::events( $id );
		$settings = Settings::get();
		include WORKPARCEL_DIR . 'admin/views/invoice.php';
	}

	/**
	 * AJAX endpoint for the barcode scan tool. A physical barcode scanner behaves like a
	 * keyboard (types the encoded text, then Enter), so the frontend just needs a focused
	 * text field — this handler does the lookup/create/update/assign work behind it.
	 */
	public function ajax_scan() {
		check_ajax_referer( 'workparcel_scan', 'nonce' );

		$mode = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : '';
		$tracking = isset( $_POST['tracking_number'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['tracking_number'] ) ) ) : '';
		if ( ! $tracking ) wp_send_json_error( array( 'message' => __( 'No tracking number scanned.', 'workparcel' ) ) );

		if ( 'create' === $mode ) {
			if ( ! current_user_can( 'workparcel_create_shipments' ) ) wp_send_json_error( array( 'message' => __( 'You do not have permission to create shipments.', 'workparcel' ) ) );

			$existing = Shipment::find_by_tracking( $tracking );
			if ( $existing ) {
				wp_send_json_error( array(
					'message' => __( 'A shipment with this tracking number already exists.', 'workparcel' ),
					'edit_url' => admin_url( 'admin.php?page=workparcel-add&id=' . $existing->id ),
				) );
			}

			$new_id = Shipment::save( array( 'tracking_number' => $tracking, 'status' => 'pending' ), 0 );
			if ( is_wp_error( $new_id ) ) wp_send_json_error( array( 'message' => $new_id->get_error_message() ) );

			wp_send_json_success( array(
				'message' => __( 'Shipment created.', 'workparcel' ),
				'tracking_number' => $tracking,
				'edit_url' => admin_url( 'admin.php?page=workparcel-add&id=' . $new_id ),
			) );
		}

		if ( 'status' === $mode ) {
			if ( ! current_user_can( 'workparcel_edit_shipments' ) ) wp_send_json_error( array( 'message' => __( 'You do not have permission to edit shipments.', 'workparcel' ) ) );

			$shipment = Shipment::find_by_tracking( $tracking );
			if ( ! $shipment ) wp_send_json_error( array( 'message' => __( 'No shipment found for this tracking number.', 'workparcel' ) ) );

			$new_status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';
			if ( $new_status ) {
				$location = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';
				$result = Shipment::update_status( $shipment->id, $new_status, $location, __( 'Status updated via barcode scan.', 'workparcel' ) );
				if ( is_wp_error( $result ) ) wp_send_json_error( array( 'message' => $result->get_error_message() ) );
				wp_send_json_success( array( 'message' => __( 'Status updated.', 'workparcel' ), 'tracking_number' => $tracking ) );
			}

			$statuses = Shipment::statuses();
			wp_send_json_success( array(
				'lookup' => true,
				'tracking_number' => $shipment->tracking_number,
				'current_status' => $shipment->status,
				'current_status_label' => $statuses[ $shipment->status ] ?? $shipment->status,
				'statuses' => $statuses,
			) );
		}

		if ( 'assign' === $mode ) {
			if ( ! current_user_can( 'workparcel_edit_shipments' ) ) wp_send_json_error( array( 'message' => __( 'You do not have permission to edit shipments.', 'workparcel' ) ) );

			$shipment = Shipment::find_by_tracking( $tracking );
			if ( ! $shipment ) wp_send_json_error( array( 'message' => __( 'No shipment found for this tracking number.', 'workparcel' ) ) );

			$driver = isset( $_POST['driver_name'] ) ? sanitize_text_field( wp_unslash( $_POST['driver_name'] ) ) : '';
			if ( $driver ) {
				$result = Shipment::assign_driver( $shipment->id, $driver );
				if ( is_wp_error( $result ) ) wp_send_json_error( array( 'message' => $result->get_error_message() ) );
				wp_send_json_success( array( 'message' => __( 'Shipment assigned.', 'workparcel' ), 'tracking_number' => $tracking ) );
			}

			wp_send_json_success( array(
				'lookup' => true,
				'tracking_number' => $shipment->tracking_number,
				'current_driver' => $shipment->driver_name,
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Unknown scan mode.', 'workparcel' ) ) );
	}
}
