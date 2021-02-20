=== WooCommerce Hide Billing Fields ===

Author: Zachary A. Martz
Contributors: zamartz
Donate link: http://bt.zamartz.com/WooHidBill
Tags: woocommerce, billing, hide, zero amount, zamartz
Requires at least: 4.0.0
Tested up to: 5.2.2
Requires WooCommerce at least: 2.5.0
Tested WooCommerce up to: 3.7.0
Stable Tag: trunk

== Description ==

Allows the ability to disable billing fields in WooCommerce checkout on Wordpess. Includes the options to hide fields only if order value is under $0.01. Standard fallbacks to show if shipping is required but also has the ability to override this functionality and always hide.

== Installation ==

* Maual Upload to Server - the entire ‘woocommerce-zero-billing-fields’ folder to the '/wp-content/plugins/' directory 
* Manual Upload throug Worpdress - Install by droping .zip throug Plugins -> Add New -> Upload Plugin -> Select File or
* Auto through Wordpress Plugin Directory

== Activation ==

1. Install and Activate Plugin through the 'Plugins' menu in WordPress
1. Goto Settings in  YourSiteDomain/wp-admin/admin.php?page=wc-settings&tab=billing
1. Free - Use Select Option and Save

== Frequently Asked Questions ==

= Does this hide billing fields when the purchase price is under $0.00 =

Yes, but billing fields can also be hidden for any amount

= Can I hide billing fields even if shipping is required =

Yes, but the default is to show billing fileds when shipping is required.

= My customers are unable to access their account after an order? =

For account creation Billing First Name, Billing Last Name, and Billing E-mail address may be required for some setups.

== Screenshots ==

1. Bumber screenshot- for WooCommerce Hide Billing Fields `/assets/screenshot-1.png`
2. Cart - Shown with a $0.00 product added `/assets/screenshot-2.png`
3. Checkout - Proceeede from cart to checkout with $0.00 item added and billing fields hidden `/assets/screenshot-3.png`
4. Admin - Option Selection in WooCommerce Settings to control how and what billing fields are shown `/assets/screenshot-4.png`

== Changelog ==

= 1.0 =

* Original Upload

= 1.0.1 =

* Upload Error Fix

= 1.0.2 =

* Upadated Versioning

= 1.0.3 =

* Upadated URL Link

= 1.1.0 =

* Added more options to unset shipping fields along with billing either at zero value or in general, however this is an all or nothing switch for shipping values

== Upgrade Notice ==

Original Version No Upfrade Required

== Buy Updgrade ==

View More infomation at  = [WooCommerce Hide Billing Fields](hhttps://zamartz.com/product/woocommerce-hide-billing-fields/)
