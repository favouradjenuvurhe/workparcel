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
		// Run the schema upgrade early on every request (not just in wp-admin) so REST/AJAX never see a half-upgraded database after an update.
		Database::maybe_upgrade();
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'parent_file', array( $this, 'menu_parent_file' ) );
		add_filter( 'submenu_file', array( $this, 'menu_submenu_file' ) );
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

	/**
	 * The invoice and add/edit-customer screens are hidden pages, which makes WordPress collapse the Workparcel menu
	 * while you are on them. These two filters keep the menu open and the right item highlighted.
	 */
	public function menu_parent_file( $parent_file ) {
		global $plugin_page;
		if ( in_array( $plugin_page, array( 'workparcel-invoice', 'workparcel-customer-edit' ), true ) ) return 'workparcel';
		return $parent_file;
	}

	public function menu_submenu_file( $submenu_file ) {
		global $plugin_page;
		if ( 'workparcel-invoice' === $plugin_page ) return 'workparcel-shipments';
		if ( 'workparcel-customer-edit' === $plugin_page ) return 'workparcel-customers';
		if ( 'workparcel-add' === $plugin_page && ! empty( $_GET['id'] ) ) return 'workparcel-shipments';
		return $submenu_file;
	}

	public function admin_assets( $hook ) {
		if ( strpos( $hook, 'workparcel' ) === false ) return;
		wp_enqueue_style( 'workparcel-admin', WORKPARCEL_URL . 'admin/css/admin.css', array(), WORKPARCEL_VERSION );
		$css_vars = Settings::css_vars();
		wp_add_inline_style( 'workparcel-admin', ':root{' . $css_vars . '}' );
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
			wp_add_inline_style( 'workparcel-invoice', '.wp-workparcel-invoice{' . $css_vars . '}' );
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
		$stats = array_fill_keys( array_keys( Shipment::statuses() ), 0 );
		$total = 0;
		foreach ( (array) $wpdb->get_results( "SELECT status, COUNT(*) AS c FROM $table GROUP BY status" ) as $row ) {
			$total += (int) $row->c;
			if ( isset( $stats[ $row->status ] ) ) $stats[ $row->status ] = (int) $row->c;
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
		$customers = Customer::all( array( 'per_page' => 100, 'status' => 'active' ) )['items'];
		// Never drop the current assignee from the list (they may be inactive or beyond the first 100),
		// otherwise saving the form would silently un-assign the shipment.
		if ( $shipment && (int) $shipment->customer_id ) {
			$listed = false;
			foreach ( $customers as $c ) {
				if ( (int) $c->id === (int) $shipment->customer_id ) { $listed = true; break; }
			}
			if ( ! $listed ) {
				$current = Customer::get( (int) $shipment->customer_id );
				if ( $current ) array_unshift( $customers, $current );
			}
		}
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
		if ( is_wp_error( $result ) ) wp_die( esc_html( $result->get_error_message() ), '', array( 'back_link' => true ) );
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
		$result = Shipment::update_status( $id, $status, $location, $description, $event_date, wp_get_current_user()->display_name );
		if ( is_wp_error( $result ) ) wp_die( esc_html( $result->get_error_message() ), '', array( 'back_link' => true ) );
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
		if ( is_wp_error( $result ) ) wp_die( esc_html( $result->get_error_message() ), '', array( 'back_link' => true ) );
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
