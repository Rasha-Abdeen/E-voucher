<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class WC_Report_Customer_Serial_Numbers extends WP_List_Table {

    private $product_serial_numbers;

    /**
     * Constructor
     */
    public function __construct() {
        $this->customer_serial_numbers = array();
        parent::__construct( array(
            'singular' => __( 'Customer Serial Number', 'serial-numbers' ),
            'plural' => __( 'Customer Serial Numbers', 'serial-numbers' ),
            'ajax' => false
        ) );
    }

    /**
     * No items found text
     */
    public function no_items() {
        _e( 'No customer serial numbers found.', 'serial-numbers' );
    }

    /**
     * Display the report
     */
    public function output_report() {
        $this->prepare_items();

        print '<div id="poststuff" class="woocommerce-reports-wide">';

        print '<form method="post" id="woocommerce_customer_serial_numbers">';
        $_SESSION[ 'serial_numbers_csv' ] = $this->print_csv_report();
        $_SESSION[ 'serial_numbers_filename' ] = 'report-page-' . $this->get_pagenum() . '-' . date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) . '.csv';
        print '<a class="button right" href="' . WP_PLUGIN_URL . '/serial-numbers/includes/download.php">' . __( 'Export as CSV', 'serial-numbers' ) . '</a>';
        $this->display();

        print '</form>';
        print '</div>';
        wp_enqueue_style( 'serial-number-reports-css', WP_PLUGIN_URL . '/serial-numbers/css/reports.css' );
    }

    /**
     * Gets data for columns
     */
    function column_default( $user, $column_name, $link = true ) {
        switch ( $column_name ) {
            case 'email' :
                return value( $user, 'email' );

            case 'customer_name' :
                return value( $user, 'name' );

            case 'serial_numbers' :
                return value( $user, 'serial_numbers' );

            case 'product_title':
                $product_id = value( $user, 'product_id' );
                $product_title = value( $user, 'product_title' );
                $product = wc_get_product( $product_id );
                if ( $product->get_type() == 'variation' ) {
                    $product_title = $product->get_formatted_name();
                }
                if ( $link ) {
                    $product_title = ' <a href="' . admin_url( 'post.php?post=' . $product_id . '&action=edit' ) . '">' . $product_title . '</a> ';
                }
                return $product_title;
            case 'order_number':
                $order_id = value( $user, 'order_number' );
                $order_number = $order_id;
                if ( $link ) {
                    $order_number = '<a href="' . admin_url( 'post.php?post=' . $order_id . '&action=edit' ) . '">#' . $order_number . '</a>';
                }
                return $order_number;
        }
    }

    /**
     * The columns
     */
    public function get_columns() {
        $columns = array(
            'customer_name' => __( 'Customer Name', 'serial-numbers' ),
            'email' => __( 'Email', 'serial-numbers' ),
            'order_number' => __( 'Order Number', 'serial-numbers' ),
            'product_title' => __( 'Product', 'serial-numbers' ),
            'serial_numbers' => __( 'Serial Numbers', 'serial-numbers' ),
        );

        return $columns;
    }

    /**
     * Exports report to csv
     */
    function print_csv_report() {
        $report = '';
        $columns = $this->get_columns();
        foreach ( $columns as $column ) {
            $report .= $column . ',';
        }
        $report = trim( $report, ',' ) . '
"';
        $serial_numbers_data = $this->get_customer_serial_numbers();
        if ( empty( $serial_numbers_data ) ) {
            $serial_numbers = array();
        } else {
            $serial_numbers = array_values( $serial_numbers_data );
        }
        $per_page = 20;
        $current_page = absint( $this->get_pagenum() );
        $start = 0;
        $end = count( $serial_numbers );
        for ( $i = $start; $i < $end; $i++ ) {
            if ( empty( $serial_numbers[ $i ] ) ) {
                continue;
            }
            $row = $serial_numbers[ $i ];
            foreach ( $columns as $id => $column ) {
                $report = $report . str_replace( '"', '\'', str_replace( '","', ' - ', $this->column_default( $row, $id, false ) ) ) . '","';
            }
            $report = substr( $report, 0, strlen( $report ) - 2 ) . '
"';
        }
        $report = str_replace( '&#36;', '$', str_replace( '&ndash;', '-', strip_tags( htmlspecialchars_decode( $report ) ) ) );
        return substr( $report, 0, strlen( $report ) - 1 );
    }

    /**
     * Gets the serial numbers data
     */
    function get_customer_serial_numbers() {
        if ( !empty( $this->customer_serial_numbers ) ) {
            return $this->customer_serial_numbers;
        }
        $this->product_serial_numbers = array();
        $customer_details_by_order_id = array();

        global $serial_numbers_page;
        $order_ids_by_product_and_serial_number = $serial_numbers_page->used_serial_numbers();
        if ( !empty( $order_ids_by_product_and_serial_number ) ) {
            foreach ( $order_ids_by_product_and_serial_number as $product_id =>
                        $order_ids_by_serial_number ) {
                if ( $product_id === 0 ) {
                    continue;
                }
                if ( !empty( $order_ids_by_serial_number ) ) {
                    foreach ( $order_ids_by_serial_number as $serial_number =>
                                $order_id ) {
                        if ( empty( $customer_details_by_order_id[ $order_id ] ) ) {
                            $order = new WC_Order( $order_id );
                            $order_date = $order->get_date_created();
                            $billing_email = $order->get_billing_email();
                            $first_name = $order->get_billing_first_name();
                            $last_name = $order->get_billing_last_name();
                            $name = $first_name . ' ' . $last_name;
                            $customer_details_by_order_id[ $order_id ] = array(
                                'name' => $name,
                                'order_date' => $order_date,
                                'email' => $billing_email,
                            );
                        }
                        if ( empty( $this->product_serial_numbers[ $order_id . '-' . $product_id ][ 'serial_numbers' ] ) ) {
                            $this->product_serial_numbers[ $order_id . '-' . $product_id ]
                                    = $customer_details_by_order_id[ $order_id ]
                                    + array(
                                'serial_numbers' => $serial_number,
                                'product_id' => $product_id,
                                'product_title' => get_the_title( $product_id ),
                                'order_number' => $order_id,
                            );
                        } else {
                            $this->product_serial_numbers[ $order_id . '-' . $product_id ][ 'serial_numbers' ] .= ', ' . $serial_number;
                        }
                    }
                }
            }
        }
        return $this->product_serial_numbers;
    }

    /**
     * Prepares the data for the table
     */
    public function prepare_items() {
        $current_page = absint( $this->get_pagenum() );
        $per_page = 20;

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

        $this->items = $this->get_customer_serial_numbers();

        $this->set_pagination_args( array(
            'total_items' => count( $this->items ),
            'per_page' => $per_page,
            'total_pages' => ceil( count( $this->items ) / $per_page )
        ) );
    }

}
