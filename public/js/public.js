/* Workparcel public tracker behaviors. No external dependencies. */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var buttons = document.querySelectorAll( '.wp-workparcel-copy' );
		if ( ! buttons.length ) return;

		buttons.forEach( function ( button ) {
			var originalLabel = button.textContent;

			button.addEventListener( 'click', function () {
				var value = button.getAttribute( 'data-copy' ) || '';
				if ( ! value ) return;

				var done = function () {
					button.textContent = button.getAttribute( 'data-copied-label' ) || 'Copied';
					button.classList.add( 'is-copied' );
					setTimeout( function () {
						button.textContent = originalLabel;
						button.classList.remove( 'is-copied' );
					}, 1800 );
				};

				if ( navigator.clipboard && window.isSecureContext ) {
					navigator.clipboard.writeText( value ).then( done ).catch( function () {
						fallbackCopy( value, done );
					} );
				} else {
					fallbackCopy( value, done );
				}
			} );
		} );

		function fallbackCopy( value, done ) {
			var temp = document.createElement( 'textarea' );
			temp.value = value;
			temp.style.position = 'fixed';
			temp.style.opacity = '0';
			document.body.appendChild( temp );
			temp.focus();
			temp.select();
			try {
				document.execCommand( 'copy' );
				done();
			} catch ( e ) {
				/* Clipboard unavailable; silently ignore. */
			}
			document.body.removeChild( temp );
		}
	} );
} )();
