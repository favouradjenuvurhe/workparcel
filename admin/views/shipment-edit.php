<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wp-workparcel-wrap">
	<?php
	$wp_page_title = $shipment ? __( 'Edit Shipment', 'workparcel' ) : __( 'Add Shipment', 'workparcel' );
	include WORKPARCEL_DIR . 'admin/views/partials/header.php';
	?>

	<?php $message = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : ''; ?>
	<?php if ( 'event_added' === $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Tracking event added.', 'workparcel' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="workparcel_save_shipment">
		<input type="hidden" name="id" value="<?php echo esc_attr( $shipment->id ?? 0 ); ?>">
		<?php wp_nonce_field( 'workparcel_save_shipment' ); ?>

		<div class="wp-workparcel-editor">
			<div class="wp-workparcel-editor-main">

				<div class="wp-workparcel-panel">
					<h2><?php esc_html_e( 'Shipment Information', 'workparcel' ); ?></h2>
					<p><label for="wp-wc-tracking_number"><?php esc_html_e( 'Tracking Number', 'workparcel' ); ?><br>
						<input type="text" id="wp-wc-tracking_number" class="widefat" name="tracking_number" value="<?php echo esc_attr( $shipment->tracking_number ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Leave blank to generate', 'workparcel' ); ?>"></label></p>
					<div class="wp-workparcel-two">
						<p><label for="wp-wc-reference"><?php esc_html_e( 'Reference', 'workparcel' ); ?><br>
							<input type="text" id="wp-wc-reference" class="widefat" name="reference" value="<?php echo esc_attr( $shipment->reference ?? '' ); ?>"></label></p>
						<p><label for="wp-wc-title"><?php esc_html_e( 'Shipment Title', 'workparcel' ); ?><br>
							<input type="text" id="wp-wc-title" class="widefat" name="title" value="<?php echo esc_attr( $shipment->title ?? '' ); ?>"></label></p>
					</div>
					<p><label for="wp-wc-description"><?php esc_html_e( 'Description', 'workparcel' ); ?><br>
						<textarea id="wp-wc-description" class="widefat" name="description" rows="4"><?php echo esc_textarea( $shipment->description ?? '' ); ?></textarea></label></p>
				</div>

				<div class="wp-workparcel-panel">
					<h2><?php esc_html_e( 'Sender', 'workparcel' ); ?></h2>
					<div class="wp-workparcel-two">
						<p><label for="wp-wc-sender_name"><?php esc_html_e( 'Name', 'workparcel' ); ?><br>
							<input type="text" id="wp-wc-sender_name" class="widefat" name="sender_name" value="<?php echo esc_attr( $shipment->sender_name ?? '' ); ?>"></label></p>
						<p><label for="wp-wc-sender_phone"><?php esc_html_e( 'Phone', 'workparcel' ); ?><br>
							<input type="text" id="wp-wc-sender_phone" class="widefat" name="sender_phone" value="<?php echo esc_attr( $shipment->sender_phone ?? '' ); ?>"></label></p>
					</div>
					<p><label for="wp-wc-sender_email"><?php esc_html_e( 'Email', 'workparcel' ); ?><br>
						<input id="wp-wc-sender_email" type="email" class="widefat" name="sender_email" value="<?php echo esc_attr( $shipment->sender_email ?? '' ); ?>"></label></p>
					<p><label for="wp-wc-sender_address"><?php esc_html_e( 'Address', 'workparcel' ); ?><br>
						<textarea id="wp-wc-sender_address" class="widefat" name="sender_address" rows="3"><?php echo esc_textarea( $shipment->sender_address ?? '' ); ?></textarea></label></p>
				</div>

				<div class="wp-workparcel-panel">
					<h2><?php esc_html_e( 'Recipient', 'workparcel' ); ?></h2>
					<div class="wp-workparcel-two">
						<p><label for="wp-wc-receiver_name"><?php esc_html_e( 'Name', 'workparcel' ); ?><br>
							<input type="text" id="wp-wc-receiver_name" class="widefat" name="receiver_name" value="<?php echo esc_attr( $shipment->receiver_name ?? '' ); ?>"></label></p>
						<p><label for="wp-wc-receiver_phone"><?php esc_html_e( 'Phone', 'workparcel' ); ?><br>
							<input type="text" id="wp-wc-receiver_phone" class="widefat" name="receiver_phone" value="<?php echo esc_attr( $shipment->receiver_phone ?? '' ); ?>"></label></p>
					</div>
					<p><label for="wp-wc-receiver_email"><?php esc_html_e( 'Email', 'workparcel' ); ?><br>
						<input id="wp-wc-receiver_email" type="email" class="widefat" name="receiver_email" value="<?php echo esc_attr( $shipment->receiver_email ?? '' ); ?>"></label></p>
					<p><label for="wp-wc-receiver_address"><?php esc_html_e( 'Address', 'workparcel' ); ?><br>
						<textarea id="wp-wc-receiver_address" class="widefat" name="receiver_address" rows="3"><?php echo esc_textarea( $shipment->receiver_address ?? '' ); ?></textarea></label></p>
				</div>

				<?php if ( $shipment ) : ?>
				<div class="wp-workparcel-panel">
					<h2><?php esc_html_e( 'Tracking Timeline', 'workparcel' ); ?></h2>
					<?php if ( $events ) : ?>
						<div class="wp-workparcel-timeline">
							<?php foreach ( $events as $event ) : ?>
								<div class="wp-workparcel-event">
									<div class="wp-workparcel-dot" aria-hidden="true"></div>
									<div class="wp-workparcel-event-body">
										<strong><?php echo esc_html( \Workparcel\Shipment::statuses()[ $event->status ] ?? $event->status ); ?></strong>
										<?php if ( $event->location ) : ?><span class="wp-workparcel-location"><?php echo esc_html( $event->location ); ?></span><?php endif; ?>
										<?php if ( $event->description ) : ?><p><?php echo esc_html( $event->description ); ?></p><?php endif; ?>
										<small><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $event->event_date ) ) ); ?></small>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p class="wp-workparcel-muted-text"><?php esc_html_e( 'No tracking events yet.', 'workparcel' ); ?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<div class="wp-workparcel-editor-side">
				<div class="wp-workparcel-panel">
					<h2><?php esc_html_e( 'Shipping', 'workparcel' ); ?></h2>
					<p><label for="wp-wc-origin"><?php esc_html_e( 'Origin', 'workparcel' ); ?><br>
						<input type="text" id="wp-wc-origin" class="widefat" name="origin" value="<?php echo esc_attr( $shipment->origin ?? '' ); ?>"></label></p>
					<p><label for="wp-wc-destination"><?php esc_html_e( 'Destination', 'workparcel' ); ?><br>
						<input type="text" id="wp-wc-destination" class="widefat" name="destination" value="<?php echo esc_attr( $shipment->destination ?? '' ); ?>"></label></p>
					<p><label for="wp-wc-parcel_type"><?php esc_html_e( 'Parcel Type', 'workparcel' ); ?><br>
						<input type="text" id="wp-wc-parcel_type" class="widefat" name="parcel_type" value="<?php echo esc_attr( $shipment->parcel_type ?? '' ); ?>"></label></p>
					<div class="wp-workparcel-two">
						<p><label for="wp-wc-weight"><?php esc_html_e( 'Weight', 'workparcel' ); ?><br>
							<input id="wp-wc-weight" type="number" step="0.01" min="0" class="widefat" name="weight" value="<?php echo esc_attr( $shipment->weight ?? '0' ); ?>"></label></p>
						<p><label for="wp-wc-quantity"><?php esc_html_e( 'Quantity', 'workparcel' ); ?><br>
							<input id="wp-wc-quantity" type="number" min="1" class="widefat" name="quantity" value="<?php echo esc_attr( $shipment->quantity ?? '1' ); ?>"></label></p>
					</div>
					<p><label for="wp-wc-shipping_fee"><?php esc_html_e( 'Shipping Fee', 'workparcel' ); ?><br>
						<input id="wp-wc-shipping_fee" type="number" step="0.01" min="0" class="widefat" name="shipping_fee" value="<?php echo esc_attr( $shipment->shipping_fee ?? '0' ); ?>"></label></p>
					<p><label for="wp-wc-estimated_delivery"><?php esc_html_e( 'Estimated Delivery', 'workparcel' ); ?><br>
						<input id="wp-wc-estimated_delivery" type="date" class="widefat" name="estimated_delivery" value="<?php echo esc_attr( $shipment->estimated_delivery ?? '' ); ?>"></label></p>
				</div>

				<div class="wp-workparcel-panel">
					<h2><?php esc_html_e( 'Status', 'workparcel' ); ?></h2>
					<p><label for="wp-wc-status" class="screen-reader-text"><?php esc_html_e( 'Current status', 'workparcel' ); ?></label>
						<select id="wp-wc-status" class="widefat" name="status">
							<?php foreach ( \Workparcel\Shipment::statuses() as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $shipment->status ?? 'pending', $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<?php if ( $shipment ) : ?>
						<p class="wp-workparcel-muted-text"><?php esc_html_e( 'Changing this dropdown updates the status without logging a tracking event. Use “Add Tracking Event” below to record location and history.', 'workparcel' ); ?></p>
					<?php endif; ?>
				</div>

				<?php submit_button( $shipment ? __( 'Update Shipment', 'workparcel' ) : __( 'Create Shipment', 'workparcel' ), 'primary', 'submit', true ); ?>
			</div>
		</div>
	</form>

	<?php if ( $shipment ) : ?>
	<div class="wp-workparcel-panel wp-workparcel-event-panel">
		<h2><?php esc_html_e( 'Add Tracking Event', 'workparcel' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wp-workparcel-event-form">
			<input type="hidden" name="action" value="workparcel_add_event">
			<input type="hidden" name="shipment_id" value="<?php echo esc_attr( $shipment->id ); ?>">
			<?php wp_nonce_field( 'workparcel_add_event_' . $shipment->id ); ?>
			<div class="wp-workparcel-two">
				<p><label for="wp-wc-event_status"><?php esc_html_e( 'Status', 'workparcel' ); ?><br>
					<select id="wp-wc-event_status" class="widefat" name="status">
						<?php foreach ( \Workparcel\Shipment::statuses() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $shipment->status, $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select></label></p>
				<p><label for="wp-wc-event_location"><?php esc_html_e( 'Location', 'workparcel' ); ?><br>
					<input type="text" id="wp-wc-event_location" class="widefat" name="location"></label></p>
			</div>
			<p><label for="wp-wc-event_description"><?php esc_html_e( 'Description', 'workparcel' ); ?><br>
				<textarea id="wp-wc-event_description" class="widefat" name="description" rows="3"></textarea></label></p>
			<p><label for="wp-wc-event_date"><?php esc_html_e( 'Date/Time', 'workparcel' ); ?><br>
				<input id="wp-wc-event_date" type="datetime-local" class="widefat" name="event_date" value="<?php echo esc_attr( current_time( 'Y-m-d\TH:i' ) ); ?>"></label></p>
			<p><button class="button button-primary"><?php esc_html_e( 'Add Tracking Event', 'workparcel' ); ?></button></p>
		</form>
	</div>
	<?php endif; ?>
</div>
