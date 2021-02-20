<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

require_once(dirname(__FILE__) . '/../functions.php');


global $wpdb;
 
$query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fswcwallet WHERE user_id='" .  (int) $_GET['user_id'] . "'");

$user = $query[0];

$uid        	= $user->user_id;
$balance        = FS_WC_Wallet::encrypt_decrypt('decrypt', $user->balance);
$last_deposit   = $user->last_deposit;
$total_spent    = FS_WC_Wallet::encrypt_decrypt('decrypt', $user->total_spent);
$status         = $user->status;
$lock_message   = $user->lock_message;

$time                   = strtotime($last_deposit);
$last_deposit_month     = date("m", $time);
$last_deposit_year      = date("Y", $time);
$last_deposit_day       = date("d", $time);

$unlocked       = '';
$locked         = '';

if($status == 'unlocked') {
    $unlocked = 'selected';
}

if($status == 'locked') {
    $locked = 'selected';
}

?>


<div class="wrap fsww">
    <h1><?php _e('Edit Wallet', 'fsww') ?></h1><br>

    <div class="postbox">
        <div class="inside">

            <form action="<?php echo admin_url('admin-ajax.php') ?>" method="post">
               
                <input type="hidden" name="action" value="fsww_edit_wallet">
                
                <div class="input-box">
                    <div class="label">
                        <span><?php echo __('User', 'fsww'); ?></span>
                    </div>
                    <div class="input">
                        <select class="input-field" name="user_id" id="user_id" disabled>
                            <?php

                            $user = get_user_by('ID', $uid);
                     
                            $user_id = $user->ID;
                            $username = $user->user_login . ' (' . $user->user_email . ')';
                            
                            echo('<option value="' . $user_id . '" selected>' . $username . '</option>');
                        
                            
                            ?>
                        </select>
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo __('Balance', 'fsww'); ?></span>
                    </div>
                    <div class="input">
                        <input class="input-field" name="balance" id="balance" type="text" value="<?php echo $balance ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo __('Total Spent', 'fsww'); ?></span>
                    </div>
                    <div class="input">
                        <input class="input-field" name="total_spent" id="total_spent" type="text" value="<?php echo $total_spent ?>">
                    </div>
                </div>
                
                <div class="input-box">
                    <div class="label">
                        <span><?php echo __('Last Deposit Date', 'fsww'); ?></span>
                    </div>
                    <div class="input">
                        <div class="timestamp-wrap">
                            <select class="date" id="last_deposit_month" name="last_deposit_month">
                                <option value=""></option>
                            <?php
                                foreach($months as $month){
                                    echo '<option value="' . $month['number'] . '" data-text="' . $month['text'] . '" ' . ($last_deposit_month==$month['number'] ? 'selected' : '') . '>' . $month['number'] . '-' . $month['text'] . '</option>';
                                }
                            ?>
                            </select>

                            <input class="date" id="last_deposit_day" name="last_deposit_day" maxlength="2" type="number" placeholder="Day" min="1" max="31" value="<?php echo $last_deposit_day ?>">

                            <input class="date" id="last_deposit_year" name="last_deposit_year" size="4" maxlength="4" type="text" placeholder="Year" value="<?php echo $last_deposit_year; ?>">
                        </div>
                    </div>

                </div>
                
                <div class="input-box">
                    <div class="label">
                        <span><?php echo __('Status', 'fsww'); ?></span>
                    </div>
                    <div class="input">
                        <select class="input-field" name="status">
                            <option value="unlocked" <?php echo $unlocked ?>><?php echo __('Unlocked', 'fsww'); ?></option>
                            <option value="locked" <?php echo $locked ?>><?php echo __('Locked', 'fsww'); ?></option>
                        </select>
                    </div>

                </div>
                
                <div class="input-box">
                    <div class="label">
                        <span><?php echo __('Lock Message', 'fsww'); ?></span>
                    </div>
                    <div class="input">
                        <textarea class="input-field" name="lock_message" id="lock_message" type="text"><?php echo $lock_message ?></textarea>
                    </div>
                </div>

                <p class="submit">
                    <input name="save" id="save-add-fund" class="button button-primary" value="<?php echo __('Add Funds', 'fsww'); ?>" type="submit">
                    <br class="clear">
                </p>
            
                
            </form>
        
        </di>
            
    </div>
    
</div>




