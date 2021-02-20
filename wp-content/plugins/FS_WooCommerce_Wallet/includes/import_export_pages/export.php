<?php

if(! defined('ABSPATH')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

?>


<form action="<?php echo admin_url('admin-ajax.php') ?>" method="post">
	
	<h3><?php echo  __('Export Wallets', 'fsww'); ?>:</h3>
	
	<p><?php _e('Export the wallet users and balances, the file can be modified and imported back again, the new values will override the old ones.', 'fsww') ?></p>
	<input type="hidden" name="action" value="fsww_export">
	
	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Export"></p>

</form>