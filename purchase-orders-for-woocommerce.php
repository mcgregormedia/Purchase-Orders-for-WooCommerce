<?php
/*
Plugin Name: Purchase Orders for WooCommerce
Plugin URI: https://mcgregormedia.co.uk
Description: Adds a Purchase Order payment method to WooCommerce.
Author: McGregor Media Web Design
Author URI: https://mcgregormedia.co.uk
Version: 1.11.1
Stable tag: 1.11.1
Text Domain: pofwc
Requires at least: 4.8
Requires PHP: 7.4
Requires plugins: woocommerce
WC requires at least: 3.0
WC tested up to: 9.3
License: GNU General Public License v2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/





if ( ! defined( 'ABSPATH' ) ) {
	
	exit; // Came directly here? Vamoose. Go on, scram.
	
}




use Automattic\WooCommerce\Utilities\OrderUtil;




/**
 * Declare HPOS compatibility
 * 
 * @since 1.9.0					Added compatibility
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );





/**
 * Loads translation files
 * 
 * @since 1.2.0					Added function
 */

function pofwc_load_textdomain() {
	
	load_plugin_textdomain( 'pofwc', false, basename( dirname( __FILE__ ) ) . '/languages' );
	
}
add_action( 'plugins_loaded', 'pofwc_load_textdomain' );




/**
 * Adds option on activation to check if newly activated. If true, runs WooCommerce check after register_activation_hook redirection
 * 
 * @since 1.2.0					Added function
 */

function pofwc_activate(){
	
    add_option( 'pofwc_activated', 'pofwc' );
	
}
register_activation_hook( __FILE__, 'pofwc_activate' );




/**
 * Adds the gateway to WC Available Gateways
 * 
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 *
 * @since 1.0.0
 */

function pofwc_add_to_gateways( $gateways ) {

	$gateways[] = 'WC_Gateway_Purchase_Order';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'pofwc_add_to_gateways' );




/**
 * Adds plugin page links
 * 
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links
 *
 * @since 1.0.0
 */

function pofwc_purchase_order_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=purchase_order_gateway' ) . '">' . __( 'Settings', 'pofwc' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pofwc_purchase_order_gateway_plugin_links' );




/**
 * Displays the invoice address
 *
 * @param  int $order_id	The order ID
 * @return string			The formatted html
 *
 * @since 1.10.0
 * @since 1.11.0	Added action hooks
 */
function pofwc_show_invoice_address( $order_id ){

	$order = wc_get_order( $order_id );
	
	echo '<h2>' . __( 'Purchase order information', 'pofwc' ) . '</h2>';

	echo '<p>';
		
		echo '<strong>' . __( 'Purchase order number', 'pofwc' ) . ':</strong> ' . $order->get_meta( '_purchase_order_number', true ) . '<br>';
		
		echo '<strong>' . __( 'Invoice address', 'pofwc' ) . ':</strong> <br>';
		
		echo ( $order->get_meta( '_purchase_order_company_name', true ) ) ? esc_html( $order->get_meta( '_purchase_order_company_name', true ) ) . '<br>' : '';
		
		echo ( $order->get_meta( '_purchase_order_address1', true ) ) ? esc_html( $order->get_meta( '_purchase_order_address1', true ) ) . '<br>' : '';
		
		echo ( $order->get_meta( '_purchase_order_address2', true ) ) ? esc_html( $order->get_meta( '_purchase_order_address2', true ) ) . '<br>' : '';
		
		echo ( $order->get_meta( '_purchase_order_address3', true ) ) ? esc_html( $order->get_meta( '_purchase_order_address3', true ) ) . '<br>' : '';
		
		echo ( $order->get_meta( '_purchase_order_town', true ) ) ? esc_html( $order->get_meta( '_purchase_order_town', true ) ) . '<br>' : '';
		
		echo ( $order->get_meta( '_purchase_order_county', true ) ) ? esc_html( $order->get_meta( '_purchase_order_county', true ) ) . '<br>' : '';
		
		echo ( $order->get_meta( '_purchase_order_postcode', true ) ) ? esc_html( $order->get_meta( '_purchase_order_postcode', true ) ) . '<br>' : '';
		
		echo ( $order->get_meta( '_purchase_order_email', true ) ) ? esc_html( $order->get_meta( '_purchase_order_email', true ) ) . '<br>' : '';

		do_action( 'pofwc_account_display_after_po_form', $order );
	echo '</p>';
		
}
add_action( 'woocommerce_view_order', 'pofwc_show_invoice_address', 20, 1 );




// Require files
require dirname( __FILE__ ) . '/class-purchase-order-gateway.php';