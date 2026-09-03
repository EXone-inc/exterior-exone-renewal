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

	var pc = window.matchMedia('(min-width: 1025px)');
	var current = '';
	var inView = false;
	var retryArmed = false;

	// 低電力モード等で play() が拒否されたら、最初のタッチで再生し直す
	//（ユーザー操作の直後なら許可される）。仕組みは js/fv-slider.js と同じ。
	function armRetry() {
		if (retryArmed) {
			return;
		}

		retryArmed = true;

		var retry = function () {
			window.removeEventListener('touchstart', retry);
			window.removeEventListener('pointerdown', retry);
			retryArmed = false;
			play();
		};

		window.addEventListener('touchstart', retry, { passive: true });
		window.addEventListener('pointerdown', retry);
	}

	function play() {
		if (!inView || !current) {
			return;
		}

		var played = video.play();

		if (played && played.catch) {
			played.catch(function () {
				// 拒否されても下の画像が見えている（is-ready は playing まで付かない）。
				// 初回タッチで再挑戦する。
				armRetry();
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

	// canplay だと「読み込めたが再生はブロック」の状態でも動画が画像に被さるため、
	// 実際に再生が始まってから前面に出す。
	video.addEventListener('playing', function () {
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
