=== Purchase Orders for WooCommerce ===
Contributors: mcgregormedia
Tags: WooCommerce, payment gateway, purchase order
Donate link: https://paypal.me/mcgregormedia
Requires at least: 4.8
Tested up to: 4.9
Requires PHP: 7.0
License: GNU General Public License v2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a Purchase Order payment method to WooCommerce.

== Description ==
Adds a Purchase Order payment method to WooCommerce.

Select if the order is to be On Hold or Processing after checkout. The gateway will ask for the purchase order number, and name and address of the company to be invoiced.

= WooCommerce compatibility =

This plugin is compatible with WooCommerce 3.x versions.

= GDPR information =

This plugin will gather and store a company's name and address. This could also be construed as an individual's personal data. However, as the user has opted to pay by this method, it is suggested that the legal basis for processing this data is contractual obligation. Processing is necessary in order to send the invoice to the user or user's representative. This data is stored as standard postmeta data and will be retained until the order is permanently deleted (but not if the order is trashed).

== Screenshots ==
1. The admin settings for the gateway.
2. Checkout page on Storefront theme.

== Installation ==
Install as usual by going to Plugins > Add New and searching for Purchase Orders for WooCommerce or download the plugin file and upload to your-site.com/wp-content/plugins.

== Changelog ==
1.2.0 27-04-2018
FIXED: Stripe plugin validation conflict
ADDED: check if WooCommerce is installed upon activation
ADDED: GDPR notice in ReadMe
REMOVED: Stripe plugin conflict warning

1.1.1 08-02-2018
ADDED: Stripe plugin conflict warning
UPDATED: WC requires at least and WC tested up to tags for WooCommerce 3.3.x


1.1.0 12-10-2017
ADDED: WC requires at least and WC tested up to tags for WooCommerce 3.2+
UPDATED: bumped WP tested up to tag

1.0.0 19-09-2017
Initial release