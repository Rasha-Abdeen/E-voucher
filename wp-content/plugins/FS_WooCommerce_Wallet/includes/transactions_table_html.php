<?php

function transactions_table_html($user_id = '', $is_user = false, $page = 'fsww-t') { 

	$args             = "";
	$formated_user_id = "";
	
    if($user_id != '' && $user_id != 0) {

        $args               = "WHERE user_id={$user_id}";
        $formated_user_id   = "&uid={$user_id}";

    }

    require_once(dirname(__FILE__) . '/functions.php');
    
    global $wpdb;

    $rows_number    = get_option('fsww_rows_per_page', '15');

    $items_count    = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fswcwallet_transaction {$args}");

    $total_pages    = ceil($items_count/$rows_number);

    $current_page   = (isset($_GET['p']) && (intval($_GET['p']) > 0)) ? (int)$_GET['p'] : 1;
    $previous_page  = ($current_page > 1) ? $current_page-1 : 1;
    $next_page      = $current_page+1;

    $sql_start_page = ($current_page-1) * $rows_number;

    $sql_limit      = "LIMIT {$sql_start_page}, {$rows_number}";
    
    if($is_user) {
        
        $sql_limit = '';
        
    }
    
    if(!$is_user) { ?>
    <div class="pages">
        <a href="<?php echo admin_url("admin.php?page={$page}{$formated_user_id}&p=1"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">«</span></a>
        <a href="<?php echo admin_url("admin.php?page={$page}{$formated_user_id}&p={$previous_page}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">‹</span></a>
        <span class="screen-reader-text"><?php _e('Current Page', 'fsww') ?></span><span id="table-paging" class="paging-input"><?php echo $current_page ?> of <span class="total-pages"><?php echo $total_pages ?></span></span>
        <a href="<?php echo admin_url("admin.php?page={$page}{$formated_user_id}&p={$next_page}"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">›</span></a>
        <a href="<?php echo admin_url("admin.php?page={$page}{$formated_user_id}&p={$total_pages}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">»</span></span></a>
    </div>
    <?php } ?>
        
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th class="xs"><b><?php _e('ID', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Order ID', 'fsww') ?></b></th>
                <?php if(!$is_user) { ?>
                <th class="l"><b><?php _e('Username/Email', 'fsww') ?></b></th>
                <?php } ?>
                <th class="s"><b><?php _e('Credits', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Debits', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Date', 'fsww') ?></b></th>
            </tr>
        </thead>

        <tbody>

            <?php

            global $wpdb;

            $query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fswcwallet_transaction {$args} ORDER BY transaction_id DESC {$sql_limit}");


            if($query) {

                foreach($query as $transaction) {

                    $transaction_id    = $transaction->transaction_id;
                    $order_id          = $transaction->order_id;
                    $type              = $transaction->type;
                    $date              = fswcw_format_date($transaction->transaction_date);
                    $amount            = wc_price($transaction->amount);



                    $username          = get_user_by('id', $transaction->user_id);
                    if($username) {	
						$username          = $username->user_login . '<br>' . $username->user_email;
					} else {
						$username          = __("User Not Found. ID ", "fsww") . $transaction->user_id;
					}

                    $order_url         = '-';
                    $credits           = '-';
                    $debits            = '-';

                    if($order_id != 0 && $is_user == false) {

                        $order         = wc_get_order($order_id);
                        $order_url     = '<a target="_blank" href="' . admin_url("post.php?post={$transaction->order_id}&action=edit") . "\">#{$transaction->order_id} - " . __('View Order', 'fsww') . '</a>';


                    }elseif($order_id !=0 && $is_user == true) {
                        
                        $order         = wc_get_order($order_id);
                        $order_url     = '<a target="_blank" href="' . $order->get_view_order_url() . "\">#{$transaction->order_id} - " . __('View Order', 'fsww') . '</a>';
                        
                    }


                    if($type == 'credits') {

                        $credits        = $amount;

                    }

                    if($type == 'debits') {

                        $debits         = $amount;

                    }





                ?>

                <tr>
                    <td>
                        <?php echo $transaction_id ?>
                        <div class="row-actions fsactions">
                            <span class="trash">
                                <a href="<?php echo admin_url("admin.php?page=fsww-t&task=delete&transaction_id={$transaction_id}") ?>" class="editinline fsww-delete" data-id="<?php echo $user_id ?>"><?php echo __('Delete', 'fsww'); ?></a>
                            </span>
                        </div>
                    </td>
                    <td><?php echo $order_url ?></td>
                    <?php if(!$is_user) { ?>
                    <td><?php echo $username ?></td>
                    <?php } ?>
                    <td><?php echo $credits ?></td>
                    <td><?php echo $debits ?></td>
                    <td><?php echo $date ?></td>
                </tr>

                <?php }
            }else { ?>

                <tr>
                    <td class="center" colspan="6"><?php _e('There is no transactions', 'fsww') ?></td>
                </tr>            

            <?php } ?>
        </tbody>

        <tfoot>
            <tr>
                <th class="xs"><b><?php _e('ID', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Order ID', 'fsww') ?></b></th>
                <?php if(!$is_user) { ?>
                <th class="l"><b><?php _e('User', 'fsww') ?></b></th>
                <?php } ?>
                <th class="s"><b><?php _e('Credits', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Debits', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Date', 'fsww') ?></b></th>
            </tr>
        </tfoot>
    </table>
    
    <?php
}


function transactions_table_string($user_id = '', $is_user = false, $page = 'fsww-t') { 

    if($user_id != '' && $user_id != 0) {

        $args               = "WHERE user_id={$user_id}";
        $formated_user_id   = "&uid={$user_id}";

    }

    require_once(dirname(__FILE__) . '/functions.php');
    
    global $wpdb;

    $rows_number    = get_option('fsww_rows_per_page', '15');

    $items_count    = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fswcwallet_transaction {$args}");

    $total_pages    = ceil($items_count/$rows_number);

    $current_page   = (isset($_GET['p']) && (intval($_GET['p']) > 0)) ? (int)$_GET['p'] : 1;
    $previous_page  = ($current_page > 1) ? $current_page-1 : 1;
    $next_page      = $current_page+1;

    $sql_start_page = ($current_page-1) * $rows_number;

    $sql_limit      = "LIMIT {$sql_start_page}, {$rows_number}";

	ob_start();

    if($is_user) {
        
        $sql_limit = '';
        
    }

    if(!$is_user) { ?>
    <div class="pages">
        <a href="<?php echo admin_url("admin.php?page={$page}{$formated_user_id}&p=1"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">«</span></a>
        <a href="<?php echo admin_url("admin.php?page={$page}{$formated_user_id}&p={$previous_page}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">‹</span></a>
        <span class="screen-reader-text"><?php _e('Current Page', 'fsww') ?></span><span id="table-paging" class="paging-input"><?php echo $current_page ?> of <span class="total-pages"><?php echo $total_pages ?></span></span>
        <a href="<?php echo admin_url("admin.php?page={$page}{$formated_user_id}&p={$next_page}"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">›</span></a>
        <a href="<?php echo admin_url("admin.php?page={$page}{$formated_user_id}&p={$total_pages}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">»</span></span></a>
    </div>
    <?php } ?>

    <table class="wp-list-table widefat  striped">
        <thead>
            <tr>
                <th class="xs"><b><?php _e('ID', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Order ID', 'fsww') ?></b></th>
                <?php if(!$is_user) { ?>
                <th class="l"><b><?php _e('User', 'fsww') ?></b></th>
                <?php } ?>
                <th class="s"><b><?php _e('Credits', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Debits', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Date', 'fsww') ?></b></th>
            </tr>
        </thead>

        <tbody>

            <?php

            global $wpdb;

            $query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fswcwallet_transaction {$args} ORDER BY transaction_id DESC {$sql_limit}");


            if($query) {

                foreach($query as $transaction) {

                    $transaction_id    = $transaction->transaction_id;
                    $order_id          = $transaction->order_id;
                    $type              = $transaction->type;
                    $date              = fswcw_format_date($transaction->transaction_date);
                    $amount            = wc_price($transaction->amount);



                    $username          = get_user_by('id', $transaction->user_id);
                    $username          = $username->user_login . ' (' . $username->user_email . ')';

                    $order_url         = '-';
                    $credits           = '-';
                    $debits            = '-';

                    if($order_id != 0 && $is_user == false) {

                        $order         = wc_get_order($order_id);
                        $order_url     = '<a target="_blank" href="' . admin_url("post.php?post={$transaction->order_id}&action=edit") . "\">#{$transaction->order_id} - " . __('View Order', 'fsww') . '</a>';


                    }elseif($order_id !=0 && $is_user == true) {
                        
                        $order         = wc_get_order($order_id);
                        $order_url     = '<a target="_blank" href="' . $order->get_view_order_url() . "\">#{$transaction->order_id} - " . __('View Order', 'fsww') . '</a>';
                        
                    }


                    if($type == 'credits') {

                        $credits        = $amount;

                    }

                    if($type == 'debits') {

                        $debits         = $amount;

                    }





                ?>

                <tr>
                    <td>
                        <?php echo $transaction_id ?>
                    </td>
                    <td><?php echo $order_url ?></td>
                    <?php if(!$is_user) { ?>
                    <td><?php echo $username ?></td>
                    <?php } ?>
                    <td><?php echo $credits ?></td>
                    <td><?php echo $debits ?></td>
                    <td><?php echo $date ?></td>
                </tr>

                <?php }
            }else { ?>

                <tr>
                    <td class="center" colspan="6"><?php _e('There is no transactions', 'fsww') ?></td>
                </tr>

            <?php } ?>
        </tbody>

        <tfoot>
            <tr>
                <th class="xs"><b><?php _e('ID', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Order ID', 'fsww') ?></b></th>
                <?php if(!$is_user) { ?>
                <th class="l"><b><?php _e('User', 'fsww') ?></b></th>
                <?php } ?>
                <th class="s"><b><?php _e('Credits', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Debits', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Date', 'fsww') ?></b></th>
            </tr>
        </tfoot>
    </table>

    <?php
	return(ob_get_clean());
}