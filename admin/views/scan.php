<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wp-workparcel-wrap">
	<?php
	$wp_page_title = __( 'Scan', 'workparcel' );
	include WORKPARCEL_DIR . 'admin/views/partials/header.php';
	?>

	<div class="wp-workparcel-panel wp-workparcel-scan-panel">
		<p class="description"><?php esc_html_e( 'Connect a USB or Bluetooth barcode scanner — it types like a keyboard, so just scan a label and this page does the rest. Works with a screen tap too if you don\'t have a scanner handy.', 'workparcel' ); ?></p>

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
				<span><strong><?php esc_html_e( 'Assign Driver / Customer', 'workparcel' ); ?></strong><br><?php esc_html_e( 'Scan a shipment to assign it to a driver or the customer picking it up.', 'workparcel' ); ?></span>
			</label>
		</div>

		<form id="wp-workparcel-scan-form" autocomplete="off">
			<label for="wp-workparcel-scan-input" class="wp-workparcel-scan-label"><?php esc_html_e( 'Scan or enter a tracking number', 'workparcel' ); ?></label>
			<input type="text" id="wp-workparcel-scan-input" class="wp-workparcel-scan-input" placeholder="<?php esc_attr_e( 'Waiting for scan…', 'workparcel' ); ?>" autofocus>
		</form>

		<div id="wp-workparcel-scan-result" class="wp-workparcel-scan-result" aria-live="polite"></div>

		<h3><?php esc_html_e( 'Recent scans', 'workparcel' ); ?></h3>
		<ul id="wp-workparcel-scan-log" class="wp-workparcel-scan-log"></ul>
	</div>
</div>
