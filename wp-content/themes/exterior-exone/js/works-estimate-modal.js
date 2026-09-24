/**
 * WORKS: SP の見積モーダル（カンプ 439:993 / 439:1020-1062）。
 *
 * ・1024px 以下でだけ使う。PC は見積表がそのまま並ぶ（CSS 側で出し分け）
 * ・表の DOM は 1 つだけ。器（.p-cworks__sheet）ごと暗幕付きの表示に切り替える
 * ・開くと × にフォーカスを移し、閉じるとボタンへ戻す
 * ・閉じる操作は CLOSE / Esc / 暗幕（パネルの外）のクリック。
 *   暗幕の上に出るヘッダーを押したときも閉じてからそのクリックを通す
 * ・開いている間は背面をスクロールさせない（body に .is-works-modal-open）
 * ・JS が動いたときだけセクションに .is-modal-ready を付ける。付かないときは
 *   見積表がそのまま出る（表が読めなくなるのを避けるため）
 * ・PC 幅（1025px 以上）へリサイズしたら閉じる
 */
(function () {
	'use strict';

	var PC_QUERY = '(min-width: 1025px)';

	var sections = Array.prototype.slice.call(document.querySelectorAll('[data-works]'));

	if (!sections.length) {
		return;
	}

	sections.forEach(function (section) {
		var sheet = section.querySelector('[data-works-modal]');
		var panel = section.querySelector('[data-works-modal-panel]');
		var opener = section.querySelector('[data-works-modal-open]');

		if (!sheet || !panel || !opener) {
			return;
		}

		var closeButton = sheet.querySelector('[data-works-modal-close]');
		var isOpen = false;

		section.classList.add('is-modal-ready');

		/**
		 * パネル内のフォーカス可能な要素。
		 */
		function focusableItems() {
			return Array.prototype.slice.call(
				panel.querySelectorAll('a[href], button:not([disabled])')
			);
		}

		function open() {
			if (isOpen) {
				return;
			}

			isOpen = true;
			sheet.classList.add('is-open');
			document.body.classList.add('is-works-modal-open');
			opener.setAttribute('aria-expanded', 'true');

			// 開いている間だけダイアログとして扱う（PC ではただの見積表のため）。
			panel.setAttribute('role', 'dialog');
			panel.setAttribute('aria-modal', 'true');

			// 画面が低いとモーダルの中身が縦に収まらないので、必ず先頭から見せる。
			sheet.scrollTop = 0;

			// クリック直後はブラウザが押した要素へフォーカスを戻すため、次フレームで移す。
			// フォーカスで勝手にスクロールしないよう preventScroll を付ける。
			window.requestAnimationFrame(function () {
				if (closeButton) {
					closeButton.focus({ preventScroll: true });
				} else {
					panel.focus({ preventScroll: true });
				}

				sheet.scrollTop = 0;
			});
		}

		function close(returnFocus) {
			if (!isOpen) {
				return;
			}

			isOpen = false;
			sheet.classList.remove('is-open');
			document.body.classList.remove('is-works-modal-open');
			opener.setAttribute('aria-expanded', 'false');
			panel.removeAttribute('role');
			panel.removeAttribute('aria-modal');

			if (returnFocus) {
				opener.focus();
			}
		}

		opener.setAttribute('aria-expanded', 'false');
		opener.addEventListener('click', open);

		// CLOSE か、パネルの外（= 暗幕の見えている部分）を押したら閉じる。
		// どちらでもフォーカスは開いたボタンへ戻す。
		sheet.addEventListener('click', function (event) {
			if (
				event.target.closest('[data-works-modal-close]') ||
				!event.target.closest('[data-works-modal-panel]')
			) {
				close(true);
			}
		});

		// ヘッダーはカンプどおり暗幕の上に出るので押せてしまう。そのままだと
		// ドロワーとモーダルが二重に開くため、ヘッダーを押したらモーダルを閉じて
		// からクリックを通す（捕捉フェーズで先に閉じる。フォーカスは戻さない）。
		document.addEventListener(
			'click',
			function (event) {
				var target = event.target;

				if (!isOpen || !target || typeof target.closest !== 'function') {
					return;
				}

				if (target.closest('.p-header')) {
					close(false);
				}
			},
			true
		);

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

			// 開いている間はフォーカスをパネル内に閉じ込める。
			var items = focusableItems();

			if (!items.length) {
				return;
			}

			var first = items[0];
			var last = items[items.length - 1];

			if (!panel.contains(document.activeElement)) {
				event.preventDefault();
				first.focus();

				return;
			}

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});

		// PC 幅ではモーダルを使わないため、跨いだら閉じる。
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
	});
})();
