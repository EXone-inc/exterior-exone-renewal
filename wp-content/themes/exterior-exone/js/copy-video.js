/**
 * コピーセクションの背景動画。
 *
 * PC（1920x1080）と SP（1080x1920）で縦横比の違う動画があるため、
 * 幅を見て必要な方だけを読み込む。両方をマークアップに置くと通信が
 * 二重になるので、src は JS が入れる。
 *
 * 読めるようになったら .is-ready を付け、下に敷いてある画像の上に重ねる。
 * 画面外にある間は止めておく（見えない動画を回し続けないため）。
 */
(function () {
	'use strict';

	var video = document.querySelector('[data-copy-video]');

	if (!video) {
		return;
	}

	var pc = window.matchMedia('(min-width: 769px)');
	var current = '';
	var inView = false;

	function play() {
		if (!inView || !current) {
			return;
		}

		var played = video.play();

		if (played && played.catch) {
			played.catch(function () {
				// 自動再生が拒否されても、下の画像が見えていれば足りる。
			});
		}
	}

	/**
	 * 幅に合う動画を読み込む。同じものなら何もしない。
	 */
	function load() {
		var next = video.getAttribute(pc.matches ? 'data-src-pc' : 'data-src-sp');

		if (!next || next === current) {
			return;
		}

		current = next;
		video.classList.remove('is-ready');
		video.src = next;
		video.load();
		play();
	}

	video.addEventListener('canplay', function () {
		video.classList.add('is-ready');
	});

	// 画面外では止める。入ったら読み込みと再生をまとめて行う。
	if (typeof window.IntersectionObserver === 'function') {
		new window.IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				inView = entry.isIntersecting;

				if (inView) {
					load();
					play();
				} else {
					video.pause();
				}
			});
		}, { rootMargin: '200px' }).observe(video);
	} else {
		inView = true;
		load();
	}

	if (typeof pc.addEventListener === 'function') {
		pc.addEventListener('change', load);
	} else if (typeof pc.addListener === 'function') {
		pc.addListener(load);
	}
})();
