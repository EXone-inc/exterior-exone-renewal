/**
 * ハンバーガーメニュー（カンプ 1036:395）の開閉。
 *
 * ・ヘッダーのボタンで開き、× / Esc / メニュー内リンクの押下で閉じる
 * ・開いている間は背面をスクロールさせない
 * ・PC 幅（1025px 以上）へリサイズしたら閉じる（ドロワーは SP のみ）
 */
(function () {
	'use strict';

	var PC_QUERY = '(min-width: 1025px)';

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

	/**
	 * @param {boolean} viaKeyboard キーボード操作で開いたか。
	 *   キーボードなら × にフォーカスを移して操作を続けやすくする。
	 *   タップで開いたときは × に移すと iOS Safari が :focus-visible の枠を
	 *   出してしまうため、パネル自体（tabindex="-1"）に移して枠を出さない。
	 *   Tab を押せばそこからメニュー内の先頭へ進めるので、キーボード操作は失わない。
	 */
	function open(viaKeyboard) {
		if (isOpen) {
			return;
		}

		isOpen = true;
		drawer.classList.add('is-open');
		document.body.classList.add('is-drawer-open');
		toggle.setAttribute('aria-expanded', 'true');
		toggle.setAttribute('aria-label', 'メニューを閉じる');

		// クリック直後はブラウザが押した要素へフォーカスを戻すため、次フレームで移す。
		window.requestAnimationFrame(function () {
			if (viaKeyboard && closeButton) {
				closeButton.focus();
			} else {
				drawer.focus({ preventScroll: true });
			}
		});
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

	// キーボード（Enter / Space）による click は detail が 0、ポインタ操作は 1 以上。
	// ポインタで閉じたときにボタンへフォーカスを戻すと、iOS Safari では
	// ハンバーガー側に枠が出るため、戻すのはキーボード操作のときだけにする。
	toggle.addEventListener('click', function (event) {
		var viaKeyboard = event.detail === 0;

		if (isOpen) {
			close(viaKeyboard);
		} else {
			open(viaKeyboard);
		}
	});

	if (closeButton) {
		closeButton.addEventListener('click', function (event) {
			close(event.detail === 0);
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
