(function ($) {
	class ElementorCustomBlocks {
		static xhrRequest;
		static initialized = false;

		static init() {
			$(() => {
				elementor.hooks.addAction('panel/open_editor/widget', (panel, model, view) => {
					panel.$el.removeClass("ecb-widget");
					this.abortXhrRequest();
				});
				elementor.hooks.addAction('panel/open_editor/widget/elementor-custom-block', (panel, model, view) => {
					panel.$el.addClass("ecb-widget uneditable-fields");
					if (!this.initialized) {
						this.initialized = true;
						$("body").on("click", ".ecb-widget .elementor-control-html_field_controls .btn-edit-fields", () => {
							panel.$el.toggleClass("uneditable-fields");
							panel.$el.toggleClass("editable-fields");
						});
						$("body").on("click", ".ecb-widget .elementor-control-html_field_controls .btn-update-fields", () => {
							this.updateFields();
						});
						$("body").on("click", ".ecb-widget .elementor-control-html_field_controls .edit-template", (e) => {
							e.preventDefault();
							this.editTemplate();
						});
					}
				});
			});
		}

		static updateFields() {
			if ($(".ecb-widget .elementor-control-fields .working-spinner").length === 0) {
				$(".ecb-widget .elementor-control-fields").append('<div class="working-spinner"><i class="eicon-loading eicon-animation-spin"></i></div>');
			}
			this.abortXhrRequest();
			var templateId = this.getTemplateId();
			if (templateId) {
				$(".ecb-widget").addClass("working");
				this.xhrRequest = $.post(this.getXhrUrl(templateId), (fields) => {
					var keysArray = Object.keys(fields);
					var keysPointer = 0;
					var items = [];
					var interval = setInterval(() => {
						var key = keysArray[keysPointer];
						var field = fields[key];
						if (field) {
							var type = field["type"];
							var $item = this.getItem(key);
							if ($item === null) {
								$item = this.createItem();
							}
							this.updateItem($item, key, type);
							items.push($item);
						}
						keysPointer++;
						if (keysPointer >= keysArray.length) {
							clearInterval(interval);
							$(".ecb-widget").removeClass("working");
						}

					});
					var orphans = this.getOrphans(keysArray);
					this.deleteItems(orphans, orphans.length + " orphaned fields found\n\nClick 'OK' to delete them");
					var duplicates = this.getDuplicates();
					this.deleteItems(duplicates, duplicates.length + " duplicate fields found\n\nClick 'OK' to delete them\n\nFields are removed from the top");
				});
			}
		}

		static getItem(key) {
			var ret = null;
			var $items = $(".ecb-widget .elementor-control-fields .elementor-repeater-fields");
			$($items).each((i, elem) => {
				var $item = $(elem);
				var keyInput = $item.find(".elementor-control-key input");
				if (keyInput.val() === key) ret = $item;
			});
			return ret;
		}

		static createItem() {
			$(".ecb-widget .elementor-control-fields .elementor-repeater-add").click();
			var $item = $(".ecb-widget .elementor-control-fields .elementor-repeater-fields").eq(-1);
			return $item;
		}

		static updateItem($item, key, type) {
			var $keyInput = $item.find(".elementor-control-key input");
			$keyInput.val(key).trigger("input");
			var $typeInput = $item.find(".elementor-control-type select");
			$typeInput.val(type).trigger("change");
		}

		static getOrphans(keysArray) {
			var orphans = [];
			$(".ecb-widget .elementor-control-fields .elementor-repeater-fields").each((i, elem) => {
				var $item = $(elem);
				var key = $item.find(".elementor-control-key input").val();
				if (keysArray.indexOf(key) === -1) {
					orphans.push($item);
				}
			});
			return orphans;
		}

		static getDuplicates() {
			var duplicates = [];
			var keys = [];
			$($(".ecb-widget .elementor-control-fields .elementor-repeater-fields").get().reverse()).each((i, elem) => {
				var $item = $(elem);
				var key = $item.find(".elementor-control-key input").val();
				if (keys.indexOf(key) === -1) {
					keys.push(key);
				} else {
					duplicates.push($item);
				}
			});
			return duplicates;
		}

		static deleteItems(itemsArray, message) {
			if (itemsArray.length > 0) {
				var conf = confirm(message);
				if (conf) {
					itemsArray.forEach(($orphan) => {
						$orphan.find(".elementor-repeater-tool-remove").click();
					});
				}
			}
		}

		static getTemplateId() {
			var templateId = $(".ecb-widget .elementor-control-template select").val();
			return templateId;
		}

		static getXhrUrl(templateId) {
			var xhrUrl = "/wp-admin/admin-ajax.php?action=get_custom_block_fields&template_id=" + templateId;
			return xhrUrl;
		}

		static abortXhrRequest() {
			if (this.xhrRequest) {
				this.xhrRequest.abort();
			}
		}

		static setSyncStatus(inSync) {
			var message = '';
			if (inSync === true) {
				message = '<span class="in-sync">In Sync</span>';
			} else {
				message = '<span class="out-of-sync">Out of Sync</span>';
			}
			$(".ecb-widget .elementor-control-update_fields .elementor-control-title").html(message);
		}

		static editTemplate() {
			var templateId = this.getTemplateId();
			if (templateId) {
				window.open('/wp-admin/post.php?post=' + templateId + '&action=elementor');
			}
		}
	}
	ElementorCustomBlocks.init();
})(jQuery);


//function syncCheck() {
//	synced = true;
//	abortXhrRequest();
//	xhrRequest = $.post(getXhrUrl(), function (fields) {
//		var items = [];
//		$(".ecb-widget .elementor-control-fields .elementor-repeater-fields").each(function () {
//			var $this = $(this);
//			var key = $this.find(".elementor-repeater-row-item-title").text();
//			var type = $this.find(".elementor-control-type select").val();
//			items.push({key: key, type: type});
//		});
//		console.log(items);
//		console.log(fields);
//		items.forEach(function (item) {
//
//		});
//		if (synced === false) {
//			$(".ecb-widget .elementor-control-update_fields .elementor-control-title").html("out of sync");
//		}
//	});
//}