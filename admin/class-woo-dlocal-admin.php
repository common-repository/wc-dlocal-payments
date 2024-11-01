<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Woo_Dlocal
 * @subpackage Woo_Dlocal/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Dlocal
 * @subpackage Woo_Dlocal/admin
 * @author     Emanuel Fernandez <emanuel.9494@outlook.com>
 */
class Woo_Dlocal_Admin {

	private $logger;

	private $plugin_name;

	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->logger = new WC_Logger();
	}

	public function display_dlocal_fields($order) {
		try {
			$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$payment_method = $order->get_payment_method();
			if (in_array($payment_method, [WOO_DLOCAL_OFFLINE_ID] )) {
				echo '<p><strong> dLocal id :</strong> ' . esc_html(get_post_meta( $order_id, 'dlocal_id', true )) . '</p>';
			}
		} catch (\Exception $ex) {
			woo_dlocal_log($this->logger, 'error', $ex->getMessage());
		}
		
	}

}
