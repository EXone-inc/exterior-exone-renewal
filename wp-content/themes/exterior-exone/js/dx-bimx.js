/**
 * DX EXPERIENCE ページ: ステップ 03（BIMx）。PC は 3D、SP は事前描画した回転映像。
 * SP では model-viewer / GLB を読み込まず、03 が画面に入り始めたら映像を再生する。
 *
 * ・03 に近づいたらライブラリ（js/vendor/model-viewer）とモデル
 *   （models/dx/bimx-house.glb）を読み込み、読み込めたら 3D をフェードで出す
 *   （.is-model-ready）。静止画は最初から隠しておき（.is-model-mode）、
 *   読み込みに失敗したときだけ戻す
 * ・3D の建物は静止画と同じ大きさ・位置で始まる。回って横幅が広がる角度では
 *   枠の左右からはみ出して見えてよい（枠で切らない。css/dx.css）
 * ・初期の画角は静止画と同じ。そこからゆっくり回し続ける（操作は受け付けない）
 * ・03 を表示していない間（ステップ切り替えで隠れている間）は回転を止める
 * ・動きを減らす設定の端末では、3D は表示したまま自動では回さず、指やマウスで
 *   ドラッグすれば回せるようにする（勝手に動くものだけ止める。2026-09-25）
 * ・読み込みに失敗したときは静止画に戻す
 *
 * 画角・回転の速さ・ファイルの場所は inc/dx-data.php が出典で、
 * template-parts/dx/section-steps.php の data-dx-model-* から受け取る。
 */
(function () {
	'use strict';

	var visual = document.querySelector('[data-dx-model]');

	if (!visual) {
		return;
	}

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	var mobile = window.matchMedia('(max-width: 1024px)');

	if (typeof window.IntersectionObserver !== 'function') {
		return;
	}

	var step = visual.closest('[data-dx-step]');
	var viewer = null;
	var started = false;
	var generation = 0;
	var near = false;
	var visible = false;
	var video = null;
	var videoPoster = null;
	var videoFailed = false;
	var playPending = false;
	var retryTimer = 0;
	var retryCount = 0;
	var libraryPromise = null;

	// 3D を出す前提なので、静止画は最初から出さない（一瞬見えてから入れ替わるのを避ける）。
	// 読み込みに失敗したら外して静止画に戻す。
	if (!mobile.matches) visual.classList.add('is-model-mode');

	function fallback() {
		visual.classList.remove('is-model-mode', 'is-model-ready');
	}

	/**
	 * ライブラリを 1 回だけ読み込む。
	 */
	function loadLibrary(src) {
		if (window.customElements && window.customElements.get('model-viewer')) {
			return Promise.resolve();
		}
		if (libraryPromise) return libraryPromise;

		libraryPromise = new Promise(function (resolve, reject) {
			var script = document.createElement('script');

			script.src = src;
			script.async = true;
			script.onload = resolve;
			script.onerror = reject;
			document.head.appendChild(script);
		}).then(function () {
			return window.customElements.whenDefined('model-viewer');
		});
		return libraryPromise;
	}

	/**
	 * いま 03 が見えているか。ステップ切り替え（js/dx-steps.js）が動いていないとき
	 * （縦に並ぶ表示）は常に見えている扱い。
	 */
	function isShown() {
		return !step || !step.hasAttribute('inert');
	}

	function mayPlay() {
		return isShown() && visible && !document.hidden && !reduced.matches &&
			// On SP, do not miss the scroll gesture while waiting for transitionend.
			(mobile.matches || !step || !step.classList.contains('is-moving'));
	}

	function clearRetry(reset) {
		window.clearTimeout(retryTimer);
		retryTimer = 0;
		if (reset) retryCount = 0;
	}

	function retryPlayback() {
		// Retry a transient visibility/autoplay rejection, but never poll indefinitely.
		if (retryTimer || retryCount >= 3 || !video || !mobile.matches || !mayPlay()) return;
		var target = video;
		var delay = [250, 1000, 2000][retryCount++];
		retryTimer = window.setTimeout(function () {
			retryTimer = 0;
			if (video === target) syncRotation();
		}, delay);
	}

	function syncRotation() {
		if (viewer) viewer.autoRotate = !mobile.matches && mayPlay();
		if (!video) return;
		video.autoplay = mobile.matches && mayPlay();
		if (!video.autoplay) {
			clearRetry(true);
			video.pause();
			return;
		}
		if (!video.paused || playPending) return;
		video.muted = true;
		var playingVideo = video;
		playPending = true;
		var result = playingVideo.play();
		if (result && result.then) {
			result.then(function () {
				if (playingVideo !== video) { playingVideo.pause(); return; }
				playPending = false;
				if (!mobile.matches || !mayPlay()) playingVideo.pause();
				else if (playingVideo.paused) retryPlayback();
			}).catch(function (error) {
				if (playingVideo !== video) return;
				playPending = false;
				// A temporary rejection must not leave 03 waiting for a video tap.
				if (!error || error.name !== 'NotSupportedError') retryPlayback();
			});
		} else {
			playPending = false;
		}
	}

	function startVideo() {
		if (!near || videoFailed) return;
		if (!video) {
			video = document.createElement('video');
			video.className = 'p-dxstep__rotation-video';
			video.muted = true;
			video.defaultMuted = true;
			video.setAttribute('muted', '');
			video.loop = true;
			video.playsInline = true;
			video.setAttribute('playsinline', '');
			video.setAttribute('webkit-playsinline', '');
			video.autoplay = mayPlay();
			video.preload = 'auto';
			video.setAttribute('aria-hidden', 'true');
			video.setAttribute('tabindex', '-1');
			video.poster = visual.getAttribute('data-dx-model-mobile-poster');
			video.addEventListener('canplay', syncRotation);
			video.addEventListener('loadeddata', syncRotation);
			video.addEventListener('pause', function () {
				if (createdVideo === video) retryPlayback();
			});
			var createdVideo = video;
			video.addEventListener('playing', function () {
				if (createdVideo !== video) return;
				if (!mobile.matches || !mayPlay()) { createdVideo.pause(); return; }
				clearRetry(true);
				visual.classList.add('is-video-playing');
			});
			video.addEventListener('error', function () {
				if (createdVideo !== video) return;
				videoFailed = true;
				removeVideo();
			});
			visual.appendChild(video);
			// ネイティブ動画の再生ボタンや空白を出さず、開始前も建物を表示する。
			videoPoster = document.createElement('img');
			videoPoster.className = 'p-dxstep__rotation-poster';
			videoPoster.alt = '';
			videoPoster.src = video.poster;
			visual.appendChild(videoPoster);
			visual.classList.add('is-video-mode');
		}
		// 動きを減らす設定ならポスターだけ。映像のダウンロードもしない。
		if (reduced.matches && video.hasAttribute('src')) {
			video.autoplay = false;
			video.pause();
			visual.classList.remove('is-video-playing');
			video.removeAttribute('src');
			video.load();
		} else if (!reduced.matches && !video.hasAttribute('src')) {
			video.src = visual.getAttribute('data-dx-model-mobile-video');
			video.load();
		}
		syncRotation();
	}

	function removeVideo() {
		clearRetry(true);
		if (video) {
			video.autoplay = false;
			video.pause();
			video.removeAttribute('src');
			video.load();
			video.remove();
			video = null;
		}
		if (videoPoster) { videoPoster.remove(); videoPoster = null; }
		playPending = false;
		visual.classList.remove('is-video-mode', 'is-video-playing');
	}

	function syncMode() {
		if (mobile.matches) {
			generation += 1;
			started = false;
			if (viewer) { viewer.autoRotate = false; viewer.remove(); viewer = null; }
			visual.classList.remove('is-model-mode', 'is-model-ready', 'is-model-interactive');
			startVideo();
		} else {
			removeVideo();
			if (near) start();
			applyMotionPreference();
		}
	}

	/**
	 * 動きを減らす設定なら自動回転をやめ、ドラッグで回せるようにする（ズーム・平行移動は
	 * させない。縦スクロールは奪わない）。通常は自動回転のみで操作は受け付けない。
	 */
	function applyMotionPreference() {
		if (!viewer) {
			return;
		}

		viewer.cameraControls = reduced.matches;
		visual.classList.toggle('is-model-interactive', reduced.matches);
		syncRotation();
	}

	function start() {
		if (started || mobile.matches) {
			return;
		}

		started = true;
		var token = ++generation;
		visual.classList.add('is-model-mode');

		loadLibrary(visual.getAttribute('data-dx-model-viewer')).then(function () {
			if (mobile.matches || token !== generation) return;
			var ModelViewer = window.customElements.get('model-viewer');

			// Draco の展開ファイルは外部 CDN ではなくテーマ内のものを使う。
			ModelViewer.dracoDecoderLocation = visual.getAttribute('data-dx-model-draco');

			viewer = document.createElement('model-viewer');
			viewer.className = 'p-dxstep__model';
			viewer.setAttribute('aria-hidden', 'true');
			viewer.setAttribute('interaction-prompt', 'none');
			viewer.setAttribute('disable-zoom', '');
			viewer.setAttribute('disable-pan', '');
			viewer.setAttribute('disable-tap', '');
			viewer.setAttribute('tabindex', '-1');
			viewer.setAttribute('shadow-intensity', '0.6');
			viewer.setAttribute('exposure', '0.8');
			// 画角を寄せられるよう、既定の距離・画角の制限を外す。
			viewer.setAttribute('min-camera-orbit', 'auto auto 1m');
			viewer.setAttribute('max-camera-orbit', 'auto auto 200m');
			viewer.setAttribute('min-field-of-view', '10deg');
			viewer.setAttribute('max-field-of-view', '90deg');
			viewer.setAttribute('camera-orbit', visual.getAttribute('data-dx-model-orbit'));
			viewer.setAttribute('field-of-view', visual.getAttribute('data-dx-model-fov'));
			viewer.setAttribute('camera-target', visual.getAttribute('data-dx-model-target'));
			viewer.setAttribute('rotation-per-second', visual.getAttribute('data-dx-model-rotation'));
			viewer.setAttribute('auto-rotate-delay', '0');
			viewer.setAttribute('auto-rotate', '');
			viewer.setAttribute('touch-action', 'pan-y');

			// 読み込み直後はカメラが指定の画角へ寄っていくので、先に飛ばしてから見せる。
			viewer.addEventListener('load', function () {
				// 画角の反映は load の次の描画で行われるため、1 フレーム待ってから飛ばす。
				window.requestAnimationFrame(function () {
					if (!viewer || token !== generation) return;
					viewer.jumpCameraToGoal();
					window.requestAnimationFrame(function () {
						if (!viewer || token !== generation) return;
						visual.classList.add('is-model-ready');
						applyMotionPreference();
					});
				});
			});

			// 読み込めなければ静止画に戻す。
			viewer.addEventListener('error', function () {
				if (token !== generation) return;
				if (viewer && viewer.parentNode) {
					viewer.parentNode.removeChild(viewer);
				}

				viewer = null;
				fallback();
			});

			viewer.src = visual.getAttribute('data-dx-model');
			visual.appendChild(viewer);
			applyMotionPreference();
		}).catch(function () {
			// ライブラリを読めなければ静止画に戻す。
			if (token === generation) fallback();
		});
	}

	// 固定区間に近づいた時点で SP 用映像か PC 用モデルの一方を準備する。
	var observer = new window.IntersectionObserver(function (entries) {
		if (entries.some(function (entry) { return entry.isIntersecting; })) {
			near = true;
			observer.disconnect();
			syncMode();
		}
	}, { rootMargin: '800px 0px' });
	observer.observe(visual.closest('[data-dx-steps]') || visual);

	new window.IntersectionObserver(function (entries) {
		visible = entries[0].isIntersecting;
		syncRotation();
	}).observe(visual);

	if (step && typeof window.MutationObserver === 'function') {
		new window.MutationObserver(syncRotation).observe(step, {
			attributes: true,
			attributeFilter: ['inert', 'class'],
		});
	}
	if (typeof reduced.addEventListener === 'function') {
		reduced.addEventListener('change', syncMode);
		mobile.addEventListener('change', syncMode);
	}
	document.addEventListener('visibilitychange', syncRotation);
	window.addEventListener('pageshow', syncRotation);
	// 通常のスクロール操作でも再試行する。動画自体へのタップは不要。
	document.addEventListener('touchstart', syncRotation, { passive: true });
	document.addEventListener('touchend', syncRotation, { passive: true });
	document.addEventListener('click', syncRotation);
	document.addEventListener('pointerdown', syncRotation, { passive: true });
})();
