<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class WC_Report_Product_Serial_Numbers extends WP_List_Table {

    private $product_serial_numbers;

    /**
     * Constructor
     */
    public function __construct() {
        $this->product_serial_numbers = array();
        parent::__construct( array(
            'singular' => __( 'Product Serial Number', 'serial-numbers' ),
            'plural' => __( 'Product Serial Numbers', 'serial-numbers' ),
            'ajax' => false
        ) );
    }

    /**
     * Export button
     */
    public function get_export_button() {
        $current_range = !empty( $_GET[ 'range' ] ) ? sanitize_text_field( $_GET[ 'range' ] )
                    : '7day';
        ?>
        <a
            href="#"
            download="report-<?php print esc_attr( $current_range ); ?>-<?php
            print date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
            ?>.csv"
            class="export_csv"
            data-export="chart"
            data-xaxes="<?php _e( 'Date', 'woocommerce' ); ?>"
            data-exclude_series="2"
            data-groupby="<?php print $this->chart_groupby; ?>"
            >
                <?php _e( 'Export CSV', 'woocommerce' ); ?>
        </a>
        <?php
    }

    /**
     * No items found text
     */
    public function no_items() {
        _e( 'No product serial numbers found.', 'serial-numbers' );
    }

    /**
     * Display the report
     */
    public function output_report() {
        $this->prepare_items();

        print '<div id="poststuff" class="woocommerce-reports-wide">';

        print '<form method="post" id="woocommerce_product_serial_numbers">';
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
    function column_default( $order_product, $column_name, $link = true ) {
        if ( count( $order_product ) == 0 ) {
            return;
        }
        $first_product = $order_product;
        switch ( $column_name ) {
            case 'name':
                return value( $first_product, 'name' );
            case 'order_number':
                $order_id = value( $first_product, 'order_number' );
                $order_number = $order_id;
                if ( $link ) {
                    $order_number = '<a href="' . admin_url( 'post.php?post=' . $order_id . '&action=edit' ) . '">#' . $order_number . '</a>';
                }
                return $order_number;
            case 'product_title':
                $product_id = value( $first_product, 'product_id' );
                $product_title = value( $first_product, 'product_title' );
                $product = wc_get_product( $product_id );
                if ( $product->get_type() == 'variation' ) {
                    $product_title = $product->get_formatted_name();
                }
                if ( $link ) {
                    $product_title = '<a href="' . admin_url( 'post.php?post=' . $product_id . '&action=edit' ) . '">' . $product_title . '</a>';
                }
                return $product_title;
            case 'serial_numbers':
                return value( $first_product, 'serial_numbers' );
                break;
        }
    }

    /**
     * The columns
     */
    public function get_columns() {
        $columns = array(
            'order_number' => __( 'Order Number', 'serial-numbers' ),
            'product_title' => __( 'Product Title', 'serial-numbers' ),
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
        $serial_numbers_data = $this->get_product_serial_numbers();
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
    function get_product_serial_numbers() {
        if ( !empty( $this->product_serial_numbers ) ) {
            return $this->product_serial_numbers;
        }
        $this->product_serial_numbers = array();
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
                        if ( empty( $this->product_serial_numbers[ $order_id . '-' . $product_id ][ 'serial_numbers' ] ) ) {
                            $this->product_serial_numbers[ $order_id . '-' . $product_id ]
                                    = array(
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
        $this->items = $this->get_product_serial_numbers();
        $this->set_pagination_args( array(
            'total_items' => count( $this->items ),
            'per_page' => $per_page,
            'total_pages' => ceil( count( $this->items ) / $per_page )
        ) );
    }

}
