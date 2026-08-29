<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wp-workparcel-wrap">
	<?php
	$wp_page_title = __( 'Customers', 'workparcel' );
	$wp_page_actions = '<a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=workparcel-customer-edit' ) ) . '">' . esc_html__( 'Add Customer', 'workparcel' ) . '</a>';
	include WORKPARCEL_DIR . 'admin/views/partials/header.php';
	?>

	<?php $message = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : ''; ?>
	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( 'saved' === $message ? __( 'Customer saved.', 'workparcel' ) : __( 'Customer deleted.', 'workparcel' ) ); ?></p></div>
	<?php endif; ?>

	<div class="wp-workparcel-panel">
		<p class="description"><?php esc_html_e( 'Drivers and customers registered here can use their Scan ID to sign in to the public Scan page (Settings → Scan & API for the shortcode). Their Scan ID can also be used to assign shipments to them.', 'workparcel' ); ?></p>

		<form method="get" class="wp-workparcel-filters">
			<input type="hidden" name="page" value="workparcel-customers">
			<label class="screen-reader-text" for="wp-workparcel-customer-search"><?php esc_html_e( 'Search customers', 'workparcel' ); ?></label>
			<input type="search" id="wp-workparcel-customer-search" name="s" placeholder="<?php esc_attr_e( 'Search name, Scan ID, email, or phone…', 'workparcel' ); ?>" value="<?php echo esc_attr( isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '' ); ?>">
			<select name="type">
				<option value=""><?php esc_html_e( 'All types', 'workparcel' ); ?></option>
				<?php foreach ( \Workparcel\Customer::types() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '', $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button class="button"><?php esc_html_e( 'Filter', 'workparcel' ); ?></button>
		</form>

		<?php if ( empty( $result['items'] ) ) : ?>
			<div class="wp-workparcel-empty">
				<p><?php esc_html_e( 'No customers registered yet.', 'workparcel' ); ?></p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-customer-edit' ) ); ?>"><?php esc_html_e( 'Add Customer', 'workparcel' ); ?></a>
			</div>
		<?php else : ?>
			<table class="widefat striped wp-workparcel-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Scan ID', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Name', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Type', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Email', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Status', 'workparcel' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'workparcel' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $result['items'] as $item ) : ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Scan ID', 'workparcel' ); ?>"><code><?php echo esc_html( $item->scan_id ); ?></code></td>
						<td data-label="<?php esc_attr_e( 'Name', 'workparcel' ); ?>"><a href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-customer-edit&id=' . $item->id ) ); ?>"><strong><?php echo esc_html( $item->name ?: '—' ); ?></strong></a></td>
						<td data-label="<?php esc_attr_e( 'Type', 'workparcel' ); ?>"><?php echo esc_html( \Workparcel\Customer::types()[ $item->type ] ?? $item->type ); ?></td>
						<td data-label="<?php esc_attr_e( 'Email', 'workparcel' ); ?>"><?php echo esc_html( $item->email ?: '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Phone', 'workparcel' ); ?>"><?php echo esc_html( $item->phone ?: '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Status', 'workparcel' ); ?>">
							<span class="wp-workparcel-badge <?php echo 'active' === $item->status ? 'wp-workparcel-badge-delivered' : 'wp-workparcel-badge-cancelled'; ?>"><?php echo esc_html( ucfirst( $item->status ) ); ?></span>
						</td>
						<td data-label="<?php esc_attr_e( 'Actions', 'workparcel' ); ?>" class="wp-workparcel-actions">
							<a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=workparcel-customer-edit&id=' . $item->id ) ); ?>"><?php esc_html_e( 'Edit', 'workparcel' ); ?></a>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wp-workparcel-inline-form" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this customer?', 'workparcel' ) ); ?>');">
								<input type="hidden" name="action" value="workparcel_delete_customer">
								<input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>">
								<?php wp_nonce_field( 'workparcel_delete_customer_' . $item->id ); ?>
								<button type="submit" class="button-link-delete"><?php esc_html_e( 'Delete', 'workparcel' ); ?></button>
							</form>
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
