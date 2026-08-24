/* Workparcel admin UI behaviors (Settings tabs). No external dependencies. */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var tabs = document.querySelectorAll( '.wp-workparcel-tab' );
		if ( ! tabs.length ) return;

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				var target = tab.getAttribute( 'data-tab' );

				tabs.forEach( function ( t ) {
					t.classList.remove( 'is-active' );
					t.setAttribute( 'aria-selected', 'false' );
				} );
				tab.classList.add( 'is-active' );
				tab.setAttribute( 'aria-selected', 'true' );

				document.querySelectorAll( '.wp-workparcel-tab-panel' ).forEach( function ( panel ) {
					panel.hidden = panel.id !== 'wp-workparcel-tab-' + target;
				} );
			} );
		} );
	} );
} )();
