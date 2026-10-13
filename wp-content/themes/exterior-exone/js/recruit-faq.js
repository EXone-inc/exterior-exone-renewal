/**
 * 採用ページ FAQ の開閉（button[data-recruit-faq]）。
 *
 * 現サイト recruit.js の FAQ と同じく、押すと回答が高さのアニメ（0.35 秒）で開き、もう一度押すと閉じる。
 * 複数の問を同時に開ける（他を閉じない）。開き終わったら高さの上限を外す（リサイズで切れない）。
 * button なので Enter / Space でも同じ操作ができる。
 */
(function () {
	'use strict';

	var buttons = Array.prototype.slice.call(document.querySelectorAll('button[data-recruit-faq]'));

	if (!buttons.length) {
		return;
	}

	function panelOf(button) {
		return document.getElementById(button.getAttribute('aria-controls'));
	}

	function open(button, panel) {
		button.setAttribute('aria-expanded', 'true');
		panel.style.maxHeight = panel.scrollHeight + 'px';
	}

	function close(button, panel) {
		button.setAttribute('aria-expanded', 'false');

		// 上限を外していた場合は今の高さを入れ直してから 0 へ（アニメさせるため）。
		if (panel.style.maxHeight === 'none') {
			panel.style.maxHeight = panel.scrollHeight + 'px';
			void panel.offsetHeight;
		}

		panel.style.maxHeight = '0px';
	}

	buttons.forEach(function (button) {
		var panel = panelOf(button);

		if (!panel) {
			return;
		}

		button.addEventListener('click', function () {
			if (button.getAttribute('aria-expanded') === 'true') {
				close(button, panel);
			} else {
				open(button, panel);
			}
		});

		panel.addEventListener('transitionend', function (event) {
			if (event.target === panel && event.propertyName === 'max-height' && button.getAttribute('aria-expanded') === 'true') {
				panel.style.maxHeight = 'none';
			}
		});

		// 動きを減らす設定では transitionend が来ないので、開いたらすぐ上限を外す。
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			button.addEventListener('click', function () {
				if (button.getAttribute('aria-expanded') === 'true') {
					panel.style.maxHeight = 'none';
				}
			});
		}
	});
})();
