# Yasyes Short Link

WordPress **short-link** plugin built by [Yasyes Studio](https://yasyes.id) that creates short links directly on your root domain (`domain.com/code`) without any third-party service. Includes a minimal dashboard to manage links.

## Features

- **Root-domain short links** — short links like `domain.com/promo2026`
- **Manage from wp-admin** — "Manage Links" menu with a clean UI
- **Front-end dashboard** — also manage from `/short/dashboard`
- **Instant search** — filter alias/destination URL in real time
- **Auto-expire** — links can have an expiration date
- **Real-time stats** — total links, active links, and total clicks with auto-polling
- **Email feedback** — users can send suggestions/bugs directly to the admin
- **Lightweight** — small CSS/JS, system fonts, no heavy dependencies

## Installation

1. Download the plugin and extract it to `wp-content/plugins/yasyes-shortlink/`
2. Activate the plugin via **Plugins → Installed Plugins**
3. Log in via `/short` with an administrator account, or use the Yasyes Short Link menu in wp-admin

## Usage

1. **Create a link** — click "Create link", enter the destination URL, then save
2. **Copy the short link** — click the copy icon in the Actions column
3. **Edit / delete** — use the icons in the Actions column of each row
4. **Search** — type in the search field to filter the list
5. **Expiry** — set an expiration date when creating/editing a link

## Requirements

| | Minimum |
|---|---|
| WordPress | 6.0+ |
| PHP | 7.4+ |

## Structure
```
yasyes-shortlink/
├── yasyes-shortlink.php              # Bootstrap & plugin metadata
├── includes/                # Logic (model, service, AJAX, routing, admin)
├── templates/               # Admin & front-end templates
└── assets/                  # yasyes-shortlink.css & yasyes-shortlink.js
```

## License

GPL v2 or later.

---

Built with ☕ by [Yasyes Studio](https://yasyes.id) — support via [Buy Me A Coffee](https://buymeacoffee.com/yasir123983?utm_source=yasyes-shortlink&utm_campaign=github).
