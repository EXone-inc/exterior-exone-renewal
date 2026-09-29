/**
 * DX EXPERIENCE ページ: ステップ 02（AI パース）のアニメーション。
 *
 * 支給 sample.html（AIパースアニメーション）の再現。
 * 暗い更地の写真 → 光る線画を line_01 → 02 → 03 の順に描く → 完成写真がぼかしから
 * 浮かび、線画は消える → 2 秒見せて最初から、を 02 を表示している間だけ繰り返す。
 * 02 に切り替わるたびに最初から始め、他のステップや画面外では止める。
 * 動きを減らす設定では完成写真を出したまま動かさない（CSS の既定表示）。
 *
 * 見た目（光る線・写真のフェード）は css/dx.css の .p-dxai。
 */
(function () {
	'use strict';

	var scene = document.querySelector('[data-dx-ai-pers]');

	if (!scene || typeof Element.prototype.animate !== 'function') {
		return;
	}

	var lines = scene.querySelector('.p-dxai__lines');
	var step = scene.closest('[data-dx-step]');
	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

	if (!lines) {
		return;
	}

	// サンプルと同じ時間（ms）。
	var BEFORE_HOLD = 1000; // 更地を見せる
	var PHOTO_FADE = 1400; // 完成写真が浮かぶ
	var AFTER_HOLD = 2000; // 完成写真を見せる
	var DRAW_END = 2800; // 描き始めから完成写真に切り替えるまで
	var GROUPS = ['line_01', 'line_02', 'line_03'];
	var STARTS = [120, 760, 1370];
	var DURATIONS = [1070, 950, 1170];
	var EASE = 'cubic-bezier(.3,.08,.32,1)';

	var groups = GROUPS.map(function (id) {
		return lines.querySelector('[id="' + id + '"]');
	}).filter(Boolean);

	var generation = 0;
	var timers = [];
	var animations = [];
	var running = false;
	var visible = false;

	function later(fn, delay) {
		timers.push(window.setTimeout(fn, delay));
	}

	function clear() {
		generation += 1;
		timers.forEach(window.clearTimeout);
		timers = [];
		animations.forEach(function (animation) {
			animation.cancel();
		});
		animations = [];
	}

	function shapes(root) {
		return Array.prototype.slice.call(root.querySelectorAll('path, line, polyline, polygon, rect'));
	}

	/**
	 * 更地の状態に戻し、少し見せてから描き始める。
	 */
	function reset(delay) {
		clear();

		var token = generation;

		scene.classList.remove('is-complete');
		scene.classList.add('is-playing');
		shapes(lines).forEach(function (el) {
			el.style.strokeDashoffset = '1';
		});
		later(function () {
			draw(token);
		}, delay);
	}

	function draw(token) {
		if (token !== generation) {
			return;
		}

		groups.forEach(function (group, index) {
			later(function () {
				if (token !== generation) {
					return;
				}

				shapes(group).forEach(function (el) {
					animations.push(el.animate(
						[{ strokeDashoffset: 1 }, { strokeDashoffset: 0 }],
						{ duration: DURATIONS[index], easing: EASE, fill: 'forwards' }
					));
				});
			}, STARTS[index]);
		});

		later(function () {
			if (token === generation) {
				scene.classList.add('is-complete');
			}
		}, DRAW_END);

		later(function () {
			if (token === generation) {
				reset(BEFORE_HOLD + PHOTO_FADE);
			}
		}, DRAW_END + PHOTO_FADE + AFTER_HOLD);
	}

	function start() {
		if (running) {
			return;
		}

		running = true;
		// 最初は更地から始める（完成写真から戻るフェードを見せない）。
		scene.classList.add('is-instant');
		reset(BEFORE_HOLD);
		void scene.offsetWidth;
		scene.classList.remove('is-instant');
	}

	function stop() {
		if (!running) {
			return;
		}

		running = false;
		clear();
		scene.classList.remove('is-playing', 'is-complete');
		shapes(lines).forEach(function (el) {
			el.style.strokeDashoffset = '';
		});
	}

	/**
	 * 02 が表示中（ステップ切り替えの inert が外れている）で、画面に入っていて、
	 * 動きを減らす設定でなければ動かす。
	 */
	function sync() {
		var shown = !step || !step.hasAttribute('inert');

		if (shown && visible && !reduced.matches) {
			start();
		} else {
			stop();
		}
	}

	if (typeof window.IntersectionObserver === 'function') {
		new window.IntersectionObserver(function (entries) {
			visible = entries[0].isIntersecting;
			sync();
		}, { threshold: 0.2 }).observe(scene);
	} else {
		visible = true;
	}

	if (step && typeof window.MutationObserver === 'function') {
		new window.MutationObserver(sync).observe(step, { attributes: true, attributeFilter: ['inert'] });
	}

	if (typeof reduced.addEventListener === 'function') {
		reduced.addEventListener('change', sync);
	}

	sync();
})();
