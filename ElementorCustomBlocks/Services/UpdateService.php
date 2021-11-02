<?php

namespace ElementorCustomBlocks\Services {

	use stdClass;

	class UpdateService {

		static function init_update() {
			add_filter('pre_set_site_transient_update_plugins', function ($transient) {
				$basefilename = basename(ECB_ROOT_FILE_PATH);
				$basedirname = basename(ECB_ROOT_DIR_PATH);
				$current_version = get_file_data(ECB_ROOT_FILE_PATH, ['version'])[0];
				$new_version = get_file_data(ECB_REMOTE_ROOT_FILE_URL, ['version'])[0];
				if (version_compare($current_version, $new_version) < 0) {
					$obj = new stdClass();
					$obj->slug = $basefilename;
					$obj->new_version = $new_version;
					$obj->url = ECB_INFO_URL;
					$obj->package = ECB_REMOTE_PACKAGE_URL;
					$transient->response[$basedirname . '/' . $basefilename] = $obj;
				}
				return $transient;
			});
		}

	}

}