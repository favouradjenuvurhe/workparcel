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
		add_action( 'admin_post_workparcel_save_customer', array( $this, 'save_customer' ) );
		add_action( 'admin_post_workparcel_delete_customer', array( $this, 'delete_customer' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'public_assets' ) );
		add_shortcode( 'workparcel_tracking', array( 'Workparcel\\Shortcodes', 'tracking' ) );
	}

	public function admin_menu() {
		add_menu_page( 'Workparcel', 'Workparcel', 'workparcel_view_shipments', 'workparcel', array( $this, 'dashboard' ), $this->menu_icon(), 26 );
		add_submenu_page( 'workparcel', 'Dashboard', 'Dashboard', 'workparcel_view_shipments', 'workparcel', array( $this, 'dashboard' ) );
		add_submenu_page( 'workparcel', 'Shipments', 'Shipments', 'workparcel_view_shipments', 'workparcel-shipments', array( $this, 'shipments' ) );
		add_submenu_page( 'workparcel', 'Add Shipment', 'Add Shipment', 'workparcel_create_shipments', 'workparcel-add', array( $this, 'edit_shipment' ) );
		add_submenu_page( 'workparcel', 'Customers', 'Customers', 'workparcel_manage_customers', 'workparcel-customers', array( $this, 'customers' ) );
		add_submenu_page( 'workparcel', 'Settings', 'Settings', 'workparcel_manage_settings', 'workparcel-settings', array( 'Workparcel\\Settings', 'page' ) );
		// Hidden (not shown in the menu): reached only via links.
		add_submenu_page( null, 'Invoice', 'Invoice', 'workparcel_view_shipments', 'workparcel-invoice', array( $this, 'invoice_page' ) );
		add_submenu_page( null, 'Add Customer', 'Add Customer', 'workparcel_manage_customers', 'workparcel-customer-edit', array( $this, 'customer_edit' ) );
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
		// Stylesheets/scripts for the [workparcel_tracking] and [workparcel_scan] shortcodes
		// are enqueued by their own render callbacks, so they only load on pages that use them.
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
		$customers = Customer::all( array( 'per_page' => 100 ) )['items'];
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
			'customer_id' => isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0,
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

	public function invoice_page() {
		$this->guard( 'workparcel_view_shipments' );
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$shipment = Shipment::get( $id );
		if ( ! $shipment ) wp_die( esc_html__( 'Shipment not found.', 'workparcel' ) );
		$events = Tracking::events( $id );
		$settings = Settings::get();
		include WORKPARCEL_DIR . 'admin/views/invoice.php';
	}

	public function customers() {
		$this->guard( 'workparcel_manage_customers' );
		$result = Customer::all( array(
			'page' => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1,
			'per_page' => 20,
			'search' => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
			'type' => isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '',
		) );
		include WORKPARCEL_DIR . 'admin/views/customers.php';
	}

	public function customer_edit() {
		$this->guard( 'workparcel_manage_customers' );
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$customer = $id ? Customer::get( $id ) : null;
		include WORKPARCEL_DIR . 'admin/views/customer-edit.php';
	}

	public function save_customer() {
		$this->guard( 'workparcel_manage_customers' );
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		check_admin_referer( 'workparcel_save_customer' );
		$data = array(
			'name' => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'type' => isset( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : 'customer',
			'email' => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone' => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'notes' => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
			'status' => isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'active',
		);
		$result = Customer::save( $data, $id );
		if ( is_wp_error( $result ) ) wp_die( esc_html( $result->get_error_message() ) );
		wp_safe_redirect( admin_url( 'admin.php?page=workparcel-customers&message=saved' ) );
		exit;
	}

	public function delete_customer() {
		$this->guard( 'workparcel_manage_customers' );
		$id = absint( $_POST['id'] ?? 0 );
		check_admin_referer( 'workparcel_delete_customer_' . $id );
		Customer::delete( $id );
		wp_safe_redirect( admin_url( 'admin.php?page=workparcel-customers&message=deleted' ) );
		exit;
	}
}
