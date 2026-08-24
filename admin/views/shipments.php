<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wp-workparcel-wrap">
	<?php
	$wp_page_title   = __( 'Shipments', 'workparcel' );
	$wp_page_actions = '<a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=workparcel-add' ) ) . '">' . esc_html__( 'Add Shipment', 'workparcel' ) . '</a>';
	include WORKPARCEL_DIR . 'admin/views/partials/header.php';
	?>

	<?php $message = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : ''; ?>
	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( 'saved' === $message ? __( 'Shipment saved.', 'workparcel' ) : __( 'Shipment deleted.', 'workparcel' ) ); ?></p></div>
	<?php endif; ?>

	<div class="wp-workparcel-panel">
		<form method="get" class="wp-workparcel-filters">
			<input type="hidden" name="page" value="workparcel-shipments">
			<label class="screen-reader-text" for="wp-workparcel-search"><?php esc_html_e( 'Search shipments', 'workparcel' ); ?></label>
			<input type="search" id="wp-workparcel-search" name="s" placeholder="<?php esc_attr_e( 'Search tracking number, title, or recipient…', 'workparcel' ); ?>" value="<?php echo esc_attr( isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '' ); ?>">
			<label class="screen-reader-text" for="wp-workparcel-status-filter"><?php esc_html_e( 'Filter by status', 'workparcel' ); ?></label>
			<select id="wp-workparcel-status-filter" name="status">
				<option value=""><?php esc_html_e( 'All statuses', 'workparcel' ); ?></option>
				<?php foreach ( Shipment::statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '', $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button class="button"><?php esc_html_e( 'Filter', 'workparcel' ); ?></button>
		</form>

		<?php if ( empty( $result['items'] ) ) : ?>
			<div class="wp-workparcel-empty">
				<p><?php esc_html_e( 'No shipments found.', 'workparcel' ); ?></p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add' ) ); ?>"><?php esc_html_e( 'Add Shipment', 'workparcel' ); ?></a>
			</div>
		<?php else : ?>
			<table class="widefat striped wp-workparcel-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tracking Number', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Shipment', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Recipient', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Origin', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Destination', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Status', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Estimated Delivery', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Created', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'workparcel' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $result['items'] as $item ) : ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Tracking Number', 'workparcel' ); ?>">
							<strong><a href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add&id=' . $item->id ) ); ?>"><?php echo esc_html( $item->tracking_number ); ?></a></strong>
						</td>
						<td data-label="<?php esc_attr_e( 'Shipment', 'workparcel' ); ?>"><?php echo esc_html( $item->title ?: $item->reference ?: '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Recipient', 'workparcel' ); ?>"><?php echo esc_html( $item->receiver_name ?: '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Origin', 'workparcel' ); ?>"><?php echo esc_html( $item->origin ?: '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Destination', 'workparcel' ); ?>"><?php echo esc_html( $item->destination ?: '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Status', 'workparcel' ); ?>">
							<span class="wp-workparcel-badge wp-workparcel-badge-<?php echo esc_attr( $item->status ); ?>"><?php echo esc_html( Shipment::statuses()[ $item->status ] ?? $item->status ); ?></span>
						</td>
						<td data-label="<?php esc_attr_e( 'Estimated Delivery', 'workparcel' ); ?>"><?php echo esc_html( $item->estimated_delivery ?: '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Created', 'workparcel' ); ?>"><?php echo esc_html( $item->created_at ); ?></td>
						<td data-label="<?php esc_attr_e( 'Actions', 'workparcel' ); ?>" class="wp-workparcel-actions">
							<a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add&id=' . $item->id ) ); ?>"><?php esc_html_e( 'Edit', 'workparcel' ); ?></a>
							<?php if ( current_user_can( 'workparcel_delete_shipments' ) ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wp-workparcel-inline-form" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this shipment?', 'workparcel' ) ); ?>');">
								<input type="hidden" name="action" value="workparcel_delete_shipment">
								<input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>">
								<?php wp_nonce_field( 'workparcel_delete_shipment_' . $item->id ); ?>
								<button type="submit" class="button-link-delete"><?php esc_html_e( 'Delete', 'workparcel' ); ?></button>
							</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php if ( $result['pages'] > 1 ) : ?>
				<div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'paged', '%#%' ), 'format' => '', 'current' => max( 1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 ), 'total' => $result['pages'] ) ) ); ?></div></div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
