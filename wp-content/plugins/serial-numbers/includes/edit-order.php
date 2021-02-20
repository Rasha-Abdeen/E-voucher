<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class Serial_Numbers_Edit_Order {

    /**
     * Constructor
     */
    function __construct() {
        add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
        add_action( 'woocommerce_admin_order_items_after_line_items', array( $this,
            'woocommerce_admin_order_items_after_line_items' ) );
    }

    /**
     * Saves serial numbers used in the order
     */
    function save_post( $post_id, $post ) {
        if ( $post->post_type != 'shop_order' || empty( $_POST[ 'saving_serial_numbers' ] ) ) {
            return;
        }
        global $serial_numbers_checkout, $serial_numbers_name;
        if ( $serial_numbers_checkout->have_updated_serial_numbers ) {
            return;
        }
        $serial_numbers = value( $_POST, 'serial_numbers' );
        $order = new WC_Order();
        if ( empty( $serial_numbers ) ) {
            $serial_numbers = array();
            $line_items = $order->get_items();
            foreach ( $line_items as $line_item_id => $line_item ) {
                wc_delete_order_item_meta( $line_item_id, 'serial_numbers' );
                wc_delete_order_item_meta( $line_item_id, $serial_numbers_name );
            }
            return;
        }
        global $serial_numbers_checkout;
        foreach ( $serial_numbers as $line_item_id => $line_item_serial_numbers ) {
            if ( empty( $line_item_serial_numbers ) ) {
                wc_delete_order_item_meta( $line_item_id, 'serial_numbers' );
                wc_delete_order_item_meta( $line_item_id, $serial_numbers_name );
                continue;
            }
            $serial_numbers_exist = false;
            foreach ( $line_item_serial_numbers as $index =>
                        $line_item_serial_number ) {
                $line_item_serial_numbers[ $index ] = wp_kses( $line_item_serial_number, array() );
                if ( $line_item_serial_number != '' ) {
                    $serial_numbers_exist = true;
                }
            }
            wc_delete_order_item_meta( $line_item_id, 'serial_numbers' );
            wc_delete_order_item_meta( $line_item_id, $serial_numbers_name );
            if ( $serial_numbers_exist ) {
                wc_add_order_item_meta( $line_item_id, $serial_numbers_name, $serial_numbers_checkout->format( $line_item_serial_numbers ), true );
            }
        }
    }

    /**
     * Gets data for serial numbers dropdowns which are displayed using javascript
     */
    function woocommerce_admin_order_items_after_line_items( $order_id ) {
        print '<div id="scan-bar-code-dialog" title="' . __( 'Scan Serial Number', 'serial-numbers' ) . '">';
        print '<p>' . __( 'Please scan your barcode/qrcode and the serial number will appear in this textbox.', 'serial-numbers' ) . '</p>';
        print '<input id="scanned-barcode" type="text" placeholder="' . __( 'Scanned serial number', 'serial-numbers' ) . '" />';
        print '</div>';
        $scripts = array( 'jquery', 'jquery-ui-core', 'jquery-ui-mouse', 'jquery-ui-tooltip',
            'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-ui-resizable',
            'jquery-ui-draggable', 'jquery-ui-button', 'jquery-ui-position', 'jquery-ui-dialog',
            'jquery-ui-widget', 'jquery-ui-menu', 'wp-a11y', 'jquery-ui-autocomplete', );
        foreach ( $scripts as $script ) {
            wp_enqueue_script( $script );
        }
        wp_enqueue_style( 'jquery-ui', plugins_url( '../css/jquery-ui.css', __FILE__ ) );
        wp_enqueue_script( 'serial-numbers-edit-order', plugins_url( '../js/edit-order.js', __FILE__ ) );
        $order = new WC_Order( $order_id );
        $order_serial_numbers = array();
        $line_items = $order->get_items();
        $serial_numbers_available = array();
        global $serial_numbers_page;
        global $all_serial_number_names;
        $used_serial_numbers = $serial_numbers_page->used_serial_numbers();
        if ( !empty( $line_items ) ) {
            foreach ( $line_items as $line_item_id => $line_item ) {
                $product_id = value( $line_item, 'product_id' );
                $variation_id = value( $line_item, 'variation_id' );
                $product_used_serial_numbers = value( $used_serial_numbers, $product_id, array() );
                if ( !empty( $variation_id ) ) {
                    $product_id = $variation_id;
                }
                if ( !empty( $line_item[ 'variation_id' ] ) ) {
                    $product_id = $line_item[ 'variation_id' ];
                }
                $line_item_serial_numbers_available = get_post_meta( $product_id, '_serial_numbers', true );
                if ( !empty( $line_item_serial_numbers_available ) ) {
                    foreach ( $line_item_serial_numbers_available as $id =>
                                $serial_number ) {
                        if ( !empty( $product_used_serial_numbers[ $serial_number ] ) ) {
                            unset( $line_item_serial_numbers_available[ $id ] );
                        }
                    }
                    $line_item_serial_numbers = array();
                    foreach ( $all_serial_number_names as $serial_number_name ) {
                        $name_serial_numbers = wc_get_order_item_meta( $line_item_id, $serial_number_name, true );
                        if ( is_string( $name_serial_numbers ) && strpos( $name_serial_numbers, ', ' )
                                !== false ) {
                            $name_serial_numbers = explode( ', ', $name_serial_numbers );
                        }
                        if ( !empty( $name_serial_numbers ) ) {
                            if ( is_array( $name_serial_numbers ) ) {
                                foreach ( $name_serial_numbers as
                                            $name_serial_number ) {
                                    $line_item_serial_numbers[ $name_serial_number ]
                                            = $name_serial_number;
                                }
                            } else {
                                $line_item_serial_numbers[ $name_serial_numbers ]
                                        = $name_serial_numbers;
                            }
                        }
                    }
                    if ( is_string( $line_item_serial_numbers ) ) {
                        $line_item_serial_numbers = explode( ', ', $line_item_serial_numbers );
                    }
                    if ( empty( $line_item_serial_numbers ) ) {
                        $line_item_serial_numbers = array();
                    }
                    $order_serial_numbers[ $line_item_id ] = $line_item_serial_numbers;
                    $serial_numbers_available[ $line_item_id ] = $line_item_serial_numbers_available;
                }
            }
        }
        wp_localize_script( 'serial-numbers-edit-order', 'serial_numbers_edit_order_settings', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'serial_numbers_for' => __( 'Edit serial numbers for', 'serial-numbers' ),
            'order_serial_numbers' => $order_serial_numbers,
            'serial_numbers_available' => $serial_numbers_available,
            'please_choose_a_serial_number' => __( 'Choose a serial number', 'serial-numbers' ),
            'select_with_barcode' => __( 'select serial number with barcode scanner', 'serial-numbers' ),
            'or' => __( 'or', 'serial-numbers' ),
            'ok' => __( 'OK', 'serial-numbers' ),
            'cancel' => __( 'Cancel', 'serial-numbers' ),
        ) );
    }

}

$serial_numbers_edit_order = new Serial_Numbers_Edit_Order();
