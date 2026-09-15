/**
 * グローバルナビのドロップダウン（STORE → 支店一覧）。
 *
 * ホバーとキーボードフォーカスでの開閉は CSS（:hover / :focus-within）が担い、
 * ここではマウスホバーの無い環境（タブレット等のタッチ）向けにトリガーのクリックで
 * 開閉し、Esc と外側クリックで閉じる。状態は aria-expanded に持つ。
 */
(function () {
	'use strict';

	var triggers = Array.prototype.slice.call(document.querySelectorAll('[data-gnav-sub-trigger]'));

	if (!triggers.length) {
		return;
	}

	function setOpen(trigger, open) {
		trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	function closeAll(except) {
		triggers.forEach(function (trigger) {
			if (trigger !== except) {
				setOpen(trigger, false);
			}
		});
	}

	triggers.forEach(function (trigger) {
		trigger.addEventListener('click', function () {
			var open = trigger.getAttribute('aria-expanded') === 'true';

			closeAll(trigger);
			setOpen(trigger, !open);
		});
	});

	document.addEventListener('keydown', function (event) {
		if (event.key !== 'Escape') {
			return;
		}

		var openTrigger = triggers.filter(function (trigger) {
			return trigger.getAttribute('aria-expanded') === 'true';
		})[0];

		if (openTrigger) {
			closeAll();
			openTrigger.focus();
		}
	});

	document.addEventListener('click', function (event) {
		if (!event.target.closest('.p-gnav__list__item--has-sub')) {
			closeAll();
		}
	});
})();
