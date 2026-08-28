/* Workparcel Settings page behaviors: color picker + logo uploader. jQuery is always
 * available in wp-admin, and wp-color-picker itself depends on it, so we rely on it here. */
jQuery( function ( $ ) {
	'use strict';

	if ( $.fn.wpColorPicker ) {
		$( '.wp-workparcel-color-field' ).wpColorPicker();
	}

	var frame;
	var $select = $( '#workparcel_company_logo_select' );
	var $remove = $( '#workparcel_company_logo_remove' );
	var $input = $( '#workparcel_company_logo' );
	var $preview = $( '#workparcel_company_logo_preview' );

	$select.on( 'click', function ( e ) {
		e.preventDefault();

		if ( frame ) {
			frame.open();
			return;
		}

		frame = wp.media( {
			title: workparcelSettings.selectLogoTitle,
			button: { text: workparcelSettings.useLogoText },
			multiple: false,
			library: { type: 'image' },
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
			$input.val( attachment.url );
			$preview.attr( 'src', url ).show();
			$remove.show();
		} );

		frame.open();
	} );

	$remove.on( 'click', function ( e ) {
		e.preventDefault();
		$input.val( '' );
		$preview.hide().attr( 'src', '' );
		$remove.hide();
	} );
} );
