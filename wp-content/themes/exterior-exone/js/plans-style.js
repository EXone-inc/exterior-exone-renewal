/**
 * PLANS: style list のカードを押すと、上の style view（画像・和名・英名・説明文）を
 * ふわっと切り替える（TOP の PLANS と同じ動き。TOP の js/plans-switch.js とは別属性）。
 *
 * 一度フェードアウトし、消え終わり + 新しい画像の読み込みが済んでから差し替えて
 * フェードインする（画像が空の瞬間を見せない）。フェードの時間は CSS
 * （.p-plppkg__view.is-switching の transition）が決め、動きを減らす設定では即時。
 * カード列のドラッグは js/scroll-row.js（ドラッグ直後の click はそちらで打ち消される）。
 */
(function () {
	'use strict';

	var list = document.querySelector('[data-plp-style-list]');
	var view = document.querySelector('[data-plp-view-root]');

	if (!list || !view) {
		return;
	}

	var buttons = Array.prototype.slice.call(list.querySelectorAll('[data-plp-style-name]'));
	var parts = {
		image: view.querySelector('[data-plp-view="image"]'),
		name: view.querySelector('[data-plp-view="name"]'),
		eng: view.querySelector('[data-plp-view="eng"]'),
		desc: view.querySelector('[data-plp-view="desc"]')
	};

	if (!buttons.length || !parts.image || !parts.name || !parts.eng || !parts.desc) {
		return;
	}

	var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
	var request = 0;

	/** フェードアウトにかかる時間（ms）。transition-duration の最長値を読む */
	function fadeTime() {
		var durations = window.getComputedStyle(parts.image).transitionDuration.split(',');
		var max = 0;

		durations.forEach(function (value) {
			var sec = parseFloat(value);

			if (sec > max) {
				max = sec;
			}
		});

		return max * 1000;
	}

	function apply(button) {
		var name = button.getAttribute('data-plp-style-name');
		var desc = button.getAttribute('data-plp-style-desc') || '';

		parts.image.src = button.getAttribute('data-plp-style-image');
		parts.image.alt = name + 'の外観イメージ';
		parts.name.textContent = name;
		parts.eng.textContent = button.getAttribute('data-plp-style-eng');

		parts.desc.textContent = '';
		desc.split('\n').forEach(function (line) {
			var p = document.createElement('p');

			p.textContent = line;
			parts.desc.appendChild(p);
		});
	}

	function select(button) {
		if (button.getAttribute('aria-pressed') === 'true') {
			return;
		}

		buttons.forEach(function (item) {
			var active = item === button;
			var card = item.closest('.p-plppkg__card');

			item.setAttribute('aria-pressed', String(active));

			// 持ち上げはカード側で行う（TOP と同じ）。
			if (card) {
				card.classList.toggle('is-active', active);
			}
		});

		var current = ++request;

		if (motion.matches) {
			view.classList.remove('is-switching');
			apply(button);
			return;
		}

		var pending = 2;

		function done() {
			pending -= 1;

			// 後から押されたカードがあれば、そちらに任せる。
			if (pending || current !== request) {
				return;
			}

			apply(button);

			// 新しい画像を描いてからフェードインする。
			window.requestAnimationFrame(function () {
				if (current === request) {
					view.classList.remove('is-switching');
				}
			});
		}

		view.classList.add('is-switching');
		window.setTimeout(done, fadeTime());

		var loader = new Image();

		loader.onload = done;
		// 読めなかったときも文字は切り替える（止まったままにしない）。
		loader.onerror = done;
		loader.src = button.getAttribute('data-plp-style-image');
	}

	buttons.forEach(function (button) {
		button.addEventListener('click', function () {
			select(button);
		});
	});
})();
