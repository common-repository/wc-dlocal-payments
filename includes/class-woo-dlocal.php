<?php

/**
 * @link       https://emanuel-fernandez.github.io
 * @since      1.0.0
 *
 * @package    Woo_Dlocal
 * @subpackage Woo_Dlocal/includes
 */

class Woo_Dlocal {


	protected $loader;

	protected $plugin_name;

	protected $version;

	protected $logger;

	public function __construct() {
		if ( defined( 'WOO_DLOCAL_VERSION' ) ) {
			$this->version = WOO_DLOCAL_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-dlocal';

		$this->load_dependencies();
		$this->define_public_hooks();
		$this->define_admin_hooks();
		$this->logger = new WC_Logger();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-dlocal-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-dlocal-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-dlocal-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-dlocal-offline-gateway.php';

		$this->loader = new Woo_Dlocal_Loader();
	}

	private function define_admin_hooks() {
		$plugin_admin = new Woo_Dlocal_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'woocommerce_admin_order_data_after_billing_address', $plugin_admin, 'display_dlocal_fields');
	}

	private function define_public_hooks() {
		$plugin_public = new Woo_Dlocal_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'woocommerce_payment_gateways', $plugin_public, 'woocommerce_dlocal_add_gateways' );
		$this->loader->add_filter( 'woocommerce_checkout_fields' , $plugin_public, 'add_client_document_field');
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_custom_styles' );
	}

	public function run() {
		$this->loader->run();
	}


	public function get_plugin_name() {
		return $this->plugin_name;
	}


	public function get_loader() {
		return $this->loader;
	}


	public function get_version() {
		return $this->version;
	}

}
