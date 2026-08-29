<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wp-workparcel-wrap">
	<?php
	$wp_page_title = $customer ? __( 'Edit Customer', 'workparcel' ) : __( 'Add Customer', 'workparcel' );
	include WORKPARCEL_DIR . 'admin/views/partials/header.php';
	?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="workparcel_save_customer">
		<input type="hidden" name="id" value="<?php echo esc_attr( $customer->id ?? 0 ); ?>">
		<?php wp_nonce_field( 'workparcel_save_customer' ); ?>

		<div class="wp-workparcel-panel" style="max-width:640px;">
			<?php if ( $customer ) : ?>
				<p>
					<label><?php esc_html_e( 'Scan ID', 'workparcel' ); ?></label><br>
					<code class="wp-workparcel-scanid-display"><?php echo esc_html( $customer->scan_id ); ?></code>
					<span class="description"><?php esc_html_e( 'Generated automatically. Use this to sign in to the Scan page or assign shipments to this person.', 'workparcel' ); ?></span>
				</p>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'A unique 11-character Scan ID will be generated automatically when you save.', 'workparcel' ); ?></p>
			<?php endif; ?>

			<p><label for="wp-wc-cust-name"><?php esc_html_e( 'Name', 'workparcel' ); ?><br>
				<input type="text" id="wp-wc-cust-name" class="widefat" name="name" value="<?php echo esc_attr( $customer->name ?? '' ); ?>" required></label></p>

			<p><label for="wp-wc-cust-type"><?php esc_html_e( 'Type', 'workparcel' ); ?><br>
				<select id="wp-wc-cust-type" class="widefat" name="type">
					<?php foreach ( \Workparcel\Customer::types() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $customer->type ?? 'customer', $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select></label></p>

			<div class="wp-workparcel-two">
				<p><label for="wp-wc-cust-email"><?php esc_html_e( 'Email', 'workparcel' ); ?><br>
					<input type="email" id="wp-wc-cust-email" class="widefat" name="email" value="<?php echo esc_attr( $customer->email ?? '' ); ?>"></label></p>
				<p><label for="wp-wc-cust-phone"><?php esc_html_e( 'Phone', 'workparcel' ); ?><br>
					<input type="text" id="wp-wc-cust-phone" class="widefat" name="phone" value="<?php echo esc_attr( $customer->phone ?? '' ); ?>"></label></p>
			</div>

			<p><label for="wp-wc-cust-notes"><?php esc_html_e( 'Notes', 'workparcel' ); ?><br>
				<textarea id="wp-wc-cust-notes" class="widefat" name="notes" rows="3"><?php echo esc_textarea( $customer->notes ?? '' ); ?></textarea></label></p>

			<p><label for="wp-wc-cust-status"><?php esc_html_e( 'Status', 'workparcel' ); ?><br>
				<select id="wp-wc-cust-status" class="widefat" name="status">
					<option value="active" <?php selected( $customer->status ?? 'active', 'active' ); ?>><?php esc_html_e( 'Active', 'workparcel' ); ?></option>
					<option value="inactive" <?php selected( $customer->status ?? 'active', 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'workparcel' ); ?></option>
				</select></label>
				<span class="description"><?php esc_html_e( 'Inactive customers cannot sign in to the Scan page with their Scan ID.', 'workparcel' ); ?></span>
			</p>

			<?php submit_button( $customer ? __( 'Update Customer', 'workparcel' ) : __( 'Create Customer', 'workparcel' ) ); ?>
		</div>
	</form>
</div>
