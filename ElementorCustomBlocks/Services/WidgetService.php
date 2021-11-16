<?php

namespace ElementorCustomBlocks\Services {

	use ElementorCustomBlocks\Classes\ElementorCustomBlockWidget;
	use Elementor\Plugin;

	class WidgetService {

		public static function init_widgets() {
			add_action('elementor/widgets/widgets_registered', function () {
				Plugin::instance()->widgets_manager->register_widget_type(new ElementorCustomBlockWidget());
			});
		}

		public static function init_editor_assets($plugin_root_file) {
			add_action('elementor/editor/after_enqueue_scripts', function () use ($plugin_root_file) {
				$plugin_root_url = plugins_url("", $plugin_root_file);
				wp_enqueue_style('ecb-admin', $plugin_root_url . "/assets/css/ecb-admin.css");
				wp_enqueue_script('ecb-admin', $plugin_root_url . "/assets/js/ecb-admin.js");
			});
		}

		public static function init_ajax_hooks() {
			add_action('wp_ajax_get_custom_block_fields', function () {
				$template_id = $_GET["template_id"];
				$content = self::get_elementor_template_content($template_id);
				$fields = self::parse_fields($content, false);
				header('Content-Type: application/json');
				echo json_encode($fields);
				exit();
			});
		}

		public static function init_elementor_custom_block_shortcode() {
			add_shortcode("elementor-custom-block", function ($atts) {
				$template_id = $atts["id"];
				$vars = $atts["vars"];
				$match_str = '';

				$content = self::get_elementor_template_content($template_id);
				$fields = self::parse_fields($content, true);

				$is_edit_mode = Plugin::$instance->editor->is_edit_mode();
				if ($is_edit_mode) {
					static $style_added = false;
					if (!$style_added) {
						$style_added = true;
						$match_str .= '<style>.ecb-widget-overlay{position:absolute;top:0;right:0;bottom:0;left:0;background-color:#00000080;opacity:0;transition:opacity 500ms;z-index:999;}.ecb-widget-overlay:hover{opacity:1;}</style>';
					}
					$match_str .= '<div class="ecb-widget-overlay"></div>';
				}

				$replacements = [];
				$kvs = explode(",", $vars);
				foreach ($kvs as $kv) {
					$key_val = explode("|", $kv);
					if (count($key_val) == 2) {
						$search = urldecode(trim($key_val[0]));
						$replace = urldecode(trim($key_val[1]));
						$replacements[$search] = $replace;
					}
				}
				$content = self::replace_fields($content, $replacements, $fields);
				return $match_str . $content;
			}, -1000);
		}

		private static function get_elementor_template_content($template_id) {
			$rnd = rand(999999, 9999999);
			$content = do_shortcode(do_shortcode('[elementor-template id="' . $template_id . '" css="true"]'));
			$content = str_replace('elementor-' . $template_id, 'elementor-rnd-' . $rnd, $content);
			return $content;
		}

		private static function parse_fields($content, $include_matches) {
			preg_match_all('/' . get_shortcode_regex(["field"]) . '/', $content, $matches, PREG_SET_ORDER);
			$keys = [];
			if (is_array($matches)) {
				foreach ($matches as $match) {
					$atts_str = self::fix_atts($match[3]);
					$atts = shortcode_parse_atts($atts_str);
					$key = isset($atts["key"]) ? $atts["key"] : null;
					if ($key) {
						$type = isset($atts["type"]) ? $atts["type"] : "text";
						$keys[$key]["type"] = $type;
						if ($include_matches) $keys[$key]["matches"][] = $match[0];
					}
				}
			}
			return $keys;
		}

		private static function replace_fields($content, $replacements, $parsed_fields) {
			$fields = $parsed_fields;
			foreach ($replacements as $key => $replacement) {
				if (isset($fields[$key])) {
					$content = str_replace($fields[$key]["matches"], $replacement, $content);
				}
			}
			return $content;
		}

		private static function fix_atts($str) {
			$str = str_replace('&#8221;', '"', $str);
			$str = html_entity_decode($str);
			$str = urldecode($str);
			return $str;
		}

		public static function migrate_widget($current_name, $new_name) {
			global $wpdb;

			$search_1 = sprintf('"widgetType":"%s"', $current_name);
			$replace_1 = sprintf('"widgetType":"%s"', $new_name);
			$search_2 = sprintf('s:%s:"%s";', strlen($current_name), $current_name);
			$replace_2 = sprintf('s:%s:"%s";', strlen($new_name), $new_name);

			$str = "update wp_postmeta
				set meta_value = replace(meta_value, '%s', '%s'), meta_value = replace(meta_value, '%s', '%s')
				where meta_key = '_elementor_data' or meta_key = '_elementor_controls_usage'";
			$query = sprintf($str, $search_1, $replace_1, $search_2, $replace_2);
			$wpdb->query($query);
		}

	}

}