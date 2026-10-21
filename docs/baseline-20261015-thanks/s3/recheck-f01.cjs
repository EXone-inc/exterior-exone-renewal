// S3 AC-03-11: F-01 の配置・書式（AC-01-3・5〜8・10〜12・17）を両サンクスページの 1920 / 375 で再確認する。
// 使い方: node recheck-f01.cjs > recheck-f01.json （NG があれば ng 配列に出る）
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');
const near = (a, b, t) => Math.abs(a - b) <= t;
(async () => {
	const browser = await chromium.launch({ channel: 'chrome', headless: true });
	const out = { ng: [], data: {} };
	const ng = (k, msg) => out.ng.push(k + ': ' + msg);
	for (const url of ['/contact/thanks/', '/recruit/thanks/']) {
		for (const [w, vp] of [['1920', { width: 1920, height: 1080 }], ['375', { width: 375, height: 812 }]]) {
			const k = w + ' ' + url;
			const ctx = await browser.newContext({ viewport: vp, reducedMotion: 'reduce' });
			const page = await ctx.newPage();
			const errors = []; const reqs = [];
			page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
			page.on('pageerror', (e) => errors.push(String(e)));
			page.on('request', (r) => reqs.push(r.url()));
			await page.goto('http://exterior-exone-renewal.local' + url, { waitUntil: 'load' });
			await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 300) { scrollTo(0, y); await new Promise((r) => setTimeout(r, 80)); } scrollTo(0, 0); });
			await page.waitForTimeout(1500);
			const d = await page.evaluate(() => {
				const all = (s) => [...document.querySelectorAll(s)];
				const q = (s, i = 0) => all(s)[i];
				const box = (el) => { const r = el.getBoundingClientRect(); return { x: r.left, y: r.top + scrollY, w: r.width, h: r.height, b: r.bottom + scrollY, cx: (r.left + r.right) / 2, cy: (r.top + r.bottom) / 2 + scrollY }; };
				const cs = (el) => getComputedStyle(el);
				const lines = (el) => Math.round(el.getBoundingClientRect().height / parseFloat(cs(el).lineHeight));
				const f = (el) => { const c = cs(el); return { ff: c.fontFamily, fw: c.fontWeight, fs: c.fontSize, ls: c.letterSpacing, lh: c.lineHeight, color: c.color, ta: c.textAlign }; };
				q('.p-thankscard__num', 1).scrollIntoView({ block: 'center', behavior: 'instant' }); const n = q('.p-thankscard__num', 1).getBoundingClientRect(); const nf = document.elementFromPoint((n.left + n.right) / 2, (n.top + n.bottom) / 2); window.scrollTo({ top: 0, behavior: 'instant' });
				return {
					header: box(q('.p-header')), title: box(q('.p-contactfv__title')), check: box(q('.p-thanksmsg__check')), lead: box(q('.p-thanksmsg__lead')),
					body: all('.p-thanksmsg__body').map(box), bodyLines: all('.p-thanksmsg__body').map(lines),
					en: all('.p-thankshead__en').map(box), ja: all('.p-thankshead__ja').map(box), jaLines: all('.p-thankshead__ja').map(lines),
					cards: all('.p-thankscard').map(box), nums: all('.p-thankscard__num').map(box), icons: all('.p-thankscard__img').map(box),
					ctitles: all('.p-thankscard__title').map(box), ctexts: all('.p-thankscard__text').map(box), ctextLines: all('.p-thankscard__text').map(lines),
					explore: box(q('.p-thanksexplore')), links: all('.p-thankslink').map(box), linkEn: all('.p-thankslink__en').map((e) => { const b = box(e); b.y += parseFloat(cs(e).paddingTop); return b; }), linkJa: all('.p-thankslink__ja').map(box),
					footer: box(q('footer')),
					font: { title: f(q('.p-contactfv__title')), lead: f(q('.p-thanksmsg__lead')), body: f(q('.p-thanksmsg__body')), strong: f(q('.p-thanksmsg__body strong, .p-thanksmsg__body b') || q('.p-thanksmsg__body')), en: f(q('.p-thankshead__en')), ja: f(q('.p-thankshead__ja')), num: f(q('.p-thankscard__num')), ctitle: f(q('.p-thankscard__title')), ctext: f(q('.p-thankscard__text')), linkEn: f(q('.p-thankslink__en')), linkJa: f(q('.p-thankslink__ja')) },
					card: { bg: cs(q('.p-thankscard')).backgroundColor, br: cs(q('.p-thankscard')).borderRadius, sh: cs(q('.p-thankscard')).boxShadow },
					linkDeco: all('.p-thankslink').map((a) => ({ br: cs(a).borderRadius, sh: cs(a).boxShadow })),
					numFront: nf ? (nf.closest('.p-thankscard__num') ? 'p-thankscard__num' : String(nf.className && nf.className.baseVal !== undefined ? nf.className.baseVal : nf.className)) : null,
					h1: all('h1').map((e) => e.textContent.trim()), zw: document.body.innerText.includes('​'), video: all('video').length,
					hrefs: all('main a[href]').map((a) => a.getAttribute('href')), aInCards: all('.p-thankscard a').length,
					text: (document.querySelector('main') || document.body).innerText.replace(/\s+/g, ''),
					bodyClass: document.body.className, robots: (document.querySelector('meta[name="robots"]') || {}).content,
					scroll: [document.documentElement.scrollWidth, innerWidth],
				};
			});
			d.errors = errors; d.dxVideoReq = reqs.filter((u) => u.includes('dx_video.mp4')).length;
			out.data[k] = d;
			const chk = (name, ok, val) => { if (!ok) ng(k, name + ' = ' + JSON.stringify(val)); };
			const T = 4;
			if (w === '1920') {
				chk('title.y 428', near(d.title.y, 428, T), d.title.y);
				chk('check 146 @737 cx960', near(d.check.w, 146, 1) && near(d.check.y, 737, T) && near(d.check.cx, 960, 2), d.check);
				chk('lead 963', near(d.lead.y, 963, T), d.lead.y);
				chk('body 1028', near(d.body[0].y, 1028, T), d.body[0].y);
				chk('NextStep 1323', near(d.en[0].y, 1323, T), d.en[0].y);
				chk('今後のご案内 1379', near(d.ja[0].y, 1379, T), d.ja[0].y);
				[160, 706, 1251].forEach((x, i) => chk('card' + i, near(d.cards[i].x, x, 2) && near(d.cards[i].y, 1566, T) && near(d.cards[i].w, 509, 2) && near(d.cards[i].h, 341, 2), d.cards[i]));
				d.nums.forEach((nb, i) => chk('num' + i, near(nb.y, 1493, T) && near(nb.x - d.cards[i].x, 29, 2), [nb.y, nb.x - d.cards[i].x]));
				d.icons.forEach((ib, i) => chk('icon' + i + ' bottom 1732', near(ib.b, 1732, 2), ib.b));
				d.ctitles.forEach((b, i) => chk('ctitle' + i, near(b.y, 1762, T), b.y));
				d.ctexts.forEach((b, i) => chk('ctext' + i, near(b.y, 1810, T), b.y));
				chk('explore 2067', near(d.explore.y, 2067, 2), d.explore.y);
				chk('Explore 2224', near(d.en[1].y, 2224, T), d.en[1].y);
				[160, 706, 1251].forEach((x, i) => chk('link' + i, near(d.links[i].x, x, 2) && near(d.links[i].y, 2418, T) && near(d.links[i].w, 509, 2) && near(d.links[i].h, 286, 2), d.links[i]));
				d.linkEn.forEach((b, i) => chk('linkEn' + i, near(b.y, 2555, T), b.y));
				d.linkJa.forEach((b, i) => chk('linkJa' + i, near(b.y, 2643, T), b.y));
				chk('footer 2864', near(d.footer.y, 2864, 8), d.footer.y);
				chk('lead font', d.font.lead.fw === '700' && d.font.lead.fs === '24px' && d.font.lead.ls === '2px' && d.font.lead.color === 'rgb(77, 77, 77)', d.font.lead);
				chk('body font', d.font.body.fw === '400' && d.font.body.fs === '18px' && d.font.body.lh === '27px', d.font.body);
				chk('ja font', d.font.ja.fw === '700' && d.font.ja.fs === '36px' && d.font.ja.color === 'rgb(26, 26, 26)' && d.jaLines.every((l) => l === 1), [d.font.ja, d.jaLines]);
				chk('num font', d.font.num.fs === '100px' && d.font.num.ls === '-3px' && d.font.num.color === 'rgb(217, 217, 217)', d.font.num);
				chk('ctext lines 1/2/2', d.ctextLines.join() === '1,2,2', d.ctextLines);
				chk('linkEn font', d.font.linkEn.fs === '64px' && d.font.linkEn.ls === '-3px', d.font.linkEn);
				chk('card deco', d.card.br === '10px' && d.card.bg === 'rgb(255, 255, 255)' && /12px 32px/.test(d.card.sh), d.card);
				chk('link deco', d.linkDeco.every((l) => l.br === '10px' && /12px 32px/.test(l.sh)), d.linkDeco);
			} else {
				chk('header 45', near(d.header.h, 45, 1), d.header.h);
				chk('title 296', near(d.title.y, 296, 3), d.title.y);
				chk('check 54 @444 cx188', near(d.check.w, 54, 1) && near(d.check.y, 444, 3) && near(d.check.cx, 188, 2), d.check);
				chk('lead 538', near(d.lead.y, 538, 3), d.lead.y);
				chk('body 574', near(d.body[0].y, 574, 3), d.body[0].y);
				chk('body 7 lines', d.bodyLines.reduce((a, b) => a + b, 0) === 7 || d.bodyLines.reduce((a, b) => a + b, 0) === 6, d.bodyLines);
				chk('NextStep 773', near(d.en[0].y, 773, 3), d.en[0].y);
				[863, 981, 1099].forEach((y, i) => chk('card' + i, near(d.cards[i].x, 22, 1) && near(d.cards[i].y, y, 3) && near(d.cards[i].w, 331, 1) && near(d.cards[i].h, 94, 2), d.cards[i]));
				d.nums.forEach((nb, i) => chk('num' + i, near(d.cards[i].y - nb.y, 24, 2) && near(nb.x, 37, 2), [d.cards[i].y - nb.y, nb.x]));
				d.icons.forEach((ib, i) => chk('icon' + i, near(ib.cx, 72, 2) && near(ib.cy, d.cards[i].cy, 3), [ib.cx, ib.cy - d.cards[i].cy]));
				d.ctitles.forEach((b, i) => { const blockC = (b.y + d.ctexts[i].b) / 2; chk('cblock' + i, near(b.x, 119, 1) && near(d.ctexts[i].x, 119, 1) && near(blockC, d.cards[i].cy, 3), [b.x, blockC - d.cards[i].cy]); });
				chk('explore 1273', near(d.explore.y, 1273, 2), d.explore.y);
				chk('Explore 1353', near(d.en[1].y, 1353, 3), d.en[1].y);
				chk('explore ja 2 lines', d.jaLines[1] === 2, d.jaLines);
				[1469, 1665, 1861].forEach((y, i) => chk('link' + i, near(d.links[i].x, 22, 1) && near(d.links[i].y, y, 3) && near(d.links[i].w, 331, 1) && near(d.links[i].h, 186, 2), d.links[i]));
				chk('footer 2127', near(d.footer.y, 2127, 6), d.footer.y);
				chk('fonts', d.font.title.fs === '50px' && d.font.lead.fs === '14px' && d.font.body.fs === '11px' && d.font.en.fs === '14px' && d.font.ja.fs === '16px' && d.font.num.fs === '36px' && d.font.ctitle.fs === '13px' && d.font.ctext.fs === '11px' && d.font.linkEn.fs === '40px' && d.font.linkJa.fs === '12px', d.font);
				chk('card deco', d.card.br === '5px' && /6px 16px/.test(d.card.sh), d.card);
			}
			chk('h1', d.h1.length === 1 && d.h1[0] === 'THANK YOU', d.h1);
			chk('zw', !d.zw, d.zw);
			chk('video', d.video === 0 && d.dxVideoReq === 0, [d.video, d.dxVideoReq]);
			chk('numFront', /p-thankscard__num/.test(d.numFront || ''), d.numFront);
			chk('hrefs', d.hrefs.map((h) => h.replace('http://exterior-exone-renewal.local', '')).join() === '/works/,/plans/,/dx/' && d.aInCards === 0, d.hrefs);
			chk('noindex', /noindex/.test(d.robots || ''), d.robots);
			chk('bodyClass', /has-fv-header/.test(d.bodyClass) && /is-thanks-page/.test(d.bodyClass) && !/is-contact-page|is-recruit-entry-page|is-recruit-page/.test(d.bodyClass), d.bodyClass);
			chk('scroll', d.scroll[0] <= d.scroll[1], d.scroll);
			chk('errors', d.errors.length === 0, d.errors);
			const want = url.startsWith('/contact') ? ['お問い合わせ、ありがとうございます。', '通常2営業日以内に担当スタッフよりご連絡いたします。', 'ご相談内容の確認・ご案内'] : ['ご応募、ありがとうございます。', '追って採用担当よりご連絡いたします。', '選考のご案内', '書類選考のうえ'];
			want.forEach((s) => chk('text ' + s, d.text.includes(s), null));
			if (url.startsWith('/recruit')) chk('no 営業日', !d.text.includes('営業日'), null);
			delete d.text;
			await ctx.close();
		}
	}
	// AC-01-17: JS 無効
	for (const url of ['/contact/thanks/', '/recruit/thanks/']) {
		const ctx = await browser.newContext({ viewport: { width: 1920, height: 1080 }, javaScriptEnabled: false });
		const page = await ctx.newPage();
		await page.goto('http://exterior-exone-renewal.local' + url, { waitUntil: 'load' });
		await page.waitForTimeout(500);
		const hidden = await page.evaluate(() => [...document.querySelectorAll('main *')].filter((e) => { const c = getComputedStyle(e); return c.opacity !== '1' || (c.transform !== 'none' && e.matches('[data-reveal], .p-thanksmsg__text')); }).map((e) => e.className.baseVal !== undefined ? e.className.baseVal : e.className).slice(0, 10));
		out.data['nojs ' + url] = hidden;
		if (hidden.length) ng('nojs ' + url, JSON.stringify(hidden));
		await ctx.close();
	}
	await browser.close();
	console.log(JSON.stringify(out, null, 1));
})();
