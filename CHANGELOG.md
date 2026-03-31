# Changelog

All notable changes to BookIt for Cal.com will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.3] — 2026-03-31

### Fixed

- Elementor widget: Cal.com popup showing 404 — event slug was missing the username prefix because the Cal.com v2 API nests the username under `owner`/`profile`/`user`, not at root level. Fixed slug construction in `register_controls()` and added username-resolution fallback in `render()` for slugs saved without a prefix.

## [1.0.2] — 2026-03-31

### Fixed

- Shortcode Helper event dropdown not populated on fresh installs: the tab now auto-fetches event types via AJAX when the server-side cache is cold.

## [1.0.1] — 2026-03-30

### Fixed

- Admin JS not enqueued on the settings page due to incorrect hook name — "Refresh event types" button was unresponsive.

## [1.0.0] — 2024-01-01

### Added

- Gutenberg block `bookit/cal-booking` with server-side rendering.
- Elementor widget in a custom "BookIt" category, compatible with Free and Pro.
- `[bookit]` shortcode with full attribute parity with the block.
- Three display modes: popup button, popup text link, inline calendar.
- Cal.com REST API integration with 1-hour transient cache.
- "Refresh event types" button in admin (AJAX, nonce-protected).
- Smart / Always script loading strategy.
- Pre-fill logged-in user name and email.
- Per-widget theme (light / dark / auto) and accent color overrides.
- Button background color, text color, and border radius controls.
- Hide booking details panel option.
- Settings page under Settings → BookIt using the WordPress Settings API.
- French (fr_FR) translation.
- Clean uninstall (removes options and transients).
