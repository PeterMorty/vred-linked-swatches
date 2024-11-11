<?php

/**			
 *
 * Plugin Name: 			Woo Linked Products
 * Description: 			Create links to other variations of the product.
 * Plugin URI: 				https://hispacomercios.com
 * Author: 					hispacomercios
 * Author URI: 				https://hispacomercios.com
 * Version: 				1.0.0
 * License: 				Copyright 2022 hispacomercios (email: info@hispacomercios.com)
 * Text Domain: 			hcwlp
 * Domain Path: 			/languages/
 * Requires at least:		5.0
 * Tested up to:			6.6.2
 * WC requires at least:	5.0
 * WC tested up to: 		9.3.3
 *
 * @author 					hispacomercios
 * @link					https://hispacomercios.com
 * @since					1.0
 * @package					WooCommerceLinkedProducts
 *
 */

// Exit if accessed directly
defined ('ABSPATH') or exit;

/**
 * Define constants
 */
if ( ! defined( 'HCWLP_PLUGIN_NAME' ) ) {
    define( 'HCWLP_PLUGIN_NAME', 'Woo Linked Products' );
}

if ( ! defined( 'HCWLP_PLUGIN_VERSION' ) ) {
    define( 'HCWLP_PLUGIN_VERSION', '1.0.0' );
}

if ( ! defined( 'HCWLP_PLUGIN_DIR_PATH' ) ) {
    define( 'HCWLP_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( is_admin() ) {
	require HCWLP_PLUGIN_DIR_PATH . '/includes/class-hcwlp-admin.php';
}
else {
	require HCWLP_PLUGIN_DIR_PATH . '/includes/class-hcwlp-front.php';
}

/**
 * Update checker
 */
require dirname( __FILE__ ) . '/updates/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$hcwlp_update_checker = PucFactory::buildUpdateChecker(
	'https://hispacomercios.com/dev/updates/plugins/hc-woo-linked-products/hcwlp.json',
	__FILE__,
	'hcwlp'
);

/**
 * Declare HPOS compatibility 
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
});

/**
 * Init plugin
 */
function hcwlp_init()
{
    if ( is_admin() ) {
		$HCWLP = new HCWLP_Admin();
	} 
	else {
		$HCWLP = new HCWLP_Front();
	}
	$HCWLP->init();

	// Load language files
	if ( is_admin() ) {
		load_plugin_textdomain( 'hcwlp', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
}
add_action( 'plugins_loaded', 'hcwlp_init' );