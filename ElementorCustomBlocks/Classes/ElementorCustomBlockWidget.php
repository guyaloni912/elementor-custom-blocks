<?php

namespace ElementorCustomBlocks\Classes {

	use Elementor\Controls_Manager;
	use Elementor\Widget_Base;

	class ElementorCustomBlockWidget extends Widget_Base {

		public function get_name() {
			return 'elementor-custom-block';
		}

		public function get_title() {
			return 'Custom Block';
		}

		public function get_icon() {
			return 'eicon-inner-section';
		}

		public function get_categories() {
			return ['basic'];
		}

		protected function _register_controls() {

			$template_array = $this->get_template_array();

			$this->start_controls_section(
					'content_section',
					[
						'label' => 'Content',
						'tab' => Controls_Manager::TAB_CONTENT,
					]
			);

			$this->add_control(
					'template',
					[
						'label' => 'Template',
						'type' => Controls_Manager::SELECT2,
						'options' => $template_array,
						'label_block' => true
					]
			);

			$this->add_control(
					'html_field_controls',
					[
						'type' => Controls_Manager::RAW_HTML,
						'show_label' => false,
						'raw' => self::get_buttons_markup(),
						'separator' => 'after',
						'conditions' => self::get_null_template_conditions()
					]
			);

			$repeater = new \Elementor\Repeater();

			$repeater->add_control(
					'key',
					[
						'label' => 'Key',
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => 'Key',
						'label_block' => true,
					]
			);
			$repeater->add_control(
					'type',
					[
						'label' => 'Type',
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => [
							"text" => "Text",
							"content" => "Content",
							"image" => "Image",
							"icon" => "Icon",
							"bool" => "Bool"
						],
						'default' => 'text',
						'label_block' => true
					]
			);

			$repeater->add_control(
					'value_text',
					[
						'label' => 'Value',
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
						'condition' => ['type' => 'text']
					]
			);
			$repeater->add_control(
					'value_content',
					[
						'label' => 'Value',
						'type' => \Elementor\Controls_Manager::WYSIWYG,
						'label_block' => true,
						'condition' => ['type' => 'content']
					]
			);
			$repeater->add_control(
					'value_image',
					[
						'label' => 'Value',
						'type' => \Elementor\Controls_Manager::MEDIA,
						'label_block' => true,
						'condition' => ['type' => 'image']
					]
			);
			$repeater->add_control(
					'value_icon',
					[
						'label' => 'Value',
						'type' => \Elementor\Controls_Manager::ICONS,
						'label_block' => true,
						'condition' => ['type' => 'icon']
					]
			);
			$repeater->add_control(
					'value_bool',
					[
						'label' => 'Value',
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'label_block' => true,
						'condition' => ['type' => 'bool']
					]
			);

			$this->add_control(
					'fields',
					[
						'label' => 'Fields',
						'type' => \Elementor\Controls_Manager::REPEATER,
						'fields' => $repeater->get_controls(),
						'default' => [],
						'prevent_empty' => false,
						'show_label' => false,
						'title_field' => '{{{ key }}}',
						'conditions' => self::get_null_template_conditions()
					]
			);

			$this->end_controls_section();
		}

		protected function render() {

			$settings = $this->get_settings_for_display();
			$template_id = $settings['template'];
			$vars = "";
			$value = "";
			$html = "";

			if ($template_id) {
				$fields = $settings['fields'];
				foreach ((array) $fields as $field) {
					$key = $field["key"];
					$type = $field["type"];
					switch ($type) {
						case "text":
							$value = $field['value_text'];
							break;
						case "content":
							$value = $field['value_content'];
							break;
						case "image":
							$img = $field['value_image'];
							$value = $img ? $img["url"] : "";
							break;
						case "icon":
							$library = $field['value_icon']["library"];
							$val = $field['value_icon']["value"];
							if (!empty($val)) {
								if ($library == "svg") $value = file_get_contents($val["url"]);
								else $value = sprintf('<i class="%s"></i>', $val);
							}
							break;
						case "bool":
							$value = $field['value_bool'] ? 1 : 0;
							break;
					}
					$vars .= sprintf("%s|%s,", urlencode($key), urlencode($value));
				}
				$vars = trim($vars, ',');

				$html = do_shortcode(sprintf('[elementor-custom-block vars="%s" id="%s"]', $vars, $template_id));
			}
			echo $html;
		}

		private function get_template_array() {
			$q = new \WP_Query([
				"post_type" => "elementor_library",
				"posts_per_page" => -1,
				"tax_query" => [
					[
						'taxonomy' => 'elementor_library_type',
						'field' => 'slug',
						'terms' => 'section'
					]
				]
			]);
			$posts = $q->posts;
			$template_array = [];
			foreach ($posts as $p) {
				$template_array[$p->ID] = $p->post_title;
			}
			return $template_array;
		}

		private static function get_null_template_conditions() {
			return [
				'relation' => 'and',
				'terms' => [
					[
						'name' => 'template', 'operator' => '!=', 'value' => ''
					],
					[
						'name' => 'template', 'operator' => '!=', 'value' => null
					]
				]
			];
		}

		private static function get_buttons_markup() {
			$html = '
				<div class="raw-html-container">
					<div class="edit-template-wrapper">
						<a href="#" class="edit-template">edit template <i class="fas fa-external-link-alt"></i></a>
					</div>
					<div class="btns-wrapper">
						<button class="elementor-button elementor-button-default btn-edit-fields" type="button">Edit Fields</button>
						<button class="elementor-button elementor-button-default btn-update-fields" type="button">Auto Update Fields</button>
					</div>
				</div>';
			return $html;
		}

	}

}