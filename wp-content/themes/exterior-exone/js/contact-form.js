/**
 * CONTACT のフォーム（個人 864:294 / 法人 860:166。決定 §2・§4、仕様書 F-06・Q11・Q13）。
 *
 * ・[data-contact-form] … フォームの段の中身（template-parts/contact/form.php）。
 *   [data-contact-form-view="input|confirm|done"] を 1 つだけ見せる。
 * ・「送信内容を確認する」（CF7 の submit）… 送信せずに入力チェック → 問題が無ければ確認表示。
 *   エラーは欄のすぐ下に CF7 と同じ .wpcf7-not-valid-tip で出し、最初のエラーの行まで送る。
 *   文言は data-contact-messages（CF7 のフォームのメッセージ）。直した欄のエラーは入力した時点で消す。
 * ・確認表示 … ラベルと入力値の一覧（値は文字列のまま。改行は CSS で保つ）。個人は先頭に「お問い合わせ先」。
 *   「修正する」で入力に戻り、「送信する」で CF7 の送信（AJAX）を呼ぶ。送信中は 2 つのボタンを押せない。
 * ・送信の結果 … 完了はフォームの位置に完了文面（CF7 の送信完了メッセージ）。
 *   サーバー側の入力エラーは入力表示に戻して CF7 が欄の下に出したエラーへ送る。
 *   送信の失敗（mail_failed など）は入力表示に戻し、CF7 の文言をフォームの帯に出す（CSS）。
 * CF7 のクライアント側の入力チェックは使わない（フォームの novalidate クラス）。
 */
(function () {
	'use strict';

	var section = document.querySelector('[data-section="contact-select"]');

	if (!section) {
		return;
	}

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

	// 画面上端に重なっている追従ヘッダーの高さ（js/contact-select.js と同じ）。
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

	// CF7 の wpcf7_is_email（is_email）の近似。最終的な判定はサーバー側の CF7。
	function isEmail(value) {
		return /^[^\s@]+@[^\s@.]+(\.[^\s@.]+)+$/.test(value);
	}

	// CF7 の wpcf7_is_tel と同じ判定。
	function isTel(value) {
		var text = value.replace(/[#*].*$/, '').replace(/[()/.*#\s-]+/g, '');

		if (/^(\+|00)/.test(text)) {
			text = '+' + text.replace(/^[+0]+/, '');
		}

		return /^[+]?[0-9]+$/.test(text) && text.length > 5 && text.length < 16;
	}

	function setup(box) {
		var form = box.querySelector('form.wpcf7-form');

		if (!form) {
			return;
		}

		var views = {};
		var list = box.querySelector('[data-contact-confirm-list]');
		var back = box.querySelector('[data-contact-confirm-back]');
		var send = box.querySelector('[data-contact-confirm-send]');
		var messages = {};
		var sending = false;
		var done = false;

		Array.prototype.forEach.call(box.querySelectorAll('[data-contact-form-view]'), function (view) {
			views[view.getAttribute('data-contact-form-view')] = view;
		});

		try {
			messages = JSON.parse(box.getAttribute('data-contact-messages') || '{}');
		} catch (error) {
			messages = {};
		}

		function message(key) {
			return messages[key] || messages.invalid_required || '';
		}

		function show(name) {
			Object.keys(views).forEach(function (key) {
				views[key].hidden = key !== name;
			});
		}

		// 入力欄（隠しフィールドを除く）。
		function controls() {
			return Array.prototype.filter.call(
				form.querySelectorAll('.wpcf7-form-control-wrap[data-name] .wpcf7-form-control'),
				function (control) {
					return /^(input|textarea|select)$/i.test(control.tagName) && control.type !== 'hidden';
				}
			);
		}

		// エラーの文言（問題が無ければ ''）。順番は CF7 と同じ 必須 → 形式 → 文字数。
		function check(control) {
			var value = control.value;
			var trimmed = value.trim();
			var length = Array.from(value).length;
			var min = parseInt(control.getAttribute('minlength'), 10);
			var max = parseInt(control.getAttribute('maxlength'), 10);

			if (trimmed === '') {
				return control.getAttribute('aria-required') === 'true' ? message('invalid_required') : '';
			}

			if (control.type === 'email' && !isEmail(trimmed)) {
				return message('invalid_email');
			}

			if (control.type === 'tel' && !isTel(trimmed)) {
				return message('invalid_tel');
			}

			if (max > 0 && length > max) {
				return message('invalid_too_long');
			}

			if (min > 0 && length < min) {
				return message('invalid_too_short');
			}

			if (control.name === 'your-zip' && !/^[0-9]{7}$/.test(trimmed)) {
				return message('invalid_zip');
			}

			return '';
		}

		function clearError(control) {
			var wrap = control.closest('.wpcf7-form-control-wrap');

			if (wrap) {
				Array.prototype.forEach.call(wrap.querySelectorAll('.wpcf7-not-valid-tip'), function (tip) {
					tip.remove();
				});
			}

			control.classList.remove('wpcf7-not-valid');
			control.setAttribute('aria-invalid', 'false');
			control.removeAttribute('aria-describedby');
		}

		function showError(control, text) {
			var wrap = control.closest('.wpcf7-form-control-wrap');

			clearError(control);

			if (!wrap) {
				return;
			}

			var tip = document.createElement('span');

			tip.className = 'wpcf7-not-valid-tip';
			tip.textContent = text;

			if (control.id) {
				tip.id = control.id + '-error';
				control.setAttribute('aria-describedby', tip.id);
			}

			wrap.appendChild(tip);
			control.classList.add('wpcf7-not-valid');
			control.setAttribute('aria-invalid', 'true');
		}

		// 最初のエラーの行まで送り、その欄にフォーカスする。
		function focusFirstError() {
			var invalid = form.querySelector('.wpcf7-not-valid');

			if (!invalid) {
				return false;
			}

			scrollToTop(invalid.closest('.p-contactform__row') || invalid);
			invalid.focus({ preventScroll: true });

			return true;
		}

		function storeName() {
			var input = form.querySelector('input[name="store-slug"]');
			var slug = input ? input.value : '';
			var button = slug ? section.querySelector('button[data-contact-store="' + CSS.escape(slug) + '"]') : null;

			return button ? button.textContent.trim() : box.getAttribute('data-contact-store-unselected') || '';
		}

		function addItem(label, value) {
			var item = document.createElement('div');
			var term = document.createElement('dt');
			var desc = document.createElement('dd');

			item.className = 'p-contactform__item';
			term.className = 'p-contactform__term';
			desc.className = 'p-contactform__value';
			term.textContent = label;
			desc.textContent = value;
			item.appendChild(term);
			item.appendChild(desc);
			list.appendChild(item);
		}

		function buildList() {
			var storeLabel = box.getAttribute('data-contact-store-label');

			list.textContent = '';

			if (storeLabel) {
				addItem(storeLabel, storeName());
			}

			Array.prototype.forEach.call(form.querySelectorAll('.p-contactform__row'), function (row) {
				var label = row.querySelector('.p-contactform__label-text');
				var control = row.querySelector('.wpcf7-form-control');

				if (label && control) {
					addItem(label.textContent.trim(), control.value);
				}
			});
		}

		function confirm() {
			var first = null;

			controls().forEach(function (control) {
				var error = check(control);

				if (error) {
					showError(control, error);
					first = first || control;
				} else {
					clearError(control);
				}
			});

			if (first) {
				focusFirstError();
				return;
			}

			buildList();
			show('confirm');
			scrollToTop(views.confirm);
			views.confirm.focus({ preventScroll: true });
		}

		function setBusy(busy) {
			sending = busy;
			send.disabled = busy;
			back.disabled = busy;
			box.toggleAttribute('aria-busy', busy);
		}

		// 「送信内容を確認する」・Enter での送信は CF7 に渡さず確認表示へ（CF7 の submit より先に受ける）。
		document.addEventListener(
			'submit',
			function (event) {
				if (event.target !== form) {
					return;
				}

				event.preventDefault();
				event.stopImmediatePropagation();

				if (!sending && !done) {
					confirm();
				}
			},
			true
		);

		// 直した欄のエラーは入力した時点で消す（他の欄は残す）。
		form.addEventListener('input', function (event) {
			var control = event.target;

			if (control.classList && control.classList.contains('wpcf7-not-valid') && !check(control)) {
				clearError(control);
			}
		});

		back.addEventListener('click', function () {
			if (sending) {
				return;
			}

			show('input');
			scrollToTop(box);

			// フォーカスは最初の入力欄へ（スクロールは scrollToTop のまま）。
			var first = form.querySelector('input:not([type="hidden"]):not([type="submit"]), textarea');

			if (first) {
				first.focus({ preventScroll: true });
			}
		});

		send.addEventListener('click', function () {
			if (sending || done) {
				return;
			}

			setBusy(true);

			if (window.wpcf7 && typeof window.wpcf7.submit === 'function') {
				window.wpcf7.submit(form);
			} else {
				// CF7 の JS が動いていないときは通常の送信（ページ遷移あり）。
				HTMLFormElement.prototype.submit.call(form);
			}
		});

		form.addEventListener('wpcf7mailsent', function (event) {
			var response = event.detail && event.detail.apiResponse ? event.detail.apiResponse : {};

			done = true;
			views.done.textContent = response.message || '';
			show('done');
			scrollToTop(views.done);
			views.done.focus({ preventScroll: true });
		});

		// サーバー側の入力エラー: CF7 が欄の下にエラーを出し終えてから入力表示に戻して送る。
		['wpcf7invalid', 'wpcf7unaccepted'].forEach(function (type) {
			form.addEventListener(type, function () {
				window.setTimeout(function () {
					show('input');

					if (!focusFirstError()) {
						scrollToTop(box);
					}
				}, 0);
			});
		});

		// 送信の失敗: 入力表示に戻し、CF7 の文言（.wpcf7-response-output）を見せる。
		['wpcf7mailfailed', 'wpcf7spam', 'wpcf7aborted'].forEach(function (type) {
			form.addEventListener(type, function () {
				window.setTimeout(function () {
					var output = form.querySelector('.wpcf7-response-output');

					show('input');
					scrollToTop(output || box);
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
	}

	Array.prototype.forEach.call(section.querySelectorAll('[data-contact-form]'), setup);
})();
