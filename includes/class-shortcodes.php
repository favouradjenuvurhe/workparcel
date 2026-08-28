<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcodes {

	/** Statuses shown left-to-right in the progress stepper. Exception states are handled separately. */
	private static function stepper_statuses() {
		return array( 'pending', 'processing', 'picked_up', 'in_transit', 'at_facility', 'out_for_delivery', 'delivered' );
	}

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
		wp_enqueue_script( 'workparcel-public', WORKPARCEL_URL . 'public/js/public.js', array(), WORKPARCEL_VERSION, true );
		$accent = sanitize_hex_color( $settings['accent_color'] ?? '' ) ?: '#2563eb';
		wp_add_inline_style( 'workparcel-public', '.wp-workparcel-tracker{--wp-workparcel-accent: ' . $accent . ';}' );
		?>
		<div class="wp-workparcel-tracker">
			<div class="wp-workparcel-card wp-workparcel-search-card">
				<?php if ( ! empty( $settings['company_logo'] ) ) : ?>
					<img class="wp-workparcel-brand-logo-img" src="<?php echo esc_url( $settings['company_logo'] ); ?>" alt="<?php echo esc_attr( $settings['company_name'] ); ?>">
				<?php elseif ( ! empty( $settings['company_name'] ) ) : ?>
					<span class="wp-workparcel-eyebrow"><?php echo esc_html( $settings['company_name'] ); ?></span>
				<?php endif; ?>
				<h2><?php echo esc_html( $settings['tracking_title'] ); ?></h2>
				<p><?php echo esc_html( $settings['tracking_description'] ); ?></p>
				<form method="get" class="wp-workparcel-form">
					<label for="wp-workparcel-tracking"><?php esc_html_e( 'Tracking number', 'workparcel' ); ?></label>
					<div class="wp-workparcel-form-row">
						<input id="wp-workparcel-tracking" name="workparcel_tracking" value="<?php echo esc_attr( $tracking ); ?>" required autocomplete="off" autocapitalize="characters" placeholder="<?php esc_attr_e( 'e.g. WP-A1B2C3D4', 'workparcel' ); ?>">
						<button type="submit"><?php esc_html_e( 'Track Shipment', 'workparcel' ); ?></button>
					</div>
				</form>
			</div>

			<?php if ( $tracking && ! $shipment ) : ?>
				<div class="wp-workparcel-alert" role="alert"><?php esc_html_e( 'Shipment not found. Please check your tracking number and try again.', 'workparcel' ); ?></div>
			<?php endif; ?>

			<?php if ( $shipment ) :
				$events        = Tracking::events( $shipment->id );
				$statuses      = Shipment::statuses();
				$status_label  = $statuses[ $shipment->status ] ?? $shipment->status;
				$is_exception  = in_array( $shipment->status, array( 'failed_delivery', 'cancelled' ), true );
				$steps         = self::stepper_statuses();
				$current_index = array_search( $shipment->status, $steps, true );
				if ( 'delivered' === $shipment->status ) $current_index = count( $steps ) - 1;

				$details = array(
					__( 'Origin', 'workparcel' )      => $shipment->origin,
					__( 'Destination', 'workparcel' ) => $shipment->destination,
					__( 'Receiver', 'workparcel' )    => $shipment->receiver_name,
					__( 'Estimated delivery', 'workparcel' ) => $shipment->estimated_delivery,
					__( 'Parcel type', 'workparcel' ) => $shipment->parcel_type,
					__( 'Weight', 'workparcel' )      => ( $shipment->weight > 0 ) ? $shipment->weight : '',
					__( 'Quantity', 'workparcel' )    => ( $shipment->quantity > 0 ) ? $shipment->quantity : '',
				);
				?>
				<div class="wp-workparcel-card">
					<div class="wp-workparcel-header">
						<div>
							<span class="wp-workparcel-muted"><?php esc_html_e( 'Tracking number', 'workparcel' ); ?></span>
							<div class="wp-workparcel-tracking-row">
								<strong><?php echo esc_html( $shipment->tracking_number ); ?></strong>
								<button type="button" class="wp-workparcel-copy" data-copy="<?php echo esc_attr( $shipment->tracking_number ); ?>" data-copied-label="<?php esc_attr_e( 'Copied', 'workparcel' ); ?>" aria-label="<?php esc_attr_e( 'Copy tracking number', 'workparcel' ); ?>">
									<?php esc_html_e( 'Copy', 'workparcel' ); ?>
								</button>
							</div>
						</div>
						<span class="wp-workparcel-status wp-workparcel-status-<?php echo esc_attr( $shipment->status ); ?>"><?php echo esc_html( $status_label ); ?></span>
					</div>

					<?php if ( $is_exception ) : ?>
						<div class="wp-workparcel-alert wp-workparcel-alert-status" role="alert"><?php echo esc_html( sprintf( __( 'This shipment is marked as: %s', 'workparcel' ), $status_label ) ); ?></div>
					<?php else : ?>
						<div class="wp-workparcel-stepper" role="list" aria-label="<?php esc_attr_e( 'Shipment progress', 'workparcel' ); ?>">
							<?php foreach ( $steps as $i => $step_key ) :
								$state = $i < $current_index ? 'done' : ( $i === $current_index ? 'current' : 'upcoming' );
								?>
								<div class="wp-workparcel-step wp-workparcel-step-<?php echo esc_attr( $state ); ?>" role="listitem">
									<span class="wp-workparcel-step-dot" aria-hidden="true"></span>
									<span class="wp-workparcel-step-label"><?php echo esc_html( $statuses[ $step_key ] ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="wp-workparcel-grid">
						<?php foreach ( $details as $label => $value ) : if ( '' === $value || null === $value ) continue; ?>
							<div><span><?php echo esc_html( $label ); ?></span><strong><?php echo esc_html( $value ); ?></strong></div>
						<?php endforeach; ?>
					</div>

					<?php if ( $events ) : ?>
						<h3><?php esc_html_e( 'Tracking history', 'workparcel' ); ?></h3>
						<div class="wp-workparcel-timeline">
							<?php foreach ( $events as $event ) : ?>
								<div class="wp-workparcel-event">
									<div class="wp-workparcel-dot" aria-hidden="true"></div>
									<div>
										<strong><?php echo esc_html( $statuses[ $event->status ] ?? $event->status ); ?></strong>
										<?php if ( $event->location ) : ?><span class="wp-workparcel-location"><?php echo esc_html( $event->location ); ?></span><?php endif; ?>
										<?php if ( $event->description ) : ?><p><?php echo esc_html( $event->description ); ?></p><?php endif; ?>
										<small><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $event->event_date ) ) ); ?></small>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
