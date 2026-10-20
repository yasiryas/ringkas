=== Yasyes Short Link ===
Contributors: yasiryas
Tags: shortlink, short link, url shortener, link management, url
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create root domain short links with a minimal dashboard, instant search, and auto-expire features. Clean, fast, and self-hosted.

== Description ==

Yasyes Short Link lets you create short URLs directly on your root domain (e.g. `domain.com/promo2026`) without any external short-link service. All data is stored in your own WordPress database.

= Key Features =

* **Root domain short links** — clean URLs like `https://domain.com/promo2026`
* **WP Admin management** — a clean, easy-to-use "Manage Links" page
* **Front-end dashboard** — manage links from `/short/dashboard` as well
* **Self-service accounts** — register at `/short/register`; new users get link-management access only, never full admin
* **Full auth flow** — custom login, register, forgot & reset password pages outside wp-login.php
* **Instant search** — filter aliases or destination URLs in real time
* **Auto-expire** — set an expiration date on any link; visitors see an HTTP 410 "link expired" page
* **Real-time statistics** — link count, active links, and total clicks update automatically
* **QR code** — generate and download a PNG QR for any short link
* **Anti-collision safety** — an alias can never override an existing post, page, or category slug
* **Rate limited** — login, register, and password reset are throttled per IP
* **Feedback via email** — users can send suggestions or bug reports directly to the admin
* **Lightweight** — small CSS/JS, system fonts, no heavy dependencies

= Getting Started =

1. Activate the plugin, then open the **Yasyes Short Link** menu in wp-admin.
2. Click **Create Link**, enter a destination URL, and save.
3. Copy the generated short link and share it.
4. Manage, edit, or delete links at any time.

= Sharing access with someone else =

1. Send them `https://your-domain.com/short/register`.
2. They create their own account (username, email, password).
3. The account automatically gets the `manage_yasyes_shortlink_links` capability:
   full access to the link dashboard at `/short/dashboard`, but nothing else in wp-admin.

== Installation ==

1. Download the plugin and extract it into `wp-content/plugins/yasyes-shortlink/`, or upload the `.zip` file via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** menu in wp-admin.
3. Open the **Yasyes Short Link** menu to start creating short links.

== Frequently Asked Questions ==

= How do I create a short link? =

Open the **Yasyes Short Link** menu in wp-admin, click the **Create Link** button, enter a destination URL, and click **Save**. The short link is created immediately.

= Where are the short links hosted? =

Directly on your WordPress root domain, e.g. `https://domain.com/image`.

= How do I set an expiring link? =

When creating or editing a link, fill in the **Expires** field with the desired date and time. The link is automatically disabled after that time.

= How do I let someone else manage links without giving them full admin access? =

Share `https://your-domain.com/short/register` with them. The account they create receives only the `manage_yasyes_shortlink_links` capability — it can create, edit, and delete links from `/short/dashboard`, but has no access to themes, plugins, users, or other wp-admin settings.

= Where does someone log in for the front-end dashboard? =

At `https://your-domain.com/short` — a custom login page that uses the same WordPress accounts as wp-admin. Password resets also stay on `/short/forgot-password` and never touch wp-login.php.

= Does this plugin use any third-party services? =

No. All links and statistics are stored in your own WordPress database.

= How do I send feedback? =

Use the **Feedback** button on the Manage Links page (wp-admin or front-end dashboard). Your message will be sent to the admin via email.

== Screenshots ==

1. Manage Links page in wp-admin.
2. Front-end dashboard at /short/dashboard.

== Changelog ==

= 1.6.0 =
* Added: self-service registration at `/short/register` — new accounts receive the `manage_yasyes_shortlink_links` capability (link management only, not full admin).
* Changed: dashboard and link CRUD access now checks the `manage_yasyes_shortlink_links` capability (or admin) instead of `manage_options` only.
* Changed: the "Manage Links" wp-admin menu is now available to anyone with the `manage_yasyes_shortlink_links` capability.
* Improved: Documentation page — new "Accounts & access" guide, full public-pages table, more FAQs.
* Improved: non-JS edit form fallback now sends the correct action and link ID.
* Fixed: malformed icon markup on the Documentation page.

= 1.5.1 =
Fixes found while testing 1.5.0 on a live install.
* Fixed: wp-admin submit without JavaScript ran inside the page callback (after output had started), so the redirect failed and PHP warnings were printed. It now runs on the `load-` hook.
* Fixed: the expired-link page (HTTP 410) was served without the plugin stylesheet and looked unstyled.
* Fixed: `.alert`, `.alert-success`, `.alert-error`, and `.modal-sm` were used by templates but missing from the stylesheet, so login/error messages and the logout modal were unstyled.
* Fixed: the expiry time was displayed with a doubled timezone offset; it now uses `wp_date()`, so the shown time matches the instant used for expiration.
* Fixed: the logout confirmation modal never opened on the dashboard (its click handler was not bound).

= 1.5.0 =
* Aliases may now contain hyphens and underscores, not only letters and numbers.
* Fixed: the admin bar was hidden on every single-word URL, not only on short links.
* Fixed: expiration dates now use the site timezone, so they no longer shift when the server timezone differs from WordPress.
* Fixed: an existing post, page, or term slug always wins over a conflicting alias, as documented.
* Fixed: `/short/logout` now requires a valid nonce (previous version allowed logout CSRF).
* Fixed: create/update/delete without JavaScript now works in wp-admin and shows error messages instead of failing silently.
* Fixed: search and pagination on the wp-admin "Manage Links" page were ignored server-side.
* Docs: corrected the auto-refresh interval shown on the Documentation page (30 seconds).

= 1.4.0 =
* Redesigned the "Manage Links" page to align with the Documentation page.
* Added email feedback feature.
* Added "Buy me a coffee" link and developer credit.
* CSS optimization and cleanup for a lighter plugin.
* Reduced statistics polling frequency to ease server load.

== Upgrade Notice ==

= 1.5.1 =
Fixes for wp-admin submit, expired-page styling, missing alert/modal styles, expiry display time, and the logout modal. Recommended for all 1.5.0 users.

= 1.5.0 =
Alias characters, expiry timezone, logout security, and wp-admin submit fixes. Existing expiry dates are re-read in the current site timezone — verify any link that expires soon.

= 1.4.0 =
UI refresh and feedback feature. Clear your browser cache after updating.
