<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$wp_statuses = \Workparcel\Shipment::statuses();
$wp_status_label = $wp_statuses[ $shipment->status ] ?? $shipment->status;
$wp_accent = sanitize_hex_color( $settings['accent_color'] ?? '' ) ?: '#2563eb';
?>
<div class="wrap wp-workparcel-wrap">
	<div class="wp-workparcel-invoice-actions">
		<button type="button" class="button button-primary" onclick="window.print()"><?php esc_html_e( 'Print / Save as PDF', 'workparcel' ); ?></button>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add&id=' . $shipment->id ) ); ?>"><?php esc_html_e( '← Back to Shipment', 'workparcel' ); ?></a>
	</div>

	<div class="wp-workparcel-invoice">
		<div class="wp-workparcel-invoice-header">
			<div>
				<span class="wp-workparcel-invoice-eyebrow"><?php esc_html_e( 'Tracking No.', 'workparcel' ); ?></span>
				<h1><?php echo esc_html( $shipment->tracking_number ); ?></h1>
				<div class="wp-workparcel-invoice-barcode"><?php echo \Workparcel\Barcode::bars_html( $shipment->tracking_number ); ?></div>
			</div>
			<div class="wp-workparcel-invoice-brand">
				<?php if ( ! empty( $settings['company_logo'] ) ) : ?>
					<img src="<?php echo esc_url( $settings['company_logo'] ); ?>" alt="<?php echo esc_attr( $settings['company_name'] ); ?>">
				<?php else : ?>
					<strong><?php echo esc_html( $settings['company_name'] ?: get_bloginfo( 'name' ) ); ?></strong>
				<?php endif; ?>
				<span class="wp-workparcel-invoice-status wp-workparcel-status-<?php echo esc_attr( $shipment->status ); ?>"><?php echo esc_html( $wp_status_label ); ?></span>
			</div>
		</div>

		<div class="wp-workparcel-invoice-grid-2">
			<div class="wp-workparcel-invoice-card">
				<h2><?php esc_html_e( 'Shipper', 'workparcel' ); ?></h2>
				<p><?php echo esc_html( $shipment->sender_name ?: '—' ); ?></p>
				<?php if ( $shipment->sender_email ) : ?><p><?php echo esc_html( $shipment->sender_email ); ?></p><?php endif; ?>
				<?php if ( $shipment->sender_phone ) : ?><p><?php echo esc_html( $shipment->sender_phone ); ?></p><?php endif; ?>
				<?php if ( $shipment->sender_address ) : ?><p><?php echo esc_html( $shipment->sender_address ); ?></p><?php endif; ?>
			</div>
			<div class="wp-workparcel-invoice-card">
				<h2><?php esc_html_e( 'Receiver', 'workparcel' ); ?></h2>
				<p><?php echo esc_html( $shipment->receiver_name ?: '—' ); ?></p>
				<?php if ( $shipment->receiver_email ) : ?><p><?php echo esc_html( $shipment->receiver_email ); ?></p><?php endif; ?>
				<?php if ( $shipment->receiver_phone ) : ?><p><?php echo esc_html( $shipment->receiver_phone ); ?></p><?php endif; ?>
				<?php if ( $shipment->receiver_address ) : ?><p><?php echo esc_html( $shipment->receiver_address ); ?></p><?php endif; ?>
			</div>
		</div>

		<div class="wp-workparcel-invoice-card">
			<h2><?php esc_html_e( 'Shipment Details', 'workparcel' ); ?></h2>
			<div class="wp-workparcel-invoice-details-grid">
				<?php
				$wp_details = array(
					__( 'Reference', 'workparcel' ) => $shipment->reference,
					__( 'Title', 'workparcel' ) => $shipment->title,
					__( 'Origin', 'workparcel' ) => $shipment->origin,
					__( 'Destination', 'workparcel' ) => $shipment->destination,
					__( 'Parcel Type', 'workparcel' ) => $shipment->parcel_type,
					__( 'Weight', 'workparcel' ) => $shipment->weight > 0 ? $shipment->weight : '',
					__( 'Quantity', 'workparcel' ) => $shipment->quantity,
					__( 'Container No.', 'workparcel' ) => $shipment->container_no,
					__( 'Assigned To', 'workparcel' ) => $shipment->driver_name,
					__( 'Estimated Delivery', 'workparcel' ) => $shipment->estimated_delivery,
					__( 'Created', 'workparcel' ) => $shipment->created_at,
				);
				foreach ( $wp_details as $wp_label => $wp_value ) :
					if ( '' === $wp_value || null === $wp_value ) continue;
					?>
					<div><span><?php echo esc_html( $wp_label ); ?></span><strong><?php echo esc_html( $wp_value ); ?></strong></div>
				<?php endforeach; ?>
			</div>
			<?php if ( $shipment->description ) : ?>
				<h3><?php esc_html_e( 'Description', 'workparcel' ); ?></h3>
				<p><?php echo esc_html( $shipment->description ); ?></p>
			<?php endif; ?>
			<?php if ( $shipment->photo ) : ?>
				<h3><?php esc_html_e( 'Photo', 'workparcel' ); ?></h3>
				<img class="wp-workparcel-invoice-photo" src="<?php echo esc_url( $shipment->photo ); ?>" alt="">
			<?php endif; ?>
			<div class="wp-workparcel-invoice-fee-row">
				<span><?php esc_html_e( 'Shipping Fee', 'workparcel' ); ?></span>
				<strong><?php echo esc_html( number_format_i18n( (float) $shipment->shipping_fee, 2 ) ); ?></strong>
			</div>
		</div>

		<?php if ( $shipment->pod_signature || $shipment->pod_photo ) : ?>
		<div class="wp-workparcel-invoice-card">
			<h2><?php esc_html_e( 'Proof of Delivery', 'workparcel' ); ?></h2>
			<div class="wp-workparcel-invoice-grid-2">
				<?php if ( $shipment->pod_signature ) : ?>
					<div>
						<span class="wp-workparcel-invoice-label"><?php esc_html_e( 'Signature', 'workparcel' ); ?></span>
						<img class="wp-workparcel-invoice-signature" src="<?php echo esc_url( $shipment->pod_signature ); ?>" alt="">
					</div>
				<?php endif; ?>
				<?php if ( $shipment->pod_photo ) : ?>
					<div>
						<span class="wp-workparcel-invoice-label"><?php esc_html_e( 'Delivery Photo', 'workparcel' ); ?></span>
						<img class="wp-workparcel-invoice-photo" src="<?php echo esc_url( $shipment->pod_photo ); ?>" alt="">
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $events ) : ?>
		<div class="wp-workparcel-invoice-card">
			<h2><?php esc_html_e( 'Shipment History', 'workparcel' ); ?></h2>
			<table class="wp-workparcel-invoice-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Time', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Location', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Status', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Remarks', 'workparcel' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $events as $wp_event ) : ?>
						<tr>
							<td><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $wp_event->event_date ) ) ); ?></td>
							<td><?php echo esc_html( wp_date( get_option( 'time_format' ), strtotime( $wp_event->event_date ) ) ); ?></td>
							<td><?php echo esc_html( $wp_event->location ?: '—' ); ?></td>
							<td><span class="wp-workparcel-status wp-workparcel-status-<?php echo esc_attr( $wp_event->status ); ?>"><?php echo esc_html( $wp_statuses[ $wp_event->status ] ?? $wp_event->status ); ?></span></td>
							<td><?php echo esc_html( $wp_event->description ?: '—' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<div class="wp-workparcel-invoice-footer">
			<?php
			$wp_footer = array( $settings['company_name'] ?: get_bloginfo( 'name' ) );
			if ( ! empty( $settings['company_address'] ) ) $wp_footer[] = $settings['company_address'];
			if ( ! empty( $settings['company_phone'] ) ) $wp_footer[] = $settings['company_phone'];
			if ( ! empty( $settings['company_email'] ) ) $wp_footer[] = $settings['company_email'];
			echo esc_html( implode( ' · ', array_filter( $wp_footer ) ) );
			?>
		</div>
	</div>
</div>
