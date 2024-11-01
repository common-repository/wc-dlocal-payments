<?php

/**
 * Client for dLocal http communication
 *
 * @link       https://emanuel-fernandez.github.io
 * @since      1.0.0
 *
 * @package    Woo_Dlocal
 * @subpackage Woo_Dlocal/includes
 */
class Woo_Dlocal_Client {

    const BASE_URL = "https://api.dlocal.com/";
    const BASE_URL_SANDBOX = "https://sandbox.dlocal.com/";

    private $base_url;
    private $x_login;
    private $x_trans_key;
    private $secret_key;
    private $is_production;

    public function __construct($is_production, $x_login, $x_trans_key, $secret_key) {
        $this->is_production = $is_production;
        $this->x_login = $x_login;
        $this->x_trans_key = $x_trans_key;
        $this->secret_key = $secret_key;

        if ($this->is_production) {
            $this->base_url = self::BASE_URL;
        } else {
            $this->base_url = self::BASE_URL_SANDBOX;
        }
    }

    private function handle_http($method, $url, $params = []) {
        $error_msg = '';
        $response = '';

        $payload = [
            'headers'   => $this->create_headers($params),
            'body'      => (empty($params) ?  [] : json_encode($params)),
            'timeout'   => 30
        ];

        if ($method == 'GET') {
            $response = wp_remote_get($url, $payload);
        } elseif ($method == 'POST') {
            $response = wp_remote_post($url, $payload);
        } elseif ($method == 'DELETE') {
            $payload['method'] = 'DELETE';
            $response = wp_remote_request($url, $payload);
        }

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $body_decoded = json_decode($body);

        if ($http_code != 200) {
            $error_msg = "HTTP CODE: $http_code";

            if (!empty($body_decoded) && isset($body_decoded->message)) {
                $error_msg .= ' - ' . $body_decoded->message;
            }
            $error_msg .= ' - ' . json_encode($params);

            throw new \Exception($error_msg);
        }

        return $body_decoded;
        
    }

    public function pay(array $params) {
        try {
            return $this->handle_http('POST', $this->base_url . 'payments', $params);
        } catch (\Exception $ex) {
            throw new \Exception('Payment error: ' . $ex->getMessage());
        }
    }

    public function get_payment_status($paymentId) {
        try {
            return $this->handle_http('GET', $this->base_url . "payments/$paymentId/status");
        } catch (\Exception $ex) {
            throw new \Exception('Payment status error: ' . $ex->getMessage());
        }
    }

    public function get_payment_methods($countryISOCode) {

        try {
            return $this->handle_http('GET', $this->base_url . "payments-methods?country=$countryISOCode");
        } catch (\Exception $ex) {
            throw new \Exception('Get payment methods error: ' . $ex->getMessage());
        }
    }

    private function generate_signature($xDate, array $body = []) {
        $data = $this->x_login . $xDate;
        $data .= empty($body) ? '' : json_encode($body);
        return hash_hmac("sha256", $data, $this->secret_key);
    }

    private function create_headers($params) {
        $xDate = date('Y-m-d\TH:i:s.u\Z');
        return [
            "X-Date" => $xDate,
            "X-Login" => $this->x_login,
            "X-Trans-Key" => $this->x_trans_key,
            "Authorization" => "V2-HMAC-SHA256, Signature: " . $this->generate_signature($xDate, $params),
            "Content-Type" => "application/json"
        ];
    }
}