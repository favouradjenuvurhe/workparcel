<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wp-workparcel-scan">
	<?php if ( empty( $is_staff ) ) : ?>
	<div id="wp-workparcel-scan-gate" class="wp-workparcel-scan-card">
		<h2><?php esc_html_e( 'Sign in with your Scan ID', 'workparcel' ); ?></h2>
		<p><?php esc_html_e( 'Enter or scan the Scan ID you were given when you were registered.', 'workparcel' ); ?></p>
		<form id="wp-workparcel-scan-gate-form" autocomplete="off">
			<input type="text" id="wp-workparcel-scan-gate-input" placeholder="<?php esc_attr_e( 'Scan or enter your Scan ID', 'workparcel' ); ?>" autofocus>
			<button type="submit"><?php esc_html_e( 'Sign In', 'workparcel' ); ?></button>
		</form>
		<p id="wp-workparcel-scan-gate-error" class="wp-workparcel-scan-error" hidden></p>
	</div>
	<?php endif; ?>

	<div id="wp-workparcel-scan-app" class="wp-workparcel-scan-card" hidden>
		<div id="wp-workparcel-scan-whoami" class="wp-workparcel-scan-whoami"></div>

		<div class="wp-workparcel-scan-modes" role="radiogroup" aria-label="<?php esc_attr_e( 'Scan mode', 'workparcel' ); ?>">
			<label class="wp-workparcel-scan-mode">
				<input type="radio" name="workparcel_scan_mode" value="create" checked>
				<span><strong><?php esc_html_e( 'Create Shipment', 'workparcel' ); ?></strong><br><?php esc_html_e( 'Scan a new tracking label to create a shipment record instantly.', 'workparcel' ); ?></span>
			</label>
			<label class="wp-workparcel-scan-mode">
				<input type="radio" name="workparcel_scan_mode" value="status">
				<span><strong><?php esc_html_e( 'Update Status', 'workparcel' ); ?></strong><br><?php esc_html_e( 'Scan an existing shipment to move it to a new status.', 'workparcel' ); ?></span>
			</label>
			<label class="wp-workparcel-scan-mode">
				<input type="radio" name="workparcel_scan_mode" value="assign">
				<span><strong><?php esc_html_e( 'Assign Driver / Customer', 'workparcel' ); ?></strong><br><?php esc_html_e( 'Scan a shipment, then scan the Scan ID of the driver or customer picking it up.', 'workparcel' ); ?></span>
			</label>
		</div>

		<form id="wp-workparcel-scan-form" autocomplete="off">
			<label for="wp-workparcel-scan-input" class="wp-workparcel-scan-label"><?php esc_html_e( 'Scan or enter a tracking number', 'workparcel' ); ?></label>
			<input type="text" id="wp-workparcel-scan-input" placeholder="<?php esc_attr_e( 'Waiting for scan…', 'workparcel' ); ?>">
		</form>

		<div id="wp-workparcel-scan-result" class="wp-workparcel-scan-result" aria-live="polite"></div>

		<h3><?php esc_html_e( 'Recent scans', 'workparcel' ); ?></h3>
		<ul id="wp-workparcel-scan-log" class="wp-workparcel-scan-log"></ul>
	</div>
</div>
