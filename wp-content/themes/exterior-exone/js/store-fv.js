/**
 * 支店ページ: FV の写真スライダー（カンプ 535:838 / 561:46 / 561:47）。
 *
 * ・背景写真 5 枚をクロスフェードで切り替える
 * ・サムネイルを押すとその写真に切り替わり、待ち時間を計り直す
 * ・選択中のサムネイルの外周リングが、次の写真までの残り時間ぶん一周する
 *   （円は CSS で -90deg 回してあるので 12 時から始まる）
 *
 * TOP の FV（js/fv-slider.js）と同じ動きだが、こちらは動画ではなく写真なので
 * 再生位置ではなく経過時間でリングを進める。
 * 「動きを減らす」設定のときは自動送りせず、サムネイルの操作だけで切り替える。
 */
(function () {
	'use strict';

	var fv = document.querySelector('[data-sfv-slider]');

	if (!fv) {
		return;
	}

	var slides = Array.prototype.slice.call(fv.querySelectorAll('[data-sfv-slide]'));
	var thumbs = Array.prototype.slice.call(fv.querySelectorAll('[data-sfv-thumb]'));

	if (slides.length < 2 || thumbs.length !== slides.length) {
		return;
	}

	var CHANGE_TIME = 9000; // 次の写真へ移るまで（ms）。TOP の FV と同じ
	var RING_LENGTH = 251.327412; // 2πr（r=40）。CSS の --st-fv-ring-length と揃える

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	var current = 0;
	var startedAt = 0;
	var frame = null;

	/**
	 * @param {Element} thumb   対象のサムネイル。
	 * @param {number}  ratio   0〜1 の進み具合。
	 * @param {boolean} visible リングを見せるか。
	 */
	function drawRing(thumb, ratio, visible) {
		var bar = thumb.querySelector('.p-sfv__ring-bar');

		if (!bar) {
			return;
		}

		bar.style.opacity = visible ? '1' : '0';
		bar.style.strokeDashoffset = String(RING_LENGTH * (1 - ratio));
	}

	/**
	 * @param {number} index 表示する写真の番号。
	 */
	function show(index) {
		current = index;

		slides.forEach(function (slide, i) {
			slide.classList.toggle('is-active', i === index);
		});

		thumbs.forEach(function (thumb, i) {
			var active = i === index;

			thumb.classList.toggle('is-active', active);
			thumb.setAttribute('aria-pressed', active ? 'true' : 'false');
			drawRing(thumb, 0, active);
		});
	}

	function stop() {
		if (frame !== null) {
			window.cancelAnimationFrame(frame);
			frame = null;
		}
	}

	function tick(now) {
		var ratio = (now - startedAt) / CHANGE_TIME;

		if (ratio >= 1) {
			show((current + 1) % slides.length);
			startedAt = now;
			ratio = 0;
		}

		drawRing(thumbs[current], ratio, true);
		frame = window.requestAnimationFrame(tick);
	}

	/**
	 * 自動送りを頭から計り直す。「動きを減らす」設定では回さない。
	 */
	function schedule() {
		stop();

		if (reduced.matches) {
			drawRing(thumbs[current], 0, true);
			return;
		}

		startedAt = window.performance.now();
		frame = window.requestAnimationFrame(tick);
	}

	thumbs.forEach(function (thumb, index) {
		thumb.addEventListener('click', function () {
			show(index);
			schedule();
		});
	});

	show(0);
	schedule();

	// 設定を切り替えたら、その場で自動送りを止める／始める。
	if (typeof reduced.addEventListener === 'function') {
		reduced.addEventListener('change', schedule);
	} else if (typeof reduced.addListener === 'function') {
		reduced.addListener(schedule);
	}
})();
