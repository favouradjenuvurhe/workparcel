<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcodes {
	public static function register() {
		add_shortcode( 'workparcel_tracking', array( __CLASS__, 'tracking' ) );
	}

	public static function tracking() {
		$settings = Settings::get();
		$shipment = null;
		$tracking = '';

		if ( isset( $_GET['workparcel_tracking'] ) ) {
			$tracking = sanitize_text_field( wp_unslash( $_GET['workparcel_tracking'] ) );
			if ( $tracking ) $shipment = Shipment::find_by_tracking( $tracking );
		}

		ob_start();
		wp_enqueue_style( 'workparcel-public', WORKPARCEL_URL . 'public/css/public.css', array(), WORKPARCEL_VERSION );
		?>
		<div class="wp-workparcel-tracker">
			<div class="wp-workparcel-card">
				<h2><?php echo esc_html( $settings['tracking_title'] ); ?></h2>
				<p><?php echo esc_html( $settings['tracking_description'] ); ?></p>
				<form method="get" class="wp-workparcel-form">
					<label for="wp-workparcel-tracking"><?php esc_html_e( 'Tracking number', 'workparcel' ); ?></label>
					<div class="wp-workparcel-form-row">
						<input id="wp-workparcel-tracking" name="workparcel_tracking" value="<?php echo esc_attr( $tracking ); ?>" required autocomplete="off">
						<button type="submit"><?php esc_html_e( 'Track Shipment', 'workparcel' ); ?></button>
					</div>
				</form>
			</div>
			<?php if ( $tracking && ! $shipment ) : ?>
				<div class="wp-workparcel-alert"><?php esc_html_e( 'Shipment not found. Please check your tracking number and try again.', 'workparcel' ); ?></div>
			<?php endif; ?>
			<?php if ( $shipment ) :
				$events = Tracking::events( $shipment->id );
				$status_label = Shipment::statuses()[ $shipment->status ] ?? $shipment->status;
				?>
				<div class="wp-workparcel-card">
					<div class="wp-workparcel-header">
						<div><span class="wp-workparcel-muted"><?php esc_html_e( 'Tracking number', 'workparcel' ); ?></span><strong><?php echo esc_html( $shipment->tracking_number ); ?></strong></div>
						<span class="wp-workparcel-status"><?php echo esc_html( $status_label ); ?></span>
					</div>
					<div class="wp-workparcel-grid">
						<div><span><?php esc_html_e( 'Origin', 'workparcel' ); ?></span><strong><?php echo esc_html( $shipment->origin ?: '—' ); ?></strong></div>
						<div><span><?php esc_html_e( 'Destination', 'workparcel' ); ?></span><strong><?php echo esc_html( $shipment->destination ?: '—' ); ?></strong></div>
						<div><span><?php esc_html_e( 'Receiver', 'workparcel' ); ?></span><strong><?php echo esc_html( $shipment->receiver_name ?: '—' ); ?></strong></div>
						<div><span><?php esc_html_e( 'Estimated delivery', 'workparcel' ); ?></span><strong><?php echo esc_html( $shipment->estimated_delivery ?: '—' ); ?></strong></div>
					</div>
					<h3><?php esc_html_e( 'Tracking history', 'workparcel' ); ?></h3>
					<div class="wp-workparcel-timeline">
						<?php foreach ( $events as $event ) : ?>
							<div class="wp-workparcel-event">
								<div class="wp-workparcel-dot"></div>
								<div><strong><?php echo esc_html( Shipment::statuses()[ $event->status ] ?? $event->status ); ?></strong>
								<?php if ( $event->location ) : ?><span class="wp-workparcel-location"><?php echo esc_html( $event->location ); ?></span><?php endif; ?>
								<p><?php echo esc_html( $event->description ); ?></p>
								<small><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $event->event_date ) ) ); ?></small></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
