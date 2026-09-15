/**
 * 支店ページ: DX EXPERIENCE のステップ切り替え（カンプ Group 331 / 572:619）。
 *
 * ・ステップの見出し（番号 + タイトル）を押すと、そのステップがアクティブになり
 *   右のビジュアルと下のバーも同じ番号に切り替わる
 * ・TOP の DX（js/dx-slider.js）のようなピン留め・スクロールジャックは行わない
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-sdx]');

	if (!root) {
		return;
	}

	var steps = root.querySelectorAll('[data-sdx-step]');
	var visuals = root.querySelectorAll('[data-sdx-visual]');
	var bars = document.querySelectorAll('[data-sdx-bar]');

	/**
	 * @param {NodeList} items 対象の要素。
	 * @param {string}   name  data 属性名（値が番号）。
	 * @param {number}   index アクティブにする番号。
	 */
	function mark(items, name, index) {
		Array.prototype.forEach.call(items, function (item) {
			var isActive = Number(item.getAttribute(name)) === index;

			item.classList.toggle('is-active', isActive);
		});
	}

	/**
	 * @param {number} index アクティブにするステップ番号。
	 */
	function activate(index) {
		mark(steps, 'data-sdx-step', index);
		mark(visuals, 'data-sdx-visual', index);
		mark(bars, 'data-sdx-bar', index);

		Array.prototype.forEach.call(root.querySelectorAll('[data-sdx-trigger]'), function (trigger) {
			trigger.setAttribute(
				'aria-pressed',
				Number(trigger.getAttribute('data-sdx-trigger')) === index ? 'true' : 'false'
			);
		});
	}

	root.addEventListener('click', function (event) {
		var trigger = event.target.closest('[data-sdx-trigger]');

		if (!trigger) {
			return;
		}

		activate(Number(trigger.getAttribute('data-sdx-trigger')));
	});
})();
