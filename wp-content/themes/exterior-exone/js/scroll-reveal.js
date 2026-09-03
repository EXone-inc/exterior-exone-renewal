/**
 * スクロール連動アニメーション。
 *
 * ・[data-reveal]  … 画面に入ったら下からフェードイン。
 *                    data-reveal="scale" で拡大しながらのフェードイン。
 *                    data-reveal-delay="150"（ms）で開始をずらす。
 * ・[data-countup] … 画面に入ったら 0 から要素内の数値までカウントアップ。
 *                    「1,800」「98.5」のような桁区切り・小数もそのまま再現する。
 *
 * prefers-reduced-motion: reduce と IntersectionObserver 非対応環境では
 * 最終状態をそのまま表示する（内容が消えたままにならないようにする）。
 */
(function () {
	'use strict';

	var COUNT_DURATION = 1400;

	var reveals = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));
	var counters = Array.prototype.slice.call(document.querySelectorAll('[data-countup]'));

	if (!reveals.length && !counters.length) {
		return;
	}

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (reduced || !('IntersectionObserver' in window)) {
		reveals.forEach(function (el) {
			el.classList.add('is-inview');
		});

		return;
	}

	// --- 下からフェードイン ---
	var revealObserver = new IntersectionObserver(
		function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) {
					return;
				}

				var el = entry.target;
				var delay = el.getAttribute('data-reveal-delay');

				if (delay) {
					el.style.setProperty('--reveal-delay', delay + 'ms');
				}

				revealObserver.unobserve(el);

				// 中に [data-reveal-wait] の画像があるときは、その読み込みを待ってから
				// 合図を出す（遅い回線で「フェードし終わってから画像が届く」のを防ぐ）。
				var waitImg = el.querySelector('img[data-reveal-wait]');

				if (waitImg && !(waitImg.complete && waitImg.naturalWidth > 0)) {
					var start = function () {
						el.classList.add('is-inview');
					};

					waitImg.addEventListener('load', start, { once: true });
					// 読めなかったときも出す（隠れたままにしない）。
					waitImg.addEventListener('error', start, { once: true });

					return;
				}

				el.classList.add('is-inview');
			});
		},
		{ rootMargin: '0px 0px -10% 0px', threshold: 0.15 }
	);

	reveals.forEach(function (el) {
		revealObserver.observe(el);
	});

	// --- カウントアップ ---

	/**
	 * 表示中の文字列から「到達値・小数桁数・桁区切りの有無」を読み取る。
	 */
	function readFormat(text) {
		var plain = text.replace(/,/g, '');
		var dot = plain.indexOf('.');

		return {
			value: parseFloat(plain),
			decimals: dot === -1 ? 0 : plain.length - dot - 1,
			grouped: text.indexOf(',') !== -1
		};
	}

	function format(value, spec) {
		var out = value.toFixed(spec.decimals);

		if (!spec.grouped) {
			return out;
		}

		var parts = out.split('.');
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

		return parts.join('.');
	}

	function countUp(el) {
		var spec = readFormat(el.getAttribute('data-countup'));

		if (isNaN(spec.value)) {
			return;
		}

		var startedAt = null;

		function step(now) {
			if (startedAt === null) {
				startedAt = now;
			}

			var progress = Math.min((now - startedAt) / COUNT_DURATION, 1);
			var eased = 1 - Math.pow(1 - progress, 3);

			el.textContent = format(spec.value * eased, spec);

			if (progress < 1) {
				window.requestAnimationFrame(step);
			}
		}

		window.requestAnimationFrame(step);
	}

	// JS 無効時は実数値がそのまま出るよう、到達値は HTML 側に置いたまま退避する。
	counters.forEach(function (el) {
		var target = el.textContent.trim();

		el.setAttribute('data-countup', target);
		el.textContent = format(0, readFormat(target));
	});

	var countObserver = new IntersectionObserver(
		function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) {
					return;
				}

				countUp(entry.target);
				countObserver.unobserve(entry.target);
			});
		},
		{ threshold: 0.5 }
	);

	counters.forEach(function (el) {
		countObserver.observe(el);
	});
})();
