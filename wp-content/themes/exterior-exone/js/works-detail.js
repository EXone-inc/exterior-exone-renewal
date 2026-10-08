/**
 * WORKS 詳細: パンフレットのサムネ 3 枚・キャプションの差し替え。
 *
 * めくり・自動送り・当たり判定・進捗下線は共通部品 js/flipbook.js（[data-flipbook]）。
 * ここはその本とサムネをつなぐだけ。対象は [data-works-detail]。
 * カンプ: PC 821:226〜228・821:223〜225 / SP 836:90〜94 / guide 821:271・並び順の図 821:334
 * 仕様書: docs/spec-20261008-works-page.md F-04
 *
 * - 並び: 左ページ = 現在 N のとき、サムネ = N+1 / N+2 / N+3（循環）
 * - サムネを押す → その写真が左ページに来るよう 1 回だけ右へめくる（flipbook:go、dir = next）
 * - flipbook:start → サムネを消す / flipbook:end → 新しい並びに差し替えて出す
 */
(function () {
	'use strict';

	function setup(section) {
		var book = section.querySelector('[data-flipbook]');
		var items = Array.prototype.slice.call(section.querySelectorAll('[data-works-thumb-item]'));

		if (!book || !items.length) {
			return;
		}

		var count = book.querySelectorAll('.c-flipbook__page--left .c-flipbook__photo').length;

		if (count < 2) {
			return;
		}

		var thumbs = items.map(function (item) {
			return {
				item: item,
				button: item.querySelector('[data-works-thumb]'),
				photos: Array.prototype.slice.call(item.querySelectorAll('.p-wkpdetail__thumb-photo')),
				cap: item.querySelector('[data-works-thumb-cap]')
			};
		});

		function fade(out) {
			thumbs.forEach(function (thumb) {
				thumb.item.classList.toggle('is-fading', out);
			});
		}

		function write(current) {
			thumbs.forEach(function (thumb, slot) {
				var index = (current + slot + 1) % count;
				var caption = '';

				thumb.photos.forEach(function (photo, i) {
					photo.classList.toggle('is-current', i === index);

					if (i === index) {
						caption = photo.getAttribute('data-caption') || '';
					}
				});

				thumb.button.setAttribute('data-works-thumb', String(index));
				thumb.button.setAttribute('aria-label', caption ? caption + 'を表示' : 'メインの写真を表示');

				if (thumb.cap) {
					thumb.cap.textContent = caption;
				}
			});
		}

		book.addEventListener('flipbook:start', function () {
			fade(true);
		});

		book.addEventListener('flipbook:end', function (event) {
			write(event.detail.current);
			fade(false);
		});

		thumbs.forEach(function (thumb) {
			thumb.button.addEventListener('click', function () {
				var index = parseInt(thumb.button.getAttribute('data-works-thumb'), 10);

				book.dispatchEvent(new CustomEvent('flipbook:go', { detail: { index: index, dir: 'next' } }));
			});
		});
	}

	Array.prototype.forEach.call(document.querySelectorAll('[data-works-detail]'), setup);
})();
