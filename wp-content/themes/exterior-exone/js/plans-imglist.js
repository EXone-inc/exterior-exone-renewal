/**
 * PLANS: PACKAGE PLAN の img list（[data-plp-imglist]）を右から左へ流し続ける。
 *
 * 列を丸ごと複製して並べ、1 巡ぶん（元の 5 枚 + 間隔 5 つ）動いたら頭に戻す
 * CSS アニメーションにする（継ぎ目は複製の同じ位置なので見えない）。
 * 速さは「画面幅 1 つ分を流れる時間」（--plp-imglist-screen-time）で PC / SP を揃え、
 * 1 巡の時間は 1 巡の長さに比例させる。
 *
 * 詳細が非表示（display: none）の間は測れないので止めておき、表示されたら
 * （ResizeObserver / plp:planchange）測り直して動かし始める。
 * 動きを減らす設定・JS 無効時は複製せず、カンプの並びのまま静止。
 */
(function () {
	'use strict';

	var row = document.querySelector('[data-plp-imglist]');

	if (!row || !row.parentElement) {
		return;
	}

	var frame = row.parentElement;
	var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
	var originals = Array.prototype.slice.call(row.children);
	var clones = [];
	var lastKey = '';

	if (!originals.length) {
		return;
	}

	function clearClones() {
		clones.forEach(function (item) {
			item.remove();
		});
		clones = [];
	}

	function stop() {
		row.classList.remove('is-running');
		row.style.removeProperty('--plp-imglist-shift');
		row.style.removeProperty('--plp-imglist-duration');
		clearClones();
		lastKey = '';
	}

	function screenTime() {
		var value = parseFloat(window.getComputedStyle(row).getPropertyValue('--plp-imglist-screen-time'));

		return value > 0 ? value : 40;
	}

	function update() {
		if (motion.matches) {
			stop();
			return;
		}

		var width = frame.clientWidth;

		if (!width) {
			// 非表示の間は動かさない（表示されたらまた呼ばれる）。
			return;
		}

		// 1 巡の長さ = 複製 1 つ目の先頭の位置（元の列の幅 + 間隔 1 つ）。
		// 最低 1 組は複製して測る。
		if (!clones.length) {
			appendSet();
		}

		// 小数まで測る（offsetLeft の整数丸めだと継ぎ目で最大 0.5px 飛ぶ）。
		var cycle = clones[0].getBoundingClientRect().left - originals[0].getBoundingClientRect().left;

		if (cycle <= 0) {
			return;
		}

		// 1 巡ずらしても画面の右端まで埋まるだけの組数にする（末尾に間隔が無いぶんも含めて実測。
		// ちょうど画面幅になる PC は端数で細い隙間が出ないよう 1 組余分に足す）。
		var guard = 0;

		while (row.getBoundingClientRect().width - cycle < width + 1 && guard < 10) {
			appendSet();
			guard += 1;
		}

		var key = width + ':' + cycle.toFixed(2);

		if (key === lastKey && row.classList.contains('is-running')) {
			return;
		}

		lastKey = key;
		row.style.setProperty('--plp-imglist-shift', cycle.toFixed(3) + 'px');
		row.style.setProperty('--plp-imglist-duration', (screenTime() * cycle / width).toFixed(3) + 's');
		row.classList.add('is-running');
	}

	function appendSet() {
		originals.forEach(function (item) {
			var copy = item.cloneNode(true);

			// 見た目のための複製なので支援技術からは隠す（列全体も aria-hidden）。
			copy.setAttribute('aria-hidden', 'true');
			copy.setAttribute('data-plp-imglist-clone', '');
			row.appendChild(copy);
			clones.push(copy);
		});
	}

	if ('ResizeObserver' in window) {
		new ResizeObserver(update).observe(frame);
	} else {
		window.addEventListener('resize', update);
	}

	document.addEventListener('plp:planchange', update);

	if (motion.addEventListener) {
		motion.addEventListener('change', update);
	}

	update();
})();
