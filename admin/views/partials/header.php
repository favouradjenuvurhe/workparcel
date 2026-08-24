<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Branded Workparcel admin header.
 * Include with: $wp_page_title (string) and optionally $wp_page_actions (raw HTML string of action buttons).
 */
$wp_page_title   = isset( $wp_page_title ) ? $wp_page_title : __( 'Workparcel', 'workparcel' );
$wp_page_actions = isset( $wp_page_actions ) ? $wp_page_actions : '';
?>
<div class="wp-workparcel-brand-header">
	<div class="wp-workparcel-brand-identity">
		<img class="wp-workparcel-brand-logo" src="<?php echo esc_url( WORKPARCEL_URL . 'admin/images/logo.svg' ); ?>" alt="" width="40" height="40">
		<div class="wp-workparcel-brand-text">
			<span class="wp-workparcel-brand-name"><?php esc_html_e( 'Workparcel', 'workparcel' ); ?></span>
			<span class="wp-workparcel-brand-tagline"><?php echo esc_html( $wp_page_title ); ?></span>
		</div>
	</div>
	<?php if ( $wp_page_actions ) : ?>
		<div class="wp-workparcel-brand-actions"><?php echo wp_kses_post( $wp_page_actions ); ?></div>
	<?php endif; ?>
</div>
