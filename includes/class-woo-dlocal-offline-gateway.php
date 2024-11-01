<?php

/**
 * Woocommerce offline methods dLocal gateway
 *
 * @link       https://emanuel-fernandez.github.io
 * @since      1.0.0
 *
 * @package    Woo_Dlocal
 * @subpackage Woo_Dlocal/includes
 */
class Woo_Dlocal_Offline_Gateway extends WC_Payment_Gateway {

    private $logger;
    private $client;

	public function __construct() {

		$this->id = WOO_DLOCAL_OFFLINE_ID;
		$this->method_title = 'Dlocal - Alternative Payments Gateway';
        $this->method_description = 'Pay with dLocal alternative payment methods';
        $this->title = $this->get_option('title');
        $this->has_fields = true;
        $this->supports = ['products'];

		$this->init_form_fields();
		$this->init_settings();

        $is_production = (bool)$this->get_option('environment');

        if ($is_production == true) {
            $x_login = $this->get_option('x_login');
            $x_trans_key = $this->get_option('x_trans_key');
            $secret_key = $this->get_option('secret_key');
        } else {
            $x_login = $this->get_option('x_login_sandbox');
            $x_trans_key = $this->get_option('x_trans_key_sandbox');
            $secret_key = $this->get_option('secret_key_sandbox');
        }

        require_once (plugin_dir_path(__FILE__) . 'class-woo-dlocal-client.php' );
        require_once (plugin_dir_path(__FILE__) . 'class-woo-dlocal-helper.php' );

        $this->logger = new WC_Logger();
        $this->client = new Woo_Dlocal_Client($is_production, $x_login, $x_trans_key, $secret_key);

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        add_action('woocommerce_api_'.strtolower(get_class($this)), array($this, 'confirmation_ipn'));

        add_action('woocommerce_thankyou_'.$this->id, array('Woo_Dlocal_Offline_Gateway', 'redirect_post_request'));

    }
    
    public static function redirect_post_request($order_id) {
        $order = new WC_Order($order_id);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $url = $order->get_checkout_order_received_url();
            header('Location: '.$url);
            exit;
        }
    }

	public function init_form_fields() {

		$this->form_fields = include(plugin_dir_path(__FILE__) . '../admin/partials/woo-dlocal-admin-offline-settings.php');
    }


    function process_payment( $order_id ) {
        
        try {
            $order = new WC_Order($order_id);

            $params = Woo_Dlocal_Payments_Helper::dlocal_payment_body($order, $this->get_option('send_main_currency') === 'yes');

            $response = $this->client->pay($params);

            if (! in_array($response->status_code, ['100', '101']) ) {
                $order->add_order_note("Status Code: " . $response->status_code . ' ' . ($response->status_detail ?? ''));
                throw new \Exception("Status Code: " . $response->status_code . ' ' . ($response->status_detail ?? '') );
            }

            update_post_meta($order_id, 'dlocal_id', sanitize_text_field($response->id));

            if (! isset($response->redirect_url) ) {
                throw new \Exception("No redirect: " . json_encode($response) );
            }

            wc_reduce_stock_levels($order_id);
            WC()->cart->empty_cart();
            return [
                'result'    => 'success',
                'redirect'  => $response->redirect_url
            ];

        } catch(\Exception $exception) {
            wc_add_notice( __('Error during payment', 'wc-dlocal-payments'), 'error' );
            woo_dlocal_log($this->logger, 'error', 'Offline payment error, Order: ' . $order_id . ' - ' . $exception->getMessage());
        }
       
    }

    public function validate_fields() {
        if ($this->get_option('show_identification_field') == 'yes') {
            $ID = WC()->checkout->get_value('billing_document');
            if (empty($ID)) {
                wc_add_notice( __('For this payment gateway, identification field is required', 'wc-dlocal-payments'), 'error');
                return false;
            }
            if (empty($ID) || !Woo_Dlocal_Payments_Helper::validateID(WC()->customer->get_shipping_country(), $ID)) {
                wc_add_notice( __('Invalid identification', 'wc-dlocal-payments'), 'error');
                return false;
            }
        }
        
        return true;
    }

   
    public function payment_fields() {
        $countryISO = WC()->customer->get_shipping_country();
        try {
            $response = $this->client->get_payment_methods($countryISO);
        } catch (\Exception $exception) {
            woo_dlocal_log($this->logger, 'error', $exception->getMessage());
            return;
        }
        if ( $description = $this->get_description() ) {
            echo wp_kses_post( wpautop( wptexturize( $description ) ) );
        }

        $html = '<div class="woo-dlocal-payment-wrapper" >';
        $html .= '<label class="woo-dlocal-payment-label" for="redirect-payment-method">' . 
            esc_html__('Payment method', 'wc-dlocal-payments') . 
        '</label>';


        $html .= '<select class="woo-dlocal-payment-select" name="redirect-payment-method" id="redirect-payment-method" >';
        if ($response) {
            foreach($response as $method) {
                if ($method->type != 'CARD') {
                    $html .= '<option class="woo-dlocal-payment-option" value="' . 
                        esc_attr($method->id) .
                        '">' . 
                        esc_html($method->name) .
                        '</option>';
                }
            }
        }
        $html .= '</select>';
        foreach($response as $method) {
            if ($method->type != 'CARD') {
                $html .= '<img style="max-height: 40px;" class="woo-dlocal-payment-img" src="' .
                    esc_html($method->logo) . 
                    '" />'; 
            }
        }
        $html .= '</div>';
        echo $html;
    }

    public function confirmation_ipn() {

        $json = file_get_contents('php://input');

        $params = json_decode($json, true);

        if (!isset($params['id']) || !isset($params['order_id'])) {
            woo_dlocal_log($this->logger, 'error', 'Notification - Bad Request: ' . $json);
            header("HTTP/1.1 400 Bad Request");
            exit;
        }

        try {
            $order_id = explode('_', $params['order_id']);
            $order_id = $order_id[0];
            $order = wc_get_order($order_id);

            if (empty($order)) {
                woo_dlocal_log($this->logger, 'error', "Notification - Order not found: $order_id");
                header("HTTP/1.1 500 Internal Server Error");
                exit;
            }

            if (! in_array($order->get_payment_method(), [WOO_DLOCAL_OFFLINE_ID] )) {
                woo_dlocal_log($this->logger, 'error', "Notification - Order $order_id, invalid payment method");
                header("HTTP/1.1 500 Internal Server Error");
                exit;
            }

            if ($order->is_paid()) {
                woo_dlocal_log($this->logger, 'info', "Notification - Order $order_id already paid");
                header("HTTP/1.1 200 OK");
                exit;
            }

            $payment_id = $params['id'];
            $response = $this->client->get_payment_status($payment_id);
            
            if ($response->status_code === '200') {
                $order->payment_complete($payment_id);
            } elseif ($response->status_code !== '100') {
                $order->update_status('failed');
                $order->add_order_note("dLocal response status code: " . $response->status_code . ", Info: " . ($response->status_detail ?? '<no info>'));
            }

            woo_dlocal_log($this->logger, 'info', 'Notification - Order: '.$order_id . ', Code: '.$response->status_code);

            header("HTTP/1.1 200 OK");
            exit;

        } catch (\Exception $exception) {
            woo_dlocal_log($this->logger, 'error', 'Notification - ' . $exception->getMessage());
            header("HTTP/1.1 500 Internal Error");
            exit;
        }
    }

}