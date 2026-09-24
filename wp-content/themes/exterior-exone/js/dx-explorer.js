(function () {
	'use strict';
	const clamp = (value) => Math.min(1, Math.max(0, Number.isFinite(value) ? value : 0));
	const scrollProgress = (y, start, distance) => clamp((y - start) / Math.max(1, distance));
	// Aim inside the final frame, not its boundary (duration may be rounded by the browser).
	const timeForProgress = (progress, duration, fps) => clamp(progress) * Math.max(0, duration - 0.5 / fps);
	const chapterForProgress = (progress, chapters) => chapters.reduce((best, chapter, index) =>
		Math.abs(chapter.position - progress) < Math.abs(chapters[best].position - progress) ? index : best, 0);

	/** Keep one seek in flight; later scroll events replace its pending destination. */
	function createSeekQueue(video, options) {
		let wanted = 0, busy = false, stopped = false, suspended = false, watchdog = 0;
		const clear = () => { clearTimeout(watchdog); watchdog = 0; };
		function watch() {
			if (!watchdog && !suspended) watchdog = setTimeout(() => {
				watchdog = 0;
				if (!stopped && !suspended) options.failed();
			}, options.timeout || 15000);
		}
		function flush() {
			if (stopped || suspended || (options.valid && !options.valid()) || !Number.isFinite(video.duration) || video.readyState < 2) return;
			if (busy || video.seeking) { watch(); return; }
			if (Math.abs(video.currentTime - wanted) < 1 / 120) { clear(); options.settled(); return; }
			busy = true;
			watch();
			try { video.currentTime = wanted; } catch (_) { clear(); options.failed(); }
		}
		function seeked() { busy = false; clear(); flush(); }
		video.addEventListener('seeked', seeked);
		video.addEventListener('loadeddata', flush);
		video.addEventListener('canplay', flush);
		return {
			request(time) { wanted = time; flush(); },
			resume() { suspended = false; flush(); },
			suspend() { suspended = true; clear(); },
			stop() {
				stopped = true; clear();
				video.removeEventListener('seeked', seeked);
				video.removeEventListener('loadeddata', flush);
				video.removeEventListener('canplay', flush);
			}
		};
	}
	if (typeof module === 'object' && module.exports) {
		module.exports = { clamp, scrollProgress, timeForProgress, chapterForProgress, createSeekQueue };
		return;
	}

	const root = document.querySelector('[data-dx-explorer]');
	if (!root) return;
	const find = (name) => root.querySelector('[data-ex-' + name + ']');
	const video = find('video'), stage = find('stage'), fallback = find('fallback');
	const poster = find('poster'), status = find('status');
	let config;
	try {
		config = JSON.parse(find('config').textContent);
		if (!video || !stage || !fallback || !config.sources.pc.url || !config.sources.sp.url || !config.chapters.length) return;
	} catch (_) { return; }
	// 章 2〜5 の右下ラベル。いまの章だけ .is-current で見せる（CSS でフェード）。
	const labels = Array.from(root.querySelectorAll('[data-ex-chapter]'));
	const header = document.querySelector('.p-header');
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	const portrait = window.matchMedia('(max-aspect-ratio: 1/1)');
	const fps = Number(config.fps) || 30;
	let active = false, failed = false, near = false;
	let progress = 0, start = 0, distance = 1, headerHeight = 0, chapter = -1;
	let frame = 0, generation = 0, loadTimer = 0, sourceKey = '', posterKey = '', queue = null, resizeAnchor = null;
	let sourceCleanup = () => {};
	const say = (text) => { if (status && status.textContent !== text) status.textContent = text; };
	const inside = () => active && window.scrollY >= start - 2 && window.scrollY <= start + distance + 2;
	function measure() {
		headerHeight = header ? header.getBoundingClientRect().height : 0;
		root.style.setProperty('--dxp-scroll-header', headerHeight + 'px');
		start = window.scrollY + root.getBoundingClientRect().top;
		distance = Math.max(1, root.offsetHeight - stage.offsetHeight);
	}
	function showChapter(value) {
		const next = chapterForProgress(value, config.chapters);
		if (chapter === next) return;
		chapter = next;
		// 全景（0）のときだけ固定コピーを見せ、それ以外は右下の章ラベルに切り替える（CSS）。
		root.dataset.chapter = String(next);
		const current = find('current');
		if (current) current.textContent = config.chapters[next].label;
		labels.forEach((label) => {
			label.classList.toggle('is-current', Number(label.getAttribute('data-ex-chapter')) === next);
		});
	}
	function jump(target, focus) {
		if (!target) return;
		resizeAnchor = null;
		window.scrollTo({ top: window.scrollY + target.getBoundingClientRect().top - headerHeight - 16, behavior: 'instant' });
		if (focus) {
			if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
			target.focus({ preventScroll: true });
		}
	}
	const article = (index) => document.getElementById('dx-view-' + config.chapters[index].id);
	function setPoster(key) {
		if (poster && posterKey !== key) poster.src = config.sources[key].poster;
		posterKey = key;
	}
	function stopSource() {
		generation += 1;
		clearTimeout(loadTimer); loadTimer = 0;
		sourceCleanup(); sourceCleanup = () => {};
		if (queue) queue.stop();
		queue = null;
		video.pause();
		if (video.hasAttribute('src')) { video.removeAttribute('src'); video.load(); }
		sourceKey = '';
	}
	// 静止画フォールバックへ切り替える（JS 無効・動きを減らす設定・読み込み失敗のときだけ）。
	function useStills(message) {
		const preserve = inside();
		const index = Math.max(0, chapter);
		resizeAnchor = null;
		stopSource(); active = false;
		root.classList.remove('is-enhanced');
		root.dataset.mode = 'static'; root.dataset.ready = 'false';
		fallback.hidden = false;
		say(message); measure();
		if (preserve) jump(article(index), false);
	}
	function failMedia() {
		failed = true;
		useStills('映像を読み込めなかったため、五つの見せ場を静止画で表示しています。');
	}
	function requestFrame() {
		if (queue && Number.isFinite(video.duration) && video.duration > 0)
			queue.request(timeForProgress(progress, video.duration, fps));
	}
	function watchLoad() {
		clearTimeout(loadTimer); loadTimer = 0;
		if (!document.hidden && active && sourceKey && root.dataset.ready !== 'true')
			loadTimer = setTimeout(failMedia, 30000);
	}
	function loadSource(key) {
		stopSource();
		sourceKey = key;
		const token = generation, source = config.sources[key];
		const expectedURL = new URL(source.url, document.baseURI).href;
		const valid = () => active && token === generation && video.currentSrc === expectedURL;
		root.dataset.mode = 'loading'; root.dataset.ready = 'false';
		setPoster(key);
		video.poster = source.poster;
		video.muted = true; video.playsInline = true; video.preload = 'auto'; video.autoplay = false; video.loop = false;
		say('映像を読み込んでいます。');
		queue = createSeekQueue(video, {
			valid,
			settled() {
				if (!valid() || document.hidden) return;
				clearTimeout(loadTimer); loadTimer = 0;
				root.dataset.ready = 'true'; root.dataset.mode = 'video';
				say('下へスクロールすると進み、上へ戻ると映像も戻ります。');
			},
			failed() { if (valid() && !document.hidden) failMedia(); }
		});
		function loaded() {
			if (!valid()) return;
			if (video.readyState >= 1 && (!Number.isFinite(video.duration) || video.duration <= 0)) { failMedia(); return; }
			// Never restore a captured scroll position after an asynchronous media event.
			measure(); progress = scrollProgress(window.scrollY, start, distance);
			showChapter(progress); requestFrame();
		}
		const error = () => { if (token === generation && active && video.error) failMedia(); };
		video.addEventListener('loadedmetadata', loaded);
		video.addEventListener('loadeddata', loaded);
		video.addEventListener('error', error);
		sourceCleanup = () => {
			video.removeEventListener('loadedmetadata', loaded);
			video.removeEventListener('loadeddata', loaded);
			video.removeEventListener('error', error);
		};
		video.src = source.url; video.load(); watchLoad();
	}
	function schedule() {
		if (!frame && !document.hidden) frame = requestAnimationFrame(update);
	}
	function update() {
		frame = 0;
		if (document.hidden) return;
		const anchor = resizeAnchor; resizeAnchor = null;
		measure();
		if (!active) return;
		if (anchor && Math.abs(window.scrollY - anchor.y) < 2) {
			window.scrollTo({ top: start + distance * anchor.progress, behavior: 'instant' });
		}
		progress = scrollProgress(window.scrollY, start, distance);
		showChapter(progress);
		const key = portrait.matches ? 'sp' : 'pc';
		if (sourceKey && sourceKey !== key) {
			stopSource(); root.dataset.ready = 'false'; root.dataset.mode = 'loading';
		}
		setPoster(key);
		if (!sourceKey && near) loadSource(key);
		requestFrame();
	}
	function enable() {
		const wasStatic = !active;
		const rect = root.getBoundingClientRect();
		const preserve = rect.top <= 0 && rect.bottom > 0;
		active = true; failed = false;
		// Reserve the entire rail before IntersectionObserver or media loading can run.
		root.classList.add('is-enhanced'); fallback.hidden = true;
		root.dataset.mode = 'loading'; root.dataset.ready = 'false';
		measure();
		// 静止画を見ている途中で映像に戻ったときは、同じ章の位置に合わせる。
		if (preserve && wasStatic) window.scrollTo({ top: start + distance * config.chapters[Math.max(0, chapter)].position, behavior: 'instant' });
		say('スクロールで五つの見せ場を巡ります。'); schedule();
	}
	function resized() {
		if (inside() && !resizeAnchor) resizeAnchor = { progress: scrollProgress(window.scrollY, start, distance), y: window.scrollY };
		schedule();
	}
	window.addEventListener('scroll', () => {
		if (resizeAnchor && Math.abs(window.scrollY - resizeAnchor.y) >= 2) resizeAnchor = null;
		schedule();
	}, { passive: true });
	window.addEventListener('resize', resized);
	window.addEventListener('orientationchange', resized);
	if (window.visualViewport) window.visualViewport.addEventListener('resize', resized);
	window.addEventListener('pageshow', schedule);
	portrait.addEventListener('change', resized);
	if (header && 'ResizeObserver' in window) new ResizeObserver(schedule).observe(header);
	document.addEventListener('visibilitychange', () => {
		if (document.hidden) {
			clearTimeout(loadTimer); loadTimer = 0;
			if (queue) queue.suspend();
		} else {
			if (queue) queue.resume();
			watchLoad(); schedule();
		}
	});
	reduced.addEventListener('change', () => {
		if (reduced.matches) useStills('動きを抑える設定に合わせて、静止画で表示しています。');
		else if (!failed) enable();
	});
	if ('IntersectionObserver' in window) new IntersectionObserver((entries) => {
		near = entries.some((entry) => entry.isIntersecting); schedule();
	}, { rootMargin: '500px 0px' }).observe(root);
	else near = true;
	if (reduced.matches) useStills('動きを抑える設定に合わせて、静止画で表示しています。');
	else enable();
	showChapter(0);
})();
