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
			'tracking_title' => 'Track Your Parcel',
			'tracking_description' => 'Enter your tracking number to see shipment progress.',
			'delete_data' => 0,
		) );
	}

	public static function register() {
		register_setting( 'workparcel_settings', 'workparcel_settings', array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $input ) {
		$statuses = Shipment::statuses();
		return array(
			'tracking_page' => absint( $input['tracking_page'] ?? 0 ),
			'default_status' => isset( $statuses[ $input['default_status'] ?? '' ] ) ? $input['default_status'] : 'pending',
			'tracking_prefix' => strtoupper( preg_replace( '/[^A-Z0-9-]/i', '', sanitize_text_field( $input['tracking_prefix'] ?? 'WP' ) ) ) ?: 'WP',
			'company_name' => sanitize_text_field( $input['company_name'] ?? '' ),
			'tracking_title' => sanitize_text_field( $input['tracking_title'] ?? '' ),
			'tracking_description' => sanitize_textarea_field( $input['tracking_description'] ?? '' ),
			'delete_data' => empty( $input['delete_data'] ) ? 0 : 1,
		);
	}

	public static function page() {
		if ( ! current_user_can( 'workparcel_manage_settings' ) ) wp_die( esc_html__( 'You do not have permission to access this page.', 'workparcel' ) );
		$s = self::get();
		$pages = get_pages();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Workparcel Settings', 'workparcel' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'workparcel_settings' ); ?>
				<table class="form-table">
					<tr><th><label for="workparcel_prefix"><?php esc_html_e( 'Tracking prefix', 'workparcel' ); ?></label></th>
						<td><input id="workparcel_prefix" name="workparcel_settings[tracking_prefix]" value="<?php echo esc_attr( $s['tracking_prefix'] ); ?>" class="regular-text"></td></tr>
					<tr><th><label for="workparcel_company"><?php esc_html_e( 'Company name', 'workparcel' ); ?></label></th>
						<td><input id="workparcel_company" name="workparcel_settings[company_name]" value="<?php echo esc_attr( $s['company_name'] ); ?>" class="regular-text"></td></tr>
					<tr><th><label for="workparcel_title"><?php esc_html_e( 'Tracking page title', 'workparcel' ); ?></label></th>
						<td><input id="workparcel_title" name="workparcel_settings[tracking_title]" value="<?php echo esc_attr( $s['tracking_title'] ); ?>" class="regular-text"></td></tr>
					<tr><th><label for="workparcel_desc"><?php esc_html_e( 'Tracking description', 'workparcel' ); ?></label></th>
						<td><textarea id="workparcel_desc" name="workparcel_settings[tracking_description]" rows="3" class="large-text"><?php echo esc_textarea( $s['tracking_description'] ); ?></textarea></td></tr>
					<tr><th><label for="workparcel_page"><?php esc_html_e( 'Tracking page', 'workparcel' ); ?></label></th>
						<td><select id="workparcel_page" name="workparcel_settings[tracking_page]"><option value="0"><?php esc_html_e( 'Select page', 'workparcel' ); ?></option><?php foreach ( $pages as $page ) : ?><option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $s['tracking_page'], $page->ID ); ?>><?php echo esc_html( $page->post_title ); ?></option><?php endforeach; ?></select></td></tr>
					<tr><th><?php esc_html_e( 'Delete data on uninstall', 'workparcel' ); ?></th>
						<td><label><input type="checkbox" name="workparcel_settings[delete_data]" value="1" <?php checked( $s['delete_data'], 1 ); ?>> <?php esc_html_e( 'Delete Workparcel database tables and settings when the plugin is uninstalled.', 'workparcel' ); ?></label></td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
