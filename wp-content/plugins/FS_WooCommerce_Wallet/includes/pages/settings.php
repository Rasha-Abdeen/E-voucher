<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : '';

?>

<div class="wrap fsww">
    <h1><?php _e('Settings', 'fsww') ?></h1><br>

    <h2 class="nav-tab-wrapper">
    <a href="<?php echo admin_url('admin.php?page=fsww-settings&tab=' . 'general') ?>" class="nav-tab <?php echo ($current_tab=='general' || $current_tab=='')?'nav-tab-active':'' ?>"><?php echo  __('General', 'fsww'); ?></a> 
    <a href="<?php echo admin_url('admin.php?page=fsww-settings&tab=' . 'encryption') ?>" class="nav-tab <?php echo ($current_tab=='encryption')?'nav-tab-active':'' ?>"><?php echo  __('Encryption', 'fsww'); ?></a>
    </h2>
    
    
    <div class="postbox">
        <div class="inside">

            <?php 
            
            if(($current_tab == 'general') || ($current_tab == '')) {

                require_once(dirname(FSWW_FILE) . '/includes/settings_pages/general.php'); 
    
            } elseif(($current_tab == 'encryption')) {
                
                require_once(dirname(FSWW_FILE) . '/includes/settings_pages/encryption.php'); 
                
            } ?>
            
        </div>
    </div>
    
</div>