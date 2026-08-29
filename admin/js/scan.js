/* Workparcel barcode scan tool. A physical barcode scanner behaves like a keyboard —
 * it types the encoded text into whatever field has focus, then sends Enter — so this
 * just needs to keep a text field focused and react to its form submission. */
jQuery( function ( $ ) {
	'use strict';

	if ( typeof workparcelScan === 'undefined' ) return;

	var $form = $( '#wp-workparcel-scan-form' );
	var $input = $( '#wp-workparcel-scan-input' );
	var $result = $( '#wp-workparcel-scan-result' );
	var $log = $( '#wp-workparcel-scan-log' );
	var pendingLookup = null; // { trackingNumber } once a status/assign scan has resolved to a shipment awaiting confirmation

	function currentMode() {
		return $( 'input[name="workparcel_scan_mode"]:checked' ).val();
	}

	function refocus() {
		$input.val( '' );
		window.setTimeout( function () {
			$input.trigger( 'focus' );
		}, 50 );
	}

	function logEntry( text, isError ) {
		var $li = $( '<li>' ).text( text ).addClass( isError ? 'is-error' : 'is-success' );
		$log.prepend( $li );
		while ( $log.children().length > 8 ) {
			$log.children().last().remove();
		}
	}

	function post( data ) {
		return $.post( workparcelScan.ajaxUrl, $.extend( { action: 'workparcel_scan', nonce: workparcelScan.nonce }, data ) );
	}

	function showMessage( html, isError ) {
		$result.html( html ).removeClass( 'is-error is-success' ).addClass( isError ? 'is-error' : 'is-success' );
	}

	function renderStatusPicker( response ) {
		var options = '';
		$.each( response.data.statuses, function ( key, label ) {
			var selected = key === response.data.current_status ? ' selected' : '';
			options += '<option value="' + key + '"' + selected + '>' + label + '</option>';
		} );

		var html = '<div class="wp-workparcel-scan-lookup">' +
			'<p><strong>' + response.data.tracking_number + '</strong> — ' + workparcelScan.i18n.currentStatus + ' <em>' + response.data.current_status_label + '</em></p>' +
			'<label>' + workparcelScan.i18n.newStatus + '<select id="wp-workparcel-scan-status">' + options + '</select></label> ' +
			'<label>' + workparcelScan.i18n.location + '<input type="text" id="wp-workparcel-scan-location" placeholder="' + workparcelScan.i18n.locationPlaceholder + '"></label> ' +
			'<button type="button" class="button button-primary" id="wp-workparcel-scan-confirm">' + workparcelScan.i18n.updateStatus + '</button>' +
			'</div>';
		showMessage( html, false );
		pendingLookup = { trackingNumber: response.data.tracking_number };

		$( '#wp-workparcel-scan-confirm' ).on( 'click', function () {
			var status = $( '#wp-workparcel-scan-status' ).val();
			var location = $( '#wp-workparcel-scan-location' ).val();
			post( { mode: 'status', tracking_number: pendingLookup.trackingNumber, status: status, location: location } ).done( function ( res ) {
				if ( res.success ) {
					showMessage( '<p>' + res.data.message + '</p>', false );
					logEntry( res.data.tracking_number + ' → ' + $( '#wp-workparcel-scan-status option:selected' ).text(), false );
				} else {
					showMessage( '<p>' + res.data.message + '</p>', true );
					logEntry( res.data.message, true );
				}
				pendingLookup = null;
				refocus();
			} );
		} );
	}

	function renderAssignPicker( response ) {
		var html = '<div class="wp-workparcel-scan-lookup">' +
			'<p><strong>' + response.data.tracking_number + '</strong></p>' +
			'<label>' + workparcelScan.i18n.driverName + '<input type="text" id="wp-workparcel-scan-driver" value="' + ( response.data.current_driver || '' ) + '" placeholder="' + workparcelScan.i18n.driverPlaceholder + '"></label> ' +
			'<button type="button" class="button button-primary" id="wp-workparcel-scan-confirm">' + workparcelScan.i18n.assign + '</button>' +
			'</div>';
		showMessage( html, false );
		pendingLookup = { trackingNumber: response.data.tracking_number };

		$( '#wp-workparcel-scan-confirm' ).on( 'click', function () {
			var driver = $( '#wp-workparcel-scan-driver' ).val();
			post( { mode: 'assign', tracking_number: pendingLookup.trackingNumber, driver_name: driver } ).done( function ( res ) {
				if ( res.success ) {
					showMessage( '<p>' + res.data.message + '</p>', false );
					logEntry( res.data.tracking_number + ' → ' + driver, false );
				} else {
					showMessage( '<p>' + res.data.message + '</p>', true );
					logEntry( res.data.message, true );
				}
				pendingLookup = null;
				refocus();
			} );
		} );
	}

	$form.on( 'submit', function ( e ) {
		e.preventDefault();
		var tracking = $.trim( $input.val() );
		if ( ! tracking ) return;

		var mode = currentMode();

		if ( 'create' === mode ) {
			post( { mode: 'create', tracking_number: tracking } ).done( function ( res ) {
				if ( res.success ) {
					showMessage( '<p>' + res.data.message + ' <a href="' + res.data.edit_url + '">' + workparcelScan.i18n.editShipment + '</a></p>', false );
					logEntry( res.data.tracking_number + ' — ' + workparcelScan.i18n.created, false );
				} else {
					var extra = res.data.edit_url ? ' <a href="' + res.data.edit_url + '">' + workparcelScan.i18n.editShipment + '</a>' : '';
					showMessage( '<p>' + res.data.message + extra + '</p>', true );
					logEntry( tracking + ' — ' + res.data.message, true );
				}
				refocus();
			} );
			return;
		}

		if ( 'status' === mode ) {
			post( { mode: 'status', tracking_number: tracking } ).done( function ( res ) {
				if ( res.success && res.data.lookup ) {
					renderStatusPicker( res );
				} else if ( ! res.success ) {
					showMessage( '<p>' + res.data.message + '</p>', true );
					logEntry( tracking + ' — ' + res.data.message, true );
					refocus();
				}
			} );
			return;
		}

		if ( 'assign' === mode ) {
			post( { mode: 'assign', tracking_number: tracking } ).done( function ( res ) {
				if ( res.success && res.data.lookup ) {
					renderAssignPicker( res );
				} else if ( ! res.success ) {
					showMessage( '<p>' + res.data.message + '</p>', true );
					logEntry( tracking + ' — ' + res.data.message, true );
					refocus();
				}
			} );
			return;
		}
	} );

	$( 'input[name="workparcel_scan_mode"]' ).on( 'change', function () {
		pendingLookup = null;
		$result.empty();
		refocus();
	} );

	// Keep the scan field focused even if the operator clicks elsewhere on the page
	// (but not while they're actively using the status/assign confirmation controls).
	$( document ).on( 'click', function ( e ) {
		if ( $( e.target ).closest( '.wp-workparcel-scan-lookup' ).length ) return;
		if ( $( e.target ).is( 'input[name="workparcel_scan_mode"]' ) ) return;
		window.setTimeout( function () {
			if ( ! pendingLookup ) $input.trigger( 'focus' );
		}, 10 );
	} );

	$input.trigger( 'focus' );
} );
