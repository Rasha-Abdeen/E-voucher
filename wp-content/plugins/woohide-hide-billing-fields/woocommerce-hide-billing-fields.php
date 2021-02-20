<?php
/**
 * Plugin Name: WooCommerce Hide Billing Fields
 * Plugin URI: https://zamartz.com/product/woocommerce-hide-billing-fields/
 * Description: Allows the ability to disable billing fields in WooCommerce checkout on Wordpess. Includes the options to hide fields only if order value is under $0.01. Standard fallbacks to show if shipping is required but also has the ability to override this functionality and always hide.
 * Version: 1.0.3
 * Author: ZAMARTZ
 * Author URI: https://zamartz.com/about
 * Developer: Zachary A. Martz
 * Developer URI: https://githib.com/zamartz
 * Text Domain: zamartz
 * Domain Path: /code
 * Copyright: © 2016 ZAMARTZ.
 **/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

// Add settings link on plugin page
function woohide_plugin_settings_link($links) { 
  $settings_link = '<a href="/wp-admin/admin.php?page=wc-settings&tab=billing">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$woohide_plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$woohide_plugin", 'woohide_plugin_settings_link' );

/**
 * Check if WooCommerce is active
**/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

//Add Settings
include( 'zero-billing-fields-admin-settings.php' );  

//Apply Settings   
include( 'zero-billing-fields-settings.php' );  

}//end if active plugin
?>