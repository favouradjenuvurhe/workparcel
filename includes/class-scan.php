<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Scan tool lives on the frontend (via shortcode) so drivers/receivers who don't have
 * a WordPress account can use it. Because of that, every action here is re-authorized on
 * every single request rather than trusting anything the client already told us:
 *   - A logged-in user with workparcel_edit_shipments (etc.) capability is trusted as staff.
 *   - Anyone else must supply a scan_id that resolves to an active Customer record; that
 *     record is re-looked-up on every action call, never just cached from an earlier step.
 */
class Scan {

	/** Unrecognised Scan IDs allowed from one IP within the lock-out window before further attempts are refused. */
	const MAX_FAILURES = 10;

	public static function init() {
		add_shortcode( 'workparcel_scan', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_ajax_workparcel_scan_nonce', array( __CLASS__, 'ajax_nonce' ) );
		add_action( 'wp_ajax_nopriv_workparcel_scan_nonce', array( __CLASS__, 'ajax_nonce' ) );
		add_action( 'wp_ajax_workparcel_scan_verify', array( __CLASS__, 'ajax_verify' ) );
		add_action( 'wp_ajax_nopriv_workparcel_scan_verify', array( __CLASS__, 'ajax_verify' ) );
		add_action( 'wp_ajax_workparcel_scan_action', array( __CLASS__, 'ajax_action' ) );
		add_action( 'wp_ajax_nopriv_workparcel_scan_action', array( __CLASS__, 'ajax_action' ) );
	}

	public static function shortcode() {
		$settings = Settings::get();

		if ( empty( $settings['enable_scan_page'] ) ) {
			if ( is_user_logged_in() && current_user_can( 'workparcel_manage_settings' ) ) {
				return '<p>' . wp_kses_post( sprintf(
					/* translators: %s: Settings URL */
					__( 'The Scan page is currently disabled. Enable it under <a href="%s">Workparcel → Settings → Scan &amp; API</a>. (Only you can see this message.)', 'workparcel' ),
					esc_url( admin_url( 'admin.php?page=workparcel-settings' ) )
				) ) . '</p>';
			}
			return '';
		}

		wp_enqueue_style( 'workparcel-scan', WORKPARCEL_URL . 'public/css/scan.css', array(), WORKPARCEL_VERSION );
		wp_enqueue_script( 'workparcel-scan', WORKPARCEL_URL . 'public/js/scan.js', array( 'jquery' ), WORKPARCEL_VERSION, true );

		wp_add_inline_style( 'workparcel-scan', '.wp-workparcel-scan{' . Settings::css_vars( $settings ) . '}' );

		// This page is an app, not content: keep page-cache plugins from freezing its nonce or state.
		if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true );

		$is_staff = is_user_logged_in() && current_user_can( 'workparcel_edit_shipments' );

		wp_localize_script( 'workparcel-scan', 'workparcelScanFront', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'workparcel_scan_front' ),
			'isStaff' => $is_staff,
			'i18n' => array(
				'signIn' => __( 'Sign in with your Scan ID', 'workparcel' ),
				'scanIdPlaceholder' => __( 'Scan or enter your Scan ID', 'workparcel' ),
				'invalidScanId' => __( 'That Scan ID was not recognized.', 'workparcel' ),
				'welcome' => __( 'Signed in as', 'workparcel' ),
				'signOut' => __( 'Sign out', 'workparcel' ),
				'currentStatus' => __( 'currently', 'workparcel' ),
				'newStatus' => __( 'New status', 'workparcel' ),
				'location' => __( 'Location', 'workparcel' ),
				'locationPlaceholder' => __( 'Optional', 'workparcel' ),
				'updateStatus' => __( 'Update Status', 'workparcel' ),
				'assignScanId' => __( "Driver/customer's Scan ID", 'workparcel' ),
				'assign' => __( 'Assign', 'workparcel' ),
				'created' => __( 'created', 'workparcel' ),
			'requestFailed' => __( 'The request failed. Check your connection or reload the page and try again.', 'workparcel' ),
			),
		) );

		ob_start();
		include WORKPARCEL_DIR . 'public/views/scan.php';
		return ob_get_clean();
	}

	/**
	 * Page-cache plugins freeze the nonce that was printed into the page, which then expires and every scan
	 * silently fails. The scan page asks for a fresh nonce on load instead. (Nonces are anti-CSRF tokens,
	 * not credentials, so handing one out publicly is fine: access is still decided by the Scan ID / capability.)
	 */
	public static function ajax_nonce() {
		nocache_headers();
		wp_send_json_success( array( 'nonce' => wp_create_nonce( 'workparcel_scan_front' ) ) );
	}

	private static function throttle_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		return 'workparcel_scan_fail_' . md5( $ip );
	}

	private static function is_locked() {
		return (int) get_transient( self::throttle_key() ) >= self::MAX_FAILURES;
	}

	private static function record_failure() {
		$key = self::throttle_key();
		set_transient( $key, (int) get_transient( $key ) + 1, 15 * MINUTE_IN_SECONDS );
	}

	private static function clear_failures() {
		delete_transient( self::throttle_key() );
	}

	private static function locked_message() {
		return __( 'Too many unrecognized Scan IDs. Please wait 15 minutes and try again.', 'workparcel' );
	}

	public static function ajax_verify() {
		check_ajax_referer( 'workparcel_scan_front', 'nonce' );

		$settings = Settings::get();
		if ( empty( $settings['enable_scan_page'] ) ) wp_send_json_error( array( 'message' => __( 'Scanning is currently disabled.', 'workparcel' ) ) );

		if ( self::is_locked() ) wp_send_json_error( array( 'message' => self::locked_message() ) );

		$scan_id = isset( $_POST['scan_id'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['scan_id'] ) ) ) : '';
		$customer = Customer::find_by_scan_id( $scan_id );
		if ( ! $customer ) {
			self::record_failure();
			wp_send_json_error( array( 'message' => __( 'That Scan ID was not recognized.', 'workparcel' ) ) );
		}
		self::clear_failures();

		wp_send_json_success( array(
			'name' => $customer->name,
			'type' => Customer::types()[ $customer->type ] ?? $customer->type,
		) );
	}

	/** Returns the acting Customer for a non-staff request, or null. Never trusts a cached/client-supplied identity beyond the scan_id itself. */
	private static function authorize( $is_staff_claim ) {
		if ( $is_staff_claim && is_user_logged_in() && current_user_can( 'workparcel_edit_shipments' ) ) {
			return true; // staff, authorized via WordPress capability
		}
		if ( self::is_locked() ) return false;
		$scan_id = isset( $_POST['scan_id'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['scan_id'] ) ) ) : '';
		$customer = Customer::find_by_scan_id( $scan_id );
		if ( ! $customer ) {
			self::record_failure();
			return false;
		}
		return $customer;
	}

	public static function ajax_action() {
		check_ajax_referer( 'workparcel_scan_front', 'nonce' );

		$settings = Settings::get();
		if ( empty( $settings['enable_scan_page'] ) ) wp_send_json_error( array( 'message' => __( 'Scanning is currently disabled.', 'workparcel' ) ) );

		$is_staff_claim = ! empty( $_POST['is_staff'] );
		$actor = self::authorize( $is_staff_claim );
		if ( ! $actor ) wp_send_json_error( array( 'message' => self::is_locked() ? self::locked_message() : __( 'Please sign in with a valid Scan ID first.', 'workparcel' ) ) );

		$mode = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : '';
		$tracking = isset( $_POST['tracking_number'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['tracking_number'] ) ) ) : '';
		if ( ! $tracking ) wp_send_json_error( array( 'message' => __( 'No tracking number scanned.', 'workparcel' ) ) );

		/**
		 * Lets a site restrict what a scanner may do (e.g. only drivers may change status, customers may only look up).
		 *
		 * @param bool        $allowed  Whether this action is allowed. Default true.
		 * @param true|object $actor    true for logged-in staff, otherwise the Customer row that signed in.
		 * @param string      $mode     'create', 'status' or 'assign'.
		 * @param string      $tracking Scanned tracking number.
		 */
		if ( ! apply_filters( 'workparcel_scan_actor_can', true, $actor, $mode, $tracking ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do that.', 'workparcel' ) ) );
		}
		$actor_label = ( true === $actor ) ? wp_get_current_user()->display_name : $actor->name;

		if ( 'create' === $mode ) {
			$existing = Shipment::find_by_tracking( $tracking );
			if ( $existing ) {
				wp_send_json_error( array( 'message' => __( 'A shipment with this tracking number already exists.', 'workparcel' ) ) );
			}
			$new_id = Shipment::save( array( 'tracking_number' => $tracking ), 0 ); // status = the Default status setting
			if ( is_wp_error( $new_id ) ) wp_send_json_error( array( 'message' => $new_id->get_error_message() ) );
			wp_send_json_success( array( 'message' => __( 'Shipment created.', 'workparcel' ), 'tracking_number' => $tracking ) );
		}

		if ( 'status' === $mode ) {
			$shipment = Shipment::find_by_tracking( $tracking );
			if ( ! $shipment ) wp_send_json_error( array( 'message' => __( 'No shipment found for this tracking number.', 'workparcel' ) ) );

			$new_status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';
			if ( $new_status ) {
				$location = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';
				// Public tracking pages show this note, so it must not name the person; who did it is kept in the private `actor` field.
				$note = __( 'Status updated via barcode scan.', 'workparcel' );
				$result = Shipment::update_status( $shipment->id, $new_status, $location, $note, '', $actor_label );
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
			$shipment = Shipment::find_by_tracking( $tracking );
			if ( ! $shipment ) wp_send_json_error( array( 'message' => __( 'No shipment found for this tracking number.', 'workparcel' ) ) );

			$assign_scan_id = isset( $_POST['assign_scan_id'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['assign_scan_id'] ) ) ) : '';
			if ( $assign_scan_id ) {
				$target = Customer::find_by_scan_id( $assign_scan_id );
				if ( ! $target ) {
					if ( true !== $actor ) self::record_failure();
					wp_send_json_error( array( 'message' => __( 'That Scan ID was not recognized.', 'workparcel' ) ) );
				}

				$result = Shipment::assign_driver( $shipment->id, $target->id );
				if ( is_wp_error( $result ) ) wp_send_json_error( array( 'message' => $result->get_error_message() ) );
				wp_send_json_success( array( 'message' => sprintf( __( 'Assigned to %s.', 'workparcel' ), $target->name ), 'tracking_number' => $tracking ) );
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
