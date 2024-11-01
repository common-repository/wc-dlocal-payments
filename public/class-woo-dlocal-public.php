<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://emanuel-fernandez.github.io
 * @since      1.0.0
 *
 * @package    Woo_Dlocal
 * @subpackage Woo_Dlocal/public
 */

class Woo_Dlocal_Public {

	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

    function enqueue_custom_styles() {
        wp_enqueue_style('woo-dlocal-styles', plugin_dir_url( __FILE__ ) . 'css/woo-dlocal-payment.css', []);
    }

	public function woocommerce_dlocal_add_gateways($methods) {
		$methods[] = 'Woo_Dlocal_Offline_Gateway';
        return $methods;
    }

	public function add_client_document_field( $fields ) {
        try {
            $payment_gateways = WC()->payment_gateways;
            if (!empty($payment_gateways)) {
                $payment_gateways = $payment_gateways->payment_gateways();
                if (!empty($payment_gateways) && array_key_exists(WOO_DLOCAL_OFFLINE_ID, $payment_gateways)) {
                    $gateway = $payment_gateways[WOO_DLOCAL_OFFLINE_ID];
                    if (!empty($gateway) && $gateway->get_option('show_identification_field') == 'yes') {
                        if (!isset($fields['billing']['billing_document'])) {
                            $fields['billing']['billing_document'] = [
                                'type'      => 'text',
                                'label'     => __('Identification', 'wc-dlocal-payments'),
                                'required'  => false,
                                'description' => __('Only numbers and letters', 'wc-dlocal-payments')
                            ];
                        }
                    }
                }
            }
        } finally {
            return $fields;
        }
    }

}
