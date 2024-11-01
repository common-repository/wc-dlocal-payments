<?php

/**
 *
 * @link              https://emanuel-fernandez.github.io
 * @since             1.0.0
 * @package           Woo_Dlocal
 *
 * @wordpress-plugin
 * Plugin Name:       Payments via dLocal for WooCommerce
 * Description:       Provide dLocal checkout methods for you WooCommerce site.
 * Version:           1.0.8
 * Author:            Emanuel Fernandez
 * Author URI:        https://emanuel-fernandez.github.io
 * Text Domain:  	  wc-dlocal-payments
 * Domain Path:  	  /languages/
 * WC tested up to:   8.5.2
 * License: 		  GPL v3 or later
 *
 * LICENSE
 * This file is part of Payments via dLocal for WooCommerce.
 *
 * Payments via dLocal for WooCommerce is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WOO_DLOCAL_VERSION', '1.0.8' );
define( 'WOO_DLOCAL_NAME', 'woo-dlocal' );
define( 'WOO_DLOCAL_FULL_NAME', WOO_DLOCAL_NAME.'-'.WOO_DLOCAL_VERSION);
define( 'WOO_DLOCAL_OFFLINE_ID', 'dlocal-offline-gateway');

require plugin_dir_path( __FILE__ ) . 'includes/class-woo-dlocal.php';

add_action('plugins_loaded', 'woo_dlocal_init');

function woo_dlocal_init() {
	load_plugin_textdomain( 'wc-dlocal-payments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	if (!requirements_woo_dlocal()){
		return;
	}
	run_woo_dlocal();
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woo_dlocal_configure_links' );
}

function woo_dlocal_configure_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section='.WOO_DLOCAL_OFFLINE_ID ) . '">' . __( 'Configure', 'wc-dlocal-payments' ) . '</a>'
	);
	return array_merge( $plugin_links, $links );
}

//TODO: Add requirements
function requirements_woo_dlocal() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action(
			'admin_notices',
			function() {
				?>
					<div class="error notice">
						<p> 
						<?php echo 'Payments via dLocal for WooCommerce: WooCommerce must be installed and active.' ?>
						</p>
					</div>
				<?php
			}
		);
		return false ;
	} else {
		return true;
	}
}

function run_woo_dlocal() {
	$plugin = new Woo_Dlocal();
	$plugin->run();
}

function woo_dlocal_log($logger, $level, $msg) {
	$context = ['source' => WOO_DLOCAL_FULL_NAME];
	$logger->log($level, $msg, $context);
}