/**
 * 理念セクションの円環図の出現順。
 *
 * 1. 中央のライン画が画面に入り、線描画される（js/line-draw.js。SVG に .is-drawing → .is-drawn）
 * 2. 描き終わった合図（SVG の .is-drawn）で、figure に .is-drawn を付け、
 *    リング・ロゴ・文言・6 項目が順にふわっとフェードインする（CSS）
 * 3. 最後の項目のフェードインが終わったら .is-alive を付け、6 項目の浮遊が始まる（CSS）
 *
 * 時間の内訳は style.css の --diagram-fade-* を参照。
 * prefers-reduced-motion: reduce では最初から完成状態にする（浮遊は CSS 側で止める）。
 */
(function () {
	'use strict';

	var figure = document.querySelector('[data-philosophy-diagram]');

	if (!figure) {
		return;
	}

	var art = figure.querySelector('svg[data-line-draw]');
	// フェードイン完了の合図を受ける要素。項目は順に出るので、最後に出る 6 つ目の文言で受ける
	//（リングは PC / SP で片方が display: none のため transitionend が来ないことがある）。
	var fadeTarget = figure.querySelector('.p-diagram__copy--6') || figure.querySelector('.p-diagram__icon');
	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function readSeconds(name) {
		var value = getComputedStyle(figure).getPropertyValue(name).trim();

		return value.slice(-2) === 'ms' ? parseFloat(value) / 1000 : parseFloat(value) || 0;
	}

	function alive() {
		figure.classList.add('is-alive');
	}

	function drawn() {
		if (figure.classList.contains('is-drawn')) {
			return;
		}

		figure.classList.add('is-drawn');

		if (reduced) {
			alive();

			return;
		}

		// フェードインが終わってから浮遊を始める。transitionend が来ない環境の保険に、
		// 最後の項目が出終わる時刻（開始遅れ + 5 段の間隔 + フェード時間）+ 少しで始めるタイマーも置く。
		var fired = false;
		var once = function () {
			if (fired) {
				return;
			}

			fired = true;
			alive();
		};

		if (fadeTarget) {
			fadeTarget.addEventListener('transitionend', function (event) {
				if (event.propertyName === 'opacity') {
					once();
				}
			}, { once: true });
		}

		var total = readSeconds('--diagram-fade-items-start') + 5 * readSeconds('--diagram-fade-stagger') + readSeconds('--diagram-fade-duration');

		window.setTimeout(once, total * 1000 + 150);
	}

	if (!art) {
		drawn();

		return;
	}

	// line-draw.js が SVG に .is-drawn を付けたら次の段階へ。すでに付いていればすぐ。
	if (art.classList.contains('is-drawn')) {
		drawn();
	} else if (typeof window.MutationObserver === 'function') {
		var observer = new window.MutationObserver(function () {
			if (art.classList.contains('is-drawn')) {
				observer.disconnect();
				drawn();
			}
		});

		observer.observe(art, { attributes: true, attributeFilter: ['class'] });
	} else {
		drawn();
	}
})();
