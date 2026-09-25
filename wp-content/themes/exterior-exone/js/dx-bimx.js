/**
 * DX EXPERIENCE ページ: ステップ 03（BIMx）の 3D モデル。
 *
 * ・03 に近づいたらライブラリ（js/vendor/model-viewer）とモデル
 *   （models/dx/bimx-house.glb）を読み込み、読み込めたら 3D をフェードで出す
 *   （.is-model-ready）。静止画は最初から隠しておき（.is-model-mode）、
 *   読み込みに失敗したときだけ戻す
 * ・3D の建物は静止画と同じ大きさ・位置で始まる。回って横幅が広がる角度では
 *   枠の左右からはみ出して見えてよい（枠で切らない。css/dx.css）
 * ・初期の画角は静止画と同じ。そこからゆっくり回し続ける（操作は受け付けない）
 * ・03 を表示していない間（ステップ切り替えで隠れている間）は回転を止める
 * ・動きを減らす設定の端末と、読み込みに失敗したときは静止画のまま
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

	if (reduced.matches || typeof window.IntersectionObserver !== 'function') {
		return;
	}

	var step = visual.closest('[data-dx-step]');
	var viewer = null;
	var started = false;

	// 3D を出す前提なので、静止画は最初から出さない（一瞬見えてから入れ替わるのを避ける）。
	// 読み込みに失敗したら外して静止画に戻す。
	visual.classList.add('is-model-mode');

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

		return new Promise(function (resolve, reject) {
			var script = document.createElement('script');

			script.src = src;
			script.async = true;
			script.onload = resolve;
			script.onerror = reject;
			document.head.appendChild(script);
		}).then(function () {
			return window.customElements.whenDefined('model-viewer');
		});
	}

	/**
	 * いま 03 が見えているか。ステップ切り替え（js/dx-steps.js）が動いていないとき
	 * （縦に並ぶ表示）は常に見えている扱い。
	 */
	function isShown() {
		return !step || !step.hasAttribute('inert');
	}

	function syncRotation() {
		if (viewer) {
			viewer.autoRotate = isShown() && !reduced.matches;
		}
	}

	function start() {
		if (started) {
			return;
		}

		started = true;

		loadLibrary(visual.getAttribute('data-dx-model-viewer')).then(function () {
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

			// 読み込み直後はカメラが指定の画角へ寄っていくので、先に飛ばしてから見せる。
			viewer.addEventListener('load', function () {
				// 画角の反映は load の次の描画で行われるため、1 フレーム待ってから飛ばす。
				window.requestAnimationFrame(function () {
					viewer.jumpCameraToGoal();
					window.requestAnimationFrame(function () {
						visual.classList.add('is-model-ready');
						syncRotation();
					});
				});
			});

			// 読み込めなければ静止画に戻す。
			viewer.addEventListener('error', function () {
				if (viewer && viewer.parentNode) {
					viewer.parentNode.removeChild(viewer);
				}

				viewer = null;
				fallback();
			});

			viewer.src = visual.getAttribute('data-dx-model');
			visual.appendChild(viewer);
			syncRotation();
		}).catch(function () {
			// ライブラリを読めなければ静止画に戻す。
			fallback();
		});
	}

	// 03 の少し手前（固定区間に入る前）から読み込み始める。
	var observer = new window.IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (entry.isIntersecting) {
				observer.disconnect();
				start();
			}
		});
	}, { rootMargin: '1200px 0px' });

	observer.observe(step || visual);

	// ステップが切り替わって 03 が隠れたら止め、出てきたら回す。
	if (step && typeof window.MutationObserver === 'function') {
		new window.MutationObserver(syncRotation).observe(step, {
			attributes: true,
			attributeFilter: ['inert'],
		});
	}

	// 動きを減らす設定に切り替わったら止める。
	if (typeof reduced.addEventListener === 'function') {
		reduced.addEventListener('change', syncRotation);
	}
})();
