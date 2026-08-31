=== Yasyes Short Link ===
Contributors: yasiryas
Tags: shortlink, short link, url shortener, link management, url
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create root domain short links with a minimal dashboard, instant search, and auto-expire features. Clean, fast, and self-hosted.

== Description ==

Yasyes Short Link lets you create short URLs directly on your root domain (e.g. `domain.com/promo2026`) without any external short-link service. All data is stored in your own WordPress database.

= Key Features =

* **Root domain short links** — clean URLs like `https://domain.com/promo2026`
* **WP Admin management** — a clean, easy-to-use "Manage Links" page
* **Front-end dashboard** — manage links from `/short/dashboard` as well
* **Instant search** — filter aliases or destination URLs in real time
* **Auto-expire** — set an expiration date on any link
* **Real-time statistics** — link count, active links, and total clicks update automatically
* **Feedback via email** — users can send suggestions or bug reports directly to the admin
* **Lightweight** — small CSS/JS, system fonts, no heavy dependencies

= Getting Started =

1. Activate the plugin, then open the **Yasyes Short Link** menu in wp-admin.
2. Click **Create Link**, enter a destination URL, and save.
3. Copy the generated short link and share it.
4. Manage, edit, or delete links at any time.

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

= Does this plugin use any third-party services? =

No. All links and statistics are stored in your own WordPress database.

= How do I send feedback? =

Use the **Feedback** button on the Manage Links page (wp-admin or front-end dashboard). Your message will be sent to the admin via email.

== Screenshots ==

1. Manage Links page in wp-admin.
2. Front-end dashboard at /short/dashboard.

== Changelog ==

= 1.4.0 =
* Redesigned the "Manage Links" page to align with the Documentation page.
* Added email feedback feature.
* Added "Buy me a coffee" link and developer credit.
* CSS optimization and cleanup for a lighter plugin.
* Reduced statistics polling frequency to ease server load.

== Upgrade Notice ==

= 1.4.0 =
UI refresh and feedback feature. Clear your browser cache after updating.
