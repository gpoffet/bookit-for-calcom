=== BookIt for Cal.com ===
Contributors: gpoffet
Tags: cal.com, booking, calendar, appointment, scheduling
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embed Cal.com booking widgets in WordPress via a Gutenberg block, Elementor widget, or [bookit] shortcode.

== Description ==

**BookIt for Cal.com** is the easiest way to add Cal.com scheduling to your WordPress site. It provides three integration methods so you can use whichever fits your workflow:

* **Gutenberg block** — drag the *Cal.com Booking* block into any page or post.
* **Elementor widget** — find the *Cal.com Booking* widget in the *BookIt* widget panel.
* **Shortcode** — paste `[bookit event="username/slug"]` anywhere.

= Features =

* Three display modes: **popup button**, **popup text link**, and **inline calendar**.
* Full **theme support**: light, dark, or auto (follows the visitor's OS preference).
* Custom **accent color** per widget or globally.
* **Smart loading** — the Cal.com embed script is loaded only on pages that actually contain a booking widget, keeping all other pages fast.
* **Pre-fill** logged-in user name and email automatically.
* **Cal.com API integration** — connect your API key to pick event types from a dropdown instead of typing slugs manually. Results are cached for 1 hour.
* "Refresh event types" button in the admin with one click.
* Compatible with Elementor **Free** and **Pro**.
* Full **i18n** support — ships with a French (fr_FR) translation.

= External services =

This plugin connects to two external services operated by Cal.com, Inc.:

* **Cal.com API** (`https://api.cal.com`) — used to fetch your event types when an API key is configured. Only called from the WordPress admin when you save settings or click "Refresh event types".
* **Cal.com embed script** (`https://app.cal.com/embed/embed.js`) — loaded on the frontend to render booking widgets. Loaded only on pages that contain a booking widget (smart strategy) or on all pages (always strategy), depending on your settings.

By using this plugin you agree to Cal.com's [Terms of Service](https://cal.com/terms) and [Privacy Policy](https://cal.com/privacy).

== Installation ==

1. Upload the `bookit-for-calcom` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Settings → BookIt** to configure your Cal.com API key and default options.
4. Add a booking widget using the Gutenberg block, Elementor widget, or `[bookit]` shortcode.

== Frequently Asked Questions ==

= Do I need a Cal.com account? =

Yes. You need a free or paid Cal.com account to get an event slug. An API key is optional but enables the event type dropdown in the editor.

= Where do I find my API key? =

In Cal.com: **Settings → Developer → API Keys**.

= What is the event slug format? =

`username/event-slug` — for example `jane/consultation-30min`.

= Does this work with Elementor Free? =

Yes. All core features work with Elementor Free. Typography controls require Elementor Pro.

= Is the Cal.com embed script loaded on every page? =

By default, the **Smart** loading strategy loads the script only on pages that contain a `[bookit]` shortcode, a *Cal.com Booking* Gutenberg block, or an Elementor BookIt widget. Switch to **Always** in settings if you prefer unconditional loading.

= Can I pre-fill the booking form with the logged-in user's details? =

Yes. Enable the "Pre-fill logged-in user data" option per block/widget/shortcode. The visitor's display name and email will be sent to Cal.com.

== Shortcode reference ==

Basic usage:
`[bookit event="username/slug"]`

All attributes:
`[bookit event="username/slug" type="popup-button" label="Book now" height="600" theme="auto" accent="#0070f3" hide_details="0" prefill="0" btn_bg="#000" btn_text="#fff" btn_radius="4"]`

| Attribute      | Values                                   | Default         |
|----------------|------------------------------------------|-----------------|
| `event`        | `username/slug`                          | *(required)*    |
| `type`         | `popup-button` \| `popup-text` \| `inline` | `popup-button` |
| `label`        | any text                                 | `Book a meeting`|
| `height`       | number (px)                              | `600`           |
| `theme`        | `global` \| `auto` \| `light` \| `dark` | `global`        |
| `accent`       | hex color                                | global setting  |
| `hide_details` | `0` \| `1`                               | `0`             |
| `prefill`      | `0` \| `1`                               | `0`             |
| `btn_bg`       | hex color                                | *(none)*        |
| `btn_text`     | hex color                                | *(none)*        |
| `btn_radius`   | number (px)                              | `4`             |

== Screenshots ==

1. The Cal.com Booking block in the Gutenberg editor with the sidebar inspector open.
2. The BookIt settings page under Settings → BookIt.
3. The Elementor widget panel showing the BookIt category.
4. A popup button widget on the frontend.
5. An inline calendar widget on the frontend.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
