/* Workparcel Settings page: color picker. The company-logo media picker is handled
 * generically by admin/js/media-field.js (loaded alongside this file). */
jQuery( function ( $ ) {
	'use strict';

	if ( $.fn.wpColorPicker ) {
		$( '.wp-workparcel-color-field' ).wpColorPicker();
	}
} );
