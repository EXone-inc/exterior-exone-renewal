/**
 * 横スクロール列（[data-scroll-row]）をドラッグでスライドできるようにする。
 *
 * PLANS のカード列で使う。ホイール／トラックパッド／タッチの既定挙動はそのまま残し、
 * マウスでも掴んで動かせるようにするだけ。ドラッグ直後のクリックは打ち消して、
 * 掴んで動かしただけでカードが選択されないようにする。
 *
 * ポインタ捕捉（setPointerCapture）は「実際に動かし始めてから」掛ける。
 * pointerdown の時点で掛けると click の発火先がこの列自体に差し替わり、
 * 中のカードボタンの click が一切呼ばれなくなる（＝カードを押しても選択できない）。
 */
(function () {
	'use strict';

	var DRAG_THRESHOLD = 4; // これ以上動いたらドラッグとみなす（px）

	Array.prototype.forEach.call(document.querySelectorAll('[data-scroll-row]'), function (row) {
		var pointerId = null;
		var startX = 0;
		var startScroll = 0;
		var dragged = false;

		function stop() {
			if (pointerId === null) {
				return;
			}

			try {
				if (row.hasPointerCapture && row.hasPointerCapture(pointerId)) {
					row.releasePointerCapture(pointerId);
				}
			} catch (e) {
				// 同上。
			}

			pointerId = null;
			row.classList.remove('is-dragging');
		}

		row.addEventListener('pointerdown', function (event) {
			// タッチとペンはブラウザの慣性スクロールに任せる。
			if (event.pointerType !== 'mouse' || event.button !== 0) {
				return;
			}

			pointerId = event.pointerId;
			startX = event.clientX;
			startScroll = row.scrollLeft;
			dragged = false;
		});

		row.addEventListener('pointermove', function (event) {
			if (pointerId === null || event.pointerId !== pointerId) {
				return;
			}

			var delta = event.clientX - startX;

			if (!dragged && Math.abs(delta) < DRAG_THRESHOLD) {
				return;
			}

			if (!dragged) {
				dragged = true;
				row.classList.add('is-dragging');

				// ここで初めて捕捉する。以降は列の外へ出しても追従できる。
				try {
					row.setPointerCapture(pointerId);
				} catch (e) {
					// 捕捉できない環境でもドラッグ自体は動くので無視する。
				}
			}

			row.scrollLeft = startScroll - delta;
			event.preventDefault();
		});

		row.addEventListener('pointerup', stop);
		row.addEventListener('pointercancel', stop);

		// ドラッグで終わったときは、離した先のカードを押した扱いにしない。
		row.addEventListener(
			'click',
			function (event) {
				if (!dragged) {
					return;
				}

				event.preventDefault();
				event.stopPropagation();
				dragged = false;
			},
			true
		);

		// 画像のドラッグ&ドロップが始まると掴めなくなるため止める。
		row.addEventListener('dragstart', function (event) {
			event.preventDefault();
		});
	});
})();
