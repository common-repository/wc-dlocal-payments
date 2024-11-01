<?php
/**
 * Helper functions for dLocal integration
 *
 * @link       https://emanuel-fernandez.github.io
 * @since      1.0.0
 *
 * @package    Woo_Dlocal
 * @subpackage Woo_Dlocal/includes
 */
class Woo_Dlocal_Payments_Helper {

    public const COUNTRY_CURRENCIES = [
        'AR' => 'ARS',
        'BD' => 'BDT',
        'BO' => 'BOB',
        'BR' => 'BRL',
        'CM' => 'XAF',
        'CL' => 'CLP',
        'CN' => 'CNY',
        'CO' => 'COP',
        'CR' => 'CRC',
        'DO' => 'DOP',
        'EC' => 'USD',
        'SV' => 'USD',
        'EG' => 'EGP',
        'GH' => 'GHS',
        'GT' => 'GTQ',
        'HN' => 'HNL',
        'IN' => 'INR',
        'ID' => 'IDR',
        'CI' => 'CFA',
        'JP' => 'YEN',
        'KE' => 'KES',
        'MY' => 'MYR',
        'MX' => 'MXN',
        'MA' => 'MAD',
        'NI' => 'NIO',
        'NG' => 'NGN',
        'PK' => 'PKR',
        'PA' => 'USD',
        'PY' => 'PYG',
        'PE' => 'PEN',
        'PH' => 'PHP',
        'RW' => 'RWF',
        'SN' => 'XOF',
        'ZA' => 'ZAR',
        'TZ' => 'TZS',
        'TH' => 'THB',
        'TR' => 'TRY',
        'UG' => 'UGX',
        'UY' => 'UYU',
        'VN' => 'VND',
        'ZM' => 'ZMW'
    ];

    private static function onlyDigits($str) {
        $x = preg_match('/^\d+$/', $str);
        return $x;
    }

    private static function onlyDigitsAndChars($str) {
        $x = preg_match('/^(\d|[a-zA-Z])+$/', $str);
        return $x;
    }

    public static function validateID($countryISOCode, $ID) {
        switch ($countryISOCode) {
            case 'AR':
                return ((strlen($ID) >= 7 && strlen($ID) <= 9) || strlen($ID) == 11);
            case 'BD':
            case 'SN':
                return (strlen($ID) >= 13 && strlen($ID) <= 17);
            case 'BO':
            case 'EC':
            case 'CN':
            case 'TR':
            case 'MA':
            case 'PY':
                return strlen($ID) >= 5 && strlen($ID) <= 20;
            case 'BR':
                return strlen($ID) >= 11 && strlen($ID) <= 14;
            case 'CM':
            case 'KE':
                return strlen($ID) == 8;
            case 'CL':
            case 'PE':
                return strlen($ID) == 8 || strlen($ID) == 9;
            case 'CO':
                return strlen($ID) >= 6 && strlen($ID) <= 10;
            case 'CR':
            case 'SV':
            case 'ZM':
                return strlen($ID) == 9;
            case 'DO':
            case 'NG':
                return strlen($ID) == 11;
            case 'EG':
            case 'NI':
                return strlen($ID) == 14;
            case 'GH':
            case 'GT':
            case 'HN':
            case 'TH':
            case 'PK':
            case 'ZA':
                return strlen($ID) == 13;
            case 'IN':
                return strlen($ID) == 10;
            case 'ID':
            case 'RW':
                return strlen($ID) == 16;
            case 'JP':
            case 'MY':
            case 'PH':
                return strlen($ID) == 12;
            case 'MX':
                return strlen($ID) >= 10 && strlen($ID) <= 18;
            case 'TZ':
                return strlen($ID) == 20;
            case 'UG':
                return strlen($ID) >= 14 && strlen($ID) <= 17;
            case 'UY':
                return strlen($ID) >= 6 && strlen($ID) <= 8 || strlen($ID) == 12;
            case 'VN':
                return strlen($ID) >= 9 && strlen($ID) <= 12;
            default:
                return true;
        }
    }

    public static function get_ip() {
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = '127.0.0.1';

        return $ipaddress;
    }

    public static function dlocal_payment_body(WC_Order $order, $send_main_currency) {
        $order_id = $order->get_id();
        $payer_id = get_post_meta( $order_id, '_billing_document', true )  ? get_post_meta( $order_id, '_billing_document', true )  : get_post_meta( $order_id, '_shipping_document', true );
        $payer = array (
            'name'      => $order->get_billing_first_name() ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
            'email'     => $order->get_billing_email(),
            'ip'        => self::get_ip(),
        );
        if (!empty($payer_id)) {
            $payer['document'] = $payer_id;
        }
        $country = $order->get_billing_country();


        if ($send_main_currency === true) {
            $currency = get_woocommerce_currency();
        } else {
            $currency = self::COUNTRY_CURRENCIES[$country];
        }

        $website_url = home_url();
        $additional_risk_data = [
            'submerchant' => [
                'website' => $website_url
            ]
        ];
        $params = array (
            'amount'                => floatval(number_format($order->get_total(), 2, '.', '')),
            'currency'              => $currency,
            'country'               => $country,
            'payer'                 => $payer,
            'order_id'              => $order_id . '_' . uniqid(),
            'notification_url'      => trailingslashit(get_bloginfo( 'url' )) . trailingslashit('wc-api') . strtolower('Woo_Dlocal_Offline_Gateway'),
            'description'           => $website_url,
            'additional_risk_data'  => $additional_risk_data
        );

        $payment_method = sanitize_text_field($_POST['redirect-payment-method']);
        
        if (empty($payment_method)) {
            throw new \Exception('Empty redirect payment method');
        }

        $params['payment_method_id'] = $payment_method;
        $params['payment_method_flow'] = 'REDIRECT';
        $params['callback_url'] = $order->get_checkout_order_received_url();

        return $params;
    }

}