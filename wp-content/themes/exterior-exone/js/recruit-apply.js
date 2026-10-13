/**
 * 採用ページ: 固定表示の「応募する」（a[data-recruit-apply="fixed"]）。
 *
 * 現サイト recruit.js の .rec-rink_btn.is-fixed と同じく、祖先の重なり順に左右されないよう body 直下へ移し、
 * Training（[data-section="recruit-training"]）の上端が画面の下端より上に来たら .is-visible を付ける（FV では出さない）。
 * FAQ（[data-section="recruit-faq"]）の下端が画面の下端より上に行ったら（= フッターが見え始めたら）外す
 *（ユーザー指示 2026-10-13「固定は FAQ セクションまで表示」。現サイトは最下部まで出していた）。
 * 見た目・フェードは css/recruit.css の .p-rcapply。モーダル・ドロワー中に隠すのも CSS（body のクラス）。
 */
(function () {
	'use strict';

	var button = document.querySelector('[data-recruit-apply="fixed"]');

	if (!button) {
		return;
	}

	var trigger = document.querySelector('[data-section="recruit-training"]');
	var last = document.querySelector('[data-section="recruit-faq"]');
	var VISIBLE_CLASS = 'is-visible';
	var raf = 0;

	document.body.appendChild(button);

	// Training が無いときは常に出す（現サイトのフォールバックと同じ）。
	if (!trigger) {
		button.classList.add(VISIBLE_CLASS);
		return;
	}

	function update() {
		raf = 0;
		var visible = trigger.getBoundingClientRect().top <= window.innerHeight;

		// FAQ の下端が画面の下端より上に来たら（フッターが見え始めたら）隠す
		if (visible && last) {
			visible = last.getBoundingClientRect().bottom >= window.innerHeight - 1; // 小数の丸め分の余裕
		}

		button.classList.toggle(VISIBLE_CLASS, visible);
	}

	function schedule() {
		if (!raf) {
			raf = window.requestAnimationFrame(update);
		}
	}

	window.addEventListener('scroll', schedule, { passive: true });
	window.addEventListener('resize', schedule);
	update();
})();
