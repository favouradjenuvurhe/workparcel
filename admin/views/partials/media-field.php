<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Reusable image-picker field. Include with these variables set:
 * $mf_name    string  form field name (e.g. "workparcel_settings[company_logo]")
 * $mf_id      string  base id for this field's elements, must be unique on the page
 * $mf_value   string  current image URL, or ''
 * $mf_title   string  media modal title
 * $mf_button  string  media modal confirm-button text
 */
$mf_name   = $mf_name ?? '';
$mf_id     = $mf_id ?? 'wp-workparcel-media-' . wp_generate_password( 6, false );
$mf_value  = $mf_value ?? '';
$mf_title  = $mf_title ?? __( 'Select image', 'workparcel' );
$mf_button = $mf_button ?? __( 'Use this image', 'workparcel' );
?>
<div class="wp-workparcel-media-field">
	<img class="wp-workparcel-media-preview" id="<?php echo esc_attr( $mf_id ); ?>_preview" src="<?php echo esc_url( $mf_value ); ?>" alt="" style="<?php echo $mf_value ? '' : 'display:none;'; ?>">
	<input type="hidden" class="wp-workparcel-media-value" id="<?php echo esc_attr( $mf_id ); ?>" name="<?php echo esc_attr( $mf_name ); ?>" value="<?php echo esc_attr( $mf_value ); ?>">
	<button type="button" class="button wp-workparcel-media-select" data-title="<?php echo esc_attr( $mf_title ); ?>" data-button="<?php echo esc_attr( $mf_button ); ?>"><?php esc_html_e( 'Select Image', 'workparcel' ); ?></button>
	<button type="button" class="button wp-workparcel-media-remove" style="<?php echo $mf_value ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'workparcel' ); ?></button>
</div>
