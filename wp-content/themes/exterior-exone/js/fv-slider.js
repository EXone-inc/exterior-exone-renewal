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

	// --- 自動再生が拒否されたとき（iOS の低電力モード等）の静止画フォールバック ---
	// 拒否を検知したら動画に is-blocked を付けて隣のポスター画像を見せ、
	// 最初のタッチ（ユーザー操作の直後なら play() が許可される）で再生し直す。
	var posters = Array.prototype.slice.call(fv.querySelectorAll('[data-fv-poster]'));
	var retryArmed = false;

	// --- PC / SP で動画を出し分ける ---
	// 横長（1920x1080）を縦画面に cover で敷くと中央の細い帯を 2 倍以上に引き伸ばして
	// 荒れるため、SP は縦に切り出した *-sp.mp4（1080x1920）を読む。両方をマークアップに
	// 置くと通信が二重になるので、src は幅を見てここで入れる（js/copy-video.js と同じ）。
	var pc = window.matchMedia('(min-width: 1025px)');
	var sourceAttr = '';

	function applySources() {
		var attr = pc.matches ? 'data-src-pc' : 'data-src-sp';

		if (attr === sourceAttr) {
			return;
		}

		sourceAttr = attr;

		videos.forEach(function (video) {
			video.src = video.getAttribute(attr);
			video.load();
		});

		// 失敗検知で既に出しているポスターは、向きに合わせて差し替える。
		posters.forEach(function (poster) {
			if (poster.getAttribute('src')) {
				poster.setAttribute('src', poster.getAttribute(attr));
			}
		});
	}

	function markBlocked(video) {
		video.classList.add('is-blocked');

		// ポスターは失敗したときに初めて読み込む（通常時に余計な画像を落とさない）。
		posters.forEach(function (poster) {
			if (!poster.getAttribute('src')) {
				poster.setAttribute('src', poster.getAttribute(sourceAttr));
			}
		});

		armRetry();
	}

	function armRetry() {
		if (retryArmed) {
			return;
		}

		retryArmed = true;

		var retry = function () {
			window.removeEventListener('touchstart', retry);
			window.removeEventListener('pointerdown', retry);

			// 操作の直後に全部を一度起こし、表示中以外はすぐ止める。
			// 1 本ずつだと後のスライドで再びブロックされるため。
			videos.forEach(function (video, i) {
				var played = video.play();

				if (played && played.then) {
					played.then(function () {
						video.classList.remove('is-blocked');

						if (i !== current) {
							video.pause();
						}
					}).catch(function () {
						// まだ拒否されるなら、次の操作でもう一度試す。
						retryArmed = false;
						armRetry();
					});
				}
			});
		};

		window.addEventListener('touchstart', retry, { passive: true });
		window.addEventListener('pointerdown', retry);
	}

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
					played.then(function () {
						video.classList.remove('is-blocked');
					}).catch(function () {
						// 低電力モード等で拒否されたら静止画を出し、初回タッチで再挑戦する。
						markBlocked(video);
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

	applySources();
	show(0);
	schedule();
	window.requestAnimationFrame(update);

	// 1024px をまたいでリサイズ／回転したら、向きに合った動画へ入れ替える。
	var onMediaChange = function () {
		applySources();
		show(current);
		schedule();
	};

	if (typeof pc.addEventListener === 'function') {
		pc.addEventListener('change', onMediaChange);
	} else if (typeof pc.addListener === 'function') {
		pc.addListener(onMediaChange);
	}
})();
