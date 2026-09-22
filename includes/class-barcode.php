<?php
namespace Workparcel;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Barcodes for the invoice and emails.
 *
 *  - render() / svg_128(): a real, scannable Code 128 (subset B) barcode as inline SVG. SVG fills print
 *    normally, unlike CSS background colours, which browsers drop by default.
 *  - bars_html(): the old purely decorative bars, kept for email clients (they strip SVG) and as a
 *    fallback when a tracking number contains characters Code 128-B cannot encode.
 */
class Barcode {

	/** Bar/space widths (in modules) for Code 128 symbols 0-105, then the stop pattern (106). */
	private static function patterns() {
		return array(
			'212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312',
			'132212', '221213', '221312', '231212', '112232', '122132', '122231', '113222',
			'123122', '123221', '223211', '221132', '221231', '213212', '223112', '312131',
			'311222', '321122', '321221', '312212', '322112', '322211', '212123', '212321',
			'232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
			'231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121',
			'313121', '211331', '231131', '213113', '213311', '213131', '311123', '311321',
			'331121', '312113', '312311', '332111', '314111', '221411', '431111', '111224',
			'111422', '121124', '121421', '141122', '141221', '112214', '112412', '122114',
			'122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
			'111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112',
			'421211', '212141', '214121', '412121', '111143', '111341', '131141', '114113',
			'114311', '411113', '411311', '113141', '114131', '311141', '411131', '211412',
			'211214', '211232', '2331112',
		);
	}

	/** Scannable barcode if possible, otherwise the decorative bars. Returns trusted, already-escaped HTML. */
	public static function render( $text, $height = 56 ) {
		$svg = self::svg_128( $text, $height );
		return '' !== $svg ? $svg : self::bars_html( $text, $height );
	}

	/** Code 128-B as inline SVG, or '' when the text is empty, too long, or not printable ASCII. */
	public static function svg_128( $text, $height = 56, $module = 2 ) {
		$text = (string) $text;
		$len = strlen( $text );
		if ( 0 === $len || $len > 40 ) return '';

		$codes = array( 104 ); // Start B
		$sum = 104;
		for ( $i = 0; $i < $len; $i++ ) {
			$ord = ord( $text[ $i ] );
			if ( $ord < 32 || $ord > 126 ) return '';
			$value = $ord - 32;
			$codes[] = $value;
			$sum += $value * ( $i + 1 );
		}
		$codes[] = $sum % 103; // checksum
		$codes[] = 106;        // Stop

		$patterns = self::patterns();
		$quiet = 10 * $module;
		$x = $quiet;
		$bars = '';
		foreach ( $codes as $code ) {
			$pattern = $patterns[ $code ];
			$n = strlen( $pattern );
			for ( $j = 0; $j < $n; $j++ ) {
				$w = (int) $pattern[ $j ] * $module;
				if ( 0 === $j % 2 ) {
					$bars .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . (int) $height . '"/>';
				}
				$x += $w;
			}
		}
		$width = $x + $quiet;
		$total_height = (int) $height + 16;

		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $total_height . '" width="' . $width . '" height="' . $total_height . '" style="display:block;max-width:100%;height:auto;" role="img" aria-label="' . esc_attr( sprintf( /* translators: %s: tracking number */ __( 'Barcode for %s', 'workparcel' ), $text ) ) . '">'
			. '<rect width="' . $width . '" height="' . $total_height . '" fill="#ffffff"/>'
			. '<g fill="#111827" shape-rendering="crispEdges">' . $bars . '</g>'
			. '<text x="' . ( $width / 2 ) . '" y="' . ( (int) $height + 13 ) . '" text-anchor="middle" font-family="monospace" font-size="12" fill="#111827">' . esc_html( $text ) . '</text>'
			. '</svg>';
	}

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
