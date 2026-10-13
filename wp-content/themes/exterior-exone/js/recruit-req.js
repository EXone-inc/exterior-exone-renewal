/**
 * 採用ページ Requirements: 募集要項 6 職種の開閉（[data-recruit-job]）と /recruit/#<slug> のディープリンク。
 *
 * 現サイト recruit.js の「募集要項：職種ナビ ＋ 各職種アコーディオン」と同じ挙動（職種ナビは出さない。決定 §2-4）。
 * ・見出し（button[data-recruit-job-toggle]）で開閉。高さのアニメ（0.4 秒）で開き、開き終わったら上限を外す
 * ・同時に 1 つだけ。押した見出しより上の職種が閉じて縮んでも、押した見出しは画面上で動かさない
 * ・読み込み時のハッシュが職種の slug なら、その職種を開いてヘッダーの下 + 24 までスクロール。
 *   画像・フォントの読み込みでずれても数回追従し、利用者がスクロール・タッチ・キー操作をしたらやめる
 * ・読み込み後のハッシュの変化（戻る / 進む・ページ内リンク）でも開いて滑らかにスクロール
 * ・slug 以外のハッシュは何もしない
 * JS が無いときは全部開いた状態（functions.php の noscript。仕様書 Q11）。
 */
(function () {
	'use strict';

	var jobs = Array.prototype.slice.call(document.querySelectorAll('[data-recruit-job]')).map(function (el) {
		return {
			id: el.getAttribute('data-recruit-job'),
			el: el,
			toggle: el.querySelector('[data-recruit-job-toggle]'),
			body: el.querySelector('.p-rcreq__body')
		};
	}).filter(function (job) {
		return job.id && job.toggle && job.body;
	});

	if (!jobs.length) {
		return;
	}

	var OPEN_CLASS = 'is-open';
	var SCROLL_GAP = 24; // ヘッダーの下に空ける余白（現サイトの SCROLL_OFFSET）
	var header = document.querySelector('.p-header');
	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

	function isOpen(job) {
		return job.el.classList.contains(OPEN_CLASS);
	}

	// html の scroll-behavior: smooth に関係なく、その場で動かす / 滑らかに動かす。
	function scrollToY(y, smooth) {
		var top = Math.max(0, y);

		if (smooth && !reduced.matches) {
			window.scrollTo({ top: top, left: 0, behavior: 'smooth' });
			return;
		}

		// 'instant' を知らないブラウザでも CSS の smooth が効かないよう、一時的に外す。
		var root = document.documentElement;
		var behavior = root.style.scrollBehavior;

		root.style.scrollBehavior = 'auto';
		window.scrollTo(0, top);
		root.style.scrollBehavior = behavior;
	}

	function headerOffset() {
		return (header ? header.getBoundingClientRect().height : 0) + SCROLL_GAP;
	}

	function targetY(job) {
		return job.el.getBoundingClientRect().top + window.pageYOffset - headerOffset();
	}

	function open(job) {
		job.el.classList.add(OPEN_CLASS);
		job.toggle.setAttribute('aria-expanded', 'true');

		if (reduced.matches) {
			job.body.style.maxHeight = 'none';
			return;
		}

		job.body.style.maxHeight = job.body.scrollHeight + 'px';
	}

	function close(job, instant) {
		if (!isOpen(job)) {
			return;
		}

		if (instant || reduced.matches) {
			var transition = job.body.style.transition;

			job.body.style.transition = 'none';
			job.el.classList.remove(OPEN_CLASS);
			job.toggle.setAttribute('aria-expanded', 'false');
			job.body.style.maxHeight = '0px';
			void job.body.offsetHeight;
			job.body.style.transition = transition;
			return;
		}

		// 上限を外していた場合は今の高さを入れ直してから 0 へ（アニメさせるため）。
		job.body.style.maxHeight = job.body.scrollHeight + 'px';
		void job.body.offsetHeight;
		job.el.classList.remove(OPEN_CLASS);
		job.toggle.setAttribute('aria-expanded', 'false');
		job.body.style.maxHeight = '0px';
	}

	// 対象だけを開く。ほかはその場で閉じ、対象の見出しが画面上で動かないようにスクロールを補正する。
	function openOnly(job) {
		var before = job.el.getBoundingClientRect().top;

		jobs.forEach(function (other) {
			if (other !== job) {
				close(other, true);
			}
		});

		var shift = job.el.getBoundingClientRect().top - before;

		if (shift) {
			scrollToY(window.pageYOffset + shift, false);
		}

		if (!isOpen(job)) {
			open(job);
		}
	}

	jobs.forEach(function (job) {
		job.toggle.addEventListener('click', function () {
			if (isOpen(job)) {
				close(job, false);
			} else {
				openOnly(job);
			}
		});

		job.body.addEventListener('transitionend', function (event) {
			if (event.target === job.body && event.propertyName === 'max-height' && isOpen(job)) {
				job.body.style.maxHeight = 'none';
			}
		});
	});

	function jobFromHash() {
		var id = (window.location.hash || '').replace(/^#/, '');

		if (!id) {
			return null;
		}

		try {
			id = decodeURIComponent(id);
		} catch (e) {
			return null;
		}

		for (var i = 0; i < jobs.length; i += 1) {
			if (jobs[i].id === id) {
				return jobs[i];
			}
		}

		return null;
	}

	// 読み込み時: 開いてから、上の画像・フォントの読み込みでずれても最終位置に合わせて数回スクロールし直す。
	function deepLink(job) {
		var stopped = false;
		var events = ['wheel', 'touchmove', 'keydown', 'mousedown'];

		function stop() {
			stopped = true;
		}

		function go() {
			if (!stopped) {
				scrollToY(targetY(job), true);
			}
		}

		events.forEach(function (name) {
			window.addEventListener(name, stop, { passive: true });
		});

		go();
		[250, 700, 1300].forEach(function (time) {
			window.setTimeout(go, time);
		});

		if (document.readyState === 'complete') {
			window.setTimeout(go, 100);
		} else {
			window.addEventListener('load', function () {
				window.setTimeout(go, 100);
			}, { once: true });
		}

		window.setTimeout(function () {
			events.forEach(function (name) {
				window.removeEventListener(name, stop, { passive: true });
			});
		}, 2500);
	}

	var initial = jobFromHash();

	if (initial) {
		// ブラウザのスクロール位置の復元が補正とぶつからないように。
		if ('scrollRestoration' in window.history) {
			try {
				window.history.scrollRestoration = 'manual';
			} catch (e) {
				// 何もしない
			}
		}

		openOnly(initial);
		deepLink(initial);
	}

	window.addEventListener('hashchange', function () {
		var job = jobFromHash();

		if (!job) {
			return;
		}

		openOnly(job);
		scrollToY(targetY(job), true);
	});
})();
