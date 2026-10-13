/**
 * 採用ページの流れる帯（[data-plp-imglist]。FV の写真スライダーなど）の速さを px/秒 で揃える。
 *
 * 流す仕組みは js/plans-imglist.js（変更しない）。あちらは「画面幅 1 つ分を流れる時間」
 *（--plp-imglist-screen-time）で速さを決めるので、列の CSS の --rc-flow-speed（px/秒。
 * 無ければ --rc-slide-speed）から「枠の幅 ÷ 速さ」を求めて列に入れる。
 * 現サイトの Splide AutoScroll（1 フレームあたり px）を 60Hz 相当に固定した値（仕様書 Q2）。
 *
 * plans-imglist.js より先に読み込む（functions.php で依存にしている）。ResizeObserver は
 * 登録順に呼ばれるので、リサイズ時もこちらが先に値を入れ直してから plans-imglist.js が測る。
 */
(function () {
	'use strict';

	var rows = Array.prototype.slice.call(document.querySelectorAll('.is-recruit-page [data-plp-imglist]'));

	function speedOf(row) {
		var style = window.getComputedStyle(row);
		var value = parseFloat(style.getPropertyValue('--rc-flow-speed'));

		if (!(value > 0)) {
			value = parseFloat(style.getPropertyValue('--rc-slide-speed'));
		}

		return value > 0 ? value : 60;
	}

	function setup(row) {
		var frame = row.parentElement;

		if (!frame) {
			return;
		}

		function update() {
			var width = frame.clientWidth;

			if (!width) {
				return;
			}

			row.style.setProperty('--plp-imglist-screen-time', (width / speedOf(row)).toFixed(4) + 's');
		}

		if ('ResizeObserver' in window) {
			new ResizeObserver(update).observe(frame);
		} else {
			window.addEventListener('resize', update);
		}

		update();
	}

	rows.forEach(setup);
})();
