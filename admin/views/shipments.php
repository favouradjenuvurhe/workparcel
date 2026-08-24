<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<h1 class="wp-heading-inline">Shipments</h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add' ) ); ?>" class="page-title-action">Add Shipment</a>
	<hr class="wp-header-end">
	<?php $message = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : ''; ?>
	<?php if ( $message ) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html( 'saved' === $message ? 'Shipment saved.' : 'Shipment deleted.' ); ?></p></div><?php endif; ?>
	<form method="get">
		<input type="hidden" name="page" value="workparcel-shipments">
		<p class="search-box">
			<label class="screen-reader-text" for="wp-workparcel-search">Search</label>
			<input type="search" id="wp-workparcel-search" name="s" value="<?php echo esc_attr( isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '' ); ?>">
			<select name="status"><option value="">All statuses</option><?php foreach ( Shipment::statuses() as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '', $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select>
			<button class="button">Filter</button>
		</p>
	</form>
	<table class="widefat striped">
		<thead><tr><th>Tracking Number</th><th>Shipment</th><th>Receiver</th><th>Origin</th><th>Destination</th><th>Status</th><th>Estimated Delivery</th><th>Created</th><th>Actions</th></tr></thead>
		<tbody>
		<?php if ( empty( $result['items'] ) ) : ?><tr><td colspan="9">No shipments found.</td></tr><?php else : foreach ( $result['items'] as $item ) : ?>
			<tr>
				<td><strong><a href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add&id=' . $item->id ) ); ?>"><?php echo esc_html( $item->tracking_number ); ?></a></strong></td>
				<td><?php echo esc_html( $item->title ?: $item->reference ?: '—' ); ?></td>
				<td><?php echo esc_html( $item->receiver_name ?: '—' ); ?></td>
				<td><?php echo esc_html( $item->origin ?: '—' ); ?></td>
				<td><?php echo esc_html( $item->destination ?: '—' ); ?></td>
				<td><?php echo esc_html( Shipment::statuses()[ $item->status ] ?? $item->status ); ?></td>
				<td><?php echo esc_html( $item->estimated_delivery ?: '—' ); ?></td>
				<td><?php echo esc_html( $item->created_at ); ?></td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-add&id=' . $item->id ) ); ?>">Edit</a>
					<?php if ( current_user_can( 'workparcel_delete_shipments' ) ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Delete this shipment?');">
						<input type="hidden" name="action" value="workparcel_delete_shipment"><input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>">
						<?php wp_nonce_field( 'workparcel_delete_shipment_' . $item->id ); ?><button type="submit" class="button-link-delete">Delete</button>
					</form>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
	<?php if ( $result['pages'] > 1 ) : ?>
		<div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'paged', '%#%' ), 'format' => '', 'current' => max(1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1), 'total' => $result['pages'] ) ) ); ?></div></div>
	<?php endif; ?>
</div>
