/* Workparcel admin UI behaviors.
 * The Settings tabs are pure CSS (radio inputs + labels) so the screens keep working even if this
 * script fails to load. This file only remembers the open tab across the "Save Changes" reload. */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var radios = document.querySelectorAll( '.wp-workparcel-tab-radio' );
		if ( ! radios.length ) return;

		var KEY = 'workparcelSettingsTab';

		try {
			var saved = window.sessionStorage.getItem( KEY );
			var el = saved ? document.getElementById( saved ) : null;
			if ( el && el.classList.contains( 'wp-workparcel-tab-radio' ) ) el.checked = true;
		} catch ( e ) { /* storage unavailable: stay on the first tab */ }

		Array.prototype.forEach.call( radios, function ( radio ) {
			radio.addEventListener( 'change', function () {
				try { window.sessionStorage.setItem( KEY, radio.id ); } catch ( e ) { /* ignore */ }
			} );
		} );
	} );
} )();
