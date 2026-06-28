<?php
/**
 * Plugin Name: VRED Linked Swatches
 * Plugin URI: https://viviendoenred.com
 * Description: Connects independent WooCommerce products as visual linked swatches.
 * Version: 2.0.0
 * Author: VRED
 * Author URI: https://viviendoenred.com
 * Text Domain: vred-linked-swatches
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 8.0
 * WC tested up to: 10.8
 * Update URI: https://dev.viviendoenred.com/wordpress/plugins/vred-linked-swatches/updates/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (! defined('ABSPATH')) {
	exit;
}

define('VRED_LINKED_SWATCHES_VERSION', '2.0.0');
define('VRED_LINKED_SWATCHES_FILE', __FILE__);
define('VRED_LINKED_SWATCHES_BASENAME', plugin_basename(__FILE__));
define('VRED_LINKED_SWATCHES_PATH', plugin_dir_path(__FILE__));
define('VRED_LINKED_SWATCHES_URL', plugin_dir_url(__FILE__));
define('VRED_LINKED_SWATCHES_SLUG', 'vred-linked-swatches');
define('VRED_LINKED_SWATCHES_UPDATE_URL', 'https://dev.viviendoenred.com/wordpress/plugins/vred-linked-swatches/updates/vred-linked-swatches.json');
define('VRED_LINKED_SWATCHES_PRIVATE_UPDATER', true);

require_once VRED_LINKED_SWATCHES_PATH . 'includes/Plugin.php';

VRED_Linked_Swatches\Plugin::instance()->boot();
