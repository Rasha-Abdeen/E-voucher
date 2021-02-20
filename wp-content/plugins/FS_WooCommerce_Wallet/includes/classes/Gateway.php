<?php

class WC_Gateway_FSWW extends WC_Payment_Gateway {

    public function __construct() {
        
        $this->id                       = 'fsww';
        $this->icon                     = apply_filters('woocommerce_cod_icon', '');
        $this->method_title             = __('WooCommerce Wallet', 'fsww');
        $this->methode_description      = __('Pay with your account balance.', 'fsww');
        $this->has_fields               = false;
        
        $this->init_form_fields();
        $this->init_settings();
        
        $this->title                    = $this->get_option('title');
        $this->description              = $this->format_description();
        $this->instructions              = $this->get_option('instructions');
        
        // registe actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
        add_action('woocommerce_before_checkout_process' . $this->id, array($this, 'check_balance'));
        
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
        
    }
    
    public function format_description() {
        
        global $woocommerce;
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        
        $balance = wc_price(Wallet::get_balance(get_current_user_id()));

        $description  = '<p>' . __('Available Balance:', 'fsww') . ' ' .$balance . '</p>';
	
        
        $description .= $this->get_option('description');
        
        return $description;
        
    }
    
    public function init_form_fields() {
        $shipping_methods = array();
        
        if(is_admin()) {
            foreach(WC()->shipping->load_shipping_methods() as $method) {
                $shipping_methods[$method->id] = $method->get_title();
            }
        }
        
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable WooCommerce Wallet', 'woocommerce'),
                'lable'       => __('Enable WooCommerce Wallet', 'woocommerce'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'title' => array(
				'title'       => __('Title', 'woocommerce'),
				'type'        => 'text',
				'description' => __('Payment method description that the customer will see on your checkout.', 'woocommerce'),
				'default'     => __('WooCommerce Wallet', 'woocommerce'),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __('Description', 'woocommerce'),
				'type'        => 'textarea',
				'description' => __('Payment method description that the customer will see on your website.', 'woocommerce'),
				'default'     => __('Pay using your Wallet.', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'instructions' => array(
				'title'       => __('Instructions', 'woocommerce'),
				'type'        => 'textarea',
				'description' => __('Instructions that will be added to the thank you page.', 'woocommerce'),
				'default'     => __('Pay using your Wallet', 'woocommerce'),
				'desc_tip'    => true,
			)
        );        
        
    }
    
    public function process_payment($order_id) {
        
        global $woocommerce;
        
        foreach($woocommerce->cart->get_cart() as $cart_item_key => $cart_item){
            
            if(has_term('fsww-wallet-credit', 'product_cat', $cart_item['product_id'])){
                
                wc_add_notice(__('<strong>Payment error:</strong> You can not purchase wallet credit money with wallet credit. Please choose another payment method.', 'fsww') , 'error' );
             
                return false;
                
            }
            
        }
        
        if(!$this->check_balance()) {
            
            wc_add_notice( __('<strong>Payment error:</strong> Insufficient funds. Please purchase more credits or use a different payment method.', 'fsww'), 'error' );
            
            return false;
            
        }
        
        $order         = wc_get_order($order_id);
        $user_id       = $order->get_user_id();

        $total         = $woocommerce->cart->total;

        if ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {

            $order_id = absint(get_query_var('order-pay'));
            $order = wc_get_order($order_id);

            $total = $order->get_total();

        }
        
        
        $balance       = floatval(Wallet::get_balance(get_current_user_id()));
        $balance_new   = $balance - floatval($total);
        
        Wallet::withdraw_funds($user_id, $total, $order_id);
        Wallet::add_spending($user_id, floatval($total));
        
        
        $balance       = floatval(Wallet::get_balance(get_current_user_id()));
        
      
        
        $order->update_status(get_option('fsww_order_status', 'completed'), __('Order status set to ' . get_option('fsww_order_status', 'completed') . ' by WooCommerce Wallet', 'fsww'));
        
        
        $order->reduce_order_stock();
        
        WC()->cart->empty_cart();
		
        
        return array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url($order)
		);
        
    }
    
    public function check_balance() {
        
        global $woocommerce;
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $balance  = floatval(Wallet::get_balance(get_current_user_id()));
        $total    = floatval($woocommerce->cart->total);

        if ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {

            $order_id = absint(get_query_var('order-pay'));
            $order = wc_get_order($order_id);

            $total = $order->get_total();

        }
        
        if($balance >= $total) {
                
            return 1;
            
        }
        
        return 0;
        
    }
    
    public function is_available() {
		
        $order = null;
        
        
        if(!is_user_logged_in()) {
            
            return false;
        
        }
        
        if($this->locked_account()) {
          
            return false;
            
        }
        
	
		if( WC()->cart != null) {
			
			$fees     = WC()->cart->get_fees();
        
			foreach($fees as $fee) {
				
				$id = strtolower(str_replace(' ', '-', __('Paid From Wallet', 'fsww')));
				
				if($fee->tax_class == 'fsww_pfw' || isset($fees[$id])) {

					return false;

				}

			}  

		}


		if ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {

            $order_id = absint(get_query_var('order-pay'));
            $order = wc_get_order($order_id);

            if (count($order->get_items()) > 0) {

                foreach ($order->get_items() as $item) {

                    $product_id = $item['product_id'];
                    $term_list = get_the_terms($product_id, 'product_cat');

                    foreach ($term_list as $term) {

                        if ($term->slug == 'fsww-wallet-credit') {

                            return false;

                        }

                    }
                }
            }

        }


        global $woocommerce;
        
        if($woocommerce->cart != null) {
	
	        foreach($woocommerce->cart->get_cart() as $cart_item_key => $cart_item){
		
		        if(has_term('fsww-wallet-credit', 'product_cat', $cart_item['product_id'])){
			
			        return false;
			
		        }
		
	        }
        
        }

		
		if(!$this->check_balance()) {
			return false;
		}
        
        
        return parent::is_available();
        
    }
    
    public function locked_account() {
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $status   = Wallet::lock_status(get_current_user_id());
        
        if($status['status'] == 'locked') {
                
            return true;
            
        }
        
        return false;
        
    }
    
    public function get_icon() {
        
        $icon = '';

		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	
    }
    
    public function thankyou_page() {
        
        if(isset($this->instructions)) {
            wpautop(wptexturize($this->instructions));
        }
        
    }
    
    public function email_instructions($order, $send_to_admin, $plain_text = false) {
        
        if($this->instructions && $send_to_admin && 'fsww' == $order->payment_method) {
            echo wpautop(wptexturize($this->instructions)) . PHP_EOL;
        }
        
    }
    
}