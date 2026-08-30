/**
 * ハンバーガーメニュー（カンプ 1036:395）の開閉。
 *
 * ・ヘッダーのボタンで開き、× / Esc / メニュー内リンクの押下で閉じる
 * ・開いている間は背面をスクロールさせない
 * ・PC 幅（769px 以上）へリサイズしたら閉じる（ドロワーは SP のみ）
 */
(function () {
	'use strict';

	var PC_QUERY = '(min-width: 769px)';

	var drawer = document.querySelector('[data-drawer]');
	var toggle = document.querySelector('.p-header__toggle');

	if (!drawer || !toggle) {
		return;
	}

	var closeButton = drawer.querySelector('[data-drawer-close]');
	var isOpen = false;

	/**
	 * ドロワー内のフォーカス可能な要素。
	 */
	function focusableItems() {
		return Array.prototype.slice.call(
			drawer.querySelectorAll('a[href], button:not([disabled])')
		);
	}

	function open() {
		if (isOpen) {
			return;
		}

		isOpen = true;
		drawer.classList.add('is-open');
		document.body.classList.add('is-drawer-open');
		toggle.setAttribute('aria-expanded', 'true');
		toggle.setAttribute('aria-label', 'メニューを閉じる');

		// クリック直後はブラウザが押した要素へフォーカスを戻すため、次フレームで移す。
		if (closeButton) {
			window.requestAnimationFrame(function () {
				closeButton.focus();
			});
		}
	}

	function close(returnFocus) {
		if (!isOpen) {
			return;
		}

		isOpen = false;
		drawer.classList.remove('is-open');
		document.body.classList.remove('is-drawer-open');
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-label', 'メニューを開く');

		if (returnFocus) {
			toggle.focus();
		}
	}

	toggle.addEventListener('click', function () {
		if (isOpen) {
			close(true);
		} else {
			open();
		}
	});

	if (closeButton) {
		closeButton.addEventListener('click', function () {
			close(true);
		});
	}

	// メニュー内のリンクを押したら閉じる（同一ページ内アンカーでも確実に閉じる）。
	drawer.addEventListener('click', function (event) {
		if (event.target.closest('a[href]')) {
			close(false);
		}
	});

	document.addEventListener('keydown', function (event) {
		if (!isOpen) {
			return;
		}

		if (event.key === 'Escape') {
			close(true);
			return;
		}

		if (event.key !== 'Tab') {
			return;
		}

		// 開いている間はフォーカスをドロワー内に閉じ込める。
		var items = focusableItems();

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

	// PC 幅ではドロワーを使わないため、跨いだら閉じる。
	var pcMedia = window.matchMedia(PC_QUERY);
	var onBreakpointChange = function (event) {
		if (event.matches) {
			close(false);
		}
	};

	if (typeof pcMedia.addEventListener === 'function') {
		pcMedia.addEventListener('change', onBreakpointChange);
	} else if (typeof pcMedia.addListener === 'function') {
		pcMedia.addListener(onBreakpointChange);
	}
})();
