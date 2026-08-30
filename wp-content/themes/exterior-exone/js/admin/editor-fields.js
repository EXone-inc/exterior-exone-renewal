/**
 * お知らせ・施工事例の編集フォームの画像フィールド。
 *
 * ・[data-exone-gallery] … 複数枚。添付ファイル ID をカンマ区切りで持ち、
 *   並び順がそのまま表示順になる（1 枚目がカードに使われる）。
 * ・[data-exone-image]   … 1 枚だけ。お知らせの画像に使う。
 */
(function ($) {
	'use strict';

	/**
	 * メディアモーダルを開く。frame は使い回す（毎回作ると選択状態が残らない）。
	 */
	function opener(config) {
		var frame = null;

		return function () {
			if (!frame) {
				frame = wp.media(config.options);
				frame.on('select', function () {
					config.onSelect(frame.state().get('selection'));
				});
			}

			frame.open();
		};
	}

	function thumbUrl(attachment) {
		var sizes = attachment.sizes || {};
		var size = sizes.thumbnail || sizes.medium || sizes.full;

		return size ? size.url : attachment.url;
	}

	/**
	 * 複数枚（施工事例の画像）。
	 */
	function initGallery(box) {
		var list = box.querySelector('[data-exone-gallery-list]');
		var input = box.querySelector('[data-exone-gallery-input]');
		var addButton = box.querySelector('[data-exone-gallery-add]');

		function sync() {
			var ids = Array.prototype.map.call(
				list.querySelectorAll('[data-exone-gallery-item]'),
				function (item) {
					return item.getAttribute('data-exone-gallery-item');
				}
			);

			input.value = ids.join(',');
		}

		function addItem(attachment) {
			// 同じ画像を二重に入れない。
			if (input.value && input.value.split(',').indexOf(String(attachment.id)) !== -1) {
				return;
			}

			var item = document.createElement('li');
			item.className = 'exone-gallery__item';
			item.setAttribute('data-exone-gallery-item', attachment.id);

			var img = document.createElement('img');
			img.src = thumbUrl(attachment);
			img.alt = '';
			item.appendChild(img);

			var remove = document.createElement('button');
			remove.type = 'button';
			remove.className = 'exone-gallery__remove';
			remove.setAttribute('data-exone-gallery-remove', '');
			remove.setAttribute('aria-label', 'この画像を外す');
			remove.innerHTML = '&times;';
			item.appendChild(remove);

			list.appendChild(item);
		}

		addButton.addEventListener('click', opener({
			options: {
				title: '施工事例の画像を選ぶ',
				button: { text: 'この画像を使う' },
				library: { type: 'image' },
				multiple: 'add'
			},
			onSelect: function (selection) {
				selection.each(function (attachment) {
					addItem(attachment.toJSON());
				});

				sync();
			}
		}));

		// 「×」は後から足した要素にも効かせたいので委譲で拾う。
		list.addEventListener('click', function (event) {
			var remove = event.target.closest('[data-exone-gallery-remove]');

			if (!remove) {
				return;
			}

			remove.closest('[data-exone-gallery-item]').remove();
			sync();
		});

		$(list).sortable({
			items: '> [data-exone-gallery-item]',
			cursor: 'move',
			update: sync
		});
	}

	/**
	 * 1 枚だけ（お知らせの画像）。
	 */
	function initSingle(box) {
		var preview = box.querySelector('[data-exone-image-preview]');
		var input = box.querySelector('[data-exone-image-input]');
		var addButton = box.querySelector('[data-exone-image-add]');
		var removeButton = box.querySelector('[data-exone-image-remove]');

		function render(attachment) {
			preview.innerHTML = '';

			if (!attachment) {
				input.value = '';
				removeButton.hidden = true;

				return;
			}

			var img = document.createElement('img');
			img.src = thumbUrl(attachment);
			img.alt = '';
			preview.appendChild(img);

			input.value = attachment.id;
			removeButton.hidden = false;
		}

		addButton.addEventListener('click', opener({
			options: {
				title: 'お知らせの画像を選ぶ',
				button: { text: 'この画像を使う' },
				library: { type: 'image' },
				multiple: false
			},
			onSelect: function (selection) {
				render(selection.first().toJSON());
			}
		}));

		removeButton.addEventListener('click', function () {
			render(null);
		});
	}

	$(function () {
		Array.prototype.forEach.call(document.querySelectorAll('[data-exone-gallery]'), initGallery);
		Array.prototype.forEach.call(document.querySelectorAll('[data-exone-image]'), initSingle);
	});
})(jQuery);
