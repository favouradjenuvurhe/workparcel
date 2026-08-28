<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Optional WooCommerce bridge. Every method here checks that WooCommerce is
 * active before doing anything, so this file is always safe to load even on
 * sites that don't run WooCommerce at all.
 */
class WooCommerceIntegration {

	public static function init() {
		// Must be registered at top level (not deferred) — WooCommerce fires this very early.
		add_action( 'before_woocommerce_init', array( __CLASS__, 'declare_hpos_compatibility' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_register_hooks' ), 20 );
	}

	public static function declare_hpos_compatibility() {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WORKPARCEL_FILE, true );
		}
	}

	public static function maybe_register_hooks() {
		if ( ! class_exists( 'WooCommerce' ) ) return;

		add_action( 'add_meta_boxes', array( __CLASS__, 'add_order_meta_box' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( __CLASS__, 'save_order_meta_box' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'render_customer_tracking' ) );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'render_customer_tracking_email' ) );
	}

	/** Works out the correct screen ID whether the store uses HPOS or legacy post-based orders. */
	private static function order_screen_id() {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\OrderUtil' )
			&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()
			&& function_exists( 'wc_get_page_screen_id' ) ) {
			return wc_get_page_screen_id( 'shop-order' );
		}
		return 'shop_order';
	}

	public static function add_order_meta_box() {
		add_meta_box(
			'workparcel_order_tracking',
			__( 'Workparcel Tracking', 'workparcel' ),
			array( __CLASS__, 'render_meta_box' ),
			self::order_screen_id(),
			'side',
			'default'
		);
	}

	public static function render_meta_box( $post_or_order ) {
		$order = ( $post_or_order instanceof \WC_Order ) ? $post_or_order : wc_get_order( $post_or_order->ID );
		if ( ! $order ) return;

		$tracking_number = $order->get_meta( '_workparcel_tracking_number' );
		wp_nonce_field( 'workparcel_order_tracking', 'workparcel_order_tracking_nonce' );
		?>
		<p>
			<label for="workparcel_tracking_number"><?php esc_html_e( 'Tracking number', 'workparcel' ); ?></label>
			<input type="text" class="widefat" id="workparcel_tracking_number" name="workparcel_tracking_number" value="<?php echo esc_attr( $tracking_number ); ?>" placeholder="WP-XXXXXXXX">
			<span class="description"><?php esc_html_e( 'Link this order to an existing Workparcel shipment.', 'workparcel' ); ?></span>
		</p>
		<?php
		if ( $tracking_number ) {
			$shipment = Shipment::find_by_tracking( $tracking_number );
			if ( $shipment ) {
				$label = Shipment::statuses()[ $shipment->status ] ?? $shipment->status;
				echo '<p>' . esc_html( sprintf( __( 'Status: %s', 'workparcel' ), $label ) ) . '</p>';
				echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=workparcel-add&id=' . $shipment->id ) ) . '">' . esc_html__( 'View shipment', 'workparcel' ) . '</a></p>';
			} else {
				echo '<p class="description">' . esc_html__( 'No matching Workparcel shipment found for this tracking number yet.', 'workparcel' ) . '</p>';
			}
		}
	}

	public static function save_order_meta_box( $order_id ) {
		if ( ! isset( $_POST['workparcel_order_tracking_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['workparcel_order_tracking_nonce'] ) ), 'workparcel_order_tracking' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_shop_orders' ) ) return;

		$order = wc_get_order( $order_id );
		if ( ! $order ) return;

		$tracking_number = isset( $_POST['workparcel_tracking_number'] ) ? sanitize_text_field( wp_unslash( $_POST['workparcel_tracking_number'] ) ) : '';
		$order->update_meta_data( '_workparcel_tracking_number', $tracking_number );
		$order->save();
	}

	public static function render_customer_tracking( $order ) {
		self::output_tracking_block( $order );
	}

	public static function render_customer_tracking_email( $order ) {
		self::output_tracking_block( $order );
	}

	private static function output_tracking_block( $order ) {
		if ( ! $order instanceof \WC_Order ) return;

		$tracking_number = $order->get_meta( '_workparcel_tracking_number' );
		if ( ! $tracking_number ) return;

		$shipment = Shipment::find_by_tracking( $tracking_number );
		if ( ! $shipment ) return;

		$label    = Shipment::statuses()[ $shipment->status ] ?? $shipment->status;
		$settings = Settings::get();
		$track_url = '';
		if ( ! empty( $settings['tracking_page'] ) ) {
			$track_url = add_query_arg( 'workparcel_tracking', $tracking_number, get_permalink( (int) $settings['tracking_page'] ) );
		}
		?>
		<h2><?php esc_html_e( 'Shipment Tracking', 'workparcel' ); ?></h2>
		<p>
			<?php echo esc_html( sprintf( __( 'Tracking number: %s', 'workparcel' ), $tracking_number ) ); ?><br>
			<?php echo esc_html( sprintf( __( 'Status: %s', 'workparcel' ), $label ) ); ?>
		</p>
		<?php if ( $track_url ) : ?>
			<p><a href="<?php echo esc_url( $track_url ); ?>"><?php esc_html_e( 'Track your shipment', 'workparcel' ); ?></a></p>
		<?php endif; ?>
		<?php
	}
}
