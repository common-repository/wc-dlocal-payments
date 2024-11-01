<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://emanuel-fernandez.github.io
 * @since      1.0.0
 *
 * @package    Woo_Dlocal
 * @subpackage Woo_Dlocal/admin/partials
 */

return array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'wc-dlocal-payments'),
        'type' => 'checkbox',
        'label' => __('Enable', 'wc-dlocal-payments'),
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Checkout title', 'wc-dlocal-payments'),
        'type' => 'text',
        'description' => __('Title during checkout', 'wc-dlocal-payments'),
        'default' => __('Pay with cash', 'wc-dlocal-payments'),
    ),
    'show_identification_field' => array (
        'title' => __('Show identification field in checkout page'),
        'description' => __('Display and validate the client ID. It is usually required by dLocal. Before disabling ask dLocal', 'wc-dlocal-payments'),
        'type' => 'checkbox',
        'default' => 'yes'
    ),
    'send_main_currency' => array(
        'title' => __('Send payments using the WooCommerce main currency', 'wc-dlocal-payments'),
        'label' => __('Enable', 'wc-dlocal-payments'),
        'type' => 'checkbox',
        'description' => __('If this option is ticked, all payments will be sent to dLocal using the main WooCommerce currency.', 'wc-dlocal-payments') 
            . __('If this option is not ticked, the currency is assumed to be the corresponding to the selected billing country during checkout.', 'wc-dlocal-payments')
            . __('This options should be ticked if you are not using a multicurrency plugin that adapts prices depending on the client selected billing country', 'wc-dlocal-payments'),
        'default' => false,
        'desc_tip' => false,
    ),
    'environment' => array(
        'title' => __('Environment', 'wc-dlocal-payments'),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'desc_tip' => true,
        'default' => '0',
        'options'     => array(
            '0'    => 'Sandbox',
            '1' => 'Production',
        )
    ),
    'sandbox_credentials' => array(
        'title'       => __('Sandbox credentials', 'wc-dlocal-payments'),
        'type'        => 'title'
    ),
    'x_login_sandbox' => array(
        'title' => 'x_login',
        'type'  => 'text',
        'desc_tip' => true
    ),
    'x_trans_key_sandbox' => array(
        'title' => 'x_trans_key',
        'type'  => 'text',
        'desc_tip' => true
    ),
    'secret_key_sandbox' => array(
        'title' => __('Secret Key', 'wc-dlocal-payments'),
        'type'  => 'password',
        'desc_tip' => true
    ),
    'credentials' => array(
        'title'       => __('Production credentials', 'wc-dlocal-payments'),
        'type'        => 'title'
    ),
    'x_login' => array(
        'title' => 'x_login',
        'type'  => 'text',
        'desc_tip' => true
    ),
    'x_trans_key' => array(
        'title' => 'x_trans_key',
        'type'  => 'text',
        'desc_tip' => true
    ),
    'secret_key' => array(
        'title' => __('Secret Key', 'wc-dlocal-payments'),
        'type'  => 'password',
        'desc_tip' => true
    )
)
?>

