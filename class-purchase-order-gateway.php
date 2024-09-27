<?php
if ( ! defined( 'ABSPATH' ) ) {
	
	exit; // Came directly here? Vamoose.
	
}




use Automattic\WooCommerce\Utilities\OrderUtil;




add_action( 'plugins_loaded', 'pofwc_purchase_order_gateway_init', 11 );




function pofwc_purchase_order_gateway_init() {
	
	
	
	
	if ( class_exists( 'WC_Payment_Gateway' ) ) {
	
	
	

		class WC_Gateway_Purchase_Order extends WC_Payment_Gateway {



			/**
			 * @since 1.10.1	Declared properties
			 */
			public $title;
			public $status;
			public $description;
			public $instructions;
			public $po_number_label;
			public $po_number_required;
			public $company_name_display;
			public $address_1_display;
			public $address_2_display;
			public $address_3_display;
			public $town_display;
			public $county_display;
			public $postcode_display;
			public $email_display;
			public $company_name_required;
			public $address_1_required;
			public $address_2_required;
			public $address_3_required;
			public $town_required;
			public $county_required;
			public $postcode_required;
			public $email_required;
			public $po_show_in_email;
		
			
			
			
			/**
			 * Constructor for the gateway
			 *
			 * @access public
			 *
			 * @since 1.0.0
			 * @since 1.4.0			Added field settings
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
				$this->title        			= $this->get_option( 'title' );
				$this->status 					= $this->get_option( 'status' );
				$this->description  			= $this->get_option( 'description' );
				$this->instructions 			= $this->get_option( 'instructions', $this->description );
				$this->po_number_label 			= $this->get_option( 'po_number_label' );
				$this->po_number_required		= $this->get_option( 'po_number_required' );
				$this->company_name_display 	= $this->get_option( 'company_name_display' );
				$this->address_1_display 		= $this->get_option( 'address_1_display' );
				$this->address_2_display 		= $this->get_option( 'address_2_display' );
				$this->address_3_display 		= $this->get_option( 'address_3_display' );
				$this->town_display 			= $this->get_option( 'town_display' );
				$this->county_display 			= $this->get_option( 'county_display' );
				$this->postcode_display 		= $this->get_option( 'postcode_display' );
				$this->email_display 			= $this->get_option( 'email_display' );
				$this->company_name_required 	= $this->get_option( 'company_name_required' );
				$this->address_1_required 		= $this->get_option( 'address_1_required' );
				$this->address_2_required 		= $this->get_option( 'address_2_required' );
				$this->address_3_required 		= $this->get_option( 'address_3_required' );
				$this->town_required 			= $this->get_option( 'town_required' );
				$this->county_required 			= $this->get_option( 'county_required' );
				$this->postcode_required 		= $this->get_option( 'postcode_required' );
				$this->email_required 			= $this->get_option( 'email_required' );
				$this->po_show_in_email 		= $this->get_option( 'po_show_in_email' );
			  
				// Actions
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'pofwc_thankyou' ) );
			  
				// Emails
				add_action( 'woocommerce_email_after_order_table', array( $this, 'pofwc_email_instructions' ), 10, 3 );
				add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'pofwc_email_order_meta_fields' ), 10, 3 );
				
				// Display meta data
				add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'pofwc_display_purchase_order_meta' ), 10, 1 );
				add_action( 'woocommerce_thankyou', array( $this, 'pofwc_add_po_number_to_order_received_page' ), 1 );
				
				// Other
				add_filter( 'wc_stripe_validate_checkout_required_fields', array( $this, 'pofwc_stripe_validate_checkout_unset_gateways_required_fields' ) );
				
			}




			/**
			 * Gets WooCommerce order statuses
			 * 
			 * @since 1.8.5			Added ability to select from all order statuses, including custom statuses
			 */
			private function get_order_statuses(){

				$statuses = wc_get_order_statuses();
				return $statuses;

			}
			
			

			
			/**
			 * Initialises Gateway Settings Form Fields
			 *
			 * @access public
			 * 
			 * @return string 		The formatted HTML
			 *
			 * @since 1.0.0
			 * @since 1.4.0			Added field settings
			 * @since 1.7.0			Added display and required options for fields
			 * @since 1.7.10		Added required option for purchase order number field
			 * @since 1.8.4			Added option to not display on order emails
			 * @since 1.8.5			Added ability to select from all order statuses, including custom statuses
			 */
			
			public function init_form_fields() {

				$statuses = $this->get_order_statuses();
		  
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
						'options' => $statuses,
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
					
					'po_number_label' => array(
						'title'       => __( 'Purchase order number label', 'pofwc' ),
						'type'        => 'text',
						'description' => __( 'This controls the label of the purchase order number field the customer sees during checkout.', 'pofwc' ),
						'default'     => __( 'Purchase order number', 'pofwc' ),
						'desc_tip'    => true,
					),				
					
					'po_number_required' => array(
						'title'   => __( 'Purchase order number', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Is purchase order number a required field?', 'pofwc' ),
						'default' => 'yes'
					),			
					
					'company_name_display' => array(
						'title'   => __( 'Company name', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Display company name field?', 'pofwc' ),
						'default' => 'yes'
					),				
					
					'company_name_required' => array(
						'type'    => 'checkbox',
						'label'   => __( 'Is company name a required field? Uncheck this if this field is not to be displayed.', 'pofwc' ),
						'default' => 'yes'
					),
					
					'address_1_display' => array(
						'title'   => __( 'Address line 1', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Display address line 1 field?', 'pofwc' ),
						'default' => 'yes'
					),
					
					'address_1_required' => array(
						'type'    => 'checkbox',
						'label'   => __( 'Is address line 1 a required field? Uncheck this if this field is not to be displayed.', 'pofwc' ),
						'default' => 'yes'
					),
					
					'address_2_display' => array(
						'title'   => __( 'Address line 2', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Display address line 2 field?', 'pofwc' ),
						'default' => 'yes'
					),
					
					'address_2_required' => array(
						'type'    => 'checkbox',
						'label'   => __( 'Is address line 2 a required field? Uncheck this if this field is not to be displayed.', 'pofwc' ),
						'default' => 'no'
					),
					
					'address_3_display' => array(
						'title'   => __( 'Address line 3', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Display address line 3 field?', 'pofwc' ),
						'default' => 'yes'
					),
					
					'address_3_required' => array(
						'type'    => 'checkbox',
						'label'   => __( 'Is address line 3 a required field? Uncheck this if this field is not to be displayed.', 'pofwc' ),
						'default' => 'no'
					),
					
					'town_display' => array(
						'title'   => __( 'City', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Display city field?', 'pofwc' ),
						'default' => 'yes'
					),
					
					'town_required' => array(
						'type'    => 'checkbox',
						'label'   => __( 'Is city a required field? Uncheck this if this field is not to be displayed.', 'pofwc' ),
						'default' => 'yes'
					),
					
					'county_display' => array(
						'title'   => __( 'State', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Display state field?', 'pofwc' ),
						'default' => 'yes'
					),
					
					'county_required' => array(
						'type'    => 'checkbox',
						'label'   => __( 'Is state a required field? Uncheck this if this field is not to be displayed.', 'pofwc' ),
						'default' => 'no'
					),
					
					'postcode_display' => array(
						'title'   => __( 'Zip', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Display zip field?', 'pofwc' ),
						'default' => 'yes'
					),	
					
					'postcode_required' => array(
						'type'    => 'checkbox',
						'label'   => __( 'Is zip a required field? Uncheck this if this field is not to be displayed.', 'pofwc' ),
						'default' => 'yes'
					),					
					
					'email_display' => array(
						'title'   => __( 'Email', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Display email field?', 'pofwc' ),
						'default' => 'yes'
					),					
					
					'email_required' => array(
						'type'    => 'checkbox',
						'label'   => __( 'Is email a required field? Uncheck this if this field is not to be displayed.', 'pofwc' ),
						'default' => 'yes'
					),

					'po_show_in_email' => array(
						'title'   => __( 'Show purchase order details in order emails', 'pofwc' ),
						'type'    => 'checkbox',
						'label'   => __( 'Show details', 'pofwc' ),
						'default' => 'yes',
						'description' => __( 'Choose to display the purchase order details in order emails. Default is checked.', 'pofwc' ),
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
			 * @param 			WC_Order $order
			 * @param bool 		$sent_to_admin
			 * @param bool 		$plain_text
			 *
			 * @since 1.0.0
			 * @since 1.8.1		Added wp_kses_post() to output
			 */
			
			public function pofwc_email_instructions( $order, $sent_to_admin, $plain_text = false ) {
				
				if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status( $this->status ) ) {
					
					echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
					
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
			 * @since 1.4.0				Added _purchase_order_email
			 * @since 1.7.4				Changed order of functionality to ensure order meta is available when order emails are sent
			 * @since 1.9.0				Added HPOS compatibility
			 * @since 1.10.0			Removed legacy meta functions so WooCommerce now handles compatibility
			 */
			
			public function process_payment( $order_id ) {
		
				$order = wc_get_order( $order_id );
				
				// Add order meta data
				if( isset( $_POST['purchase-order-number'] ) && trim( $_POST['purchase-order-number'] ) != ''){
					
					$order->update_meta_data( '_purchase_order_number', sanitize_text_field( $_POST['purchase-order-number'] ) );
					$order->update_meta_data( '_purchase_order_company_name', sanitize_text_field( $_POST['purchase-order-company-name'] ) );
					$order->update_meta_data( '_purchase_order_address1', sanitize_text_field( $_POST['purchase-order-address1'] ) );
					$order->update_meta_data( '_purchase_order_address2', sanitize_text_field( $_POST['purchase-order-address2'] ) );
					$order->update_meta_data( '_purchase_order_address3', sanitize_text_field( $_POST['purchase-order-address3'] ) );
					$order->update_meta_data( '_purchase_order_town', sanitize_text_field( $_POST['purchase-order-town'] ) );
					$order->update_meta_data( '_purchase_order_county', sanitize_text_field( $_POST['purchase-order-county'] ) );
					$order->update_meta_data( '_purchase_order_postcode', sanitize_text_field( $_POST['purchase-order-postcode'] ) );
					$order->update_meta_data( '_purchase_order_email', sanitize_text_field( $_POST['purchase-order-email'] ) );
					
				}
				
				// Check if order total is zero
				if ( $order->get_total() > 0 ) {
					
					$order->update_status( $this->status, 'Awaiting invoice payment from purchase order.' );
				
				} else {
					
					$order->payment_complete();
					
				}
				
				// Reduce stock levels.
				wc_reduce_stock_levels( $order_id );
				
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
			 * @since 1.4.0			Added description, field settings
			 * @since 1.7.0			Added display and required options for fields
			 * @since 1.7.10		Added required option for purchase order number field
			 * @since 1.7.16		Fixed company name translation not working on the frontend
			 * @since 1.10.0		Fixed missing translation strings
			 * @since 1.11.0		Added action hooks
			 */
			
			public function payment_fields(){

				?>
				
				<p class="form-row form-row-wide">
					 <?php echo $this->description; ?>
				</p>
				
				<?php
				$po_number_label = ( $this->po_number_label != '' ) ? $this->po_number_label : __( 'Purchase order number', 'pofwc' );
				$po_number_required_text = ( $this->po_number_required == 'yes' ) ? '<span class="required">*</span>' : ''; 
				$po_number_required_class = ( $this->po_number_required == 'yes' ) ? 'validate-required' : '';
				?>
				
				<p class="form-row form-row-wide  <?php echo $po_number_required_class; ?>">
					<label for="purchase-order-number"><?php echo esc_html( $po_number_label ); ?><?php echo $po_number_required_text; ?></label>
					<input type="text" id="purchase-order-number" name="purchase-order-number" class="input-text" placeholder="<?php echo esc_attr( $po_number_label ); ?>">
				</p>
				 
				<?php if( $this->company_name_display == 'yes' ){ ?>
					
					<?php
					$company_name_required_text = ( $this->company_name_required == 'yes' ) ? '<span class="required">*</span>' : ''; 
					$company_name_required_class = ( $this->company_name_required == 'yes' ) ? 'validate-required' : '';
					?>
				
					<p class="form-row form-row-wide <?php echo $company_name_required_class; ?>">
						<label for="purchase-order-company-name"><?php _e( 'Invoice company name', 'pofwc' ); ?><?php echo $company_name_required_text; ?></label>
						<input type="text" id="purchase-order-company-name" name="purchase-order-company-name" class="input-text" placeholder="<?php _e( 'Company name', 'pofwc' ); ?>">
					</p>
				
				<?php } ?>
				
				<?php if( $this->address_1_display == 'yes' ){ ?>
					
					<?php
					$address_1_required_text = ( $this->address_1_required == 'yes' ) ? '<span class="required">*</span>' : ''; 
					$address_1_required_class = ( $this->address_1_required == 'yes' ) ? 'validate-required' : '';
					?>
				
					<p class="form-row form-row-wide <?php echo $address_1_required_class; ?>">
						<label for="purchase-order-address1"><?php _e( 'Address line 1', 'pofwc' ); ?><?php echo $address_1_required_text; ?></label>
						<input type="text" id="purchase-order-address1" name="purchase-order-address1" class="input-text" placeholder="<?php esc_attr_e( 'Invoice address line 1', 'pofwc' ); ?>">
					</p>
				
				<?php } ?>
				
				<?php if( $this->address_2_display == 'yes' ){ ?>
					
					<?php
					$address_2_required_text = ( $this->address_2_required == 'yes' ) ? '<span class="required">*</span>' : ''; 
					$address_2_required_class = ( $this->address_2_required == 'yes' ) ? 'validate-required' : '';
					?>
				
					<p class="form-row form-row-wide <?php echo $address_2_required_class; ?>">
						<label for="purchase-order-address2"><?php _e( 'Address line 2', 'pofwc' ); ?><?php echo $address_2_required_text; ?></label>
						<input type="text" id="purchase-order-address2" name="purchase-order-address2" class="input-text" placeholder="<?php esc_attr_e( 'Invoice address line 2', 'pofwc' ); ?>">
					</p>
				
				<?php } ?>
				
				<?php if( $this->address_3_display == 'yes' ){ ?>
					
					<?php
					$address_3_required_text = ( $this->address_3_required == 'yes' ) ? '<span class="required">*</span>' : ''; 
					$address_3_required_class = ( $this->address_3_required == 'yes' ) ? 'validate-required' : '';
					?>
				
					<p class="form-row form-row-wide <?php echo $address_3_required_class; ?>">
						<label for="purchase-order-address3"><?php _e( 'Address line 3', 'pofwc' ); ?><?php echo $address_3_required_text; ?></label>
						<input type="text" id="purchase-order-address3" name="purchase-order-address3" class="input-text" placeholder="<?php esc_attr_e( 'Invoice address line 3', 'pofwc' ); ?>">
					</p>
				
				<?php } ?>
				
				<?php if( $this->town_display == 'yes' ){ ?>
					
					<?php
					$town_required_text = ( $this->town_required == 'yes' ) ? '<span class="required">*</span>' : ''; 
					$town_required_class = ( $this->town_required == 'yes' ) ? 'validate-required' : '';
					?>
				
					<p class="form-row form-row-wide <?php echo $town_required_class; ?>">
						<label for="purchase-order-town"><?php _e( 'City', 'pofwc' ); ?><?php echo $town_required_text; ?></label>
						<input type="text" id="purchase-order-town" name="purchase-order-town" class="input-text" placeholder="<?php esc_attr_e( 'Invoice city', 'pofwc' ); ?>">
					</p>
				
				<?php } ?>
				
				<?php if( $this->county_display == 'yes' ){ ?>
					
					<?php
					$county_required_text = ( $this->county_required == 'yes' ) ? '<span class="required">*</span>' : ''; 
					$county_required_class = ( $this->county_required == 'yes' ) ? 'validate-required' : '';
					?>
				
					<p class="form-row form-row-wide <?php echo $county_required_class; ?>">
						<label for="purchase-order-county"><?php _e( 'State', 'pofwc' ); ?></label>
						<input type="text" id="purchase-order-county" name="purchase-order-county" class="input-text" placeholder="<?php esc_attr_e( 'Invoice state', 'pofwc' ); ?>">
					</p>
				
				<?php } ?>
				
				<?php if( $this->postcode_display == 'yes' ){ ?>
					
					<?php
					$postcode_required_text = ( $this->postcode_required == 'yes' ) ? '<span class="required">*</span>' : ''; 
					$postcode_required_class = ( $this->postcode_required == 'yes' ) ? 'validate-required' : '';
					?>
				
					<p class="form-row form-row-wide <?php echo $postcode_required_class; ?>">
						<label for="purchase-order-postcode"><?php _e( 'Zip', 'pofwc' ); ?><?php echo $postcode_required_text; ?></label>
						<input type="text" id="purchase-order-postcode" name="purchase-order-postcode" class="input-text" placeholder="<?php esc_attr_e( 'Invoice zip', 'pofwc' ); ?>">
					</p>
				
				<?php } ?>
				
				<?php if( $this->email_display == 'yes' ){ ?>
					
					<?php
					$email_required_text = ( $this->email_required == 'yes' ) ? '<span class="required">*</span>' : ''; 
					$email_required_class = ( $this->email_required == 'yes' ) ? 'validate-required' : '';
					?>
				
					<p class="form-row form-row-wide <?php echo $email_required_class; ?>">
						<label for="purchase-order-email">Email<?php echo $email_required_text; ?></label>
						<input type="email" id="purchase-order-email" name="purchase-order-email" class="input-text" placeholder="<?php esc_attr_e( 'Invoice email', 'pofwc' ); ?>">
					</p>
				
				<?php } ?>

				<?php do_action( 'pofwc_form_after_po_form' ); ?>
				
				<?php
			}


			

			/**
			 * Validates gateway checkout form fields
			 *
			 * @access public
			 * 
			 * @return mixed		Boolean or formatted HTML string
			 *
			 * @since 1.0.0
			 * @since 1.4.0			Added conditional for field settings
			 */
			
			public function validate_fields() {
				
				// Check if required fields are set, if not add an error
				if ( $this->po_number_required == 'yes' && ! $_POST['purchase-order-number'] ){
					
					wc_add_notice( __( 'Please enter a purchase order number.', 'pofwc' ), 'error' );
					
				}
				
				if ( $this->company_name_required == 'yes' && ! $_POST['purchase-order-company-name'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - company name is missing.', 'pofwc' ), 'error' );
					
				}
				
				if ( $this->address_1_required == 'yes' && ! $_POST['purchase-order-address1'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - address line 1 is missing.', 'pofwc' ), 'error' );
					
				}
				
				if ( $this->address_2_required == 'yes' && ! $_POST['purchase-order-address2'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - address line 2 is missing.', 'pofwc' ), 'error' );
					
				}
				
				if ( $this->address_3_required == 'yes' && ! $_POST['purchase-order-address3'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - address line 3 is missing.', 'pofwc' ), 'error' );
					
				}
				
				if ( $this->town_required == 'yes' && ! $_POST['purchase-order-town'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - city is missing.', 'pofwc' ), 'error' );
					
				}
				
				if ( $this->county_required == 'yes' && ! $_POST['purchase-order-county'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - state is missing.', 'pofwc' ), 'error' );
					
				}
				
				if ( $this->postcode_required == 'yes' && ! $_POST['purchase-order-postcode'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - zip is missing.', 'pofwc' ), 'error' );
					
				}
				
				if ( $this->email_required == 'yes' && ! $_POST['purchase-order-email'] ){
					
					wc_add_notice( __( 'Please complete the address to send the invoice to - email is missing.', 'pofwc' ), 'error' );
					
				}
				
				if ( $this->email_required == 'yes' && ! is_email( $_POST['purchase-order-email'] ) ){
					
					wc_add_notice( __( 'Please check the invoice email address supplied is a valid email address.', 'pofwc' ), 'error' );
					
				}
				
				else{
					return true;
				}
				
			}
			
			
			
			
			/**
			 * Bypasses the Stripe plugin's validation of fields
			 *
			 *  @access public
			 * 
			 * @param array $required_fields  		The required fields
			 *
			 * @since 1.2.0
			 * @since 1.4.0							Added purchase-order-email
			 */

			public function pofwc_stripe_validate_checkout_unset_gateways_required_fields( $required_fields ){
				
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
				if( isset( $required_fields['purchase-order-email'] ) ){
					unset( $required_fields['purchase-order-email'] );
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
			 * @since 1.4.0			Added _purchase_order_email
			 * @since 1.10.0		Removed legacy meta functions so WooCommerce now handles compatibility
			 * @since 1.10.0		Fixed missing translation strings
			 * @since 1.11.0		Fixed PHP warning "Notice: Function is_internal_meta_key was called incorrectly"
			 * @since 1.11.0		Added action hooks
			 */
			
			public function pofwc_display_purchase_order_meta(){
				
				$order = wc_get_order( get_the_ID() );
				
				if( $order->get_payment_method() == 'purchase_order_gateway' && $order->get_meta('_purchase_order_number', true ) ){
		
					echo '<h3>' . __( 'Purchase order information', 'pofwc' ) . '</h3>';

					echo '<p>';
						echo '<strong>' . __( 'Purchase order number:', 'pofwc' ) . '</strong> ' . $order->get_meta('_purchase_order_number', true ) . '<br>';

						echo '<strong>' . __( 'Invoice details:', 'pofwc' ) . '</strong> <br>';
						echo ( $order->get_meta('_purchase_order_company_name', true ) ) ? esc_html( $order->get_meta('_purchase_order_company_name', true ) ) . '<br>' : '';

						echo ( $order->get_meta('_purchase_order_address1', true ) ) ? esc_html( $order->get_meta('_purchase_order_address1', true ) ) . '<br>' : '';

						echo ( $order->get_meta('_purchase_order_address2', true ) ) ? esc_html( $order->get_meta('_purchase_order_address2', true ) ) . '<br>' : '';

						echo ( $order->get_meta('_purchase_order_address3', true ) ) ? esc_html( $order->get_meta('_purchase_order_address3', true ) ) . '<br>' : '';

						echo ( $order->get_meta('_purchase_order_town', true ) ) ? esc_html( $order->get_meta('_purchase_order_town', true ) ) . '<br>' : '';

						echo ( $order->get_meta('_purchase_order_county', true ) ) ? esc_html( $order->get_meta('_purchase_order_county', true ) ) . '<br>' : '';

						do_action( 'pofwc_admin_display_after_po_county', $order );	

						echo ( $order->get_meta('_purchase_order_postcode', true ) ) ? esc_html( $order->get_meta('_purchase_order_postcode', true ) ) . '<br>' : '';

						echo ( $order->get_meta('_purchase_order_email', true ) ) ? esc_html( $order->get_meta('_purchase_order_email', true ) ) . '<br>' : '';

						do_action( 'pofwc_admin_display_after_po_form', $order );
					echo '</p>';
				
				}
				
			}
			


			
			/**
			 *  Displays the purchase order number on the order-received page
			 *
			 *  @access public
			 *  
			 *  @return string		The formatted HTML
			 *  
			 *  @since 1.7.4
			 *  @since 1.7.9		Purchase order details now displayed on order-received page
			 *  @since 1.10.0		Removed legacy meta functions so WooCommerce now handles compatibility
			 *  @since 1.11.0		Added action hooks
			 */
			
			public function pofwc_add_po_number_to_order_received_page() {
				
				global $wp;
				$order_id = absint( $wp->query_vars['order-received'] );
				$order = wc_get_order( $order_id );
				
				$purchase_order_number = $order->get_meta('_purchase_order_number', true );
				
				if ( '' != $purchase_order_number ) {
					
					echo '<p><strong>' . __( 'Purchase Order number', 'pofwc' ) . ':</strong> ' . $purchase_order_number . '<br>';

					echo ( $order->get_meta('_purchase_order_company_name', true ) ) ? esc_html( $order->get_meta('_purchase_order_company_name', true ) ) . '<br>' : '';

					echo ( $order->get_meta('_purchase_order_address1', true ) ) ? esc_html( $order->get_meta('_purchase_order_address1', true ) ) . '<br>' : '';

					echo ( $order->get_meta('_purchase_order_address2', true ) ) ? esc_html( $order->get_meta('_purchase_order_address2', true ) ) . '<br>' : '';

					echo ( $order->get_meta('_purchase_order_address3', true ) ) ? esc_html( $order->get_meta('_purchase_order_address3', true ) ) . '<br>' : '';

					echo ( $order->get_meta('_purchase_order_town', true ) ) ? esc_html( $order->get_meta('_purchase_order_town', true ) ) . '<br>' : '';

					echo ( $order->get_meta('_purchase_order_county', true ) ) ? esc_html( $order->get_meta('_purchase_order_county', true ) ) . '<br>' : '';

					echo ( $order->get_meta('_purchase_order_postcode', true ) ) ? esc_html( $order->get_meta('_purchase_order_postcode', true ) ) . '<br>' : '';

					echo ( $order->get_meta('_purchase_order_email', true ) ) ? esc_html( $order->get_meta('_purchase_order_email', true ) ) . '<br>' : '';

					do_action( 'pofwc_thankyou_display_after_po_form', $order );
					
				}


				
			}			
			


			
			/**
			 *  Adds the purchase order number to the order emails
			 *
			 *  @access public
			 *  
			 *  @param array $fields 				The order meta fields
			 *  @param bool $sent_to_admin 			Send email to admin as well as customer?
			 *  @param object $order 				The order object
			 *  
			 *  @return array $fields				The updated order meta fields
			 *  
			 *  @since 1.7.4
			 *  @since 1.7.5						Removed erroneous colon
			 *  @since 1.7.9						Purchase order details now displayed on order emails
			 *  @since 1.8.4						Added option to not display on order emails
			 *  @since 1.11.0						Added action hooks
			 */
			
			public function pofwc_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
				
				if( $this->po_show_in_email == 'yes' ){

					$sent_to_admin = true;

					$purchase_order_number = array( 'label' => __( 'Purchase Order number', 'pofwc' ), 'value' => esc_html( $order->get_meta( '_purchase_order_number', true ) ) );

					$purchase_order_company_name = ( $order->get_meta( '_purchase_order_company_name', true ) ) ?  esc_html( $order->get_meta( '_purchase_order_company_name', true ) ) : '';

					$purchase_order_address1 = ( $order->get_meta( '_purchase_order_address1', true ) ) ? '<br>' . esc_html( $order->get_meta( '_purchase_order_address1', true ) ) : '';

					$purchase_order_address2 = ( $order->get_meta( '_purchase_order_address2', true ) ) ? '<br>' . esc_html( $order->get_meta( '_purchase_order_address2', true ) ) : '';

					$purchase_order_address3 = ( $order->get_meta( '_purchase_order_address3', true ) ) ? '<br>' . esc_html( $order->get_meta( '_purchase_order_address3', true ) ) : '';

					$purchase_order_town = ( $order->get_meta( '_purchase_order_town', true ) ) ? '<br>' . esc_html( $order->get_meta( '_purchase_order_town', true ) ) : '';

					$purchase_order_county = ( $order->get_meta( '_purchase_order_county', true ) ) ? '<br>' . esc_html( $order->get_meta( '_purchase_order_county', true ) ) : '';

					$purchase_order_postcode = ( $order->get_meta( '_purchase_order_postcode', true ) ) ? '<br>' . esc_html( $order->get_meta( '_purchase_order_postcode', true ) ) : '';
					
					$purchase_order_email = ( $order->get_meta( '_purchase_order_email', true ) ) ? '<br>' . esc_html( $order->get_meta( '_purchase_order_email', true ) ) : '';

					ob_start();
					do_action( 'pofwc_email_display_after_po_form', $order );
					$after_po_form = ob_get_contents();
					ob_end_clean();
					$after_po_form = $after_po_form ? '<br>' . $after_po_form : '';
					
					$purchase_order_details = ( $order->get_meta( '_purchase_order_address1', true ) ) ? array( 'label' => __( 'Invoice details', 'pofwc' ), 'value' => '<br>' . $purchase_order_company_name . $purchase_order_address1 .  $purchase_order_address2 . $purchase_order_address3 . $purchase_order_town . $purchase_order_county . $purchase_order_postcode . $purchase_order_email . $after_po_form ) : '';


					if ( '' != $purchase_order_number ) {
						
						$fields['purchase_order_number'] = $purchase_order_number;
						$fields['purchase_order_details'] = $purchase_order_details;
						
					}
					
					return $fields;

				}
				
			}

		}
			
	}
  
}