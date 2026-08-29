/* Workparcel media picker. Wires up every .wp-workparcel-media-field on the page —
 * used for the company logo (Settings) and shipment photo/signature fields (Add/Edit Shipment). */
jQuery( function ( $ ) {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.media ) return;

	$( '.wp-workparcel-media-field' ).each( function () {
		var $field = $( this );
		var $select = $field.find( '.wp-workparcel-media-select' );
		var $remove = $field.find( '.wp-workparcel-media-remove' );
		var $input = $field.find( '.wp-workparcel-media-value' );
		var $preview = $field.find( '.wp-workparcel-media-preview' );
		var frame;

		$select.on( 'click', function ( e ) {
			e.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: $select.data( 'title' ) || 'Select image',
				button: { text: $select.data( 'button' ) || 'Use this image' },
				multiple: false,
				library: { type: 'image' },
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var thumb = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
				$input.val( attachment.url ).trigger( 'change' );
				$preview.attr( 'src', thumb ).show();
				$remove.show();
			} );

			frame.open();
		} );

		$remove.on( 'click', function ( e ) {
			e.preventDefault();
			$input.val( '' ).trigger( 'change' );
			$preview.hide().attr( 'src', '' );
			$remove.hide();
		} );
	} );
} );
