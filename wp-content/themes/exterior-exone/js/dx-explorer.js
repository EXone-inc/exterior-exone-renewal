(function () {
	'use strict';
	const clamp = (value) => Math.min(1, Math.max(0, Number.isFinite(value) ? value : 0));
	const scrollProgress = (y, start, distance) => clamp((y - start) / Math.max(1, distance));
	// Aim inside the final frame, not its boundary (duration may be rounded by the browser).
	const timeForProgress = (progress, duration, fps) => clamp(progress) * Math.max(0, duration - 0.5 / fps);
	const chapterForProgress = (progress, chapters) => chapters.reduce((best, chapter, index) =>
		Math.abs(chapter.position - progress) < Math.abs(chapters[best].position - progress) ? index : best, 0);
	// 固定している区間を章の数で等分し、いまいる区画の章を返す（DX のステップ js/dx-steps.js と同じ区切り方）。
	const chapterForScroll = (progress, count) => Math.min(count - 1, Math.floor(clamp(progress) * count));

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
		module.exports = { clamp, scrollProgress, timeForProgress, chapterForProgress, chapterForScroll, createSeekQueue };
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
	const intro = root.querySelector('.p-dxex__inner');
	const header = document.querySelector('.p-header');
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	const portrait = window.matchMedia('(max-aspect-ratio: 1/1)');
	const fps = Number(config.fps) || 30;
	let active = false, failed = false, near = false;
	// 映像の代わりに章ごとの静止画をフェードで切り替える表示（動きを減らす設定・映像を読めないとき）。
	// 画面固定とスクロールでの章の進み方は映像と同じ。
	let stillMode = false, stillLayer = null;
	let progress = 0, start = 0, distance = 1, headerHeight = 0, chapter = -1;
	// 映像はスクロール量に比例させず、章が変わったら次の章の位置まで自動で進める（戻るときは逆向きにコマ送り）。
	// shownTime = いま映像に求めている時刻、travelRate = 進める速さ（映像の秒 / 実時間の秒）。
	let shownTime = 0, travelFrame = 0, travelTick = 0, travelRate = 2;
	let frame = 0, generation = 0, loadTimer = 0, sourceKey = '', posterKey = '', queue = null, resizeAnchor = null;
	let sourceCleanup = () => {};
	const say = (text) => { if (status && status.textContent !== text) status.textContent = text; };
	const inside = () => active && window.scrollY >= start - 2 && window.scrollY <= start + distance + 2;
	function measure() {
		headerHeight = header ? header.getBoundingClientRect().height : 0;
		root.style.setProperty('--dxp-scroll-header', headerHeight + 'px');
		// PC は全景のコピーを映像の上の帯に置く（CSS）。帯はふつうに流れ、映像が画面いっぱいに
		// なった位置から固定が始まるので、帯の高さを CSS に渡し、進み具合の起点もそのぶん下げる。
		// SP はコピーを映像に重ねたまま（position: absolute）なので 0。
		const band = intro && window.getComputedStyle(intro).position !== 'absolute' ? intro.offsetHeight : 0;
		root.style.setProperty('--dxp-ex-intro-h', band + 'px');
		start = window.scrollY + root.getBoundingClientRect().top + band;
		distance = Math.max(1, root.offsetHeight - stage.offsetHeight);
	}
	// 章の文字（全景の固定コピー / 章 2〜5 の右下ラベル）。index が null のあいだ（映像が移動中）は
	// どれも出さない。全景（0）のときだけ固定コピーを見せ、それ以外は右下の章ラベル（CSS）。
	function setLabel(index) {
		root.dataset.chapter = index === null ? 'moving' : String(index);
		labels.forEach((label) => {
			label.classList.toggle('is-current', Number(label.getAttribute('data-ex-chapter')) === index);
		});
	}
	const timeForChapter = (index) => timeForProgress(config.chapters[index].position, video.duration, fps);
	const canTravel = () => !stillMode && queue && Number.isFinite(video.duration) && video.duration > 0;
	function travelNumber(name, fallback) {
		const value = parseFloat(window.getComputedStyle(root).getPropertyValue(name));
		return Number.isFinite(value) && value > 0 ? value : fallback;
	}
	// いまの章の位置へ映像を進める。1 章ぶんは --dxp-ex-travel-rate 倍速、何章も飛ばしたときは
	// --dxp-ex-travel-max 秒で着くように速める。着いたら章の文字を出す。
	function travel(now) {
		travelFrame = 0;
		if (!canTravel() || document.hidden || chapter < 0) return;
		const target = timeForChapter(chapter);
		const step = Math.min(0.1, Math.max(0, (now - travelTick) / 1000)) * travelRate;
		travelTick = now;
		const remaining = target - shownTime;
		if (Math.abs(remaining) <= Math.max(step, 0.5 / fps)) {
			shownTime = target;
			queue.request(shownTime);
			setLabel(chapter);
			return;
		}
		shownTime += Math.sign(remaining) * step;
		queue.request(shownTime);
		travelFrame = requestAnimationFrame(travel);
	}
	function startTravel() {
		if (!canTravel() || chapter < 0) return;
		const gap = Math.abs(timeForChapter(chapter) - shownTime);
		if (gap <= 0.5 / fps) { setLabel(chapter); return; }
		travelRate = Math.max(travelNumber('--dxp-ex-travel-rate', 2), gap / travelNumber('--dxp-ex-travel-max', 2.4));
		setLabel(null);
		travelTick = performance.now();
		if (!travelFrame) travelFrame = requestAnimationFrame(travel);
	}
	// 映像を今の章の位置へ直接合わせる（読み込み直後など。移動は見せない）。
	function jumpToChapter() {
		if (!canTravel() || chapter < 0) return;
		cancelAnimationFrame(travelFrame); travelFrame = 0;
		shownTime = timeForChapter(chapter);
		queue.request(shownTime);
		setLabel(chapter);
	}
	function showChapter(value) {
		const next = chapterForScroll(value, config.chapters.length);
		if (chapter === next) return;
		chapter = next;
		const current = find('current');
		if (current) current.textContent = config.chapters[next].label;
		if (stillMode) { setLabel(next); showStill(next); return; }
		// 映像を使えるときは次の章まで映像を進め、着いてから文字を出す。まだ読み込めていなければ文字だけ。
		if (canTravel()) startTravel();
		else setLabel(next);
	}
	// 静止画の層は、JS 無効時用の静止画（.p-dxex__fallback の picture）を複製して作る。
	function buildStills() {
		if (stillLayer) return;
		stillLayer = document.createElement('div');
		stillLayer.className = 'p-dxex__stills';
		stillLayer.setAttribute('aria-hidden', 'true');
		fallback.querySelectorAll('picture').forEach((picture) => {
			const copy = picture.cloneNode(true);
			const img = copy.querySelector('img');
			if (img) { img.removeAttribute('loading'); img.alt = ''; }
			stillLayer.appendChild(copy);
		});
		const media = root.querySelector('.p-dxex__media');
		(media || stage).appendChild(stillLayer);
	}
	function showStill(index) {
		if (!stillLayer) return;
		Array.from(stillLayer.children).forEach((item, i) => item.classList.toggle('is-current', i === index));
	}
	function enterStillMode(message) {
		stillMode = true;
		stopSource();
		buildStills();
		root.dataset.mode = 'stills'; root.dataset.ready = 'false';
		cancelAnimationFrame(travelFrame); travelFrame = 0;
		setLabel(Math.max(0, chapter));
		showStill(Math.max(0, chapter));
		say(message); schedule();
	}
	function leaveStillMode() {
		stillMode = false;
		root.dataset.mode = 'loading'; root.dataset.ready = 'false';
		schedule();
	}
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
	// 映像を読み込めなかったときは、静止画の切り替え表示にする（画面固定はそのまま）。
	function failMedia() {
		failed = true;
		enterStillMode('映像を読み込めなかったため、五つの見せ場を静止画で表示しています。');
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
				say('スクロールすると、次の見せ場へ映像が進みます。上へ戻ると前の見せ場に戻ります。');
			},
			failed() { if (valid() && !document.hidden) failMedia(); }
		});
		function loaded() {
			if (!valid()) return;
			if (video.readyState >= 1 && (!Number.isFinite(video.duration) || video.duration <= 0)) { failMedia(); return; }
			// Never restore a captured scroll position after an asynchronous media event.
			measure(); progress = scrollProgress(window.scrollY, start, distance);
			showChapter(progress); jumpToChapter();
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
		armUnlock(token);
	}
	// iOS の低電力モードでは、画面に触れるまで動画のデータを読み込まない（metadata 止まりで
	// シークしても絵が出ず、30 秒後に静止画へ落ちる）。最初に触れた・押した直後に
	// 再生 → 即停止して読み込みを始めさせ、今の章の絵を出し直す。
	// 仕組みは TOP の FV（js/fv-slider.js の armRetry）と同じ。
	function armUnlock(token) {
		const unlock = () => {
			window.removeEventListener('touchstart', unlock);
			window.removeEventListener('pointerdown', unlock);
			if (token !== generation || !active || stillMode || root.dataset.ready === 'true') return;
			const played = video.play();
			const settle = () => { video.pause(); watchLoad(); jumpToChapter(); };
			if (played && played.then) played.then(settle).catch(settle);
			else settle();
		};
		window.addEventListener('touchstart', unlock, { passive: true });
		window.addEventListener('pointerdown', unlock);
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
		if (stillMode) return;
		const key = portrait.matches ? 'sp' : 'pc';
		if (sourceKey && sourceKey !== key) {
			stopSource(); root.dataset.ready = 'false'; root.dataset.mode = 'loading';
		}
		setPoster(key);
		if (!sourceKey && near) loadSource(key);
	}
	function enable() {
		active = true;
		// Reserve the entire rail before IntersectionObserver or media loading can run.
		root.classList.add('is-enhanced'); fallback.hidden = true;
		root.dataset.mode = 'loading'; root.dataset.ready = 'false';
		measure();
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
			watchLoad(); schedule(); startTravel();
		}
	});
	// 動きを減らす設定では、映像（章が変わるたびにカメラが移動する）の代わりに静止画の切り替えにする。
	const REDUCED_MESSAGE = '動きを抑える設定に合わせて、五つの見せ場を静止画で切り替えています。';
	reduced.addEventListener('change', () => {
		if (reduced.matches) enterStillMode(REDUCED_MESSAGE);
		else if (!failed) leaveStillMode();
	});
	if ('IntersectionObserver' in window) new IntersectionObserver((entries) => {
		near = entries.some((entry) => entry.isIntersecting); schedule();
	}, { rootMargin: '500px 0px' }).observe(root);
	else near = true;
	enable();
	if (reduced.matches) enterStillMode(REDUCED_MESSAGE);
	showChapter(0);
})();
