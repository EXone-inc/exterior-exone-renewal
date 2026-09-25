/**
 * WORKS: 右列の写真・3D パースを押すと、左のメイン写真を切り替える（DX ページ）。
 *
 * template-parts/common/section-works.php の gallery => true のときだけ
 * [data-works-gallery] が付く。支店ページ（CPT の写真・投稿へのリンク）には効かない。
 *
 * ・右列のボタン [data-works-thumb] の URL をメインの <img> に入れ、フェードで切り替える
 * ・3D パースは透過部分があるので、メインの背景に空写真（data-works-thumb-backdrop）を
 *   敷く（.is-render）
 * ・3D パースのボタンに data-works-model があれば、メインに 3D モデルを出す。
 *   最初はパース画像と同じ画角で止まっていて、ドラッグで回せる（自動では回らない）。
 *   モデルは 4 枚目にマウスを乗せた・触れた・押したときに読み込み（ページを開いた
 *   だけでは読み込まない）、読み込めるまではパース画像の上にローディングを出す
 *   （.is-model-ready で入れ替え）。読み込めなければパース画像のまま
 * ・押したボタンに aria-pressed="true" / .is-current を付ける
 */
(function () {
	'use strict';

	var sections = Array.prototype.slice.call(document.querySelectorAll('[data-works-gallery]'));

	/**
	 * 3D 表示のライブラリ（js/vendor/model-viewer）を 1 回だけ読み込む。
	 * DX ページのステップ 03（js/dx-bimx.js）が先に読んでいれば、それを使う。
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

	sections.forEach(function (section) {
		var main = section.querySelector('[data-works-main]');
		var image = main ? main.querySelector('img') : null;
		var thumbs = Array.prototype.slice.call(section.querySelectorAll('[data-works-thumb]'));
		var viewer = null;

		if (!image || !thumbs.length) {
			return;
		}

		// srcset / sizes は切り替え先と合わないので外し、src だけで持つ。
		image.removeAttribute('srcset');
		image.removeAttribute('sizes');
		image.removeAttribute('loading');

		/**
		 * 3D モデルを読み込んでメインに置く（1 回だけ）。表示するかどうかは .is-model。
		 * 押す前（マウスを乗せた・触れた時点）から始めて、表示までの待ちを短くする。
		 */
		function prepareModel(thumb) {
			if (viewer) {
				return;
			}

			viewer = true; // 読み込み中の二重起動を防ぐ

			loadLibrary(thumb.getAttribute('data-works-model-viewer')).then(function () {
				// Draco の展開ファイルは外部 CDN ではなくテーマ内のものを使う。
				window.customElements.get('model-viewer').dracoDecoderLocation = thumb.getAttribute('data-works-model-draco');

				viewer = document.createElement('model-viewer');
				viewer.className = 'p-cworks__model';
				viewer.setAttribute('alt', thumb.getAttribute('aria-label') || '');
				// ドラッグで回すだけ。ズーム・平行移動はさせない（ページのスクロールを奪わない）。
				viewer.setAttribute('camera-controls', '');
				viewer.setAttribute('disable-zoom', '');
				viewer.setAttribute('disable-pan', '');
				viewer.setAttribute('disable-tap', '');
				viewer.setAttribute('interaction-prompt', 'none');
				viewer.setAttribute('touch-action', 'pan-y');
				viewer.setAttribute('shadow-intensity', '0.6');
				viewer.setAttribute('exposure', '0.9');
				viewer.setAttribute('camera-orbit', thumb.getAttribute('data-works-model-orbit'));
				viewer.setAttribute('field-of-view', thumb.getAttribute('data-works-model-fov'));
				viewer.setAttribute('camera-target', thumb.getAttribute('data-works-model-target'));
				// 最初の画角に寄せられるよう既定の制限を外し、真下からは覗けないようにする。
				viewer.setAttribute('min-camera-orbit', 'auto 45deg 1m');
				viewer.setAttribute('max-camera-orbit', 'auto 92deg 200m');
				viewer.setAttribute('min-field-of-view', '10deg');
				viewer.setAttribute('max-field-of-view', '90deg');

				// 読み込み直後はカメラが全体の見える位置から指定の画角へ寄っていくので、
				// 先に指定の画角へ飛ばしてから見せる（寄っていく動きを見せない）。
				viewer.addEventListener('load', function () {
					// 画角の反映は load の次の描画で行われるため、1 フレーム待ってから飛ばす。
					window.requestAnimationFrame(function () {
						viewer.jumpCameraToGoal();
						window.requestAnimationFrame(function () {
							main.classList.add('is-model-ready');
							main.setAttribute('aria-busy', 'false');
						});
					});
				});

				// 読み込めなければパース画像のまま。
				viewer.addEventListener('error', function () {
					main.classList.remove('is-model', 'is-model-ready');
					main.setAttribute('aria-busy', 'false');

					if (viewer && viewer.parentNode) {
						viewer.parentNode.removeChild(viewer);
					}

					viewer = null;
				});

				viewer.src = thumb.getAttribute('data-works-model');
				main.appendChild(viewer);
			}).catch(function () {
				viewer = null;
				main.classList.remove('is-model');
			});
		}

		function showModel(thumb) {
			main.classList.add('is-model');
			// 読み込み中はローディングを出す（CSS）。支援技術にも読み込み中を伝える。
			main.setAttribute('aria-busy', main.classList.contains('is-model-ready') ? 'false' : 'true');
			prepareModel(thumb);
		}

		function select(thumb) {
			var src = thumb.getAttribute('data-works-thumb');
			var backdrop = thumb.getAttribute('data-works-thumb-backdrop');
			var hasModel = thumb.hasAttribute('data-works-model');

			thumbs.forEach(function (item) {
				var current = item === thumb;

				item.classList.toggle('is-current', current);
				item.setAttribute('aria-pressed', current ? 'true' : 'false');
			});

			if (!hasModel) {
				main.classList.remove('is-model');
				main.setAttribute('aria-busy', 'false');
			}

			if (image.getAttribute('src') !== src) {
				main.classList.add('is-switching');

				// 先に読み込んでから入れ替える（読み込み途中の白い画像を見せない）。
				var next = new Image();

				next.onload = next.onerror = function () {
					image.src = src;
					main.classList.toggle('is-render', !!backdrop);
					main.style.backgroundImage = backdrop ? 'url("' + backdrop + '")' : '';
					window.requestAnimationFrame(function () {
						main.classList.remove('is-switching');
					});
				};
				next.src = src;
			}

			if (hasModel) {
				showModel(thumb);
			}
		}

		thumbs.forEach(function (thumb) {
			thumb.addEventListener('click', function () {
				select(thumb);
			});

			if (thumb.hasAttribute('data-works-model')) {
				['pointerenter', 'touchstart', 'focus'].forEach(function (type) {
					thumb.addEventListener(type, function () {
						prepareModel(thumb);
					}, { once: true, passive: true });
				});
			}
		});
	});
})();
