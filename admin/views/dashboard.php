<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<h1>Workparcel</h1>
	<div class="wp-workparcel-admin-grid">
		<div class="wp-workparcel-stat"><span>Total Shipments</span><strong><?php echo esc_html( $total ); ?></strong></div>
		<?php foreach ( array( 'pending','in_transit','out_for_delivery','delivered' ) as $key ) : ?>
			<div class="wp-workparcel-stat"><span><?php echo esc_html( Shipment::statuses()[ $key ] ); ?></span><strong><?php echo esc_html( $stats[ $key ] ); ?></strong></div>
		<?php endforeach; ?>
	</div>
	<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add' ) ); ?>">Add Shipment</a>
	<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-shipments' ) ); ?>">View Shipments</a></p>
</div>
