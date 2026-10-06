/**
 * ピン留めした FV に、スクロール量に応じて濃くなる暗幕をかける。
 *
 * .l-pin の中の「1 つ目のセクション」が留まる側（FV）、「2 つ目のセクション」が
 * その上にせり上がってくる側。2 つ目の上端が画面下端にある時点を 0、画面上端に
 * 達した時点を 1 として進捗を出し、暗幕を 0 → --philo-overlay（#090908 / 70%）
 * まで均一に濃くする。同じ進捗で FV の中身も消える。戻せば元に戻る。
 *
 * 使う側の組み合わせは .l-pin の中身だけで決まるので、この JS は中身を問わない:
 *   TOP      … .p-fv  + .p-philosophy
 *   企業情報 … .p-cfv + .p-cwhy
 *
 * 描画自体は CSS 側（.l-pin.is-scroll-mask::after ほか）が持ち、ここは進捗値
 * --fv-mask-progress を書き込むだけ。ピン留めは CSS の sticky で PC / SP とも有効。
 *
 * オプトイン: .l-pin に data-fv-mask-span があるとき（ハイエンド … .p-hepfv + 走路）だけ、
 * 進捗を「1 つ目が留まり始めてからのスクロール量 ÷ 2 つ目（走路）の高さ」にする。
 * 固定区間を画面 1 つ分より長く取れる。動きを減らす設定では .is-scroll-mask を付けず
 * 何もしない（CSS 側が完成状態を出す）。属性が無いページ（TOP・企業情報）は従来どおり。
 */
(function () {
	'use strict';

	var pin = document.querySelector('.l-pin');

	if (!pin) {
		return;
	}

	var sticky = pin.firstElementChild;
	var cover = pin.lastElementChild;

	// 2 つ揃っていなければ演出は成立しない（素の縦積みのまま出す）。
	if (!sticky || !cover || sticky === cover) {
		return;
	}

	var spanMode = pin.hasAttribute('data-fv-mask-span');

	if (spanMode && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	var ticking = false;
	var last = -1;
	var stickTop = 0;

	/**
	 * 暗転の進捗（0〜1）。1 画面分スクロールする間に 0 → 1 になる。
	 * 画像の読み込みでレイアウトがずれても追従するよう、毎回実測する。
	 */
	function measure() {
		if (spanMode) {
			return measureSpan();
		}

		var span = window.innerHeight;

		if (span <= 0) {
			return 1;
		}

		var ratio = 1 - cover.getBoundingClientRect().top / span;

		// 小数 3 桁で足りる。丸めておくと同値のときの書き込みを飛ばせる。
		return Math.min(1, Math.max(0, Math.round(ratio * 1000) / 1000));
	}

	/**
	 * オプトイン時の進捗（0〜1）。走路の高さぶんスクロールする間に 0 → 1 になる。
	 */
	function measureSpan() {
		var run = cover.getBoundingClientRect().height;

		if (run <= 0) {
			return 1;
		}

		var ratio = (stickTop - pin.getBoundingClientRect().top) / run;

		return Math.min(1, Math.max(0, Math.round(ratio * 1000) / 1000));
	}

	function write(value) {
		if (value === last) {
			return;
		}

		last = value;
		pin.style.setProperty('--fv-mask-progress', String(value));

		// ほぼ透明になった中身（TOP のサムネイル等）はクリック対象から外す。
		sticky.classList.toggle('is-masked', value >= 0.9);
	}

	function update() {
		ticking = false;
		write(measure());
	}

	function request() {
		if (ticking) {
			return;
		}

		ticking = true;
		window.requestAnimationFrame(update);
	}

	// 画面の高さが変わると進捗の分母が変わるので取り直す。
	function sync() {
		last = -1;

		// 留まる位置（SP はヘッダーの下）。ヘッダーの高さは画面幅で変わる。
		if (spanMode) {
			stickTop = parseFloat(window.getComputedStyle(sticky).top) || 0;
		}

		update();
	}

	pin.classList.add('is-scroll-mask');

	window.addEventListener('scroll', request, { passive: true });
	window.addEventListener('resize', sync);

	// 画像の読み込み完了でセクション位置が動くことがあるため、そこでも取り直す。
	window.addEventListener('load', request);

	sync();
})();
