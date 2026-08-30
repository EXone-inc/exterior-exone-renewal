/**
 * DX EXPERIENCE のステップ送り。
 *
 * ステップ・左の点・下のバー・右のビジュアルを 1 つの添字で束ねて切り替える。
 *
 * PC / SP とも、セクションをピン留めしてある区間のスクロール量で
 * 01 → 02 → 03 と進める。ステップを押すとその位置までスクロールする。
 * 見せ方だけ CSS で分けてあり、PC は 3 つ並べて現在地を点で示し、
 * SP はカンプどおり 1 件ずつ入れ替える。
 *
 * 動画は「セクションが画面に入っていて、かつ自分が表示中」のときだけ再生する。
 * 見えていない動画を回し続けないための制御で、参照実装 test.html と同じ。
 *
 * カンプ: 917:174 / 917:200（PC）、917:841 / 917:885-889（SP）
 */
(function () {
	'use strict';

	var section = document.querySelector('[data-dx-scroll]');
	var container = document.querySelector('[data-dx-slider]');

	if (!section || !container) {
		return;
	}

	var steps = Array.prototype.slice.call(container.querySelectorAll('[data-dx-step]'));
	var dots = Array.prototype.slice.call(container.querySelectorAll('.p-dx__dot'));
	var bars = Array.prototype.slice.call(section.querySelectorAll('.p-dx__bar'));
	var visuals = Array.prototype.slice.call(section.querySelectorAll('[data-dx-visual]'));

	if (!steps.length) {
		return;
	}

	var total = steps.length;
	var current = -1;
	var inView = false;
	var ticking = false;
	var lockUntil = 0;

	/**
	 * 表示中のビジュアルが動画なら再生、それ以外は止める。
	 */
	function syncVideos() {
		visuals.forEach(function (visual, i) {
			var video = visual.querySelector('video');

			if (!video) {
				return;
			}

			if (inView && i === current) {
				var played = video.play();

				if (played && played.catch) {
					played.catch(function () {
						// 自動再生が拒否されても静止画として見えていれば足りる。
					});
				}
			} else {
				video.pause();
			}
		});
	}

	function setActive(index) {
		index = Math.max(0, Math.min(total - 1, index));

		if (index === current) {
			return;
		}

		current = index;

		steps.forEach(function (step, i) {
			step.classList.toggle('is-active', i === index);
		});
		dots.forEach(function (dot, i) {
			dot.classList.toggle('is-active', i === index);
		});
		bars.forEach(function (bar, i) {
			bar.classList.toggle('is-active', i === index);
		});
		visuals.forEach(function (visual, i) {
			visual.classList.toggle('is-active', i === index);
		});

		syncVideos();
	}

	/**
	 * ピン留め区間のどこまで来たかを 0〜1 で出し、ステップ数で割り当てる。
	 */
	function readScroll() {
		ticking = false;

		// 押した直後はスクロールが落ち着くまで読み取らない。
		if (Date.now() < lockUntil) {
			return;
		}

		var runway = section.offsetHeight - window.innerHeight;

		if (runway <= 0) {
			return;
		}

		var progress = -section.getBoundingClientRect().top / runway;

		// 1 になった瞬間に範囲外の添字が出ないよう、わずかに手前で止める。
		progress = Math.max(0, Math.min(0.999, progress));

		setActive(Math.floor(progress * total));
	}

	function request() {
		if (ticking) {
			return;
		}

		ticking = true;
		window.requestAnimationFrame(readScroll);
	}

	/**
	 * ステップを押したとき、そのステップが出る位置までスクロールする。
	 */
	function scrollToStep(index) {
		var runway = section.offsetHeight - window.innerHeight;
		var top = window.scrollY + section.getBoundingClientRect().top + runway * (index / total) + 4;

		// 慣性で戻される前に見た目を合わせ、スクロールが落ち着くまで読み取りを止める。
		setActive(index);
		lockUntil = Date.now() + 650;
		window.scrollTo({ top: top, behavior: 'smooth' });
	}

	function sync() {
		// 添字を持ち越すと readScroll が同値で早期 return して見た目が揃わない。
		current = -1;
		readScroll();
	}

	steps.forEach(function (step, index) {
		step.addEventListener('click', function () {
			scrollToStep(index);
		});
	});

	// 見えていない動画は止めておく。
	if (typeof window.IntersectionObserver === 'function') {
		new window.IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				inView = entry.isIntersecting;
				syncVideos();
			});
		}, { threshold: 0.18 }).observe(section);
	} else {
		inView = true;
	}

	window.addEventListener('scroll', request, { passive: true });
	window.addEventListener('resize', sync);

	setActive(0);
	sync();
})();
