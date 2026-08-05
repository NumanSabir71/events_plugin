# Mithila Art Events
WordPress plugin for **Mithila Art / Mithila Center** — a full event booking system with volunteer and sponsor registration, paid ticketing, art-piece checkout, and content modules for leadership, partners, press, recognition, and artists.
| | |
|---|---|
| **Plugin name** | Mithila Art Events |
| **Text domain** | `mae` |
| **Version** | 1.5.8 |
| **License** | GPL-2.0+ |
| **Requires** | WordPress (PHP), Stripe and/or PayPal accounts for paid flows |
---
## Table of contents
1. [Overview](#overview)
2. [Feature summary](#feature-summary)
3. [Modules](#modules)
4. [Custom post types & taxonomies](#custom-post-types--taxonomies)
5. [Frontend pages & templates](#frontend-pages--templates)
6. [Shortcodes](#shortcodes)
7. [Registration types](#registration-types)
8. [Payments](#payments)
9. [Admin screens](#admin-screens)
10. [Database](#database)
11. [Helper functions](#helper-functions)
12. [File structure](#file-structure)
13. [Installation & setup](#installation--setup)
---
## Overview
The plugin is the events and cultural-content engine for the site. It:
- Registers **Events** with categories, dates, venues, free/paid tickets, and associated artists with sellable art pieces.
- Collects **attendee**, **volunteer**, and **sponsor** registrations (with optional online payment).
- Sells **art pieces** attached to event artists via Stripe.
- Provides **content CPTs** (Leadership, Partners, Press Releases, Recognition, Artists) with archives, single pages, and shortcodes.
- Sends **email notifications** to the admin and the registrant/buyer after each successful submission.
Bootstrap file: `mithila-art-events.php`. On `plugins_loaded` it instantiates every module class listed below.
---
## Feature summary
### Events
- Custom post type `mae_event` at `/events/`
- Hierarchical categories (`mae_event_cat`) at `/events/category/{slug}/`
- Event fields: start date, optional end date, time, location, free/paid type, ticket price (USD), registration closed toggle
- Featured image, title, editor content, excerpt
- Upcoming / ongoing / past status derived from dates
- Per-event **Registration Closed** flag (dates alone do not close registration)
- Category hero image and about-section image (term meta)
- Category-specific frontend layouts for selected slugs
### Associated artists (per event)
Each event can have multiple artists with:
- Name, optional URL slug (QR deep-link: `{event-url}/#{slug}`)
- Bio, photo, donate URL
- Art pieces (media library images) with caption, description, dimensions, and USD price
- Buy button on the event page when a price is set
### Registrations
Three registration types stored in one table:
| Type | How it is submitted | Payment |
|---|---|---|
| **Attendee** | Dedicated checkout page `[mae_checkout]` | Free, or Stripe/PayPal tickets |
| **Volunteer** | Modal form (event page or shortcode) | None |
| **Sponsor** | Modal form (event page or shortcode) | Optional contribution via Stripe/PayPal |
### Art sales
- Dedicated checkout URL: `/?mae_art_checkout=1&event_id=&artist_idx=&art_id=`
- Stripe PaymentIntent checkout
- Orders stored in `wp_mae_art_orders`
- Admin list under **Events → Art Orders**
### Content / storytelling modules
Standalone CPTs with Gutenberg/REST support:
- Leadership team
- Partners (carousel shortcode)
- Press releases
- Recognition & awards (logo grid + dedicated archive)
- Associated artists (profiles + art style taxonomy)
Dummy sample content is inserted once on first run for each content type.
### Payments & notifications
- Choose **Stripe** or **PayPal** globally
- PayPal sandbox / live mode
- Admin notification email setting
- Confirmation emails to users after registration or art purchase
---
## Modules
Each class lives in `includes/` and is loaded from the main plugin file.
### `MAE_Activator` — `class-mae-activator.php`
- Runs on plugin activate / deactivate
- Creates or upgrades database tables via `dbDelta`
- Seeds default options (gateway, keys, admin email)
- Flushes rewrite rules
- `maybe_upgrade_db()` runs on `admin_init` when `mae_db_version` ≠ `MAE_VERSION`
### `MAE_Event_CPT` — `class-mae-event-cpt.php`
- Registers `mae_event` post type and `mae_event_cat` taxonomy
- Loads single, archive, and taxonomy templates
- Term-specific templates: `templates/taxonomy-mae_event_cat-{slug}.php`
- Category image fields (hero + about)
- Enqueues frontend CSS (`mae-events.css`) and theme counter script when needed
- Archive title filter and single-event body class
### `MAE_Event_Meta` — `class-mae-event-meta.php`
- Meta box **Event Details** (dates, time, venue, type, price, registration closed)
- Meta box **Associated Artists** (repeatable rows, media picker, captions/prices)
- Admin styles and `assets/js/mae-admin-artists.js`
### `MAE_Registration` — `class-mae-registration.php`
- AJAX: `mae_submit` — volunteer / sponsor form handler
- AJAX: `mae_stripe_intent` — Stripe PaymentIntent for tickets or custom sponsor amounts
- Enqueues Stripe.js or PayPal SDK on event/archive/category pages
- Validates required fields, stores row in `mae_registrations`, sends emails
### `MAE_Payment` — `class-mae-payment.php`
- PayPal Orders API (create + capture)
- AJAX: `mae_paypal_order`, `mae_paypal_capture`
- Sandbox vs live base URL from settings
### `MAE_Checkout` — `class-mae-checkout.php`
- Shortcode `[mae_checkout]`
- Auto-creates page **Event Registration** (`/event-registration/`) if missing
- Full attendee form: personal details, emergency contact, special needs, multi-day attendance date, ticket quantity, payment
- AJAX: `mae_checkout_submit`
- Stores type `attendee` in `mae_registrations`
### `MAE_Art_Checkout` — `class-mae-art-checkout.php`
- Intercepts `?mae_art_checkout=1` and renders a purchase page
- AJAX: `mae_art_stripe_intent`, `mae_art_purchase`
- Stripe-only checkout for a single art piece
- Emails admin and buyer
### `MAE_Admin` — `class-mae-admin.php`
Submenus under **Events**:
- **Registrations** — filter by event; shows volunteer / sponsor / attendee rows
- **Art Orders** — filter by event; buyer, artist, piece, amount, status
### `MAE_Settings` — `class-mae-settings.php`
Submenu **Events → Settings**:
- Notification email
- Payment gateway (Stripe or PayPal)
- Stripe publishable + secret keys
- PayPal mode, client ID, secret