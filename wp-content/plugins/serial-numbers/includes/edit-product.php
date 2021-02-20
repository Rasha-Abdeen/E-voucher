<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class Serial_Numbers_Edit_Product {

    /**
     * Constructor
     */
    function __construct() {
        add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'woocommerce_product_data_tabs' ) );
        add_action( 'woocommerce_product_data_panels', array( $this, 'woocommerce_product_data_panels' ) );
        add_action( 'woocommerce_product_after_variable_attributes', array( $this,
            'woocommerce_product_after_variable_attributes' ), 10, 3 );
    }

    /**
     * Adds serial numbers data when saving a product
     */
    function save_post( $post_id, $post ) {
        if ( $post->post_type != 'product' || empty( $_POST[ 'saving_serial_numbers' ] ) ) {
            return;
        }
        $_serial_numbers = wpshowcase_kses_post( value( value( $_POST, 'serial_numbers_settings', array() ), $post_id ) );
        delete_post_meta( $post_id, '_serial_numbers_settings' );
        add_post_meta( $post_id, '_serial_numbers_settings', $_serial_numbers, true );
        $serial_number_posts = value( $_POST, 'serial_number_posts', array() );
        $_serial_numbers = value( value( $serial_number_posts, strval( $post_id ), array() ), 'serial_number' );
        if ( !empty( $_serial_numbers ) ) {
            foreach ( $_serial_numbers as $id => $serial_number ) {
                $_serial_numbers[ $id ] = wp_kses( $serial_number, array() );
            }
        }
        delete_post_meta( $post_id, '_serial_numbers' );
        add_post_meta( $post_id, '_serial_numbers', $_serial_numbers, true );
        $pattern = value( value( $serial_number_posts, strval( $post_id ), array() ), 'pattern' );
        delete_post_meta( $post_id, '_serial_number_pattern' );
        add_post_meta( $post_id, '_serial_number_pattern', $pattern, true );          
        $random = value( value( $serial_number_posts, strval( $post_id ), array() ), 'random' );
        delete_post_meta( $post_id, '_serial_number_random' );
        add_post_meta( $post_id, '_serial_number_random', $random, true );          
        $product = wc_get_product( $post_id );
        if ( $product->get_type() == 'variable' ) {
            $children = $product->get_children();
            if ( !empty( $children ) ) {
                foreach ( $children as $child ) {
                    if ( !is_numeric( $child ) ) {
                        continue;
                    }
                    $_serial_numbers = value( value( $serial_number_posts, strval( $child ), array() ), 'serial_number' );
                    if ( !empty( $_serial_numbers ) ) {
                        foreach ( $_serial_numbers as $id => $serial_number ) {
                            $_serial_numbers[ $id ] = wp_kses( $serial_number, array() );
                        }
                    }
                    delete_post_meta( $child, '_serial_numbers' );
                    add_post_meta( $child, '_serial_numbers', $_serial_numbers, true );
                    $pattern = wp_kses_post( value( value( $serial_number_posts, strval( $child ), array() ), 'pattern' ) );
                    delete_post_meta( $child, '_serial_number_pattern' );
                    add_post_meta( $child, '_serial_number_pattern', $pattern, true );
                    $random = wp_kses_post( value( value( $serial_number_posts, strval( $child ), array() ), 'random' ) );
                    delete_post_meta( $child, '_serial_number_random' );
                    add_post_meta( $child, '_serial_number_random', $random, true );  
                }
            }
        }
    }

    /**
     * Adds serial numbers to the product tabs
     */
    function woocommerce_product_data_tabs( $product_data_tabs ) {
        $product_data_tabs[ 'serial_numbers' ] = array(
            'label' => __( 'Serial Numbers', 'serial-numbers' ),
            'target' => 'serial_numbers_data',
            'class' => 'hide_if_external hide_if_grouped',
        );
        return $product_data_tabs;
    }

    /**
     * The content of the serial numbers tab
     */
    function woocommerce_product_data_panels() {
        print '<div id="serial_numbers_data" class="panel woocommerce_options_panel hidden">';
        print '<input type="hidden" name="saving_serial_numbers" value="yes" />';
        print '<div class="product-serial-number-wrapper">';
        global $serial_numbers_page;
        global $post;
        $product = wc_get_product( $post->ID );
        $serial_numbers_page->product_settings( $post->ID );
        if ( $product->get_type() != 'variable' ) {
            $serial_numbers_page->product_serial_numbers( $post->ID );
        } else {
            print '<br><p>' . __( '(You can edit serial numbers when editing variations.)', 'serial-numbers' ) . '</p>';
        }
        print '<input type="hidden" class="post-id" value="' . $post->ID . '" />';
        print '</div>';
        print '</div>';
        $serial_numbers_page->enqueue_scripts();
    }

    /**
     * Adds serial numbers to variable products
     */
    function woocommerce_product_after_variable_attributes( $loop, $variation_data, $variation ) {
        print '<div class="product-serial-number-wrapper">';
        global $serial_numbers_page;
        $serial_numbers_page->product_serial_numbers( $variation->ID );
        print '<input type="hidden" class="post-id" value="' . $variation->ID . '" />';
        print '</div>';
    }

}

$serial_numbers_edit_product = new Serial_Numbers_Edit_Product();
