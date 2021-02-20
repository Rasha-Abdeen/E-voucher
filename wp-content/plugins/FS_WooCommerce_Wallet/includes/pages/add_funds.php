<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

require_once(dirname(__FILE__) . '/../functions.php');

?>

<div class="wrap fsww">
    <h1><?php _e('Add Funds', 'fsww') ?></h1><br>

    <div class="postbox">
        <div class="inside">

            <form action="<?php echo admin_url('admin-ajax.php') ?>" method="post">
               
                <input type="hidden" name="action" value="fsww_add_funds">
                
                <div class="input-box">
                    <div class="label">
                        <span><?php echo __('Username Or Email', 'fsww'); ?></span>
                    </div>
                    <div class="input">
                        <input type="text" class="input-field" name="user_id" id="user_id">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo __('Amount', 'fsww'); ?></span>
                    </div>
                    <div class="input">
                        <input class="input-field" name="balance" id="balance" type="text" placeholder="0.00">
                    </div>
                </div>
                
                <div class="input-box">
                    <div class="label">
                        <span><?php echo __('Status', 'fsww'); ?></span>
                    </div>
                    <div class="input">
                        <select class="input-field" name="status">
                            <option value="unlocked"><?php echo __('Unlocked', 'fsww'); ?></option>
                            <option value="locked"><?php echo __('Locked', 'fsww'); ?></option>
                        </select>
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




