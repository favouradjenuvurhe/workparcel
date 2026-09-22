/* Workparcel frontend scan tool. */
jQuery( function ( $ ) {
	'use strict';

	if ( typeof workparcelScanFront === 'undefined' ) return;

	var i18n = workparcelScanFront.i18n;
	var isStaff = !! workparcelScanFront.isStaff;
	var nonce = workparcelScanFront.nonce;
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

	/* Everything that comes back from the server is escaped before it is put into markup. */
	function esc( value ) {
		return $( '<div>' ).text( value == null ? '' : String( value ) ).html().replace( /"/g, '&quot;' ).replace( /'/g, '&#39;' );
	}

	/* Page caches freeze the nonce printed in the page; ask for a fresh one. */
	function refreshNonce() {
		return $.post( workparcelScanFront.ajaxUrl, { action: 'workparcel_scan_nonce' } ).done( function ( res ) {
			if ( res && res.success && res.data && res.data.nonce ) nonce = res.data.nonce;
		} );
	}

	/* Always resolves with { success, data } — network/server errors become a visible error message instead of silence. */
	function request( data, retried ) {
		var d = $.Deferred();
		$.post( workparcelScanFront.ajaxUrl, $.extend( { nonce: nonce }, data ) ).done( function ( res ) {
			d.resolve( res );
		} ).fail( function ( xhr ) {
			if ( 403 === xhr.status && ! retried ) {
				refreshNonce().always( function () {
					request( data, true ).done( function ( res ) { d.resolve( res ); } );
				} );
				return;
			}
			d.resolve( { success: false, data: { message: i18n.requestFailed } } );
		} );
		return d.promise();
	}

	function post( data ) {
		return request( $.extend( {
			action: 'workparcel_scan_action',
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

	function showText( text, isError ) {
		showMessage( '<p>' + esc( text ) + '</p>', isError );
	}

	function revealApp() {
		$gate.hide();
		$app.prop( 'hidden', false );
		$input.trigger( 'focus' );
	}

	refreshNonce();

	if ( isStaff ) {
		revealApp();
	} else {
		$gateForm.on( 'submit', function ( e ) {
			e.preventDefault();
			var candidate = $.trim( $gateInput.val() );
			if ( ! candidate ) return;

			request( { action: 'workparcel_scan_verify', scan_id: candidate } ).done( function ( res ) {
				if ( res.success ) {
					scanId = candidate.toUpperCase();
					$gateError.prop( 'hidden', true );
					$whoami.html( esc( i18n.welcome ) + ' <strong>' + esc( res.data.name ) + '</strong> (' + esc( res.data.type ) + ') <button type="button" class="wp-workparcel-scan-signout">' + esc( i18n.signOut ) + '</button>' );
					$whoami.find( '.wp-workparcel-scan-signout' ).on( 'click', function () {
						scanId = '';
						$app.prop( 'hidden', true );
						$result.empty();
						$gate.show();
						$gateInput.val( '' ).trigger( 'focus' );
					} );
					revealApp();
				} else {
					$gateError.text( ( res.data && res.data.message ) || i18n.invalidScanId ).prop( 'hidden', false );
					$gateInput.val( '' ).trigger( 'focus' );
				}
			} );
		} );
	}

	function renderStatusPicker( response ) {
		var options = '';
		$.each( response.data.statuses, function ( key, label ) {
			var selected = key === response.data.current_status ? ' selected' : '';
			options += '<option value="' + esc( key ) + '"' + selected + '>' + esc( label ) + '</option>';
		} );

		var html = '<div class="wp-workparcel-scan-lookup">' +
			'<p><strong>' + esc( response.data.tracking_number ) + '</strong> — ' + esc( i18n.currentStatus ) + ' <em>' + esc( response.data.current_status_label ) + '</em></p>' +
			'<label>' + esc( i18n.newStatus ) + '<select id="wp-workparcel-scan-status">' + options + '</select></label>' +
			'<label>' + esc( i18n.location ) + '<input type="text" id="wp-workparcel-scan-location" placeholder="' + esc( i18n.locationPlaceholder ) + '"></label>' +
			'<button type="button" id="wp-workparcel-scan-confirm">' + esc( i18n.updateStatus ) + '</button>' +
			'</div>';
		showMessage( html, false );
		pendingLookup = { trackingNumber: response.data.tracking_number };

		$( '#wp-workparcel-scan-confirm' ).on( 'click', function () {
			var status = $( '#wp-workparcel-scan-status' ).val();
			var location = $( '#wp-workparcel-scan-location' ).val();
			post( { mode: 'status', tracking_number: pendingLookup.trackingNumber, status: status, location: location } ).done( function ( res ) {
				if ( res.success ) {
					showText( res.data.message, false );
					logEntry( res.data.tracking_number + ' → ' + $( '#wp-workparcel-scan-status option:selected' ).text(), false );
				} else {
					showText( res.data.message, true );
					logEntry( res.data.message, true );
				}
				pendingLookup = null;
				refocus();
			} );
		} );
	}

	function renderAssignPicker( response ) {
		var html = '<div class="wp-workparcel-scan-lookup">' +
			'<p><strong>' + esc( response.data.tracking_number ) + '</strong></p>' +
			'<label>' + esc( i18n.assignScanId ) + '<input type="text" id="wp-workparcel-scan-assign-id" placeholder="' + esc( i18n.scanIdPlaceholder ) + '"></label>' +
			'<button type="button" id="wp-workparcel-scan-confirm">' + esc( i18n.assign ) + '</button>' +
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
					showText( res.data.message, false );
					logEntry( res.data.message, false );
				} else {
					showText( res.data.message, true );
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
					showText( res.data.message, false );
					logEntry( res.data.tracking_number + ' — ' + i18n.created, false );
				} else {
					showText( res.data.message, true );
					logEntry( tracking + ' — ' + res.data.message, true );
				}
				refocus();
			} );
			return;
		}

		if ( 'status' === mode || 'assign' === mode ) {
			post( { mode: mode, tracking_number: tracking } ).done( function ( res ) {
				if ( res.success && res.data.lookup ) {
					if ( 'status' === mode ) {
						renderStatusPicker( res );
					} else {
						renderAssignPicker( res );
					}
				} else if ( ! res.success ) {
					showText( res.data.message, true );
					logEntry( tracking + ' — ' + res.data.message, true );
					refocus();
				}
			} );
		}
	} );

	$( 'input[name="workparcel_scan_mode"]' ).on( 'change', function () {
		pendingLookup = null;
		$result.empty();
		refocus();
	} );
} );
