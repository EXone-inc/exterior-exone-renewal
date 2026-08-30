/**
 * FV の動画スライダー。
 *
 * 5 本の動画をクロスフェードで切り替え、同じ添字のコピーとサムネイルを連動させる。
 * サムネイルの外周リングは動画の再生位置に合わせて一周し、進み具合がそのまま
 * 次のスライドまでの残り時間になる。円は CSS で -90deg 回してあるので 12 時から始まる。
 *
 * 動画は 9.03 秒 / 切り替えは 9 秒。リングが一周しきる直前に次へ移る。
 */
(function () {
	'use strict';

	var fv = document.querySelector('[data-fv-slider]');

	if (!fv) {
		return;
	}

	var videos = Array.prototype.slice.call(fv.querySelectorAll('[data-fv-video]'));
	var thumbs = Array.prototype.slice.call(fv.querySelectorAll('[data-fv-thumb]'));
	var texts = Array.prototype.slice.call(fv.querySelectorAll('[data-fv-text]'));

	if (!videos.length) {
		return;
	}

	var CHANGE_TIME = 9000; // 次のスライドへ移るまで（ms）
	var FADE_TIME = 1200; // クロスフェードの長さ（ms）。CSS の transition と揃える
	var RING_LENGTH = 251.327412; // 2πr（r=40）。SVG の viewBox 単位

	var current = 0;
	var timer = null;

	/**
	 * サムネイルの外周リングを ratio（0〜1）ぶんだけ描く。
	 */
	function drawRing(thumb, ratio, visible) {
		var bar = thumb.querySelector('.p-fv__thumb-progress-bar');

		if (!bar) {
			return;
		}

		bar.style.opacity = visible ? '1' : '0';
		bar.style.strokeDashoffset = String(RING_LENGTH * (1 - ratio));
	}

	function show(index) {
		current = index;

		videos.forEach(function (video, i) {
			var active = i === index;

			video.classList.toggle('is-active', active);

			if (active) {
				try {
					video.currentTime = 0;
				} catch (e) {
					// 読み込み前は巻き戻せないことがある。再生できれば問題ない。
				}

				var played = video.play();

				if (played && played.catch) {
					played.catch(function () {
						// 自動再生が拒否されても静止画として見えていれば足りる。
					});
				}
			} else {
				// フェードが終わってから止める。すぐ止めると消えかけの絵が固まる。
				window.setTimeout(function () {
					if (!video.classList.contains('is-active')) {
						video.pause();
					}
				}, FADE_TIME);
			}
		});

		texts.forEach(function (text) {
			text.classList.toggle('is-active', Number(text.getAttribute('data-fv-text')) === index);
		});

		thumbs.forEach(function (thumb, i) {
			var active = i === index;

			thumb.classList.toggle('is-active', active);
			thumb.setAttribute('aria-selected', active ? 'true' : 'false');
			drawRing(thumb, 0, active);
		});
	}

	function schedule() {
		window.clearInterval(timer);

		timer = window.setInterval(function () {
			show((current + 1) % videos.length);
		}, CHANGE_TIME);
	}

	function update() {
		var video = videos[current];
		var thumb = thumbs[current];

		if (video && thumb) {
			// メタデータ未取得の間は切り替え間隔を尺の代わりに使う。
			var duration = isFinite(video.duration) && video.duration > 0 ? video.duration : CHANGE_TIME / 1000;

			drawRing(thumb, Math.min(video.currentTime / duration, 1), true);
		}

		window.requestAnimationFrame(update);
	}

	thumbs.forEach(function (thumb, index) {
		thumb.addEventListener('click', function () {
			show(index);

			// 押した直後にすぐ切り替わらないよう、待ち時間を計り直す。
			schedule();
		});
	});

	show(0);
	schedule();
	window.requestAnimationFrame(update);
})();
