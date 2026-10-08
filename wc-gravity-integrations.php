<?php
/**
 * Plugin Name: WooCommerce & Gravity Forms Custom Integrations Starter
 * Plugin URI: https://github.com/nadimtuhin/wc-gravity-integrations-starter
 * Description: Production-ready WordPress plugin boilerplate demonstrating custom WooCommerce checkout hooks, Gravity Forms submission and validation add-on pipelines, third-party REST API sync, and secure SQL architecture.
 * Version: 1.0.0
 * Author: Nadim Tuhin
 * Author URI: https://nadimtuhin.com
 * License: GPL-2.0-or-later
 * Text Domain: wc-gf-integrations
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/includes/class-woocommerce-hooks.php';
require_once __DIR__ . '/includes/class-gravity-forms-addon.php';
require_once __DIR__ . '/includes/class-api-client.php';
require_once __DIR__ . '/includes/class-db-manager.php';

register_activation_hook( __FILE__, [ '\WCGravityIntegrations\DbManager', 'migrate' ] );

add_action(
	'plugins_loaded',
	function () {
		\WCGravityIntegrations\WooCommerceHooks::init();
		\WCGravityIntegrations\GravityFormsAddon::init();
	}
);
