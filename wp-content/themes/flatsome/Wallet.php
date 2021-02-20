<?php

class Wallet {
    
    public $user_id = null;
    
    public function __constract($user_id = null) {
        
        $this->$user_id = $user_id;
        
    }
    
    // get user balance
    public static function get_balance($user_id) {
        
        global $wpdb;
        
        $balance = $wpdb->get_var("SELECT balance FROM {$wpdb->prefix}fswcwallet WHERE user_id={$user_id}");
        
        return FS_WC_Wallet::encrypt_decrypt('decrypt', $balance);
        
    }
    
    // get user balance
    public static function set_balance($user_id, $amount) {
        
        global $wpdb;
        
        if(!Wallet::wallet_exist($user_id)) {
            
            Wallet::create_wallet($user_id, 0, 0, 'unlocked');
            
        }
        
        $data = array(
            'balance' => FS_WC_Wallet::encrypt_decrypt('encrypt', $amount)
        );
        
        $where = array(
            'user_id' => $user_id
        );
        
        $wpdb->update("{$wpdb->prefix}fswcwallet", $data, $where);
                
    }
    
    // add to totla spent
    public static function add_spending($user_id, $amount) {
        
        global $wpdb;
        
        if(!Wallet::wallet_exist($user_id)) {
            
            Wallet::create_wallet($user_id, 0, 0, 'unlocked');
            
        }
        
        $total_spent   = FS_WC_Wallet::encrypt_decrypt('decrypt', $wpdb->get_var("SELECT total_spent FROM {$wpdb->prefix}fswcwallet WHERE user_id={$user_id}"));
        $total_spent   = $amount + $total_spent;
        
        $data = array(
            'total_spent' => FS_WC_Wallet::encrypt_decrypt('encrypt', $total_spent)
        );
        
        $where = array(
            'user_id' => $user_id
        );
        
        $wpdb->update("{$wpdb->prefix}fswcwallet", $data, $where);
        
    }
    
    // Add funds
    public static function add_funds($user_id, $amount, $order_id = 0) {
        
        global $wpdb;
        
        if(!Wallet::wallet_exist($user_id)) {
            
            Wallet::create_wallet($user_id, 0, 0, 'unlocked');
            
        }
        
        $current_balance    = Wallet::get_balance($user_id);
        $new_balance        = $current_balance + $amount;
        
        $last_deposit       = date('Y-m-d H:i:s');
        
        $data = array(
            'balance' => FS_WC_Wallet::encrypt_decrypt('encrypt', $new_balance),
            'last_deposit' => $last_deposit
        );
        
        $where = array(
            'user_id' => $user_id
        );
        
        $wpdb->update("{$wpdb->prefix}fswcwallet", $data, $where);
        
        Wallet::add_transaction($order_id, $user_id, 'credits', $amount);
        
    }
    
    // Withdraw funds
    public static function withdraw_funds($user_id, $amount, $order_id = 0) {
        
        global $wpdb;
        
        $current_balance    = Wallet::get_balance($user_id);
        
        if($current_balance >= $amount) {
         
            $new_balance    = $current_balance - $amount;
            
            $data = array(
                'balance' => FS_WC_Wallet::encrypt_decrypt('encrypt', $new_balance)
            );

            $where = array(
                'user_id' => $user_id
            );

            $wpdb->update("{$wpdb->prefix}fswcwallet", $data, $where);

        }
        
        Wallet::add_transaction($order_id, $user_id, 'debits', $amount);
        
    }
    
    
    // Lock account
    public static function lock_account($user_id, $lock_message) {
        
        global $wpdb;

        $data = array(
            'status' => 'locked',
            'lock_message' => $lock_message
        );

        $where = array(
            'user_id' => $user_id
        );

        $wpdb->update("{$wpdb->prefix}fswcwallet", $data, $where);

    }
    
    // Unlock account
    public static function unlock_account($user_id) {
        
        global $wpdb;

        $data = array(
            'status' => 'unlocked'
        );

        $where = array(
            'user_id' => $user_id
        );

        $wpdb->update("{$wpdb->prefix}fswcwallet", $data, $where);
        
    }
    
    // check lock status
    public static function lock_status($user_id) {
        
        global $wpdb;
        
        $query = $wpdb->get_results("SELECT status, lock_message FROM {$wpdb->prefix}fswcwallet WHERE user_id={$user_id}");
        
        if($query) {
	        return array(
		        'status'        => $query[0]->status,
		        'lock_message'  => $query[0]->lock_message,
	        );
        }
        return array(
            'status'        => '',
            'lock_message'  => ''
        );
    }
    
    // Refund
    public static function refund($user_id, $amount, $order_id = 0) {
        
        global $wpdb;
        
        $rate = (int)get_option('fsww_refund_rate', '100')/100;
        
        
        if(!Wallet::wallet_exist($user_id)) {
            
            Wallet::create_wallet($user_id, 0, 0, 'unlocked');
            
        }
        
        $amount             = $amount * $rate;
        
        $current_balance    = Wallet::get_balance($user_id);
        $new_balance        = $current_balance + $amount;
        
		
	
		
        $data = array(
            'balance' => FS_WC_Wallet::encrypt_decrypt('encrypt', $new_balance)
        );
        
        $where = array(
            'user_id' => $user_id
        );
        
        $wpdb->update("{$wpdb->prefix}fswcwallet", $data, $where);
        
        Wallet::add_transaction($order_id, $user_id, 'credits', $amount);
        
        if($order_id != 0) {
            
            $order          = wc_get_order($order_id);
            $order->update_status('refunded', __( 'Payment refunded using Wallet Credit', 'fsww'));
            
        }
        
    }
    
    public static function add_transaction($order_id, $user_id, $type, $amount) {
        
        global $wpdb;
        
        if(!Wallet::wallet_exist($user_id)) {
            
            Wallet::create_wallet($user_id, 0, 0, 'unlocked');
            
        }
        
        $data = array(
            'order_id'               => $order_id,
            'type'                   => $type,
            'user_id'                => $user_id,
            'amount'                 => $amount,
            'transaction_date'       => date('Y-m-d H:i:s')
        );
        
        $wpdb->insert("{$wpdb->prefix}fswcwallet_transaction", $data);
        
    }
    
    public static function create_wallet($user_id, $balance, $total_spent, $status) {
        
        global $wpdb;    
        
        $balance              = FS_WC_Wallet::encrypt_decrypt('encrypt', $balance);
        $total_spent          = FS_WC_Wallet::encrypt_decrypt('encrypt', $total_spent);
        
        $data = array(
            'user_id'       => $user_id,
            'balance'       => $balance,
            'last_deposit'  => date('Y-m-d H:i:s'),
            'total_spent'   => $total_spent,
            'status'        => $status
        );
        
        $wpdb->insert("{$wpdb->prefix}fswcwallet", $data);
        
        //die('<pre>' . print_r($data, true) . '</pre>');
        
    }
    
    public static function wallet_exist($user_id) {
        
        global $wpdb;
        
        return((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fswcwallet WHERE user_id={$user_id}")>0)?true:false;
   
    }
    
}
