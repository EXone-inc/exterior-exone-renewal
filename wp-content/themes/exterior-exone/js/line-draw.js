/**
 * ライン画（line_animation.svg）の線描画。TOP の理念セクションと企業情報の Why We Exist で共通。
 *
 * [data-line-draw] の付いたインライン SVG が画面に入ったら、Illustrator のグループ
 * line_01（敷地）→ 02（建物）→ 03（門柱・塀・フェンス）→ 04（植栽）の順に線を描く。
 * グループ内は 1 本ずつ --diagram-draw-step ずつ遅らせ、次のグループは前のグループの
 * 最後の線が描き始まってから（+ --diagram-draw-gap）始める。開始時刻は線数から計算する。
 *
 * 進み具合は SVG 自身のクラスで表す:
 *   .is-drawing … 描き始めた（CSS の animation が走る）
 *   .is-drawn   … 最後の線を描き終えた（続きの演出はこれを合図にする）
 * 各図形の pathLength="1" と、時間のトークン（--diagram-draw-*）は
 * functions.php の exterior_exone_inline_line_svg() と style.css を参照。
 * prefers-reduced-motion: reduce では描かずに最初から完成状態にする。
 */
(function () {
	'use strict';

	var targets = Array.prototype.slice.call(document.querySelectorAll('svg[data-line-draw]'));

	if (!targets.length) {
		return;
	}

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var GROUPS = ['line_01', 'line_02', 'line_03', 'line_04'];
	var SHAPES = 'path, line, polyline, polygon, rect, circle, ellipse';

	function readSeconds(el, name) {
		var value = getComputedStyle(el).getPropertyValue(name).trim();

		return value.slice(-2) === 'ms' ? parseFloat(value) / 1000 : parseFloat(value) || 0;
	}

	/**
	 * 各線に開始の遅れ（--delay）を振り、最後に描き終わる線を返す。
	 */
	function schedule(svg) {
		var duration = readSeconds(svg, '--diagram-draw-duration');
		var step = readSeconds(svg, '--diagram-draw-step');
		var gap = readSeconds(svg, '--diagram-draw-gap');
		var start = readSeconds(svg, '--diagram-draw-start');
		var lastEnd = -1;
		var lastEl = null;

		GROUPS.forEach(function (id) {
			var group = svg.querySelector('#' + id);

			if (!group) {
				return;
			}

			var shapes = Array.prototype.slice.call(group.querySelectorAll(SHAPES));

			shapes.forEach(function (el, index) {
				var delay = start + index * step;

				el.style.setProperty('--delay', delay.toFixed(3) + 's');

				if (delay + duration > lastEnd) {
					lastEnd = delay + duration;
					lastEl = el;
				}
			});

			// 次のグループは、このグループの最後の線が描き始まってから。
			if (shapes.length) {
				start += (shapes.length - 1) * step + gap;
			}
		});

		return lastEl;
	}

	function start(svg) {
		if (svg.classList.contains('is-drawing')) {
			return;
		}

		if (reduced) {
			svg.classList.add('is-drawing', 'is-drawn');

			return;
		}

		var lastEl = schedule(svg);

		svg.classList.add('is-drawing');

		if (!lastEl) {
			svg.classList.add('is-drawn');

			return;
		}

		lastEl.addEventListener('animationend', function () {
			svg.classList.add('is-drawn');
		}, { once: true });
	}

	if (reduced || !('IntersectionObserver' in window)) {
		targets.forEach(start);

		return;
	}

	// 画面に入ったら描き始める（しきい値は js/scroll-reveal.js と同じ）。
	var observer = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (!entry.isIntersecting) {
				return;
			}

			observer.unobserve(entry.target);
			start(entry.target);
		});
	}, { rootMargin: '0px 0px -10% 0px', threshold: 0.15 });

	targets.forEach(function (svg) {
		observer.observe(svg);
	});
})();
