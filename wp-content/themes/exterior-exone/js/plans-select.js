/**
 * PLANS ページ: プランの選択と切り替え（PLAN select の演出・タブ・URL ハッシュ）。
 *
 * ・状態は body[data-plp-selected] の 3 つ（none / package / high-end）。JS が動くときだけ
 *   body に .is-plp-enhanced を付け、css/plans.css がこの 2 つで出し分ける
 *   （未選択 = PLAN select + 共通 2 セクション / 選択後 = 選んだプランの詳細 + 共通 2 セクション）。
 *   JS が動かないときは何も隠れず、PLAN select の左右はページ内リンクのまま
 * ・PLAN select の半分を押すと、①選んだ半分の中心からその色の円が広がる ②プラン名が詳細冒頭の
 *   位置へ動く ③プラン名の下が詳細の中身に入れ替わる、を同時に行う。詳細は PLAN select と
 *   同じ上端から始まるので、演出中は PLAN select を詳細と重ねておき、終わったら外す
 * ・選んだ後はタブで切り替える（丸の広がりは使わず、上に重ねた方をふわっと出す）
 * ・選択・切り替えのたびに #package / #high-end を replaceState で書き換える（履歴は増やさない）。
 *   ハッシュ付きで開いたときは演出なしでそのプランを選んだ状態にする
 * ・プランの詳細を表示した時点で document に plp:planchange を投げる
 *   （detail: plan / previous / animated。非表示だった詳細の中の動きを始める合図）
 * ・動きを減らす設定では、どれも即時に切り替える
 *
 * 時間・イージングは css/plans.css の --plp-select-*（演出）と --transition-base（タブ・切り替え）。
 */
(function () {
	'use strict';

	var body = document.body;
	var select = document.querySelector('.p-plpselect');
	var common = document.querySelector('.p-plpprocess');
	var tabsNav = document.querySelector('[data-plp-tabs]');
	var tabs = tabsNav ? Array.prototype.slice.call(tabsNav.querySelectorAll('[data-plp-tab]')) : [];
	var plans = {};

	Array.prototype.forEach.call(document.querySelectorAll('[data-plp-plan]'), function (el) {
		plans[el.getAttribute('data-plp-plan')] = el;
	});

	if (!select || !plans.package || !plans['high-end']) {
		return;
	}

	var reducedQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
	var current = 'none';
	var busy = false;
	var userScrolled = false;

	function planFromHash() {
		var key = window.location.hash.replace(/^#/, '');

		return Object.prototype.hasOwnProperty.call(plans, key) ? key : null;
	}

	// CSS の時間（例 "1.5s" / "0.3s ease"）をミリ秒に直す
	function durationOf(el, prop) {
		var match = window.getComputedStyle(el).getPropertyValue(prop).match(/([\d.]+)(ms|s)/);

		if (!match) {
			return 0;
		}

		return parseFloat(match[1]) * (match[2] === 's' ? 1000 : 1);
	}

	function reflow(el) {
		return el.getBoundingClientRect();
	}

	// 演出中はホイール・タッチでスクロールさせない（円が届いていない範囲に元の画面が覗くため）。
	// キーボード・スクロールバーは止めない
	function blockScroll(event) {
		event.preventDefault();
	}

	function lockScroll(lock) {
		var method = lock ? 'addEventListener' : 'removeEventListener';

		window[method]('wheel', blockScroll, { passive: false });
		window[method]('touchmove', blockScroll, { passive: false });
	}

	// 上端を画面上端（SP はヘッダーの下端。scroll-margin-top）に合わせる。
	// html の scroll-behavior: smooth を効かせず、進行中のなめらかなスクロールも止めてその場で合わせる。
	// スクロール位置は整数にする（端数のままだと、マスクの掛かった要素とそうでない要素で描画位置の
	// 丸め方が食い違い、境目に 1px の線が出る）
	function alignTo(el) {
		var margin = parseFloat(window.getComputedStyle(el).scrollMarginTop) || 0;

		window.scrollTo({
			top: Math.round(el.getBoundingClientRect().top + window.pageYOffset - margin),
			behavior: 'instant'
		});
	}

	// 演出の間だけ、PLAN select の上端と高さを画素ちょうどの位置に揃える（CSS が --plp-snap ぶん
	// ずらす）。端数のままだと、半分の下端や詳細の透かしの縁が半端な画素になり、下にある別の色が
	// 1px の線として覗く（1366×768 の最下行、SP の PLAN select 下端など）
	function snapSelect() {
		body.style.removeProperty('--plp-snap');
		body.style.removeProperty('--plp-select-h');
		alignTo(select);

		var rect = select.getBoundingClientRect();

		body.style.setProperty('--plp-snap', Math.round(rect.top) - rect.top + 'px');
		body.style.setProperty('--plp-select-h', Math.round(rect.height) + 'px');
	}

	// 共通セクションを、詳細が伸びたぶん引き上げて元の位置（PLAN select の下端）に見せておく
	function shiftCommon() {
		if (!common) {
			return;
		}

		var shift = parseFloat(body.style.getPropertyValue('--plp-shift')) || 0;

		body.style.setProperty('--plp-shift', Math.round(select.getBoundingClientRect().bottom) - (common.getBoundingClientRect().top - shift) + 'px');
	}

	function pressTab(plan) {
		tabs.forEach(function (tab) {
			tab.setAttribute('aria-pressed', tab.getAttribute('data-plp-tab') === plan ? 'true' : 'false');
		});
	}

	function setState(plan) {
		current = plan;
		body.setAttribute('data-plp-selected', plan);
		pressTab(plan);
	}

	function writeHash(plan) {
		window.history.replaceState(window.history.state, '', '#' + plan);
	}

	function announce(plan, previous, animated) {
		document.dispatchEvent(new CustomEvent('plp:planchange', {
			detail: { plan: plan, previous: previous, animated: animated }
		}));
	}

	function showTabs(instant) {
		if (!tabsNav) {
			return;
		}

		if (instant) {
			tabsNav.classList.add('is-instant');
		}

		tabsNav.classList.add('is-visible');

		if (instant) {
			reflow(tabsNav);
			tabsNav.classList.remove('is-instant');
		}
	}

	/**
	 * 演出なしでプランを選んだ状態にする（ハッシュ付きで開いたとき・動きを減らす設定）。
	 * 選んだ半分にフォーカスがあったら詳細へ移す（半分は非表示になるため）。
	 */
	function selectNow(plan) {
		var hadFocus = select.contains(document.activeElement);

		setState(plan);
		alignTo(plans[plan]);
		showTabs(true);
		announce(plan, 'none', false);

		if (hadFocus) {
			plans[plan].focus({ preventScroll: true });
		}
	}

	/**
	 * PLAN select の半分を押したとき。
	 */
	function pick(plan) {
		if (busy || current !== 'none') {
			return;
		}

		writeHash(plan);

		if (reducedQuery.matches) {
			selectNow(plan);

			return;
		}

		busy = true;
		lockScroll(true);
		snapSelect();

		// フォーカスに伴うスクロールが後から入ることがあるので、次のフレームでも合わせ直す
		window.requestAnimationFrame(function () {
			if (body.classList.contains('is-plp-selecting')) {
				snapSelect();
				shiftCommon();
			}
		});

		var half = select.querySelector('[data-plp-select="' + plan + '"]');
		var other = select.querySelector('[data-plp-select]:not([data-plp-select="' + plan + '"])');
		var fromName = half.querySelector('[data-plp-select-part="name"]');
		var detail = plans[plan];
		var name = detail.querySelector('[data-plp-plan-name]');

		// ① 円: 選んだ半分の中心を、もう一方の半分の座標で表す。半径はその半分の一番遠い角まで。
		// 画面が PLAN select より縦長で下に帯が見えているときは、画面の下の角までを覆う
		var selectRect = select.getBoundingClientRect();
		var halfRect = half.getBoundingClientRect();
		var otherRect = other.getBoundingClientRect();
		var cx = halfRect.left + halfRect.width / 2 - otherRect.left;
		var cy = halfRect.top + halfRect.height / 2 - otherRect.top;
		var px = halfRect.left + halfRect.width / 2 - selectRect.left;
		var bottom = Math.max(window.innerHeight - selectRect.top, selectRect.height);
		var radius = Math.max(
			Math.hypot(cx, cy),
			Math.hypot(cx - otherRect.width, cy),
			Math.hypot(cx, cy - otherRect.height),
			Math.hypot(cx - otherRect.width, cy - otherRect.height),
			Math.hypot(px, bottom - cy),
			Math.hypot(selectRect.width - px, bottom - cy)
		);

		[other, detail].forEach(function (el) {
			el.style.setProperty('--plp-circle-x', (el === other ? cx : px) + 'px');
			el.style.setProperty('--plp-circle-y', cy + 'px');
			el.style.setProperty('--plp-circle-r', '0px');
		});

		// ② プラン名: 選択画面の名前の位置・大きさを覚えておく
		var fromRect = fromName.getBoundingClientRect();
		var fromSize = parseFloat(window.getComputedStyle(fromName.lastElementChild).fontSize);

		half.classList.add('is-picked');
		other.classList.add('is-covered');
		body.classList.add('is-plp-selecting');
		setState(plan);
		announce(plan, 'none', true);

		shiftCommon();

		// 詳細側の名前の写しを、詳細側の名前と同じ位置・大きさで一番上に重ね、
		// 選択画面の名前の位置・大きさから戻していく（本物は演出中は隠しておく）
		var toRect = name.getBoundingClientRect();
		var toSize = parseFloat(window.getComputedStyle(name.lastElementChild).fontSize);
		var flyer = name.cloneNode(true);

		flyer.removeAttribute('data-plp-plan-name');
		flyer.classList.add('p-plpselect__flyer');
		flyer.setAttribute('aria-hidden', 'true');
		flyer.style.color = window.getComputedStyle(name).color;
		flyer.style.left = '0px';
		flyer.style.top = '0px';
		body.appendChild(flyer);

		// 写しは文字の幅だけの箱（動かしてもページの横幅を広げない）。中心を詳細側の名前に合わせる
		var base = flyer.getBoundingClientRect();
		var toCenter = toRect.left + toRect.width / 2;
		var dx = fromRect.left + fromRect.width / 2 - toCenter;
		var dy = fromRect.top - toRect.top;

		flyer.style.left = toCenter - base.width / 2 - base.left + 'px';
		flyer.style.top = toRect.top - base.top + 'px';
		flyer.style.transform = 'translate(' + dx + 'px, ' + dy + 'px) scale(' + fromSize / toSize + ')';
		reflow(flyer);

		// ①②③ を同時に始める（③ は CSS の .is-plp-selecting-run）
		body.classList.add('is-plp-selecting-run');
		flyer.style.transform = 'none';
		other.style.setProperty('--plp-circle-r', radius + 'px');
		detail.style.setProperty('--plp-circle-r', radius + 'px');

		window.setTimeout(function () {
			var hadFocus = select.contains(document.activeElement);

			flyer.parentNode.removeChild(flyer);
			other.removeAttribute('style');
			detail.removeAttribute('style');
			body.style.removeProperty('--plp-shift');
			body.style.removeProperty('--plp-snap');
			body.style.removeProperty('--plp-select-h');

			if (!body.getAttribute('style')) {
				body.removeAttribute('style');
			}
			half.classList.remove('is-picked');
			other.classList.remove('is-covered');
			body.classList.remove('is-plp-selecting', 'is-plp-selecting-run');
			busy = false;
			lockScroll(false);
			showTabs(false);

			if (hadFocus) {
				detail.focus({ preventScroll: true });
			}
		}, durationOf(document.documentElement, '--plp-select-duration'));
	}

	/**
	 * タブでもう一方のプランへ切り替える。
	 */
	function switchTo(plan) {
		if (busy || current === 'none' || plan === current) {
			return;
		}

		var previous = current;
		var from = plans[previous];
		var to = plans[plan];

		writeHash(plan);
		alignTo(from);

		if (reducedQuery.matches) {
			setState(plan);
			alignTo(to);
			announce(plan, previous, false);

			return;
		}

		busy = true;
		pressTab(plan);

		// 切り替え先を今の詳細の上端に重ね、ふわっと出す
		to.classList.add('is-plp-incoming');
		to.style.top = '0px';
		to.style.top = from.getBoundingClientRect().top - to.getBoundingClientRect().top + 'px';
		announce(plan, previous, true);
		reflow(to);
		to.classList.add('is-plp-incoming-run');

		var done = false;

		function finish() {
			if (done) {
				return;
			}

			done = true;
			to.removeEventListener('transitionend', onEnd);
			setState(plan);
			to.classList.remove('is-plp-incoming', 'is-plp-incoming-run');
			to.removeAttribute('style');
			alignTo(to);
			busy = false;
		}

		function onEnd(event) {
			if (event.target === to && event.propertyName === 'opacity') {
				finish();
			}
		}

		to.addEventListener('transitionend', onEnd);
		window.setTimeout(finish, durationOf(to, 'transition-duration') + 100);
	}

	// --- 初期化 ---
	body.classList.add('is-plp-enhanced');
	Object.keys(plans).forEach(function (key) {
		plans[key].setAttribute('tabindex', '-1');
	});

	// 状態はハッシュでしか持たないので、戻る・再読み込みでのスクロール位置の復元は使わない
	if ('scrollRestoration' in window.history) {
		window.history.scrollRestoration = 'manual';
	}

	if (tabsNav) {
		// タブをヘッダーの中へ移す。ヘッダーと同じ重なりの層に入れることで、ヘッダーの地の上・
		// STORE のドロップダウンの下に来る（別の層のままだと、後から描かれるタブがドロップダウンを隠す）
		var header = document.querySelector('.p-header');

		if (header) {
			header.appendChild(tabsNav);
		}

		tabsNav.hidden = false;
	}

	var initial = planFromHash();

	if (initial) {
		selectNow(initial);

		// 画像やフォントの読み込みで位置がずれた場合に合わせ直す（自分でスクロールしていなければ）
		['wheel', 'touchstart', 'keydown'].forEach(function (type) {
			window.addEventListener(type, function () {
				userScrolled = true;
			}, { once: true, passive: true });
		});

		window.addEventListener('load', function () {
			if (!userScrolled && current !== 'none') {
				alignTo(plans[current]);
			}
		});
	} else {
		setState('none');
	}

	select.addEventListener('click', function (event) {
		var half = event.target.closest('[data-plp-select]');

		if (!half) {
			return;
		}

		event.preventDefault();
		pick(half.getAttribute('data-plp-select'));
	});

	tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			switchTo(tab.getAttribute('data-plp-tab'));
		});
	});

	// ページ内リンクなどでハッシュだけが変わったとき
	window.addEventListener('hashchange', function () {
		var plan = planFromHash();

		if (!plan || plan === current || busy) {
			return;
		}

		if (current === 'none') {
			selectNow(plan);
		} else {
			switchTo(plan);
		}
	});
})();
