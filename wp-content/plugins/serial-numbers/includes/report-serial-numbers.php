<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class WC_Report_Serial_Numbers extends WP_List_Table {

    private $product_serial_numbers = array();

    /**
     * __construct function.
     */
    public function __construct() {
        $this->product_serial_numbers = array();
        parent::__construct( array(
            'singular' => __( 'Serial Number', 'serial-numbers' ),
            'plural' => __( 'Serial Numbers', 'serial-numbers' ),
            'ajax' => false
        ) );
    }

    /**
     * Output an export link
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
        _e( 'No serial numbers found.', 'serial-numbers' );
    }

    /**
     * Output the report
     */
    public function output_report() {
        $this->prepare_items();

        print '<div id="poststuff" class="woocommerce-reports-wide">';

        /* print '<form method="get" action="admin.php">'
          . __( 'Search:', 'serial-numbers' )
          . '<input type="hidden" name="page" value="' . value( $_GET, 'page' ) . '" />'
          . '<input type="hidden" name="tab" value="' . value( $_GET, 'tab' ) . '" />'
          . '<input type="hidden" name="report" value="' . value( $_GET, 'report' ) . '" />'
          . ' <input type="text" name="meta_search" placeholder="' . __( 'Search Product Meta', 'serial-numbers' ) . '" value="' . value( $_GET, 'meta_search' ) . '" />'
          . ' <input type="submit" value="' . __( 'GO', 'serial-numbers' ) . '" />'
          . '</form>'; */

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
     * column_default function.
     *
     * @param mixed  $user
     * @param string $column_name
     * @return int|string
     * @todo Inconsistent return types, and void return at the end. Needs a rewrite.
     */
    function column_default( $order_product, $column_name, $link = true ) {
        if ( count( $order_product ) == 0 ) {
            return;
        }
        $first_product = $order_product;
        switch ( $column_name ) {
            case 'product_title':
                $product_title = value( $first_product, 'product_title' );
                $product_id = value( $first_product, 'product_id' );
                if ( $link ) {
                    $product_title = '<a href="' . admin_url( 'post.php?post=' . $product_id . '&action=edit' ) . '">' . $product_title . '</a>';
                }
                return $product_title;
            case 'stock':
                $stock = value( $first_product, 'stock', __( '-', 'serial-numbers' ) );
                return $stock;
            case 'serial_numbers':
                $serial_numbers = '';
                if ( !empty( $order_product[ 'serial_numbers' ] ) ) {
                    foreach ( $order_product[ 'serial_numbers' ] as
                                $serial_number ) {
                        $serial_numbers .= $serial_number . ' | ';
                    }
                }
                $serial_numbers = trim( $serial_numbers, ' |' );
                if ( empty( $serial_numbers ) ) {
                    $serial_numbers = __( '-', 'serial-numbers' );
                }
                return $serial_numbers;
                break;
        }
    }

    /**
     * get_columns function.
     */
    public function get_columns() {
        $columns = array(
            'product_title' => __( 'Product Title', 'serial-numbers' ),
            'stock' => __( 'Stock Quantity', 'serial-numbers' ),
            'serial_numbers' => __( 'Serial Numbers', 'serial-numbers' ),
        );

        return $columns;
    }

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
        //
//		$start = ($current_page - 1) * $per_page;
//		$end = $current_page * $per_page;
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

    function get_product_serial_numbers() {
        if ( !empty( $this->product_serial_numbers ) ) {
            return $this->product_serial_numbers;
        }
        return $this->product_serial_numbers();
    }

    function product_serial_numbers() {
        //Get All Products
        //global $serial_numbers_page;
        //$used_serial_numbers = $serial_numbers_page->used_serial_numbers();
        global $wpdb;
        $sql = "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type='product'";
        if ( !empty( $_GET[ 'meta_search' ] ) ) {
            $sql = $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts AS p INNER JOIN {$wpdb->prefix}postmeta AS pm"
                    . " ON p.ID=pm.post_id WHERE post_type='product' AND meta_value "
                    . "= %s", $_GET[ 'meta_search' ] );
        }
        $results = $wpdb->get_col( $sql );
        foreach ( $results as $result ) {
            $product = wc_get_product( $result );
            $product_id = $result;
            if ( $product->get_type() == 'variable' ) {
                $children = $product->get_children();
                foreach ( $children as $child ) {
                    $post_id = $child;
                    $variation = wc_get_product( $post_id );
                    if ( empty( $this->product_serial_numbers[ $post_id ] ) ) {
                        $this->product_serial_numbers[ $post_id ] = array( 'serial_numbers' => array() );
                    }
                    $this->product_serial_numbers[ $post_id ][ 'product_title' ]
                            = $variation->get_formatted_name();
                    $this->product_serial_numbers[ $post_id ][ 'stock' ] = get_post_meta( $post_id, '_stock', true );
                    $this->product_serial_numbers[ $post_id ][ 'product_id' ] = $product_id;
                    $serial_numbers = get_post_meta( $post_id, '_serial_numbers', true );
                    if ( !empty( $serial_numbers ) ) {
                        foreach ( $serial_numbers as $serial_number ) {
                            //if ( !empty( $used_serial_numbers[ $post_id ] ) && !empty(
                            //                $used_serial_numbers[ $post_id ][ $serial_number ] ) ) {
                            //    
                            //} else {
                            $this->product_serial_numbers[ $post_id ][ 'serial_numbers' ][ $serial_number ]
                                    = $serial_number;
                            //}
                        }
                    }
                }
            } else {
                $post_id = $result;
                $variation = wc_get_product( $post_id );
                if ( empty( $this->product_serial_numbers[ $post_id ] ) ) {
                    $this->product_serial_numbers[ $post_id ] = array( 'serial_numbers' => array() );
                }
                $this->product_serial_numbers[ $post_id ][ 'product_title' ] = $variation->get_formatted_name();
                $this->product_serial_numbers[ $post_id ][ 'stock' ] = get_post_meta( $post_id, '_stock', true );
                $this->product_serial_numbers[ $post_id ][ 'product_id' ] = $product_id;
                $serial_numbers = get_post_meta( $post_id, '_serial_numbers', true );
                if ( !empty( $serial_numbers ) ) {
                    foreach ( $serial_numbers as $serial_number ) {
                        //if ( !empty( $used_serial_numbers[ $post_id ] ) && !empty(
                        //                $used_serial_numbers[ $post_id ][ $serial_number ] ) ) {
                        //} else {
                        $this->product_serial_numbers[ $post_id ][ 'serial_numbers' ][ $serial_number ]
                                = $serial_number;
                        //}
                    }
                }
            }
        }
        return $this->product_serial_numbers;
    }

    /**
     * prepare_items function.
     */
    public function prepare_items() {
        $current_page = absint( $this->get_pagenum() );
        $per_page = 20;

        /**
         * Init column headers
         */
        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );


        $this->items = $this->get_product_serial_numbers();


        /**
         * Pagination
         */
        $this->set_pagination_args( array(
            'total_items' => count( $this->items ),
            'per_page' => $per_page,
            'total_pages' => ceil( count( $this->items ) / $per_page )
        ) );
    }

}
