=== Workparcel ===
Contributors: favouradjenuvurhe
Tags: shipment tracking, parcel tracking, woocommerce, courier management, delivery management
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage shipments and track parcels directly from WordPress. Give customers a branded tracking page, connect shipments to WooCommerce orders, and manage deliveries from one dashboard.

== Description ==

**Workparcel** is a self-hosted shipment management and parcel tracking plugin for WordPress. It helps courier companies, delivery businesses, freight teams, and online stores create shipments, manage delivery progress, and let customers track parcels on their own website.

Create a shipment, generate a tracking number, record status updates, and share the tracking number with the customer. Customers can enter it on your branded tracking page to see the shipment's current status and tracking history — without being sent to a third-party tracking website.

Workparcel keeps shipment records in your WordPress database, so you manage the tracking experience on your own site and under your own domain.

= Why use Workparcel? =

* **Manage shipments from WordPress** — create, edit, search, and organize shipments from a dedicated dashboard with shipment statistics and status filters.
* **Give customers branded parcel tracking** — publish a tracking page on your website with your company identity and a clear shipment progress timeline.
* **Connect WooCommerce orders** — link a shipment to a WooCommerce order and display its tracking information in the customer's order view and order emails. Compatible with High-Performance Order Storage (HPOS).
* **Keep a complete tracking history** — record shipment status changes with locations, notes, and timestamps so staff and customers can follow progress.
* **Notify the right people** — send branded HTML emails when shipments are created or their status changes, with separate notification options for senders, receivers, and administrators.
* **Support scanning workflows** — use a USB or Bluetooth barcode scanner to create shipments, update statuses, and assign registered drivers or customers from a front-end Scan page.
* **Connect other systems** — use the toggleable REST API and developer hooks to integrate shipment workflows with other WordPress plugins and external applications.
* **Keep control of your data** — shipment and tracking records are stored in your own WordPress database. Workparcel does not require a separate third-party tracking subscription.

= Key features =

* Shipment dashboard with totals, status breakdown, and recent shipments.
* Create and manage shipments with sender, receiver, and delivery details.
* Automatically generated tracking numbers with an optional prefix.
* Configurable shipment statuses and a visual progress stepper on the public tracking page.
* Tracking timeline with status, location, note, and date/time information.
* Public tracking page using the `[workparcel_tracking]` shortcode.
* Searchable and filterable shipment list in the WordPress admin area.
* Company profile and branding, including company name, logo, contact details, and accent colour.
* WooCommerce order integration with HPOS compatibility.
* Branded HTML email notifications for shipment creation and status updates.
* Notification controls for sender, receiver, and site administrator.
* Printable shipment invoices with a scannable Code 128 barcode and shipment history. Use your browser's Print option to save the invoice as PDF.
* Optional shipment photo and Proof of Delivery attachments, including a delivery signature and photo.
* Optional front-end Scan page using the `[workparcel_scan]` shortcode.
* Customer registry for drivers and receivers, with automatically generated 11-character Scan IDs.
* Barcode scanning workflows for shipment creation, status updates, and driver/customer assignment.
* Toggleable REST API for shipments, customers, and public read-only tracking lookup.
* Developer actions and filters for custom integrations.
* Translation-ready strings and responsive public tracking layout.

= Who is Workparcel for? =

* **Courier and delivery companies** that want to manage shipments and provide customers with tracking on their own website.
* **WooCommerce store owners** who need to connect physical orders to shipment tracking without adopting a large shipping suite.
* **Freight and logistics businesses** that need shipment records, delivery status history, and internal workflows.
* **WordPress agencies and developers** building courier, delivery, cargo, or e-commerce websites for clients.

== Installation ==

1. In your WordPress dashboard, go to **Plugins → Add New → Upload Plugin** and upload the Workparcel ZIP file. You can also upload the extracted `workparcel` folder to `/wp-content/plugins/` using FTP or your hosting file manager.
2. Activate **Workparcel** from the Plugins screen.
3. Open **Workparcel** in the WordPress admin menu to view the dashboard.
4. Go to **Workparcel → Settings** and add your company details, logo, and accent colour.
5. Create a page and add the `[workparcel_tracking]` shortcode. Select that page under **Workparcel → Settings → General → Tracking page**.
6. Open **Workparcel → Add Shipment** to create your first shipment and tracking number.
7. For WooCommerce, open an order and use the **Workparcel Tracking** box to link its tracking number. For barcode scanning, enable the Scan page under **Settings → Scan & API** and add the `[workparcel_scan]` shortcode to a page.

== Frequently Asked Questions ==

= How do customers track a parcel? =

Create a WordPress page, add the `[workparcel_tracking]` shortcode, and select that page under **Workparcel → Settings → General → Tracking page**. Give customers the tracking number for their shipment. They can enter it on your website to view the current status and tracking history.

= Does Workparcel need an external tracking service or subscription? =

No. Workparcel stores shipment records, statuses, and tracking history in your WordPress database and provides the tracking page on your own website. It does not require a separate third-party tracking subscription.

= Can I use Workparcel with WooCommerce? =

Yes. Workparcel supports WooCommerce and declares compatibility with High-Performance Order Storage (HPOS). Link a shipment to an order using the **Workparcel Tracking** box on the order edit screen. The tracking information can then appear on the customer's order view and in order emails.

= Can I add my company's logo and colours? =

Yes. Add your company profile and logo in Workparcel Settings, then choose an accent colour under **Settings → Appearance**. Workparcel uses your branding across supported dashboard, tracking page, invoice, and email areas.

= Does Workparcel send shipment email notifications? =

Yes. Under **Settings → Notifications**, choose whether to notify the sender, receiver, and/or site administrator when a shipment is created or its status changes. Workparcel checks for a supported SMTP sender before sending by default to help avoid unreliable default mail delivery. You can enable **Send notifications anyway** when your mail setup is reliable but is not detected automatically.

= Can I print a shipment invoice? =

Yes. Open a shipment and select **View Invoice**. The invoice includes the tracking number, shipper and receiver details, package information, barcode, and shipment history. Use your browser's **Print → Save as PDF** option to save a PDF copy.

= Does Workparcel support Proof of Delivery? =

Yes. You can optionally attach a delivery signature and photo to a shipment using the WordPress Media Library. You can also add an optional shipment photo. When provided, supported attachments appear on the invoice and in shipment emails.

= Can I use a barcode scanner? =

Yes. Enable the Scan page under **Settings → Scan & API** and add the `[workparcel_scan]` shortcode to a WordPress page. Compatible USB and Bluetooth barcode scanners work like a keyboard. Depending on the user's permissions, the Scan page can be used to create shipments, update shipment statuses, or assign a registered driver or customer.

= What is a Scan ID? =

A Scan ID is an automatically generated, unique 11-character ID assigned to a driver or receiver registered under **Workparcel → Customers**. A registered person can use their Scan ID to access the enabled Scan page without a WordPress account, subject to the Scan page's permissions.

= Does Workparcel provide a REST API? =

Yes. The API can be enabled or disabled under **Settings → Scan & API**. It provides endpoints for shipment and customer workflows, plus a public read-only tracking lookup. Authenticated endpoints use WordPress capability checks. External applications can authenticate with a WordPress Application Password belonging to a user with the required Workparcel capability.

= Can developers extend Workparcel? =

Yes. Workparcel provides actions and filters for custom integrations, including shipment creation and update events, status changes, tracking events, customer creation, and shipment assignment. See the plugin code and REST API settings for integration details.

= Can I remove shipment data when uninstalling Workparcel? =

Yes. Enable **Delete data on uninstall** under **Workparcel → Settings → Advanced** before uninstalling the plugin. Leave this option disabled to retain your stored shipment data.

= Is Workparcel translation-ready? =

Yes. Workparcel uses the `workparcel` text domain and its user-facing strings are prepared for translation.

== Screenshots ==

1. Dashboard — shipment totals, status breakdown, and recent shipments.
2. Shipments — search and filter shipments with colour-coded status labels.
3. Add/Edit Shipment — enter sender, receiver, and shipment details and review the tracking timeline.
4. Public tracking page — let customers look up a parcel and follow its shipment progress.
5. Settings — configure company branding, appearance, and notification preferences.

== Changelog ==

= 1.1.2 =
* Improved: rewrote the plugin directory listing, feature descriptions, installation guidance, and FAQs to make Workparcel's shipment management, parcel tracking, WooCommerce, scanning, and API capabilities clearer.
* Updated: plugin version metadata to 1.1.2. No database schema changes are included in this release.

= 1.1.1 =
* Fixed: updating a shipment through the REST API (for example a PATCH with only a status) no longer wipes the other fields, resets the status to Pending or generates a new tracking number. Missing fields now keep their stored value.
* Fixed: tracking-event times were shifted by the site's UTC offset on the public tracking page, admin timeline, invoice and emails. Dates now use the site timezone and date format everywhere (including estimated delivery and created dates).
* Fixed: a PHP notice on every unfiltered Shipments list load.
* Fixed: saving a shipment could silently un-assign it when the assignee was inactive or beyond the first 100 customers, and erased free-text "Assigned To" names from before 1.1.0.
* Fixed: deleting a customer now un-assigns their shipments; customers need a name; duplicate tracking numbers show a clear message; tracking numbers are stored trimmed and uppercase.
* Privacy: scan and assignment notes no longer include the driver/customer name on the public tracking page or the public REST lookup. Who made an update is kept privately (new `actor` field, shown to staff only). Existing older notes are not rewritten.
* Scan page: failed requests now show an error instead of nothing; the nonce is refreshed automatically so page-caching plugins no longer break it; all server text is escaped before display; repeated unrecognized Scan IDs are temporarily blocked; new `workparcel_scan_actor_can` filter to restrict what drivers/customers may do.
* The invoice now has a real, scannable Code 128 barcode and prints correctly (table header and barcode were invisible with default print settings).
* Added Settings → Tracking: Default status (was stored but never used) and Currency (shown next to fees on invoices and emails).
* Added Settings → Notifications: "Send notifications anyway" plus a detection status line.
* Emails and buttons pick black or white text automatically so light accent colours stay readable.
* WooCommerce: plain-text order emails no longer receive HTML, and a deleted tracking page no longer produces a link to the current page.
* Admin UI: dashboard cards link to the filtered list and include Out for Delivery and Failed Delivery; Invoice button in the Shipments list; the Workparcel menu stays open on the Invoice and Add/Edit Customer screens; Settings tabs wrap on small screens and remember the open tab after saving; corrected the misleading Status help text; RTL-friendly spacing.
* Public tracking page: vertical progress stepper on phones (no stray connector lines), better contrast for upcoming steps.
* The database upgrade now runs on the first request after an update, not only in wp-admin. Removed unused admin scan files.

= 1.1.0 =
* Moved the barcode Scan tool out of wp-admin entirely: it's now the `[workparcel_scan]` shortcode, off by default (enable it under Settings → Scan & API), so it can be placed on any WordPress page and used without a wp-admin login.
* Added Workparcel → Customers: a registry of drivers and receivers. Each gets an automatically generated, unique 11-character Scan ID (unambiguous character set — no 0/O or 1/I/L mix-ups).
* The Scan page is protected: a logged-in staff member with shipment permissions can use it directly; anyone else must sign in with a registered, active Scan ID first. Every scan action is re-verified server-side — nothing is trusted from the page alone.
* Assigning a shipment to a driver/customer is now done through the Customers registry instead of free text — pick them from a dropdown on Add/Edit Shipment, or scan their Scan ID directly on the Scan page.
* Added a REST API under `workparcel/v1` (toggleable in Settings → Scan & API, on by default): shipments (list/get/create/update/delete/status), customers, and a public read-only tracking lookup. Authenticated endpoints use standard WordPress capability checks — pair with an Application Password for external access.
* Added a `workparcel_customer_created` action hook.
* Database schema updated (new `customer_id` column on shipments, new customers table — no data loss), applied automatically via the existing safe upgrade routine.

= 1.0.8 =
* Added a printable shipment invoice ("View Invoice" on any shipment): tracking number with a barcode-style header, shipper/receiver details, package info, container/driver info, Proof of Delivery, and full shipment history — styled to be cleaner and more modern than typical cargo-invoice templates, and works with your browser's Print → Save as PDF.
* Redesigned the shipment-created and status-update emails to match the new invoice look: barcode header, shipper/receiver blocks, container/driver info, Proof of Delivery images, and recent activity — still HTML-table based so it renders correctly across email clients.
* Added optional shipment photo and Proof of Delivery (signature + photo) fields on Add/Edit Shipment, using the WordPress Media Library. All optional — nothing is required to keep using the plugin as before.
* Added Container No. and Assigned To (driver/customer) fields to shipments.
* Added Workparcel → Scan: a barcode-scanner-friendly tool with three modes — Create Shipment (scan a new label to create it instantly), Update Status (scan, then confirm a new status), and Assign Driver/Customer (scan, then assign) — works with any USB/Bluetooth barcode scanner, which behaves like a keyboard.
* Added a `workparcel_shipment_assigned` action hook, fired when a shipment is assigned to a driver/customer.
* Refactored status-changing logic into a shared `Shipment::update_status()` method used by both the manual "Add Tracking Event" form and the new Scan tool, so behavior (including notification emails) stays consistent everywhere a status change happens.
* Generalized the company-logo image picker into a reusable component now also used for the new shipment photo and signature fields.
* Database schema updated (new columns only — no data loss) with a safe upgrade routine that runs automatically on your next admin page load after updating.

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

= 1.1.2 =
Listing and documentation refresh with updated version metadata. No database migration or additional setup is required.

= 1.1.1 =
Important fixes: REST updates no longer erase shipment data, event times use your site timezone, and driver names no longer appear on the public tracking page. Includes a small automatic database update (one new column).

= 1.1.0 =
Moves the Scan tool to a shortcode (off by default), adds a Customers registry with auto-generated Scan IDs, and adds a toggleable REST API. Note: if you were using Workparcel → Scan in wp-admin before, that menu is gone — enable the new shortcode under Settings → Scan & API instead.

= 1.0.8 =
Adds a printable shipment invoice, redesigned emails, optional shipment/Proof-of-Delivery photos, and a barcode scan tool for creating shipments, updating status, and driver assignment. Includes a safe automatic database update (new columns only).

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
