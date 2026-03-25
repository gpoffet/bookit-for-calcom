# CLAUDE.md — BookIt for Cal.com

## Project overview

WordPress plugin that integrates Cal.com booking widgets into WordPress.
It provides a Gutenberg block, an Elementor widget, a shortcode, and a
central admin settings page. The plugin is intended for publication on
WordPress.org.

---

## Naming conventions

| Context         | Value                  |
|-----------------|------------------------|
| Plugin slug     | `bookit-for-calcom`    |
| WP.org slug     | `bookit-for-cal-com`   |
| PHP prefix      | `bookit_`              |
| PHP classes     | `BookIt_`              |
| JS global       | `bookitCalcom`         |
| Text domain     | `bookit-for-cal-com`   |
| Option key      | `bookit_settings`      |
| Transient key   | `bookit_event_types`   |
| Constant prefix | `BOOKIT_`              |

---

## PHP standards

- Minimum PHP version: **7.4** (use typed properties and return types).
- Follow **WordPress Coding Standards** (WPCS):
  - Tabs for indentation (not spaces).
  - DocBlocks on all classes, methods, hooks.
  - Prefix ALL functions, classes, hooks, options, transients with `bookit_` / `BookIt_` / `BOOKIT_`.
- Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`.
- Sanitize all input: `sanitize_text_field()`, `sanitize_hex_color()`, etc.
- Use nonces for all forms and AJAX calls.
- Never use `$_GET` / `$_POST` directly — always go through `sanitize_*`.
- Capabilities: settings page requires `manage_options`.
- All database options via `get_option()` / `update_option()` — no custom tables.

---

## JavaScript / React (blocks)

- Use `@wordpress/scripts` for build toolchain.
- Block source in `blocks/cal-booking/src/` — compiled to `blocks/cal-booking/build/`.
- Use `@wordpress/components` for all UI inside the block editor (no custom CSS in editor).
- Use `ServerSideRender` for the block preview in editor — rendering is done PHP-side.
- Block attributes must match exactly the PHP render callback parameters.
- No jQuery — vanilla JS only in `assets/js/`.
- Follow WordPress JS coding standards.

---

## File structure (target)

```
bookit-for-calcom/
├── bookit-for-calcom.php          # Main plugin file (headers, bootstrap)
├── uninstall.php                  # Clean up options + transients on uninstall
├── readme.txt                     # WordPress.org format
├── CHANGELOG.md
│
├── includes/
│   ├── class-bookit-api.php       # Cal.com REST API wrapper + transient cache
│   ├── class-bookit-admin.php     # Settings page (WP Settings API)
│   ├── class-bookit-assets.php    # Script/style enqueue (conditional loading)
│   ├── class-bookit-shortcode.php # [bookit] shortcode
│   └── class-bookit-block.php     # Gutenberg block registration
│
├── blocks/
│   └── cal-booking/
│       ├── block.json             # Block metadata
│       ├── render.php             # PHP server-side render callback
│       ├── src/
│       │   ├── index.js           # Block registration
│       │   ├── edit.jsx           # Editor component
│       │   └── editor.scss        # Editor-only styles (minimal)
│       └── build/                 # @wordpress/scripts output (gitignored)
│
├── elementor/
│   └── class-bookit-elementor-widget.php
│
├── assets/
│   ├── js/
│   │   └── bookit-loader.js       # Cal.com init + popup/inline logic
│   └── css/
│       └── bookit-admin.css       # Admin page styles only
│
└── languages/
    ├── bookit-for-cal-com.pot
    └── bookit-for-cal-com-fr_FR.po
```

---

## Settings stored in `bookit_settings` (single option array)

```php
[
  'api_key'        => '',       // Cal.com API key (encrypted ideally)
  'username'       => '',       // Cal.com username (fallback slug prefix)
  'namespace'      => 'cal',    // JS namespace (default: cal)
  'theme'          => 'auto',   // light | dark | auto
  'accent_color'   => '#000000',
  'hide_branding'  => false,
  'load_strategy'  => 'smart',  // smart | always
]
```

---

## Cal.com API integration

- Endpoint: `https://api.cal.com/v1/event-types?apiKey={key}`
- Cache result in transient `bookit_event_types` for **1 hour**.
- Admin settings page must have a **"Refresh event types"** button (AJAX, nonce-protected).
- If API key is absent or API call fails: graceful fallback to a free-text slug input.
- Display both the event title and slug in dropdowns (e.g. `Consultation — consultation-30min`).

---

## Block attributes

```json
{
  "eventType":      { "type": "string",  "default": "" },
  "displayType":    { "type": "string",  "default": "popup-button" },
  "label":          { "type": "string",  "default": "Book a meeting" },
  "inlineHeight":   { "type": "number",  "default": 600 },
  "theme":          { "type": "string",  "default": "global" },
  "accentColor":    { "type": "string",  "default": "" },
  "hideDetails":    { "type": "boolean", "default": false },
  "prefillUser":    { "type": "boolean", "default": false },
  "btnBgColor":     { "type": "string",  "default": "" },
  "btnTextColor":   { "type": "string",  "default": "" },
  "btnBorderRadius":{ "type": "number",  "default": 4 }
}
```

`displayType` values: `popup-button` | `popup-text` | `inline`

---

## Elementor widget

- Class: `BookIt_Elementor_Widget extends \Elementor\Widget_Base`
- Category: create a custom category `bookit` labelled "BookIt"
- Load only if `did_action('elementor/loaded')` is true
- Controls must mirror block attributes exactly
- Style tab: button colors, border radius, typography (Pro feature — wrap in
  `\Elementor\Plugin::$instance->editor->is_edit_mode()` checks where needed)
- Works with Elementor Free AND Pro — Pro-only features must be wrapped in
  capability checks, never cause fatal errors on Free

---

## Shortcode `[bookit]`

```
[bookit event="username/slug" type="popup-button" label="Book now"]
[bookit event="username/slug" type="inline" height="600"]
[bookit event="username/slug" type="popup-text" label="Click here to book"]
```

All shortcode attributes must accept the same options as block attributes.
Missing attributes fall back to global settings.

---

## Asset loading strategy

- Cal.com embed script: `https://app.cal.com/embed/embed.js`
- `load_strategy = smart`: enqueue only on pages/posts that contain a
  `[bookit]` shortcode OR a `bookit/cal-booking` block OR an Elementor widget.
  Use `has_block()`, `has_shortcode()`, and a custom post meta flag set by
  Elementor on save.
- `load_strategy = always`: enqueue on every frontend page.
- Script must be loaded in footer (`true` as last arg to `wp_enqueue_script`).
- The inline init script must run AFTER `embed.js` is loaded.

---

## i18n

- All user-facing strings wrapped in `__()`, `_e()`, `esc_html__()`, etc.
- Text domain: `bookit-for-cal-com`
- `.pot` file generated via `wp i18n make-pot`.
- Provide `fr_FR` translation for all strings.
- JS strings passed via `wp_localize_script` under `bookitCalcomData` object.

---

## Security checklist (apply to every file)

- [ ] Nonce on every form (`wp_nonce_field` / `check_admin_referer`)
- [ ] Nonce on every AJAX call (`wp_create_nonce` / `check_ajax_referer`)
- [ ] Capability check before any privileged operation
- [ ] All output escaped
- [ ] All input sanitized
- [ ] `defined('ABSPATH') || exit;` at top of every PHP file

---

## Build & dev workflow

```bash
# Install JS dependencies (from plugin root)
npm install

# Dev watch mode
npm run start

# Production build
npm run build

# Generate .pot file
wp i18n make-pot . languages/bookit-for-cal-com.pot

# PHP linting (if phpcs installed)
phpcs --standard=WordPress .
```

---

## WordPress.org readiness

- `readme.txt` must follow WordPress.org format with: Tested up to, Requires
  at least, Requires PHP, Description, Installation, FAQ, Changelog sections.
- No external calls except `api.cal.com` and `app.cal.com` — document in
  readme.txt under "External services".
- GPL v2 or later license.
- No minified code without providing original source.
- Prefix everything — zero global namespace pollution.
