<?php
/**
 * Plugin Name: WooCommerce Serial Numbers - WordPress Plugin |  VestaThemes.com
 * Plugin URI: http://www.codecanyon.net/user/WPShowCase?ref=WPShowCase
 * Description: Easily manage serial numbers with WooCommerce. You can download updates of WooCommerce Serial Numbers easily using this plugin: <a href="http://envato.github.io/wp-envato-market/dist/envato-market.zip">envato-market.zip</a>
 * Author: WPShowCase
 * Version: 1.25
 * WC tested up to: 3.3.2
 * Author URI: http://www.codecanyon.net/user/WPShowCase?ref=WPShowCase
 */
if ( !is_admin() && !isset( $_SESSION ) ) {
    @session_start();
}

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

require_once dirname( __FILE__ ) . '/wpshowcase/wpshowcase-functions.php';
require_once dirname( __FILE__ ) . '/includes/serial-numbers-page.php';
require_once dirname( __FILE__ ) . '/includes/report-customer-serial-numbers.php';
require_once dirname( __FILE__ ) . '/includes/report-product-serial-number-sales.php';
require_once dirname( __FILE__ ) . '/includes/report-serial-numbers.php';
require_once dirname( __FILE__ ) . '/includes/edit-order.php';
require_once dirname( __FILE__ ) . '/includes/edit-product.php';
require_once dirname( __FILE__ ) . '/includes/cart.php';
require_once dirname( __FILE__ ) . '/includes/checkout.php';
require_once dirname( __FILE__ ) . '/includes/product-frontend.php';
require_once dirname( __FILE__ ) . '/includes/import-or-generate.php';

/**
 * Serial numbers class
 */
class Serial_Numbers {

    /**
     * Filters for reports and languages
     */
    function __construct() {
        $has_shown_update_message = get_option( 'has_displayed_woocommerce_serial_numbers_update_message', false );
        if ( empty( $has_shown_update_message ) ) {
            add_action( 'admin_notices', array( $this, 'plugin_activated_notice' ) );
            update_option( 'has_displayed_woocommerce_serial_numbers_update_message', true );
        }
        load_plugin_textdomain( 'serial-numbers', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        add_filter( 'wc_admin_reports_path', array( $this, 'wc_admin_reports_path' ), 10, 3 );
        add_filter( 'woocommerce_admin_reports', array( $this, 'woocommerce_admin_reports' ) );
        global $serial_numbers_page;
//        add_action( 'wp_ajax_save_serial_numbers', array( $serial_numbers_page, 'wp_ajax_save_serial_numbers' ) );
    }

    function plugin_activated_notice() {
?>
        <div class="notice notice-info is-dismissible">
            <p><?php
                print sprintf( __( 'Thank you for activating (or updating) WooCommerce Serial Numbers. Did you know that you can download updates easily by installing and activating %senvato-market.zip%s?', 'serial-numbers' ), '<a href="http://envato.github.io/wp-envato-market/dist/envato-market.zip">', '</a>' );
                ?></p>
        </div>
        <?php
    }

    /**
     * Adds the reports to WooCommerce
     */
    function wc_admin_reports_path( $file, $name, $class ) {
        if ( $name == 'customer-serial-numbers' ) {
            return WP_PLUGIN_DIR . '/serial-numbers/includes/report-customer-serial-numbers.php';
        }
        if ( $name == 'product-serial-numbers' ) {
            return WP_PLUGIN_DIR . '/serial-numbers/includes/report-product-serial-number-sales.php';
        }
        if ( $name == 'serial-numbers' ) {
            return WP_PLUGIN_DIR . '/serial-numbers/includes/report-serial-numbers.php';
        }
        return $file;
    }

    /**
     * Adds the report tabs to WooCommerce
     */
    function woocommerce_admin_reports( $reports ) {
        $reports[ 'customers' ][ 'reports' ][ 'customer-serial-numbers' ] = array(
            'title' => __( 'Customer Serial Numbers', 'serial-numbers' ),
            'description' => '',
            'hide_title' => true,
            'callback' => array( 'WC_Admin_Reports', 'get_report' )
        );
        $reports[ 'orders' ][ 'reports' ][ 'product-serial-numbers' ] = array(
            'title' => __( 'Product Serial Numbers', 'serial-numbers' ),
            'description' => '',
            'hide_title' => true,
            'callback' => array( 'WC_Admin_Reports', 'get_report' )
        );
        $reports[ 'stock' ][ 'reports' ][ 'serial-numbers' ] = array(
            'title' => __( 'Serial Numbers', 'serial-numbers' ),
            'description' => '',
            'hide_title' => true,
            'callback' => array( 'WC_Admin_Reports', 'get_report' )
        );
        return $reports;
    }

}

$serial_numbers = new Serial_Numbers();
?>