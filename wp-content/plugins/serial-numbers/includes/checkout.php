<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class Serial_Numbers_Checkout {

    public $have_updated_serial_numbers = false;

    function __construct() {
        $this->have_updated_serial_numbers = false;
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this,
            'woocommerce_checkout_create_order_line_item' ), 10, 4 );
        add_action( 'woocommerce_new_order_item', array( $this, 'woocommerce_new_order_item' ), 10, 3 );
        add_action( 'woocommerce_order_status_processing', array( $this, 'woocommerce_order_status_processing' ) );
        add_action( 'woocommerce_order_status_completed', array( $this, 'woocommerce_order_status_processing' ) );
        add_filter( 'woocommerce_email_order_items_args', array( $this, 'woocommerce_email_order_items_args' ) );
    }

    function woocommerce_email_order_items_args( $args ) {
        if ( !empty( $args[ 'items' ] ) ) {
            foreach ( $args[ 'items' ] as $item_id => $item ) {
                $args[ 'items' ][ $item_id ]->read_meta_data( true );
            }
        }
        return $args;
    }

    function woocommerce_order_status_processing( $order_id ) {
        $order = new WC_Order( $order_id );
        $order_items = $order->get_items();
        global $serial_numbers_page;
        global $serial_numbers_name;
        global $serial_numbers_import_or_generate;
        foreach ( $order_items as $item_id => $item ) {
            $serial_numbers = wc_get_order_item_meta( $item_id, $serial_numbers_name, true );
            if ( !empty( $serial_numbers ) ) {
                continue;
            }
            $post_id = $item[ 'product_id' ];
            $how_serial_numbers_chosen = $serial_numbers_page->how_serial_numbers_chosen( $post_id );
            $serial_numbers_settings = get_option( '_serial_numbers_settings', array() );
            if ( $how_serial_numbers_chosen == 'auto_assigned' ) {
                $variation_id = value( $item, 'variation_id' );
                if ( !empty( $variation_id ) ) {
                    $post_id = $variation_id;
                }
                $quantity = intval( apply_filters( 'woocommerce_order_item_quantity', $item[ 'qty' ], $order, $item ) );
                $available_serial_numbers = $serial_numbers_page->get_available_serial_numbers( $post_id );
                $number_to_generate = $quantity - count( $available_serial_numbers );
                if ( $number_to_generate >= 0 ) {
                    $serial_numbers_import_or_generate->generate_serial_numbers( $number_to_generate, get_post_meta( $post_id, '_serial_number_random', true ), $post_id, get_post_meta( $post_id, '_serial_number_pattern', true ) );
                }
                $available_serial_numbers = $serial_numbers_page->get_available_serial_numbers( $post_id );
                $serial_numbers = array();
                $available_serial_number_keys = array_keys( $available_serial_numbers );
                for ( $i = 0; $i < $quantity && count( $available_serial_numbers )
                        > 0; $i++ ) {
                    if ( !empty( $available_serial_numbers ) ) {
                        $serial_numbers[] = $available_serial_numbers[ $available_serial_number_keys[ $i ] ];
                        unset( $available_serial_numbers[ $available_serial_number_keys[ $i ] ] );
                        unset( $available_serial_number_keys[ $i ] );
                    }
                }
                if ( !empty( $serial_numbers ) ) {
                    $serial_numbers_formatted = $this->format( $serial_numbers );
                    $this->have_updated_serial_numbers = true;
                    wc_add_order_item_meta( $item_id, $serial_numbers_name, $serial_numbers_formatted, true );
                }
            }
        }
    }

    function format( $serial_numbers ) {
        return join( ', ', $serial_numbers );
    }

    function woocommerce_new_order_item( $item_id, $item, $order_id ) {
        if ( empty( $item ) ) {
            return;
        }
        global $serial_numbers_name;
        $advanced_serial_numbers_settings = get_option( '_serial_numbers_advanced_settings', array() );
        $name = value( $advanced_serial_numbers_settings, 'serial_numbers_name', __( 'Serial Number', 'serial-numbers' ) );
        $serial_numbers = explode( ', ', $item->get_meta( $name, true ) );
        if ( !empty( $serial_numbers ) && !empty( $serial_numbers[ 0 ] ) ) {
            $formatted_serial_numbers = $this->format( $serial_numbers );
            wc_add_order_item_meta( $item_id, $serial_numbers_name, $formatted_serial_numbers, true );
        }
    }

    /**
     * Adds options to order item meta
     */
    function woocommerce_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
        global $serial_numbers_name;
        if ( !empty( $values[ 'serial_numbers' ] ) ) {
            $serial_numbers = explode( ', ', value( value( $values[ 'serial_numbers' ], 0, array() ), 'value' ) );
            $item->add_meta_data( $serial_numbers_name, $this->format( $serial_numbers ), true );
        }
    }

}

$serial_numbers_checkout = new Serial_Numbers_Checkout();
?>