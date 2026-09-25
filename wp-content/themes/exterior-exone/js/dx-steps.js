/**
 * DX EXPERIENCE ページ: ステップ 01〜04 のスクロール切り替え。
 *
 * TOP の DX セクション（js/dx-slider.js）と同じく、ステップ部分を画面に固定し、
 * 固定している区間のスクロール量で 01 → 04 を 1 画面ずつ切り替える。
 *
 * ・切り替わるときは今のステップが左へ流れ、次のステップが右から入る
 *   （上へ戻るときは逆向き）。流すのは入れ替わる 2 枚だけで、速くスクロールして
 *   飛ばしたステップは見えないまま位置だけ移す
 * ・フローバーは 1 本だけ。今のステップに当たる点をアクティブにする（線は伸ばさない）
 * ・見えていないステップは inert にして、中のボタンにフォーカスが入らないようにする
 * ・JS が動かないときは .is-enhanced が付かず、01〜04 が縦に並ぶ
 *
 * 見た目（固定・流れ方・速さ）は css/dx.css の「ステップのスクロール切り替え」。
 */
(function () {
	'use strict';

	var rail = document.querySelector('[data-dx-steps]');

	if (!rail) {
		return;
	}

	var stage = rail.querySelector('.p-dxsteps__stage');
	var steps = Array.prototype.slice.call(rail.querySelectorAll('[data-dx-step]'));
	var items = Array.prototype.slice.call(rail.querySelectorAll('[data-dx-flow-item]'));

	if (!stage || steps.length < 2) {
		return;
	}

	var total = steps.length;
	var current = -1;
	var ticking = false;

	function setFlow(index) {
		items.forEach(function (item, i) {
			var active = i === index;

			item.classList.toggle('is-active', active);

			if (active) {
				item.setAttribute('aria-current', 'step');
			} else {
				item.removeAttribute('aria-current');
			}
		});
	}

	/**
	 * index のステップを表示する。animate のときは入れ替わる 2 枚だけを流す。
	 */
	function show(index, animate) {
		var previous = current;

		current = index;

		steps.forEach(function (step, i) {
			var active = i === index;

			step.classList.toggle('is-moving', animate && (active || i === previous));
			step.classList.toggle('is-active', active);
			step.classList.toggle('is-before', i < index);
			step.classList.toggle('is-after', i > index);

			if (active) {
				step.removeAttribute('inert');
				step.removeAttribute('aria-hidden');
			} else {
				step.setAttribute('inert', '');
				step.setAttribute('aria-hidden', 'true');
			}
		});

		setFlow(Number(steps[index].getAttribute('data-flow-active')));
	}

	/**
	 * 固定している区間のどこまで来たかを 0〜1 で出し、ステップ数で割り当てる。
	 */
	function readScroll() {
		ticking = false;

		var runway = rail.offsetHeight - stage.offsetHeight;

		if (runway <= 0) {
			return;
		}

		// 固定の上端（PC は 0、SP はヘッダーの下）からどれだけ進んだか。
		var pinTop = parseFloat(window.getComputedStyle(stage).top) || 0;
		var progress = (pinTop - rail.getBoundingClientRect().top) / runway;

		// 1 になった瞬間に範囲外の添字が出ないよう、わずかに手前で止める。
		progress = Math.max(0, Math.min(0.999, progress));

		var index = Math.floor(progress * total);

		if (index !== current) {
			show(index, current !== -1);
		}
	}

	function request() {
		if (ticking) {
			return;
		}

		ticking = true;
		window.requestAnimationFrame(readScroll);
	}

	// 流し終わったら transition を外す（次に飛ばされたときに見えたまま横切らないため）。
	steps.forEach(function (step) {
		step.addEventListener('transitionend', function (event) {
			if (event.target === step && event.propertyName === 'transform') {
				step.classList.remove('is-moving');
			}
		});
	});

	rail.style.setProperty('--dxp-steps-count', String(total));
	rail.classList.add('is-enhanced');

	show(0, false);
	readScroll();

	window.addEventListener('scroll', request, { passive: true });
	window.addEventListener('resize', request);
})();
