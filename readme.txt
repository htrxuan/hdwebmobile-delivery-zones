=== HDWebmobile Delivery Zones ===
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

Add a delivery fee and a checkout note (such as pickup instructions) for the postcodes you choose, on classic and block checkout.

== Description ==

HDWebmobile Delivery Zones lets you add a fee (or a free local-pickup note) for orders shipping to postcodes you choose. There is nothing for a shopper to select at checkout -- the matching zone, and its fee, are resolved automatically from their own shipping address.

= What makes it different =
* **It works alongside your shipping methods, not instead of them.** Keep your WooCommerce shipping zones and rates as they are; a delivery zone adds its own fee line and note on top, for example a surcharge for outlying districts or pickup instructions for one neighbourhood.
* **Wildcard postcodes.** One pattern like "7001*" covers every postcode with that prefix, so a whole district is one line, not a long list.
* **A note at checkout, not just a fee.** Each zone can show its own message in the order summary, and it updates as the shopper edits their address -- including on the block-based checkout.
* **Nothing to choose, nothing to tamper with.** The zone is matched from the shipping postcode on the server, so there is no field at checkout for a shopper to pick a cheaper zone.
* **Free, with every feature included.** There is no paid tier or upgrade.

= Security by design =
* There is no zone-selection field on the checkout form at all. The applicable zone is always looked up from the shipping postcode WooCommerce's own customer object is currently tracking -- never a value read from the request.
* The fee actually charged is always re-read fresh from the matched zone's own saved configuration by its id, right before it's applied -- never a number supplied by anything else.

= Key Features =
* Any number of zones, each matched by exact postcodes or a wildcard prefix (e.g. "9021*")
* An optional fee per zone, and an optional note shown at checkout (e.g. pickup instructions)
* Works identically on classic and block-based checkout

= Limitations (please read before installing) =
* Postcode-based matching only -- no city/state or radius-based zones in this version
* One fee per matched zone; the first matching zone wins if postcode patterns overlap

= Enjoying this plugin? =
If it helps your site, please consider [leaving an honest review on WordPress.org](https://wordpress.org/support/plugin/hdwebmobile-delivery-zones/reviews/#new-post) -- it helps other WordPress users find it. Found a problem or have an idea? Please [open a support topic](https://wordpress.org/support/plugin/hdwebmobile-delivery-zones/) first so it can be fixed.

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
* Initial release: postcode-based delivery zone fees and checkout notes, resolved entirely server-side from the shopper's own shipping address, on classic and block checkout.
