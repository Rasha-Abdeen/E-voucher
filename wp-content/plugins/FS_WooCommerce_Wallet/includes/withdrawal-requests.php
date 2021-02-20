<?php

	if(! defined('ABSPATH')) {
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit();
	}

	global $wpdb;


	$user_id  = get_current_user_id(); 
	$query    = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fswcwallet_withdrawal_requests WHERE user_id='$user_id' ORDER BY request_id DESC");
	echo '$query' ;

?>
	

<style>
	
	#paypal-option,
	#swift-option,
	#bitcoin-option,
    #bank-option {
		display: none;
	}

	.center {
		text-align: center;
	}
	
</style>

<div class="fsww-meke-deposit-sc">
	<h4><?php _e('Withdrawal Requests', 'fsww') ?></h4>

	<form id="send_money" method="post">

		<input type="hidden" value="fsww" name="fsww-request-withrawal">

		<p class="form-row form-row-wide">

			<label for="amount" class=""><?php _e('Amount', 'fsww') ?></label>
			<input type="text" class="input-text" name="amount" id="amount" placeholder="0.00" value="">

		</p>
			
		<p class="form-row form-row-wide">

			<label for="method" class=""><?php _e('Select Payment Method', 'fsww') ?></label>
			<select name="method" class="input-select" id="fsww-payment-method">
				<option value="0"><?php _e("Select Payment Method", "fsww") ?></option>
                <?php if(get_option('fsww_withdrawals_paypal', 'on') == 'on') { ?>
				    <option value="paypal"><?php _e("PayPal", "fsww") ?></option>
                <?php
                }

                if(get_option('fsww_withdrawals_bitcoin', 'off') == 'on') { ?>
				    <option value="bitcoin"><?php _e("Bitcoin", "fsww") ?></option>

                <?php }

                if(get_option('fsww_withdrawals_swift', 'off') == 'on') { ?>
				    <option value="swift"><?php _e("SWIFT", "fsww") ?></option>
                <?php }

                if(get_option('fsww_withdrawals_bank_transfer', 'off') == 'on') { ?>
                    <option value="bank"><?php _e("Bank Transfer", "fsww") ?></option>
                <?php } ?>
			</select>
			
		</p>
		
		<div id="paypal-option">
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('PayPal Address', 'fsww') ?></label>
				<input type="text" class="input-text" name="paypal-address" id="address" value="">

			</p>
			
		</div>
		
		
		<div id="bitcoin-option">
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Bitcoin Address', 'fsww') ?></label>
				<input type="text" class="input-text" name="bitcoin-address" id="address" value="">

			</p>
			
		</div>
		
		<div id="swift-option">
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Full Name *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[fn]" value="">

			</p>
				
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Billing Address Line 1 *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[bal1]"  value="">

			</p>
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Billing Address Line 2', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[bal2]"  value="">

			</p>
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Billing Address Line 3', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[bal3]"  value="">

			</p>
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('City *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[city]"  value="">

			</p>
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('State', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[state]"  value="">

			</p>
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Postcode *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[pc]"  value="">

			</p>
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Country *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[country]"  value="">

			</p>
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Bank Account Holder\'s Name *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[bahn]"  value="">

			</p>
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Bank Account Number/IBAN *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[iban]"  value="">

			</p>
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('SWIFT Code *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[swift]"  value="">

			</p>
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Bank Name in Full *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[bfn]"  value="">

			</p>
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Bank Branch City *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[bbcity]"  value="">

			</p>
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Bank Branch Country *', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[bbcountry]"  value="">

			</p>
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Intermediary Bank - Bank Code', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[ibbc]"  value="">

			</p>
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Intermediary Bank - Name', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[ibn]"  value="">

			</p>
			
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Intermediary Bank - City', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[ibcity]"  value="">

			</p>
			
			
			<p class="form-row form-row-wide">

				<label for="address" class=""><?php _e('Intermediary Bank - Country', 'fsww') ?></label>
				<input type="text" class="input-text" name="swift[ibcountry]"  value="">

			</p>
			
		</div>


        <div id="bank-option">

            <p class="form-row form-row-wide">

                <label for="address" class=""><?php _e('Owner Name *', 'fsww') ?></label>
                <input type="text" class="input-text" name="bank[fn]" value="">

            </p>

            <p class="form-row form-row-wide">

                <label for="address" class=""><?php _e('Bank Name *', 'fsww') ?></label>
                <input type="text" class="input-text" name="bank[bn]" value="">

            </p>

            <p class="form-row form-row-wide">

                <label for="address" class=""><?php _e('Bank Agency Number *', 'fsww') ?></label>
                <input type="number" class="input-text" name="bank[ban]" value="">

            </p>

            <p class="form-row form-row-wide">

                <label for="address" class=""><?php _e('Bank Account Number *', 'fsww') ?></label>
                <input type="number" class="input-text" name="bank[bacn]" value="">

            </p>


            <p class="form-row form-row-wide">

                <label for="address" class=""><?php _e('CPF *', 'fsww') ?></label>
                <input type="number" class="input-text" name="bank[cpf]" value="">

            </p>


            <p class="form-row form-row-wide">

                <label for="address" class=""><?php _e('Account Type *', 'fsww') ?></label>
                <select name="bank[type]" class="input-text">
                    <option label="<?php _e('Checking Account', 'fsww') ?>"><?php _e('Checking Account *', 'fsww') ?></option>
                    <option label="<?php _e('Savings  Account', 'fsww') ?>"><?php _e('Savings  Account *', 'fsww') ?></option>
                </select>

            </p>



        </div>
		
		
		
		
		
		<input type="submit" value="<?php _e('Send Request', 'fsww') ?>">

	</form>
</div>

<br>

<table class="fsww-request-table">
	
	<thead>
		
		<tr>
			
			<th><?php _e("ID", "fsww") ?></th>
			<th><?php _e("Amount", "fsww") ?></th>
			<th><?php _e("Method", "fsww") ?></th>
			<th><?php _e("Address", "fsww") ?></th>
			<th><?php _e("Status", "fsww") ?></th>
			
		</tr>
		
	</thead>
	
	<tbody>
	
		<?php
		
			if($query) {

                foreach($query as $request) {
					
					$request_id = $request->request_id;
					$amount		= wc_price($request->amount);
					$status     = $request->status;

					$method		= $request->payment_method;
					$address	= $request->address;
					
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
						
					} else if($method == "Bank Transfer") {

                        $json_value	   = json_decode($request->address);

                        $address	   = "<strong>" . __('Owner Name', 'fsww') . "</strong>: " . $json_value->fn . "<br>";
                        $address	  .= "<strong>" . __('Bank Name', 'fsww') . "</strong>: " . $json_value->bn . "<br>";
                        $address	  .= "<strong>" . __('Bank Agency Number', 'fsww') . "</strong>: " . $json_value->ban . "<br>";
                        $address	  .= "<strong>" . __('Bank Account Number', 'fsww') . "</strong>: " . $json_value->bacn . "<br>";
                        $address	  .= "<strong>" . __('CPF', 'fsww') . "</strong>: " . $json_value->cpf . "<br>";
                        $address	  .= "<strong>" . __('Account Type', 'fsww') . "</strong>: " . $json_value->type . "<br>";

                    }
					
                    if($status == 'under_review') {
                    
                        $status  = __("Under Review", "fsww");
                        
                    } elseif($status == 'accepted') {
                        
                        $status  = __("Request Accepted", "fsww");
                        
                    } elseif($status == 'rejected') {
                        
                        $status  = __("Request Rejected", "fsww");
                        
                    }
                    
					
				 ?>
				
				<tr>
			
					<td><?php echo $request_id ?></td>
					<td><?php echo $amount ?></td>
					<td><?php echo $method ?></td>
					<td><?php echo $address ?></td>
					<td><?php echo $status ?></td>

				</tr>
				
				<?php
					 
				}
				
			}else {
		
		?>
		
		<tr>
			
			<td class="center" colspan="5"><?php _e("There is no requests", "fsww") ?></td>
			
		</tr>
		
		<?php } ?>
		
	</tbody>
	
</table>


<script type="text/javascript">

	(function(){
	
		"use strict";

		jQuery(function($) {

			jQuery("#fsww-payment-method").on("change", function() {
				
				fsww_hide_all_payment_options();
				
				var payment_option = jQuery("#fsww-payment-method").val();
				
				jQuery("#" + payment_option + "-option").show();
				
			});

		});
		
		function fsww_hide_all_payment_options() {
			
			jQuery("#paypal-option").hide();
			jQuery("#bitcoin-option").hide();
			jQuery("#swift-option").hide();
			jQuery("#bank-option").hide();

		}

	})();
	
</script>