<?php

/*
  Plugin Name: AloniDev - Elementor Custom Blocks
  Plugin URI: http://www.alonidev.com
  Version: 1.0.0
  Author: Guy Aloni
  Text Domain: aedt
 */


add_filter('pre_set_site_transient_update_plugins', function ($transient) {
	$dirname = basename(__DIR__);
	$filename = basename(__FILE__);
	$base_url = 'https://bitbucket.org/guyaloni912/elementor-custom-blocks/';
	@$version = file_get_contents('raw/release/version.txt');
	$obj = new stdClass();
	$obj->slug = $filename;
	$obj->new_version = $version;
	$obj->url = 'http://alonidev.com/';
	$obj->package = 'get/release.zip';
	$transient->response[$dirname . '/' . $filename] = $obj;
	return $transient;
});
