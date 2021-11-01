<?php

/*
  Plugin Name: AloniDev - Elementor Custom Blocks
  Plugin URI: http://www.alonidev.com
  Version: 1.3.6
  Author: Guy Aloni
  Text Domain: ecb
 */


add_filter('pre_set_site_transient_update_plugins', function ($transient) {
	$base_url = 'https://bitbucket.org/guyaloni912/elementor-custom-blocks/';
	@$current_version = get_file_data(__FILE__, ['version'])[0];
	@$new_version = get_file_data('raw/release/ecb.php', ['version'])[0];
	if (version_compare($current_version, $new_version) < 0) {
		$obj = new stdClass();
		$obj->slug = __FILE__;
		$obj->new_version = $new_version;
		$obj->url = 'http://alonidev.com/';
		$obj->package = $base_url . 'get/release.zip';
		$transient->response[__DIR__ . '/' . __FILE__] = $obj;
	}
	return $transient;
});
