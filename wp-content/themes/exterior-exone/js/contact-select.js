/**
 * CONTACT の選択フロー（guide 853:568〜853:578・869:614 / 決定 §3・§5-11）。
 *
 * ・[data-section="contact-select"] … 選択フロー全体。
 * ・button[data-contact-type="personal|corporate"] / button[data-contact-store="<slug>"] /
 *   button[data-contact-method="mail|tel"] … 単一選択（aria-pressed）。選択中をもう一度押しても解除しない。
 *   a[data-contact-method="line"] … 選択中の支店の LINE への直リンク（状態を持たない）。
 *   SP の組み（1024px 以下）では電話も a[data-contact-method="tel"]（選択中の支店の tel:。仕様書 Q14）。
 *   PC の button と同じ中身の a を作って差し替える。押しても電話ブロックは出さない。
 * ・[data-contact-step="store|method|tel|form-personal|form-corporate"] … 段。出さない段は hidden。
 *   個人 → 店舗 → 方法 → 電話 / 個人フォーム、法人 → 法人フォーム。上の段は動かず、下に足されていく。
 *   切り替えても選択（支店・方法）とフォームの入力は捨てない。
 * ・店舗ボタンの data-contact-tel-title / -tel / -tel-url / -tel-hours / -line-url …
 *   電話ブロック（[data-contact-tel-slot="title|number|hours"]）と LINE の href の差し替え元。
 * ・[data-contact-store-input]、個人フォームの段の input[name="store-slug"] … 送り先支店の隠しフィールド。
 *   支店が替わるたびに value をスラッグにし、[data-section] に contact:store イベントを出す（S3 の CF7 用）。
 *
 * 新しく現れた段は短くフェードイン（--contact-reveal）し、下端が画面外なら見える位置まで
 * 滑らかにスクロールする（scroll-margin-bottom ぶんの余白を残す。上端はヘッダーの下より上に出さない）。
 * 動きを減らす設定ではどちらも即時。初期状態（?store= の選択済み）は HTML のとおりで、読み込み時はスクロールしない。
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-section="contact-select"]');

	if (!root) {
		return;
	}

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	var spLayout = window.matchMedia('(max-width: 1024px)');

	function all(selector) {
		return Array.prototype.slice.call(root.querySelectorAll(selector));
	}

	var typeButtons = all('button[data-contact-type]');
	var storeButtons = all('button[data-contact-store]');
	var methodButtons = all('button[data-contact-method]');
	var line = root.querySelector('a[data-contact-method="line"]');
	var telTitle = root.querySelector('[data-contact-tel-slot="title"]');
	var telNumber = root.querySelector('[data-contact-tel-slot="number"]');
	var telHours = root.querySelector('[data-contact-tel-slot="hours"]');
	var telButton = root.querySelector('button[data-contact-method="tel"]');
	var telLink = null;
	var steps = {};

	// SP の電話ボタン（PC の button と同じクラス・中身の a）。
	if (telButton) {
		telLink = document.createElement('a');
		telLink.className = telButton.className;
		telLink.setAttribute('data-contact-method', 'tel');
		Array.prototype.forEach.call(telButton.childNodes, function (node) {
			telLink.appendChild(node.cloneNode(true));
		});
	}

	all('[data-contact-step]').forEach(function (step) {
		steps[step.getAttribute('data-contact-step')] = step;
	});

	// 押されているボタンの値（HTML の初期状態。?store= ではサーバーが 個人 + 支店 を選択済みにしている）。
	function pressedValue(buttons, attr) {
		for (var i = 0; i < buttons.length; i += 1) {
			if (buttons[i].getAttribute('aria-pressed') === 'true') {
				return buttons[i].getAttribute(attr) || '';
			}
		}

		return '';
	}

	var state = {
		type: pressedValue(typeButtons, 'data-contact-type'),
		store: pressedValue(storeButtons, 'data-contact-store'),
		method: pressedValue(methodButtons, 'data-contact-method')
	};

	function press(buttons, attr, value) {
		buttons.forEach(function (button) {
			button.setAttribute('aria-pressed', String(button.getAttribute(attr) === value));
		});
	}

	// いまの選択で見せる段。
	function visibleSteps() {
		var personal = state.type === 'personal';
		var store = personal && state.store !== '';

		return {
			type: true,
			store: personal,
			method: store,
			tel: store && state.method === 'tel',
			'form-personal': store && state.method === 'mail',
			'form-corporate': state.type === 'corporate'
		};
	}

	function findStoreButton(slug) {
		for (var i = 0; i < storeButtons.length; i += 1) {
			if (storeButtons[i].getAttribute('data-contact-store') === slug) {
				return storeButtons[i];
			}
		}

		return null;
	}

	// 支店に合わせて 電話ブロック・LINE・送り先支店の隠しフィールドを差し替える。
	function applyStore() {
		var button = findStoreButton(state.store);

		if (!button) {
			return;
		}

		var lineUrl = button.getAttribute('data-contact-line-url') || '';
		var telUrl = button.getAttribute('data-contact-tel-url') || '';

		if (line) {
			if (lineUrl) {
				line.setAttribute('href', lineUrl);
			} else {
				line.removeAttribute('href');
			}
		}

		if (telTitle) {
			telTitle.textContent = button.getAttribute('data-contact-tel-title') || '';
		}

		if (telNumber) {
			telNumber.textContent = button.getAttribute('data-contact-tel') || '';

			if (telUrl) {
				telNumber.setAttribute('href', telUrl);
			} else {
				telNumber.removeAttribute('href');
			}
		}

		if (telHours) {
			telHours.textContent = button.getAttribute('data-contact-tel-hours') || '';
		}

		if (telLink) {
			if (telUrl) {
				telLink.setAttribute('href', telUrl);
			} else {
				telLink.removeAttribute('href');
			}
		}

		all('[data-contact-store-input], [data-contact-step="form-personal"] input[name="store-slug"]').forEach(function (input) {
			input.value = state.store;
		});

		root.dispatchEvent(new CustomEvent('contact:store', { detail: { store: state.store } }));
	}

	// 1 フレームだけ透明にしてから戻す（CSS の transition で --contact-reveal かけて現れる）。
	function fadeIn(step) {
		if (reduced.matches) {
			return;
		}

		step.classList.add('is-entering');
		void step.offsetWidth; // 透明の状態を確定させる
		step.classList.remove('is-entering');
	}

	// 画面上端に重なっている追従ヘッダーの高さ。
	function headerOffset() {
		var header = document.querySelector('.p-header');

		if (!header || !/^(fixed|sticky)$/.test(getComputedStyle(header).position)) {
			return 0;
		}

		return Math.max(0, header.getBoundingClientRect().bottom);
	}

	// 現れた段（first〜last）の下端が画面外なら、見える位置まで送る。
	function reveal(shown) {
		if (!shown.length) {
			return;
		}

		var first = shown[0];
		var last = shown[shown.length - 1];
		var lastRect = last.getBoundingClientRect();
		var viewport = window.innerHeight || document.documentElement.clientHeight;

		if (lastRect.bottom <= viewport) {
			return;
		}

		var below = parseFloat(getComputedStyle(last).scrollMarginBottom) || 0;
		var above = parseFloat(getComputedStyle(first).scrollMarginTop) || 0;
		var amount = Math.min(
			lastRect.bottom - viewport + below,
			first.getBoundingClientRect().top - headerOffset() - above
		);

		if (amount <= 0) {
			return;
		}

		window.scrollBy({ top: amount, left: 0, behavior: reduced.matches ? 'instant' : 'smooth' });
	}

	function render(animate) {
		var visible = visibleSteps();
		var shown = [];

		press(typeButtons, 'data-contact-type', state.type);
		press(storeButtons, 'data-contact-store', state.store);
		press(methodButtons, 'data-contact-method', state.method);

		Object.keys(steps).forEach(function (key) {
			var step = steps[key];
			var on = !!visible[key];

			if (on && step.hidden) {
				shown.push(step);
			}

			step.hidden = !on;
		});

		if (!animate) {
			return;
		}

		shown.forEach(fadeIn);
		reveal(shown);
	}

	typeButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			var value = button.getAttribute('data-contact-type') || '';

			if (value === state.type) {
				return;
			}

			state.type = value;
			render(true);
		});
	});

	storeButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			var value = button.getAttribute('data-contact-store') || '';

			if (value === state.store) {
				return;
			}

			state.store = value;
			applyStore();
			render(true);
		});
	});

	methodButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			var value = button.getAttribute('data-contact-method') || '';

			if (value === state.method) {
				return;
			}

			state.method = value;
			render(true);
		});
	});

	// 幅に合わせて電話ボタンを button（PC）/ a（SP）に入れ替える。
	// SP に切り替わったときに電話が選ばれていたら選択を外す（SP は電話ブロックを出さない）。
	function syncTel() {
		if (!telButton || !telLink) {
			return;
		}

		var from = spLayout.matches ? telButton : telLink;
		var to = spLayout.matches ? telLink : telButton;

		if (from.parentNode) {
			from.parentNode.replaceChild(to, from);
		}

		if (spLayout.matches && state.method === 'tel') {
			state.method = '';
			render(false);
		}
	}

	// 初期状態（?store= の選択済み）を揃える。読み込み時はフェード・スクロールしない。
	if (state.store) {
		applyStore();
	}

	syncTel();
	render(false);

	if (spLayout.addEventListener) {
		spLayout.addEventListener('change', syncTel);
	} else if (spLayout.addListener) {
		spLayout.addListener(syncTel);
	}
})();
