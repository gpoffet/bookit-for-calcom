/**
 * BookIt for Cal.com — Admin JS.
 *
 * Handles the "Refresh event types" AJAX button on the settings page.
 */
/* global bookitAdminData */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var btn    = document.getElementById( 'bookit-refresh-event-types' );
		var status = document.getElementById( 'bookit-refresh-status' );

		if ( ! btn || ! status ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			btn.disabled = true;
			status.className  = '';
			status.textContent = '\u29d7 \u2026';

			var data = new FormData();
			data.append( 'action', 'bookit_refresh_event_types' );
			data.append( 'nonce',  bookitAdminData.refreshNonce );

			fetch( bookitAdminData.ajaxUrl, {
				method:      'POST',
				credentials: 'same-origin',
				body:        data,
			} )
				.then( function ( response ) { return response.json(); } )
				.then( function ( json ) {
					if ( json.success ) {
						status.className  = 'bookit-success';
						status.textContent = bookitAdminData.msgSuccess +
							' (' + json.data.count + ')';
					} else {
						status.className  = 'bookit-error';
						status.textContent = json.data || bookitAdminData.msgError;
					}
				} )
				.catch( function () {
					status.className  = 'bookit-error';
					status.textContent = bookitAdminData.msgError;
				} )
				.finally( function () {
					btn.disabled = false;
				} );
		} );
	} );
}() );
