<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

class Settings {
	public static function get() {
		return wp_parse_args( get_option( 'workparcel_settings', array() ), array(
			'tracking_page' => '',
			'default_status' => 'pending',
			'tracking_prefix' => 'WP',
			'company_name' => get_bloginfo( 'name' ),
			'company_logo' => '',
			'company_email' => get_option( 'admin_email' ),
			'company_phone' => '',
			'company_address' => '',
			'company_website' => home_url( '/' ),
			'tracking_title' => 'Track Your Parcel',
			'tracking_description' => 'Enter your tracking number to see shipment progress.',
			'accent_color' => '#2563eb',
			'notify_sender' => 1,
			'notify_receiver' => 1,
			'notify_admin' => 1,
			'delete_data' => 0,
		) );
	}

	public static function register() {
		register_setting( 'workparcel_settings', 'workparcel_settings', array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $input ) {
		$statuses = Shipment::statuses();
		$accent = isset( $input['accent_color'] ) ? sanitize_hex_color( wp_unslash( $input['accent_color'] ) ) : '';
		return array(
			'tracking_page' => absint( $input['tracking_page'] ?? 0 ),
			'default_status' => isset( $statuses[ $input['default_status'] ?? '' ] ) ? $input['default_status'] : 'pending',
			'tracking_prefix' => strtoupper( preg_replace( '/[^A-Z0-9-]/i', '', sanitize_text_field( $input['tracking_prefix'] ?? 'WP' ) ) ) ?: 'WP',
			'company_name' => sanitize_text_field( $input['company_name'] ?? '' ),
			'company_logo' => isset( $input['company_logo'] ) ? esc_url_raw( wp_unslash( $input['company_logo'] ) ) : '',
			'company_email' => isset( $input['company_email'] ) ? sanitize_email( wp_unslash( $input['company_email'] ) ) : '',
			'company_phone' => sanitize_text_field( $input['company_phone'] ?? '' ),
			'company_address' => sanitize_textarea_field( $input['company_address'] ?? '' ),
			'company_website' => isset( $input['company_website'] ) ? esc_url_raw( wp_unslash( $input['company_website'] ) ) : '',
			'tracking_title' => sanitize_text_field( $input['tracking_title'] ?? '' ),
			'tracking_description' => sanitize_textarea_field( $input['tracking_description'] ?? '' ),
			'accent_color' => $accent ?: '#2563eb',
			'notify_sender' => empty( $input['notify_sender'] ) ? 0 : 1,
			'notify_receiver' => empty( $input['notify_receiver'] ) ? 0 : 1,
			'notify_admin' => empty( $input['notify_admin'] ) ? 0 : 1,
			'delete_data' => empty( $input['delete_data'] ) ? 0 : 1,
		);
	}

	public static function page() {
		if ( ! current_user_can( 'workparcel_manage_settings' ) ) wp_die( esc_html__( 'You do not have permission to access this page.', 'workparcel' ) );
		$s = self::get();
		$pages = get_pages();
		?>
		<div class="wrap wp-workparcel-wrap wp-workparcel-settings-wrap">
			<?php
			$wp_page_title = __( 'Settings', 'workparcel' );
			include WORKPARCEL_DIR . 'admin/views/partials/header.php';
			?>
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php settings_fields( 'workparcel_settings' ); ?>

				<div class="wp-workparcel-tabs-wrap">
					<input type="radio" name="wp_wc_tab" id="wp-wc-tab-general" class="wp-workparcel-tab-radio" checked>
					<input type="radio" name="wp_wc_tab" id="wp-wc-tab-tracking" class="wp-workparcel-tab-radio">
					<input type="radio" name="wp_wc_tab" id="wp-wc-tab-appearance" class="wp-workparcel-tab-radio">
					<input type="radio" name="wp_wc_tab" id="wp-wc-tab-notifications" class="wp-workparcel-tab-radio">
					<input type="radio" name="wp_wc_tab" id="wp-wc-tab-advanced" class="wp-workparcel-tab-radio">

					<div class="wp-workparcel-tabs" role="tablist">
						<label for="wp-wc-tab-general" class="wp-workparcel-tab"><?php esc_html_e( 'General', 'workparcel' ); ?></label>
						<label for="wp-wc-tab-tracking" class="wp-workparcel-tab"><?php esc_html_e( 'Tracking', 'workparcel' ); ?></label>
						<label for="wp-wc-tab-appearance" class="wp-workparcel-tab"><?php esc_html_e( 'Appearance', 'workparcel' ); ?></label>
						<label for="wp-wc-tab-notifications" class="wp-workparcel-tab"><?php esc_html_e( 'Notifications', 'workparcel' ); ?></label>
						<label for="wp-wc-tab-advanced" class="wp-workparcel-tab"><?php esc_html_e( 'Advanced', 'workparcel' ); ?></label>
					</div>

					<div class="wp-workparcel-panel wp-workparcel-tab-panel" id="wp-workparcel-panel-general">
						<h2><?php esc_html_e( 'Company Profile', 'workparcel' ); ?></h2>
						<p class="description"><?php esc_html_e( 'Shown on your public tracking page and in shipment emails.', 'workparcel' ); ?></p>
						<table class="form-table" role="presentation">
							<tr>
								<th><label for="workparcel_company"><?php esc_html_e( 'Company name', 'workparcel' ); ?></label></th>
								<td><input type="text" id="workparcel_company" name="workparcel_settings[company_name]" value="<?php echo esc_attr( $s['company_name'] ); ?>" class="regular-text"></td>
							</tr>
							<tr>
								<th><label for="workparcel_company_logo"><?php esc_html_e( 'Company logo', 'workparcel' ); ?></label></th>
								<td>
									<?php
									$mf_name = 'workparcel_settings[company_logo]';
									$mf_id = 'workparcel_company_logo';
									$mf_value = $s['company_logo'];
									$mf_title = __( 'Select company logo', 'workparcel' );
									$mf_button = __( 'Use this logo', 'workparcel' );
									include WORKPARCEL_DIR . 'admin/views/partials/media-field.php';
									?>
								</td>
							</tr>
							<tr>
								<th><label for="workparcel_company_email"><?php esc_html_e( 'Company email', 'workparcel' ); ?></label></th>
								<td><input type="email" id="workparcel_company_email" name="workparcel_settings[company_email]" value="<?php echo esc_attr( $s['company_email'] ); ?>" class="regular-text"></td>
							</tr>
							<tr>
								<th><label for="workparcel_company_phone"><?php esc_html_e( 'Company phone', 'workparcel' ); ?></label></th>
								<td><input type="text" id="workparcel_company_phone" name="workparcel_settings[company_phone]" value="<?php echo esc_attr( $s['company_phone'] ); ?>" class="regular-text"></td>
							</tr>
							<tr>
								<th><label for="workparcel_company_website"><?php esc_html_e( 'Company website', 'workparcel' ); ?></label></th>
								<td><input type="url" id="workparcel_company_website" name="workparcel_settings[company_website]" value="<?php echo esc_attr( $s['company_website'] ); ?>" class="regular-text"></td>
							</tr>
							<tr>
								<th><label for="workparcel_company_address"><?php esc_html_e( 'Company address', 'workparcel' ); ?></label></th>
								<td><textarea id="workparcel_company_address" name="workparcel_settings[company_address]" rows="3" class="large-text"><?php echo esc_textarea( $s['company_address'] ); ?></textarea></td>
							</tr>
							<tr>
								<th><label for="workparcel_page"><?php esc_html_e( 'Tracking page', 'workparcel' ); ?></label></th>
								<td>
									<select id="workparcel_page" name="workparcel_settings[tracking_page]">
										<option value="0"><?php esc_html_e( 'Select page', 'workparcel' ); ?></option>
										<?php foreach ( $pages as $page ) : ?>
											<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $s['tracking_page'], $page->ID ); ?>><?php echo esc_html( $page->post_title ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'The page where you have placed the [workparcel_tracking] shortcode.', 'workparcel' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<div class="wp-workparcel-panel wp-workparcel-tab-panel" id="wp-workparcel-panel-tracking">
						<table class="form-table" role="presentation">
							<tr>
								<th><label for="workparcel_prefix"><?php esc_html_e( 'Tracking prefix', 'workparcel' ); ?></label></th>
								<td><input type="text" id="workparcel_prefix" name="workparcel_settings[tracking_prefix]" value="<?php echo esc_attr( $s['tracking_prefix'] ); ?>" class="regular-text"></td>
							</tr>
							<tr>
								<th><label for="workparcel_title"><?php esc_html_e( 'Tracking page title', 'workparcel' ); ?></label></th>
								<td><input type="text" id="workparcel_title" name="workparcel_settings[tracking_title]" value="<?php echo esc_attr( $s['tracking_title'] ); ?>" class="regular-text"></td>
							</tr>
							<tr>
								<th><label for="workparcel_desc"><?php esc_html_e( 'Tracking description', 'workparcel' ); ?></label></th>
								<td><textarea id="workparcel_desc" name="workparcel_settings[tracking_description]" rows="3" class="large-text"><?php echo esc_textarea( $s['tracking_description'] ); ?></textarea></td>
							</tr>
						</table>
					</div>

					<div class="wp-workparcel-panel wp-workparcel-tab-panel" id="wp-workparcel-panel-appearance">
						<table class="form-table" role="presentation">
							<tr>
								<th><label for="workparcel_accent_color"><?php esc_html_e( 'Accent color', 'workparcel' ); ?></label></th>
								<td>
									<input type="text" id="workparcel_accent_color" name="workparcel_settings[accent_color]" value="<?php echo esc_attr( $s['accent_color'] ); ?>" class="wp-workparcel-color-field" data-default-color="#2563eb">
									<p class="description"><?php esc_html_e( 'Used for buttons, status highlights, and the progress stepper on the admin and public tracking pages.', 'workparcel' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<div class="wp-workparcel-panel wp-workparcel-tab-panel" id="wp-workparcel-panel-notifications">
						<p class="description"><?php esc_html_e( 'Emails are only sent when an SMTP sender is detected (a plugin like WP Mail SMTP, FluentSMTP, Easy WP SMTP, Post SMTP, or a configured SMTP constant). Otherwise notifications are silently skipped.', 'workparcel' ); ?></p>
						<table class="form-table" role="presentation">
							<tr>
								<th><?php esc_html_e( 'Notify sender', 'workparcel' ); ?></th>
								<td><label><input type="checkbox" name="workparcel_settings[notify_sender]" value="1" <?php checked( $s['notify_sender'], 1 ); ?>> <?php esc_html_e( 'Email the sender when a shipment is created or its status changes.', 'workparcel' ); ?></label></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Notify receiver', 'workparcel' ); ?></th>
								<td><label><input type="checkbox" name="workparcel_settings[notify_receiver]" value="1" <?php checked( $s['notify_receiver'], 1 ); ?>> <?php esc_html_e( 'Email the receiver when a shipment is created or its status changes.', 'workparcel' ); ?></label></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Notify admin', 'workparcel' ); ?></th>
								<td><label><input type="checkbox" name="workparcel_settings[notify_admin]" value="1" <?php checked( $s['notify_admin'], 1 ); ?>> <?php esc_html_e( 'Email the site admin when a shipment is created or its status changes.', 'workparcel' ); ?></label></td>
							</tr>
						</table>
					</div>

					<div class="wp-workparcel-panel wp-workparcel-tab-panel" id="wp-workparcel-panel-advanced">
						<table class="form-table" role="presentation">
							<tr>
								<th><?php esc_html_e( 'Delete data on uninstall', 'workparcel' ); ?></th>
								<td><label><input type="checkbox" name="workparcel_settings[delete_data]" value="1" <?php checked( $s['delete_data'], 1 ); ?>> <?php esc_html_e( 'Delete Workparcel database tables and settings when the plugin is uninstalled.', 'workparcel' ); ?></label></td>
							</tr>
						</table>
					</div>
				</div>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
