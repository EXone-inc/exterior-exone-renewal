/**
 * TOP / SP のスクロール区間を、表示開始時のビューポート高で固定する。
 * アプリ内ブラウザのバー出入りによる高さだけの resize で、下方の NEWS 等を動かさない。
 * 幅変更・画面回転では再計測し、PC は従来の可変高さに戻す。
 */
(function () {
	'use strict';

	if (!document.body.classList.contains('is-front-page')) {
		return;
	}

	var root = document.documentElement;
	var mobile = window.matchMedia('(max-width: 1024px)');
	var width = 0;
	var scrollHeight = 0;
	var units = ['vh', 'svh', 'lvh', 'dvh'];

	// FV の暗転と DX のステップ送りも、CSS と同じ固定期間で計算する。
	window.exoneFrontViewportHeight = function () {
		return scrollHeight || window.innerHeight;
	};

	function sync(force) {
		var nextWidth = root.clientWidth;

		if (!force && nextWidth === width) {
			return;
		}

		width = nextWidth;

		if (!mobile.matches) {
			scrollHeight = 0;
			root.classList.remove('has-stable-front-viewport');
			units.forEach(function (unit) {
				root.style.removeProperty('--front-viewport-' + unit);
			});
		} else {
			// lvh の背景と svh のコピー領域を別々に保ち、既存の FV の見た目を維持する。
			var probe = document.createElement('div');
			probe.style.cssText = 'position:fixed;top:0;left:0;width:0;visibility:hidden;pointer-events:none;contain:strict;';
			document.body.appendChild(probe);

			units.forEach(function (unit) {
				probe.style.height = '100vh';
				probe.style.height = '100' + unit;
				root.style.setProperty('--front-viewport-' + unit, probe.getBoundingClientRect().height + 'px');
			});

			probe.remove();
			scrollHeight = window.innerHeight;
			root.classList.add('has-stable-front-viewport');
		}

		window.dispatchEvent(new Event('exone:front-viewport-change'));
	}

	window.addEventListener('resize', function () { sync(false); });
	window.addEventListener('pageshow', function () { sync(false); });
	window.addEventListener('orientationchange', function () {
		window.requestAnimationFrame(function () { sync(true); });
	});

	sync(true);
})();
