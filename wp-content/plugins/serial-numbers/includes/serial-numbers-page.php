<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

$serial_numbers_advanced_settings = get_option( '_serial_numbers_advanced_settings', array() );
$serial_numbers_name = value( $serial_numbers_advanced_settings, 'serial_numbers_name', __( 'Serial Number', 'serial-numbers' ) );
$all_serial_number_names = get_option( '_all_serial_number_names', array( 'serial_numbers' => 'serial_numbers',
    __( 'Serial Number', 'serial-numbers' ) => __( 'Serial Number', 'serial-numbers' ) ) );

class Serial_Numbers_Page {

    /**
     * Constructor
     */
    function __construct() {
        $this->used_serial_numbers = array();
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'wp_ajax_save_serial_numbers', array( $this, 'wp_ajax_save_serial_numbers' ) );
    }

    /**
     * Gets the serial numbers which have been assigned to orders
     */
    function used_serial_numbers() {
        if ( !empty( $this->used_serial_numbers ) ) {
            return $this->used_serial_numbers;
        }
        global $wpdb;
        global $all_serial_number_names;
        $all_serial_number_names_array = array();
        foreach ( $all_serial_number_names as $index => $value ) {
            $all_serial_number_names_array[ $index ] = $wpdb->prepare( '%s', $value );
        }
        $refunded_orders_sql = '';
        $serial_numbers_settings = get_option( '_serial_numbers_settings', array() );
        if ( !empty( $serial_numbers_settings[ 'reassign_refunded_and_canceled_and_failed_orders' ] )
                && $serial_numbers_settings[ 'reassign_refunded_and_canceled_and_failed_orders' ]
                == 'no' ) {
            $refunded_orders_sql = "OR orders.post_status LIKE '%refunded%' OR orders.post_status LIKE '%cancelled%' OR orders.post_status LIKE '%failed%'";
        }
        $from_all_serial_numbers = '( ' . join( ', ', $all_serial_number_names_array ) . ' )';
        $sql = "SELECT oi_pi.meta_value as product_id, oi_pi.meta_value as variation_id, oi_sn.meta_value as serial_number, order_id "
                . "FROM {$wpdb->prefix}woocommerce_order_itemmeta AS oi_pi INNER JOIN "
                . "{$wpdb->prefix}woocommerce_order_itemmeta AS oi_sn ON oi_pi.order_item_id=oi_sn.order_item_id INNER JOIN "
                . "{$wpdb->prefix}woocommerce_order_items AS ois ON oi_pi.order_item_id = ois.order_item_id INNER JOIN "
                . " {$wpdb->prefix}posts AS orders ON orders.ID=order_id INNER JOIN "
                . "{$wpdb->prefix}posts as products ON products.ID=oi_pi.meta_value INNER JOIN "
                . "{$wpdb->prefix}term_relationships AS tr ON tr.object_id = products.ID INNER JOIN "
                . "{$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN "
                . "{$wpdb->prefix}terms AS t ON t.term_id = tt.term_id "
                . " WHERE t.slug!='variable' AND products.post_type='product' AND oi_pi.meta_key='_product_id' AND oi_sn.meta_key IN {$from_all_serial_numbers} "
                . "AND orders.post_type='shop_order' AND (orders.post_status LIKE '%complete%' OR orders.post_status LIKE '%processing%' $refunded_orders_sql)";
        $used_serial_numbers_1 = $wpdb->get_results( $sql );
        $sql = "SELECT oi_pi.meta_value as product_id, oi_pi.meta_value as variation_id, oi_sn.meta_value as serial_number, order_id "
                . "FROM {$wpdb->prefix}woocommerce_order_itemmeta AS oi_pi INNER JOIN "
                . "{$wpdb->prefix}woocommerce_order_itemmeta AS oi_sn ON oi_pi.order_item_id=oi_sn.order_item_id INNER JOIN "
                . "{$wpdb->prefix}woocommerce_order_items AS ois ON oi_pi.order_item_id = ois.order_item_id INNER JOIN "
                . " {$wpdb->prefix}posts AS orders ON orders.ID=order_id INNER JOIN "
                . "{$wpdb->prefix}posts as products ON products.ID=oi_pi.meta_value"
                . " WHERE products.post_type='product_variation' AND oi_pi.meta_key='_variation_id' AND oi_sn.meta_key IN {$from_all_serial_numbers} "
                . "AND orders.post_type='shop_order' AND (orders.post_status LIKE '%complete%' OR orders.post_status LIKE '%processing%' $refunded_orders_sql)";
        $used_serial_numbers_2 = $wpdb->get_results( $sql );
        print '<br><br>';
        $used_serial_numbers = array_merge( $used_serial_numbers_1, $used_serial_numbers_2 );
        foreach ( $used_serial_numbers as $used_serial_number ) {
            $product_id = intval( maybe_unserialize( $used_serial_number->product_id ) );
            $serial_number = maybe_unserialize( $used_serial_number->serial_number );
            if ( is_string( $serial_number ) ) {
                $serial_number = explode( ', ', $serial_number );
            }
            $order_id = maybe_unserialize( $used_serial_number->order_id );
            if ( empty( $this->used_serial_numbers[ $product_id ] ) ) {
                $this->used_serial_numbers[ $product_id ] = array();
            }
            $serial_numbers_as_array = $serial_number;
            if ( !empty( $serial_numbers_as_array ) ) {
                foreach ( $serial_numbers_as_array as $serial_number_from_array ) {
                    $this->used_serial_numbers[ $product_id ][ $serial_number_from_array ]
                            = $order_id;
                }
            }
        }
        return $this->used_serial_numbers;
    }

    /**
     * Adds page to admin menu
     */
    function admin_menu() {
        add_submenu_page( 'woocommerce', __( 'Serial Numbers', 'serial-numbers' ), __( 'Serial Numbers', 'serial-numbers' ), 'manage_woocommerce', 'serial-numbers', array(
            $this, 'settings_page' ) );
    }

    /**
     * Saves the serial numbers data
     */
    function save_serial_numbers() {
        check_admin_referer( 'serial-numbers-page-save', 'serial-numbers-page' );

        $advanced_serial_numbers_settings = wpshowcase_kses_post( value( $_POST, 'serial_numbers_advanced_settings', array() ) );
        $name = value( $advanced_serial_numbers_settings, 'serial_numbers_name' );
        if ( !empty( $name ) ) {
            $all_serial_number_names = get_option( '_all_serial_number_names', array(
                'serial_numbers' => 'serial_numbers', __( 'Serial Number', 'serial-numbers' ) => __( 'Serial Number', 'serial-numbers' ) ) );
            $all_serial_number_names[ $name ] = $name;
            update_option( '_all_serial_number_names', $all_serial_number_names );
        } else {
            $advanced_serial_numbers_settings[ 'serial_numbers_name' ] = __( 'Serial Number', 'serial-numbers' );
        }
        update_option( '_serial_numbers_advanced_settings', $advanced_serial_numbers_settings );

        $serial_numbers_settings = wpshowcase_kses_post( value( $_POST, 'serial_numbers_settings', array() ) );
        if ( !empty( $serial_numbers_settings ) ) {
            foreach ( $serial_numbers_settings as $post_id => $settings ) {
                if ( is_numeric( $post_id ) ) {
                    delete_post_meta( $post_id, '_serial_numbers_settings' );
                    add_post_meta( $post_id, '_serial_numbers_settings', $settings, true );
                    unset( $serial_numbers_settings[ $post_id ] );
                }
            }
        }
        update_option( '_serial_numbers_settings', $serial_numbers_settings );

        $posts = value( $_POST, 'serial_number_posts', array() );
        $this->save_serial_number_posts( $posts );
    }

    function save_serial_number_posts( $posts ) {
        if ( !empty( $posts ) ) {
            foreach ( $posts as $post_id => $data ) {
                $pattern = wp_kses( value( $data, 'pattern' ), array() );
                $random = wp_kses( value( $data, 'random' ), array() );
                $price = wp_kses( value( $data, 'price' ), array() );
                $stock = wp_kses( value( $data, 'stock' ), array() );
                $stock_status = value( $data, 'stock_status' );
                $serial_numbers = value( $data, 'serial_number' );
                if ( is_array( $serial_numbers ) && !empty( $serial_numbers ) ) {
                    foreach ( $serial_numbers as $index => $serial_number ) {
                        unset( $serial_numbers[ $index ] );
                        $serial_numbers[ wp_kses( $serial_number, array() ) ] = wp_kses( $serial_number, array() );
                    }
                }
                wc_update_product_stock_status( $post_id, $stock_status );
                delete_post_meta( $post_id, '_serial_number_pattern' );
                delete_post_meta( $post_id, '_serial_number_random' );
                delete_post_meta( $post_id, '_regular_price' );
                delete_post_meta( $post_id, '_price' );
                delete_post_meta( $post_id, '_manage_stock' );
                $manage_stock = value( $data, 'manage_stock' );
                add_post_meta( $post_id, '_manage_stock', $manage_stock, true );
                if ( $manage_stock === 'yes' ) {
                    $stock = intval( $stock );
                    wc_update_product_stock( $post_id, $stock );
                    $stock = get_post_meta( $post_id, '_stock', true );
                } else {
                    wc_update_product_stock( $post_id, 0 );
                }
                delete_post_meta( $post_id, '_serial_numbers' );
                add_post_meta( $post_id, '_regular_price', $price, true );
                add_post_meta( $post_id, '_price', $price, true );
                add_post_meta( $post_id, '_serial_numbers', $serial_numbers, true );
                add_post_meta( $post_id, '_serial_number_pattern', $pattern, true );
                add_post_meta( $post_id, '_serial_number_random', $random, true );
            }
        }
    }

    function wp_ajax_save_serial_numbers() {
        check_ajax_referer( 'save-serial-numbers-ajax', 'save_serial_numbers_ajax' );
        $data = str_replace( '&amp;', '&', $_POST[ 'products' ] );
        $products = array();
        parse_str( $data, $products );
        $posts = value( $products, 'serial_number_posts', array() );
        $this->save_serial_number_posts( $posts );
        die();
    }

    /**
     * Displays the quick edit page
     */
    function settings_page() {
        if ( !empty( $_POST[ 'save_serial_numbers' ] ) ) {
            $this->save_serial_numbers();
        }
        print '<h1>' . __( 'Serial Numbers', 'serial-numbers' ) . '</h1>';
        print '<p>' . __( 'Please use this page to edit serial number general settings, unused serial numbers, remaining stock and product prices.', 'serial-numbers' ) . '</p>';
        print '<form id="serial-numbers-form" method="post" action="' . admin_url( 'admin.php?page=serial-numbers' ) . '">';
        print '<input type="hidden" name="save_serial_numbers" value="yes" />';
        $serial_numbers_advanced_settings = get_option( '_serial_numbers_advanced_settings', array() );
        wpshowcase_print_items(
                $serial_numbers_advanced_settings, array(
            'name' => 'serial_numbers_advanced_settings',
            'items' => array(
                'serial_numbers_name' => array(
                    'element_type' => 'input',
                    'type' => 'text',
                    'label' => __( 'Label to Display to Customer', 'serial-numbers' ) . '<br>',
                    'default' => __( 'Serial Number', 'serial-numbers' ),
                ),
            ),
                )
        );
        print '<br>';
        $serial_numbers_settings = get_option( '_serial_numbers_settings', array() );
        wpshowcase_print_html(
                $serial_numbers_settings, array(
            'name' => 'serial_numbers_settings',
            'element_type' => 'select_and_display_suboptions',
            'dropdown' => array(
                'how_serial_numbers_chosen' => array(
                    'label' => __( 'How should serial numbers be selected?', 'serial-numbers' ),
                    'element_type' => 'dropdown',
                    'default' => 'frontend_dropdowns',
                ),
            ),
            'items' => array(
                'admin_chooses' => array( 'label' => __( 'Site Administrator assigns serial numbers to orders', 'serial-numbers' ), ),
                'customer_chooses' => array( 'label' => __( 'Customer chooses serial number(s)', 'serial-numbers' ),
                    'items' => array( 'customer_chooses_title' => array( 'element_type' => 'input',
                            'label' => '<br style="clear:both;">' . __( 'Label for dropdown e.g. Please choose a serial number', 'serial-numbers' ) . '<br>',
                            'type' => 'text',
                            'default' => __( 'Please choose a serial number', 'serial-numbers' ),
                        ), ), ),
                'auto_assigned' => array( 'label' => __( 'Serial number(s) automatically assigned to orders', 'serial-numbers' ), ),
            ),
        ) );
        wpshowcase_print_html(
                $serial_numbers_settings, array(
            'name' => 'serial_numbers_settings',
            'element_type' => 'select_and_display_suboptions',
            'dropdown' => array(
                'reassign_refunded_and_canceled_and_failed_orders' => array(
                    'label' => __( 'Can the serial numbers assigned to refunded/canceled/failed orders be reassigned?', 'serial-numbers' ),
                    'element_type' => 'dropdown',
                    'default' => 'yes',
                ),
            ),
            'items' => array(
                'yes' => array( 'label' => __( 'Yes', 'serial-numbers' ), ),
                'no' => array( 'label' => __( 'No', 'serial-numbers' ), ),
            ),
        ) );
        $this->products_serial_numbers();
        print '</form>';
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
        wp_enqueue_script( 'serial-numbers-page', WP_PLUGIN_URL . '/serial-numbers/js/serial-numbers-page.js', array(
            'jquery' ), false, true );
        ob_start();
        $this->product_serial_number( 'post_id', 'serial_number_id', '' );
        $new_serial_number = $this->prepare_for_js( ob_get_clean() );
        wp_localize_script( 'serial-numbers-page', 'serial_numbers_page_settings', array(
            'new_serial_number' => $new_serial_number,
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'save_serial_numbers_ajax' => wp_create_nonce( 'save-serial-numbers-ajax' ),
        ) );
        wp_enqueue_style( 'serial-numbers-page', WP_PLUGIN_URL . '/serial-numbers/css/serial-numbers-page.css' );
    }

    /**
     * Prepares a string for adding to javascript
     */
    function prepare_for_js( $string ) {
        $string = str_replace( "'", "\'", $string );
        $string = str_replace( "\n", " ", $string );
        $string = str_replace( "\r", " ", $string );
        $string = str_replace( "
", " ", $string );
        return $string;
    }

    /**
     * Displays product and its serial numbers
     */
    function products_serial_numbers() {
        wp_nonce_field( 'serial-numbers-page-save', 'serial-numbers-page' );
        $product_query = new WP_Query( array(
            'post_type' => 'product',
            'posts_per_page' => -1,
                ) );
        while ( $product_query->have_posts() ) {
            $product_query->the_post();
            $product = wc_get_product( get_the_id() );
            if ( $product->get_type() == 'variable' ) {
                print '<div class="variable-product-serial-number-wrapper">';
                print '<h2>' . __( 'Variable Product : ', 'serial-numbers' ) .
                        '<a href="' . admin_url( 'post.php?post=' . get_the_id() . '&action=edit' ) . '">'
                        . get_the_title() . '</a></h2>';
                $this->product_settings( get_the_id() );
                $children = $product->get_children();
                if ( !empty( $children ) ) {
                    foreach ( $children as $child ) {
                        print '<div class="product-serial-number-wrapper unchanged-product">';
                        print '<input type="hidden" class="post-id" value="' . $child . '" />';
                        $variation = wc_get_product( $child );
                        print '<h3>' . $variation->get_formatted_name() . '</h3>';
                        $this->price_and_stock( $child );
                        $this->product_serial_numbers( $child );
                        print '</div>';
                    }
                }
                print '</div>';
            } else {
                print '<div class="product-serial-number-wrapper unchanged-product" style="max-width:800px;">';
                print '<input type="hidden" class="post-id" value="' . get_the_id() . '" />';
                print '<h2>' . __( 'Product : ', 'serial-numbers' ) . '<a href="' . admin_url( 'post.php?post=' . get_the_id() . '&action=edit' ) . '">'
                        . get_the_title() . '</a></h2>';
                $this->product_settings( get_the_id() );
                $this->price_and_stock( get_the_id() );
                $this->product_serial_numbers( get_the_id() );
                print '</div>';
            }
            print '<hr>';
        }
        wp_reset_postdata();
    }

    function product_settings( $post_id ) {
        $serial_numbers_settings = get_post_meta( $post_id, '_serial_numbers_settings', true );
        wpshowcase_print_html(
                $serial_numbers_settings, array(
            'name' => 'serial_numbers_settings[' . $post_id . ']',
            'element_type' => 'select_and_display_suboptions',
            'dropdown' => array(
                'how_serial_numbers_chosen' => array(
                    'label' => __( 'How should serial numbers be assigned to this product?', 'serial-numbers' ) . '<br>',
                    'element_type' => 'dropdown',
                    'default' => 'frontend_dropdowns',
                ),
            ),
            'items' => array(
                'default_settings' => array( 'label' => __( 'Use plugin settings', 'serial-numbers' ), ),
                'admin_chooses' => array( 'label' => __( 'Site Administrator assigns serial numbers to orders', 'serial-numbers' ), ),
                'customer_chooses' => array( 'label' => __( 'Customer chooses serial number(s)', 'serial-numbers' ),
                    'items' => array( 'customer_chooses_title' => array( 'element_type' => 'input',
                            'label' => '<br style="clear:both;">' . __( 'Label for dropdown e.g. Please choose a serial number', 'serial-numbers' ) . '<br>',
                            'type' => 'text',
                            'default' => __( 'Please choose a serial number', 'serial-numbers' ),
                        ), ), ),
                'auto_assigned' => array( 'label' => __( 'Serial number(s) automatically assigned to orders', 'serial-numbers' ), ),
            ),
        ) );
    }

    /**
     * Displays the price and stock of product $post_id
     */
    function price_and_stock( $post_id ) {
        $price = get_post_meta( $post_id, '_regular_price', true );
        print '<label>' . __( 'Price', 'serial-numbers' ) . '<br><input type="number" step="0.01" value="' . $price . '" name="serial_number_posts[' . $post_id . '][price]" placeholder="100.00" /></label>';

        $stock_status = get_post_meta( $post_id, '_stock_status', true );
        $options = array(
            'instock' => __( 'In stock', 'woocommerce' ),
            'outofstock' => __( 'Out of stock', 'woocommerce' )
        );
        print '<br>';
        wpshowcase_select( $options, $stock_status, __( 'Stock Status', 'serial-numbers' ) . '<br>', 'serial_number_posts[' . $post_id . '][stock_status]' );
        $manage_stock = get_post_meta( $post_id, '_manage_stock', true );
        print '<br>';
        wpshowcase_select(
                array(
            'yes' => __( 'Yes', 'serial-numbers' ),
            'no' => __( 'No', 'serial-numbers' ),
                ), $manage_stock, __( 'Manage Stock', 'serial-numbers' ) . '<br>', 'serial_number_posts[' . $post_id . '][manage_stock]', array(
            'class' => 'manage-stock', )
        );
        $stock = intval( get_post_meta( $post_id, '_stock', true ) );
        print '<br><label class="stock-wrapper">' . __( 'Stock', 'serial-numbers' ) . '<br><input type="number" step="1" value="' . $stock . '" name="serial_number_posts[' . $post_id . '][stock]" placeholder="10" /></label>';
    }

    /**
     * Displays serial numbers for $post_id
     */
    function product_serial_numbers( $post_id ) {
        $serial_numbers = get_post_meta( $post_id, '_serial_numbers', true );
        print '<br><h4>' . __( 'Serial Numbers', 'serial-numbers' ) . '</h4>';
        print '<p class="no-serial-numbers">'
                . __( 'No serial numbers have been assigned to this product', 'serial-numbers' )
                . '</p>';
        print '<div class="serial-numbers">';
        do_action( 'product_serial_numbers_before', $post_id );
        if ( !empty( $serial_numbers ) ) {
            foreach ( $serial_numbers as $id => $serial_number ) {
                $this->product_serial_number( $post_id, $id, $serial_number );
            }
        }
        do_action( 'product_serial_numbers_after', $post_id );
        print '</div><br>';
        print '<a href="#" class="add-serial-number button">' . __( 'Add New Serial Number', 'serial-numbers' ) . ' </a>';
        print '<br><h4>' . __( 'Serial Numbers Generator Pattern', 'serial-numbers' ) . '</h4>';
        print '<div class="serial-numbers-generator-pattern">';
        print '<p>'.__('If you would like to generate serial numbers to automatically be assigned to this product when this product is purchased, please enter a pattern for the serial numbers.','serial-numbers').'</p>';
        print '<p>'.__('#\'s will be replaced by numbers so ABC####D## would generate a serial number like ABC9451D37.','serial-numbers').'</p>';      
        print '<p>'.__('Y, m and d within {} will be replaced with the year, month and date (so {Ymd} would be replaced with 20180327 on March 27th 2018).','serial-numbers').'</p>';      
        $pattern = get_post_meta($post_id,'_serial_number_pattern',true);
        if($pattern===false) {
            $pattern = '';
        }
        print '<input type="text" placeholder="SERIAL################" name="serial_number_posts[' . $post_id . '][pattern]" value="' . $pattern . '" />';
        
        $random = get_post_meta($post_id,'_serial_number_random',true);
        $random_array = array($random=>' selected ');
        print '<p>' . __( 'How would you like to generate serial numbers?', 'serial-numbers' ) . '</p>';
        print '<select name="serial_number_posts[' . $post_id . '][random]">';
        print '<option value="yes" '.value($random_array,'yes').'>' . __( 'Generate random serial numbers', 'serial-numbers' ) . '</option>';
        print '<option value="no" '.value($random_array,'no').'>' . __( 'Generate serial numbers sequentially', 'serial-numbers' ) . '</option>';
        print '</select><br style="clear:both;">';
        
        print '</div>';
        print '<input type="submit" style="float:right;" value="' . __( 'Save Changes to All Products', 'serial-numbers' ) . '"/>';
    }

    /**
     * An array of available serial numbers for $post_id
     */
    function get_available_serial_numbers( $post_id ) {
        $used_serial_numbers = $this->used_serial_numbers();
        $serial_numbers = get_post_meta( $post_id, '_serial_numbers', true );
        $available_serial_numbers = array();
        if ( empty( $serial_numbers ) ) {
            return array();
        }
        foreach ( $serial_numbers as $serial_number ) {
            if ( empty( $used_serial_numbers[ $post_id ] ) || empty( $used_serial_numbers[ $post_id ][ $serial_number ] ) ) {
                $available_serial_numbers[ $serial_number ] = $serial_number;
            }
        }
        return $available_serial_numbers;
    }

    /**
     * Displays data for serial number
     */
    function product_serial_number( $post_id, $id, $serial_number ) {
        $used_serial_numbers = $this->used_serial_numbers();
        if ( !empty( $used_serial_numbers[ $post_id ] ) && !empty(
                        $used_serial_numbers[ $post_id ][ $serial_number ] ) ) {
            print '<div class="serial-number"><label for="serial_number_posts[' . $post_id . '][serial_number][' . $id . ']">' . __( 'Serial Number: ', 'serial-numbers' ) .
                    '</label><input type="text" disabled="disabled" value="' . $serial_number . '" placeholder="12345678901234" />'
                    . '<input type="hidden" value="' . $serial_number . '" name="serial_number_posts[' . $post_id . '][serial_number][' . $id . ']" />';
            print '<input type="hidden" class="serial-number-id" value="' . $id . '"/>';
            $order_id = $used_serial_numbers[ $post_id ][ $serial_number ];
            print ' <a href="' . admin_url( 'post.php?post=' . $order_id . '&action=edit"' ) . '">';
            _e( 'Used in Order ', 'serial-numbers' );
            print $order_id . '</a>';
            do_action( 'product_serial_number_used', $post_id, $id, $serial_number );
            print '</div>';
        } else {
            print '<div class="serial-number"><label for="serial_number_posts[' . $post_id . '][serial_number][' . $id . ']">' . __( 'Serial Number: ', 'serial-numbers' ) .
                    '</label><input type="text" value="' . $serial_number . '" name="serial_number_posts[' . $post_id . '][serial_number][' . $id . ']" placeholder="12345678901234" />';
            print '<a href="#" class="button remove-serial-number">';
            _e( 'Remove', 'serial-numbers' );
            print '</a>';
            print '<input type="hidden" class="serial-number-id" value="' . $id . '"/>';
            do_action( 'product_serial_number_unused', $post_id, $id, $serial_number );
            print '</div>';
        }
    }

    function how_serial_numbers_chosen( $post_id ) {
        $serial_numbers_settings = get_post_meta( $post_id, '_serial_numbers_settings', true );
        $how_serial_numbers_chosen = 'admin_chooses';
        if ( !empty( $serial_numbers_settings ) ) {
            $how_serial_numbers_chosen = value( $serial_numbers_settings, 'how_serial_numbers_chosen' );
            if ( $how_serial_numbers_chosen == 'default_settings' ) {
                $serial_numbers_default_settings = get_option( '_serial_numbers_settings', array() );
                $how_serial_numbers_chosen = value( $serial_numbers_default_settings, 'how_serial_numbers_chosen', 'admin_chooses' );
            }
        }
        return $how_serial_numbers_chosen;
    }

}

$serial_numbers_page = new Serial_Numbers_Page();
