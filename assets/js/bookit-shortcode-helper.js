/**
 * BookIt for Cal.com — Shortcode Helper.
 *
 * Interactive shortcode builder on the Shortcode Helper admin tab.
 * Vanilla JS, no jQuery.
 */
/* global bookitAdminData */
( function () {
	'use strict';

	// ── Defaults map — mirrors shortcode_atts() in class-bookit-shortcode.php ──
	var DEFAULTS = {
		event:                   '',
		type:                    'popup-button',
		label:                   'Book a meeting',
		height:                  '600',
		theme:                   'global',
		accent:                  '',
		hide_details:            '0',
		prefill:                 '0',
		btn_bg:                  '',
		btn_text:                '',
		btn_radius:              '4',
		btn_border_width:        '0',
		btn_border_style:        'solid',
		btn_border_color:        '',
		btn_padding_top:         '10',
		btn_padding_right:       '20',
		btn_padding_bottom:      '10',
		btn_padding_left:        '20',
		btn_font_size:           '14',
		btn_font_weight:         '',
		btn_text_transform:      '',
		btn_letter_spacing:      '0',
		btn_full_width:          '0',
		btn_hover_bg:            '',
		btn_hover_text:          '',
		btn_hover_border_color:  '',
		btn_transition_duration: '200',
	};

	var form, output, copyBtn, copyStatus, typeSelect;

	// ── Init ────────────────────────────────────────────────────────────────────

	document.addEventListener( 'DOMContentLoaded', function () {
		form       = document.getElementById( 'bookit-sh-form' );
		if ( ! form ) {
			return; // Not on the shortcode helper tab.
		}

		output     = document.getElementById( 'bookit-sh-result' );
		copyBtn    = document.getElementById( 'bookit-sh-copy' );
		copyStatus = document.getElementById( 'bookit-sh-copy-status' );
		typeSelect = document.getElementById( 'bookit-sh-type' );

		// Build event dropdown from cached event types if available,
		// otherwise fetch from API automatically if an API key is configured.
		if ( Array.isArray( bookitAdminData.eventTypes ) && bookitAdminData.eventTypes.length > 0 ) {
			buildEventField( bookitAdminData.eventTypes, '' );
		} else if ( '1' === bookitAdminData.hasApiKey ) {
			fetchEventTypes();
		}

		// Wire color pickers ↔ hidden inputs.
		initColorPickers();

		// Wire "Clear" buttons for color fields.
		initColorClearButtons();

		// Initial state.
		applyVisibility( typeSelect ? typeSelect.value : 'popup-button' );
		updateShortcode();

		// Live update on any input/change inside the form.
		form.addEventListener( 'change', onFormChange );
		form.addEventListener( 'input',  onFormChange );

		if ( copyBtn ) {
			copyBtn.addEventListener( 'click', copyToClipboard );
		}
	} );

	// ── Public API (used by bookit-admin.js after AJAX refresh) ─────────────────

	window.bookitShortcodeHelper = {
		/**
		 * Rebuild the event dropdown after a successful "Refresh event types".
		 *
		 * @param {Array}  events   Raw event-type objects from the API response.
		 * @param {string} username Auto-detected Cal.com username.
		 */
		refreshEventTypes: function ( events, username ) {
			if ( ! form ) {
				return;
			}
			var formatted = [];
			if ( Array.isArray( events ) ) {
				events.forEach( function ( et ) {
					var slug      = et.slug  || '';
					var title     = et.title || slug;
					var qualified = username ? username + '/' + slug : slug;
					if ( slug ) {
						formatted.push( { slug: qualified, label: title + ' \u2014 ' + slug } );
					}
				} );
			}
			buildEventField( formatted, username );
			updateShortcode();
		},
	};

	// ── fetchEventTypes ─────────────────────────────────────────────────────────

	/**
	 * Auto-fetch event types via AJAX when the transient is cold on page load.
	 * Reuses the same AJAX action as the "Refresh" button.
	 */
	function fetchEventTypes() {
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
				if ( json.success && typeof window.bookitShortcodeHelper !== 'undefined' ) {
					window.bookitShortcodeHelper.refreshEventTypes(
						json.data.events,
						json.data.username
					);
				}
			} )
			.catch( function () { /* silent fail — input field remains */ } );
	}

	// ── buildEventField ─────────────────────────────────────────────────────────

	/**
	 * Replace the event <input> with a <select> when event types are available,
	 * or restore the <input> when not.
	 *
	 * @param {Array}  eventTypes Array of {slug, label} objects.
	 * @param {string} username   Current Cal.com username (unused directly here).
	 */
	function buildEventField( eventTypes, username ) { // eslint-disable-line no-unused-vars
		var container = document.getElementById( 'bookit-sh-event' )
			? document.getElementById( 'bookit-sh-event' ).parentNode
			: null;

		if ( ! container ) {
			return;
		}

		// Remove existing field (input or select).
		var existing = container.querySelector( '[data-bookit-attr="event"]' );
		if ( existing ) {
			container.removeChild( existing );
		}

		var newField;

		if ( Array.isArray( eventTypes ) && eventTypes.length > 0 ) {
			newField = document.createElement( 'select' );
			newField.id = 'bookit-sh-event';
			newField.setAttribute( 'data-bookit-attr', 'event' );

			var placeholder = document.createElement( 'option' );
			placeholder.value       = '';
			placeholder.textContent = '\u2014 ' + ( window.bookitHelperL10n
				? window.bookitHelperL10n.selectEvent
				: 'Select an event' ) + ' \u2014';
			newField.appendChild( placeholder );

			eventTypes.forEach( function ( et ) {
				var opt       = document.createElement( 'option' );
				opt.value     = et.slug;
				opt.textContent = et.label;
				newField.appendChild( opt );
			} );
		} else {
			newField = document.createElement( 'input' );
			newField.type        = 'text';
			newField.id          = 'bookit-sh-event';
			newField.className   = 'regular-text';
			newField.placeholder = 'username/slug';
			newField.setAttribute( 'data-bookit-attr', 'event' );
		}

		// Insert before the description paragraph.
		var desc = container.querySelector( '.description' );
		container.insertBefore( newField, desc || null );
	}

	// ── Color pickers ───────────────────────────────────────────────────────────

	/**
	 * Wire each <input type="color"> to update its companion hidden input.
	 */
	function initColorPickers() {
		// Map: picker id → hidden input id.
		var pairs = {
			'bookit-sh-accent-picker':          'bookit-sh-accent',
			'bookit-sh-btn-bg-picker':          'bookit-sh-btn-bg',
			'bookit-sh-btn-text-picker':        'bookit-sh-btn-text',
			'bookit-sh-btn-border-color-picker':'bookit-sh-btn-border-color',
			'bookit-sh-btn-hover-bg-picker':    'bookit-sh-btn-hover-bg',
			'bookit-sh-btn-hover-text-picker':  'bookit-sh-btn-hover-text',
			'bookit-sh-btn-hover-border-picker':'bookit-sh-btn-hover-border-color',
		};

		Object.keys( pairs ).forEach( function ( pickerId ) {
			var picker = document.getElementById( pickerId );
			var hidden = document.getElementById( pairs[ pickerId ] );
			if ( ! picker || ! hidden ) {
				return;
			}
			picker.addEventListener( 'input', function () {
				// Only propagate if the hidden field is non-empty (user actively chose).
				if ( hidden.value !== '' || picker.value !== '#000000' ) {
					hidden.value = picker.value;
					updateShortcode();
				}
			} );
			picker.addEventListener( 'change', function () {
				hidden.value = picker.value;
				updateShortcode();
			} );
		} );
	}

	/**
	 * Wire .bookit-sh-color-clear buttons to reset their target hidden input.
	 */
	function initColorClearButtons() {
		// Accent clear has its own id.
		var accentClear = document.getElementById( 'bookit-sh-accent-clear' );
		if ( accentClear ) {
			accentClear.addEventListener( 'click', function () {
				var hidden = document.getElementById( 'bookit-sh-accent' );
				if ( hidden ) {
					hidden.value = '';
					updateShortcode();
				}
			} );
		}

		// Generic clear buttons.
		var clears = form.querySelectorAll( '.bookit-sh-color-clear' );
		clears.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var targetId = btn.getAttribute( 'data-target' );
				var hidden   = document.getElementById( targetId );
				if ( hidden ) {
					hidden.value = '';
					updateShortcode();
				}
			} );
		} );
	}

	// ── Form change handler ─────────────────────────────────────────────────────

	function onFormChange( e ) {
		// Ignore color pickers — they drive hidden inputs via initColorPickers().
		if ( e.target.type === 'color' ) {
			return;
		}
		if ( typeSelect && e.target === typeSelect ) {
			applyVisibility( typeSelect.value );
		}
		if ( e.target.getAttribute( 'data-bookit-attr' ) === 'btn_border_width' ) {
			syncBorderConditionals( parseInt( e.target.value, 10 ) > 0 );
		}
		updateShortcode();
	}

	// ── Visibility ──────────────────────────────────────────────────────────────

	/**
	 * Show/hide rows based on the current display type.
	 *
	 * @param {string} type Current value of the type select.
	 */
	function applyVisibility( type ) {
		var conditionalRows = form.querySelectorAll( '[data-bookit-show]' );
		conditionalRows.forEach( function ( row ) {
			var showFor = row.getAttribute( 'data-bookit-show' );
			var visible = false;

			if ( showFor === type ) {
				visible = true;
			} else if ( showFor === 'popup' && ( 'popup-button' === type || 'popup-text' === type ) ) {
				visible = true;
			}

			// Border-conditional rows are managed separately.
			if ( row.hasAttribute( 'data-bookit-border-conditional' ) ) {
				return;
			}

			row.classList.toggle( 'bookit-sh-hidden', ! visible );
		} );

		// Re-evaluate border conditionals only when popup-button is active.
		var bwEl    = document.querySelector( '[data-bookit-attr="btn_border_width"]' );
		var hasBorder = bwEl ? parseInt( bwEl.value, 10 ) > 0 : false;
		syncBorderConditionals( 'popup-button' === type && hasBorder );
	}

	/**
	 * Show/hide rows that depend on border_width > 0.
	 *
	 * @param {boolean} show Whether to show border-conditional rows.
	 */
	function syncBorderConditionals( show ) {
		var rows = form.querySelectorAll( '[data-bookit-border-conditional]' );
		rows.forEach( function ( row ) {
			row.classList.toggle( 'bookit-sh-hidden', ! show );
		} );
	}

	// ── Shortcode builder ───────────────────────────────────────────────────────

	/**
	 * Rebuild the shortcode string from current form values.
	 */
	function updateShortcode() {
		if ( ! output ) {
			return;
		}

		var parts   = [ '[bookit' ];
		var bwValue = 0;

		// Iterate in DOM order to keep the shortcode predictable.
		var nodes = form.querySelectorAll( '[data-bookit-attr]' );
		nodes.forEach( function ( el ) {
			var attr = el.getAttribute( 'data-bookit-attr' );
			var val  = getFieldValue( el );
			var def  = DEFAULTS[ attr ];

			// Track border width for border_style / border_color omission.
			if ( attr === 'btn_border_width' ) {
				bwValue = parseInt( val, 10 );
			}

			// Omit border_style and border_color when border_width is 0.
			if ( ( 'btn_border_style' === attr || 'btn_border_color' === attr ) && 0 === bwValue ) {
				return;
			}

			// Always include event (even if empty — makes invalid state obvious).
			if ( 'event' === attr ) {
				parts.push( 'event="' + val + '"' );
				return;
			}

			// Skip if equal to the default value.
			if ( String( val ) === String( def ) ) {
				return;
			}

			// Skip empty non-default values (color clears).
			if ( '' === val && '' === def ) {
				return;
			}

			parts.push( attr + '="' + val + '"' );
		} );

		output.textContent = parts.join( ' ' ) + ']';
	}

	/**
	 * Get the current value of a form element.
	 *
	 * @param {HTMLElement} el Form element.
	 * @return {string} Current value.
	 */
	function getFieldValue( el ) {
		if ( 'checkbox' === el.type ) {
			return el.checked ? '1' : '0';
		}
		return el.value;
	}

	// ── Clipboard ───────────────────────────────────────────────────────────────

	function copyToClipboard() {
		var text = output ? output.textContent : '';
		if ( ! text ) {
			return;
		}

		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text )
				.then( showCopySuccess )
				.catch( function () { fallbackCopy( text ); } );
		} else {
			fallbackCopy( text );
		}
	}

	function fallbackCopy( text ) {
		var ta        = document.createElement( 'textarea' );
		ta.value      = text;
		ta.style.position = 'fixed';
		ta.style.opacity  = '0';
		document.body.appendChild( ta );
		ta.focus();
		ta.select();
		try {
			var ok = document.execCommand( 'copy' );
			if ( ok ) { showCopySuccess(); } else { showCopyError(); }
		} catch ( e ) {
			showCopyError();
		}
		document.body.removeChild( ta );
	}

	function showCopySuccess() {
		if ( ! copyStatus ) { return; }
		copyStatus.className   = '';
		copyStatus.textContent = bookitAdminData.msgCopied || 'Copied!';
		setTimeout( function () {
			copyStatus.textContent = '';
		}, 2500 );
	}

	function showCopyError() {
		if ( ! copyStatus ) { return; }
		copyStatus.className   = 'bookit-sh-error';
		copyStatus.textContent = bookitAdminData.msgCopyFailed || 'Copy failed.';
	}

}() );
