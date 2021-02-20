<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

?>

<form action="<?php echo admin_url('admin-ajax.php') ?>" method="post">

    <h3><?php echo  __('Encryption Settings', 'fsww'); ?>:</h3>
    
    <p><?php _e('The encryption keys used to encrypt wallet balances to prevent tempering if the database is hacked', 'fsww') ?></p><br>

    <?php

    $upload_directory = wp_upload_dir();
    $target_file = $upload_directory['basedir'] . '/fsww_files/encryption_key.php';

    if(!@include($target_file)) {
        FS_WC_Wallet::set_encryption_key('5RdRDCmG89DooltnMlUG', '2Ve2W2g9ANKpvQNXuP3w');
        @include($target_file);
    }

    ?>
    
    <input type="hidden" name="action" value="fsww_save_encryption_setting">

    <div class="input-box">
        <div class="label">
            <span><?php echo __('Disable Encryption', 'fsww'); ?></span>
        </div>
        <div class="input">
            <input type="checkbox" name="fsww_disable_encryption" <?php echo esc_attr(get_option('fsww_disable_encryption', 'off'))=='on'?'checked':''?>>
        </div>
    </div>

    <div class="input-box">
        <div class="label">
            <span><?php echo  __('Data Encryption Key', 'fsww'); ?></span>
        </div>
        <div class="input">

            <input class="input-field" type="text" name="fsww_encryption_key" value="<?php echo ENCRYPTION_KEY; ?>">
            <div class="helper">?<div class="tip">
                    <?php echo __('The key used to encrypt/decrypt license keys in the database', 'fsww'); ?>
                </div></div>
        </div>

    </div>

    <div class="input-box">
        <div class="label">
            <span><?php echo  __('Data Encryption VI', 'fsww'); ?></span>
        </div>
        <div class="input">

            <input class="input-field" type="text" name="fsww_encryption_vi" value="<?php echo ENCRYPTION_VI; ?>">
            <div class="helper">?<div class="tip">
                    <?php echo __('The VI used to encrypt/decrypt license keys in the database', 'fsww'); ?>
                </div></div>
        </div>

    </div>

    <?php if (!extension_loaded('openssl')) { ?>
        <p class="no_openssl"><?php echo __('Open SSL is not installed on this server license keys will be stored without encryption ', 'fsww') ?></p>
    <?php } ?>

        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>

</form>