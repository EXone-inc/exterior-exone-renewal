/**
 * WORKS のカルーセル。
 *
 * PC はカンプ（917:118）どおり中央のアクティブカードを軸に左右のカードを
 * 傾けて重ねる（coverflow）。SP は傾きなしの横並び。
 * Swiper は effect を breakpoints で切り替えられないため、
 * matchMedia で設定ごと作り直す。
 */
(function () {
	'use strict';

	var container = document.querySelector('[data-works-carousel]');

	if (!container || typeof window.Swiper === 'undefined') {
		return;
	}

	var mq = window.matchMedia('(min-width: 769px)');
	var desc = document.querySelector('[data-works-desc]');
	var swiper = null;
	var isPc = null;

	/**
	 * カルーセルの下の枠に、選択中のカードのタイトルを出す。
	 * カンプ 917:117 の説明文の枠をそのまま使う（要素は増やさない）。
	 */
	function showTitle(slide) {
		if (!desc || !slide) {
			return;
		}

		var title = slide.getAttribute('data-works-title');

		if (title) {
			desc.textContent = title;
		}
	}

	function options(pc) {
		if (!pc) {
			return {
				slidesPerView: 'auto',
				spaceBetween: 19.09, // SP のカード間（917:881 → 917:882）
				grabCursor: true
			};
		}

		return {
			slidesPerView: 'auto',
			centeredSlides: true,
			// カンプは中央のカードを軸に左右 2 枚ずつ並ぶ
			initialSlide: Math.floor(container.querySelectorAll('.swiper-slide').length / 2),
			spaceBetween: 0,
			grabCursor: true,
			// カンプは面の 3D 回転ではなく平面回転の扇形なので creative を使う。
			// 1 枚隣: 中心間 397.56 / 下げ 41.79 / 回転 10.24 度 / 不透明度 0.5
			// 2 枚隣: 中心間 783.46 / 下げ 145.03 / 回転 17.34 度
			// （creative は進行度に比例するため 1 枚隣を基準に置き、2 枚隣は近似）
			// 不透明度は進行度に比例して 0 まで下がってしまうため CSS 側で固定する。
			effect: 'creative',
			creativeEffect: {
				limitProgress: 2,
				prev: {
					translate: ['-397.56px', '50px', 0],
					rotate: [0, 0, -9.5],
					scale: 0.9415 // 306.846 / 325.924
				},
				next: {
					translate: ['397.56px', '50px', 0],
					rotate: [0, 0, 9.5],
					scale: 0.9415
				}
			}
		};
	}

	function build() {
		var pc = mq.matches;

		if (pc === isPc) {
			return;
		}

		if (swiper) {
			swiper.destroy(true, true);
			swiper = null;
		}

		isPc = pc;
		swiper = new window.Swiper(container, options(pc));

		// PC は中央のカードから始まるため、初期表示もそのカードに合わせる。
		showTitle(swiper.slides[swiper.activeIndex]);

		swiper.on('slideChange', function () {
			showTitle(this.slides[this.activeIndex]);
		});
	}

	build();

	if (typeof mq.addEventListener === 'function') {
		mq.addEventListener('change', build);
	} else if (typeof mq.addListener === 'function') {
		mq.addListener(build);
	}
})();
