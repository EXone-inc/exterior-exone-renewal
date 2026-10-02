/**
 * 円ダイアグラムの 6 項目を、画面に入っている間だけ浮遊させる（支店の選ばれる理由）。
 *
 * 浮遊そのものは CSS（style.css の p-diagram-float / -rev と同じ keyframes・周期）。
 * ここでは [data-diagram-float] に .is-floating を付け外しするだけ。出現演出は無い。
 * prefers-reduced-motion: reduce では CSS 側で animation を外して静止させる。
 * IntersectionObserver 非対応の環境では常に動かす。
 */
(function () {
	'use strict';

	var figures = Array.prototype.slice.call(document.querySelectorAll('[data-diagram-float]'));

	if (!figures.length) {
		return;
	}

	if (!('IntersectionObserver' in window)) {
		figures.forEach(function (figure) {
			figure.classList.add('is-floating');
		});

		return;
	}

	var observer = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			entry.target.classList.toggle('is-floating', entry.isIntersecting);
		});
	});

	figures.forEach(function (figure) {
		observer.observe(figure);
	});
})();
