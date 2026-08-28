=== Workparcel ===
Contributors: favouradjenuvurhe
Tags: shipment tracking, parcel tracking, order tracking, delivery, woocommerce
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track parcels and shipments on WordPress with a branded tracking page, a clean shipment dashboard, and WooCommerce order integration.

== Description ==

**Workparcel** turns WordPress into a full shipment management and parcel tracking system — for a courier business, a freight forwarder, a fulfillment team, or any WooCommerce store that ships physical products.

Create shipments, generate tracking numbers automatically, log every status change as a tracking event, and give your customers a live, branded tracking page — right on your own site, with your own domain and your own logo. No third-party tracking service, no external subscription, no iframe.

= Why Workparcel =

* **Built for shipping and logistics businesses** — manage every shipment from a professional, purpose-built dashboard instead of spreadsheets.
* **Branded, not generic** — your company name, logo, and accent color appear on the admin dashboard, the public tracking page, and shipment emails.
* **Works with WooCommerce** — link any WooCommerce order to a shipment and its tracking status shows automatically on the customer's order page and order emails. High-Performance Order Storage (HPOS) compatible.
* **Real tracking history** — every status change (Pending, Processing, Picked Up, In Transit, At Facility, Out for Delivery, Delivered, Failed Delivery, Cancelled) is logged with a location, a note, and a timestamp, and shown to customers as a visual progress stepper.
* **Automatic email notifications** — shipment-created and status-update emails can go out to the sender, the receiver, and the site admin, using a clean HTML template with your branding. Emails only send when a working SMTP sender is detected, so you never get silent failures from unreliable default mail delivery.
* **Extensible for developers** — filter and action hooks (`workparcel_statuses`, `workparcel_shipment_created`, `workparcel_shipment_updated`, `workparcel_status_changed`, `workparcel_tracking_event_added`) let you connect Workparcel to other plugins and custom workflows.
* **Fast and self-contained** — its own database tables, its own capabilities, no bloat, no page builder dependency, and admin assets that only load on Workparcel screens.

= Core Features =

* Shipment management dashboard with live stats and recent shipments
* Automatic, prefixable tracking number generation
* Custom shipment statuses with a visual progress stepper on the tracking page
* Full tracking history/timeline per shipment
* Public tracking page via the `[workparcel_tracking]` shortcode — plain, full-width, and theme-agnostic so it matches any WordPress theme instead of fighting it
* Search and status filtering in the admin shipment list
* Company branding: logo, name, address, phone, email, and a custom accent color
* WooCommerce order tracking integration (HPOS compatible)
* HTML email notifications for shipment creation and status updates (sender, receiver, admin)
* Role-based capabilities for viewing, creating, editing, and deleting shipments
* Translation-ready strings
* Developer hooks and filters for third-party integrations

= Who is Workparcel for? =

* Courier and delivery businesses that want their own branded tracking page instead of a third-party tracking widget
* WooCommerce stores that want order tracking without a heavyweight shipping suite
* Freight, cargo, and logistics companies that need an internal shipment dashboard
* Agencies building a shipping/tracking site for a client

== Installation ==

1. In your WordPress admin, go to **Plugins → Add New → Upload Plugin** and upload the Workparcel `.zip` file (or upload the `workparcel` folder to `/wp-content/plugins/` via FTP).
2. Activate Workparcel from the Plugins screen.
3. Open **Workparcel** in the WordPress admin menu to see your dashboard.
4. Go to **Workparcel → Settings** and fill in your company name, logo, and accent color.
5. Create a page, add the `[workparcel_tracking]` shortcode to it, then set that page under **Workparcel → Settings → General → Tracking page**.
6. Create your first shipment under **Workparcel → Add Shipment**.

== Frequently Asked Questions ==

= How do customers track a shipment? =

Create a page containing the `[workparcel_tracking]` shortcode and set it as your Tracking Page in Settings. Give the customer their tracking number — they enter it on that page to see live status and history.

= Does Workparcel require an external tracking service or subscription? =

No. Everything — shipments, statuses, and tracking history — is stored in your own WordPress database. There's no external API and no recurring cost.

= Does this work with WooCommerce? =

Yes. Workparcel is High-Performance Order Storage (HPOS) compatible. Open any WooCommerce order and use the "Workparcel Tracking" box to link it to a shipment by tracking number — the shipment's status then appears automatically on the customer's order view and order emails.

= Will Workparcel send emails automatically? =

Yes, if you want it to. Under Settings → Notifications you can choose to email the sender, the receiver, and/or the admin whenever a shipment is created or its status changes. To avoid emails silently failing, Workparcel only sends them when it detects a working SMTP sender (a plugin like WP Mail SMTP, FluentSMTP, Easy WP SMTP, or Post SMTP, or a defined SMTP_HOST). If no SMTP sender is detected, notifications are skipped rather than sent through an unreliable default mail transport.

= Can I change the colors to match my brand? =

Yes. Settings → Appearance includes a color picker that sets the accent color used across the admin dashboard, the public tracking page, and email notifications.

= Can I delete all shipment data when uninstalling? =

Yes. Enable "Delete data on uninstall" under Settings → Advanced before you uninstall the plugin.

= Is Workparcel translation-ready? =

Yes, all strings are wrapped for translation using the `workparcel` text domain.

== Screenshots ==

1. Dashboard — shipment totals, status breakdown, and recent shipments at a glance.
2. Shipments — a searchable, filterable list with color-coded status badges.
3. Add/Edit Shipment — sender, recipient, and shipping details organized into clear sections, plus the tracking timeline.
4. Public tracking page — a plain, full-width, theme-agnostic tracker with a live progress stepper.
5. Settings — company branding, your own accent color, and notification preferences.

== Changelog ==

= 1.0.7 =
* SEO and WordPress.org directory overhaul: rewritten description, features, and FAQ; added a Screenshots section with real interface previews; refreshed plugin icon and banner assets.
* Added a Company Profile section in Settings (logo, email, phone, address, website) used across the admin dashboard, the public tracking page, and email notifications.
* Added an Appearance tab in Settings with a color picker — pick your own accent color and it's applied consistently across the admin UI, the public tracking page, and shipment emails.
* Added HTML email notifications for shipment creation and status changes, sent to the sender, the receiver, and/or the admin (each independently toggleable in Settings → Notifications), using a branded "invoice style" template with shipment, sender, recipient, and fee details.
* Emails are only sent when a working SMTP sender is detected (a known SMTP plugin, a defined SMTP_HOST, or a configured mail transport) — otherwise notifications are silently skipped instead of relying on unreliable default mail delivery.
* Company logo can now be selected from the WordPress Media Library and appears in the admin header, the public tracking page, and email notifications.

= 1.0.6 =
* Public tracking page: removed the boxed/card container styling. The search form and results now render as plain, full-width content with a simple divider line, so it blends into any WordPress theme instead of fighting its design.
* Public tracking page now fills 100% of its containing column instead of capping its own width, so it always reads as "full length" regardless of theme.
* Admin: added a consistent, explicit style ruleset for all Workparcel form fields (text, email, number, date, datetime-local, textarea, select) — uniform padding, borders, border-radius, and focus states across every screen.
* Fixed several admin inputs that were missing an explicit type="text" attribute, which had been silently skipping some of the form-field styling.
* Added WooCommerce compatibility: declares High-Performance Order Storage (HPOS) compatibility, adds a "Workparcel Tracking" meta box on the WooCommerce order edit screen to link an order to a shipment by tracking number, and automatically displays tracking status on the customer-facing order view and order emails when linked.
* Added developer hooks for third-party/plugin integrations: `workparcel_statuses` filter, and `workparcel_shipment_created`, `workparcel_shipment_updated`, `workparcel_status_changed`, and `workparcel_tracking_event_added` actions.

= 1.0.5 =
* Fixed the public tracking page "squeezing"/zoom issue on mobile: the tracking number input had no explicit font size, which triggers an automatic page zoom on focus in mobile Safari/Chrome when a field's text is under 16px.
* Fixed the public tracking page layout to be fully fluid and defensively boxed (box-sizing, explicit widths, wrapping form row) so it can no longer be squeezed or overflowed by a theme's surrounding layout.
* Added a visual shipment progress stepper to the public tracking result (Pending → Processing → Picked Up → In Transit → At Facility → Out for Delivery → Delivered), with a distinct alert state for Failed Delivery/Cancelled shipments.
* Added a "Copy" button next to the tracking number for one-tap copying.
* Added Parcel Type, Weight, and Quantity to the public tracking result (previously only shown in admin).
* Added your Company Name as a small branded label above the tracking form (from Settings → General).
* Color-coded public status badges to match the admin Shipments list.
* Public tracking page now uses a responsive auto-fit details grid instead of a fixed breakpoint, so it reflows cleanly at any screen width.

= 1.0.4 =
* Fixed a critical fatal error on the Dashboard, Shipments, Add/Edit Shipment, and Settings screens caused by an unqualified class reference in the admin templates (PHP namespace resolution bug introduced in the 1.0.2 UI update).
* Fixed the admin menu icon: the crash on Dashboard made the menu item appear broken/unresponsive; this is resolved now that the page loads correctly.
* Fixed the Settings page branded header rendering outside the page container, which was breaking the layout of the settings form.
* Rebuilt the Settings tabs using a pure CSS technique so they work reliably even if a browser fails to load/cache the admin JavaScript.
* General verification pass on Add/Edit Shipment: confirmed the Add Shipment button, all form fields, and all status dropdowns render and submit correctly.

= 1.0.2 =
* Redesigned Workparcel admin interface.
* Added Workparcel admin branding.
* Added custom Workparcel admin menu icon.
* Improved dashboard interface.
* Improved shipment management interface.
* Improved shipment forms.
* Improved tracking event presentation.
* Improved responsive admin experience.
* Added/updated WordPress.org branding assets.
* Improved accessibility and general UI consistency.
* Fixed invalid nested-form markup on the Add/Edit Shipment screen.
* Updated internal plugin version.

= 1.0.0 =
* Initial MVP release.

== Upgrade Notice ==

= 1.0.7 =
Adds company branding (logo, accent color), HTML email notifications for shipment updates, and a WordPress.org directory refresh (screenshots, SEO-focused description).

= 1.0.6 =
Removes the boxed public tracking layout in favor of a plain, full-width, theme-agnostic design; polishes admin form fields; adds WooCommerce (HPOS-compatible) order tracking integration and developer hooks.

= 1.0.5 =
Fixes the mobile tracking page zoom/squeeze issue and adds a progress stepper, copy button, and richer shipment details to the public tracker.

= 1.0.4 =
Critical fix for a fatal error affecting the Dashboard, Shipments, Add/Edit Shipment, and Settings screens. Update immediately.

= 1.0.2 =
Admin UI and branding refresh. No database changes; safe to update.

= 1.0.0 =
Initial release.
