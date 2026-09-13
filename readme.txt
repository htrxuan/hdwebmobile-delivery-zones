=== HDWebmobile Local Pickup & Delivery Zones ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, delivery zones, local pickup, shipping fee, postcode
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Postcode-based delivery zone fees, resolved server-side from the shopper's own address -- never accepted from the request.

== Description ==

HDWebmobile Local Pickup & Delivery Zones lets you add a fee (or a free local-pickup note) for orders shipping to postcodes you choose. There is nothing for a shopper to select at checkout -- the matching zone, and its fee, are resolved automatically from their own shipping address.

= Why this plugin exists =
Several competing delivery-zone and local-pickup plugins computed the matching zone and its fee in client-side JavaScript from a zone list already embedded in the page, then trusted a hidden checkout field carrying either the chosen zone id or the fee amount when the order was placed (CWE-602, Client-Side Enforcement of Server-Side Security). Editing that field let a shopper claim a zone their address didn't belong to, or submit a lower fee outright.

This plugin has no such field to tamper with in the first place:

* There is no zone-selection field on the checkout form at all. The applicable zone is always looked up from the shipping postcode WooCommerce's own customer object is currently tracking -- never a value read from the request.
* The fee actually charged is always re-read fresh from the matched zone's own saved configuration by its id, right before it's applied -- never a number supplied by anything else.

= Key Features =
* Any number of zones, each matched by exact postcodes or a wildcard prefix (e.g. "9021*")
* An optional fee per zone, and an optional note shown at checkout (e.g. pickup instructions)
* Works identically on classic and block-based checkout

= Limitations (please read before installing) =
* Postcode-based matching only -- no city/state or radius-based zones in this version
* One fee per matched zone; the first matching zone wins if postcode patterns overlap

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-delivery-zones` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Go to **WooCommerce > HDWebmobile > Delivery Zones** to add your zones.

== How to Use ==

= 1. Add a zone =
Give it a name, a comma-separated list of postcodes or wildcard patterns, a fee, and an optional note.

= 2. It applies automatically =
When a shopper's shipping postcode matches, the fee and note appear at checkout with nothing for them to select.

== Screenshots ==

1. The delivery zones table under WooCommerce > HDWebmobile.
2. A matched zone's fee and note shown at checkout.

== Changelog ==

= 1.0.0 =
* Initial release: postcode-based delivery zone fees, resolved entirely server-side from the shopper's own shipping address.
