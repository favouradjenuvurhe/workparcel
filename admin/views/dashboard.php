<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wp-workparcel-wrap">
	<?php
	$wp_page_title   = __( 'Shipment management & tracking', 'workparcel' );
	$wp_page_actions = '<a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=workparcel-add' ) ) . '">' . esc_html__( 'Add Shipment', 'workparcel' ) . '</a>';
	include WORKPARCEL_DIR . 'admin/views/partials/header.php';
	?>

	<div class="wp-workparcel-stat-grid">
		<div class="wp-workparcel-stat">
			<span class="wp-workparcel-stat-label"><?php esc_html_e( 'Total Shipments', 'workparcel' ); ?></span>
			<strong class="wp-workparcel-stat-value"><?php echo esc_html( $total ); ?></strong>
		</div>
		<?php foreach ( array( 'pending', 'in_transit', 'delivered' ) as $key ) : ?>
			<div class="wp-workparcel-stat wp-workparcel-stat-<?php echo esc_attr( $key ); ?>">
				<span class="wp-workparcel-stat-label"><?php echo esc_html( Shipment::statuses()[ $key ] ); ?></span>
				<strong class="wp-workparcel-stat-value"><?php echo esc_html( $stats[ $key ] ); ?></strong>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="wp-workparcel-panel wp-workparcel-recent">
		<div class="wp-workparcel-panel-heading">
			<h2><?php esc_html_e( 'Recent Shipments', 'workparcel' ); ?></h2>
			<a class="wp-workparcel-link" href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-shipments' ) ); ?>"><?php esc_html_e( 'View all', 'workparcel' ); ?></a>
		</div>
		<?php if ( empty( $recent['items'] ) ) : ?>
			<div class="wp-workparcel-empty">
				<p><?php esc_html_e( 'No shipments yet.', 'workparcel' ); ?></p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add' ) ); ?>"><?php esc_html_e( 'Create your first shipment', 'workparcel' ); ?></a>
			</div>
		<?php else : ?>
			<table class="widefat striped wp-workparcel-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tracking Number', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Recipient', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Status', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Created', 'workparcel' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $recent['items'] as $item ) : ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Tracking Number', 'workparcel' ); ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add&id=' . $item->id ) ); ?>"><strong><?php echo esc_html( $item->tracking_number ); ?></strong></a>
						</td>
						<td data-label="<?php esc_attr_e( 'Recipient', 'workparcel' ); ?>"><?php echo esc_html( $item->receiver_name ?: '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Status', 'workparcel' ); ?>">
							<span class="wp-workparcel-badge wp-workparcel-badge-<?php echo esc_attr( $item->status ); ?>"><?php echo esc_html( Shipment::statuses()[ $item->status ] ?? $item->status ); ?></span>
						</td>
						<td data-label="<?php esc_attr_e( 'Created', 'workparcel' ); ?>"><?php echo esc_html( $item->created_at ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
