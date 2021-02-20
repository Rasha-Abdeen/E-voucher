<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

require_once(dirname(__FILE__) . '/../functions.php');

global $wpdb;

$rows_number    = get_option('fsww_rows_per_page', '15');

$items_count    = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fswcwallet_withdrawal_requests");

$total_pages    = ceil($items_count/$rows_number);

$current_page   = (isset($_GET['p']) && (intval($_GET['p']) > 0)) ? (int)$_GET['p'] : 1;
$previous_page  = ($current_page > 1) ? $current_page-1 : 1;
$next_page      = $current_page+1;
 
$sql_start_page = ($current_page-1) * $rows_number;

$sql_limit      = "LIMIT {$sql_start_page}, {$rows_number}";

?>   

<div class="wrap fsww">
    <h1><?php _e('Withdrawal Requests', 'fsww') ?></h1><br>
    
    <div class="pages">
        <a href="<?php echo admin_url("admin.php?page=fsww-wr&p=1"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">«</span></a>
        <a href="<?php echo admin_url("admin.php?page=fsww-wr&p={$previous_page}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">‹</span></a>
        <span class="screen-reader-text"><?php _e('Current Page', 'fsww') ?></span><span id="table-paging" class="paging-input"><?php echo $current_page ?> of <span class="total-pages"><?php echo $total_pages ?></span></span>
        <a href="<?php echo admin_url("admin.php?page=fsww-wr&p={$next_page}"); ?>"><span class="pagination-links"><span class="tablenav-pages-navspan button" aria-hidden="true">›</span></a>
        <a href="<?php echo admin_url("admin.php?page=fsww-wr&p={$total_pages}"); ?>"><span class="tablenav-pages-navspan button" aria-hidden="true">»</span></span></a>
    </div>
    
    
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th class="xs"><b><?php _e('Request ID', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('User ID', 'fsww') ?></b></th>
                <th class="s"><b><?php _e('Amount', 'fsww') ?></b></th>
			    <th class="s"><b><?php _e('Method', 'fsww') ?></b></th>
                <th class="l"><b><?php _e('Address', 'fsww') ?></b></th>
                <th><b><?php _e('Action', 'fsww') ?></b></th>
            </tr>
        </thead>
        
        <tbody>
           
            <?php
    
            global $wpdb;

            $query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fswcwallet_withdrawal_requests ORDER BY request_id ASC {$sql_limit}");

        
            if($query) {
                
                foreach($query as $request) {

                    $request_id        = $request->request_id;
                    $user_id          = $request->user_id;
					
					$username          = get_user_by('id', $user_id);
                    $username          = $username->user_login . ' (' . $username->user_email . ')';
					
                	$amount            = wc_price($request->amount);
                	$status            = $request->status;
					
					$method	       	   = $request->payment_method;
					$address	       = $request->address;
					
					if($method == "SWIFT") {
					
						$json_value	   = json_decode($request->address);
						
						$address	   = "<strong>" . __('Full Name', 'fsww') . "</strong>: " . $json_value->fn . "<br>";
						$address	  .= "<strong>" . __('Billing Address Line 1', 'fsww') . "</strong>: " . $json_value->bal1 . "<br>";
						$address	  .= "<strong>" . __('Billing Address Line 2', 'fsww') . "</strong>: " . $json_value->bal2 . "<br>";
						$address	  .= "<strong>" . __('Billing Address Line 3', 'fsww') . "</strong>: " . $json_value->bal3 . "<br>";
						$address	  .= "<strong>" . __('City', 'fsww') . "</strong>: " . $json_value->city . "<br>";
						$address	  .= "<strong>" . __('State', 'fsww') . "</strong>: " . $json_value->state . "<br>";
						$address	  .= "<strong>" . __('Postcode', 'fsww') . "</strong>: " . $json_value->pc . "<br>";
						$address	  .= "<strong>" . __('Country', 'fsww') . "</strong>: " . $json_value->country . "<br>";
						$address	  .= "<strong>" . __('Bank Account Holder\'s Name', 'fsww') . "</strong>: " . $json_value->bahn . "<br>";
						$address	  .= "<strong>" . __('Bank Account Number/IBAN', 'fsww') . "</strong>: " . $json_value->iban . "<br>";
						$address	  .= "<strong>" . __('SWIFT Code', 'fsww') . "</strong>: " . $json_value->swift . "<br>";
						$address	  .= "<strong>" . __('Bank Name in Full', 'fsww') . "</strong>: " . $json_value->bfn . "<br>";
						$address	  .= "<strong>" . __('Bank Branch City', 'fsww') . "</strong>: " . $json_value->bbcity . "<br>";
						$address	  .= "<strong>" . __('Bank Branch Country', 'fsww') . "</strong>: " . $json_value->bbcountry . "<br>";
						$address	  .= "<strong>" . __('Intermediary Bank - Bank Code', 'fsww') . "</strong>: " . $json_value->ibbc . "<br>";
						$address	  .= "<strong>" . __('Intermediary Bank - Name', 'fsww') . "</strong>: " . $json_value->ibn . "<br>";
						$address	  .= "<strong>" . __('Intermediary Bank - City', 'fsww') . "</strong>: " . $json_value->ibcity . "<br>";
						$address	  .= "<strong>" . __('Intermediary Bank - Country', 'fsww') . "</strong>: " . $json_value->ibcountry . "<br>";
						
					}

                    if($status == 'under_review') {
                    
                        $status  = '<form class="fsww-actions-from" action="' . admin_url('admin-ajax.php') . '" method="post">
                                        <input type="hidden" name="action" value="fsww_withdraw">
                                        <input type="hidden" name="fsww_refund_action" value="fsww_withdraw">
                                        <input type="hidden" name="request_id" value="' . $request_id . '">
										<input type="hidden" name="user_id" value="' . $user_id . '">
                                        <input type="hidden" name="amount" value="' . $request->amount . '">
                                        <input type="submit" class="fsww-actions refund-btn button action" value="' . __('Approve', 'fsww') . '">
                                    </form>';
                        
                        $status  .= '<form class="fsww-actions-from" action="' . admin_url('admin-ajax.php') . '" method="post">
                                        <input type="hidden" name="action" value="fsww_reject_withdraw">
                                        <input type="hidden" name="request_id" value="' . $request_id . '">
                                        <input type="submit" class="fsww-actions refund-btn button action" value="' . __('Reject Request', 'fsww') . '">
                                    </form>';
                        
                    } elseif($status == 'accepted') {
                        
                        $status = '<button class="fsww-actions button action" disabled>' . __('Already Approved', 'fsww') . '</button>';
                        
                    } elseif($status == 'rejected') {
                        
                        $status  = '<form class="fsww-actions-from" action="' . admin_url('admin-ajax.php') . '" method="post">
                                        <input type="hidden" name="action" value="fsww_withdraw">
                                        <input type="hidden" name="request_id" value="' . $request_id . '">
                                        <input type="hidden" name="user_id" value="' . $user_id . '">
                                        <input type="hidden" name="amount" value="' . $request->amount . '">
                                        <input type="submit" class="fsww-actions refund-btn button action" value="' . __('Approve', 'fsww') . '">
                                    </form>';
                        
                        $status .= '<button class="fsww-actions button action" disabled>' . __('Request Rejected', 'fsww') . '</button>';
                        
                    }
                    

                ?>

                <tr>
                    <td><?php echo $request_id ?></td>
                    <td><?php echo $username ?></td>
                    <td><?php echo $amount ?></td>
					<td><?php echo $method ?></td>
					<td><?php echo $address ?></td>
                    <td><?php echo $status ?></td>
                </tr>

                <?php }
            }else { ?>
                
                <tr>
                    <td class="center" colspan="6"><?php _e('There is no withrawal requests', 'fsww') ?></td>
                </tr>            
                
            <?php } ?>
        </tbody>
        
        <tfoot>
            <tr>
                <th class="xs"><b><?php _e('Request ID', 'fsww') ?></b></th>
                <th class="m"><b><?php _e('User ID', 'fsww') ?></b></th>
                <th class="s"><b><?php _e('Amount', 'fsww') ?></b></th>
                <th class="s"><b><?php _e('Method', 'fsww') ?></b></th>
                <th class="s"><b><?php _e('Address', 'fsww') ?></b></th>
                <th><b><?php _e('Action', 'fsww') ?></b></th>
            </tr>
        </tfoot>
    </table>
</div>
