# Changelog

All notable changes to BookIt for Cal.com will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
