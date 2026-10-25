/** お知らせ本文を200文字で折りたたむ。JSが動かない場合も全文は読める。 */
(function () {
	'use strict';

	document.querySelectorAll('[data-news-disclosure]').forEach(function (section) {
		var preview = section.querySelector('[data-news-preview]');
		var full = section.querySelector('[data-news-full]');
		var button = section.querySelector('[data-news-toggle]');
		if (!preview || !full || !button) {
			return;
		}

		function setExpanded(expanded) {
			preview.hidden = expanded;
			full.hidden = !expanded;
			button.setAttribute('aria-expanded', String(expanded));
			button.textContent = expanded ? '閉じる' : 'もっと見る';
		}

		setExpanded(false);
		button.hidden = false;
		button.addEventListener('click', function () {
			setExpanded(button.getAttribute('aria-expanded') !== 'true');
		});
	});
})();
