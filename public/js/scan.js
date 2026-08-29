/* Workparcel frontend scan tool. */
jQuery( function ( $ ) {
	'use strict';

	if ( typeof workparcelScanFront === 'undefined' ) return;

	var i18n = workparcelScanFront.i18n;
	var isStaff = !! workparcelScanFront.isStaff;
	var scanId = '';

	var $gate = $( '#wp-workparcel-scan-gate' );
	var $gateForm = $( '#wp-workparcel-scan-gate-form' );
	var $gateInput = $( '#wp-workparcel-scan-gate-input' );
	var $gateError = $( '#wp-workparcel-scan-gate-error' );
	var $app = $( '#wp-workparcel-scan-app' );
	var $whoami = $( '#wp-workparcel-scan-whoami' );

	var $form = $( '#wp-workparcel-scan-form' );
	var $input = $( '#wp-workparcel-scan-input' );
	var $result = $( '#wp-workparcel-scan-result' );
	var $log = $( '#wp-workparcel-scan-log' );
	var pendingLookup = null;

	function post( data ) {
		return $.post( workparcelScanFront.ajaxUrl, $.extend( {
			action: 'workparcel_scan_action',
			nonce: workparcelScanFront.nonce,
			scan_id: scanId,
			is_staff: isStaff ? 1 : 0,
		}, data ) );
	}

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

	function showMessage( html, isError ) {
		$result.html( html ).removeClass( 'is-error is-success' ).addClass( isError ? 'is-error' : 'is-success' );
	}

	function revealApp() {
		$gate.hide();
		$app.prop( 'hidden', false );
		$input.trigger( 'focus' );
	}

	if ( isStaff ) {
		revealApp();
	} else {
		$gateForm.on( 'submit', function ( e ) {
			e.preventDefault();
			var candidate = $.trim( $gateInput.val() );
			if ( ! candidate ) return;

			$.post( workparcelScanFront.ajaxUrl, {
				action: 'workparcel_scan_verify',
				nonce: workparcelScanFront.nonce,
				scan_id: candidate,
			} ).done( function ( res ) {
				if ( res.success ) {
					scanId = candidate.toUpperCase();
					$whoami.html( i18n.welcome + ' <strong>' + res.data.name + '</strong> (' + res.data.type + ') <a id="wp-workparcel-scan-signout">' + i18n.signOut + '</a>' );
					$( '#wp-workparcel-scan-signout' ).on( 'click', function () {
						scanId = '';
						$app.prop( 'hidden', true );
						$gate.show();
						$gateInput.val( '' ).trigger( 'focus' );
					} );
					revealApp();
				} else {
					$gateError.text( res.data.message || i18n.invalidScanId ).prop( 'hidden', false );
					$gateInput.val( '' ).trigger( 'focus' );
				}
			} );
		} );
	}

	function renderStatusPicker( response ) {
		var options = '';
		$.each( response.data.statuses, function ( key, label ) {
			var selected = key === response.data.current_status ? ' selected' : '';
			options += '<option value="' + key + '"' + selected + '>' + label + '</option>';
		} );

		var html = '<div class="wp-workparcel-scan-lookup">' +
			'<p><strong>' + response.data.tracking_number + '</strong> — ' + i18n.currentStatus + ' <em>' + response.data.current_status_label + '</em></p>' +
			'<label>' + i18n.newStatus + '<select id="wp-workparcel-scan-status">' + options + '</select></label>' +
			'<label>' + i18n.location + '<input type="text" id="wp-workparcel-scan-location" placeholder="' + i18n.locationPlaceholder + '"></label>' +
			'<button type="button" id="wp-workparcel-scan-confirm">' + i18n.updateStatus + '</button>' +
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
			'<label>' + i18n.assignScanId + '<input type="text" id="wp-workparcel-scan-assign-id" placeholder="' + i18n.scanIdPlaceholder + '"></label>' +
			'<button type="button" id="wp-workparcel-scan-confirm">' + i18n.assign + '</button>' +
			'</div>';
		showMessage( html, false );
		pendingLookup = { trackingNumber: response.data.tracking_number };
		window.setTimeout( function () { $( '#wp-workparcel-scan-assign-id' ).trigger( 'focus' ); }, 50 );

		$( '#wp-workparcel-scan-assign-id' ).on( 'keydown', function ( e ) {
			if ( 13 === e.which ) {
				e.preventDefault();
				$( '#wp-workparcel-scan-confirm' ).trigger( 'click' );
			}
		} );

		$( '#wp-workparcel-scan-confirm' ).on( 'click', function () {
			var assignScanId = $( '#wp-workparcel-scan-assign-id' ).val();
			post( { mode: 'assign', tracking_number: pendingLookup.trackingNumber, assign_scan_id: assignScanId } ).done( function ( res ) {
				if ( res.success ) {
					showMessage( '<p>' + res.data.message + '</p>', false );
					logEntry( res.data.message, false );
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
					showMessage( '<p>' + res.data.message + '</p>', false );
					logEntry( res.data.tracking_number + ' — ' + i18n.created, false );
				} else {
					showMessage( '<p>' + res.data.message + '</p>', true );
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
} );
