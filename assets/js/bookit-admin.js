/**
 * BookIt for Cal.com — Admin JS.
 *
 * Handles the "Refresh event types" AJAX button and the username field
 * auto-detection UX on the settings page.
 */
/* global bookitAdminData */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var btn           = document.getElementById( 'bookit-refresh-event-types' );
		var status        = document.getElementById( 'bookit-refresh-status' );
		var apiKeyField   = document.getElementById( 'bookit_api_key' );
		var apiBaseField  = document.getElementById( 'bookit_api_base' );
		var usernameField = document.getElementById( 'bookit_username' );
		var usernameDesc  = document.getElementById( 'bookit-username-desc' );

		// -----------------------------------------------------------------
		// Username field — readonly when API key is present.
		// -----------------------------------------------------------------

		/**
		 * Set the username field into auto-detect (readonly) or manual mode.
		 *
		 * @param {boolean} auto True = readonly / auto mode.
		 */
		function setUsernameMode( auto ) {
			if ( ! usernameField ) {
				return;
			}
			usernameField.readOnly            = auto;
			usernameField.style.background    = auto ? '#f0f0f1' : '';
			usernameField.style.color         = auto ? '#666'    : '';
			usernameField.style.cursor        = auto ? 'default' : '';

			if ( usernameDesc ) {
				usernameDesc.textContent = auto
					? bookitAdminData.msgUsernameAuto
					: bookitAdminData.msgUsernameManual;
			}
		}

		// Initialise mode from server-side data.
		// wp_localize_script serialises PHP true → "1", false → "".
		var hasApiKey = ( '1' === bookitAdminData.hasApiKey );
		setUsernameMode( hasApiKey );

		// If a cached username is available and the field is empty, pre-fill it.
		if ( usernameField && bookitAdminData.autoUsername && ! usernameField.value ) {
			usernameField.value = bookitAdminData.autoUsername;
		}

		// React to API key field changes.
		if ( apiKeyField ) {
			apiKeyField.addEventListener( 'input', function () {
				var hasKey = '' !== apiKeyField.value.trim();
				setUsernameMode( hasKey );
				// Clear stale username when key is removed.
				if ( ! hasKey && usernameField ) {
					usernameField.value = '';
				}
			} );
		}

		// -----------------------------------------------------------------
		// Refresh button.
		// -----------------------------------------------------------------

		if ( ! btn || ! status ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			btn.disabled       = true;
			status.className   = '';
			status.textContent = '\u29d7 \u2026';

			// If an API key is present, switch to readonly mode immediately.
			if ( apiKeyField && apiKeyField.value.trim() ) {
				setUsernameMode( true );
			}

			var data = new FormData();
			data.append( 'action',   'bookit_refresh_event_types' );
			data.append( 'nonce',    bookitAdminData.refreshNonce );
			data.append( 'api_key',  apiKeyField  ? apiKeyField.value  : '' );
			data.append( 'api_base', apiBaseField ? apiBaseField.value : '' );

			fetch( bookitAdminData.ajaxUrl, {
				method:      'POST',
				credentials: 'same-origin',
				body:        data,
			} )
				.then( function ( response ) { return response.json(); } )
				.then( function ( json ) {
					if ( json.success ) {
						status.className   = 'bookit-success';
						status.textContent = bookitAdminData.msgSuccess +
							' (' + json.data.count + ')';

						// Auto-fill username and lock the field.
						if ( json.data.username && usernameField ) {
							usernameField.value = json.data.username;
							setUsernameMode( true );
						}

						// Sync shortcode helper event dropdown if active.
						if ( typeof window.bookitShortcodeHelper !== 'undefined' ) {
							window.bookitShortcodeHelper.refreshEventTypes(
								json.data.events,
								json.data.username
							);
						}
					} else {
						status.className   = 'bookit-error';
						status.textContent = json.data || bookitAdminData.msgError;
					}
				} )
				.catch( function () {
					status.className   = 'bookit-error';
					status.textContent = bookitAdminData.msgError;
				} )
				.finally( function () {
					btn.disabled = false;
				} );
		} );
	} );
}() );
