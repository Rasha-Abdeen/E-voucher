<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


if(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['user_id'])) {
    
    require(dirname(__FILE__) . '/edit_wallet.php');
    
    die();
    
}

if(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['user_id'])) {
	
	global $wpdb;
	$wpdb->delete($wpdb->prefix . 'fswcwallet', array('user_id' =>(int)$_GET['user_id']));
    
}


require_once(dirname(__FILE__) . '/../functions.php');

global $wpdb;

$rows_number    = get_option('fsww_rows_per_page', '15');

$items_count    = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fswcwallet");

$total_pages    = ceil($items_count/$rows_number);

$current_page   = (isset($_GET['p']) && (intval($_GET['p']) > 0)) ? (int)$_GET['p'] : 1;
$previous_page  = ($current_page > 1) ? $current_page-1 : 1;
$next_page      = $current_page+1;

$sql_start_page = ($current_page-1) * $rows_number;

$sql_limit      = "LIMIT {$sql_start_page}, {$rows_number}";

?>   

<div class="wrap fsww">
    <h1><?php _e('Wallets', 'fsww') ?> <a href="<?php echo admin_url('admin.php?page=fsww-add-funds') ?>" class="page-title-action"><?php _e('Add Funds To a User', 'fsww'); ?></a></h1><br>

    <form method="post" action="<?php echo admin_url("admin.php?page=fsww-wallets"); ?>">
        <label class="screen-reader-text" for="post-search-input"><?php _e('Search wallets', 'fsww') ?>:</label>
        <input id="post-search-input" name="fsww-s" value="" autocomplete="on" type="search">
        <input id="search-submit" class="button" value="<?php _e('Search wallets', 'fsww') ?>" autocomplete="on" type="submit">
    </form>
    
    <div class="pages">
        <a href="<?php echo admin_url("admin.php?page=fsww-wallets&p=1"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">«</span></a>
        <a href="<?php echo admin_url("admin.php?page=fsww-wallets&p={$previous_page}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">‹</span></a>
        <span class="screen-reader-text"><?php _e('Current Page', 'fsww') ?></span><span id="table-paging" class="paging-input"><?php echo $current_page ?> of <span class="total-pages"><?php echo $total_pages ?></span></span>
        <a href="<?php echo admin_url("admin.php?page=fsww-wallets&p={$next_page}"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">›</span></a>
        <a href="<?php echo admin_url("admin.php?page=fsww-wallets&p={$total_pages}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">»</span></span></a>
    </div>
    
    <table class="wp-list-table widefat  striped">
        <thead>
            <tr>
                <th class="xs"><b><?php _e('ID', 'fsww') ?></b></th>
                <th class="l"><b><?php _e('Username', 'fsww') ?></b></th>
                <th class="s"><b><?php _e('Balance', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Last Deposit', 'fsww') ?></b></th>
                <th class="s"><b><?php _e('Total Spent', 'fsww') ?></b></th>
                <th class="a"><b><?php _e('Actions', 'fsww') ?></b></th>
            </tr>
        </thead>
        
        <tbody>
           
            <?php
    
            global $wpdb;

            if(isset($_POST['fsww-s']) && $_POST['fsww-s'] != "") {
	            $sql = "SELECT * FROM {$wpdb->prefix}fswcwallet w, {$wpdb->prefix}users u WHERE u.id = w.user_id AND (u.user_login LIKE '%{$_POST['fsww-s']}%' OR u.user_nicename LIKE '%{$_POST['fsww-s']}%' OR u.user_email LIKE '%{$_POST['fsww-s']}%')";
                $query = $wpdb->get_results($sql);
            } else {
	            $query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fswcwallet ORDER BY user_id ASC $sql_limit");
            }
            
            

        
            if($query) {
                
                foreach($query as $user) {

                    if($user->user_id != 0) {

                        $user_id        = $user->user_id;
                        $balance        = wc_price(FS_WC_Wallet::encrypt_decrypt('decrypt', $user->balance));
                        $last_deposit   = fswcw_format_date($user->last_deposit);
                        $total_spent    = wc_price(FS_WC_Wallet::encrypt_decrypt('decrypt', $user->total_spent));
                        $status         = $user->status;

                        $username       = get_user_by('id', $user_id);
                        if($username) {
                            $username          = $username->user_login . ' (' . $username->user_email . ')';
                        } else {
                            $username          = __("User Not Found. ID ", "fsww") . $user_id;
                        }


                        $actions        = '<button id="add-credit-btn" class="fsww-actions add-credit-btn button action" data-uid="' . $user_id . '">' . __('Add Credit', 'fsww') . '</button>';
                        $actions       .= '<button id="withdraw-credit-btn" class="fsww-actions withdraw-credit-btn button action" data-uid="' . $user_id . '">' . __('Withdraw Credit', 'fsww') . '</button>';

                        $classes        = "";

                        if($status == 'locked') {

                            $actions   .= '<button id="lock-credit-btn" class="fsww-actions lock-credit-btn button action" data-uid="' . $user_id . '" data-action="unlock"><span class="dashicons dashicons-lock"></span> ' . __('Unlock Credit', 'fsww') . '</button>';
                            $classes    = 'lock';

                        } elseif($status == 'unlocked') {

                            $actions       .= '<button id="lock-credit-btn" class="fsww-actions lock-credit-btn button action" data-uid="' . $user_id . '" data-action="lock"><span class="dashicons dashicons-lock"></span> ' . __('Lock Credit', 'fsww') . '</button>';

                        }

                    ?>

                    <tr class="<?php echo $classes ?>">
                       <td>
                            <?php echo $user_id ?>
                            <div class="row-actions fsactions">
                                <span class="inline">
                                    <a href="<?php echo admin_url("admin.php?page=fsww-wallets&task=edit&user_id={$user_id}") ?>" class="editinline fsww-edit" data-id="<?php echo $user_id ?>"><?php echo __('Edit', 'fsww'); ?></a>
                                </span>|
                                <span class="trash">
                                    <a href="<?php echo admin_url("admin.php?page=fsww-wallets&task=delete&user_id={$user_id}") ?>" class="editinline fsww-delete" data-id="<?php echo $user_id ?>"><?php echo __('Delete', 'fsww'); ?></a>
                                </span>
                            </div>
                        </td>
                        <td><?php echo $username ?></td>
                        <td><?php echo $balance ?></td>
                        <td><?php echo $last_deposit ?></td>
                        <td><?php echo $total_spent ?></td>
                        <td><?php echo $actions ?></td>
                    </tr>

                    <?php }
                    }
            }else { ?>
                
                <tr>
                    <td class="center" colspan="6"><?php _e('There is no user wallets, the user need to make a deposit or an admiin add funds the user to appear here', 'fsww') ?></td>
                </tr>            
                
            <?php } ?>
        </tbody>
        
        <tfoot>
            <tr>
                <th><b><?php _e('ID', 'fsww') ?></b></th>
                <th><b><?php _e('Username', 'fsww') ?></b></th>
                <th><b><?php _e('Balance', 'fsww') ?></b></th>
                <th><b><?php _e('Last Deposit', 'fsww') ?></b></th>
                <th><b><?php _e('Total Spent', 'fsww') ?></b></th>
                <th><b><?php _e('Actions', 'fsww') ?></b></th>
            </tr>
        </tfoot>
    </table>
    
    
    <div class="fsww-actions-window" id="add-funds">
        
        <div class="container">
            <div class="inside">
            
                <h1><?php _e('Add Credit', 'fsww') ?></h1>
                
                <p><?php _e('The amount will be added to the current balance.', 'fsww') ?></p><br>
        
                <form action="<?php echo admin_url('admin-ajax.php') ?>" method="post">


                    <input type="hidden" name="action" value="fsww_i_add_funds">
                    
                    <input type="hidden" class="user_id" name="user_id">

                    <div class="input-box">
                        <div class="label">
                            <span><?php echo __('Amount', 'fsww'); ?></span>
                        </div>
                        <div class="input">
                            <input class="input-field" name="amount" id="amount" type="text" placeholder="0.00">
                        </div>
                    </div>
                    
                    
                    <div class="input-box">
                        <div class="label">
                            <span><?php echo  __('Notify the user', 'fsww'); ?></span>
                        </div>
                        <div class="input">
                            <input type="checkbox" name="notify">
                        </div>
                    </div>
                    
                    <div class="input-box">
                        <div class="label">
                            <span><?php echo __('Notification Message', 'fsww'); ?></span>
                        </div>
                        <div class="input">
                            <textarea class="textarea-field" name="message" id="message"></textarea>
                        </div>
                    </div>

                    <p class="submit">
                        <input id="save-add-fund" class="button button-primary" value="<?php echo __('Add Funds', 'fsww'); ?>" type="submit">
                        <input id="cancel-add-fund" class="button button-secondary" value="<?php echo __('Cancel', 'fsww'); ?>" type="button">
                        <br class="clear">
                    </p>

                </form>
            </div>            
        </div>
    </div>
        
        
    <div class="fsww-actions-window" id="withdraw-funds">

        <div class="container">
            <div class="inside">

                <h1><?php _e('Withdraw Credit', 'fsww') ?></h1>
                
                <p><?php _e('The amount will be substracted from the current balance, If the amount is larger than the current balance the operation will fail.', 'fsww') ?></p><br>

                <form action="<?php echo admin_url('admin-ajax.php') ?>" method="post">

                    <input type="hidden" name="action" value="fsww_i_withdraw">
                    
                    <input type="hidden" class="user_id" name="user_id">

                    <div class="input-box">
                        <div class="label">
                            <span><?php echo __('Amount', 'fsww'); ?></span>
                        </div>
                        <div class="input">
                            <input class="input-field" name="amount" id="amount" type="text" placeholder="0.00">
                        </div>
                    </div>


                    <div class="input-box">
                        <div class="label">
                            <span><?php echo  __('Notify the user', 'fsww'); ?></span>
                        </div>
                        <div class="input">
                            <input type="checkbox" name="notify">
                        </div>
                    </div>

                    <div class="input-box">
                        <div class="label">
                            <span><?php echo __('Notification Message', 'fsww'); ?></span>
                        </div>
                        <div class="input">
                            <textarea class="textarea-field" name="message" id="message"></textarea>
                        </div>
                    </div>

                    <p class="submit">
                        <input id="save-withdraw-fund" class="button button-primary" value="<?php echo __('Withraw Funds', 'fsww'); ?>" type="submit">
                        <input id="cancel-withdraw-fund" class="button button-secondary" value="<?php echo __('Cancel', 'fsww'); ?>" type="button">
                        <br class="clear">
                    </p>

                </form>
            </div>            
        </div>
        
    </div>
        
        
    <div class="fsww-actions-window" id="lock-credit">

        <div class="container">
            <div class="inside">

                <h1><?php _e('Lock Credit', 'fsww') ?></h1>
                
                <p><?php _e('Locking the credit will make the user unable to use his wallet credit to checkout, the lock reason will be dispalyed instead.', 'fsww') ?></p><br>

                <form action="<?php echo admin_url('admin-ajax.php') ?>" method="post">

                    <input type="hidden" name="action" value="fsww_i_lock">
                    <input type="hidden" class="fsww_action" name="fsww_action">
                    
                    <input type="hidden" class="user_id" name="user_id">
                    
                    <div class="input-box">
                        <div class="label">
                            <span><?php echo __('Lock Reason', 'fsww'); ?></span>
                        </div>
                        <div class="input">
                            <textarea class="textarea-field" name="message" id="message"></textarea>
                        </div>
                    </div>

                    <div class="input-box">
                        <div class="label">
                            <span><?php echo  __('Notify the user', 'fsww'); ?></span>
                        </div>
                        <div class="input">
                            <input type="checkbox" name="notify">
                        </div>
                    </div>

                    <p class="submit">
                        <input id="save-lock-credit" class="button button-primary" value="<?php echo __('Lock/Unlock Funds', 'fsww'); ?>" type="submit">
                        <input id="cancel-lock-credit" class="button button-secondary" value="<?php echo __('Cancel', 'fsww'); ?>" type="button">
                        <br class="clear">
                    </p>

                </form>
            </div>            
        </div>
        
    </div>
        
</div>
