<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Renders a barcode-style visual next to a tracking number on the invoice and in emails.
 *
 * This is intentionally decorative, not a standards-compliant Code128/Code39 encoding —
 * it's built from simple HTML bars so it renders identically in the admin invoice page,
 * a printed page, and in email clients (including ones that strip webfonts/JS/canvas).
 * The actual scan-to-action workflow (Workparcel → Scan) works from whatever barcode
 * label you're already using operationally; it doesn't depend on this graphic at all.
 */
class Barcode {

	public static function bars_html( $text, $height = 56 ) {
		$hash = md5( strtoupper( $text ) );
		$bars = '';
		for ( $i = 0; $i < strlen( $hash ); $i++ ) {
			$val = hexdec( $hash[ $i ] );
			$width = 1 + ( $val % 4 ); // 1-4px
			$is_bar = ( $i % 2 === 0 );
			$color = $is_bar ? '#1f2937' : '#ffffff';
			$bars .= sprintf(
				'<span style="display:inline-block;width:%dpx;height:%dpx;background:%s;"></span>',
				$width,
				$height,
				$color
			);
		}
		return '<span style="display:inline-block;white-space:nowrap;line-height:0;font-size:0;">' . $bars . '</span>';
	}
}
