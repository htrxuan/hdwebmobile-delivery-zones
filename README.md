# HDWebmobile Local Pickup & Delivery Zones

Postcode-based delivery zone fees, resolved server-side from the shopper's own address -- never accepted from the request.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-delivery-zones/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

Adds a fee (or a free local-pickup note) for orders shipping to postcodes you choose. There is nothing for a shopper to select at checkout -- the matching zone, and its fee, are resolved automatically from their own shipping address.

## Why this plugin exists

Several competing delivery-zone and local-pickup plugins computed the matching zone and its fee in client-side JavaScript from a zone list already embedded in the page, then trusted a hidden checkout field carrying either the chosen zone id or the fee amount when the order was placed (CWE-602, Client-Side Enforcement of Server-Side Security). Editing that field let a shopper claim a zone their address didn't belong to, or submit a lower fee outright.

This plugin has no such field to tamper with in the first place:

* There is no zone-selection field on the checkout form at all. The applicable zone is always looked up from the shipping postcode WooCommerce's own customer object is currently tracking -- never a value read from the request.
* The fee actually charged is always re-read fresh from the matched zone's own saved configuration by its id, right before it's applied -- never a number supplied by anything else.

## Features

* Any number of zones, each matched by exact postcodes or a wildcard prefix (e.g. "9021*")
* An optional fee per zone, and an optional note shown at checkout (e.g. pickup instructions)
* Works identically on classic and block-based checkout

## Limitations

* Postcode-based matching only -- no city/state or radius-based zones in this version
* One fee per matched zone; the first matching zone wins if postcode patterns overlap

## Installation

1. Upload to `/wp-content/plugins/hdwebmobile-delivery-zones`, or install through the WordPress plugins screen.
2. Activate. WooCommerce must already be installed and active.
3. Go to **WooCommerce > HDWebmobile > Delivery Zones** to add your zones.

## Development

```
includes/
  class-hdlpz-activator.php   activation check
  class-hdlpz-core.php        bootstraps admin + frontend
  class-hdlpz-repository.php  zone config + postcode matching -- see its docblock for the security design
  class-hdlpz-admin.php       hub tab, zone table, admin-post save handler
  class-hdlpz-frontend.php    fee/note applied from the customer's own shipping postcode
  class-hdlpz-hub.php         shared HDWebmobile admin hub (see the suite's other plugins)
```

## License

GPLv2 or later — https://www.gnu.org/licenses/gpl-2.0.html
