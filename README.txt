=== Purchase Orders for WooCommerce ===
Contributors: mcgregormedia
Tags: WooCommerce, payment gateway, purchase order
Donate link: https://paypal.me/mcgregormedia
Requires at least: 4.8
Tested up to: 6.6
Stable tag: 1.11.1
Requires PHP: 7.4
Requires plugin: woocommerce
WC requires at least: 3.0
WC tested up to: 9.3
License: GNU General Public License v2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a Purchase Order payment method to WooCommerce.

== Description ==
Adds a Purchase Order payment method to WooCommerce.

Select if the order is to be Pending, On Hold or Processing after checkout. The gateway will ask for the purchase order number - select whether to also display text boxes for name and address of the company to be invoiced, and whether any of those are required fields. Don't forget to mark a field as not required if it's not to be displayed or your customer will not be able to check out!

The purchase order details will be displayed in the admin order screen, the customer order received screen and both admin and customer order emails.

= WooCommerce compatibility =

This plugin is compatible with WooCommerce 3.x, 4.x, 5.x, 6.x, 7.x, 8.x and 9.x versions.

= HPOS compatibility =

This plugin is compatible with WooCommerce High Performance Order Storage (HPOS) and WordPress posts storage (legacy).

= Checkout Blocks Compatibility =

This plugin is not yet compatible with checkout blocks. 

= Compatibility with other plugins =

Some invoicing plugins require the meta keys of purchase order data to display this data on invoices. The meta keys used in this plugin are listed below:

_purchase_order_number
_purchase_order_company_name
_purchase_order_address1
_purchase_order_address2
_purchase_order_address3
_purchase_order_town
_purchase_order_county
_purchase_order_postcode
_purchase_order_email

= Order status =

Select the order status to apply to the order to when a customer checks out using a Purchase Order. All order statuses are available for selection including any custom statuses that may have been added. Be aware that if you set the status to Pending, neither you nor the customer will receive an order email after checkout - this is standard WooCommerce functionality. By default, order emails will be sent when a status is changed from Pending to On Hold or Processing.

= Custom fields =

You can add your own fields to the checkout form by adding custom HTML to the action hook in the PO checkout form:

pofwc_form_after_po_form

To add a text input field after the PO number field, the code should look something like this:


function custom_checkout_field_after_po_form() {

    echo '<p class="form-row form-row-wide">';
        echo '<label for="YOUR-FIELD-ID">YOUR FIELD LABEL TEXT</label>';
        echo '<input type="text" id="YOUR-FIELD-ID" name="YOUR_FIELD_NAME" class="input-text" placeholder="YOUR FIELD PLACEHOLDER">';
    echo '</p>';
     
}
add_action( 'pofwc_form_after_po_form', 'custom_checkout_field_after_po_form' );


You can of course change the form HTML to output a different field type such as a `select` dropdown or `textarea`. 

To save your custom field, hook into the woocommerce_checkout_update_order_meta action as in the example below:

function custom_checkout_field_update_order_meta( $order_id ) {

    $order = wc_get_order( $order_id );

    if ( ! empty( $_POST['YOUR_FIELD_NAME'] ) ) {
        $order->update_meta_data( 'YOUR_FIELD_NAME', sanitize_text_field( $_POST['YOUR_FIELD_NAME'] ) );
    }

    $order->save();
}
add_action( 'woocommerce_checkout_update_order_meta', 'custom_checkout_field_update_order_meta', 10, 1 );

There are four places the PO data can be displayed: the order thank you page, the order emails, the customer order history, and the admin Edit Order screen. To display your custom field data, use one of the following action hooks to add your data in the required place:

pofwc_thankyou_display_after_po_form
pofwc_email_display_after_po_form
pofwc_account_display_after_po_form
pofwc_admin_display_after_po_form

To output your example text input from above in the checkout thank you page, the Edit Order screen and customer order history, the code should look something like this:

function display_custom_order_data_after_po_form( $order ) {

    echo ( $order->get_meta( 'YOUR_FIELD_NAME', true ) ) ? esc_html( $order->get_meta( 'YOUR_FIELD_NAME', true ) ) . '<br>' : '';
}
add_action( 'pofwc_thankyou_display_after_po_form', 'display_custom_order_data_after_po_form', 10, 1 );
add_action( 'pofwc_account_display_after_po_form', 'display_custom_order_data_after_po_form', 10, 1 );
add_action( 'pofwc_admin_display_after_po_form', 'display_custom_order_data_after_po_form', 10, 1 );

Displaying the data in the emails is slightly different as data escaping is done later in the output process:

function display_email_custom_order_data_after_po_form( $order ) {

    echo $order->get_meta( 'YOUR_FIELD_NAME', true ) ? $order->get_meta( 'YOUR_FIELD_NAME', true ) : '';
}
add_action( 'pofwc_email_display_after_po_form', 'display_email_custom_order_data_after_po_form', 10, 1 );

This code all goes in your functions.php file in your child theme - don't place this code in a parent theme (unless it's one you maintain yourself) as it will be overwritten when the theme is updated.

= GDPR information =

This plugin will gather and store a company's name, address and/or email address. This could also be construed as an individual's personal data. However, as the user has opted to pay by this method, it is suggested that the lawful basis for processing this data is contractual necessity. Processing is necessary in order to send the invoice to the user or user's representative. This data is stored as standard postmeta data and will be retained until the order is permanently deleted (not trashed).

== Screenshots ==
1. The admin settings for the gateway.
2. Checkout page on Storefront theme.

== Installation ==
Install as usual by going to Plugins > Add New and searching for Purchase Orders for WooCommerce or download the plugin file and upload to your-site.com/wp-content/plugins.

== Changelog ==
1.11.1 27-09-2024
TWEAK: format readme code text

1.11.0 27-09-2024
ADDED: action hooks in PO checkout form, thank you page, order history and admin Edit Order screen
UPDATED: Compatibility with WooCommerce 9.3

1.10.1 23-05-2024
FIXED: PHP notice "Function is_internal_meta_key was called incorrectly"
FIXED: deprecated dynamic properties for PHP 8.2
FIXED: errors in fr-CA translation
CHECKED: reported issues with order emails not sending. Could not replicate issue.

1.10.0 19-05-2024
FIXED: Missing translation strings
ADDED: French Canadian translation (H/T Marc-André)
ADDED: Plugin dependencies
UPDATED: Compatibility with WooCommerce 8.9
REMOVED: Notice displayed if WooCommerce is not activated
REMOVED: Legacy meta data functions so WooCommerce now handles compatibility

1.9.1 20-12-2023
UPDATED: compatibility with WooCommerce 8.4

1.9.0 15-11-2023
ADDED: HPOS compatibility
UPDATED: Tested up to WordPress 6.5
UPDATED: compatibility with WooCommerce 8.3
UPDATED: minimum PHP version to 7.4

1.8.5 06-09-2023
ADDED: ability to select from all order statuses, including custom statuses
UPDATED: compatibility with WooCommerce 8.1

1.8.4 28-08-2023
ADDED: option to not display PO details in order emails
UPDATED: Tested up to WordPress 6.3
UPDATED: compatibility with WooCommerce 8.0

1.8.3 14-03-2023
UPDATED: Tested up to WordPress 6.2
UPDATED: compatibility with WooCommerce 7.5

1.8.2 27-10-2022
UPDATED: Tested up to WordPress 6.1
UPDATED: compatibility with WooCommerce 7.1

1.8.1 25-10-2021
ADDED: Added wp_kses_post() to email output
UPDATED: compatibility with WooCommerce 5.8

1.8 30-06-2021
ADDED: Order pending status
UPDATED: Tested up to WordPress 5.8
UPDATED: compatibility with WooCommerce 5.5
UPDATED: ReadMe

1.7.16 08-03-2021
FIXED: Company name translation not working on the frontend
UPDATED: Tested up to WordPress 5.7
UPDATED: compatibility with WooCommerce 5.1

1.7.15 14-12-2020
FIXED: Issue with customer not being able to checkout if PO number is empty but is not required
UPDATED: English (UK) translation files
TWEAK: Replaced POT file

1.7.14 14-12-2020
ADDED: English (Canada) translation

1.7.13 24-11-2020
UPDATED: Tested up to WordPress 5.6

1.7.12 11-11-2020
UPDATED: compatibility with WooCommerce 4.7

1.7.11 15-10-2020
UPDATED: compatibility with WooCommerce 4.6.x

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