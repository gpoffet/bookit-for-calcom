/**
 * BookIt for Cal.com — Frontend loader.
 *
 * Initialises Cal.com embed widgets based on data-attributes set by the
 * PHP render layer (block, shortcode, or Elementor widget).
 *
 * Supported display types:
 *   - popup-button : Cal.com floating button attached to a <button> element.
 *   - popup-text   : Cal.com popup triggered by a text link / any element.
 *   - inline       : Cal.com inline iframe embedded directly on the page.
 *
 * Expected data-attributes on .bookit-widget:
 *   data-event       : full Cal.com event slug (e.g. "username/consultation")
 *   data-type        : "popup-button" | "popup-text" | "inline"
 *   data-label       : button / link label (popup types)
 *   data-height      : iframe height in px (inline type)
 *   data-theme       : "light" | "dark" | "auto" | "global"
 *   data-accent      : hex accent color (optional)
 *   data-hide-details: "1" to hide booking details panel
 *   data-prefill     : "1" to pre-fill logged-in user data
 *   data-ns          : Cal.com JS namespace (default "cal")
 */
/* global Cal, bookitCalcomData */
( function () {
	'use strict';

	/**
	 * Build the Cal.com config object from data-attributes.
	 *
	 * @param {HTMLElement} el Widget root element.
	 * @returns {Object}
	 */
	function buildConfig( el ) {
		var config = {};

		var theme = el.dataset.theme || 'auto';
		if ( 'global' !== theme ) {
			config.theme = theme;
		}

		if ( el.dataset.accent ) {
			config.styles = { branding: { brandColor: el.dataset.accent } };
		}

		if ( '1' === el.dataset.hideDetails ) {
			config.hideEventTypeDetails = true;
		}

		return config;
	}

	/**
	 * Build the pre-fill object if user is logged in and prefill is requested.
	 *
	 * @param {HTMLElement} el Widget root element.
	 * @returns {Object|null}
	 */
	function buildPrefill( el ) {
		if ( '1' !== el.dataset.prefill ) {
			return null;
		}
		if ( ! bookitCalcomData || ! bookitCalcomData.currentUser ) {
			return null;
		}
		var user = bookitCalcomData.currentUser;
		var prefill = {};
		if ( user.name )  { prefill.name  = user.name;  }
		if ( user.email ) { prefill.email = user.email; }
		return Object.keys( prefill ).length ? prefill : null;
	}

	/**
	 * Initialise a single popup-button widget.
	 *
	 * @param {HTMLElement} el    Widget root element.
	 * @param {string}      ns    Cal.com JS namespace.
	 * @param {string}      event Full event slug.
	 * @returns {void}
	 */
	function initPopupButton( el, ns, event ) {
		var btn = el.querySelector( '.bookit-btn' );
		if ( ! btn ) {
			return;
		}

		var config  = buildConfig( el );
		var prefill = buildPrefill( el );

		Cal( 'init', ns, { origin: 'https://cal.com' } );

		var uiConfig = Object.assign( {}, config );
		Cal.ns[ ns ]( 'ui', uiConfig );

		Cal.ns[ ns ]( 'on', {
			action: 'bookingSuccessful',
			callback: function () {
				// Custom event so themes/plugins can hook in.
				el.dispatchEvent( new CustomEvent( 'bookitBookingSuccessful', { bubbles: true } ) );
			},
		} );

		btn.addEventListener( 'click', function () {
			var opts = { calLink: event };
			if ( prefill ) { opts.calPageConfig = prefill; }
			Cal.ns[ ns ]( 'modal', opts );
		} );
	}

	/**
	 * Initialise a popup-text widget (any element triggers the popup).
	 *
	 * @param {HTMLElement} el    Widget root element.
	 * @param {string}      ns    Cal.com JS namespace.
	 * @param {string}      event Full event slug.
	 * @returns {void}
	 */
	function initPopupText( el, ns, event ) {
		var trigger = el.querySelector( '.bookit-link' );
		if ( ! trigger ) {
			return;
		}

		var config  = buildConfig( el );
		var prefill = buildPrefill( el );

		Cal( 'init', ns, { origin: 'https://cal.com' } );
		Cal.ns[ ns ]( 'ui', config );

		trigger.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var opts = { calLink: event };
			if ( prefill ) { opts.calPageConfig = prefill; }
			Cal.ns[ ns ]( 'modal', opts );
		} );
	}

	/**
	 * Initialise an inline embed widget.
	 *
	 * @param {HTMLElement} el    Widget root element.
	 * @param {string}      ns    Cal.com JS namespace.
	 * @param {string}      event Full event slug.
	 * @returns {void}
	 */
	function initInline( el, ns, event ) {
		var container = el.querySelector( '.bookit-inline' );
		if ( ! container ) {
			return;
		}

		var config  = buildConfig( el );
		var prefill = buildPrefill( el );
		var height  = parseInt( el.dataset.height, 10 ) || 600;

		// Give the container a unique id if missing.
		if ( ! container.id ) {
			container.id = 'bookit-inline-' + Math.random().toString( 36 ).slice( 2, 9 );
		}

		Cal( 'init', ns, { origin: 'https://cal.com' } );

		var inlineOpts = Object.assign(
			{ calLink: event, elementOrSelector: '#' + container.id },
			config
		);
		if ( prefill ) {
			inlineOpts.calPageConfig = prefill;
		}

		Cal.ns[ ns ]( 'inline', inlineOpts );

		Cal.ns[ ns ]( 'ui', {
			styles: Object.assign( { height: height + 'px' }, config.styles || {} ),
			hideEventTypeDetails: config.hideEventTypeDetails || false,
		} );
	}

	/**
	 * Initialise all .bookit-widget elements on the page.
	 *
	 * @returns {void}
	 */
	function initAll() {
		var widgets = document.querySelectorAll( '.bookit-widget' );
		widgets.forEach( function ( el ) {
			var type  = el.dataset.type  || 'popup-button';
			var event = el.dataset.event || '';
			var ns    = el.dataset.ns    || 'cal';

			if ( ! event ) {
				return;
			}

			switch ( type ) {
				case 'popup-button':
					initPopupButton( el, ns, event );
					break;
				case 'popup-text':
					initPopupText( el, ns, event );
					break;
				case 'inline':
					initInline( el, ns, event );
					break;
			}
		} );
	}

	// Run after DOM + Cal embed script are ready.
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
}() );
