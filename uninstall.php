<?php
/**
 * Uninstall cleanup
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

delete_site_transient('vred_linked_swatches_remote_plugin_info');
delete_site_transient('update_plugins');
