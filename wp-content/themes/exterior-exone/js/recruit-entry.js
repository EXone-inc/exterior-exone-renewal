/**
 * 応募フォーム /recruit/entry/（仕様書 F-11・決定 §5 Q13）。
 *
 * ・送信は CF7 の AJAX のまま（入力チェックもサーバー側の CF7。現サイトと同じ）。
 * ・送信中はもう一度押しても送らない（ボタンを押せなくし、Enter での送信も止める）。
 * ・完了 … data-recruit-entry-thanks-url の /recruit/thanks/ へ遷移する（履歴に残る遷移）。
 *   遷移先が無いとき（サンクスページが無い・非公開）は、フォームを隠し、CF7 の送信完了メッセージだけを
 *   フォームの位置に出して画面内へ送る。
 *   「戻る」で戻ったとき（bfcache の復元）は入力前の状態（フォーム・ボタンを押せる）に戻す。
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
	var leaving = false;
	var thanks = box.getAttribute('data-recruit-entry-thanks-url') || '';

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

			if (sending || leaving) {
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

		// サンクスページへ（送信中の表示のまま。2 回目は leaving で止める）。
		if (thanks) {
			leaving = true;
			setBusy(true);
			window.location.assign(thanks);
			return;
		}

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

		if (sending && status !== 'submitting' && !leaving) {
			setBusy(false);
		}
	});

	// 遷移後に「戻る」で bfcache から復元されたら、入力前の状態に戻す（再送信はしない）。
	window.addEventListener('pageshow', function (event) {
		if (!event.persisted || !leaving) {
			return;
		}

		leaving = false;
		setBusy(false);
		form.reset();
	});
})();
