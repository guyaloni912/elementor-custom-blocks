<?php

use ElementorCustomBlocks\Services\UpdateService;
use ElementorCustomBlocks\Services\WidgetService;

/*
  Plugin Name: AloniDev - Elementor Custom Blocks
  Plugin URI: http://www.alonidev.com
  Version: 1.0.0
  Author: Guy Aloni
  Text Domain: ecb
 */

spl_autoload_register(function ($class_name) {
	if (stripos($class_name, 'ElementorCustomBlocks\\') === 0) {
		include __DIR__ . "/" . str_replace('\\', '/', $class_name) . '.php';
	}
});

define('ECB_ROOT_DIR_PATH', __DIR__);
define('ECB_ROOT_FILE_PATH', __FILE__);
define('ECB_ROOT_DIR_URL', plugins_url("", __FILE__));
define('ECB_INFO_URL', 'http://alonidev.com/');
define('ECB_REMOTE_PACKAGE_URL', 'https://bitbucket.org/guyaloni912/elementor-custom-blocks/get/release.zip');
define('ECB_REMOTE_FILE_URL', 'https://bitbucket.org/guyaloni912/elementor-custom-blocks/raw/release/ecb.php');

UpdateService::init_update();

WidgetService::init_widgets();
WidgetService::init_editor_assets();
WidgetService::init_ajax_hooks();
WidgetService::init_elementor_custom_block_shortcode();
