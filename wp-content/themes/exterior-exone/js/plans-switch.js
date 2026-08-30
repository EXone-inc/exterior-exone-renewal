/**
 * PLANS のカードを押すと、下の PACKAGE PLAN の外観画像を差し替える。
 *
 * 画像は読み込み終わってから入れ替えて、切り替え時に一瞬空になるのを防ぐ。
 */
(function () {
	'use strict';

	var media = document.querySelector('[data-plan-media]');
	var buttons = Array.prototype.slice.call(document.querySelectorAll('[data-plan-image]'));

	if (!media || !buttons.length) {
		return;
	}

	var image = media.querySelector('img');

	if (!image) {
		return;
	}

	function select(button) {
		var src = button.getAttribute('data-plan-image');

		buttons.forEach(function (item) {
			var active = item === button;
			var card = item.closest('.p-plans__card');

			item.setAttribute('aria-pressed', String(active));

			// 持ち上げはカード側で行う（内側だけ動かすと下端に隙間ができる）。
			if (card) {
				card.classList.toggle('is-active', active);
			}
		});

		if (!src || image.getAttribute('src') === src) {
			return;
		}

		var loader = new Image();

		loader.onload = function () {
			media.classList.add('is-switching');

			window.setTimeout(function () {
				image.src = src;
				image.alt = button.getAttribute('data-plan-name') + 'の外観イメージ';
				media.classList.remove('is-switching');
			}, 180);
		};

		loader.src = src;
	}

	buttons.forEach(function (button) {
		button.addEventListener('click', function () {
			select(button);
		});
	});
})();
