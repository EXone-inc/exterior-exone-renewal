/**
 * CONTACT のフォームの郵便番号 → 住所の自動入力（個人・法人とも。ユーザー指示 2026-10-14）。
 *
 * ・郵便番号欄（name="your-zip"）に 7 桁入ったら zipcloud API（郵便番号データ配信サービス）に問い合わせ、
 *   都道府県 + 市区町村 + 町域 を住所欄（name="your-address"）に入れる。番地以降は利用者が続けて書く。
 * ・住所欄に利用者が自分で書いた内容があるときは上書きしない（空か、前回の自動入力のままのときだけ入れる）。
 * ・全角数字・ハイフン・空白は数字だけに直してから問い合わせる。欄を離れたときは欄の値も数字だけにする
 *   （フォームは「ハイフンなし 7 桁」が必須のため）。
 * ・見つからない・通信に失敗したときは何もしない（住所は手入力できる）。入力中に番号が変わったら前の問い合わせは捨てる。
 */
(function () {
	'use strict';

	var API = 'https://zipcloud.ibsnet.co.jp/api/search?zipcode=';
	var cache = {};

	/**
	 * 全角数字を半角にし、数字以外を取り除く。
	 */
	function digitsOnly(value) {
		return String(value || '')
			.replace(/[０-９]/g, function (ch) {
				return String.fromCharCode(ch.charCodeAt(0) - 0xfee0);
			})
			.replace(/\D/g, '');
	}

	function lookup(zip) {
		if (cache[zip]) {
			return cache[zip];
		}

		cache[zip] = window.fetch(API + zip, { mode: 'cors' })
			.then(function (res) {
				return res.ok ? res.json() : null;
			})
			.then(function (data) {
				var hit = data && data.status === 200 && data.results && data.results[0];

				return hit ? (hit.address1 || '') + (hit.address2 || '') + (hit.address3 || '') : '';
			})
			.catch(function () {
				delete cache[zip];
				return '';
			});

		return cache[zip];
	}

	function setup(form) {
		var zipInput = form.querySelector('input[name="your-zip"]');
		var addressInput = form.querySelector('input[name="your-address"]');

		if (!zipInput || !addressInput) {
			return;
		}

		var lastAuto = '';
		var lastZip = '';

		function fill(address) {
			var current = addressInput.value.trim();

			// 利用者が自分で書いた住所は消さない。
			if (current !== '' && current !== lastAuto) {
				return;
			}

			addressInput.value = address;
			lastAuto = address;

			// 入力チェック（contact-form.js）に値が変わったことを伝える。
			addressInput.dispatchEvent(new Event('input', { bubbles: true }));
		}

		zipInput.addEventListener('input', function () {
			var zip = digitsOnly(zipInput.value);

			if (zip.length !== 7 || zip === lastZip) {
				return;
			}

			lastZip = zip;

			lookup(zip).then(function (address) {
				// 問い合わせ中に番号が変わっていたら、古い結果は使わない。
				if (address && digitsOnly(zipInput.value) === zip) {
					fill(address);
				}
			});
		});

		zipInput.addEventListener('blur', function () {
			var zip = digitsOnly(zipInput.value);

			if (zip !== zipInput.value && zip !== '') {
				zipInput.value = zip;
				zipInput.dispatchEvent(new Event('input', { bubbles: true }));
			}
		});
	}

	if (!window.fetch) {
		return;
	}

	Array.prototype.forEach.call(document.querySelectorAll('form.wpcf7-form'), setup);
})();
