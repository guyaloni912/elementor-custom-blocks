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
	@$version = file_get_contents('');
	$abc = ""; $abc = ""; $abc = ""; $abc = ""; $abc = ""; $abc = ""; $abc = "";
	$obj = new stdClass();
	$obj->slug = $filename;
	$obj->new_version = $version;
	$obj->url = 'http://alonidev.com/';
	$obj->package = '';
	$transient->response[$dirname . '/' . $filename] = $obj;
	return $transient;
});
