<?php
if ( ! defined( 'ABSPATH' ) ) {
	
	exit; // Came directly here? Vamoose.
	
}




add_action( 'plugins_loaded', 'pofwc_purchase_order_gateway_init', 11 );




function pofwc_purchase_order_gateway_init() {
	
	
	
	
	if ( class_exists( 'WC_Payment_Gateway' ) ) {
	
	
	

		class WC_Gateway_Purchase_Order extends WC_Payment_Gateway {
		
			
			
			
			/**
			 * Constructor for the gateway
			 *
			 * @access public
			 *
			 * @since 1.0.0
			 */
			public function __construct() {
		  
				$this->id                 = 'purchase_order_gateway';
				$this->icon               = apply_filters( 'woocommerce_offline_icon', '' );
				$this->has_fields         = true;
				$this->method_title       = __( 'Purchase Order', 'pofwc' );
				$this->method_description = __( 'Allows purchase order payments.', 'pofwc' );
			  
				// Load the settings.
				$this->init_form_fields();
				$this->init_settings();
			  
				// Define user set variables
				$this->title        = $this->get_option( 'title' );
				$this->status 		= $this->get_option( 'status' );
				$this->description  = $this->get_option( 'description' );
				$this->instructions = $this->get_option( 'instructions', $this->description );
			  
				// Actions
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'pofwc_thankyou' ) );
			  
				// Customer Emails
				add_action( 'woocommerce_email_after_order_table', array( $this, 'pofwc_email_instructions' ), 10, 3 );
				
				// Display meta data
				add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'pofwc_display_purchase_order_meta' ), 10, 1 );
				
				add_filter( 'wc_stripe_validate_checkout_required_fields', array( $this, 'pofwc_stripe_validate_checkout_unset_gateways_required_fields' ) );
				
			}
			
			

			
			/**
			 * Initialises Gateway Settings Form Fields
			 *
			 * @access public
			 * 
			 * @return string 		The formatted HTML
			 *
			 * @since 1.0.0
			 */
			
			public function init_form_fields() {
		  
				$this->form_fields = apply_filters( 'wc_offline_form_fields', array(
			  
					'enabled' => array(
						'title'   => __( 'Enable/Disable', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable purchase order payment', 'pofwc' ),
						'default' => 'no'
					),
			  
					'status' => array(
						'title'   => __( 'Checkout order status', 'pofwc' ),
						'type'    => 'select',
						'options' => array(
							'on-hold' => 'On Hold',
							'processing' => 'Processing'
						 ),
						'description' => __( 'This controls the order status after checkout.', 'pofwc' ),
						'desc_tip'    => true,
					),
					
					'title' => array(
						'title'       => __( 'Title', 'pofwc' ),
						'type'        => 'text',
						'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'pofwc' ),
						'default'     => __( 'Purchase order', 'pofwc' ),
						'desc_tip'    => true,
					),
					
					'description' => array(
						'title'       => __( 'Description', 'pofwc' ),
						'type'        => 'textarea',
						'description' => __( 'Payment method description that the customer will see on your checkout.', 'pofwc' ),
						'default'     => __( 'We will send our invoice to the address supplied.', 'pofwc' ),
						'desc_tip'    => true,
					),
					
					'instructions' => array(
						'title'       => __( 'Instructions', 'pofwc' ),
						'type'        => 'textarea',
						'description' => __( 'Instructions that will be added to the thank you page and emails.', 'pofwc' ),
						'default'     => 'We will send our invoice to the address supplied.',
						'desc_tip'    => true,
					),
				) );
			}
		
		
			

			/**
			 * Defines output for the order received page
			 *
			 * @access public
			 * 
			 * @return string 		The formatted HTML
			 *
			 * @since 1.0.0
			 */
			
			public function pofwc_thankyou() {
				
				if ( $this->instructions ) {
					
					echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
					
				}
				
			}
		
		
			

			/**
			 * Adds content to the WC emails
			 *
			 * @access public
			 *
			 * @param WC_Order $order
			 * @param bool $sent_to_admin
			 * @param bool $plain_text
			 *
			 * @since 1.0.0
			 */
			
			public function pofwc_email_instructions( $order, $sent_to_admin, $plain_text = false ) {
				
				if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status( $this->status ) ) {
					
					echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
					
				}
				
			}
		
		
			

			/**
			 * Processes the payment and returns the result
			 *
			 * @access public
			 *
			 * @param int $order_id		The order ID
			 * @return array			Redirects to thankyou page with relevant data
			 *
			 * @since 1.0.0
			 */
			
			public function process_payment( $order_id ) {
		
				$order = wc_get_order( $order_id );
				
				// Check if order total is zero
				if ( $order->get_total() > 0 ) {
					
					$order->update_status( $this->status, 'Awaiting invoice payment from purchase order.' );
				
				} else {
					
					$order->payment_complete();
					
				}
				
				// Reduce stock levels.
				wc_reduce_stock_levels( $order_id );
				
				if( isset( $_POST['purchase-order-number'] ) && trim( $_POST['purchase-order-number'] )!=''){
					
					update_post_meta( $order_id, '_purchase_order_number', sanitize_text_field( $_POST['purchase-order-number'] ) );
					update_post_meta( $order_id, '_purchase_order_company_name', sanitize_text_field( $_POST['purchase-order-company-name'] ) );
					update_post_meta( $order_id, '_purchase_order_address1', sanitize_text_field( $_POST['purchase-order-address1'] ) );
					update_post_meta( $order_id, '_purchase_order_address2', sanitize_text_field( $_POST['purchase-order-address2'] ) );
					update_post_meta( $order_id, '_purchase_order_address3', sanitize_text_field( $_POST['purchase-order-address3'] ) );
					update_post_meta( $order_id, '_purchase_order_town', sanitize_text_field( $_POST['purchase-order-town'] ) );
					update_post_meta( $order_id, '_purchase_order_county', sanitize_text_field( $_POST['purchase-order-county'] ) );
					update_post_meta( $order_id, '_purchase_order_postcode', sanitize_text_field( $_POST['purchase-order-postcode'] ) );
					
				}
				
				// Remove cart.
				WC()->cart->empty_cart();
				
				// Return thankyou redirect.
				return array(
					'result' 	=> 'success',
					'redirect'	=> $this->get_return_url( $order )
				);
				
			}

			
			
			
			/**
			 * Adds form fields to checkout gateway
			 *
			 * @access public
			 * 
			 * @return string 		The formatted HTML
			 *
			 * @since 1.0.0
			 */
			
			public function payment_fields(){
				?>
				
				<p class="form-row form-row-wide validate-required">
					<label for="purchase-order-number">Purchase order number<span class="required">*</span></label>
					<input type="text" id="purchase-order-number" name="purchase-order-number" class="input-text" placeholder="Purchase order number">
				</p>
				
				<p class="form-row form-row-wide">Where should we send the invoice to?</p>
				
				<p class="form-row form-row-wide validate-required">
					<label for="purchase-order-company-name">Company name<span class="required">*</span></label>
					<input type="text" id="purchase-order-company-name" name="purchase-order-company-name" class="input-text" placeholder="Invoice company name">
				</p>
				
				<p class="form-row form-row-wide validate-required">
					<label for="purchase-order-address1">Address line 1<span class="required">*</span></label>
					<input type="text" id="purchase-order-address1" name="purchase-order-address1" class="input-text" placeholder="Invoice address line 1">
				</p>
				
				<p class="form-row form-row-wide">
					<label for="purchase-order-address2">Address line 2</label>
					<input type="text" id="purchase-order-address2" name="purchase-order-address2" class="input-text" placeholder="Invoice address line 2">
				</p>
				
				<p class="form-row form-row-wide">
					<label for="purchase-order-address3">Address line 3</label>
					<input type="text" id="purchase-order-address3" name="purchase-order-address3" class="input-text" placeholder="Invoice address line 3">
				</p>
				
				<p class="form-row form-row-wide validate-required">
					<label for="purchase-order-town">Town<span class="required">*</span></label>
					<input type="text" id="purchase-order-town" name="purchase-order-town" class="input-text" placeholder="Invoice town">
				</p>
				
				<p class="form-row form-row-wide">
					<label for="purchase-order-county">County</label>
					<input type="text" id="purchase-order-county" name="purchase-order-county" class="input-text" placeholder="Invoice county">
				</p>
				
				<p class="form-row form-row-wide validate-required">
					<label for="purchase-order-postcode">Postcode<span class="required">*</span></label>
					<input type="text" id="purchase-order-postcode" name="purchase-order-postcode" class="input-text" placeholder="Invoice postcode">
				</p>
				
				<?php
			}


			

			/**
			 * Validates gateway checkout form fields
			 *
			 * @access public
			 * 
			 * @return mixed		boolean or formatted HTML string
			 *
			 * @since 1.0.0
			 */
			
			public function validate_fields() {
				
				// Check if required fields are set, if not add an error
				if ( ! $_POST['purchase-order-number'] ){
					
					wc_add_notice( __( 'Please enter a purchase order number.' ), 'error' );
					
				}
				
				if ( ! $_POST['purchase-order-company-name'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - company name is missing.' ), 'error' );
					
				}
				
				if ( ! $_POST['purchase-order-address1'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - address line 1 is missing.' ), 'error' );
					
				}
				
				if ( ! $_POST['purchase-order-town'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - town is missing.' ), 'error' );
					
				}
				
				if ( ! $_POST['purchase-order-postcode'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - postcode is missing.' ), 'error' );
					
				}
				
				else{
					return true;
				}
				
			}
			
			
			
			
			/**
			 * Bypasses the Stripe plugin's validation of fields
			 * 
			 * @param array $required_fields  		The required fields
			 *
			 * @since 1.2.0
			 */

			function pofwc_stripe_validate_checkout_unset_gateways_required_fields( $required_fields ){
				
				if( isset( $required_fields['purchase-order-number'] ) ){
					unset( $required_fields['purchase-order-number'] );
				}
				if( isset( $required_fields['purchase-order-company-name'] ) ){
					unset( $required_fields['purchase-order-company-name'] );
				}
				if( isset( $required_fields['purchase-order-address1'] ) ){
					unset( $required_fields['purchase-order-address1'] );
				}
				if( isset( $required_fields['purchase-order-address2'] ) ){
					unset( $required_fields['purchase-order-address2'] );
				}
				if( isset( $required_fields['purchase-order-address3'] ) ){
					unset( $required_fields['purchase-order-address3'] );
				}
				if( isset( $required_fields['purchase-order-town'] ) ){
					unset( $required_fields['purchase-order-town'] );
				}
				if( isset( $required_fields['purchase-order-county'] ) ){
					unset( $required_fields['purchase-order-county'] );
				}
				if( isset( $required_fields['purchase-order-postcode'] ) ){
					unset( $required_fields['purchase-order-postcode'] );
				}
				if( isset( $required_fields['purchase-order-query-name'] ) ){
					unset( $required_fields['purchase-order-query-name'] );
				}
				if( isset( $required_fields['purchase-order-query-email'] ) ){
					unset( $required_fields['purchase-order-query-email'] );
				}
				
			}			
			
			
			
			
			/**
			 * Displays meta data in order screen
			 *
			 * @access public
			 * 
			 * @return string 		The formatted HTML
			 *
			 * @since 1.0.0
			 */
			
			function pofwc_display_purchase_order_meta(){
				
				$order_id = get_the_ID();
				
				if( get_post_meta( $order_id, '_payment_method', true ) == 'purchase_order_gateway' && get_post_meta( $order_id, '_purchase_order_number', true ) ){
		
					echo '<h3>Purchase order information</h3>';
					echo '<p>';
						echo '<strong>Purchase order number:</strong> ' . get_post_meta( $order_id, '_purchase_order_number', true ) . '<br>';			
						echo '<strong>Invoice address:</strong> <br>';
						echo ( get_post_meta( $order_id, '_purchase_order_company_name', true ) ) ? esc_html( get_post_meta( $order_id, '_purchase_order_company_name', true ) ) . '<br>' : '';	
						echo ( get_post_meta( $order_id, '_purchase_order_address1', true ) ) ? esc_html( get_post_meta( $order_id, '_purchase_order_address1', true ) ) . '<br>' : '';	
						echo ( get_post_meta( $order_id, '_purchase_order_address2', true ) ) ? esc_html( get_post_meta( $order_id, '_purchase_order_address2', true ) ) . '<br>' : '';		
						echo ( get_post_meta( $order_id, '_purchase_order_address3', true ) ) ? esc_html( get_post_meta( $order_id, '_purchase_order_address3', true ) ) . '<br>' : '';	
						echo ( get_post_meta( $order_id, '_purchase_order_town', true ) ) ? esc_html( get_post_meta( $order_id, '_purchase_order_town', true ) ) . '<br>' : '';	
						echo ( get_post_meta( $order_id, '_purchase_order_county', true ) ) ? esc_html( get_post_meta( $order_id, '_purchase_order_county', true ) ) . '<br>' : '';	
						echo ( get_post_meta( $order_id, '_purchase_order_postcode', true ) ) ? esc_html( get_post_meta( $order_id, '_purchase_order_postcode', true ) ) . '<br>' : '';					
					echo '</p>';
				
				}
				
			}
			
			
			
			
		}
			
		
		
	
	}
  
  
  
  
}