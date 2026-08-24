=== Workparcel ===
Contributors: favouradjenuvurhe
Tags: parcel, shipment, tracking, delivery, logistics
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight parcel and shipment tracking system for WordPress.

== Description ==

Workparcel provides a simple shipment management and public parcel tracking workflow for WordPress websites.

Create shipments, generate tracking numbers, update shipment statuses, add tracking events, and let customers track parcels from a WordPress page.

== Features ==

* Shipment management
* Automatic tracking numbers
* Shipment statuses
* Tracking history
* Public tracking shortcode
* Search and filtering
* Responsive frontend
* WordPress capability checks
* Translation-ready strings
* Custom database tables

== Installation ==

1. Upload the `workparcel` folder to `/wp-content/plugins/`.
2. Activate Workparcel from Plugins.
3. Open Workparcel in the WordPress admin.
4. Create a page and add `[workparcel_tracking]`.
5. Configure the tracking page and basic settings under Workparcel > Settings.

== Frequently Asked Questions ==

= How do customers track a shipment? =

Create a page containing `[workparcel_tracking]`, then give customers their tracking number.

= Does Workparcel require an external service? =

No. The MVP stores shipment and tracking data in the WordPress database.

= Can I delete shipment data when uninstalling? =

Yes. Enable the data deletion option under Workparcel > Settings before uninstalling.

== Changelog ==

= 1.0.1 =
* Maintenance update.
* Improved release and update compatibility.
