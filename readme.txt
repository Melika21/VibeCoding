=== Request & Coupon System ===
Contributors: melikasohrabi
Tags: forms, woocommerce, coupon, sms, requests, persian, fa_IR
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modular WordPress and WooCommerce plugin for building forms, collecting requests and revisions, approving them as an expert and issuing WooCommerce coupons with SMS.ir notifications.

== Description ==

Request & Coupon System covers one workflow:

* Build a form and render it on the site or inside Elementor.
* Let a user submit the form, save it as a draft or a request, and revise it after a rejection.
* Let an expert review, approve or reject a request.
* Issue exactly one WooCommerce coupon for an approved request.
* Notify the customer and the administrator through SMS.ir patterns.
* Remind the customer before the coupon expires.
* Keep a full audit trail of every important action.

The plugin keeps WooCommerce as the source of truth for coupons and stores only the relations it needs in its own tables.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen.
3. Open Request & Coupon > Settings to configure the SMS.ir credentials.

On activation the plugin creates its database tables, the `rcs_expert` role and the scheduled tasks.

== Translation ==

The plugin is fully translated into Persian (Farsi). The translation files live in the `/languages` folder and load automatically when the site language is set to Persian (`fa_IR` or `fa`).

* `request-coupon-system.pot` — translation template
* `request-coupon-system-fa_IR.po` / `.mo` — Persian translation

To rebuild the compiled file after editing the `.po` file, run `msgfmt request-coupon-system-fa_IR.po -o request-coupon-system-fa_IR.mo`.

== Frequently Asked Questions ==

= Does it work without WooCommerce or Elementor? =

Yes. The plugin activates without them. WooCommerce is required for coupon issuing and Elementor is required for the form widget.

= Who can approve a request? =

Users with the `rcs_approve_requests` capability. The dedicated `rcs_expert` role receives it on activation.

== Changelog ==

= 1.0.0 =
* Initial skeleton: plugin lifecycle, database tables, expert role, admin menu, audit log and Elementor widget registration.
