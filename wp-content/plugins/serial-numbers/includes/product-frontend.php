<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class Serial_Numbers_Product_Frontend {

    function __construct() {
        add_action( 'woocommerce_single_product_summary', array( $this, 'woocommerce_single_product_summary' ), 30 );
    }

    function woocommerce_single_product_summary() {
        global $serial_numbers_page;
        global $post;
        $post_id = $post->ID;
        $how_serial_numbers_chosen = $serial_numbers_page->how_serial_numbers_chosen( $post_id );
        if ( $how_serial_numbers_chosen != 'customer_chooses' ) {
            return;
        }
        $serial_numbers_settings = get_post_meta( $post_id, '_serial_numbers_settings', true );
        $title = value( value( $serial_numbers_settings, 'customer_chooses', array() ), 'customer_chooses_title', __( 'Please choose a serial number', 'serial-numbers' ) );
        $product = wc_get_product( $post_id );
        $available_dropdowns_by_variation = array();
        $available_serial_numbers = array();
        $is_variable = 'no';
        if ( $product->get_type() == 'variable' ) {
            $is_variable = 'yes';
            $children = $product->get_children();
            if ( empty( $children ) ) {
                return;
            }
            foreach ( $children as $child ) {
                $available_dropdowns_by_variation[ $child ] = array( '' => $title )
                        + $serial_numbers_page->get_available_serial_numbers( $child );
            }
        } else {
            $available_serial_numbers = array( '' => $title ) + $serial_numbers_page->get_available_serial_numbers( $post_id );
        }
        wp_enqueue_script( 'serial-numbers-product-frontend', plugins_url( '/../js/product-frontend.js', __FILE__ ) );
        wp_localize_script( 'serial-numbers-product-frontend', 'serial_numbers_product_frontend_settings', array(
            'available_serial_numbers_by_variation' => $available_dropdowns_by_variation,
            'available_serial_numbers' => $available_serial_numbers,
            'is_variable' => $is_variable,
        ) );
        wp_enqueue_style( 'serial-numbers-product-frontend', plugins_url( '/../css/product-frontend.css', __FILE__ ) );
    }

}

$serial_numbers_product_frontend = new Serial_Numbers_Product_Frontend();
