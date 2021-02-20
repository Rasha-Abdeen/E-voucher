<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

?>

<form action="<?php echo admin_url('options.php') ?>" method="post">

    <?php

    settings_fields('fsww_general_options_group');
    do_settings_sections('fsww_general_options_group');

    ?>

    <div class="input-box">
        <div class="label">
            <span><?php echo __('Enable refund requests', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input type="checkbox" name="fsww_refunds" <?php echo esc_attr(get_option('fsww_refunds', 'on'))=='on'?'checked':''?>>
        </div>
    </div>

    <div class="input-box">
        <div class="label">
            <span><?php echo __('Enable withdrawal requests', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input type="checkbox" name="fsww_withdrawals" <?php echo esc_attr(get_option('fsww_withdrawals', 'off'))=='on'?'checked':''?>>
        </div>

        <br><br>

        <div class="input-box">
            <div class="label">
                <span>&emsp;&mdash; <?php echo __('PayPal', 'fsww'); ?></span>
            </div>
            <div class="input">
                <input type="checkbox" name="fsww_withdrawals_paypal" <?php echo esc_attr(get_option('fsww_withdrawals_paypal', 'on'))=='on'?'checked':''?>>
            </div>
        </div><br>

        <div class="input-box">
            <div class="label">
                <span>&emsp;&mdash; <?php echo __('SWIFT bank transfer', 'fsww'); ?></span>
            </div>
            <div class="input">
                <input type="checkbox" name="fsww_withdrawals_swift" <?php echo esc_attr(get_option('fsww_withdrawals_swift', 'off'))=='on'?'checked':''?>>
            </div>
        </div><br>

        <div class="input-box">
            <div class="label">
                <span>&emsp;&mdash; <?php echo __('Bitcoin', 'fsww'); ?></span>
            </div>
            <div class="input">
                <input type="checkbox" name="fsww_withdrawals_bitcoin" <?php echo esc_attr(get_option('fsww_withdrawals_bitcoin', 'off'))=='on'?'checked':''?>>
            </div>
        </div><br>

        <div class="input-box">
            <div class="label">
                <span>&emsp;&mdash; <?php echo __('Bank Transfer (Brazil)', 'fsww'); ?></span>
            </div>
            <div class="input">
                <input type="checkbox" name="fsww_withdrawals_bank_transfer" <?php echo esc_attr(get_option('fsww_withdrawals_bank_transfer', 'off'))=='on'?'checked':''?>>
            </div>
        </div>

    </div>





    <div class="input-box">
        <div class="label">
            <span><?php echo __('Enable user to user transfers', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input type="checkbox" name="fsww_transfers" <?php echo esc_attr(get_option('fsww_transfers', 'off'))=='on'?'checked':''?>>
        </div>
    </div>


    <div class="input-box">
        <div class="label">
            <span><?php echo __('Show make a deposit page in my account page', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input type="checkbox" name="fsww_deposit" <?php echo esc_attr(get_option('fsww_deposit', 'on'))=='on'?'checked':''?>>
        </div>
    </div>

    <div class="input-box">
        <div class="label">
            <span><?php echo __('Show transactions in my account page', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input type="checkbox" name="fsww_transactions" <?php echo esc_attr(get_option('fsww_transactions', 'on'))=='on'?'checked':''?>>
        </div>
    </div>

    <div class="input-box">
        <div class="label">
            <span><?php echo __('Refund Rate (%)', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input class="input-field" name="fsww_refund_rate" id="fsww_refund_rate" type="number" placeholder="100" min="0" max="100" value="<?php echo esc_attr(get_option('fsww_refund_rate', '100')); ?>">
        </div>
    </div>

    <div class="input-box">
        <div class="label">
            <span><?php echo __('Partial Payments', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input type="checkbox" name="fsww_partial_payments" <?php echo esc_attr(get_option('fsww_partial_payments', 'on'))=='on'?'checked':''?>>
            <div class="helper">?<div class="tip">
                <?php echo __('If account balance is not enough discount the available funds from the accounts balance and pay the rest using a diffret payment gateway.', 'fsww'); ?>
            </div></div>
        </div>
    </div>  
    
    <div class="input-box">
        <div class="label">
            <span><?php echo __('Show Wallet Balance in The Primary Menu', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input type="checkbox" name="fsww_show_balance_in_menu" <?php echo esc_attr(get_option('fsww_show_balance_in_menu', 'on'))=='on'?'checked':''?>>
        </div>
    </div>   
    
    <div class="input-box">
        <div class="label">
            <span><?php echo __('Number of Rows Per Page for Tables', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input class="input-field" name="fsww_rows_per_page" id="fsww_rows_per_page" type="number" placeholder="100" min="10" value="<?php echo esc_attr(get_option('fsww_rows_per_page', '10')); ?>">
        </div>
    </div>
       
    <div class="input-box">
        <div class="label">
            <span><?php echo __('Order Status After Purchase', 'fsww'); ?></span>
        </div>
        
        <div class="input">
			<select class="input-field" name="fsww_order_status">       
        
        <?php
		
		$order_status = get_option('fsww_order_status', 'completed');
		
		require_once(dirname(FSWW_FILE) . '/includes/classes/FS_WC_Wallet.php');
		
		$order_statuses = (array) FS_WC_Wallet::get_terms('shop_order_status', array('hide_empty' => 0, 'orderby' => 'id'));

		if($order_statuses && !is_wp_error($order_statuses)) {
			foreach($order_statuses as $s) {

				if(version_compare(WOOCOMMERCE_VERSION, '2.2', '>=' )) {

					$s->slug = str_replace('wc-', '', $s->slug);     

				} 
				
				$selected = ($order_status==$s->slug)?'selected':'';
				
				?>
			
				<option value="<?php echo($s->slug) ?>" <?php echo($selected) ?>><?php echo($s->name) ?></option>

		<?php
					
			}
				
		}
		
		?>
        
        
    		</select>
		</div>
	</div>

    <?php submit_button(); ?>

</form>

