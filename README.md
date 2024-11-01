# Payments via dLocal for WooCommerce
Contributors: emanuel94
Tags: checkout, payments, dlocal, woocommerce, payment gateway
Requires at least: 4.0
Tested up to: 6.4.3
WC tested up to: 8.5.2
License: GPLv3
Stable tag: 1.0.8

## Description
**For support, bugs, changes, Smart Fields (Card Payments) or other projects please contact me.** 
Plugin for WooCommerce. It provides alternative payments through dLocal API. The payment can be processed using the billing country selected in the checkout by the client. It is recommended to use a multicurrency plugin capable or altering the order amount depending on the billing country selected by the client. The Plugin configuration is located in WooCommerce->Settings->Payments. To use the dLocal payments, you should first contact dLocal to obtain credentials.
Information used for creating the payment:
- Billing name
- Billing country
- Billing email

## How to use
1) Install
2) Configure parameters in the admin dashboard in WooCommerce->Settings->Payments
If payments are not working, try checking the logs in WooCommerce->Status->Logs, then select a woo-dlocal log to view in the dropdown. 

## Functionalities
* Offline payments.
* Client ID input on checkout page.
* Logging.
* Payment notification listener.

## Notes
 - Payment notification listener is: `https://example.com/wc-api/woo_dlocal_offline_gateway`

## Author
Emanuel Fernández - emanuel.9494@outlook.com - https://emanuel-fernandez.github.io

## Links
- Documentation: https://docs.dlocal.com/
- https://dashboard.dlocal.com/
- https://merchant.dlocal.com/
