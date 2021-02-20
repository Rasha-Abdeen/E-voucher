<?php
class WC_billing {
    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function woohide_init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::woohide_add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_billing', __CLASS__ . '::woohide_settings_tab' );
        add_action( 'woocommerce_update_options_billing', __CLASS__ . '::woohide_update_settings' );
    }
    
    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function woohide_add_settings_tab( $settings_tabs ) {
        $settings_tabs['billing'] = __( 'Billing', 'woocommerce-settings-tab-billing' );
        return $settings_tabs;
    }
    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::woohide_get_settings()
     */
    public static function woohide_settings_tab() {
        woocommerce_admin_fields( self::woohide_get_settings() );
    }
    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::woohide_get_settings()
     */
    public static function woohide_update_settings() {
        woocommerce_update_options( self::woohide_get_settings() );
    }
    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function woohide_get_settings() {
        $settings = array(

            'section_title' => array(
                'name'     => __( 'Hide Billing Fields', 'woocommerce-settings-tab-billing' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'WC_billing_section_title'
            ),
            'billing_address_required' => array(
                'name'     => __( 'Shipping is Required', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Fields in the Checkout Flow even if shipping is required.', 'text-domain' ),
                'id'       => 'wcbilling_hide_shippingrequired',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Even if Shipping is required', 'text-domain' ),
            ),
            'shipping_address_not_required' => array(
                'name'     => __( 'Unset Shipping if order value is Zero', 'text-domain' ),
                'desc_tip' => __( 'Unset Shipping if order value is Zero', 'text-domain' ),
                'id'       => 'wcbilling_hide_shippingnot',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Unset the requirement status of all shipping fields ', 'text-domain' ),
            ),
            'shipping_address_not_required_amount' => array(
                'name'     => __( 'Unset Shipping if Greater Than Zero', 'text-domain' ),
                'desc_tip' => __( 'Unset Shipping if Greater Than Zero', 'text-domain' ),
                'id'       => 'wcbilling_hide_shippingnot_amount',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Unset the requirement status of all shipping fields ', 'text-domain' ),
            ),
            'billing_zero_total' => array(
                'name'     => __( 'Zero Order Total', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Fields in the Checkout Flow even if the order total is not zero.', 'text-domain' ),
                'id'       => 'wcbilling_hide_zerototal',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Even if Order Total is not Zero', 'text-domain' ),
            ),
            'billing_fn' => array(
                'name'     => __( 'Billing First Name', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing First Name Field in the Checkout Flow. WARNING: this may be required for account creation.', 'text-domain' ),
                'id'       => 'wcbilling_hide_fn',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing First Name', 'text-domain' ),
            ),
            'billing_ln' => array(
                'name'     => __( 'Billing Last Name', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Last Name Field in the Checkout Flow. WARNING: this may be required for account creation.', 'text-domain' ),
                'id'       => 'wcbilling_hide_ln',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing Last Name', 'text-domain' ),
            ),
            'billing_email' => array(
                'name'     => __( 'Billing Email Address', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Email Address Field in the Checkout Flow. WARNING: this may be required for account creation.', 'text-domain' ),
                'id'       => 'wcbilling_hide_email',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing Email Address', 'text-domain' ),
            ),
            'billing_company' => array(
                'name'     => __( 'Billing Company', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Company Field in the Checkout Flow.', 'text-domain' ),
                'id'       => 'wcbilling_hide_company',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing Company', 'text-domain' ),
            ),
            'billing_country' => array(
                'name'     => __( 'Billing Country', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Country Field in the Checkout Flow.', 'text-domain' ),
                'id'       => 'wcbilling_hide_country',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing Country', 'text-domain' ),
            ),
             'billing_address1' => array(
                'name'     => __( 'Billing Address Line 1', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Address Line 1 Field in the Checkout Flow.', 'text-domain' ),
                'id'       => 'wcbilling_hide_address1',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing Address Line 1', 'text-domain' ),
            ),
            'billing_address2' => array(
                'name'     => __( 'Billing Address Line 2', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Address Line 2 Field in the Checkout Flow.', 'text-domain' ),
                'id'       => 'wcbilling_hide_address2',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing Address Line 2', 'text-domain' ),
            ),
            'billing_city' => array(
                'name'     => __( 'Billing City', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing City Field in the Checkout Flow.', 'text-domain' ),
                'id'       => 'wcbilling_hide_city',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing City', 'text-domain' ),
            ),
            'billing_state' => array(
                'name'     => __( 'Billing State', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing State Field in the Checkout Flow.', 'text-domain' ),
                'id'       => 'wcbilling_hide_state',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing State', 'text-domain' ),
            ),
            'billing_postcode' => array(
                'name'     => __( 'Billing Postal Code', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Costal Code Field in the Checkout Flow.', 'text-domain' ),
                'id'       => 'wcbilling_hide_postcode',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing Postal Code', 'text-domain' ),
            ),
            'billing_phone' => array(
                'name'     => __( 'Billing Phone Number', 'text-domain' ),
                'desc_tip' => __( 'Hide Billing Phone Number Field in the Checkout Flow.', 'text-domain' ),
                'id'       => 'wcbilling_hide_phone',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Hide Billing Phone Number', 'text-domain' ),
            ),
            'section_end' => array(
                 'type' => 'sectionend',
                 'id' => 'WC_billing_section_end'
            )
        );
        return apply_filters( 'WC_billing_settings', $settings );
    }
}
WC_billing::woohide_init();
?>