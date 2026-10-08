/**
 * WORKS の一覧ブロック: デザインテイストでその場で絞り込む（guide 821:262・決定 §3）。
 *
 * ・[data-works-filter] … 一覧ブロック 1 つ。1 ページに複数あってもそれぞれ独立して動く。
 * ・[data-works-filter-btn="<slug>"] … テイストのボタン。単一選択のトグル（aria-pressed）。
 *   もう一度押すと解除して全件、別のボタンを押すと切り替わる。
 * ・[data-works-item][data-works-tastes="<slug> <slug>"] … カード。付いたテイストのどれかが
 *   一致すれば表示する。
 * ・[data-works-filter-status] … 0 件のとき data-empty-text の文言を入れる（aria-live）。
 *
 * グリッドを短くフェードアウト（--wkp-filter-fade）→ 表示を入れ替えて詰める → フェードイン。
 * 動きを減らす設定では即時に切り替える。JS が無いときは全件表示のまま。
 */
(function () {
	'use strict';

	var blocks = document.querySelectorAll('[data-works-filter]');

	if (!blocks.length) {
		return;
	}

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

	// CSS のトークン（0.3s / 300ms）をミリ秒にする。
	function toMs(value) {
		var num = parseFloat(value);

		if (isNaN(num)) {
			return 0;
		}

		return /ms$/.test(value.trim()) ? num : num * 1000;
	}

	Array.prototype.forEach.call(blocks, function (block) {
		var buttons = Array.prototype.slice.call(block.querySelectorAll('[data-works-filter-btn]'));
		var items = Array.prototype.slice.call(block.querySelectorAll('[data-works-item]'));
		var grid = block.querySelector('[data-works-filter-grid]');
		var status = block.querySelector('[data-works-filter-status]');
		var current = '';
		var timer = 0;

		if (!buttons.length || !grid) {
			return;
		}

		function apply(slug) {
			var shown = 0;

			items.forEach(function (item) {
				var tastes = (item.getAttribute('data-works-tastes') || '').split(/\s+/);
				var show = !slug || tastes.indexOf(slug) !== -1;

				item.hidden = !show;

				if (show) {
					shown += 1;
				}
			});

			if (status) {
				status.textContent = shown ? '' : status.getAttribute('data-empty-text') || '';
			}
		}

		function select(slug) {
			var fade = reduced.matches ? 0 : toMs(getComputedStyle(grid).getPropertyValue('--wkp-filter-fade'));

			current = slug;

			buttons.forEach(function (button) {
				button.setAttribute('aria-pressed', String(button.getAttribute('data-works-filter-btn') === slug));
			});

			window.clearTimeout(timer);

			if (!fade) {
				grid.classList.remove('is-fading');
				apply(slug);
				return;
			}

			grid.classList.add('is-fading');
			timer = window.setTimeout(function () {
				apply(current);
				grid.classList.remove('is-fading');
			}, fade);
		}

		buttons.forEach(function (button) {
			button.addEventListener('click', function () {
				var slug = button.getAttribute('data-works-filter-btn') || '';

				select(slug === current ? '' : slug);
			});
		});
	});
})();
