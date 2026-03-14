/**
 * BookIt for Cal.com — Admin JS.
 *
 * Handles the "Refresh event types" AJAX button and the username field
 * auto-detection UX on the settings page.
 */
/* global bookitAdminData */
( function () {
	'use strict';

	var INSTANCE_URLS = {
		global: 'https://api.cal.com/v2',
		eu:     'https://api.cal.eu/v2',
	};

	document.addEventListener( 'DOMContentLoaded', function () {
		var btn            = document.getElementById( 'bookit-refresh-event-types' );
		var status         = document.getElementById( 'bookit-refresh-status' );
		var apiKeyField    = document.getElementById( 'bookit_api_key' );
		var apiBaseField   = document.getElementById( 'bookit_api_base' );
		var usernameField  = document.getElementById( 'bookit_username' );
		var usernameDesc   = document.getElementById( 'bookit-username-desc' );
		var instanceSelect = document.getElementById( 'bookit_api_instance' );

		// -----------------------------------------------------------------
		// Instance select — keep hidden URL input in sync.
		// -----------------------------------------------------------------
		if ( instanceSelect && apiBaseField ) {
			instanceSelect.addEventListener( 'change', function () {
				var val = instanceSelect.value;
				if ( 'custom' === val ) {
					apiBaseField.style.display = '';
					apiBaseField.focus();
				} else {
					apiBaseField.value         = INSTANCE_URLS[ val ];
					apiBaseField.style.display = 'none';
				}
			} );
		}

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

		// -----------------------------------------------------------------
		// Style tab — color controls (text input + native color picker + clear).
		// -----------------------------------------------------------------
		document.querySelectorAll( '.bookit-color-control' ).forEach( function ( ctrl ) {
			var textInput = ctrl.querySelector( '.bookit-color-text' );
			var picker    = ctrl.querySelector( '.bookit-color-picker' );
			var clearBtn  = ctrl.querySelector( '.bookit-color-clear' );

			if ( ! textInput || ! picker ) {
				return;
			}

			// Picker → text.
			picker.addEventListener( 'input', function () {
				textInput.value = picker.value;
			} );

			// Text → picker (only when valid 6-digit hex).
			textInput.addEventListener( 'input', function () {
				if ( /^#[0-9a-fA-F]{6}$/.test( textInput.value ) ) {
					picker.value = textInput.value;
				}
			} );

			// Clear button.
			if ( clearBtn ) {
				clearBtn.addEventListener( 'click', function () {
					textInput.value = '';
				} );
			}
		} );
	} );
}() );
