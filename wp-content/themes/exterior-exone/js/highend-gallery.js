/**
 * ハイエンドページ Gallery: タブ・番号 / 英名 / コピーの差し替えとフェード・タブの押下状態。
 *
 * めくり・自動送り・当たり判定・進捗下線は共通部品 js/flipbook.js（[data-flipbook]）。
 * ここはその本と .p-hepgal のタブ・文字をつなぐだけ。
 * カンプ: PC 713:164 / SP 733:380〜404 / guide 718:245
 * 仕様書: docs/spec-20261005-highend-page.md F-07 / docs/spec-20261008-works-page.md F-03
 *
 * - タブを押す → flipbook:go（番号が大きければ next、小さければ prev）
 * - flipbook:start → タブの押下を移し、文字を消す / flipbook:end → 文字を差し替えて出す
 * - 写真の枚数とタブの数が合わないときはつながない（本は単独で動く）
 */
(function () {
	'use strict';

	function setup(section) {
		var book = section.querySelector('[data-flipbook]');
		var tabs = Array.prototype.slice.call(section.querySelectorAll('.p-hepgal__tab'));

		if (!book || tabs.length !== book.querySelectorAll('.c-flipbook__page--left .c-flipbook__photo').length) {
			return;
		}

		var texts = {
			num: book.querySelector('[data-flipbook-num]'),
			eng: section.querySelector('[data-hep-text="eng"]'),
			copy: section.querySelector('[data-hep-text="copy"]')
		};

		function fadeText(out) {
			Object.keys(texts).forEach(function (key) {
				if (texts[key]) {
					texts[key].classList.toggle('is-fading', out);
				}
			});
		}

		function writeText(index) {
			var tab = tabs[index];

			if (!tab) {
				return;
			}

			if (texts.num) {
				texts.num.textContent = tab.getAttribute('data-num');
			}
			if (texts.eng) {
				texts.eng.textContent = tab.getAttribute('data-eng');
			}
			if (texts.copy) {
				texts.copy.textContent = tab.getAttribute('data-copy');
			}
		}

		function pressTab(index) {
			tabs.forEach(function (tab, i) {
				tab.setAttribute('aria-pressed', i === index ? 'true' : 'false');
			});
		}

		book.addEventListener('flipbook:start', function (event) {
			pressTab(event.detail.to);
			fadeText(true);
		});

		book.addEventListener('flipbook:end', function (event) {
			writeText(event.detail.current);
			fadeText(false);
		});

		tabs.forEach(function (tab, index) {
			tab.addEventListener('click', function () {
				book.dispatchEvent(new CustomEvent('flipbook:go', { detail: { index: index } }));
			});
		});
	}

	Array.prototype.forEach.call(document.querySelectorAll('.p-hepgal'), setup);
})();
