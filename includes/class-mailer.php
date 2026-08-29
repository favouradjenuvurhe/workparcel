<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sends shipment notification emails. Every send is gated behind smtp_configured() —
 * on a site with no SMTP plugin/config, WordPress's default mail() transport is
 * unreliable and frequently silently dropped by hosts, so we skip sending entirely
 * rather than give a false sense that notifications went out.
 */
class Mailer {

	public static function init() {
		add_action( 'workparcel_shipment_created', array( __CLASS__, 'on_created' ) );
		add_action( 'workparcel_status_changed', array( __CLASS__, 'on_status_changed' ), 10, 3 );
	}

	/**
	 * Detect whether an SMTP sender is likely configured. Checked in order of
	 * reliability: known SMTP plugin classes, an SMTP_HOST constant, then a
	 * generic phpmailer_init listener as a catch-all for anything else.
	 */
	public static function smtp_configured() {
		$known_plugin_classes = array(
			'WPMailSMTP\\Core',                               // WP Mail SMTP
			'EasyWPSMTP\\Core',                                // Easy WP SMTP
			'FluentMail\\App\\Hooks\\Handlers\\MailHandler',   // FluentSMTP
			'PostmanOptions',                                  // Post SMTP / Postman
			'Wp_Smtp',                                         // WP SMTP
		);
		foreach ( $known_plugin_classes as $class ) {
			if ( class_exists( $class ) ) return true;
		}

		if ( defined( 'SMTP_HOST' ) && SMTP_HOST ) return true;

		// Most SMTP plugins configure PHPMailer through this filter; treat any listener as a signal.
		if ( has_action( 'phpmailer_init' ) ) return true;

		/**
		 * Filter whether Workparcel should treat SMTP as configured.
		 * Lets a site owner force-enable (or disable) email sending regardless of the built-in detection.
		 */
		return (bool) apply_filters( 'workparcel_smtp_configured', false );
	}

	public static function on_created( $shipment_id ) {
		$shipment = Shipment::get( $shipment_id );
		if ( ! $shipment ) return;
		self::notify( $shipment, 'created' );
	}

	public static function on_status_changed( $shipment_id, $old_status, $new_status ) {
		if ( $old_status === $new_status ) return;
		$shipment = Shipment::get( $shipment_id );
		if ( ! $shipment ) return;
		self::notify( $shipment, 'status_changed' );
	}

	private static function notify( $shipment, $context ) {
		if ( ! self::smtp_configured() ) return;

		$settings = Settings::get();
		if ( empty( $settings['notify_sender'] ) && empty( $settings['notify_receiver'] ) && empty( $settings['notify_admin'] ) ) return;

		$statuses = Shipment::statuses();
		$status_label = $statuses[ $shipment->status ] ?? $shipment->status;
		$company = $settings['company_name'] ?: get_bloginfo( 'name' );

		$subject = 'created' === $context
			? sprintf( __( '[%1$s] Shipment created — %2$s', 'workparcel' ), $company, $shipment->tracking_number )
			: sprintf( __( '[%1$s] Shipment update — %2$s is now %3$s', 'workparcel' ), $company, $shipment->tracking_number, $status_label );

		$body = self::build_email_html( $shipment, $settings, $context );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		if ( ! empty( $settings['company_email'] ) && is_email( $settings['company_email'] ) ) {
			$headers[] = 'From: ' . $company . ' <' . $settings['company_email'] . '>';
		}

		$recipients = array();
		if ( ! empty( $settings['notify_sender'] ) && ! empty( $shipment->sender_email ) && is_email( $shipment->sender_email ) ) {
			$recipients[] = $shipment->sender_email;
		}
		if ( ! empty( $settings['notify_receiver'] ) && ! empty( $shipment->receiver_email ) && is_email( $shipment->receiver_email ) ) {
			$recipients[] = $shipment->receiver_email;
		}
		if ( ! empty( $settings['notify_admin'] ) ) {
			$admin_email = get_option( 'admin_email' );
			if ( $admin_email ) $recipients[] = $admin_email;
		}
		$recipients = array_unique( array_filter( $recipients ) );
		if ( ! $recipients ) return;

		foreach ( $recipients as $to ) {
			wp_mail( $to, $subject, $body, $headers );
		}
	}

	private static function build_email_html( $shipment, $settings, $context ) {
		$statuses     = Shipment::statuses();
		$status_label = $statuses[ $shipment->status ] ?? $shipment->status;
		$accent       = sanitize_hex_color( $settings['accent_color'] ?? '' ) ?: '#2563eb';
		$company      = $settings['company_name'] ?: get_bloginfo( 'name' );
		$events       = array_slice( Tracking::events( $shipment->id ), 0, 5 );

		$track_url = '';
		if ( ! empty( $settings['tracking_page'] ) ) {
			$permalink = get_permalink( (int) $settings['tracking_page'] );
			if ( $permalink ) $track_url = add_query_arg( 'workparcel_tracking', $shipment->tracking_number, $permalink );
		}

		ob_start();
		?>
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 0;">
<tr><td align="center">
<table role="presentation" width="640" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;max-width:100%;">
	<tr>
		<td style="background:<?php echo esc_attr( $accent ); ?>;padding:24px 28px;">
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td style="vertical-align:middle;">
						<?php if ( ! empty( $settings['company_logo'] ) ) : ?>
							<img src="<?php echo esc_url( $settings['company_logo'] ); ?>" alt="<?php echo esc_attr( $company ); ?>" height="32" style="display:block;margin-bottom:8px;max-height:32px;">
						<?php endif; ?>
						<span style="color:#ffffff;font-size:19px;font-weight:bold;font-family:Arial,Helvetica,sans-serif;"><?php echo esc_html( $company ); ?></span>
					</td>
					<td style="vertical-align:middle;text-align:right;">
						<span style="display:inline-block;background:rgba(255,255,255,0.18);color:#ffffff;font-size:12px;font-weight:bold;padding:6px 12px;border-radius:999px;font-family:Arial,Helvetica,sans-serif;"><?php echo esc_html( $status_label ); ?></span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="padding:28px;">
			<h2 style="margin:0 0 4px;color:#0f172a;font-family:Arial,Helvetica,sans-serif;">
				<?php echo esc_html( 'created' === $context ? __( 'Your shipment has been created', 'workparcel' ) : __( 'Your shipment status has been updated', 'workparcel' ) ); ?>
			</h2>
			<p style="color:#64748b;margin:0 0 6px;font-size:13px;font-family:Arial,Helvetica,sans-serif;text-transform:uppercase;letter-spacing:.05em;">
				<?php esc_html_e( 'Tracking Number', 'workparcel' ); ?>
			</p>
			<p style="color:#0f172a;margin:0 0 10px;font-size:20px;font-weight:bold;font-family:Arial,Helvetica,sans-serif;letter-spacing:.03em;">
				<?php echo esc_html( $shipment->tracking_number ); ?>
			</p>
			<p style="margin:0 0 22px;"><?php echo Barcode::bars_html( $shipment->tracking_number, 40 ); ?></p>

			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#334155;font-family:Arial,Helvetica,sans-serif;">
				<tr>
					<td style="width:50%;vertical-align:top;padding-bottom:16px;">
						<strong><?php esc_html_e( 'Sender', 'workparcel' ); ?></strong><br>
						<?php echo esc_html( $shipment->sender_name ?: '—' ); ?><br>
						<?php echo esc_html( $shipment->sender_phone ?: '' ); ?>
					</td>
					<td style="width:50%;vertical-align:top;padding-bottom:16px;">
						<strong><?php esc_html_e( 'Recipient', 'workparcel' ); ?></strong><br>
						<?php echo esc_html( $shipment->receiver_name ?: '—' ); ?><br>
						<?php echo esc_html( $shipment->receiver_phone ?: '' ); ?>
					</td>
				</tr>
				<tr>
					<td style="vertical-align:top;padding-bottom:16px;"><strong><?php esc_html_e( 'Origin', 'workparcel' ); ?></strong><br><?php echo esc_html( $shipment->origin ?: '—' ); ?></td>
					<td style="vertical-align:top;padding-bottom:16px;"><strong><?php esc_html_e( 'Destination', 'workparcel' ); ?></strong><br><?php echo esc_html( $shipment->destination ?: '—' ); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:top;padding-bottom:16px;"><strong><?php esc_html_e( 'Parcel Type', 'workparcel' ); ?></strong><br><?php echo esc_html( $shipment->parcel_type ?: '—' ); ?></td>
					<td style="vertical-align:top;padding-bottom:16px;"><strong><?php esc_html_e( 'Weight', 'workparcel' ); ?></strong><br><?php echo esc_html( $shipment->weight > 0 ? $shipment->weight : '—' ); ?></td>
				</tr>
				<?php if ( $shipment->container_no || $shipment->driver_name ) : ?>
				<tr>
					<?php if ( $shipment->container_no ) : ?>
						<td style="vertical-align:top;padding-bottom:16px;"><strong><?php esc_html_e( 'Container No.', 'workparcel' ); ?></strong><br><?php echo esc_html( $shipment->container_no ); ?></td>
					<?php else : ?>
						<td></td>
					<?php endif; ?>
					<?php if ( $shipment->driver_name ) : ?>
						<td style="vertical-align:top;padding-bottom:16px;"><strong><?php esc_html_e( 'Assigned To', 'workparcel' ); ?></strong><br><?php echo esc_html( $shipment->driver_name ); ?></td>
					<?php else : ?>
						<td></td>
					<?php endif; ?>
				</tr>
				<?php endif; ?>
				<?php if ( $shipment->estimated_delivery ) : ?>
				<tr>
					<td colspan="2" style="vertical-align:top;padding-bottom:16px;"><strong><?php esc_html_e( 'Estimated delivery', 'workparcel' ); ?></strong><br><?php echo esc_html( $shipment->estimated_delivery ); ?></td>
				</tr>
				<?php endif; ?>
			</table>

			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e2e8f0;padding-top:14px;font-family:Arial,Helvetica,sans-serif;">
				<tr>
					<td style="font-size:14px;color:#64748b;"><?php esc_html_e( 'Shipping Fee', 'workparcel' ); ?></td>
					<td style="font-size:16px;color:#0f172a;font-weight:bold;text-align:right;"><?php echo esc_html( number_format_i18n( (float) $shipment->shipping_fee, 2 ) ); ?></td>
				</tr>
			</table>

			<?php if ( $shipment->pod_signature || $shipment->pod_photo ) : ?>
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:22px;">
				<tr>
					<td colspan="2" style="font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:.05em;font-family:Arial,Helvetica,sans-serif;padding-bottom:8px;"><?php esc_html_e( 'Proof of Delivery', 'workparcel' ); ?></td>
				</tr>
				<tr>
					<?php if ( $shipment->pod_signature ) : ?>
						<td style="width:50%;vertical-align:top;"><img src="<?php echo esc_url( $shipment->pod_signature ); ?>" alt="<?php esc_attr_e( 'Signature', 'workparcel' ); ?>" style="max-height:80px;max-width:100%;border:1px solid #e2e8f0;border-radius:6px;padding:6px;"></td>
					<?php else : ?>
						<td style="width:50%;"></td>
					<?php endif; ?>
					<?php if ( $shipment->pod_photo ) : ?>
						<td style="width:50%;vertical-align:top;"><img src="<?php echo esc_url( $shipment->pod_photo ); ?>" alt="<?php esc_attr_e( 'Delivery Photo', 'workparcel' ); ?>" style="max-height:110px;max-width:100%;border-radius:6px;"></td>
					<?php else : ?>
						<td style="width:50%;"></td>
					<?php endif; ?>
				</tr>
			</table>
			<?php endif; ?>

			<?php if ( $events ) : ?>
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;font-family:Arial,Helvetica,sans-serif;">
				<tr>
					<td style="font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:.05em;padding-bottom:8px;"><?php esc_html_e( 'Recent Activity', 'workparcel' ); ?></td>
				</tr>
				<?php foreach ( $events as $event ) : ?>
				<tr>
					<td style="font-size:13px;color:#334155;padding:6px 0;border-top:1px solid #f1f5f9;">
						<strong><?php echo esc_html( $statuses[ $event->status ] ?? $event->status ); ?></strong>
						<?php if ( $event->location ) : ?> — <?php echo esc_html( $event->location ); ?><?php endif; ?>
						<span style="color:#94a3b8;"> · <?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $event->event_date ) ) ); ?></span>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php endif; ?>

			<?php if ( $track_url ) : ?>
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;">
				<tr><td align="center">
					<a href="<?php echo esc_url( $track_url ); ?>" style="display:inline-block;background:<?php echo esc_attr( $accent ); ?>;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:bold;font-family:Arial,Helvetica,sans-serif;font-size:14px;">
						<?php esc_html_e( 'Track Shipment', 'workparcel' ); ?>
					</a>
				</td></tr>
			</table>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td style="padding:16px 28px;background:#f8fafc;font-size:12px;color:#94a3b8;font-family:Arial,Helvetica,sans-serif;">
			<?php
			$footer = array( $company );
			if ( ! empty( $settings['company_address'] ) ) $footer[] = $settings['company_address'];
			if ( ! empty( $settings['company_phone'] ) ) $footer[] = $settings['company_phone'];
			echo esc_html( implode( ' · ', array_filter( $footer ) ) );
			?>
		</td>
	</tr>
</table>
</td></tr>
</table>
</body>
</html>
		<?php
		return ob_get_clean();
	}
}
