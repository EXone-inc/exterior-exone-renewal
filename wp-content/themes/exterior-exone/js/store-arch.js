/**
 * 支店ページ AREA の店内写真（[data-store-arch]）を右から左へ止まらず流す（決定事項 A）。
 *
 * 写真の枠（li）ごとに、PC は円筒上の角度 --st-arch-a（度。0 が正面・正が右）、
 * SP は列の何枚目か --st-arch-s を毎フレーム書き換える。置き方（円筒の半径・傾き・
 * 両端の消え方 / 直線の間隔）は css/store.css が持ち、ここは位置を進めるだけ。
 *
 * 速さ: PC は「写真 1 組（複製でない枚数）ぶん進む時間」が --st-arch-loop-time。
 * SP は PC の正面の速さで画面幅を横切る時間と同じ時間で、SP の画面幅を横切る（体感をそろえる）。
 * 画面外では止める。タブが裏にある間はブラウザが requestAnimationFrame を止めるので、
 * 戻ったら続きから動く（1 フレームの進みは 0.1 秒で頭打ちにして、戻った瞬間に飛ばない）。
 * 動きを減らす設定・JS 無効では PHP が入れた初期位置（カンプの並び）のまま静止。
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-store-arch]');

	if (!root) {
		return;
	}

	var items = Array.prototype.slice.call(root.querySelectorAll('.p-sarea__arch-item'));
	var count = items.length;

	if (!count) {
		return;
	}

	var originals = items.filter(function (item) {
		return !item.hasAttribute('aria-hidden');
	}).length || count;
	var pc = window.matchMedia('(min-width: 1025px)');
	var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
	var step = 360 / count;
	var phase = 0; // 進んだ枠の数（0〜count）
	var rate = 0; // 1 秒あたりに進む枠の数
	var last = 0;
	var frame = 0;
	var inView = false;

	function token(name, fallback) {
		var value = parseFloat(window.getComputedStyle(root).getPropertyValue(name));

		return isFinite(value) && value > 0 ? value : fallback;
	}

	// 現在の幅での速さ（枠 / 秒）を決める。
	function measure() {
		var loopTime = token('--st-arch-loop-time', 60);
		var pcRate = originals / loopTime;

		if (pc.matches) {
			rate = pcRate;
			return;
		}

		// PC の正面の速さ（カンプ 1920 の単位 / 秒）で 1920 を横切る時間。
		var radius = token('--st-arch-radius', 941.6);
		var screenTime = token('--st-arch-comp-width', 1920) / (radius * pcRate * step * Math.PI / 180);
		var width = token('--st-arch-sp-width', 142);
		var pitch = token('--st-arch-sp-pitch', 150);
		var pitchPx = items[0].offsetWidth * pitch / width;
		var frameWidth = root.parentElement ? root.parentElement.clientWidth : window.innerWidth;

		rate = pitchPx > 0 ? frameWidth / screenTime / pitchPx : 0;
	}

	// PC では写真 1 枚を縦の短冊に分け、短冊ごとに角度を変えて円筒に沿わせる（css/store.css の
	// .p-sarea__arch-strip。平らな板のままだと軌道が正 12 角形に見える。2026-10-07）。
	// 1 回だけ作り、SP では CSS が短冊を隠して元の写真を出す。
	var stripsBuilt = false;

	function buildStrips() {
		if (stripsBuilt) {
			return;
		}

		var strips = Math.max(1, Math.round(token('--st-arch-strips', 10)));

		items.forEach(function (item) {
			var image = item.querySelector('.p-sarea__arch-image');

			if (!image) {
				return;
			}

			var holder = document.createElement('div');
			holder.className = 'p-sarea__arch-strips';
			holder.setAttribute('aria-hidden', 'true');

			for (var i = 0; i < strips; i++) {
				var strip = document.createElement('div');
				var slice = document.createElement('div');
				var copy = image.cloneNode(false);

				strip.className = 'p-sarea__arch-strip';
				strip.style.setProperty('--st-arch-strip-i', String(i));
				slice.className = 'p-sarea__arch-slice';
				copy.removeAttribute('alt');
				copy.removeAttribute('class');
				slice.appendChild(copy);
				strip.appendChild(slice);
				holder.appendChild(strip);
			}

			item.appendChild(holder);
			item.classList.add('has-strips');
		});

		stripsBuilt = true;
	}

	function render() {
		items.forEach(function (item, index) {
			var angle = (((1 - index - phase) * step) % 360 + 540) % 360 - 180;
			var slot = ((index - phase + 1) % count + count) % count - 1;

			item.style.setProperty('--st-arch-a', angle.toFixed(3));
			item.style.setProperty('--st-arch-s', slot.toFixed(4));
		});
	}

	function tick(now) {
		if (last) {
			phase = (phase + rate * Math.min((now - last) / 1000, 0.1)) % count;
			render();
		}

		last = now;
		frame = window.requestAnimationFrame(tick);
	}

	function stop() {
		if (frame) {
			window.cancelAnimationFrame(frame);
		}

		frame = 0;
		last = 0;
		root.classList.remove('is-running');
	}

	function update() {
		if (pc.matches) {
			buildStrips();
		}

		if (motion.matches) {
			stop();
			phase = 0;
			render();
			return;
		}

		if (inView) {
			if (!frame) {
				measure();
				root.classList.add('is-running');
				frame = window.requestAnimationFrame(tick);
			}
		} else {
			stop();
		}
	}

	if ('IntersectionObserver' in window) {
		new IntersectionObserver(function (entries) {
			inView = entries[entries.length - 1].isIntersecting;
			update();
		}, { rootMargin: '100px 0px' }).observe(root);
	} else {
		inView = true;
	}

	window.addEventListener('resize', measure);

	[pc, motion].forEach(function (query) {
		var onChange = function () {
			measure();
			update();
		};

		if (query.addEventListener) {
			query.addEventListener('change', onChange);
		} else if (query.addListener) {
			query.addListener(onChange);
		}
	});

	update();
})();
