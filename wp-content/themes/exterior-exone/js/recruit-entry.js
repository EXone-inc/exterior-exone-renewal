/**
 * 応募フォーム /recruit/entry/（仕様書 F-11・決定 §5 Q13）。
 *
 * ・送信は CF7 の AJAX のまま（入力チェックもサーバー側の CF7。現サイトと同じ）。
 * ・送信中はもう一度押しても送らない（ボタンを押せなくし、Enter での送信も止める）。
 * ・完了 … フォームを隠し、CF7 の送信完了メッセージだけをフォームの位置に出して画面内へ送る。
 * ・入力エラー … CF7 が欄の下に出したエラーのうち最初の項目へ送る（帯は CSS で出さない）。
 * ・送信の失敗（mail_failed など）… CF7 の帯（.wpcf7-response-output。CSS で表示）へ送る。
 */
(function () {
	'use strict';

	var box = document.querySelector('[data-recruit-entry]');
	var form = box ? box.querySelector('form.wpcf7-form') : null;
	var done = box ? box.querySelector('[data-recruit-entry-done]') : null;

	if (!form || !done) {
		return;
	}

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	var submit = form.querySelector('.wpcf7-submit');
	var sending = false;

	// 画面上端に重なっている追従ヘッダーの高さ（js/contact-form.js と同じ）。
	function headerOffset() {
		var header = document.querySelector('.p-header');

		if (!header || !/^(fixed|sticky)$/.test(getComputedStyle(header).position)) {
			return 0;
		}

		return Math.max(0, header.getBoundingClientRect().bottom);
	}

	// el の上端が画面内（ヘッダーの下）に無ければ、上端が見える位置まで送る。
	function scrollToTop(el) {
		var rect = el.getBoundingClientRect();
		var top = headerOffset();
		var viewport = window.innerHeight || document.documentElement.clientHeight;

		if (rect.top >= top && rect.bottom <= viewport) {
			return;
		}

		var margin = parseFloat(getComputedStyle(el).scrollMarginTop) || 0;

		window.scrollBy({ top: rect.top - top - margin, left: 0, behavior: reduced.matches ? 'instant' : 'smooth' });
	}

	function setBusy(busy) {
		sending = busy;

		if (submit) {
			submit.disabled = busy;
		}

		box.toggleAttribute('aria-busy', busy);
	}

	// CF7 の submit より先に受け、送信中・完了後の 2 回目を止める。
	document.addEventListener(
		'submit',
		function (event) {
			if (event.target !== form) {
				return;
			}

			if (sending) {
				event.preventDefault();
				event.stopImmediatePropagation();
				return;
			}

			setBusy(true);
		},
		true
	);

	form.addEventListener('wpcf7mailsent', function (event) {
		var response = event.detail && event.detail.apiResponse ? event.detail.apiResponse : {};

		form.hidden = true;
		done.textContent = response.message || '';
		done.hidden = false;
		scrollToTop(done);
		done.focus({ preventScroll: true });
	});

	// サーバー側の入力エラー: CF7 が欄の下にエラーを出し終えてから最初の項目へ送る。
	['wpcf7invalid', 'wpcf7unaccepted'].forEach(function (type) {
		form.addEventListener(type, function () {
			window.setTimeout(function () {
				var invalid = form.querySelector('.wpcf7-not-valid, .wpcf7-form-control-wrap .wpcf7-not-valid-tip');

				if (!invalid) {
					return;
				}

				scrollToTop(invalid.closest('.page_form-item01') || invalid);

				var control = invalid.closest('.wpcf7-form-control-wrap');
				var field = control ? control.querySelector('input, textarea') : null;

				if (field) {
					field.focus({ preventScroll: true });
				}
			}, 0);
		});
	});

	// 送信の失敗: CF7 の文言（帯）を見せる。
	['wpcf7mailfailed', 'wpcf7spam', 'wpcf7aborted'].forEach(function (type) {
		form.addEventListener(type, function () {
			window.setTimeout(function () {
				var output = form.querySelector('.wpcf7-response-output');

				if (output) {
					scrollToTop(output);
				}
			}, 0);
		});
	});

	// 送信が終わったら（結果を問わず）ボタンを戻す。通信エラーで結果のイベントが来ない場合も含む。
	form.addEventListener('wpcf7statuschanged', function (event) {
		var status = event.detail ? event.detail.status : '';

		if (sending && status !== 'submitting') {
			setBusy(false);
		}
	});
})();
