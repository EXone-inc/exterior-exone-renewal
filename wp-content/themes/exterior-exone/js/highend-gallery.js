/**
 * ハイエンドページ Gallery: パンフレットのめくり・自動送り・タブ・クリック・進捗下線。
 *
 * 対象: [data-hep-gallery]（.p-hepgal__book）。1 ページに複数あっても独立に動く。
 * カンプ: PC 713:164 / SP 733:380〜404 / guide 718:245
 * 仕様書: docs/spec-20261005-highend-page.md F-07（決定 §3「Gallery」・§6・§7）
 *
 * - めくり: 左ページを細い短冊に分け、綴じ目（右端）を軸に外縁ほど角度を遅らせて回し、
 *   紙がしなるように見せる（ユーザー支給の参考コードの方式。決定 §7）。
 *   明暗は短冊の中身に filter で掛け、短冊どうしの重なりに暗さが二重に乗らないようにする。
 * - 自動送り: めくり終わり（初回は画面に入った時点）から --hep-gal-interval 表示してから次へ。
 *   画面外・タブ非表示の間は止め、戻ったら 0 から計り直す。進捗下線を 0 → 1 で伸ばす。
 * - 動きを減らす設定: めくらずクロスフェード（CSS）、自動送りなし、下線は全幅。
 * - 検証用: めくり中は root に data-hep-flip="next|prev" と data-hep-progress（0 = 左に平ら、
 *   1 = 右へ倒れきった状態。次へは 0 → 1、前へは 1 → 0）を付ける。
 */
(function () {
	'use strict';

	var STRIPS = 48; // 短冊の数（参考コードと同じ）
	var OVERLAP = 10; // 短冊を外縁側へ重ねる幅（px）。真横向きに近い短冊では重なりの見かけが縮むので広めに取る
	var BEND = 0.56; // 外縁の遅れ（rad。参考コード）
	var BEND_POW = 0.85;
	var SHADE_FRONT = 0.28; // 表の外縁ほど暗くする最大量（参考コード）
	var SHADE_BACK = 0.19; // 裏の綴じ目側ほど暗くする最大量
	var CAST = 0.65; // 落ち影の最大の濃さ
	var VIEW_RATIO = 0.2; // この割合が画面に入ったら「画面に入った」とみなす
	var reducedQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

	// CSS の時間トークン（"5s" / "400ms"）を ms にする。
	function readTime(name, fallback) {
		var value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
		var num = parseFloat(value);

		if (isNaN(num)) {
			return fallback;
		}

		return /ms$/.test(value) ? num : num * 1000;
	}

	// smootherstep（参考コード）
	function ease(p) {
		return p * p * p * (p * (p * 6 - 15) + 10);
	}

	function cssUrl(src) {
		return 'url("' + String(src).replace(/["\\\n]/g, '\\$&') + '")';
	}

	function ready(img) {
		if (!img || (img.complete && img.naturalWidth > 0) || typeof img.decode !== 'function') {
			return Promise.resolve();
		}

		return img.decode().catch(function () {});
	}

	function setup(root) {
		var section = root.closest('.p-hepgal') || root.parentNode;
		var leftPhotos = root.querySelectorAll('.p-hepgal__page--left .p-hepgal__photo');
		var rightPhotos = root.querySelectorAll('.p-hepgal__page--right .p-hepgal__photo');
		var leaf = root.querySelector('.p-hepgal__leaf');
		var leftPage = root.querySelector('.p-hepgal__page--left');
		var cast = root.querySelector('.p-hepgal__cast');
		var hitNext = root.querySelector('.p-hepgal__hit--next');
		var hitPrev = root.querySelector('.p-hepgal__hit--prev');
		var progress = section.querySelector('.p-hepgal__progress');
		var tabs = Array.prototype.slice.call(section.querySelectorAll('.p-hepgal__tab'));
		var texts = {
			num: section.querySelector('[data-hep-text="num"]'),
			eng: section.querySelector('[data-hep-text="eng"]'),
			copy: section.querySelector('[data-hep-text="copy"]')
		};
		var count = leftPhotos.length;

		if (count < 2 || rightPhotos.length !== count || !leaf || !leftPage || tabs.length !== count) {
			return;
		}

		var intervalMs = readTime('--hep-gal-interval', 5000);
		var flipMs = readTime('--hep-gal-flip', 1200);
		var fadeMs = readTime('--hep-gal-text-fade', 400);
		var crossMs = readTime('--hep-gal-crossfade', 400);

		var current = 0;
		var busy = false; // めくり〜文字のフェードインが終わるまで操作を受けない
		var inView = false;
		var raf = 0;
		var timerStart = -1; // 自動送りの計測開始時刻（-1 = 止まっている）
		var flip = null; // { dir, from, to, target, start, fallback }
		var strips = []; // { el, front, back, edge, width }
		var leafWidth = 0;

		function reduced() {
			return reducedQuery.matches;
		}

		function next(index) {
			return (index + 1) % count;
		}

		function prev(index) {
			return (index - 1 + count) % count;
		}

		function show(list, index) {
			Array.prototype.forEach.call(list, function (img, i) {
				var on = i === index;
				img.classList.toggle('is-current', on);

				if (list === leftPhotos) {
					if (on) {
						img.removeAttribute('aria-hidden');
					} else {
						img.setAttribute('aria-hidden', 'true');
					}
				}
			});
		}

		function photo(index) {
			var img = leftPhotos[index];
			return img.currentSrc || img.src;
		}

		function setProgress(value) {
			if (progress) {
				progress.style.transform = value >= 1 ? '' : 'scaleX(' + Math.max(0, value).toFixed(4) + ')';
			}
		}

		function fadeText(out) {
			Object.keys(texts).forEach(function (key) {
				if (texts[key]) {
					texts[key].classList.toggle('is-fading', out);
				}
			});
		}

		function writeText(index) {
			var tab = tabs[index];

			if (texts.num) {
				texts.num.textContent = tab.getAttribute('data-num');
			}
			if (texts.eng) {
				texts.eng.textContent = tab.getAttribute('data-eng');
			}
			if (texts.copy) {
				texts.copy.textContent = tab.getAttribute('data-copy');
			}
		}

		function pressTab(index) {
			tabs.forEach(function (tab, i) {
				tab.setAttribute('aria-pressed', i === index ? 'true' : 'false');
			});
		}

		// ---- 短冊（初回のめくりの直前に 1 度だけ作る。以後は写真の差し替えだけ） ----
		function build() {
			var frag = document.createDocumentFragment();

			for (var i = 0; i < STRIPS; i++) {
				var el = document.createElement('div');
				var front = document.createElement('div');
				var back = document.createElement('div');
				var frontImg = document.createElement('div');
				var backImg = document.createElement('div');

				el.className = 'p-hepgal__strip';
				front.className = 'p-hepgal__leaf-front';
				back.className = 'p-hepgal__leaf-back';
				frontImg.className = 'p-hepgal__leaf-img';
				backImg.className = 'p-hepgal__leaf-img';
				front.appendChild(frontImg);
				back.appendChild(backImg);
				el.appendChild(front);
				el.appendChild(back);
				frag.appendChild(el);
				strips.push({ el: el, front: frontImg, back: backImg, edge: 0, width: 0 });
			}

			leaf.appendChild(frag);
		}

		// 短冊の位置と幅を決める。綴じ目からの境目は整数 px に丸め、外縁側へ OVERLAP だけ重ねる。
		function layout() {
			// めくれる 1 枚は待機中 display: none なので、同寸の左ページで測る
			leafWidth = leftPage.getBoundingClientRect().width;
			leaf.style.setProperty('--hep-leaf-w', leafWidth + 'px');

			strips.forEach(function (strip, i) {
				var inner = Math.round((i * leafWidth) / STRIPS);
				var outer = i === STRIPS - 1 ? leafWidth : Math.round(((i + 1) * leafWidth) / STRIPS);
				var overlap = Math.min(OVERLAP, Math.floor(leafWidth - outer)); // 外縁より外へは出さない
				var left = leafWidth - outer - overlap;

				strip.edge = inner;
				strip.width = outer - inner;
				strip.el.style.left = left + 'px';
				strip.el.style.width = strip.width + overlap + 'px';
				// 表は左ページの同じ位置、裏は右ページ（綴じ目から左揃え）の同じ位置を見せる
				strip.front.style.left = -left + 'px';
				strip.back.style.left = -inner + 'px';
			});
		}

		// progress: 0 = 左に平ら、1 = 綴じ目を軸に右へ倒れきった状態
		function draw(value) {
			var base = value * Math.PI;
			var lift = Math.sin(Math.PI * value);
			var bend = BEND * lift;
			var x = 0;
			var z = 0;

			strips.forEach(function (strip, i) {
				var u = (i + 0.5) / STRIPS;
				var theta = base - bend * Math.pow(u, BEND_POW);

				strip.el.style.transform = 'translate3d(' + (x + strip.edge).toFixed(3) + 'px,0,' + z.toFixed(3) + 'px) rotateY(' + theta.toFixed(5) + 'rad)';
				strip.front.style.filter = 'brightness(' + (1 - SHADE_FRONT * lift * u).toFixed(4) + ')';
				strip.back.style.filter = 'brightness(' + (1 - SHADE_BACK * lift * (1 - u)).toFixed(4) + ')';
				x -= strip.width * Math.cos(theta);
				z += strip.width * Math.sin(theta);
			});

			if (cast) {
				cast.style.opacity = (lift * CAST).toFixed(4);
				cast.style.transform = 'scaleX(' + (0.3 + 0.7 * lift).toFixed(4) + ')';
			}

			root.setAttribute('data-hep-progress', value.toFixed(3));
		}

		// ---- requestAnimationFrame は 1 本（めくりと進捗下線） ----
		function loop(now) {
			raf = 0;

			if (flip) {
				var t = Math.min((now - flip.start) / flipMs, 1);
				draw(flip.from + (flip.to - flip.from) * ease(t));

				if (t >= 1) {
					endFlip();
				}
			} else if (timerStart >= 0) {
				var elapsed = (now - timerStart) / intervalMs;

				if (elapsed >= 1) {
					setProgress(0);
					timerStart = -1;
					go(next(current), 'next');
				} else {
					setProgress(elapsed);
				}
			}

			if (flip || timerStart >= 0) {
				raf = requestAnimationFrame(loop);
			}
		}

		function kick() {
			if (!raf) {
				raf = requestAnimationFrame(loop);
			}
		}

		function canAuto() {
			return inView && !document.hidden && !reduced();
		}

		function startTimer() {
			if (!canAuto()) {
				timerStart = -1;
				return;
			}

			timerStart = performance.now();
			setProgress(0);
			kick();
		}

		function stopTimer() {
			timerStart = -1;
			setProgress(reduced() ? 1 : 0);
		}

		// ---- 切り替え ----
		function go(target, dir) {
			if (busy || target === current) {
				return;
			}

			busy = true;
			stopTimer();

			if (reduced()) {
				crossfade(target);
				return;
			}

			var from = current;
			var front = dir === 'next' ? from : target;
			var back = dir === 'next' ? next(target) : next(from);

			Promise.all([ready(leftPhotos[front]), ready(leftPhotos[back])]).then(function () {
				startFlip(from, target, dir, front, back);
			});
		}

		function startFlip(from, target, dir, front, back) {
			if (!strips.length) {
				build();
			}

			layout();
			leaf.style.setProperty('--hep-leaf-front', cssUrl(photo(front)));
			leaf.style.setProperty('--hep-leaf-back', cssUrl(photo(back)));

			flip = {
				dir: dir,
				from: dir === 'next' ? 0 : 1,
				to: dir === 'next' ? 1 : 0,
				target: target,
				start: performance.now()
			};

			// めくれる 1 枚の下から現れる側を先に替える
			if (dir === 'next') {
				show(leftPhotos, target);
			} else {
				show(rightPhotos, next(target));
			}

			draw(flip.from);
			root.setAttribute('data-hep-flip', dir);
			pressTab(target);
			fadeText(true);

			// rAF が止まる（タブ非表示など）ときも必ず後始末する
			flip.fallback = window.setTimeout(endFlip, flipMs + 300);
			kick();
		}

		function endFlip() {
			if (!flip) {
				return;
			}

			var done = flip;
			flip = null;
			window.clearTimeout(done.fallback);

			// めくれる 1 枚に隠れている側を替えてから隠す
			if (done.dir === 'next') {
				show(rightPhotos, next(done.target));
			} else {
				show(leftPhotos, done.target);
			}

			root.removeAttribute('data-hep-flip');
			root.removeAttribute('data-hep-progress');
			current = done.target;
			writeText(current);
			fadeText(false);
			startTimer();

			window.setTimeout(function () {
				busy = false;
			}, fadeMs);
		}

		// 動きを減らす設定: 写真は CSS のクロスフェード、文字は半分ずつで消えて出る
		function crossfade(target) {
			pressTab(target);
			show(leftPhotos, target);
			show(rightPhotos, next(target));
			fadeText(true);

			window.setTimeout(function () {
				current = target;
				writeText(target);
				fadeText(false);
			}, crossMs / 2);

			window.setTimeout(function () {
				busy = false;
			}, crossMs);
		}

		// ---- 操作 ----
		if (hitNext) {
			hitNext.addEventListener('click', function () {
				go(next(current), 'next');
			});
		}

		if (hitPrev) {
			hitPrev.addEventListener('click', function () {
				go(prev(current), 'prev');
			});
		}

		tabs.forEach(function (tab, index) {
			tab.addEventListener('click', function () {
				if (index !== current) {
					go(index, index > current ? 'next' : 'prev');
				}
			});
		});

		// ---- 画面内外・タブ表示・動きを減らす設定の切り替え ----
		function refresh() {
			if (busy) {
				// めくり終わりの startTimer() が判断する
				if (!canAuto()) {
					timerStart = -1;
				}
				return;
			}

			if (canAuto()) {
				if (timerStart < 0) {
					startTimer();
				}
			} else {
				stopTimer();
			}
		}

		if ('IntersectionObserver' in window) {
			new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						inView = entry.isIntersecting && entry.intersectionRatio >= VIEW_RATIO;
					});
					refresh();
				},
				{ threshold: [0, VIEW_RATIO] }
			).observe(root);
		} else {
			inView = true;
		}

		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				stopTimer();
			}
			refresh();
		});

		if (typeof reducedQuery.addEventListener === 'function') {
			reducedQuery.addEventListener('change', function () {
				stopTimer();
				refresh();
			});
		}

		if ('ResizeObserver' in window) {
			new ResizeObserver(function () {
				if (flip) {
					layout();
				}
			}).observe(leaf.parentNode);
		}

		stopTimer();
		refresh();
	}

	Array.prototype.forEach.call(document.querySelectorAll('[data-hep-gallery]'), setup);
})();
