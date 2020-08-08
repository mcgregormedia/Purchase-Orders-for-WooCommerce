=== Purchase Orders for WooCommerce ===
Contributors: mcgregormedia
Tags: WooCommerce, payment gateway, purchase order
Donate link: https://paypal.me/mcgregormedia
Requires at least: 4.8
Tested up to: 5.5
Requires PHP: 7.0
License: GNU General Public License v2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a Purchase Order payment method to WooCommerce.

== Description ==
Adds a Purchase Order payment method to WooCommerce.

Select if the order is to be On Hold or Processing after checkout. The gateway will ask for the purchase order number - select whether to also display text boxes for name and address of the company to be invoiced, and whether any of those are required fields. Don't forget to mark a field as not required if it's not to be displayed or your customer will not be able to check out!

The purchase order details will be displayed in the admin order screen, the customer order received screen and both admin and customer order emails.

= WooCommerce compatibility =

This plugin is compatible with WooCommerce 3.x and 4.x versions.

= GDPR information =

This plugin will gather and store a company's name, address and/or email address. This could also be construed as an individual's personal data. However, as the user has opted to pay by this method, it is suggested that the lawful basis for processing this data is contractual necessity. Processing is necessary in order to send the invoice to the user or user's representative. This data is stored as standard postmeta data and will be retained until the order is permanently deleted (but not if the order is trashed).

== Screenshots ==
1. The admin settings for the gateway.
2. Checkout page on Storefront theme.

== Installation ==
Install as usual by going to Plugins > Add New and searching for Purchase Orders for WooCommerce or download the plugin file and upload to your-site.com/wp-content/plugins.

== Changelog ==
1.7.10 08-08-2020
ADDED: required option for purchase order number field
UPDATED: Tested up to WordPress 5.5
UPDATED: Tested up to WooCommerce 4.4

1.7.9 13-07-2020
UPDATED: Tested up to WooCommerce 4.3
ADDED: Purchase order details now displayed on order-received page
ADDED: Purchase order details now displayed on order emails

1.7.8 03-06-2020
UPDATED: Tested up to WooCommerce 4.2

1.7.7 26-02-2020
UPDATED: Tested up to WooCommerce 4.0
UPDATED: Tested up to WordPress 5.4

1.7.6 24-01-2020
UPDATED: Tested up to WooCommerce 3.9

1.7.5 24-10-2019
UPDATED: Tested up to WordPress 5.3
TWEAK: Removed erroneous colon in pofwc_email_order_meta_fields()

1.7.4 13-08-2019
UPDATED: Tested up to WooCommerce 3.7
ADDED: Purchase order number now displayed on order-received page
ADDED: Purchase order number now added to order emails
TWEAK: Changed order of functionality in process_payment() to ensure order meta is available when order emails are sent

1.7.1 11-06-2019
FIXED: Fatal error on some systems from incomplete php tag.

1.7.0 10-06-2019
ADDED: Option to display/hide/require any of the purchase order checkout fields except PO number which is always displayed and required.
UPDATED: new options listed in readme 
UPDATED: en_GB translation

1.6.0 01-05-2019
ADDED: Compatibility with WooCommerce 3.6+ and WordPress 5.2+

1.5.0 15-02-2019
ADDED: Compatibility with WooCommerce 3.5+ and WordPress 5.1+

1.4.0 18-05-2018
FIXED: Notice that payment_method was called incorrectly
ADDED: Gateway description on checkout page
ADDED: Invoice email field
ADDED: various field settings
ADDED: en_GB translation, plugin now defaults to en_US
UPDATED: GDPR notice in readme to reflect the new email setting
REMOVED: 'Where should we send the invoice to?' text from checkout page as not really required

1.3.0 17-05-2018
ADDED: Compatibility with WooCommerce 3.4+ and WordPress 5.0+

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