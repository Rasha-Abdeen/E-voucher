<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


if(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['transaction_id'])) {

	global $wpdb;
	$wpdb->delete($wpdb->prefix . 'fswcwallet_transaction', array('transaction_id' =>(int)$_GET['transaction_id']));

}

$_user_id = '';
        
if(isset($_GET['uid'])) $_user_id = $_GET['uid'];

?>   

<div class="wrap fsww">
    <h1><?php _e('Transactions', 'fsww') ?></h1><br>
    
    <div class="input-box">
        <div class="input">
            <select class="input-field" name="user_id" id="select_user_id">
                <option value="0"><?php _e('Filter by user') ?></option>

                <?php

                $users_list = get_users();

                foreach($users_list as $user) {

                    $user_id  = $user->ID;
                    $username = $user->user_login . ' (' . $user->user_email . ')';
                    $selected = ($_user_id == $user_id) ? ' selected' : '';

                    echo('<option value="' . $user_id . '"' . $selected . '>' . $username . '</option>');

                }

                ?>
            </select>
        </div>
    </div>
    
    <div id="user_transaction_table_container">
       
        <?php

        require_once(dirname(__FILE__) . '/../transactions_table_html.php');
        
        transactions_table_html($_user_id);

        ?>

    </div>
    
</div>
