/**
 * WORKS の円弧（観覧車）カルーセル。
 *
 * カンプ（917:118）の扇状の並びを「大きな円の上端に沿ったカード」として
 * 実装する。隣のカードとの中心間隔と 1 枚あたりの回転角（917:119 実測）から
 * 円の半径を割り出し、リスト全体を回して 1 枚ずつ送る。
 * 動きは rara.ritsumei.ac.jp の FELLOWS セクションを踏襲:
 * - ドラッグ（PC/SP）で輪が回り、離すと最寄りのカードに吸着
 * - 左右の透明ゾーン（PC のみ）のクリックで 1 枚送り
 * - PC はカーソル追従の円に NEXT / PREV / DRAG を表示
 * - カードは足りない分を複製して輪にし、端に来たものを反対側へ回して
 *   無限に一周させる
 */
(function () {
	'use strict';

	var STEP_DEG = 10.24; // 1 枚あたりの回転角（917:119）
	// PC の円周上（カード上端）の中心間隔 / カード幅。カードは円の中心へ向けて
	// 傾くため上端の間隔は見かけより広く、カード中央の高さでの見かけの間隔が
	// カンプの 397.56px（カード 306.18px 時 = 1.2985 倍）になるのがこの値。
	var PC_SPACING_RATIO = 1.4125;
	// SP の中心間隔 / カード幅。カードは円の中心へ向けて傾くため、下端どうしが
	// カード高 × 2sin(STEP_DEG/2) ≈ カード幅の 0.216 倍ぶん内側に寄る。
	// 1.2164 未満だと下端の角が重なるので、数 px の余白を足した値にする
	// （カード幅は vw 比例なので、間隔も幅比例で追従する）。
	var SP_SPACING_RATIO = 1.23;
	var MIN_SLOTS = 14; // 輪に置く最小枚数。足りない分は複製で埋める
	var DRAG_SPEED = 2; // ドラッグ量 → 回転角の倍率（1 でカード間隔ぶんのドラッグ = 1 枚）
	var DRAG_START_PX = 10; // これ未満の横移動はドラッグにしない。実マウスのクリックは数 px 滑ることがあり、小さすぎると送りクリックがドラッグ扱いで握りつぶされる
	var STEP_MS = 600; // クリック送りの所要時間
	var SNAP_MS = 300; // ドラッグ後の吸着の所要時間

	var slider = document.querySelector('[data-works-carousel]');

	if (!slider) {
		return;
	}

	var list = slider.querySelector('[data-works-list]');
	var desc = document.querySelector('[data-works-desc]');
	var dotsWrap = document.querySelector('[data-works-dots]');
	var dots = dotsWrap ? Array.prototype.slice.call(dotsWrap.children) : [];
	var prevBtn = slider.querySelector('[data-works-prev]');
	var nextBtn = slider.querySelector('[data-works-next]');
	var stalker = slider.querySelector('[data-works-stalker]');
	var stalkerText = slider.querySelector('[data-works-stalker-text]');

	if (!list || !list.children.length) {
		return;
	}

	var originals = Array.prototype.slice.call(list.children);
	var count = originals.length;
	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var mqPc = window.matchMedia('(min-width: 1025px)');
	var STEP_RAD = (STEP_DEG * Math.PI) / 180;

	var nodes = []; // { el, angle } を角度の昇順で保持
	var total = 0; // 累計の送り枚数（prev はマイナス）
	var rot = 0; // リストの現在の回転角。静止時は -total * STEP_DEG
	var chord = 0; // 隣のカードとの中心間距離
	var anim = null; // 実行中の回転アニメーション
	var currentNode = null;
	var titleTimer = 0;

	/* ---- 輪の構築 ------------------------------------------------ */

	// 中央（slots の真ん中）に 1 件目が来るように、複製したカードを
	// 15 度刻みではなく STEP_DEG 刻みで円周上に並べる。
	function buildSlots() {
		var slots = count * Math.ceil(MIN_SLOTS / count);
		var mid = Math.floor(slots / 2);
		var frag = document.createDocumentFragment();
		var i;
		var itemIndex;
		var el;
		var angle;

		nodes = [];

		for (i = 0; i < slots; i++) {
			itemIndex = (((i - mid) % count) + count) % count;
			el = originals[itemIndex].cloneNode(true);
			angle = (i - mid) * STEP_DEG;
			el.style.transform = 'rotate(' + angle + 'deg)';
			frag.appendChild(el);
			nodes.push({ el: el, angle: angle });
		}

		list.textContent = '';
		list.appendChild(frag);
	}

	// カード幅から円の半径を割り出し、回転原点（円の中心）を配る。
	// 原点はカード上端から radius 下: カードとリストの上端・中心線は
	// 一致しているので、同じ値で「輪の中心を軸にした回転」になる。
	function applyGeometry() {
		var cardW = nodes[0].el.offsetWidth;
		var radius;
		var origin;
		var i;

		if (!cardW) {
			return;
		}

		chord = cardW * (mqPc.matches ? PC_SPACING_RATIO : SP_SPACING_RATIO);
		radius = chord / (2 * Math.sin(STEP_RAD / 2));
		origin = '50% ' + radius + 'px';

		list.style.transformOrigin = origin;

		for (i = 0; i < nodes.length; i++) {
			nodes[i].el.style.transformOrigin = origin;
		}
	}

	function render() {
		list.style.transform = 'rotate(' + rot + 'deg)';
		updateVisibility();
	}

	// 中央から 2.5 枚ぶんより外のカードは隠す。静止時は中央 ±2 の 5 枚だけが
	// 見え、送り中は端のカードが 3 枚目の位置に抜けるときフェードで消える。
	function updateVisibility() {
		var limit = STEP_DEG * 2.5;
		var i;
		var outside;

		for (i = 0; i < nodes.length; i++) {
			outside = Math.abs(nodes[i].angle + rot) > limit;
			nodes[i].el.classList.toggle('is-outside', outside);
		}
	}

	// 回転が進んで端に寄ったカードを反対側の端へ回す。
	// slots は count の倍数なので、一周（slots * STEP_DEG）ずらしても
	// 施工事例の並び順は崩れない。
	function rebalance() {
		var center = total * STEP_DEG;
		var span = nodes.length * STEP_DEG;
		var moved = true;
		var node;

		while (moved) {
			moved = false;
			node = nodes[0];

			if (center - node.angle > span / 2) {
				nodes.shift();
				node.angle += span;
				node.el.style.transform = 'rotate(' + node.angle + 'deg)';
				nodes.push(node);
				moved = true;
				continue;
			}

			node = nodes[nodes.length - 1];

			if (node.angle - center > span / 2) {
				nodes.pop();
				node.angle -= span;
				node.el.style.transform = 'rotate(' + node.angle + 'deg)';
				nodes.unshift(node);
				moved = true;
			}
		}
	}

	/* ---- 中央カードとタイトル ------------------------------------ */

	function hideTitle() {
		if (desc) {
			desc.classList.add('is-hidden');
		}
	}

	// 消えていれば差し替えてからフェードで戻す。初期表示はそのまま出す。
	function showTitle(title) {
		if (!desc) {
			return;
		}

		window.clearTimeout(titleTimer);

		if (!desc.classList.contains('is-hidden')) {
			if (title) {
				desc.textContent = title;
			}
			return;
		}

		titleTimer = window.setTimeout(
			function () {
				if (title) {
					desc.textContent = title;
				}
				desc.classList.remove('is-hidden');
			},
			reduceMotion ? 0 : 200
		);
	}

	function findTopNode() {
		var target = total * STEP_DEG;
		var i;

		for (i = 0; i < nodes.length; i++) {
			if (Math.abs(nodes[i].angle - target) < STEP_DEG / 2) {
				return nodes[i];
			}
		}

		return null;
	}

	// 現在何枚目かのドットを点灯し直す
	function updateDots() {
		var active = ((total % count) + count) % count;
		var i;

		for (i = 0; i < dots.length; i++) {
			dots[i].classList.toggle('is-active', i === active);
		}
	}

	function setCurrent() {
		var node = findTopNode();

		if (!node) {
			return;
		}

		if (currentNode && currentNode !== node) {
			currentNode.el.classList.remove('is-current');
		}

		node.el.classList.add('is-current');
		currentNode = node;
		showTitle(node.el.getAttribute('data-works-title'));
		updateDots();
	}

	function removeCurrent() {
		if (!currentNode) {
			return;
		}

		currentNode.el.classList.remove('is-current');
		currentNode = null;
		hideTitle();
	}

	/* ---- 回転アニメーション -------------------------------------- */

	function easeOutQuint(t) {
		return 1 - Math.pow(1 - t, 5);
	}

	function stopAnim() {
		if (anim) {
			window.cancelAnimationFrame(anim.raf);
			anim = null;
		}
	}

	function animateTo(target, duration, done) {
		stopAnim();

		if (reduceMotion || duration <= 0) {
			rot = target;
			render();

			if (done) {
				done();
			}
			return;
		}

		var from = rot;
		var start = performance.now();
		var a = { raf: 0 };

		anim = a;

		function tick(now) {
			var t = Math.min(1, (now - start) / duration);

			rot = from + (target - from) * easeOutQuint(t);
			render();

			if (t < 1) {
				a.raf = window.requestAnimationFrame(tick);
			} else {
				anim = null;

				if (done) {
					done();
				}
			}
		}

		a.raf = window.requestAnimationFrame(tick);
	}

	function settle() {
		rebalance();
		updateVisibility(); // 張り替えたカードの角度で表示状態を取り直す
		setCurrent();
	}

	// dir: 1 で次（右のカードが中央へ）、-1 で前
	function step(dir) {
		total += dir;
		removeCurrent();
		animateTo(-total * STEP_DEG, STEP_MS, settle);
	}

	/* ---- ドラッグ ------------------------------------------------- */

	var drag = {
		active: false,
		dragging: false,
		x: 0,
		rot0: 0,
		pointerId: undefined,
		suppress: false
	};

	function onPointerDown(e) {
		if (e.button !== undefined && e.button > 0) {
			return;
		}

		drag.active = true;
		drag.dragging = false;
		drag.x = e.clientX;
		drag.pointerId = e.pointerId;

		stopAnim();
		drag.rot0 = rot;
	}

	function onPointerMove(e) {
		if (!drag.active) {
			return;
		}

		var dx = e.clientX - drag.x;

		if (!drag.dragging) {
			if (Math.abs(dx) < DRAG_START_PX) {
				return;
			}
			drag.dragging = true;
			setStalkerDrag(true);

			// ポインターはドラッグが始まってから捕まえる（スライダー外に出ても追従させるため）。
			// pointerdown の時点で捕まえると pointerup / click がスライダーへ再ターゲットされ、
			// 実マウス・実タッチで送りゾーンやカードリンクの click が一切発火しなくなる。
			if (slider.setPointerCapture && drag.pointerId !== undefined) {
				try {
					slider.setPointerCapture(drag.pointerId);
				} catch (err) {
					// 既に離れているなど。キャプチャ無しで続行する。
				}
			}
		}

		rot = drag.rot0 + (dx * STEP_DEG * DRAG_SPEED) / chord;
		render();

		// 中央のカードが替わったらタイトルを先に消しておく
		if (Math.round(-rot / STEP_DEG) !== total) {
			removeCurrent();
		}

		e.preventDefault();
	}

	function onPointerUp() {
		if (!drag.active) {
			return;
		}

		drag.active = false;
		setStalkerDrag(false);

		if (!drag.dragging) {
			return;
		}

		drag.dragging = false;

		// 直後の click（ゾーンやカードリンク）を無効化する
		drag.suppress = true;
		window.setTimeout(function () {
			drag.suppress = false;
		}, 0);

		total = Math.round(-rot / STEP_DEG);
		animateTo(-total * STEP_DEG, SNAP_MS, settle);
	}

	/* ---- マウスストーカー（PC のみ） ------------------------------ */

	var stalkerOn = false;
	var st = { x: 0, y: 0, tx: 0, ty: 0, raf: 0, running: false, dragging: false };

	function setStalkerLabel(text) {
		if (stalkerText && stalkerText.textContent !== text) {
			stalkerText.textContent = text;
		}
	}

	function setStalkerDrag(dragging) {
		st.dragging = dragging;

		if (stalkerOn && dragging) {
			setStalkerLabel('DRAG');
			stalker.classList.add('is-visible');
		}
	}

	function stalkerFrame() {
		st.x += (st.tx - st.x) * 0.18;
		st.y += (st.ty - st.y) * 0.18;
		stalker.style.transform = 'translate3d(' + st.x + 'px,' + st.y + 'px,0)';
		st.raf = window.requestAnimationFrame(stalkerFrame);
	}

	function startStalkerLoop() {
		if (!st.running) {
			st.running = true;
			st.raf = window.requestAnimationFrame(stalkerFrame);
		}
	}

	function stopStalkerLoop() {
		st.running = false;
		window.cancelAnimationFrame(st.raf);
	}

	function onStalkerMove(e) {
		var rect = slider.getBoundingClientRect();
		var overCurrent;

		st.tx = e.clientX - rect.left;
		st.ty = e.clientY - rect.top;

		if (st.dragging) {
			setStalkerLabel('DRAG');
			stalker.classList.add('is-visible');
			return;
		}

		// 中央のカード（リンク）の上ではストーカーを消して通常のカーソルに戻す
		overCurrent = false;

		if (currentNode) {
			var cardRect = currentNode.el.getBoundingClientRect();
			overCurrent =
				e.clientX >= cardRect.left &&
				e.clientX <= cardRect.right &&
				e.clientY >= cardRect.top &&
				e.clientY <= cardRect.bottom;
		}

		if (overCurrent) {
			stalker.classList.remove('is-visible');
			return;
		}

		setStalkerLabel(st.tx < rect.width / 2 ? 'PREV' : 'NEXT');
		stalker.classList.add('is-visible');
	}

	function initStalker() {
		if (!stalker || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
			return;
		}

		stalkerOn = true;

		slider.addEventListener('mouseenter', function (e) {
			startStalkerLoop();
			st.x = st.tx = e.clientX - slider.getBoundingClientRect().left;
			st.y = st.ty = e.clientY - slider.getBoundingClientRect().top;
		});

		slider.addEventListener('mousemove', onStalkerMove);

		slider.addEventListener('mouseleave', function () {
			stalker.classList.remove('is-visible');
			stopStalkerLoop();
		});

		slider.addEventListener('mousedown', function () {
			stalker.classList.add('is-down');
		});

		window.addEventListener('mouseup', function () {
			stalker.classList.remove('is-down');
		});
	}

	/* ---- 初期化 --------------------------------------------------- */

	buildSlots();
	slider.classList.add('is-wheel');
	applyGeometry();
	render();
	setCurrent();
	initStalker();

	slider.addEventListener('pointerdown', onPointerDown);
	slider.addEventListener('pointermove', onPointerMove);
	slider.addEventListener('pointerup', onPointerUp);
	slider.addEventListener('pointercancel', onPointerUp);

	// ドラッグ直後の click は送り・リンクとも無効化（捕捉フェーズで止める）
	slider.addEventListener(
		'click',
		function (e) {
			if (drag.suppress) {
				e.preventDefault();
				e.stopPropagation();
			}
		},
		true
	);

	// 画像の標準ドラッグが輪のドラッグを妨げないように
	slider.addEventListener('dragstart', function (e) {
		e.preventDefault();
	});

	if (prevBtn) {
		prevBtn.addEventListener('click', function () {
			step(-1);
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', function () {
			step(1);
		});
	}

	// ブレークポイントの行き来やリサイズで半径を取り直す
	var resizeTimer = 0;

	window.addEventListener('resize', function () {
		window.clearTimeout(resizeTimer);
		resizeTimer = window.setTimeout(applyGeometry, 150);
	});

	if (typeof mqPc.addEventListener === 'function') {
		mqPc.addEventListener('change', applyGeometry);
	} else if (typeof mqPc.addListener === 'function') {
		mqPc.addListener(applyGeometry);
	}
})();
