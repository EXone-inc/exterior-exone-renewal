/**
 * 採用ページ Work: インタビューのモーダル（dialog[data-recruit-modal="<1|2|3>"]）。
 *
 * 現サイトは popover（#Modal_menu1〜3）。<dialog> の showModal で作り直す（決定 §2-6）。
 * ・＋ボタン（[data-recruit-modal-open]）で開く。パネルは 0.8 秒でフェードイン、閉じるのは即時（仕様書 Q4）
 * ・閉じる操作は × / Esc / 暗幕（パネルの外）のクリック（仕様書 Q5）
 * ・開くとフォーカスは ×、Tab はモーダル内で循環。閉じると押した＋ボタンへ戻す
 * ・開いている間は背面をスクロールさせない（body.is-rc-modal-open）。中身は dialog の中でスクロール
 * ・閉じたらフェードの状態を戻す（現サイトの fade-in のリセットと同じ）
 */
(function () {
	'use strict';

	var dialogs = Array.prototype.slice.call(document.querySelectorAll('dialog[data-recruit-modal]'));

	if (!dialogs.length || typeof dialogs[0].showModal !== 'function') {
		return;
	}

	var OPEN_CLASS = 'is-rc-modal-open';
	var opener = null;

	function find(id) {
		return document.querySelector('dialog[data-recruit-modal="' + id + '"]');
	}

	function open(dialog, button) {
		if (dialog.open) {
			return;
		}

		var panel = dialog.querySelector('[data-recruit-modal-panel]');

		opener = button || null;
		document.body.classList.add(OPEN_CLASS);

		if (panel) {
			panel.classList.remove('is-shown');
		}

		dialog.showModal();
		dialog.scrollTop = 0;

		var close = dialog.querySelector('[data-recruit-modal-close]');

		if (close) {
			close.focus({ preventScroll: true });
		}

		if (panel) {
			// 透明の状態を一度確定させてから付ける（付けたときに 0 → 1 のフェードになる）。
			void window.getComputedStyle(panel).opacity;
			panel.classList.add('is-shown');
		}
	}

	function onClose(dialog) {
		var panel = dialog.querySelector('[data-recruit-modal-panel]');

		if (panel) {
			panel.classList.remove('is-shown');
		}

		document.body.classList.remove(OPEN_CLASS);

		if (opener) {
			opener.focus({ preventScroll: true });
			opener = null;
		}
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('[data-recruit-modal-open]');

		if (!button) {
			return;
		}

		var dialog = find(button.getAttribute('data-recruit-modal-open'));

		if (dialog) {
			open(dialog, button);
		}
	});

	dialogs.forEach(function (dialog) {
		// × か、パネルの外（= dialog 自体。暗幕の見えている部分）を押したら閉じる。
		dialog.addEventListener('click', function (event) {
			if (event.target === dialog || event.target.closest('[data-recruit-modal-close]')) {
				dialog.close();
			}
		});

		// Esc は dialog の既定の動き（cancel → close）。後始末は close でまとめる。
		dialog.addEventListener('close', function () {
			onClose(dialog);
		});

		// Tab はモーダル内で循環させる（フォーカスできるのは × だけなので × に留める）。
		dialog.addEventListener('keydown', function (event) {
			if (event.key !== 'Tab') {
				return;
			}

			var items = Array.prototype.slice.call(dialog.querySelectorAll('a[href], button:not([disabled])'));

			if (!items.length) {
				return;
			}

			var first = items[0];
			var last = items[items.length - 1];

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});
	});
})();
