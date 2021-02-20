<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

require_once(dirname(__FILE__) . '/../functions.php');

global $wpdb;

$rows_number    = get_option('fsww_rows_per_page', '15');

$items_count    = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fswcwallet_requests");

$total_pages    = ceil($items_count/$rows_number);

$current_page   = (isset($_GET['p']) && (intval($_GET['p']) > 0)) ? (int)$_GET['p'] : 1;
$previous_page  = ($current_page > 1) ? $current_page-1 : 1;
$next_page      = $current_page+1;
 
$sql_start_page = ($current_page-1) * $rows_number;

$sql_limit      = "LIMIT {$sql_start_page}, {$rows_number}";

?>   

<div class="wrap fsww">
    <h1><?php _e('Refund Requests', 'fsww') ?></h1><br>
    
    <div class="pages">
        <a href="<?php echo admin_url("admin.php?page=fsww-rr&p=1"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">«</span></a>
        <a href="<?php echo admin_url("admin.php?page=fsww-rr&p={$previous_page}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">‹</span></a>
        <span class="screen-reader-text"><?php _e('Current Page', 'fsww') ?></span><span id="table-paging" class="paging-input"><?php echo $current_page ?> of <span class="total-pages"><?php echo $total_pages ?></span></span>
        <a href="<?php echo admin_url("admin.php?page=fsww-rr&p={$next_page}"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">›</span></a>
        <a href="<?php echo admin_url("admin.php?page=fsww-rr&p={$total_pages}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">»</span></span></a>
    </div>
    
    
    <table class="wp-list-table widefat  striped">
        <thead>
            <tr>
                <th class="xs"><b><?php _e('Request ID', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Order ID', 'fsww') ?></b></th>
                <th class="l"><b><?php _e('User', 'fsww') ?></b></th>
                <th class="s"><b><?php _e('Amount', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Request Date', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('Order Date', 'fsww') ?></b></th>
                <th><b><?php _e('Action', 'fsww') ?></b></th>
            </tr>
        </thead>
        
        <tbody>
           
            <?php
    
            global $wpdb;

            $query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fswcwallet_requests ORDER BY request_date DESC {$sql_limit}");

        
            if($query) {
                
                foreach($query as $request) {

                    $request_id        = $request->request_id;
                    $order_id          = $request->order_id;
                    $request_date      = fswcw_format_date($request->request_date);
                    $status            = $request->status;
                    
                    $order             = wc_get_order($order_id);
                    
                    if($order) {
                        $username          = get_user_by('id', $order->get_user_id());
						if($username) {	
                        	$username          = $username->user_login . ' (' . $username->user_email . ')';
						} else {
							$username          = __("User Not Found. ID ", "fsww") . $order->get_user_id();
						}
						
						$amount            = $order->get_total();
						$fees         = $order->get_fees();
						$order_date        = fswcw_format_date($order->get_date_paid());
                    }else{
                        $username          = __("Order Not Found. ID", "fsww") . $order_id;
                        $amount            = __("Order Not Found. ID", "fsww") . $order_id;
                        $fees              = array();
						$order_date        = "";
                    }
                    
                    
                    
                    
                    
                    //Parial payment
                    $total_fees   = 0; 

                    $transaction_found = false;

                    foreach($fees as $fee) {

                        if($fee['tax_class'] == 'fsww_pfw'  || $fee['name'] == __('Paid From Wallet', 'fsww')) {

                            $total_fees += abs(floatval($fee['line_total']));
                            $transaction_found = true;

                        }

                    }        

                    if($transaction_found) {

                        $amount         = $amount + $total_fees;

                    }
                    
                    $amount = wc_price($amount);

                    //End Parial payment
                    
                    
                    
                    $order_url          = '<a target="_blank" href="' . admin_url("post.php?post={$request->order_id}&action=edit") . "\">#{$request->order_id} - " . __('View Order', 'fsww') . '</a>';
                    
                    if($status == 'requested') {
                    
                        $status  = '<form class="fsww-actions-from" action="' . admin_url('admin-ajax.php') . '" method="post">
                                        <input type="hidden" name="action" value="fsww_refund">
                                        <input type="hidden" name="fsww_refund_action" value="fsww_refund">
                                        <input type="hidden" name="request_id" value="' . $request_id . '">
                                        <input type="submit" class="fsww-actions refund-btn button action" value="' . __('Refund', 'fsww') . '">
                                    </form>';
                        
                        $status  .= '<form class="fsww-actions-from" action="' . admin_url('admin-ajax.php') . '" method="post">
                                        <input type="hidden" name="action" value="fsww_reject">
                                        <input type="hidden" name="request_id" value="' . $request_id . '">
                                        <input type="submit" class="fsww-actions refund-btn button action" value="' . __('Reject Request', 'fsww') . '">
                                    </form>';
                        
                    } elseif($status == 'refunded') {
                        
                        $status = '<button class="fsww-actions button action" disabled>' . __('Already Refunded', 'fsww') . '</button>';
                        
                    } elseif($status == 'rejected') {
                        
                        $status  = '<form class="fsww-actions-from" action="' . admin_url('admin-ajax.php') . '" method="post">
                                        <input type="hidden" name="action" value="fsww_refund">
                                        <input type="hidden" name="request_id" value="' . $request_id . '">
                                        <input type="submit" class="fsww-actions refund-btn button action" value="' . __('Refund', 'fsww') . '">
                                    </form>';
                        
                        $status .= '<button class="fsww-actions button action" disabled>' . __('Request Rejected', 'fsww') . '</button>';
                        
                    }
                    

                ?>

                <tr>
                    <td><?php echo $request_id ?></td>
                    <td><?php echo $order_url ?></td>
                    <td><?php echo $username ?></td>
                    <td><?php echo $amount ?></td>
                    <td><?php echo $request_date ?></td>
                    <td><?php echo $order_date ?></td>
                    <td><?php echo $status ?></td>
                </tr>

                <?php }
            }else { ?>
                
                <tr>
                    <td class="center" colspan="7"><?php _e('There is no refund requests', 'fsww') ?></td>
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
                <th><b><?php _e('Order Date', 'fsww') ?></b></th>
                <th><b><?php _e('Action', 'fsww') ?></b></th>
            </tr>
        </tfoot>
    </table>
</div>
