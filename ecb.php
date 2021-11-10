<?php

use ElementorCustomBlocks\Services\WidgetService;

/*
  Plugin Name: AloniDev - Elementor Custom Blocks
  Plugin URI: http://www.alonidev.com
  Version: 1.3.0
  Author: Guy Aloni
  Text Domain: ecb
 */

$plugin_vars = [
	'namespace' => 'ElementorCustomBlocks',
	'info_url' => 'http://alonidev.com/',
	'remote_package_url' => 'https://github.com/guyaloni912/elementor-custom-blocks/archive/refs/heads/release.zip',
	'remote_root_file_url' => 'https://raw.githubusercontent.com/guyaloni912/elementor-custom-blocks/release/ecb.php'
];

spl_autoload_register(function ($class_name) use ($plugin_vars) {
	if (stripos($class_name, $plugin_vars["namespace"] . '\\') === 0) {
		include __DIR__ . "/" . str_replace('\\', '/', $class_name) . '.php';
	}
});

add_filter('pre_set_site_transient_update_plugins', function ($transient) use ($plugin_vars) {
	$current_version = get_file_data(__FILE__, ['version'])[0];
	$new_version = get_file_data($plugin_vars["remote_root_file_url"], ['version'])[0];
	if (version_compare($current_version, $new_version) < 0) {
		$obj = new stdClass();
		$obj->slug = basename(__FILE__);
		$obj->new_version = $new_version;
		$obj->url = $plugin_vars["info_url"];
		$obj->package = $plugin_vars["remote_package_url"];
		$transient->response[basename(__DIR__) . '/' . basename(__FILE__)] = $obj;
	}
	return $transient;
});

//////////////////////////

WidgetService::init_widgets();
WidgetService::init_editor_assets(__FILE__);
WidgetService::init_ajax_hooks();
WidgetService::init_elementor_custom_block_shortcode();
