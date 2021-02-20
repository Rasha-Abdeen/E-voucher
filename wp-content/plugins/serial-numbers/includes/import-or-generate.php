<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

$serial_numbers_advanced_settings = get_option( '_serial_numbers_advanced_settings', array() );
$serial_numbers_name = value( $serial_numbers_advanced_settings, 'serial_numbers_name', __( 'Serial Number', 'serial-numbers' ) );
$all_serial_number_names = get_option( '_all_serial_number_names', array( 'serial_numbers' => 'serial_numbers',
    __( 'Serial Number', 'serial-numbers' ) => __( 'Serial Number', 'serial-numbers' ) ) );

class Serial_Numbers_Import_Or_Generate {

    /**
     * Constructor
     */
    function __construct() {
        $this->used_serial_numbers = array();
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    /**
     * Adds page to admin menu
     */
    function admin_menu() {
        add_submenu_page( 'woocommerce', __( 'Import or Generate Serial Numbers', 'serial-numbers' ), __( 'Import or Generate Serial Numbers', 'serial-numbers' ), 'manage_woocommerce', 'import-serial-numbers', array(
            $this, 'import_settings_page' ) );
    }

    function upload_serial_numbers() {
        check_admin_referer( 'serial-numbers-upload' );
        $file = $_FILES[ 'upload-serial-numbers' ][ 'tmp_name' ];
        if ( empty( $file ) ) {
            print __( 'File not found.', 'serial-numbers' );
            return;
        }
        $fh = fopen( $file, 'r' );
        if ( empty( $fh ) ) {
            print __( 'Unable to open file.', 'serial-numbers' );
            return;
        }
        $serial_numbers_uploaded = array();
        $row_count = 1;
        while ( $row = fgetcsv( $fh ) ) {
            if ( count( $row ) == 2 ) {
                $post_id = $row[ 0 ];
                if ( !is_numeric( $post_id ) ) {
                    print sprintf( __( 'Row %d is in the wrong format. The post id in the first column is not an integer.', 'serial-numbers' ), $row_count );
                    break;
                }
                if ( $row[ 1 ] == '' ) {
                    print sprintf( __( 'Row %d is in the wrong format. The serial number is blank.', 'serial-numbers' ), $row_count );
                    break;
                }
                if ( empty( $serial_numbers_uploaded[ $post_id ] ) ) {
                    $serial_numbers_uploaded[ $post_id ] = array();
                }
                $serial_numbers_uploaded[ $post_id ][ $row[ 1 ] ] = $row[ 1 ];
            } else {
                print sprintf( __( 'Row %d is in the wrong format. It does not have two columns.', 'serial-numbers' ), $row_count );
                break;
            }
            $row_count++;
        }
        fclose( $fh );
        if ( !empty( $serial_numbers_uploaded ) ) {
            foreach ( $serial_numbers_uploaded as $post_id => $serial_numbers ) {
                if ( empty( $serial_numbers ) ) {
                    continue;
                }
                $existing_serial_numbers = array();
                $serial_numbers_post_meta = get_post_meta( $post_id, '_serial_numbers', true );
                if ( !empty( $serial_numbers_post_meta ) ) {
                    foreach ( $serial_numbers_post_meta as $serial_number ) {
                        $existing_serial_numbers[ $serial_number ] = $serial_number;
                    }
                }
                foreach ( $serial_numbers as $serial_number ) {
                    $existing_serial_numbers[ wp_kses( $serial_number, array() ) ]
                            = wp_kses( $serial_number, array() );
                }
                delete_post_meta( $post_id, '_serial_numbers' );
                add_post_meta( $post_id, '_serial_numbers', $existing_serial_numbers, true );
            }
            print '<p>' . __( 'Upload complete', 'serial-numbers' ) . '</p>';
        }
    }

    function generate_serial_numbers_from_request() {
        check_admin_referer( 'serial-numbers-generate' );
        if ( empty( $post_id ) ) {
            print '<p>' . __( 'No product selected', 'serial-numbers' ) . '</p>';
            return;
        }
        $number = intval( $_POST[ 'number' ] );
        $random = $_POST[ 'random' ];
        $pattern = strval( $_POST[ 'pattern' ] );
        $post_id = intval( $_POST[ 'product' ] );
        if ( pow( 10, substr_count( $pattern, '#' ) ) < $number ) {
            print '<p style="color:red;">' . sprintf( __( 'Not enough # characters to generate %d random numbers', 'serial-numbers' ), $number ) . '</p>';
            return;
        }
        $this->generate_serial_numbers( $number, $random, $post_id, $pattern );
    }

    function preg_match_date( $matches ) {
        return date( substr( $matches[ 0 ], 1, strlen( $matches[ 0 ] ) - 2 ) );
    }

    function format_serial_number( $serial_number ) {
        return preg_replace_callback( '/({[a-zA-Z0-9]+})/', array( $this, 'preg_match_date' ), $serial_number );
    }

    function generate_serial_numbers( $number, $random, $post_id, $pattern ) {
        if ( $number <= 0 ) {
            return;
        }
        $existing_serial_numbers = array();
        $serial_numbers_post_meta = get_post_meta( $post_id, '_serial_numbers', true );
        if ( !empty( $serial_numbers_post_meta ) ) {
            foreach ( $serial_numbers_post_meta as $serial_number ) {
                $existing_serial_numbers[ $serial_number ] = $serial_number;
            }
        } else {
            $serial_numbers_post_meta = array();
        }
        $number_generated = 0;
        if ( $random == 'yes' ) {
            $pattern_length = strlen( $pattern );
            while ( $number_generated < $number ) {
                $generated_serial_number = $pattern;
                for ( $i = 0; $i < $pattern_length; $i++ ) {
                    if ( $generated_serial_number[ $i ] == '#' ) {
                        $generated_serial_number[ $i ] = rand( 0, 9 );
                    }
                }
                $generated_serial_number = $this->format_serial_number( $generated_serial_number );
                if ( empty( $existing_serial_numbers[ $generated_serial_number ] ) ) {
                    $number_generated++;
                    $serial_numbers_post_meta[] = $generated_serial_number;
                }
            }
        } else {
            $pattern_substring_length = substr_count( $pattern, '#' );
            $pattern_length = strlen( $pattern );
            $max = pow( 10, $pattern_substring_length );
            $new_serial_number = 0;
            while ( $number_generated < $number && $new_serial_number < $max ) {
                $new_serial_number_generated = str_pad( strval( $new_serial_number ), $pattern_substring_length, '0', STR_PAD_LEFT );
                $generated_serial_number = $pattern;
                $new_serial_number_generated_count = 0;
                for ( $pattern_count = 0; $pattern_count < $pattern_length;
                            $pattern_count++ ) {
                    if ( $pattern[ $pattern_count ] == '#' ) {
                        $generated_serial_number[ $pattern_count ] = $new_serial_number_generated[ $new_serial_number_generated_count ];
                        $new_serial_number_generated_count++;
                    }
                }
                $new_serial_number++;
                $generated_serial_number = $this->format_serial_number( $generated_serial_number );
                if ( empty( $existing_serial_numbers[ $generated_serial_number ] ) ) {
                    $number_generated++;
                    $serial_numbers_post_meta[] = $generated_serial_number;
                }
            }
        }
        delete_post_meta( $post_id, '_serial_numbers' );
        add_post_meta( $post_id, '_serial_numbers', $serial_numbers_post_meta, true );
        print '<p>' . __( 'Serial Numbers created', 'serial-numbers' ) . '</p>';
    }

    function import_settings_page() {
        print '<h1>' . __( 'Serial Numbers Import or Generate Page', 'serial-numbers' ) . '</h1>';
        print '<div class="serial-numbers-box">';
        if ( !empty( $_POST[ 'uploading_serial_numbers' ] ) ) {
            $this->upload_serial_numbers();
        }
        print '<h2>' . __( 'Import', 'serial-numbers' ) . '</h2>';
        print '<form id="serial-numbers-upload-form" method="POST" action="' . admin_url( 'admin.php?page=import-serial-numbers' ) . '" enctype="multipart/form-data" >';
        print '<p>' . __( 'This page is for uploading serial numbers by CSV.', 'serial-numbers' ) . '</p>';
        print '<p>' . sprintf( __( 'Here is a %s', 'serial-numbers' ), sprintf( '<a href="' . WP_PLUGIN_URL . '/serial-numbers/samples/serialnumbers.csv">%s</a>', __( 'sample file', 'serial-numbers' ) ) ) . '.</p>';
        print '<p>' . __( 'The first column of the file contains the product or variation id, the second column contains the serial number. The two columns are separated by a comma.', 'serial-numbers' ) . '</p>';
        print '<input type="file" name="upload-serial-numbers" /><br>';
        print '<input type="submit" value="' . __( 'Upload Serial Numbers', 'serial-numbers' ) . '" />';
        print '<input type="hidden" name="uploading_serial_numbers" value="yes" />';
        wp_nonce_field( 'serial-numbers-upload' );
        print '</form>';
        print '</div>';
        print '<div class="serial-numbers-box">';
        print '<h2>' . __( 'Generate Serial Numbers', 'serial-numbers' ) . '</h2>';
        if ( !empty( $_POST[ 'generating_serial_numbers' ] ) ) {
            $this->generate_serial_numbers_from_request();
        }
        print '<form id="serial-numbers-generate" method="POST" action="' . admin_url( 'admin.php?page=import-serial-numbers' ) . '" enctype="multipart/form-data" >';
        print '<p>' . __( 'What pattern would you like for your serial numbers (#\'s will be replaced by random numbers so ABC####D## would generate a serial number like ABC9451D37)?', 'serial-numbers' ) . '</p>';
        print '<p>'.__('Y, m and d within {} will be replaced with the year, month and date (so {Ymd} would be replaced with 20180327 on March 27th 2018).','serial-numbers').'</p>';      
        print '<input type="text" placeholder="SERIAL################" name="pattern" />';
        print '<p>' . __( 'How many serial numbers would you like to generate?', 'serial-numbers' ) . '</p>';
        print '<input type="number" placeholder="1000" name="number" />';
        print '<p>' . __( 'How would you like to generate serial numbers?', 'serial-numbers' ) . '</p>';
        print '<select name="random">';
        print '<option value="yes">' . __( 'Generate random serial numbers', 'serial-numbers' ) . '</option>';
        print '<option value="no">' . __( 'Generate serial numbers sequentially', 'serial-numbers' ) . '</option>';
        print '</select>';
        print '<p>' . __( 'To which product/variation would you like to assign these serial numbers?', 'serial-numbers' ) . '</p>';
        print '<select class="combobox" name="product">';
        print '<option value="">' . __( 'Select a product', 'serial-numbers' ) . '</option>';
        $product_query = new WP_Query( array(
            'post_type' => 'product',
            'posts_per_page' => -1,
                ) );
        while ( $product_query->have_posts() ) {
            $product_query->the_post();
            $product = wc_get_product( get_the_id() );
            if ( $product->get_type() == 'variable' ) {
                $children = $product->get_children();
                if ( !empty( $children ) ) {
                    foreach ( $children as $child ) {
                        $variation = wc_get_product( $child );
                        print '<option value="' . $child . '">' . $variation->get_formatted_name() . '</option>';
                    }
                }
            } else {
                print '<option value="' . get_the_id() . '">' . get_the_title() . '</option>';
            }
        }
        wp_reset_postdata();
        print '</select><br><br>';
        print '<input type="submit" value="' . __( 'Generate Serial Numbers', 'serial-numbers' ) . '" />';
        print '<input type="hidden" name="generating_serial_numbers" value="yes" />';
        wp_nonce_field( 'serial-numbers-generate' );
        print '</form>';
        print '</div>';
        $this->enqueue_scripts();
    }

    /**
     * CSS and JS
     */
    function enqueue_scripts() {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-tooltip' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'jquery-ui-button' );
        wp_enqueue_script( 'jquery-ui-widget' );
        wp_enqueue_script( 'jquery-ui-position' );
        wp_enqueue_script( 'jquery-ui-menu' );
        wp_enqueue_script( 'wp-a11y' );
        wp_enqueue_style( 'jquery-ui', plugins_url( '../css/jquery-ui.css', __FILE__ ) );
        wp_enqueue_script( 'serial-numbers-import-or-generate', WP_PLUGIN_URL . '/serial-numbers/js/import-or-generate.js', array(
            'jquery' ), false, true );
        wp_enqueue_style( 'serial-numbers-import-or-generate', WP_PLUGIN_URL . '/serial-numbers/css/import-or-generate.css' );
    }

}

$serial_numbers_import_or_generate = new Serial_Numbers_Import_Or_Generate();
