/**
 * 採用ページ FV の粒子（canvas[data-recruit-particles]。値 = 粒子の数）。
 *
 * 現サイト recruit.js の Three.js（画角 75°・カメラ z = 3・半径 0.015 の球 140 個）を Canvas 2D で
 * 同じ遠近の式で描き直したもの。白地に 6 色の点が奥（z = -10）から手前へ流れ、カメラを越えた点・
 * 範囲（|x|, |y| > 1.5）を出た点は奥へ置き直して色も選び直す。
 * 速さは最初の 2 秒が 0.13 → 0.005（1 フレームあたり。ease-out）、以降 0.005。60Hz 相当の時間で進める。
 * 色は css/recruit.css の --rc-particle-1〜6。
 *
 * 内部解像度は表示サイズ × devicePixelRatio（にじまない）。FV が画面外の間は描画を止める。
 * 動きを減らす設定では点を散らした 1 枚を描くだけ。
 */
(function () {
	'use strict';

	var canvas = document.querySelector('canvas[data-recruit-particles]');

	if (!canvas || !canvas.getContext) {
		return;
	}

	var ctx = canvas.getContext('2d');
	var host = canvas.closest('[data-section]') || canvas.parentElement;
	var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
	var count = parseInt(canvas.getAttribute('data-recruit-particles'), 10) || 140;

	// 現サイトの Three.js の値。
	var FOV = 75;
	var CAMERA_Z = 3;
	var DEPTH = 10;
	var NEAR = 0.1;
	var RADIUS = 0.015;
	var LIMIT = 1.5;
	var INITIAL_SPEED = 0.13;
	var FINAL_SPEED = 0.005;
	var INITIAL_DURATION = 2000;
	var FRAME = 1000 / 60;
	var MAX_STEP = 100; // タブ復帰などで一度に進みすぎないように（ms）
	var TAN = Math.tan((FOV / 2) * Math.PI / 180);

	var colors = readColors();
	var ground = window.getComputedStyle(canvas).getPropertyValue('--rc-white').trim() || '#fff';
	var width = 0;
	var height = 0;
	var particles = [];
	var elapsed = 0;
	var last = 0;
	var raf = 0;
	var inView = true;

	if (!ctx || !colors.length) {
		return;
	}

	function readColors() {
		var style = window.getComputedStyle(canvas);
		var list = [];

		for (var i = 1; i <= 6; i += 1) {
			var value = style.getPropertyValue('--rc-particle-' + i).trim();

			if (value) {
				list.push(value);
			}
		}

		return list;
	}

	function pick() {
		return colors[Math.floor(Math.random() * colors.length)];
	}

	function aspect() {
		return height ? width / height : 1;
	}

	// 置き直し（現サイトの reset と同じ分布。y は ±1）。
	function reset(p) {
		p.x = (Math.random() * 2 - 1) * aspect();
		p.y = Math.random() * 2 - 1;
		p.z = -Math.random() * DEPTH;
		p.color = pick();
	}

	function create(settled) {
		particles = [];

		for (var i = 0; i < count; i += 1) {
			var p = {
				// 初期配置は現サイトのまま（y も ±aspect。範囲外の点は最初のフレームで置き直される）。
				x: (Math.random() * 2 - 1) * aspect(),
				y: (Math.random() * 2 - 1) * aspect(),
				z: -Math.random() * DEPTH,
				color: pick()
			};

			if (settled && (Math.abs(p.x) > LIMIT || Math.abs(p.y) > LIMIT)) {
				reset(p);
			}

			particles.push(p);
		}
	}

	function resize() {
		var rect = canvas.getBoundingClientRect();
		var dpr = window.devicePixelRatio || 1;

		width = rect.width;
		height = rect.height;
		canvas.width = Math.max(1, Math.round(width * dpr));
		canvas.height = Math.max(1, Math.round(height * dpr));
		ctx.setTransform(canvas.width / Math.max(width, 1), 0, 0, canvas.height / Math.max(height, 1), 0, 0);
	}

	function draw() {
		var scale0 = height / (2 * TAN);
		var sorted = particles.slice().sort(function (a, b) {
			return a.z - b.z;
		});

		ctx.fillStyle = ground;
		ctx.fillRect(0, 0, width, height);

		sorted.forEach(function (p) {
			var distance = CAMERA_Z - p.z;

			if (distance <= NEAR) {
				return;
			}

			var scale = scale0 / distance;
			var r = RADIUS * scale;
			var sx = width / 2 + p.x * scale;
			var sy = height / 2 - p.y * scale;

			if (sx + r < 0 || sx - r > width || sy + r < 0 || sy - r > height) {
				return;
			}

			ctx.beginPath();
			ctx.arc(sx, sy, r, 0, Math.PI * 2);
			ctx.fillStyle = p.color;
			ctx.fill();
		});
	}

	function speedAt(time) {
		if (time >= INITIAL_DURATION) {
			return FINAL_SPEED;
		}

		var t = time / INITIAL_DURATION;
		var eased = 1 - Math.pow(1 - t, 3);

		return INITIAL_SPEED * (1 - eased) + FINAL_SPEED * eased;
	}

	function step(delta) {
		var dz = speedAt(elapsed) * (delta / FRAME);

		elapsed += delta;

		particles.forEach(function (p) {
			p.z += dz;

			if (p.z > CAMERA_Z || Math.abs(p.x) > LIMIT || Math.abs(p.y) > LIMIT) {
				reset(p);
			}
		});
	}

	function tick(now) {
		raf = 0;

		var delta = last ? Math.min(now - last, MAX_STEP) : FRAME;

		last = now;
		step(delta);
		draw();
		schedule();
	}

	function schedule() {
		if (!raf && inView && !motion.matches) {
			raf = window.requestAnimationFrame(tick);
		}
	}

	function stop() {
		if (raf) {
			window.cancelAnimationFrame(raf);
			raf = 0;
		}
		last = 0;
	}

	// 動きを減らす設定: 置き直し後の分布で奥〜手前に散らした 1 枚。
	function still() {
		stop();
		create(true);
		particles.forEach(function (p) {
			p.z = -Math.random() * DEPTH + 1;
		});
		draw();
	}

	resize();
	create(false);

	if (motion.matches) {
		still();
	} else {
		draw();
		schedule();
	}

	if ('ResizeObserver' in window) {
		new ResizeObserver(function () {
			resize();
			draw();
		}).observe(host);
	} else {
		window.addEventListener('resize', function () {
			resize();
			draw();
		});
	}

	if ('IntersectionObserver' in window) {
		new IntersectionObserver(function (entries) {
			inView = entries[entries.length - 1].isIntersecting;

			if (inView) {
				schedule();
			} else {
				stop();
			}
		}).observe(host);
	}

	var onMotionChange = function () {
		if (motion.matches) {
			still();
		} else {
			create(false);
			elapsed = 0;
			schedule();
		}
	};

	if (motion.addEventListener) {
		motion.addEventListener('change', onMotionChange);
	} else if (motion.addListener) {
		motion.addListener(onMotionChange);
	}
})();
