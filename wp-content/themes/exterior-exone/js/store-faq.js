/**
 * 支店ページ: FAQ のアコーディオン（カンプ Group 372 / 669:963）。
 *
 * ・質問行（button）を押すと回答が開き、開くのは 1 つだけ
 * ・もう一度押すと閉じる
 * ・button なので Enter / Space でも同じ操作ができる
 * ・回答は hidden 属性で出し入れし、開くときだけ CSS 側でフェード + スライドさせる
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-sfaq]');

	if (!root) {
		return;
	}

	var triggers = root.querySelectorAll('[data-sfaq-trigger]');

	/**
	 * @param {Element} trigger 質問行のボタン。
	 * @param {boolean} isOpen  開くなら true。
	 */
	function toggle(trigger, isOpen) {
		var answer = document.getElementById(trigger.getAttribute('aria-controls'));

		trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		trigger.classList.toggle('is-open', isOpen);

		if (!answer) {
			return;
		}

		if (isOpen) {
			answer.hidden = false;
		} else {
			answer.hidden = true;
		}
	}

	root.addEventListener('click', function (event) {
		var trigger = event.target.closest('[data-sfaq-trigger]');

		if (!trigger || !root.contains(trigger)) {
			return;
		}

		var willOpen = trigger.getAttribute('aria-expanded') !== 'true';

		// 開くのは 1 つだけなので、いったん全部閉じる。
		Array.prototype.forEach.call(triggers, function (item) {
			toggle(item, false);
		});

		if (willOpen) {
			toggle(trigger, true);
		}
	});
})();
