/**
 * ピン留めした FV に、スクロール量に応じて濃くなる暗幕をかける。
 *
 * 理念セクションの上端が画面下端にある時点を 0、画面上端に達した時点を 1 として
 * 進捗を出し、暗幕を 0 → --philo-overlay（#090908 / 70%）まで均一に濃くする。
 * 同じ進捗で FV のコピーとサムネイルも消える。スクロールを戻せば元に戻る。
 *
 * 描画自体は CSS 側（.l-pin.is-scroll-mask::after）が持ち、ここは進捗値
 * --fv-mask-progress を書き込むだけ。ピン留めは CSS の sticky で PC / SP とも有効。
 */
(function () {
	'use strict';

	var pin = document.querySelector('.l-pin');

	if (!pin) {
		return;
	}

	var fv = pin.querySelector('.p-fv');
	var philosophy = pin.querySelector('.p-philosophy');

	if (!fv || !philosophy) {
		return;
	}

	var ticking = false;
	var last = -1;

	/**
	 * 暗転の進捗（0〜1）。1 画面分スクロールする間に 0 → 1 になる。
	 * 画像の読み込みでレイアウトがずれても追従するよう、毎回実測する。
	 */
	function measure() {
		var span = window.innerHeight;

		if (span <= 0) {
			return 1;
		}

		var ratio = 1 - philosophy.getBoundingClientRect().top / span;

		// 小数 3 桁で足りる。丸めておくと同値のときの書き込みを飛ばせる。
		return Math.min(1, Math.max(0, Math.round(ratio * 1000) / 1000));
	}

	function write(value) {
		if (value === last) {
			return;
		}

		last = value;
		pin.style.setProperty('--fv-mask-progress', String(value));

		// ほぼ透明になったサムネイルはクリック対象から外す。
		fv.classList.toggle('is-masked', value >= 0.9);
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
		update();
	}

	pin.classList.add('is-scroll-mask');

	window.addEventListener('scroll', request, { passive: true });
	window.addEventListener('resize', sync);

	// 画像の読み込み完了でセクション位置が動くことがあるため、そこでも取り直す。
	window.addEventListener('load', request);

	sync();
})();
