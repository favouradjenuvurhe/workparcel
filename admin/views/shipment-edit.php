<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<h1><?php echo esc_html( $shipment ? 'Edit Shipment' : 'Add Shipment' ); ?></h1>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="workparcel_save_shipment">
		<input type="hidden" name="id" value="<?php echo esc_attr( $shipment->id ?? 0 ); ?>">
		<?php wp_nonce_field( 'workparcel_save_shipment' ); ?>
		<div class="wp-workparcel-editor">
			<div class="wp-workparcel-panel">
				<h2>Shipment</h2>
				<p><label>Tracking Number<br><input class="widefat" name="tracking_number" value="<?php echo esc_attr( $shipment->tracking_number ?? '' ); ?>" placeholder="Leave blank to generate"></label></p>
				<p><label>Reference<br><input class="widefat" name="reference" value="<?php echo esc_attr( $shipment->reference ?? '' ); ?>"></label></p>
				<p><label>Title<br><input class="widefat" name="title" value="<?php echo esc_attr( $shipment->title ?? '' ); ?>"></label></p>
				<p><label>Description<br><textarea class="widefat" name="description" rows="4"><?php echo esc_textarea( $shipment->description ?? '' ); ?></textarea></label></p>
				<div class="wp-workparcel-two"><p><label>Parcel Type<br><input class="widefat" name="parcel_type" value="<?php echo esc_attr( $shipment->parcel_type ?? '' ); ?>"></label></p><p><label>Weight<br><input type="number" step="0.01" min="0" class="widefat" name="weight" value="<?php echo esc_attr( $shipment->weight ?? '0' ); ?>"></label></p></div>
				<div class="wp-workparcel-two"><p><label>Quantity<br><input type="number" min="1" class="widefat" name="quantity" value="<?php echo esc_attr( $shipment->quantity ?? '1' ); ?>"></label></p><p><label>Shipping Fee<br><input type="number" step="0.01" min="0" class="widefat" name="shipping_fee" value="<?php echo esc_attr( $shipment->shipping_fee ?? '0' ); ?>"></label></p></div>
				<div class="wp-workparcel-two"><p><label>Status<br><select class="widefat" name="status"><?php foreach ( Shipment::statuses() as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $shipment->status ?? 'pending', $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label></p><p><label>Estimated Delivery<br><input type="date" class="widefat" name="estimated_delivery" value="<?php echo esc_attr( $shipment->estimated_delivery ?? '' ); ?>"></label></p></div>
			</div>
			<div class="wp-workparcel-panel">
				<h2>Sender</h2>
				<p><label>Name<br><input class="widefat" name="sender_name" value="<?php echo esc_attr( $shipment->sender_name ?? '' ); ?>"></label></p>
				<p><label>Email<br><input type="email" class="widefat" name="sender_email" value="<?php echo esc_attr( $shipment->sender_email ?? '' ); ?>"></label></p>
				<p><label>Phone<br><input class="widefat" name="sender_phone" value="<?php echo esc_attr( $shipment->sender_phone ?? '' ); ?>"></label></p>
				<p><label>Address<br><textarea class="widefat" name="sender_address" rows="4"><?php echo esc_textarea( $shipment->sender_address ?? '' ); ?></textarea></label></p>
				<h2>Receiver</h2>
				<p><label>Name<br><input class="widefat" name="receiver_name" value="<?php echo esc_attr( $shipment->receiver_name ?? '' ); ?>"></label></p>
				<p><label>Email<br><input type="email" class="widefat" name="receiver_email" value="<?php echo esc_attr( $shipment->receiver_email ?? '' ); ?>"></label></p>
				<p><label>Phone<br><input class="widefat" name="receiver_phone" value="<?php echo esc_attr( $shipment->receiver_phone ?? '' ); ?>"></label></p>
				<p><label>Address<br><textarea class="widefat" name="receiver_address" rows="4"><?php echo esc_textarea( $shipment->receiver_address ?? '' ); ?></textarea></label></p>
			</div>
			<div class="wp-workparcel-panel">
				<h2>Delivery</h2>
				<p><label>Origin<br><input class="widefat" name="origin" value="<?php echo esc_attr( $shipment->origin ?? '' ); ?>"></label></p>
				<p><label>Destination<br><input class="widefat" name="destination" value="<?php echo esc_attr( $shipment->destination ?? '' ); ?>"></label></p>
				<?php if ( $shipment ) : ?>
					<h2>Tracking Event</h2>
					<form></form>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="workparcel_add_event"><input type="hidden" name="shipment_id" value="<?php echo esc_attr( $shipment->id ); ?>">
						<?php wp_nonce_field( 'workparcel_add_event_' . $shipment->id ); ?>
						<p><label>Status<br><select class="widefat" name="status"><?php foreach ( Shipment::statuses() as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $shipment->status, $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label></p>
						<p><label>Location<br><input class="widefat" name="location"></label></p>
						<p><label>Description<br><textarea class="widefat" name="description" rows="3"></textarea></label></p>
						<p><label>Date/time<br><input type="datetime-local" class="widefat" name="event_date" value="<?php echo esc_attr( current_time( 'Y-m-d\TH:i' ) ); ?>"></label></p>
						<p><button class="button">Add Tracking Event</button></p>
					</form>
					<h2>History</h2>
					<?php if ( $events ) : ?><ul class="wp-workparcel-history"><?php foreach ( $events as $event ) : ?><li><strong><?php echo esc_html( Shipment::statuses()[ $event->status ] ?? $event->status ); ?></strong><br><?php echo esc_html( $event->location ); ?> <?php echo esc_html( $event->description ); ?><br><small><?php echo esc_html( $event->event_date ); ?></small></li><?php endforeach; ?></ul><?php else : ?><p>No tracking events.</p><?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php submit_button( $shipment ? 'Update Shipment' : 'Create Shipment', 'primary', 'submit', true ); ?>
	</form>
</div>
